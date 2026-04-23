<?php

/**
 * DoProductCompatibility ObjectModel
 * Classe ObjectModel pour les compatibilités produit/véhicule
 */

class DoProductCompatibility extends ObjectModel
{
    /**
     * @var int
     */
    public $id_do_product_vehicle_compat;

    /**
     * @var int
     */
    public $id_product;

    /**
     * @var int
     */
    public $id_manufacturer;

    /**
     * @var int|null
     */
    public $id_do_vehicle_model;

    /**
     * @var int|null
     */
    public $id_do_vehicle_engine;

    /**
     * @var string|null Notes optionnelles
     */
    public $note;

    /**
     * @var string
     */
    public $date_add;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table'   => 'do_product_vehicle_compat',
        'primary' => 'id_do_product_vehicle_compat',
        'fields'  => [
            'id_product'           => ['type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true],
            'id_manufacturer'      => ['type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true],
            'id_do_vehicle_model'  => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'id_do_vehicle_engine' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'note'                 => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => 255],
            'date_add'             => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];
}
