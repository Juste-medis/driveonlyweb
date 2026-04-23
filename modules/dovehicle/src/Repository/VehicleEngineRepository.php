<?php
/**
 * VehicleEngineRepository
 * Motorisations (2.0 TDI 150ch, etc.)
 */

namespace DoVehicle\Repository;

use Db;

class VehicleEngineRepository
{
    private $table = 'do_vehicle_engine';

    /**
     * Récupérer toutes les motorisations d'un modèle
     */
    public function findByModel(int $idModel, bool $activeOnly = true): array
    {
        $sql = 'SELECT e.*
                FROM `' . _DB_PREFIX_ . $this->table . '` e
                WHERE e.id_do_vehicle_model = ' . (int)$idModel;

        if ($activeOnly) {
            $sql .= ' AND e.active = 1';
        }

        $sql .= ' ORDER BY e.position ASC, e.name ASC';

        return Db::getInstance()->executeS($sql) ?: [];
    }

    /**
     * Récupérer une motorisation par ID avec infos modèle/marque
     */
    public function findById(int $id): ?array
    {
        $sql = 'SELECT e.*, 
                       m.name AS model_name,
                       m.id_manufacturer,
                       man.name AS manufacturer_name
                FROM `' . _DB_PREFIX_ . $this->table . '` e
                LEFT JOIN `' . _DB_PREFIX_ . 'do_vehicle_model` m ON e.id_do_vehicle_model = m.id_do_vehicle_model
                LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` man ON m.id_manufacturer = man.id_manufacturer
                WHERE e.id_do_vehicle_engine = ' . (int)$id;

        return Db::getInstance()->getRow($sql) ?: null;
    }

    /**
     * Récupérer toutes les motorisations
     */
    public function findAll(): array
    {
        $sql = 'SELECT e.*,
                       m.name AS model_name,
                       man.name AS manufacturer_name
                FROM `' . _DB_PREFIX_ . $this->table . '` e
                LEFT JOIN `' . _DB_PREFIX_ . 'do_vehicle_model` m ON e.id_do_vehicle_model = m.id_do_vehicle_model
                LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` man ON m.id_manufacturer = man.id_manufacturer
                ORDER BY man.name ASC, m.name ASC, e.name ASC';

        return Db::getInstance()->executeS($sql) ?: [];
    }

    /**
     * Créer une motorisation
     */
    public function create(array $data): int
    {
        $sql = 'INSERT INTO `' . _DB_PREFIX_ . $this->table . '`
                (id_do_vehicle_model, name, year_start, year_end, active, position, date_add, date_upd)
                VALUES (
                    ' . (!empty($data['id_do_vehicle_model']) ? (int)$data['id_do_vehicle_model'] : 'NULL') . ',
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
     * Mettre à jour une motorisation
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
                WHERE id_do_vehicle_engine = ' . (int)$id;

        return Db::getInstance()->execute($sql);
    }

    /**
     * Supprimer une motorisation
     */
    public function delete(int $id): bool
    {
        $sql = 'DELETE FROM `' . _DB_PREFIX_ . $this->table . '`
                WHERE id_do_vehicle_engine = ' . (int)$id;

        return Db::getInstance()->execute($sql);
    }
}
