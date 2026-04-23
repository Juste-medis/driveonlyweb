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
        // 'displayCategoryHeader',
        "displayLeftColumn",
        // Nav
        'moduleRoutes',
        //Search
        'filterProductSearch'
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

                                           Utils::log($idCategory);

            // Récupérer attributs et caractéristiques
            $attributes = $filtersRepo->getAttributesWithValuesByCategory($idCategory, $idLang);
            $features = $filtersRepo->getFeaturesByCategory($idCategory, $idLang);

            // Passer les données au template
            $dataassign = [
                'dovehicle_attributes' => $attributes,
                'dovehicle_features'   => $features,
                'dovehicle_id_category' => $idCategory,
                'dovehicle_ajax_url'   => $this->context->link->getModuleLink('dovehicle', 'filters'),
            ];
            $this->context->smarty->assign( $dataassign );
        Utils::log($dataassign);

            return $this->display(__FILE__, 'views/templates/front/display_filters.twig');
        } catch (\Exception $e) {
            \DoVehicle\Tools\Utils::log("ERREUR dans hookdisplayLeftColumn: " . $e->getMessage());
            //stack trace complète dans les logs pour debug
            Utils::log($e->getTraceAsString());

            return '';
        }
    }
 
public function hookFilterProductSearch($params)
{
    $searchQuery = $_GET;

    $idCategory      = (int) ($searchQuery['id_category'] ?? 0);
    $idLang          = (int) ($searchQuery['id_lang'] ?? (int) Context::getContext()->language->id);
    $attributeIds    = isset($searchQuery['attributes']) ? array_map('intval', (array) $searchQuery['attributes']) : [];
    $featureValueIds = isset($searchQuery['features'])   ? array_map('intval', (array) $searchQuery['features'])   : [];

    if (!$idCategory || (empty($attributeIds) && empty($featureValueIds))) {
        return;
    }

    $productIds = $this->getFilteredProductIds($idCategory, $attributeIds, $featureValueIds);

    // On injecte les IDs dans le contexte pour que le controller category les utilise
    // PS 1.7 : on surcharge via hook actionProductSearchProviderRunQueryBefore
    // ou on passe par assignation Smarty + JS selon votre architecture
    Context::getContext()->smarty->assign([
        'dovehicle_filtered_ids'   => $productIds,
        'dovehicle_filtered_count' => count($productIds),
    ]);
}

/**
 * Retourne les IDs produits correspondant aux filtres attributs ET features
 * Logique : intersection (AND entre groupes, OR au sein d'un groupe)
 *
 * Exemple :
 *   attributeIds    = [1053, 1011]  → produits ayant l'attr 1053 OU 1011
 *   featureValueIds = [208]         → ET ayant la feature_value 208
 */
private function getFilteredProductIds(
    int   $idCategory,
    array $attributeIds,
    array $featureValueIds
): array {
    $db = Db::getInstance();
    $ps = _DB_PREFIX_;

    // ── Étape 1 : scope produits actifs de la catégorie (récursif) ──────────
    // On réutilise la même CTE que getCategoryFiltersSummary
    $db->execute('SET SESSION group_concat_max_len = 1000000');

    // ── Étape 2 : construire la requête d'intersection ───────────────────────
    // Stratégie :
    //   - Pour les attributs   : un produit doit avoir AU MOINS UN des id_attribute sélectionnés
    //   - Pour les features    : un produit doit avoir AU MOINS UNE des id_feature_value sélectionnées
    //   - Entre les deux blocs : ET (intersection)
    //
    // Si on veut un AND strict par groupe d'attributs (ex : Couleur=Rouge ET Taille=XL),
    // remplacer le HAVING par autant de EXISTS qu'il y a de groupes distincts.

    $sql = '
        WITH RECURSIVE category_branch AS (
            SELECT c.id_category
            FROM `' . $ps . 'category` c
            WHERE c.id_category = ' . $idCategory . '
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
            INNER JOIN `' . $ps . 'product` p ON p.id_product = cp.id_product AND p.active = 1
        )

        SELECT ps.id_product
        FROM product_scope ps
        WHERE 1=1
    ';

    // ── Filtre attributs ────────────────────────────────────────────────────
    if (!empty($attributeIds)) {
        $placeholders = implode(',', $attributeIds);
        $sql .= '
        AND EXISTS (
            SELECT 1
            FROM `' . $ps . 'product_attribute` pa
            INNER JOIN `' . $ps . 'product_attribute_combination` pac
                ON pac.id_product_attribute = pa.id_product_attribute
            WHERE pa.id_product = ps.id_product
              AND pac.id_attribute IN (' . $placeholders . ')
        )';
    }

    // ── Filtre features ─────────────────────────────────────────────────────
    if (!empty($featureValueIds)) {
        $placeholders = implode(',', $featureValueIds);
        $sql .= '
        AND EXISTS (
            SELECT 1
            FROM `' . $ps . 'feature_product` fp
            WHERE fp.id_product = ps.id_product
              AND fp.id_feature_value IN (' . $placeholders . ')
        )';
    }

    $resource = $db->query($sql);

    if (!$resource) {
        return [];
    }

    $rows = $resource->fetchAll(\PDO::FETCH_ASSOC);

    return array_column($rows, 'id_product');
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
 
            $compats   = json_decode($compatJson, true)   ?: [];
            $families  = json_decode($familiesJson, true) ?: [];

            $handler->saveCompatibilities($idProduct, $compats);
            $handler->saveFamilyLinks($idProduct, $families);

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
