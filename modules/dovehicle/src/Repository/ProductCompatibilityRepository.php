<?php
/**
 * ProductCompatibilityRepository
 * Liaisons produit <> véhicule (marque, modèle, motorisation)
 */

namespace DoVehicle\Repository;

use Db;

class ProductCompatibilityRepository
{
    private $table = 'do_product_vehicle_compat';

    /**
     * Récupérer toutes les compatibilités d'un produit
     */
    public function findByProduct(int $idProduct): array
    {
        $sql = 'SELECT vc.*, 
                       man.name AS manufacturer_name,
                       m.name AS model_name,
                       e.name AS engine_name
                FROM `' . _DB_PREFIX_ . $this->table . '` vc
                LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` man ON vc.id_manufacturer = man.id_manufacturer
                LEFT JOIN `' . _DB_PREFIX_ . 'do_vehicle_model` m ON vc.id_do_vehicle_model = m.id_do_vehicle_model
                LEFT JOIN `' . _DB_PREFIX_ . 'do_vehicle_engine` e ON vc.id_do_vehicle_engine = e.id_do_vehicle_engine
                WHERE vc.id_product = ' . (int)$idProduct . '
                ORDER BY man.name ASC, m.name ASC, e.name ASC';

        return Db::getInstance()->executeS($sql) ?: [];
    }

    /**
     * Récupérer une compatibilité par ID
     */
    public function findById(int $idCompat): ?array
    {
        $sql = 'SELECT vc.*, 
                       man.name AS manufacturer_name,
                       m.name AS model_name,
                       e.name AS engine_name
                FROM `' . _DB_PREFIX_ . $this->table . '` vc
                LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` man ON vc.id_manufacturer = man.id_manufacturer
                LEFT JOIN `' . _DB_PREFIX_ . 'do_vehicle_model` m ON vc.id_do_vehicle_model = m.id_do_vehicle_model
                LEFT JOIN `' . _DB_PREFIX_ . 'do_vehicle_engine` e ON vc.id_do_vehicle_engine = e.id_do_vehicle_engine
                WHERE vc.id_compat = ' . (int)$idCompat;

        return Db::getInstance()->getRow($sql) ?: null;
    }

    /**
     * Trouver les produits compatibles avec un véhicule
     */
    public function findProductsByVehicle(int $idManufacturer, ?int $idModel = null, ?int $idEngine = null): array
    {
        $sql = 'SELECT DISTINCT vc.id_product
                FROM `' . _DB_PREFIX_ . $this->table . '` vc
                INNER JOIN `' . _DB_PREFIX_ . 'product` p ON vc.id_product = p.id_product AND p.active = 1
                WHERE vc.id_manufacturer = ' . (int)$idManufacturer;

        if ($idModel) {
            $sql .= ' AND (vc.id_do_vehicle_model IS NULL OR vc.id_do_vehicle_model = ' . (int)$idModel . ')';
        }
        if ($idEngine) {
            $sql .= ' AND (vc.id_do_vehicle_engine IS NULL OR vc.id_do_vehicle_engine = ' . (int)$idEngine . ')';
        }

        $sql .= ' ORDER BY vc.id_product ASC';

        $result = Db::getInstance()->executeS($sql) ?: [];

        return array_column($result, 'id_product');
    }

    /**
     * Créer une compatibilité
     */
    public function create(array $data): int
    {
        $sql = 'INSERT INTO `' . _DB_PREFIX_ . $this->table . '`
                (id_product, id_manufacturer, id_do_vehicle_model, id_do_vehicle_engine, note, date_add)
                VALUES (
                    ' . (int)$data['id_product'] . ',
                    ' . (!empty($data['id_manufacturer']) ? (int)$data['id_manufacturer'] : 'NULL') . ',
                    ' . (!empty($data['id_do_vehicle_model']) ? (int)$data['id_do_vehicle_model'] : 'NULL') . ',
                    ' . (!empty($data['id_do_vehicle_engine']) ? (int)$data['id_do_vehicle_engine'] : 'NULL') . ',
                    ' . (!empty($data['note']) ? '"' . pSQL($data['note']) . '"' : 'NULL') . ',
                    NOW()
                )';

        if (Db::getInstance()->execute($sql)) {
            return Db::getInstance()->Insert_ID();
        }

        return 0;
    }

    /**
     * Mettre à jour une compatibilité
     */
    public function update(int $idCompat, array $data): bool
    {
        $updates = [];

        if (isset($data['note'])) {
            $updates[] = 'note = ' . (!empty($data['note']) ? '"' . pSQL($data['note']) . '"' : 'NULL');
        }

        if (empty($updates)) {
            return true;
        }

        $sql = 'UPDATE `' . _DB_PREFIX_ . $this->table . '`
                SET ' . implode(', ', $updates) . '
                WHERE id_compat = ' . (int)$idCompat;

        return Db::getInstance()->execute($sql);
    }

    /**
     * Supprimer une compatibilité
     */
    public function delete(int $idCompat): bool
    {
        $sql = 'DELETE FROM `' . _DB_PREFIX_ . $this->table . '`
                WHERE id_compat = ' . (int)$idCompat;

        return Db::getInstance()->execute($sql);
    }

    /**
     * Supprimer toutes les compatibilités d'un produit
     */
    public function deleteByProduct(int $idProduct): bool
    {
        $sql = 'DELETE FROM `' . _DB_PREFIX_ . $this->table . '`
                WHERE id_product = ' . (int)$idProduct;

        return Db::getInstance()->execute($sql);
    }

    /**
     * Compter les compatibilités d'un produit
     */
    public function countByProduct(int $idProduct): int
    {
        $sql = 'SELECT COUNT(*) as count FROM `' . _DB_PREFIX_ . $this->table . '`
                WHERE id_product = ' . (int)$idProduct;

        $result = Db::getInstance()->getRow($sql);

        return (int)($result['count'] ?? 0);
    }
}
