<?php

/**
 * Repository pour récupérer les attributs et caractéristiques d'une catégorie
 * 
 * Optimisé pour les requêtes de catégories avec beaucoup de produits
 * N'utilise que les produits existants (LIMIT pas nécessaire)
 */

namespace DoVehicle\Repository;

use Db;
use DoVehicle\Tools\Utils;

class CategoryFiltersRepository
{
    private const DB_PREFIX = _DB_PREFIX_;

    /**
     * Récupère tous les attributs disponibles pour les produits d'une catégorie
     * (méthode legacy - utiliser getAttributesWithValuesByCategory pour plus de détails)
     *
     * @param int $idCategory ID de la catégorie
     * @param int $idLang ID de la langue
     * @return array
     */
    public function getAttributesByCategory(int $idCategory, int $idLang): array
    {
        $sql = 'SELECT DISTINCT ag.`id_attribute_group`, ag.`position`, agl.`name`
                FROM `' . self::DB_PREFIX . 'product` p
                INNER JOIN `' . self::DB_PREFIX . 'category_product` cp ON p.`id_product` = cp.`id_product`
                INNER JOIN `' . self::DB_PREFIX . 'product_attribute` pa ON p.`id_product` = pa.`id_product`
                INNER JOIN `' . self::DB_PREFIX . 'attribute` a ON pa.`id_attribute` = a.`id_attribute`
                INNER JOIN `' . self::DB_PREFIX . 'attribute_group` ag ON a.`id_attribute_group` = ag.`id_attribute_group`
                INNER JOIN `' . self::DB_PREFIX . 'attribute_group_lang` agl ON ag.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = ' . (int)$idLang . '
                WHERE cp.`id_category` = ' . (int)$idCategory . '
                AND p.`active` = 1
                ORDER BY ag.`position` ASC';

        return Db::getInstance()->executeS($sql) ?: [];
    }

    /**
     * Récupère un résumé complet des filtres disponibles pour une catégorie
     * Utilise GROUP_CONCAT pour performance optimale
     *
     * @param int $idCategory ID de la catégorie
     * @param int $idLang ID de la langue
     * @param int $idShop ID de la boutique
     * @return array Structure avec caracteristiques, declinaisons, proprietes, nb_produits
     */
    public function getCategoryFiltersSummary(int $idCategory, int $idLang, int $idShop = 1): array
{
    $db = Db::getInstance();
    $ps = _DB_PREFIX_;

    $idCategory = (int) $idCategory;
    $idLang = (int) $idLang;
    $idShop = (int) $idShop; 
     
    // SET SESSION ne passe pas par executeS() — utiliser execute() (pas de résultat attendu)
    $db->execute('SET SESSION group_concat_max_len = 1000000');
    $ps = self::DB_PREFIX;
 
$sql = '
    WITH RECURSIVE category_branch AS (
        SELECT c.id_category
        FROM `' . $ps . 'category` c
        WHERE c.id_category = ' . $idCategory . '
          AND c.active = 1

        UNION ALL

        SELECT c.id_category
        FROM `' . $ps . 'category` c
        INNER JOIN category_branch cb
            ON cb.id_category = c.id_parent
        WHERE c.active = 1
    ),

    product_scope AS (
        SELECT DISTINCT cp.id_product
        FROM `' . $ps . 'category_product` cp
        INNER JOIN category_branch cb
            ON cb.id_category = cp.id_category
        INNER JOIN `' . $ps . 'product` p
            ON p.id_product = cp.id_product
    ),

    features_counts AS (
        SELECT
            fp.id_feature_value,
            COUNT(DISTINCT fp.id_product) AS product_count
        FROM product_scope ps
        INNER JOIN `' . $ps . 'feature_product` fp
            ON fp.id_product = ps.id_product
        GROUP BY fp.id_feature_value
    ),

    features_distinct AS (
        SELECT DISTINCT
            fp.id_feature,
            fl.name AS feature_name,
            fp.id_feature_value,
            fvl.value AS feature_value
        FROM product_scope ps
        INNER JOIN `' . $ps . 'feature_product` fp
            ON fp.id_product = ps.id_product
        INNER JOIN `' . $ps . 'feature_lang` fl
            ON fl.id_feature = fp.id_feature
           AND fl.id_lang = ' . $idLang . '
        INNER JOIN `' . $ps . 'feature_value_lang` fvl
            ON fvl.id_feature_value = fp.id_feature_value
           AND fvl.id_lang = ' . $idLang . '
    ),

    features_unique_values AS (
        SELECT
            fd.id_feature,
            fd.feature_name,
            MIN(fd.id_feature_value) AS id_feature_value,
            fd.feature_value
        FROM features_distinct fd
        GROUP BY
            fd.id_feature,
            fd.feature_name,
            fd.feature_value
    ),

    features_grouped AS (
        SELECT
            fuv.id_feature,
            fuv.feature_name,
            CONCAT(
                \'[\',
                GROUP_CONCAT(
                    CONCAT(
                        \'{"id_feature_value":\', fuv.id_feature_value,
                        \',"value":\', JSON_QUOTE(fuv.feature_value),
                        \',"count":\', COALESCE(fc.product_count, 0),
                        \'}\'
                    )
                    ORDER BY fuv.feature_value
                    SEPARATOR \',\'
                ),
                \']\'
            ) AS feature_values
        FROM features_unique_values fuv
        LEFT JOIN features_counts fc
            ON fc.id_feature_value = fuv.id_feature_value
        GROUP BY
            fuv.id_feature,
            fuv.feature_name
    ),

    declinations_counts AS (
        SELECT
            pac.id_attribute,
            COUNT(DISTINCT pa.id_product) AS product_count
        FROM product_scope ps
        INNER JOIN `' . $ps . 'product_attribute` pa
            ON pa.id_product = ps.id_product
        INNER JOIN `' . $ps . 'product_attribute_combination` pac
            ON pac.id_product_attribute = pa.id_product_attribute
        GROUP BY pac.id_attribute
    ),

    declinations_distinct AS (
        SELECT DISTINCT
            a.id_attribute_group,
            agl.name AS attribute_group_name,
            a.id_attribute,
            al.name AS attribute_name
        FROM product_scope ps
        INNER JOIN `' . $ps . 'product_attribute` pa
            ON pa.id_product = ps.id_product
        INNER JOIN `' . $ps . 'product_attribute_combination` pac
            ON pac.id_product_attribute = pa.id_product_attribute
        INNER JOIN `' . $ps . 'attribute` a
            ON a.id_attribute = pac.id_attribute
        INNER JOIN `' . $ps . 'attribute_lang` al
            ON al.id_attribute = a.id_attribute
           AND al.id_lang = ' . $idLang . '
        INNER JOIN `' . $ps . 'attribute_group_lang` agl
            ON agl.id_attribute_group = a.id_attribute_group
           AND agl.id_lang = ' . $idLang . '
    ),

    declinations_unique_values AS (
        SELECT
            dd.id_attribute_group,
            dd.attribute_group_name,
            MIN(dd.id_attribute) AS id_attribute,
            dd.attribute_name
        FROM declinations_distinct dd
        GROUP BY
            dd.id_attribute_group,
            dd.attribute_group_name,
            dd.attribute_name
    ),

    declinations_grouped AS (
        SELECT
            duv.id_attribute_group,
            duv.attribute_group_name,
            CONCAT(
                \'[\',
                GROUP_CONCAT(
                    CONCAT(
                        \'{"id_attribute":\', duv.id_attribute,
                        \',"value":\', JSON_QUOTE(duv.attribute_name),
                        \',"count":\', COALESCE(dc.product_count, 0),
                        \'}\'
                    )
                    ORDER BY duv.attribute_name
                    SEPARATOR \',\'
                ),
                \']\'
            ) AS attribute_values
        FROM declinations_unique_values duv
        LEFT JOIN declinations_counts dc
            ON dc.id_attribute = duv.id_attribute
        GROUP BY
            duv.id_attribute_group,
            duv.attribute_group_name
    )

    SELECT
        ' . $idCategory . ' AS id_category,
        COALESCE(
            (
                SELECT CONCAT(
                    \'[\',
                    GROUP_CONCAT(
                        CONCAT(
                            \'{"id_feature":\', fg.id_feature,
                            \',"feature_name":\', JSON_QUOTE(fg.feature_name),
                            \',"feature_values":\', fg.feature_values,
                            \'}\'
                        )
                        ORDER BY fg.feature_name
                        SEPARATOR \',\'
                    ),
                    \']\'
                )
                FROM features_grouped fg
            ),
            \'[]\'
        ) AS caracteristiques,
        COALESCE(
            (
                SELECT CONCAT(
                    \'[\',
                    GROUP_CONCAT(
                        CONCAT(
                            \'{"id_attribute_group":\', dg.id_attribute_group,
                            \',"attribute_group_name":\', JSON_QUOTE(dg.attribute_group_name),
                            \',"attribute_values":\', dg.attribute_values,
                            \'}\'
                        )
                        ORDER BY dg.attribute_group_name
                        SEPARATOR \',\'
                    ),
                    \']\'
                )
                FROM declinations_grouped dg
            ),
            \'[]\'
        ) AS declinaisons
'; 
                $resource = Db::getInstance()->query($sql); 
    if (!$resource) {
        return [];
    }
    $results = $resource->fetchAll();
    $row = $results[0];

    $row['id_category'] = (int) $row['id_category'];
    $row['caracteristiques'] = !empty($row['caracteristiques']) ? json_decode($row['caracteristiques'], true) : [];
    $row['declinaisons'] = !empty($row['declinaisons']) ? json_decode($row['declinaisons'], true) : [];
  
    return $row;
}

    /**
     * Récupère tous les attributs avec leurs valeurs pour une catégorie
     *
     * @param int $idCategory ID de la catégorie
     * @param int $idLang ID de la langue
     * @return array
     */
    public function getAttributesWithValuesByCategory(int $idCategory, int $idLang): array
    {

    //get filter summary 
    $summary = $this->getCategoryFiltersSummary($idCategory, $idLang);
 
        
$results = $summary['declinaisons'] ?? [];
 
        // Grouper par groupe d'attributs
        $grouped = [];
        foreach ($results as $row) {
            $groupId = $row['id_attribute_group'];
            if (!isset($grouped[$groupId])) {
                $grouped[$groupId] = [
                    'id_attribute_group' => $groupId,
                    'group_name' => $row['attribute_group_name'],
                    'attributes' => [],
                ];
            }
            foreach ($row['attribute_values'] as $attr) {
                $grouped[$groupId]['attributes'][] = [
                    'id_attribute' => $attr['id_attribute'],
                    'value' => $attr['value'],
                    'count' => $attr['count'],
                ];
            }
            
        }

        return array_values($grouped);
    }

    /**
     * Récupère toutes les caractéristiques disponibles pour les produits d'une catégorie
     *
     * @param int $idCategory ID de la catégorie
     * @param int $idLang ID de la langue
     * @return array
     */
    public function getFeaturesByCategory(int $idCategory, int $idLang): array
    { 
        $summary = $this->getCategoryFiltersSummary($idCategory, $idLang);

        $results = $summary['caracteristiques'] ?? [];
        // Grouper par feature
        $grouped = [];
        foreach ($results as $row) {
            $featureId = $row['id_feature'];
            if (!isset($grouped[$featureId])) {
                $grouped[$featureId] = [
                    'id_feature' => $featureId,
                    'feature_name' => $row['feature_name'],
                     'values' => [],
                ];
            }
            foreach ($row['feature_values'] as $value) {
                $grouped[$featureId]['values'][] = [
                    'id_feature_value' => $value['id_feature_value'],
                    'value' => $value['value'],
                    'count' => $value['count'],
                ];
            }
        }

        return array_values($grouped);
    }

    /**
     * Récupère les produits filtrés par attributs et caractéristiques
     *
     * @param int $idCategory ID de la catégorie
     * @param array $attributeIds Tableau des IDs d'attributs sélectionnés
     * @param array $featureValueIds Tableau des IDs de valeurs de features sélectionnées
     * @param int $limit Nombre max de produits
     * @param int $offset Offset pour la pagination
     * @return array
     */
    public function getFilteredProducts(
        int $idCategory,
        array $attributeIds = [],
        array $featureValueIds = [],
        int $limit = 20,
        int $offset = 0
    ): array {
        $query = 'SELECT DISTINCT p.`id_product`
                  FROM `' . self::DB_PREFIX . 'product` p
                  INNER JOIN `' . self::DB_PREFIX . 'category_product` cp ON p.`id_product` = cp.`id_product`
                  WHERE cp.`id_category` = ' . (int)$idCategory . '
                  AND p.`active` = 1';

        // Ajouter le filtre par attributs si présent
        if (!empty($attributeIds)) {
            $attributeIds = array_map('intval', $attributeIds);
            $query .= ' AND p.`id_product` IN (
                        SELECT DISTINCT pa.`id_product`
                        FROM `' . self::DB_PREFIX . 'product_attribute` pa
                        WHERE pa.`id_attribute` IN (' . implode(',', $attributeIds) . ')
                    )';
        }

        // Ajouter le filtre par features si présent
        if (!empty($featureValueIds)) {
            $featureValueIds = array_map('intval', $featureValueIds);
            $query .= ' AND p.`id_product` IN (
                        SELECT DISTINCT fp.`id_product`
                        FROM `' . self::DB_PREFIX . 'feature_product` fp
                        WHERE fp.`id_feature_value` IN (' . implode(',', $featureValueIds) . ')
                    )';
        }

        // Ajouter limit et offset
        if ($limit > 0) {
            $query .= ' LIMIT ' . (int)$limit . ' OFFSET ' . (int)$offset;
        }

        return Db::getInstance()->executeS($query) ?: [];
    }

    /**
     * Récupère les déclinaisons disponibles pour une catégorie (groupe d'attributs et valeurs)
     * Utilise GROUP_CONCAT pour performance optimale
     *
     * @param int $idCategory ID de la catégorie
     * @param int $idLang ID de la langue
     * @param int $idShop ID de la boutique
     * @return array Tableau avec "declinaisons" et autres métadonnées
     */
    public function getDeclinaisonsSummary(int $idCategory, int $idLang, int $idShop = 1): array
    {
        Db::getInstance()->execute('SET SESSION group_concat_max_len = 1000000');

        $sql = 'WITH product_scope AS (
                    SELECT DISTINCT
                        p.id_product
                    FROM `' . self::DB_PREFIX . 'category_product` cp
                    JOIN `' . self::DB_PREFIX . 'product` p
                        ON p.id_product = cp.id_product
                        AND p.active = 1
                    WHERE cp.id_category = ' . (int)$idCategory . '
                )
                SELECT
                    GROUP_CONCAT(
                        DISTINCT CONCAT(agl.name, \' = \', al.name)
                        ORDER BY agl.name, al.name
                        SEPARATOR \' || \'
                    ) AS declinaisons,
                    COUNT(DISTINCT ps.id_product) AS nb_produits_with_variants
                FROM product_scope ps
                JOIN `' . self::DB_PREFIX . 'product_attribute` pa
                    ON pa.id_product = ps.id_product
                JOIN `' . self::DB_PREFIX . 'product_attribute_combination` pac
                    ON pac.id_product_attribute = pa.id_product_attribute
                JOIN `' . self::DB_PREFIX . 'attribute` a
                    ON a.id_attribute = pac.id_attribute
                JOIN `' . self::DB_PREFIX . 'attribute_lang` al
                    ON al.id_attribute = a.id_attribute
                   AND al.id_lang = ' . (int)$idLang . '
                JOIN `' . self::DB_PREFIX . 'attribute_group_lang` agl
                    ON agl.id_attribute_group = a.id_attribute_group
                   AND agl.id_lang = ' . (int)$idLang;

        $result = Db::getInstance()->executeS($sql);
        return $result ? $result[0] : [];
    }

    /**
     * Récupère les caractéristiques disponibles pour une catégorie
     * Utilise GROUP_CONCAT pour performance optimale
     *
     * @param int $idCategory ID de la catégorie
     * @param int $idLang ID de la langue
     * @param int $idShop ID de la boutique
     * @return array Tableau avec "caracteristiques" et autres métadonnées
     */
    public function getCaracteristiquesSummary(int $idCategory, int $idLang, int $idShop = 1): array
    {
        Db::getInstance()->execute('SET SESSION group_concat_max_len = 1000000');

        $sql = 'WITH product_scope AS (
                    SELECT DISTINCT
                        p.id_product
                    FROM `' . self::DB_PREFIX . 'category_product` cp
                    JOIN `' . self::DB_PREFIX . 'product` p
                        ON p.id_product = cp.id_product
                        AND p.active = 1
                    WHERE cp.id_category = ' . (int)$idCategory . '
                )
                SELECT
                    GROUP_CONCAT(
                        DISTINCT CONCAT(fl.name, \' = \', fvl.value)
                        ORDER BY fl.name, fvl.value
                        SEPARATOR \' || \'
                    ) AS caracteristiques,
                    COUNT(DISTINCT ps.id_product) AS nb_produits_with_features
                FROM product_scope ps
                JOIN `' . self::DB_PREFIX . 'feature_product` fp
                    ON fp.id_product = ps.id_product
                JOIN `' . self::DB_PREFIX . 'feature_lang` fl
                    ON fl.id_feature = fp.id_feature
                   AND fl.id_lang = ' . (int)$idLang . '
                JOIN `' . self::DB_PREFIX . 'feature_value_lang` fvl
                    ON fvl.id_feature_value = fp.id_feature_value
                   AND fvl.id_lang = ' . (int)$idLang;

        $result = Db::getInstance()->executeS($sql);
        return $result ? $result[0] : [];
    }

    /**
     * Récupère les propriétés uniques disponibles pour une catégorie
     * (Fabricants, Références, Conditions)
     *
     * @param int $idCategory ID de la catégorie
     * @param int $idLang ID de la langue
     * @return array
     */
    public function getPropertiesSummary(int $idCategory, int $idLang): array
    {
        Db::getInstance()->execute('SET SESSION group_concat_max_len = 1000000');

        $sql = 'WITH product_scope AS (
                    SELECT DISTINCT
                        p.id_product
                    FROM `' . self::DB_PREFIX . 'category_product` cp
                    JOIN `' . self::DB_PREFIX . 'product` p
                        ON p.id_product = cp.id_product
                        AND p.active = 1
                    WHERE cp.id_category = ' . (int)$idCategory . '
                )
                SELECT
                    GROUP_CONCAT(
                        DISTINCT COALESCE(m.name, \'N/A\')
                        ORDER BY m.name
                        SEPARATOR \' || \'
                    ) AS fabricants,
                    GROUP_CONCAT(
                        DISTINCT COALESCE(NULLIF(p.reference, \'\'), \'N/A\')
                        ORDER BY p.reference
                        SEPARATOR \' || \'
                    ) AS references,
                    GROUP_CONCAT(
                        DISTINCT COALESCE(NULLIF(p.condition, \'\'), \'N/A\')
                        ORDER BY p.condition
                        SEPARATOR \' || \'
                    ) AS conditions
                FROM product_scope ps
                JOIN `' . self::DB_PREFIX . 'product` p
                    ON p.id_product = ps.id_product
                LEFT JOIN `' . self::DB_PREFIX . 'manufacturer` m
                    ON m.id_manufacturer = p.id_manufacturer';

        $result = Db::getInstance()->executeS($sql);
        return $result ? $result[0] : [];
    }

    /**
     * Compte le nombre total de produits dans une catégorie
     *
     * @param int $idCategory ID de la catégorie
     * @return int
     */
    public function getCategoryProductCount(int $idCategory): int
    {
        $sql = 'SELECT COUNT(DISTINCT p.id_product) as total
                FROM `' . self::DB_PREFIX . 'category_product` cp
                JOIN `' . self::DB_PREFIX . 'product` p
                    ON p.id_product = cp.id_product
                    AND p.active = 1
                WHERE cp.id_category = ' . (int)$idCategory;

        $result = Db::getInstance()->executeS($sql);
        return $result ? (int)$result[0]['total'] : 0;
    }

    /**
     * Récupère des statistiques détaillées sur les attributs d'une catégorie
     *
     * @param int $idCategory ID de la catégorie
     * @param int $idLang ID de la langue
     * @return array Statistiques par groupe d'attribut
     */
    public function getAttributeStats(int $idCategory, int $idLang): array
    {
        $sql = 'SELECT
                    ag.id_attribute_group,
                    agl.name as group_name,
                    COUNT(DISTINCT a.id_attribute) as nb_values,
                    COUNT(DISTINCT pac.id_product_attribute) as nb_variants,
                    GROUP_CONCAT(DISTINCT al.name ORDER BY al.name SEPARATOR \', \') as values_list
                FROM `' . self::DB_PREFIX . 'product` p
                INNER JOIN `' . self::DB_PREFIX . 'category_product` cp ON p.id_product = cp.id_product
                INNER JOIN `' . self::DB_PREFIX . 'product_attribute` pa ON p.id_product = pa.id_product
                INNER JOIN `' . self::DB_PREFIX . 'product_attribute_combination` pac ON pa.id_product_attribute = pac.id_product_attribute
                INNER JOIN `' . self::DB_PREFIX . 'attribute` a ON pac.id_attribute = a.id_attribute
                INNER JOIN `' . self::DB_PREFIX . 'attribute_lang` al ON a.id_attribute = al.id_attribute AND al.id_lang = ' . (int)$idLang . '
                INNER JOIN `' . self::DB_PREFIX . 'attribute_group` ag ON a.id_attribute_group = ag.id_attribute_group
                INNER JOIN `' . self::DB_PREFIX . 'attribute_group_lang` agl ON ag.id_attribute_group = agl.id_attribute_group AND agl.id_lang = ' . (int)$idLang . '
                WHERE cp.id_category = ' . (int)$idCategory . '
                AND p.active = 1
                GROUP BY ag.id_attribute_group, agl.name
                ORDER BY ag.position ASC';

        return Db::getInstance()->executeS($sql) ?: [];
    }

    /**
     * Récupère des statistiques détaillées sur les caractéristiques d'une catégorie
     *
     * @param int $idCategory ID de la catégorie
     * @param int $idLang ID de la langue
     * @return array Statistiques par feature
     */
    public function getFeatureStats(int $idCategory, int $idLang): array
    {
        $sql = 'SELECT
                    f.id_feature,
                    fl.name as feature_name,
                    COUNT(DISTINCT fv.id_feature_value) as nb_values,
                    COUNT(DISTINCT p.id_product) as nb_products_with_this_feature,
                    GROUP_CONCAT(DISTINCT fvl.value ORDER BY fvl.value SEPARATOR \', \') as values_list
                FROM `' . self::DB_PREFIX . 'product` p
                INNER JOIN `' . self::DB_PREFIX . 'category_product` cp ON p.id_product = cp.id_product
                INNER JOIN `' . self::DB_PREFIX . 'feature_product` fp ON p.id_product = fp.id_product
                INNER JOIN `' . self::DB_PREFIX . 'feature` f ON fp.id_feature = f.id_feature
                INNER JOIN `' . self::DB_PREFIX . 'feature_lang` fl ON f.id_feature = fl.id_feature AND fl.id_lang = ' . (int)$idLang . '
                INNER JOIN `' . self::DB_PREFIX . 'feature_value` fv ON fp.id_feature_value = fv.id_feature_value
                INNER JOIN `' . self::DB_PREFIX . 'feature_value_lang` fvl ON fv.id_feature_value = fvl.id_feature_value AND fvl.id_lang = ' . (int)$idLang . '
                WHERE cp.id_category = ' . (int)$idCategory . '
                AND p.active = 1
                GROUP BY f.id_feature, fl.name
                ORDER BY f.position ASC';

        return Db::getInstance()->executeS($sql) ?: [];
    }
}
