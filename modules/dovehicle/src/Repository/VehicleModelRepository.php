<?php
/**
 * VehicleModelRepository
 * Modèles de véhicule (XC90, Serie 5, etc.)
 */

namespace DoVehicle\Repository;

use Db;

class VehicleModelRepository
{
    private $table = 'do_vehicle_model';

    /**
     * Récupérer tous les modèles d'une marque
     */
    public function findByManufacturer(int $idManufacturer, bool $activeOnly = true): array
    {
        $sql = 'SELECT m.*
                FROM `' . _DB_PREFIX_ . $this->table . '` m
                WHERE m.id_manufacturer = ' . (int)$idManufacturer;

        if ($activeOnly) {
            $sql .= ' AND m.active = 1';
        }

        $sql .= ' ORDER BY m.position ASC, m.name ASC';

        return Db::getInstance()->executeS($sql) ?: [];
    }

    /**
     * Récupérer un modèle par ID
     */
    public function findById(int $id): ?array
    {
        $sql = 'SELECT m.*, man.name AS manufacturer_name
                FROM `' . _DB_PREFIX_ . $this->table . '` m
                LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` man ON m.id_manufacturer = man.id_manufacturer
                WHERE m.id_do_vehicle_model = ' . (int)$id;

        return Db::getInstance()->getRow($sql) ?: null;
    }

    /**
     * Récupérer tous les modèles
     */
    public function findAll(): array
    {
        $sql = 'SELECT m.*, man.name AS manufacturer_name
                FROM `' . _DB_PREFIX_ . $this->table . '` m
                LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` man ON m.id_manufacturer = man.id_manufacturer
                ORDER BY man.name ASC, m.name ASC';

        return Db::getInstance()->executeS($sql) ?: [];
    }

    /**
     * Créer un modèle
     */
    public function create(array $data): int
    {
        $sql = 'INSERT INTO `' . _DB_PREFIX_ . $this->table . '`
                (id_manufacturer, name, year_start, year_end, active, position, date_add, date_upd)
                VALUES (
                    ' . (int)$data['id_manufacturer'] . ',
                    "' . pSQL($data['name']) . '",
                    ' . (!empty($data['year_start']) ? (int)$data['year_start'] : 'NULL') . ',
                    ' . (!empty($data['year_end']) ? (int)$data['year_end'] : 'NULL') . ',
                    ' . (!empty($data['active']) ? 1 : 0) . ',
                    ' . ((int)($data['position'] ?? 0)) . ',
                    NOW(),
                    NOW()
                )';

        if (Db::getInstance()->execute($sql)) {
            return Db::getInstance()->Insert_ID();
        }

        return 0;
    }

    /**
     * Mettre à jour un modèle
     */
    public function update(int $id, array $data): bool
    {
        $updates = [];

        if (isset($data['name'])) {
            $updates[] = 'name = "' . pSQL($data['name']) . '"';
        }
        if (isset($data['active'])) {
            $updates[] = 'active = ' . (int)$data['active'];
        }
        if (isset($data['position'])) {
            $updates[] = 'position = ' . (int)$data['position'];
        }

        if (empty($updates)) {
            return true;
        }

        $updates[] = 'date_upd = NOW()';

        $sql = 'UPDATE `' . _DB_PREFIX_ . $this->table . '`
                SET ' . implode(', ', $updates) . '
                WHERE id_do_vehicle_model = ' . (int)$id;

        return Db::getInstance()->execute($sql);
    }

    /**
     * Supprimer un modèle
     */
    public function delete(int $id): bool
    {
        $sql = 'DELETE FROM `' . _DB_PREFIX_ . $this->table . '`
                WHERE id_do_vehicle_model = ' . (int)$id;

        return Db::getInstance()->execute($sql);
    }

    /**
     * Nombre de produits compatibles avec un modèle
     */
    public function countCompatibleProducts(int $idModel): int
    {
        $sql = 'SELECT COUNT(DISTINCT vc.id_product) as count
                FROM `' . _DB_PREFIX_ . 'do_product_vehicle_compat` vc
                WHERE vc.id_do_vehicle_model = ' . (int)$idModel;

        $result = Db::getInstance()->getRow($sql);

        return (int)($result['count'] ?? 0);
    }
}
