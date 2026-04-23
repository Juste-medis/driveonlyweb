<?php

/**
 * Contrôleur BO — Gestion des Compatibilités Produit/Véhicule
 * Compatible PrestaShop 1.7.8
 */

declare(strict_types=1);
require_once dirname(__FILE__) . '/../../classes/DoProductCompatibility.php';

use DoVehicle\Repository\ProductCompatibilityRepository;
use DoVehicle\Repository\VehicleModelRepository;

class AdminDoProductCompatibilityController extends ModuleAdminController
{
    /** @var ProductCompatibilityRepository */
    private $compatRepo;

    /** @var VehicleModelRepository */
    private $modelRepo;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table     = 'do_product_vehicle_compat';
        $this->className = 'DoProductCompatibility';
        $this->lang      = false;
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        parent::__construct();

        $this->module    = Module::getInstanceByName('dovehicle');
        $this->compatRepo = new ProductCompatibilityRepository();
        $this->modelRepo  = new VehicleModelRepository();

        $this->fields_list = [
            'id_do_product_vehicle_compat' => [
                'title'   => $this->l('ID'),
                'align'   => 'center',
                'width'   => 40,
            ],
            'id_product' => [
                'title'   => $this->l('Produit'),
                'align'   => 'center',
                'width'   => 60,
            ],
            'manufacturer_name' => [
                'title'   => $this->l('Marque'),
            ],
            'model_name' => [
                'title'   => $this->l('Modèle'),
            ],
            'engine_name' => [
                'title'   => $this->l('Motorisation'),
            ],
            'note' => [
                'title'   => $this->l('Note'),
                'maxlength' => 50,
            ],
            'date_add' => [
                'title'   => $this->l('Créé le'),
                'type'    => 'datetime',
                'align'   => 'center',
            ],
        ];
    }

    /**
     * Restitue le formulaire d'édition/création
     */
    public function renderForm(): string
    {
        $allModels = $this->modelRepo->findAll();
        $modelOptions = [];
        foreach ($allModels as $m) {
            $modelOptions[] = [
                'id_do_vehicle_model' => $m['id_do_vehicle_model'],
                'name'                => ($m['manufacturer_name'] ?? '') . ' — ' . ($m['name'] ?? ''),
            ];
        }

        $this->fields_form = [
            'legend' => [
                'title' => $this->l('Compatibilité Produit / Véhicule'),
                'icon'  => 'settings',
            ],
            'input'  => [
                [
                    'type'     => 'text',
                    'label'    => $this->l('ID Produit'),
                    'name'     => 'id_product',
                    'required' => true,
                    'hint'     => $this->l('ID du produit PrestaShop'),
                    'class'    => 'fixed-width-sm',
                ],
                [
                    'type'     => 'text',
                    'label'    => $this->l('ID Manufacturer'),
                    'name'     => 'id_manufacturer',
                    'required' => true,
                    'hint'     => $this->l('ID de la marque PrestaShop'),
                    'class'    => 'fixed-width-sm',
                ],
                [
                    'type'     => 'select',
                    'label'    => $this->l('Modèle / Motorisation'),
                    'name'     => 'id_do_vehicle_model',
                    'hint'     => $this->l('Sélectionner le véhicule compatible'),
                    'options'  => [
                        'query' => $modelOptions,
                        'id'    => 'id_do_vehicle_model',
                        'name'  => 'name',
                    ],
                ],
                [
                    'type'  => 'textarea',
                    'label' => $this->l('Note (optionnelle)'),
                    'name'  => 'note',
                    'rows'  => 3,
                    'hint'  => $this->l('Ex: "Nécessite adaptation", "Modèle spécifique"'),
                ],
            ],
            'submit' => [
                'title' => $this->l('Enregistrer'),
            ],
        ];

        return parent::renderForm();
    }

    /**
     * Traite l'enregistrement du formulaire
     */
    public function processSave(): void
    {
        $idCompat = (int) Tools::getValue('id_do_product_vehicle_compat', 0);
        $idProduct = (int) Tools::getValue('id_product', 0);
        $idManufacturer = (int) Tools::getValue('id_manufacturer', 0);
        $idModel = (int) Tools::getValue('id_do_vehicle_model', 0);
        $note = Tools::getValue('note', '');

        if (!$idProduct || !$idManufacturer || !$idModel) {
            $this->errors[] = $this->l('Veuillez remplir tous les champs requis');
            return;
        }

        $data = [
            'id_product'           => $idProduct,
            'id_manufacturer'      => $idManufacturer,
            'id_do_vehicle_model'  => $idModel,
            'id_do_vehicle_engine' => 0,
            'note'                 => $note,
        ];

        try {
            if ($idCompat > 0) {
                $this->compatRepo->update($idCompat, $data);
                $this->confirmations[] = $this->l('Compatibilité mise à jour');
            } else {
                $this->compatRepo->create($data);
                $this->confirmations[] = $this->l('Compatibilité créée');
            }
        } catch (\Exception $e) {
            $this->errors[] = $this->l('Erreur : ') . $e->getMessage();
        }
    }

    /**
     * Supprime une compatibilité
     */
    public function processDelete(): void
    {
        $idCompat = (int) Tools::getValue('id_do_product_vehicle_compat', 0);

        if ($idCompat <= 0) {
            $this->errors[] = $this->l('ID invalide');
            return;
        }

        try {
            $this->compatRepo->delete($idCompat);
            $this->confirmations[] = $this->l('Compatibilité supprimée');
        } catch (\Exception $e) {
            $this->errors[] = $this->l('Erreur : ') . $e->getMessage();
        }
    }
}
