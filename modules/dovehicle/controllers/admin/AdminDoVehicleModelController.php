<?php

/**
 * Contrôleur BO — Gestion des Modèles de véhicule
 * URL : /admin/index.php?controller=AdminDoVehicleModel&token=xxx
 */

declare(strict_types=1);

require_once dirname(__FILE__) . '/../../classes/DoVehicleModel.php';

use DoVehicle\Repository\VehicleModelRepository;
use DoVehicle\Tools\Utils;

class AdminDoVehicleModelController extends ModuleAdminController
{
    /** @var VehicleModelRepository */
    private $modelRepo;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table     = 'do_vehicle_model';
        $this->className = 'DoVehicleModel';     // classe ObjectModel non utilisée ici
        $this->lang      = false;
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        parent::__construct();

        $this->module    = Module::getInstanceByName('dovehicle');
        $this->modelRepo = new VehicleModelRepository();


        // $sql = new DbQuery();
        // $sql->select('id_tab, id_parent, class_name, position')
        //     ->from('tab')
        //     ->orderBy('id_parent ASC, position ASC');

        // $tabs = Db::getInstance()->executeS($sql);

        // Utils::log(DoVehicleModel());

        // $hooks = Hook::getHooks();

        // foreach ($hooks as $hook) {
        //     // echo $hook['id_hook'].' - '.$hook['name'].'<br>';
        //     Utils::log($hook);
        // }


        // Définition du listing
        $this->fields_list = [
            'id_do_vehicle_model' => [
                'title'   => $this->l('ID'),
                'align'   => 'center',
                'width'   => 40,
            ],
            'manufacturer_name' => [
                'title'   => $this->l('Marque'),
                'filter_key' => 'man!name',
            ],
            'name' => [
                'title'   => $this->l('Modèle'),
                'filter_key' => 'a!name',
            ],
            'year_start' => [
                'title'   => $this->l('Année début'),
                'align'   => 'center',
                'width'   => 80,
            ],
            'year_end' => [
                'title'   => $this->l('Année fin'),
                'align'   => 'center',
                'width'   => 80,
            ],
            'active' => [
                'title'   => $this->l('Actif'),
                'active'  => 'status',
                'align'   => 'center',
                'type'    => 'bool',
            ],
        ];
    }

    /**
     * Formulaire de création / édition d'un modèle
     */
    public function renderForm(): string
    {
        $manufacturers = Db::getInstance()->executeS(
            'SELECT `id_manufacturer`, `name` FROM `' . _DB_PREFIX_ . 'manufacturer` WHERE `active` = 1 ORDER BY `name` ASC'
        );

        $manufacturerOptions = [];
        foreach ($manufacturers as $m) {
            $manufacturerOptions[] = ['id_manufacturer' => $m['id_manufacturer'], 'name' => $m['name']];
        }

        $this->fields_form = [
            'legend' => ['title' => $this->l('Modèle de véhicule'), 'icon' => 'directions_car'],
            'input'  => [
                [
                    'type'     => 'select',
                    'label'    => $this->l('Marque'),
                    'name'     => 'id_manufacturer',
                    'required' => true,
                    'options'  => [
                        'query' => $manufacturerOptions,
                        'id'    => 'id_manufacturer',
                        'name'  => 'name',
                    ],
                ],
                [
                    'type'     => 'text',
                    'label'    => $this->l('Nom du modèle'),
                    'name'     => 'name',
                    'required' => true,
                    'hint'     => $this->l('Ex : 850, S70, V70'),
                ],
                [
                    'type'  => 'text',
                    'label' => $this->l('Année début (optionnel)'),
                    'name'  => 'year_start',
                    'class' => 'fixed-width-sm',
                ],
                [
                    'type'  => 'text',
                    'label' => $this->l('Année fin (optionnel)'),
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

        // Pré-remplir si édition
        $idModel = (int) Tools::getValue('id_do_vehicle_model', 0);
        if ($idModel > 0) {
            $model = $this->modelRepo->findById($idModel);
            if ($model) {
                $this->fields_value = [
                    'id_manufacturer' => $model['id_manufacturer'],
                    'name'            => $model['name'],
                    'year_start'      => $model['year_start'],
                    'year_end'        => $model['year_end'],
                    'position'        => $model['position'],
                    'active'          => $model['active'],
                ];
            }
        } else {
            $this->fields_value = ['active' => 1, 'position' => 0];
        }

        return parent::renderForm();
    }

    public function processSave(): bool
    {
        $idModel = (int) Tools::getValue('id_do_vehicle_model', 0);
        $data = [
            'id_manufacturer' => (int) Tools::getValue('id_manufacturer'),
            'name'            => pSQL(Tools::getValue('name', '')),
            'year_start'      => (int) Tools::getValue('year_start') ?: null,
            'year_end'        => (int) Tools::getValue('year_end') ?: null,
            'position'        => (int) Tools::getValue('position', 0),
            'active'          => (int) Tools::getValue('active', 1),
        ];

        if (!$data['id_manufacturer'] || !$data['name']) {
            $this->errors[] = $this->l('Marque et nom sont obligatoires.');
            return false;
        }

        if ($idModel > 0) {
            $this->modelRepo->update($idModel, $data);
        } else {
            $this->modelRepo->create($data);
        }

        $this->redirect_after = self::$currentIndex . '&conf=4&token=' . $this->token;
        return true;
    }
}
