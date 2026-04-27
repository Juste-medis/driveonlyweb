<?php

/**
 * Contrôleur AJAX Back-Office
 * Endpoints pour les selects dépendants Marque > Modèle > Motorisation
 *
 * URL pattern (généré par PS) :
 *   /admin/index.php?controller=AdminDoVehicleAjax&action=getModels&token=xxx
 */

declare(strict_types=1);

use DoVehicle\Service\VehicleService;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;

class AdminDoVehicleAjaxController extends ModuleAdminController
{
    /** @var VehicleService */
    private $vehicleService;

    public function __construct()
    {
        parent::__construct();

        $this->ajax             = true;
        $this->content_only     = true;   // pas de décoration BO
        $this->module           = Module::getInstanceByName('dovehicle');
        $this->bootstrap        = false;
    }

    /**
     * Initialise le service depuis le container
     */
    public function init() 
    {
        parent::init();
        $this->vehicleService = $this->getService('dovehicle.service.vehicle');
    }

    /**
     * Dispatcher principal
     * Lit le paramètre GET "action" et appelle la méthode correspondante
     */
    public function postProcess()
    {
        // Sécurité : vérifier que la requête est bien un AJAX authentifié
        if (!$this->isXmlHttpRequest()) {
            $this->dieWithError('Requête non autorisée', 403);
        }

        $action = Tools::getValue('action', '');

        switch ($action) {
            case 'getModels':
                $this->actionGetModels();
                break;

            case 'getEngines':
                $this->actionGetEngines();
                break;

            case 'deleteCompat':
                $this->actionDeleteCompat();
                break;

            case 'addCompat':
                $this->actionAddCompat();
                break;

            default:
                $this->dieWithError('Action inconnue', 400);
        }
    }

    // ─── Actions ─────────────────────────────────────────────────────────────

    /**
     * Retourne les modèles d'une marque
     *
     * GET params : id_manufacturer (int)
     *
     * Exemple de réponse :
     * {
     *   "success": true,
     *   "data": [
     *     {"id": 12, "name": "850", "year_start": 1991, "year_end": 1997},
     *     {"id": 13, "name": "S70", "year_start": 1997, "year_end": 2000}
     *   ]
     * }
     */
    private function actionGetModels()
    {
        $idManufacturer = (int) Tools::getValue('id_manufacturer', 0);

        if ($idManufacturer <= 0) {
            $this->dieWithJson(['success' => false, 'message' => 'Paramètre id_manufacturer manquant ou invalide']);
        }

        $result = $this->vehicleService->getModelsForManufacturer($idManufacturer);

        $this->dieWithJson($result);
    }

    /**
     * Retourne les motorisations d'un modèle
     *
     * GET params : id_model (int)
     *
     * Exemple de réponse :
     * {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 45,
     *       "name": "2.0 T",
     *       "label": "2.0 T 200ch — Essence (1997–2000)",
     *       "fuel_type": "essence",
     *       "power_hp": 200,
     *       "displacement_cc": 1984,
     *       "year_start": 1997,
     *       "year_end": 2000
     *     }
     *   ]
     * }
     */
    private function actionGetEngines()
    {
        $idModel = (int) Tools::getValue('id_model', 0);

        if ($idModel <= 0) {
            $this->dieWithJson(['success' => false, 'message' => 'Paramètre id_model manquant ou invalide']);
        }

        $result = $this->vehicleService->getEnginesForModel($idModel);

        $this->dieWithJson($result);
    }

    /**
     * Supprime une compatibilité véhicule (appelé depuis le tableau BO)
     *
     * POST params : id_compat (int)
     */
    private function actionDeleteCompat()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->dieWithError('Méthode POST requise', 405);
        }

        $idCompat = (int) Tools::getValue('id_compat', 0);

        if ($idCompat <= 0) {
            $this->dieWithJson(['success' => false, 'message' => 'id_compat invalide']);
        }
 
        $compatRepo = $this->getService('dovehicle.repository.product_compatibility');
        $deleted    = $compatRepo->delete($idCompat);

        $this->dieWithJson(['success' => $deleted]);
    }

    /**
     * Ajoute une compatibilité véhicule (appelé depuis le formulaire BO)
     *
     * POST params : id_product, id_manufacturer, id_model, id_engine, note
     */
    private function actionAddCompat()
        {       
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->dieWithError('Méthode POST requise', 405);
            }

            $id_product = (int) Tools::getValue('id_product', 0); 
            $compatJson = Tools::getValue('compat', '{}');

            $compat = json_decode($compatJson, true);

            if ($id_product <= 0) {
                $this->dieWithJson(['success' => false, 'message' => 'id_product invalide']);
            }

            if (!is_array($compat)) {
                $this->dieWithJson(['success' => false, 'message' => 'JSON invalide']);
            }

            $compatRepo = $this->getService('dovehicle.repository.product_compatibility');

            $createdId = $compatRepo->create(
                array_merge($compat, ['id_product' => $id_product])
            );

            $this->dieWithJson(['success' => 'ok', 'id_compat' => $createdId]);
        }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isXmlHttpRequest(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    private function dieWithJson(array $data, int $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function dieWithError(string $message, int $statusCode = 500)
    {
        $this->dieWithJson(['success' => false, 'message' => $message], $statusCode);
    }


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
}
