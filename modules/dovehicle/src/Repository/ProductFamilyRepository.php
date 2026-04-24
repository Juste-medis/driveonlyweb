<?php
/**
 * ProductFamilyRepository
 * Familles produit (Accessoires, Échappement, etc.)
 */

namespace DoVehicle\Repository;

use Db;

class ProductFamilyRepository
{
    private $table = 'do_product_family';
    private $linkTable = 'do_product_family_link';

    /**
     * Récupérer toutes les familles (aplaties)
     */
    public function findAllFlat(bool $activeOnly = true): array
    {
        $sql = 'SELECT f.*, p.name AS parent_name
                FROM `' . _DB_PREFIX_ . $this->table . '` f
                LEFT JOIN `' . _DB_PREFIX_ . $this->table . '` p ON f.id_parent = p.id_do_product_family
                ';

        if ($activeOnly) {
            $sql .= ' WHERE f.active = 1';
        }

        $sql .= ' ORDER BY f.id_parent IS NULL DESC, f.position ASC';

        return Db::getInstance()->executeS($sql) ?: [];
    }

    /**
     * Récupérer une famille par ID
     */
    public function findById(int $id): ?array
    {
        $sql = 'SELECT f.*, p.name AS parent_name
                FROM `' . _DB_PREFIX_ . $this->table . '` f
                LEFT JOIN `' . _DB_PREFIX_ . $this->table . '` p ON f.id_parent = p.id_do_product_family
                WHERE f.id_do_product_family = ' . (int)$id;

        return Db::getInstance()->getRow($sql) ?: null;
    }

    /**
     * Récupérer les familles racines (niveau 4)
     */
    public function findRootFamilies(bool $activeOnly = true): array
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . $this->table . '`
                WHERE id_parent IS NULL';

        if ($activeOnly) {
            $sql .= ' AND active = 1';
        }

        $sql .= ' ORDER BY position ASC';

        return Db::getInstance()->executeS($sql) ?: [];
    }

    /**
     * Récupérer les sous-familles d'une famille
     */
    public function findChildren(int $idParent, bool $activeOnly = true): array
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . $this->table . '`
                WHERE id_parent = ' . (int)$idParent;

        if ($activeOnly) {
            $sql .= ' AND active = 1';
        }

        $sql .= ' ORDER BY position ASC';

        return Db::getInstance()->executeS($sql) ?: [];
    }

    /**
     * Récupérer une famille par slug
     */
    public function findBySlug(string $slug): ?array
    {
        $sql = 'SELECT f.*, p.name AS parent_name
                FROM `' . _DB_PREFIX_ . $this->table . '` f
                LEFT JOIN `' . _DB_PREFIX_ . $this->table . '` p ON f.id_parent = p.id_do_product_family
                WHERE f.name = "' . pSQL($slug) . '"';

        return Db::getInstance()->getRow($sql) ?: null;
    }

    /**
     * Récupérer les familles d'un produit
     */
    public function findFamiliesForProduct(int $idProduct): array
    {
        $sql = 'SELECT f.* FROM `' . _DB_PREFIX_ . $this->table . '` f
                INNER JOIN `' . _DB_PREFIX_ . $this->linkTable . '` l ON f.id_do_product_family = l.id_do_product_family
                WHERE l.id_product = ' . (int)$idProduct . '
                ORDER BY f.position ASC';

        return Db::getInstance()->executeS($sql) ?: [];
    }

    /**
     * Créer une famille
     */
    public function create(array $data): int
    {
        $sql = 'INSERT INTO `' . _DB_PREFIX_ . $this->table . '`
                (id_parent, name, active, position, date_add, date_upd)
                VALUES (
                    ' . (!empty($data['id_parent']) ? (int)$data['id_parent'] : 'NULL') . ',
                    "' . pSQL($data['name']) . '",
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
     * Mettre à jour une famille
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
                WHERE id_do_product_family = ' . (int)$id;

        return Db::getInstance()->execute($sql);
    }

    /**
     * Supprimer une famille
     */
    public function delete(int $id): bool
    {
        $sql = 'DELETE FROM `' . _DB_PREFIX_ . $this->table . '`
                WHERE id_do_product_family = ' . (int)$id;

        return Db::getInstance()->execute($sql);
    }

    /**
     * Lier un produit à des familles (remplace les liaisons existantes)
     */
    public function syncProductFamilies(int $idProduct, array $familyIds): bool
    {
        Db::getInstance()->delete(
            $this->linkTable, 
            'id_product = ' . (int)$idProduct
        );



        // Ajouter les nouvelles liaisons
        foreach (array_unique($familyIds) as $idFamily) {
            $idFamily = (int)$idFamily;
            if ($idFamily <= 0) {
                continue;
            }

            $sql = 'INSERT INTO `' . _DB_PREFIX_ . $this->linkTable . '`
                    (id_product, id_do_product_family, date_add)
                    VALUES (' . (int)$idProduct . ', ' . $idFamily . ', NOW())';

            Db::getInstance()->execute($sql);
        }

        return true;
    }
}
