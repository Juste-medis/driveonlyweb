<?php

/**
 * Contrôleur BO — Gestion des Motorisations
 * Compatible PrestaShop 1.7.8.7
 */

declare(strict_types=1);
require_once dirname(__FILE__) . '/../../classes/DoVehicleEngine.php';

use DoVehicle\Repository\VehicleEngineRepository;
use DoVehicle\Repository\VehicleModelRepository;

class AdminDoVehicleEngineController extends ModuleAdminController
{
    /** @var VehicleEngineRepository */
    private $engineRepo;

    /** @var VehicleModelRepository */
    private $modelRepo;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table     = 'do_vehicle_engine';
        $this->className = 'DoVehicleEngine';
        $this->lang      = false;
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        parent::__construct();

        $this->module     = Module::getInstanceByName('dovehicle');
        $this->engineRepo = new VehicleEngineRepository();
        $this->modelRepo  = new VehicleModelRepository();

        $this->fields_list = [
            'id_do_vehicle_engine' => ['title' => $this->l('ID'), 'align' => 'center', 'width' => 40],
            'manufacturer_name'    => ['title' => $this->l('Marque')],
            'model_name'           => ['title' => $this->l('Modèle')],
            'name'                 => ['title' => $this->l('Motorisation')],
            'year_start'           => ['title' => $this->l('Début'), 'align' => 'center', 'width' => 60],
            'year_end'             => ['title' => $this->l('Fin'), 'align' => 'center', 'width' => 60],
            'active'               => [
                'title'  => $this->l('Actif'),
                'active' => 'status',
                'type'   => 'bool',
                'align'  => 'center',
            ],
        ];
    }

    public function renderForm(): string
    {
        $allModels = $this->modelRepo->findAll();
        $modelOptions = [];
        foreach ($allModels as $m) {
            $modelOptions[] = [
                'id_do_vehicle_model' => $m['id_do_vehicle_model'],
                'name'                => $m['manufacturer_name'] . ' — ' . $m['name'],
            ];
        }

        $fuelOptions = [
            ['id' => 'essence',    'name' => 'Essence'],
            ['id' => 'diesel',     'name' => 'Diesel'],
            ['id' => 'electrique', 'name' => 'Électrique'],
            ['id' => 'hybride',    'name' => 'Hybride'],
            ['id' => 'gpl',        'name' => 'GPL'],
            ['id' => 'autre',      'name' => 'Autre'],
        ];

        $this->fields_form = [
            'legend' => ['title' => $this->l('Motorisation'), 'icon' => 'build'],
            'input'  => [
                [
                    'type'     => 'select',
                    'label'    => $this->l('Modèle de véhicule'),
                    'name'     => 'id_do_vehicle_model',
                    'required' => true,
                    'options'  => [
                        'query' => $modelOptions,
                        'id'    => 'id_do_vehicle_model',
                        'name'  => 'name',
                    ],
                ],
                [
                    'type'     => 'text',
                    'label'    => $this->l('Nom de la motorisation'),
                    'name'     => 'name',
                    'required' => true,
                    'hint'     => $this->l('Ex : 2.0 TDI 150ch, 1.8 T, T5 250ch'),
                ],
                [
                    'type'    => 'select',
                    'label'   => $this->l('Type de carburant'),
                    'name'    => 'fuel_type',
                    'options' => ['query' => $fuelOptions, 'id' => 'id', 'name' => 'name'],
                ],
                [
                    'type'  => 'text',
                    'label' => $this->l('Cylindrée (cc)'),
                    'name'  => 'displacement_cc',
                    'class' => 'fixed-width-sm',
                ],
                [
                    'type'  => 'text',
                    'label' => $this->l('Année début'),
                    'name'  => 'year_start',
                    'class' => 'fixed-width-sm',
                ],
                [
                    'type'  => 'text',
                    'label' => $this->l('Année fin'),
                    'name'  => 'year_end',
                    'class' => 'fixed-width-sm',
                ],
                [
                    'type'  => 'text',
                    'label' => $this->l('Position'),
                    'name'  => 'position',
                    'class' => 'fixed-width-sm',
                ],
                [
                    'type'   => 'switch',
                    'label'  => $this->l('Actif'),
                    'name'   => 'active',
                    'values' => [
                        ['id' => 'active_on',  'value' => 1, 'label' => $this->l('Oui')],
                        ['id' => 'active_off', 'value' => 0, 'label' => $this->l('Non')],
                    ],
                ],
            ],
            'submit' => ['title' => $this->l('Enregistrer')],
        ];

        $idEngine = (int) Tools::getValue('id_do_vehicle_engine', 0);
        if ($idEngine > 0) {
            $engine = $this->engineRepo->findById($idEngine);
            if ($engine) {
                $this->fields_value = [
                    'id_do_vehicle_model' => $engine['id_do_vehicle_model'],
                    'name'                => $engine['name'],
                    // 'fuel_type'           => $engine['fuel_type'],
                    // 'power_hp'            => $engine['power_hp'],
                    // 'displacement_cc'     => $engine['displacement_cc'],
                    'year_start'          => $engine['year_start'],
                    'year_end'            => $engine['year_end'],
                    'position'            => $engine['position'],
                    'active'              => $engine['active'],
                ];
            }
        } else {
            $this->fields_value = ['active' => 1, 'position' => 0, 'fuel_type' => 'autre'];
        }

        return parent::renderForm();
    }

    public function processSave(): bool
    {
        $idEngine = (int) Tools::getValue('id_do_vehicle_engine', 0);
        $data = [
            'id_do_vehicle_model' => (int)    Tools::getValue('id_do_vehicle_model'),
            'name'                => pSQL(Tools::getValue('name', '')),
            'fuel_type'           =>           Tools::getValue('fuel_type', 'autre'),
            // 'power_hp'            => (int)    Tools::getValue('power_hp') ?: null,
            'displacement_cc'     => (int)    Tools::getValue('displacement_cc') ?: null,
            'year_start'          => (int)    Tools::getValue('year_start') ?: null,
            'year_end'            => (int)    Tools::getValue('year_end') ?: null,
            'position'            => (int)    Tools::getValue('position', 0),
            'active'              => (int)    Tools::getValue('active', 1),
        ];

        if (!$data['id_do_vehicle_model'] || !$data['name']) {
            $this->errors[] = $this->l('Modèle et nom de motorisation sont obligatoires.');
            return false;
        }

        if ($idEngine > 0) {
            $this->engineRepo->update($idEngine, $data);
        } else {
            $this->engineRepo->create($data);
        }

        $this->redirect_after = self::$currentIndex . '&conf=4&token=' . $this->token;
        return true;
    }
}
