<?php
/**
 * Contrôleur AJAX pour filtrer les produits
 * 
 * Usage: 
 * POST /modules/dovehicle/ajax/filters
 * Params: 
 *   - id_category: ID de la catégorie
 *   - features: IDs de features séparés par des virgules
 *   - attributes: IDs d'attributs séparés par des virgules
 *   - limit: nombre de produits par page (défaut 20)
 *   - offset: offset pour pagination (défaut 0)
 */

class DoVehicleFiltersAjaxModuleFrontController extends ModuleFrontController
{
    public $ajax = true;
    public $module = 'dovehicle';

    public function postProcess()
    {
        if (!Tools::getValue('action')) {
            $this->ajaxError('Action required');
        }

        $action = Tools::getValue('action');

        if ($action === 'filter') {
            $this->ajaxFilter();
        } elseif ($action === 'get_filters') {
            $this->ajaxGetFilters();
        } else {
            $this->ajaxError('Unknown action');
        }
    }

    /**
     * Filtre les produits et retourne la liste
     */
    private function ajaxFilter()
    {
        $idCategory = (int)Tools::getValue('id_category', 0);
        $limit = (int)Tools::getValue('limit', 20);
        $offset = (int)Tools::getValue('offset', 0);

        // Récupérer les filtres sélectionnés
        $features = array_filter(array_map('intval', explode(',', Tools::getValue('features', ''))));
        $attributes = array_filter(array_map('intval', explode(',', Tools::getValue('attributes', ''))));

        if ($idCategory <= 0) {
            $this->ajaxError('Invalid category');
        }

        try {
            $repo = new \DoVehicle\Repository\CategoryFiltersRepository();
            $products = $repo->getFilteredProducts($idCategory, $attributes, $features, $limit, $offset);

            $this->ajaxSuccess([
                'products' => $products,
                'count' => count($products),
                'limit' => $limit,
                'offset' => $offset,
            ]);
        } catch (Exception $e) {
            $this->ajaxError('Error filtering products: ' . $e->getMessage());
        }
    }

    /**
     * Récupère les filtres disponibles pour une catégorie
     */
    private function ajaxGetFilters()
    {
        $idCategory = (int)Tools::getValue('id_category', 0);
        $idLang = (int)Tools::getValue('id_lang', Context::getContext()->language->id);

        if ($idCategory <= 0) {
            $this->ajaxError('Invalid category');
        }

        try {
            $repo = new \DoVehicle\Repository\CategoryFiltersRepository();
            $filters = $repo->getCategoryFiltersSummary($idCategory, $idLang);

            $this->ajaxSuccess([
                'filters' => $filters,
                'id_category' => $idCategory,
            ]);
        } catch (Exception $e) {
            $this->ajaxError('Error getting filters: ' . $e->getMessage());
        }
    }

    /**
     * Retour AJAX succès
     */
    private function ajaxSuccess($data = [])
    {
        header('Content-Type: application/json');
        echo json_encode(array_merge(['success' => true], $data));
        die;
    }

    /**
     * Retour AJAX erreur
     */
    private function ajaxError($message = '')
    {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => $message,
        ]);
        die;
    }
}
