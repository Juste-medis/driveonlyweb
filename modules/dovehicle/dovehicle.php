<?php

/**
 * MODULE DOVEHICLE
 * Navigation véhicule : Marque > Modèle > Motorisation > Famille > Sous-famille
 *
 * Compatible PrestaShop 1.7.8.7
 *
 * @author  DriveOnly
 * @version 1.0.0
 */

declare(strict_types=1);

if (!defined('_PS_VERSION_')) {
    exit;
}

// Autoload PSR-4 des classes src/
require_once __DIR__ . '/vendor/autoload.php';

use DoVehicle\Repository\VehicleModelRepository;
use DoVehicle\Repository\VehicleEngineRepository;
use DoVehicle\Repository\ProductFamilyRepository;
use DoVehicle\Repository\ProductCompatibilityRepository;
use DoVehicle\Service\VehicleService;
use DoVehicle\FormHandler\ProductVehicleFormHandler;
use Symfony\Component\Form\Extension\Core\Type\TextType;

use DoVehicle\Tools\Utils;

class Dovehicle extends Module
{
    // ─── Constantes du module ───────────────────────────────────────────────

    const VERSION      = '1.0.0';
    const DB_PREFIX_DO = 'ps_';        // prefix des tables métier

    // ─── Hooks enregistrés ─────────────────────────────────────────────────

    private const HOOKS = [
        // BO Produit
        'displayAdminProductsMainStepLeftColumnBottom',
        'actionAfterCreateProductFormHandler',
        'actionAfterUpdateProductFormHandler',
        // FO
        'displayTop',
        'displayHeader',
        'displayFooter',
        // Nav
        'moduleRoutes',
    ];

    public function __construct()
    {
        $this->name                   = 'dovehicle';
        $this->tab                    = 'front_office_features';
        $this->version                = self::VERSION;
        $this->author                 = 'DriveOnly';
        $this->need_instance          = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.8.0', 'max' => '1.7.9.9'];
        $this->bootstrap              = true;

        parent::__construct();

        $this->displayName = $this->l('DoVehicle – Navigation Véhicule');
        $this->description = $this->l('Arborescence Marque > Modèle > Motorisation > Famille > Sous-famille');
        $this->confirmUninstall = $this->l('Supprimer toutes les données de compatibilité véhicule ?');
    }


    public function install(): bool
    {

        return parent::install()
            && $this->installSql()
            && $this->registerHooks()
            && $this->installTab();
    }

    public function uninstall(): bool
    {
        return parent::uninstall()
            && $this->uninstallSql()
            && $this->uninstallTab();
    }

    public function hookDisplayAdminProductsMainStepLeftColumnBottom($params)
    {
        try {
            $idProduct = isset($params['id_product']) ? (int) $params['id_product'] : 0;

            Utils::log("HOOK hookDisplayAdminProductsMainStepLeftColumnBottom appelé pour produit ID: " . $idProduct);

            // Récupérer les repositories
            $modelRepo   = new VehicleModelRepository();
            $familyRepo  = new ProductFamilyRepository();
            $compatRepo  = new ProductCompatibilityRepository();

            // Données pré-remplissage
            $existingCompats  = $idProduct ? $compatRepo->findByProduct($idProduct) : [];
            $linkedFamilies   = $idProduct ? $familyRepo->findFamiliesForProduct($idProduct) : [];
            $allFamilies      = $familyRepo->findAllFlat();
            $manufacturers    = $this->getManufacturerList();

            Utils::log("Compats trouvées: " . count($existingCompats));

            // Assigner les variables Smarty
            $this->context->smarty->assign([
                'dovehicle_manufacturers'    => $manufacturers,
                'dovehicle_existing_compats' => $existingCompats,
                'dovehicle_all_families'     => $allFamilies,
                'dovehicle_linked_families'  => array_column($linkedFamilies, 'id_do_product_family'),
                'dovehicle_id_product'       => $idProduct,
                'dovehicle_ajax_url'         => $this->context->link->getAdminLink('AdminDoVehicleAjax'),
                'dovehicle_token'            => Tools::getAdminTokenLite('AdminDoVehicleAjax'),
                'dovehicle_compat_json'      => json_encode($existingCompats),
                'dovehicle_families_json'    => json_encode(array_column($linkedFamilies, 'id_do_product_family')),
                "module_dir"        => $this->_path,
            ]);

            Utils::log("Hook hookDisplayAdminProductsMainStepLeftColumnBottom complété avec succès");

            // Rendre et retourner le template Smarty
            return $this->display(__FILE__, 'views/templates/hook/product_vehicle_tab.tpl');
        } catch (\Exception $e) {
            Utils::log("ERREUR dans hookDisplayAdminProductsMainStepLeftColumnBottom: " . $e->getMessage());
            return '';
        }
    }







    /**
     * Exécute install.sql en remplaçant PREFIX_ par le vrai préfixe PS
     */
    private function installSql(): bool
    {
        $sql  = file_get_contents(__DIR__ . '/install/install.sql');
        $sql  = str_replace('PREFIX_', _DB_PREFIX_, $sql);

        foreach (array_filter(array_map('trim', explode(';', $sql))) as $query) {
            if (!Db::getInstance()->execute($query)) {
                return false;
            }
        }

        return true;
    }

    private function uninstallSql(): bool
    {
        $sql = file_get_contents(__DIR__ . '/install/uninstall.sql');
        $sql = str_replace('PREFIX_', _DB_PREFIX_, $sql);

        foreach (array_filter(array_map('trim', explode(';', $sql))) as $query) {
            Db::getInstance()->execute($query); // on ne stoppe pas sur erreur lors de désinstall
        }

        return true;
    }

    private function registerHooks(): bool
    {
        foreach (self::HOOKS as $hook) {
            if (!$this->registerHook($hook)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Crée un onglet BO "DoVehicle" dans le menu Catalogue
     */
    private function installTab(): bool
    {
        $idParent = (int) Tab::getIdFromClassName('AdminParentManufacturers');

        if (!$idParent) {
            // Fallback : si le menu n'existe pas (installation atypique),
            // on se raccroche à AdminCatalog plutôt que d'échouer
            $idParent = (int) Tab::getIdFromClassName('AdminCatalog');
        }

        $subTabs = [
            'AdminDoVehicleModel'  => [
                'fr' => 'Modèles véhicule',
                'en' => 'Vehicle Models',
            ],
            'AdminDoVehicleEngine' => [
                'fr' => 'Motorisations',
                'en' => 'Engines',
            ],
            'AdminDoProductFamily' => [
                'fr' => 'Familles produit',
                'en' => 'Product Families',
            ],
        ];

        $languages = Language::getLanguages(false);

        foreach ($subTabs as $className => $labels) {
            // Ne pas créer deux fois le même onglet (sécurité re-install)
            if (Tab::getIdFromClassName($className)) {
                continue;
            }

            $tab            = new Tab();
            $tab->active    = 1;
            $tab->class_name = $className;
            $tab->module    = $this->name;
            $tab->id_parent = $idParent;

            foreach ($languages as $lang) {
                // Utiliser le label fr si la langue est FR, sinon EN par défaut
                $iso = strtolower($lang['iso_code']);
                $tab->name[$lang['id_lang']] = $labels[$iso] ?? $labels['en'];
            }

            if (!$tab->add()) {
                return false;
            }
        }

        return true;
    }

    private function uninstallTab(): bool
    {
        $tabs = [
            'AdminDoVehicleModel',
            'AdminDoVehicleEngine',
            'AdminDoProductFamily',
        ];

        foreach ($tabs as $className) {
            $idTab = (int) Tab::getIdFromClassName($className);
            if ($idTab) {
                $tab = new Tab($idTab);
                $tab->delete();
            }
        }

        return true;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CONFIGURATION PAGE (page module dans BO)
    // ─────────────────────────────────────────────────────────────────────────

    public function getContent(): string
    {
        $output = '';

        if (Tools::isSubmit('submitDoVehicleConfig')) {
            Configuration::updateValue(
                'DOVEHICLE_SELECTOR_POSITION',
                (string) Tools::getValue('DOVEHICLE_SELECTOR_POSITION')
            );
            Configuration::updateValue(
                'DOVEHICLE_COOKIE_LIFETIME',
                (int) Tools::getValue('DOVEHICLE_COOKIE_LIFETIME')
            );
            $output .= $this->displayConfirmation($this->l('Configuration sauvegardée.'));
        }

        return $output . $this->renderConfigForm();
    }

    private function renderConfigForm(): string
    {
        $helper = new HelperForm();
        $helper->module        = $this;
        $helper->identifier    = 'id_dovehicle_config';
        $helper->title         = $this->displayName;
        $helper->submit_action = 'submitDoVehicleConfig';
        $helper->currentIndex  = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token         = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars      = [
            'fields_value' => [
                'DOVEHICLE_SELECTOR_POSITION' => Configuration::get('DOVEHICLE_SELECTOR_POSITION', null, null, null, 'displayTop'),
                'DOVEHICLE_COOKIE_LIFETIME'   => Configuration::get('DOVEHICLE_COOKIE_LIFETIME', null, null, null, 30),
            ],
            'languages' => $this->context->controller->getLanguages(),
        ];

        return $helper->generateForm([[
            'form' => [
                'legend' => ['title' => $this->l('Paramètres'), 'icon' => 'icon-cogs'],
                'input'  => [
                    [
                        'type'    => 'select',
                        'label'   => $this->l('Position du sélecteur véhicule (FO)'),
                        'name'    => 'DOVEHICLE_SELECTOR_POSITION',
                        'options' => [
                            'query' => [
                                ['id' => 'displayTop',    'name' => 'displayTop (bandeau haut)'],
                                ['id' => 'displayHeader', 'name' => 'displayHeader (header)'],
                                ['id' => 'displayFooter', 'name' => 'displayFooter (pied de page)'],
                            ],
                            'id'    => 'id',
                            'name'  => 'name',
                        ],
                    ],
                    [
                        'type'  => 'text',
                        'label' => $this->l('Durée de vie du cookie véhicule (jours)'),
                        'name'  => 'DOVEHICLE_COOKIE_LIFETIME',
                        'class' => 'fixed-width-sm',
                    ],
                ],
                'submit' => ['title' => $this->l('Sauvegarder'), 'class' => 'btn btn-default pull-right'],
            ],
        ]]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HOOKS — BACK OFFICE
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Sauvegarde après création produit
     */
    public function hookActionAfterCreateProductFormHandler(array $params): void
    {
        $this->saveVehicleData($params);
    }

    /**
     * Sauvegarde après mise à jour produit
     */
    public function hookActionAfterUpdateProductFormHandler(array $params): void
    {
        $this->saveVehicleData($params);
    }

    /**
     * Logique de sauvegarde commune création/mise à jour
     */
    private function saveVehicleData(array $params): void
    {
        $idProduct = isset($params['id']) ? (int) $params['id'] : 0;

        if (!$idProduct) {
            return;
        }

        try {
            $handler = $this->getService('dovehicle.form_handler.product_vehicle');

            // Récupérer les données depuis POST (champs hidden du formulaire)
            $compatJson   = Tools::getValue('product.dovehicle_compat_json', '[]');
            $familiesJson = Tools::getValue('product.dovehicle_families_json', '[]');

            Utils::log("Saving DoVehicle data for product ID: " . $idProduct);
            Utils::log("Compat JSON: " . substr($compatJson, 0, 50) . "...");

            $compats   = json_decode($compatJson, true)   ?: [];
            $families  = json_decode($familiesJson, true) ?: [];

            $handler->saveCompatibilities($idProduct, $compats);
            $handler->saveFamilyLinks($idProduct, $families);

            Utils::log("DoVehicle data saved successfully. Compats: " . count($compats) . ", Families: " . count($families));
        } catch (\Exception $e) {
            Utils::log("ERROR in saveVehicleData: " . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HOOKS — FRONT OFFICE
    // ─────────────────────────────────────────────────────────────────────────

    public function hookDisplayHeader(): string
    {
        // Chargement JS + CSS FO sur toutes les pages
        $this->context->controller->addJS($this->_path . 'views/js/fo_vehicle.js');
        $this->context->controller->addCSS($this->_path . 'views/css/fo_vehicle.css');

        return '';
    }

    public function hookDisplayTop(): string
    {
        // Afficher le sélecteur véhicule uniquement si configuré pour displayTop
        if (Configuration::get('DOVEHICLE_SELECTOR_POSITION') !== 'displayTop') {
            return '';
        }

        return $this->renderVehicleSelector();
    }

    public function hookDisplayFooter(): string
    {
        if (Configuration::get('DOVEHICLE_SELECTOR_POSITION') !== 'displayFooter') {
            return '';
        }

        return $this->renderVehicleSelector();
    }

    /**
     * Rendu du widget de sélection véhicule (FO)
     */
    private function renderVehicleSelector(): string
    {
        $modelRepo =  new VehicleModelRepository();

        // Véhicule actuellement sélectionné (cookie ou session)
        $selectedVehicle = $this->getSelectedVehicleFromContext();

        $this->context->smarty->assign([
            'dovehicle_manufacturers'     => $this->getManufacturerList(),
            'dovehicle_selected_vehicle'  => $selectedVehicle,
            'dovehicle_ajax_url_fo'       => "", //$this->context->link->getModuleLink($this->name, 'vehicle'),
            'dovehicle_fo_token'          => Tools::encrypt('dovehicle_fo'),
        ]);

        return $this->display(__FILE__, 'views/templates/front/vehicle_selector.tpl');
    }

    /**
     * Récupère le véhicule sélectionné depuis le cookie ou la session
     */
    public function getSelectedVehicleFromContext(): array
    {
        $cookie = $this->context->cookie;

        $idEngine = isset($cookie->dovehicle_engine) ? (int) $cookie->dovehicle_engine : 0;
        $idModel  = isset($cookie->dovehicle_model)  ? (int) $cookie->dovehicle_model  : 0;
        $idBrand  = isset($cookie->dovehicle_brand)  ? (int) $cookie->dovehicle_brand  : 0;

        if (!$idBrand) {
            return [];
        }

        $engineRepo = new VehicleEngineRepository();
        $modelRepo  =  new VehicleModelRepository();

        $result = [
            'id_manufacturer'      => $idBrand,
            'id_do_vehicle_model'  => $idModel,
            'id_do_vehicle_engine' => $idEngine,
        ];

        if ($idModel) {
            $model = $modelRepo->findById($idModel);
            $result['model_name'] = $model ? $model['name'] : '';
        }

        if ($idEngine) {
            $engine = $engineRepo->findById($idEngine);
            $result['engine_name'] = $engine ? $engine['name'] : '';
        }

        return $result;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HOOKS — ROUTES
    // ─────────────────────────────────────────────────────────────────────────

    public function hookModuleRoutes(): array
    {
        return [
            'module-dovehicle-vehicle' => [
                'controller' => 'vehicle',
                'rule'       => 'vehicule/{id_manufacturer}/{id_model}/{id_engine}',
                'keywords'   => [
                    'id_manufacturer' => ['regexp' => '[0-9]+', 'param' => 'id_manufacturer'],
                    'id_model'        => ['regexp' => '[0-9]+', 'param' => 'id_model'],
                    'id_engine'       => ['regexp' => '[0-9]+', 'param' => 'id_engine'],
                ],
                'params' => [
                    'fc'     => 'module',
                    'module' => $this->name,
                ],
            ],
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Retourne la liste des marques (ps_manufacturer) actives
     */
    public function getManufacturerList(): array
    {
        return Db::getInstance()->executeS(
            'SELECT m.`id_manufacturer`, m.`name`
             FROM `' . _DB_PREFIX_ . 'manufacturer` m
             WHERE m.`active` = 1
             ORDER BY m.`name` ASC'
        ) ?: [];
    }

    /**
     * Accès au container Symfony (services.yml)
     * Encapsulé ici pour éviter la dépendance directe dans le code métier
     */
    public function getService(string $serviceId): ?object
    {
        $container = $this->context->controller->getContainer();
        return $container->get($serviceId);
    }
}
