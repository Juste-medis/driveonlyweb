<?php

/**
 * Contrôleur AJAX pour le filtrage des produits
 * URL: /module/dovehicle/filters
 */

declare(strict_types=1);

use DoVehicle\Repository\CategoryFiltersRepository;
use DoVehicle\Tools\Utils;

class DovehicleFiltersModuleFrontController extends ModuleFrontController
{
    public $ajax = true;
    public $display_column_left = false;
    public $display_column_right = false;

    public function __construct()
    {
        parent::__construct();
        $this->display_column_left = false;
        $this->display_column_right = false;
    }

    public function initContent(): void
    {
        parent::initContent();

        try {
            $action = Tools::getValue('action', 'getFilters');

            switch ($action) {
                case 'filter':
                    $this->ajaxFilter();
                    break;
                case 'getFilters':
                default:
                    $this->ajaxGetFilters();
                    break;
            }
        } catch (\Exception $e) {
            Utils::log("ERREUR dans FiltersController: " . $e->getMessage());
            $this->ajaxDie(json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE));
        }
    }

    /**
     * Récupère les filtres disponibles pour une catégorie (GET)
     * URL: /module/dovehicle/filters?action=getFilters&id_category=X
     */
    private function ajaxGetFilters(): void
    {
        $idCategory = (int) Tools::getValue('id_category', 0);

        if (!$idCategory) {
            $this->ajaxDie(json_encode(['success' => false, 'error' => 'Category ID required'], JSON_UNESCAPED_UNICODE));
            return;
        }

        $filtersRepo = new CategoryFiltersRepository();
        $idLang = (int) $this->context->language->id;

        $attributes = $filtersRepo->getAttributesWithValuesByCategory($idCategory, $idLang);
        $features = $filtersRepo->getFeaturesByCategory($idCategory, $idLang);

        $this->ajaxDie(json_encode([
            'success' => true,
            'attributes' => $attributes,
            'features' => $features,
        ], JSON_UNESCAPED_UNICODE));
    }

    /**
     * Filtre les produits selon les critères sélectionnés (POST)
     * URL: /module/dovehicle/filters?action=filter
     * Body: {id_category, attributes[], features[], page, limit}
     */
    private function ajaxFilter(): void
    {
        $idCategory = (int) Tools::getValue('id_category', 0);
        $attributeIds = (array) Tools::getValue('attributes', []);
        $featureValueIds = (array) Tools::getValue('features', []);
        $page = max(1, (int) Tools::getValue('page', 1));
        $limit = min(100, max(1, (int) Tools::getValue('limit', 20)));

        if (!$idCategory) {
            $this->ajaxDie(json_encode(['success' => false, 'error' => 'Category ID required'], JSON_UNESCAPED_UNICODE));
            return;
        }

        // Nettoyer les IDs
        $attributeIds = array_filter(array_map('intval', $attributeIds));
        $featureValueIds = array_filter(array_map('intval', $featureValueIds));
        $offset = ($page - 1) * $limit;

        $filtersRepo = new CategoryFiltersRepository();

        // Récupérer les produits filtrés
        $filteredProducts = $filtersRepo->getFilteredProducts(
            $idCategory,
            $attributeIds,
            $featureValueIds,
            $limit,
            $offset
        );

        // Convertir les IDs de produit en objets Product avec données complètes
        $products = [];
        foreach ($filteredProducts as $row) {
            $product = new \Product((int) $row['id_product'], false, (int) $this->context->language->id);
            if ($product->id) {
                $products[] = $product;
            }
        }

        $this->ajaxDie(json_encode([
            'success' => true,
            'products' => $this->formatProducts($products),
            'total' => count($products),
            'page' => $page,
            'limit' => $limit,
        ], JSON_UNESCAPED_UNICODE));
    }

    /**
     * Formate les produits pour la réponse JSON
     */
    private function formatProducts(array $products): array
    {
        $formatted = [];
        foreach ($products as $product) {
            $cover = $product->getCover((int) $this->context->language->id);
            $formatted[] = [
                'id_product' => (int) $product->id,
                'name' => $product->name,
                'description_short' => $product->description_short,
                'price' => $product->getPrice(),
                'image_url' => $cover ? $this->context->link->getImageLink($product->link_rewrite, $cover['id_image']) : '',
                'link' => $this->context->link->getProductLink($product),
            ];
        }
        return $formatted;
    }
}
