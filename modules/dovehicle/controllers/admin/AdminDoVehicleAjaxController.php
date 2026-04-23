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
    public function init(): void
    {
        parent::init();

        $this->vehicleService = $this->getService('dovehicle.service.vehicle');
    }

    /**
     * Dispatcher principal
     * Lit le paramètre GET "action" et appelle la méthode correspondante
     */
    public function postProcess(): void
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
    private function actionGetModels(): void
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
    private function actionGetEngines(): void
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
    private function actionDeleteCompat(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->dieWithError('Méthode POST requise', 405);
        }

        $idCompat = (int) Tools::getValue('id_compat', 0);

        if ($idCompat <= 0) {
            $this->dieWithJson(['success' => false, 'message' => 'id_compat invalide']);
        }

        /** @var \DoVehicle\Repository\ProductCompatibilityRepository $compatRepo */
        $compatRepo = $this->getService('dovehicle.repository.product_compatibility');
        $deleted    = $compatRepo->deleteSingle($idCompat);

        $this->dieWithJson(['success' => $deleted]);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isXmlHttpRequest(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    private function dieWithJson(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function dieWithError(string $message, int $statusCode = 500): void
    {
        $this->dieWithJson(['success' => false, 'message' => $message], $statusCode);
    }

    /**
     * Override pour accéder au container Symfony
     */
    public function getService(string $serviceId): ?object
    {
        $container = $this->context->controller->getContainer();
        return $container->get($serviceId);
    }
}
