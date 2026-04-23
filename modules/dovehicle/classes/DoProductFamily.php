<?php

/**
 * DoProductFamily ObjectModel
 * Classe ObjectModel pour les familles de produits hiérarchiques
 */

class DoProductFamily extends ObjectModel
{
    /**
     * @var int
     */
    public $id_do_product_family;

    /**
     * @var int|null Identifiant de la famille parent (NULL pour racine)
     */
    public $id_parent;

    /**
     * @var string
     */
    public $name;

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
        'table'   => 'do_product_family',
        'primary' => 'id_do_product_family',
        'fields'  => [
            'id_parent' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'name'      => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 128],
            'active'    => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true],
            'position'  => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'date_add'  => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd'  => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];
}
