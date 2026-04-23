<?php

/**
 * DoVehicleEngine ObjectModel
 * Classe ObjectModel pour les motorisations
 */

class DoVehicleEngine extends ObjectModel
{
    /**
     * @var int
     */
    public $id_do_vehicle_engine;

    /**
     * @var int|null
     */
    public $id_do_vehicle_model;

    /**
     * @var string
     */
    public $name;

    /**
     * @var int|null
     */
    public $year_start;

    /**
     * @var int|null
     */
    public $year_end;

    /**
     * @var bool
     */
    public $active = true;

    /**
     * @var int
     */
    public $position = 0;

    /**
     * @var string
     */
    public $date_add;

    /**
     * @var string
     */
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table'   => 'do_vehicle_engine',
        'primary' => 'id_do_vehicle_engine',
        'fields'  => [
            'id_do_vehicle_model' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'name'                => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 128],
            'year_start'          => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'year_end'            => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'active'              => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true],
            'position'            => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'date_add'            => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd'            => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];
}
