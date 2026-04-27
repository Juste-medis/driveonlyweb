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
use DoVehicle\Repository\CategoryFiltersRepository;
use DoVehicle\Service\VehicleService;
use DoVehicle\FormHandler\ProductVehicleFormHandler;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;

 
class Dovehicle extends Module
{
    // ─── Constantes du module ───────────────────────────────────────────────

    const VERSION      = '1.0.0';
    const DB_PREFIX_DO = 'ps_';        // prefix des tables métier

    // ─── Hooks enregistrés ─────────────────────────────────────────────────

    private const HOOKS = [
        // BO Produit
        'displayAdminProductsMainStepLeftColumnBottom', 
        'actionProductSave', 
        'actionProductAdd',
        // FO
        'displayTop',
        'displayHeader',
        'displayFooter',
        // 'displayCategoryHeader',
        "displayLeftColumn",
        // Nav
        'moduleRoutes',
        //Search 
        'actionProductSearchProviderRunQueryAfter'
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
            // && $this->installSql()
            && $this->registerHooks()
            && $this->setHookPosition('displayLeftColumn', 0)
            && $this->installTab();
    }

    public function uninstall(): bool
    {
        return parent::uninstall()
            // && $this->uninstallSql()
            && $this->uninstallTab();
    }

    public function hookdisplayLeftColumn($params)
    {
      try {
            $categoryVar = $this->context->smarty->getTemplateVars()['category']?? null;
  
            if ($categoryVar) { 
            $idCategory = (int) $categoryVar["id"];
            } elseif (
                $this->context->controller->getPageType() === 'category'
            ) {
                $category = $this->context->controller->getCategory();
                if ($category instanceof \Category) {
                    $idCategory = (int) $category->id;
                }
            }
            if (!$idCategory) {
                return '';
            }

            $filtersRepo = new CategoryFiltersRepository();
            $idLang = (int) $this->context->language->id;
            $summary = $filtersRepo->getCategoryFiltersSummary($idCategory, $idLang);


        $attributes = $filtersRepo->getAttributesWithValuesByCategory($summary);
        $features = $filtersRepo->getFeaturesByCategory($summary);

        $manufacturers = $filtersRepo->getManufacturersByCategory($summary);
        $models = $filtersRepo->getModelsByCategory($summary);
        $engines = $filtersRepo->getEnginesByCategory($summary);
        $families = $filtersRepo->getFamiliesByCategory($summary);


            // Passer les données au template
            $dataassign = [
                'dovehicle_attributes' => $attributes,
                'dovehicle_features'   => $features,
                'dovehicle_id_category' => $idCategory,
                'dovehicle_manufacturers' => $manufacturers,
                'dovehicle_models' => $models,
                'dovehicle_engines' => $engines,
                'dovehicle_families' => $families,
                'dovehicle_ajax_url'   => $this->context->link->getModuleLink('dovehicle', 'vehicle', ['id_manufacturer' => 0, 'id_model' => 0, 'id_engine' => 0], true),
                'dovehicle_fo_token'          => Tools::encrypt('dovehicle_fo'),

            ];
            $this->context->smarty->assign( $dataassign );

            
            return $this->display(__FILE__, 'views/templates/front/display_filters.twig');
        } catch (\Exception $e) { 
            myprint("ERREUR dans hookdisplayLeftColumn: " . $e->getMessage());  
            //stacktrace dans logs PS
            myprint($e->getTraceAsString());
            return '';
        }
    }
  
 
public function hookActionProductSearchProviderRunQueryAfter(array $params)
{
    $result = $params['result'] ?? null;

    if (!$result) {
        return;
    }

    $get = $_GET;

    $idCategory      = (int) ($get['id_category'] ?? 0);
    $attributeIds    = isset($get['attributes']) ? array_map('intval', (array) $get['attributes']) : [];
    $featureValueIds = isset($get['features']) ? array_map('intval', (array) $get['features']) : [];
    $idManufacturer  = (int) ($get['dov_manufacturer'] ?? 0);
    $idModel         = (int) ($get['dov_model'] ?? 0);
    $idEngine        = (int) ($get['dov_engine'] ?? 0);
    $idFamily        = (int) ($get['dov_family'] ?? 0);

    if (
        empty($attributeIds) &&
        empty($featureValueIds) &&
        !$idManufacturer &&
        !$idModel &&
        !$idEngine &&
        !$idFamily
    ) {
        return;
    }

    $productIds = $this->getFilteredProductIds(
        $idCategory,
        $attributeIds,
        $featureValueIds,
        $idManufacturer,
        $idModel,
        $idEngine,
        $idFamily
    );
 
    if (empty($productIds)) {
        $result->setProducts([]);
        $result->setTotalProductsCount(0);
        return;
    } 

    $products = $result->getProducts();
    
    //transforrmer $productIds en ([["id_product" => 12], ["id_product" => 45], ...]) pour comparaison avec $products
    $productIds = array_map(function ($id) {
        return ['id_product' => $id];
    }, $productIds);

 
    $filtered =  $productIds; 
    $query = $params['query'] ?? null;

    $page = (int) Tools::getValue('page', 0);
    if ($page <= 0 && $query && method_exists($query, 'getPage')) {
        $page = (int) $query->getPage();
    }
    $page = max(1, $page);

    $perPage = (int) Tools::getValue('resultsPerPage', 0);
    if ($perPage <= 0 && $query && method_exists($query, 'getResultsPerPage')) {
        $perPage = (int) $query->getResultsPerPage();
    }
    if ($perPage <= 0) {
        $perPage = (int) Tools::getValue('n', 12);
    }
    $perPage = max(1, $perPage);
    $totalFiltered = count($filtered);

    $offset = ($page - 1) * $perPage;
    $finalfiltered = array_slice($filtered, $offset, $perPage);


if (empty($finalfiltered)) { 
             $page = 1; 
        $offset = ($page - 1) * $perPage;
    $finalfiltered = array_slice($filtered, $offset, $perPage);

    }   


 // Ajouter une donnée personnalisée
        $result->my_custom_data = [
            'foo' => 'bar',
            'timestamp' => time(),
        ];



$result->setProducts($finalfiltered);
    $result->setTotalProductsCount($totalFiltered);
    return; 
}
                
    private function getFilteredProductIds(
            int   $idCategory,
            array $attributeIds      = [],
            array $featureValueIds   = [],
            int   $idManufacturer    = 0,
            int   $idModel           = 0,
            int   $idEngine          = 0,
            int   $idFamily          = 0
        ): array {
            $db = Db::getInstance();
            $ps = _DB_PREFIX_;
 
             $db->execute('SET SESSION group_concat_max_len = 1000000');

            $sql = '
                WITH RECURSIVE category_branch AS (
                    SELECT c.id_category
                    FROM `' . $ps . 'category` c
                    WHERE c.id_category = ' . (int) $idCategory . '
                    AND c.active = 1

                    UNION ALL

                    SELECT c.id_category
                    FROM `' . $ps . 'category` c
                    INNER JOIN category_branch cb ON cb.id_category = c.id_parent
                    WHERE c.active = 1
                ),

                product_scope AS (
                    SELECT DISTINCT cp.id_product
                    FROM `' . $ps . 'category_product` cp
                    INNER JOIN category_branch cb ON cb.id_category = cp.id_category
                    INNER JOIN `' . $ps . 'product` p
                        ON p.id_product = cp.id_product
                )

                SELECT ps.id_product
                FROM product_scope ps
                WHERE 1=1
            ';

            // ── Attributs (déclinaisons) ─────────────────────────────────────────
            if (!empty($attributeIds)) {
                $sql .= '
                AND EXISTS (
                    SELECT 1
                    FROM `' . $ps . 'product_attribute` pa
                    INNER JOIN `' . $ps . 'product_attribute_combination` pac
                        ON pac.id_product_attribute = pa.id_product_attribute
                    INNER JOIN `' . $ps . 'product_attribute_shop` pas
                        ON pas.id_product_attribute = pa.id_product_attribute
                       AND pas.id_shop = ' . (int) $this->context->shop->id . '
                    WHERE pa.id_product = ps.id_product
                      AND pac.id_attribute IN (' . implode(',', $attributeIds) . ')
                )';
            }

            // ── Features (caractéristiques) ──────────────────────────────────────
            if (!empty($featureValueIds)) {
                $sql .= '
                AND EXISTS (
                    SELECT 1
                    FROM `' . $ps . 'feature_product` fp
                    WHERE fp.id_product       = ps.id_product
                    AND fp.id_feature_value IN (' . implode(',', $featureValueIds) . ')
                )';
            }

            // ── Marque véhicule ──────────────────────────────────────────────────
            if ($idManufacturer > 0) {
                $sql .= '
                AND EXISTS (
                    SELECT 1
                    FROM `' . $ps . 'do_product_vehicle_compat` vc
                    WHERE vc.id_product      = ps.id_product
                    AND vc.id_manufacturer = ' . $idManufacturer . '
                )';
            }

            // ── Modèle véhicule ──────────────────────────────────────────────────
            if ($idModel > 0) {
                $sql .= '
                AND EXISTS (
                    SELECT 1
                    FROM `' . $ps . 'do_product_vehicle_compat` vc
                    WHERE vc.id_product          = ps.id_product
                    AND vc.id_do_vehicle_model = ' . $idModel . '
                )';
            }

            // ── Motorisation ─────────────────────────────────────────────────────
            if ($idEngine > 0) {
                $sql .= '
                AND EXISTS (
                    SELECT 1
                    FROM `' . $ps . 'do_product_vehicle_compat` vc
                    WHERE vc.id_product           = ps.id_product
                    AND vc.id_do_vehicle_engine = ' . $idEngine . '
                )';
            }

            // ── Famille produit ──────────────────────────────────────────────────
            if ($idFamily > 0) {
                $sql .= '
                AND EXISTS (
                    SELECT 1
                    FROM `' . $ps . 'do_product_family_link` fl
                    WHERE fl.id_product          = ps.id_product
                    AND fl.id_do_product_family = ' . $idFamily . '
                )';
            }

            $resource = $db->query($sql); 
            if (!$resource) {
                return [];
            }

            return array_column($resource->fetchAll(\PDO::FETCH_ASSOC), 'id_product');
        }




    public function hookDisplayAdminProductsMainStepLeftColumnBottom($params)
    {
        try {
            $idProduct = isset($params['id_product']) ? (int) $params['id_product'] : 0;

 
            // Récupérer les repositories
            $modelRepo   = new VehicleModelRepository();
            $familyRepo  = new ProductFamilyRepository();
            $compatRepo  = new ProductCompatibilityRepository();

            // Données pré-remplissage
            $existingCompats  = $idProduct ? $compatRepo->findByProduct($idProduct) : [];
            $linkedFamilies   = $idProduct ? $familyRepo->findFamiliesForProduct($idProduct) : [];
            $allFamilies      = $familyRepo->findAllFlat();
            $manufacturers    = $this->getManufacturerList();

 
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

 
            // Rendre et retourner le template Smarty
            return $this->display(__FILE__, 'views/templates/hook/product_vehicle_tab.tpl');
        } catch (\Exception $e) {
            myprint("ERREUR dans hookDisplayAdminProductsMainStepLeftColumnBottom: " . $e->getMessage());
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
 
    public function hookActionProductSave(array $params): void
    {
        $this->saveVehicleData($params); 
    }
    public function hookActionProductAdd(array $params): void
    {
        $this->saveVehicleData($params); 
    } 
    
    /**
     * Logique de sauvegarde commune création/mise à jour
     */
    private function saveVehicleData(array $params): void
    {
        $idProduct = isset($params['id_product']) ? (int) $params['id_product'] : 0;

        if (!$idProduct) {
            return;
        }
        
        $productData = $_POST["product"] ?? [];
     
        try { 
            $handler = $this->getService('dovehicle.form_handler.product_vehicle');
                
            $compatJson   = $productData['dovehicle_compat_json'] ?? '[]';
            $familiesJson = $productData['dovehicle_families_json'] ?? '[]';
                
          
            $compats   = json_decode($compatJson, true)   ?: [];
            $families  = json_decode($familiesJson, true) ?: []; 
 
            // $handler->saveCompatibilities($idProduct, $compats);
            $handler->saveFamilyLinks($idProduct, $families); 
         } catch (\Exception $e) { 
            myprint("ERROR in saveVehicleData: " . $e->getMessage());
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
        $this->context->controller->registerJavascript(
        'mon-script-custom',
        'modules/' . $this->name . '/views/js/filters.js',
            [
                'position' => 'bottom',
                'priority' => 150,
                'version' => filemtime(_PS_MODULE_DIR_.$this->name.'/views/js/filters.js'),
            ]
        );
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
 
        $this->context->smarty->assign([
            'dovehicle_manufacturers'     => $this->getManufacturerList(),
            'dovehicle_ajax_url_fo'       =>    $this->context->link->getModuleLink('dovehicle', 'vehicle', ['id_manufacturer' => 0, 'id_model' => 0, 'id_engine' => 0], true),
        ]);

        return $this->display(__FILE__, 'views/templates/front/vehicle_selector.tpl');
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
                    'id_model'            => ['regexp' => '[0-9]+', 'param' => 'id_model'],
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

    public function getService(string $serviceId) {
        $container = SymfonyContainer::getInstance();
        if (!$container->has($serviceId)) {
                if ($containermy->has($serviceId)) {
                    $containermy = $this->context->controller->getContainer();
        $container = $containermy;
                }

            return null;
        }

        return $container->get($serviceId);
    }

    private function setHookPosition($hookName, $position)
    {
        $idHook = Hook::getIdByName($hookName);
        $idModule = (int) $this->id;

        if (!$idHook) {
            return false;
        }

        return Db::getInstance()->update(
            'hook_module',
            ['position' => (int) $position],
            'id_module = ' . $idModule . ' AND id_hook = ' . (int) $idHook
        );
    }
}
