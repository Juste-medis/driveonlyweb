<?php

/**
 * Contrôleur Front-Office — Module DoVehicle
 *
 * Gère :
 * 1. La sélection / mémorisation du véhicule (cookie)
 * 2. L'affichage de la liste produits compatibles
 * 3. Les endpoints AJAX FO pour les selects en cascade
 *
 * URL d'accès (via moduleRoutes) :
 *   /vehicule/{id_manufacturer}/{id_model}/{id_engine}
 *   /module/dovehicle/vehicle?action=getModels&id_manufacturer=X
 */

declare(strict_types=1);

use DoVehicle\Service\VehicleService;
use DoVehicle\Repository\ProductFamilyRepository;
use DoVehicle\Repository\VehicleModelRepository;

class DovehicleVehicleModuleFrontController extends ModuleFrontController
{
    /** @var VehicleService */
    private $vehicleService;

    /** @var ProductFamilyRepository */
    private $familyRepo;

    public function __construct()
    {
        parent::__construct();

        $this->display_column_left  = false;
        $this->display_column_right = false;
    }

    // ─── Init ────────────────────────────────────────────────────────────────

    public function init(): void
    {
        parent::init();

        $container = $this->context->controller->getContainer();

        $this->vehicleService = $container->get('dovehicle.service.vehicle');
        $this->familyRepo     = $container->get('dovehicle.repository.product_family');
    }

    // ─── Dispatcher ──────────────────────────────────────────────────────────

    public function initContent(): void
    {
        parent::initContent();

        $action = Tools::getValue('action', '');

        // Actions AJAX
        if (in_array($action, ['getModels', 'getEngines', 'saveVehicle', 'clearVehicle'], true)) {
            $this->handleAjaxAction($action);
            return;
        }

        // Page produits compatibles
        $this->initProductListContent();
    }

    // ─── AJAX FO ─────────────────────────────────────────────────────────────

    private function handleAjaxAction(string $action): void
    {
        // Vérification token anti-CSRF
        // Note : pour les AJAX publics on utilise un token statique chiffré
        // La vérification CSRF stricte est réservée aux actions mutantes (POST)

        switch ($action) {
            case 'getModels':
                $this->ajaxGetModels();
                break;

            case 'getEngines':
                $this->ajaxGetEngines();
                break;

            case 'saveVehicle':
                $this->ajaxSaveVehicle();
                break;

            case 'clearVehicle':
                $this->ajaxClearVehicle();
                break;
        }
    }

    /**
     * Retourne les modèles d'une marque en JSON
     * GET /module/dovehicle/vehicle?action=getModels&id_manufacturer=X
     *
     * Réponse :
     * {"success":true,"data":[{"id":12,"name":"850","year_start":1991,"year_end":1997},...]}
     */
    private function ajaxGetModels(): void
    {
        $idManufacturer = (int) Tools::getValue('id_manufacturer', 0);

        $result = $this->vehicleService->getModelsForManufacturer($idManufacturer);

        $this->ajaxDie(json_encode($result, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Retourne les motorisations d'un modèle en JSON
     * GET /module/dovehicle/vehicle?action=getEngines&id_model=X
     */
    private function ajaxGetEngines(): void
    {
        $idModel = (int) Tools::getValue('id_model', 0);

        $result = $this->vehicleService->getEnginesForModel($idModel);

        $this->ajaxDie(json_encode($result, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Mémorise le véhicule choisi dans le cookie
     * POST /module/dovehicle/vehicle?action=saveVehicle
     * Body : {id_manufacturer, id_model, id_engine}
     */
    private function ajaxSaveVehicle(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->ajaxDie(json_encode(['success' => false, 'message' => 'POST requis']));
        }

        $idBrand  = (int) Tools::getValue('id_manufacturer', 0);
        $idModel  = (int) Tools::getValue('id_model', 0);
        $idEngine = (int) Tools::getValue('id_engine', 0);

        if ($idBrand <= 0) {
            $this->ajaxDie(json_encode(['success' => false, 'message' => 'Marque invalide']));
        }

        $lifetime = (int) Configuration::get('DOVEHICLE_COOKIE_LIFETIME', null, null, null, 30);

        // Stockage dans le cookie PrestaShop (géré par la classe Cookie native PS)
        $this->context->cookie->dovehicle_brand  = $idBrand;
        $this->context->cookie->dovehicle_model  = $idModel;
        $this->context->cookie->dovehicle_engine = $idEngine;
        $this->context->cookie->write();

        $this->ajaxDie(json_encode([
            'success'  => true,
            'redirect' => $this->buildVehicleUrl($idBrand, $idModel, $idEngine),
        ], JSON_UNESCAPED_UNICODE));
    }

    /**
     * Efface le véhicule mémorisé
     */
    private function ajaxClearVehicle(): void
    {
        $this->context->cookie->__unset('dovehicle_brand');
        $this->context->cookie->__unset('dovehicle_model');
        $this->context->cookie->__unset('dovehicle_engine');
        $this->context->cookie->write();

        $this->ajaxDie(json_encode(['success' => true]));
    }

    // ─── Page produits compatibles ────────────────────────────────────────────

    /**
     * Prépare la page listant les produits compatibles avec le véhicule sélectionné
     */
    private function initProductListContent(): void
    {
        // Récupération des params (GET ou route nommée)
        $idManufacturer = (int) Tools::getValue('id_manufacturer', 0);
        $idModel        = (int) Tools::getValue('id_model', 0);
        $idEngine       = (int) Tools::getValue('id_engine', 0);
        $idFamily       = Tools::getValue('id_family', null);
        $idFamily       = $idFamily !== null ? (int) $idFamily : null;
        $page           = max(1, (int) Tools::getValue('page', 1));
        $perPage        = 24;

        // Si pas de véhicule en paramètre, lire le cookie
        if ($idManufacturer <= 0) {
            $idManufacturer = (int) ($this->context->cookie->dovehicle_brand  ?? 0);
            $idModel        = (int) ($this->context->cookie->dovehicle_model  ?? 0);
            $idEngine       = (int) ($this->context->cookie->dovehicle_engine ?? 0);
        }

        // Récupérer les produits compatibles
        $result = $this->vehicleService->getCompatibleProducts(
            $idManufacturer,
            $idModel,
            $idEngine,
            $idFamily,
            (int) $this->context->language->id,
            (int) $this->context->shop->id,
            $page,
            $perPage
        );

        // Familles pour le filtre latéral
        $familyTree = $this->familyRepo->findTree();

        // Pagination
        $totalPages = $perPage > 0 ? (int) ceil($result['total'] / $perPage) : 1;

        // Breadcrumb
        $breadcrumb = $this->buildBreadcrumb($idManufacturer, $idModel, $idEngine, $idFamily);

        // Méta SEO (canonical + title)
        $vehicleLabel = $this->buildVehicleLabel($idManufacturer, $idModel, $idEngine);
        $metaTitle    = $vehicleLabel
            ? sprintf($this->module->l('Pièces et accessoires pour %s'), $vehicleLabel)
            : $this->module->l('Catalogue pièces & accessoires');

        // Assignation Smarty
        $this->context->smarty->assign([
            'dovehicle_products'        => $result['products'],
            'dovehicle_total'           => $result['total'],
            'dovehicle_page'            => $page,
            'dovehicle_per_page'        => $perPage,
            'dovehicle_total_pages'     => $totalPages,
            'dovehicle_family_tree'     => $familyTree,
            'dovehicle_id_family'       => $idFamily,
            'dovehicle_id_manufacturer' => $idManufacturer,
            'dovehicle_id_model'        => $idModel,
            'dovehicle_id_engine'       => $idEngine,
            'dovehicle_breadcrumb'      => $breadcrumb,
            'dovehicle_vehicle_label'   => $vehicleLabel,
            'dovehicle_meta_title'      => $metaTitle,
            'dovehicle_ajax_url'        => $this->context->link->getModuleLink('dovehicle', 'vehicle'),
        ]);

        $this->setTemplate('module:dovehicle/views/templates/front/product_list.tpl');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function buildVehicleUrl(int $idBrand, int $idModel, int $idEngine): string
    {
        return $this->context->link->getModuleLink(
            'dovehicle',
            'vehicle',
            [
                'id_manufacturer' => $idBrand,
                'id_model'        => $idModel,
                'id_engine'       => $idEngine,
            ]
        );
    }

    private function buildVehicleLabel(int $idBrand, int $idModel, int $idEngine): string
    {
        if ($idBrand <= 0) {
            return '';
        }

        $parts = [];

        $manufacturer = Manufacturer::getNameById($idBrand);
        if ($manufacturer) {
            $parts[] = $manufacturer;
        }

        if ($idModel > 0) {
            $modelRepo = new VehicleModelRepository();
            $model = $modelRepo->findById($idModel);
            if ($model) {
                $parts[] = $model['name'];
            }
        }

        if ($idEngine > 0) {
            $container = $this->context->controller->getContainer();

            $engineRepo = $container->get('dovehicle.repository.vehicle_engine');
            $engine = $engineRepo->findById($idEngine);
            if ($engine) {
                $parts[] = $engine['name'];
            }
        }

        return implode(' ', $parts);
    }

    private function buildBreadcrumb(
        int $idBrand,
        int $idModel,
        int $idEngine,
        ?int $idFamily
    ): array {
        $breadcrumb = [
            ['label' => $this->module->l('Accueil'), 'url' => $this->context->link->getPageLink('index')],
            ['label' => $this->module->l('Catalogue véhicule'), 'url' => ''],
        ];

        if ($idBrand > 0) {
            $name = Manufacturer::getNameById($idBrand);
            $breadcrumb[] = [
                'label' => $name,
                'url'   => $this->buildVehicleUrl($idBrand, 0, 0),
            ];
        }

        if ($idModel > 0) {
            $modelRepo = new VehicleModelRepository();
            $model = $modelRepo->findById($idModel);
            if ($model) {
                $breadcrumb[] = [
                    'label' => $model['name'],
                    'url'   => $this->buildVehicleUrl($idBrand, $idModel, 0),
                ];
            }
        }

        return $breadcrumb;
    }
}
