-- ============================================================
-- MODULE DOVEHICLE — install.sql
-- PrestaShop 1.7.8.7
-- ============================================================

-- ─────────────────────────────────────────────
-- 1. MODÈLES DE VÉHICULE
--    Lié à ps_manufacturer (marque) via id_manufacturer
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `PREFIX_do_vehicle_model` (
    `id_do_vehicle_model`   INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_manufacturer`       INT(10) UNSIGNED NOT NULL,          -- FK vers ps_manufacturer
    `name`                  VARCHAR(128)     NOT NULL,
    `year_start`            SMALLINT(4)      UNSIGNED DEFAULT NULL,
    `year_end`              SMALLINT(4)      UNSIGNED DEFAULT NULL,
    `active`                TINYINT(1)       NOT NULL DEFAULT 1,
    `position`              INT(10)          UNSIGNED NOT NULL DEFAULT 0,
    `date_add`              DATETIME         NOT NULL,
    `date_upd`              DATETIME         NOT NULL,
    PRIMARY KEY (`id_do_vehicle_model`),
    KEY `idx_manufacturer`  (`id_manufacturer`),
    KEY `idx_active`        (`active`),
    CONSTRAINT `fk_dvm_manufacturer`
        FOREIGN KEY (`id_manufacturer`)
        REFERENCES `PREFIX_manufacturer` (`id_manufacturer`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- 2. MOTORISATIONS
--    Liée au modèle via id_do_vehicle_model
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `PREFIX_do_vehicle_engine` (
    `id_do_vehicle_engine`  INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_do_vehicle_model`   INT(10) UNSIGNED DEFAULT NULL,       -- FK vers do_vehicle_model (peut être null pour motorisation générique)
    `name`                  VARCHAR(128)     NOT NULL,           -- ex: "2.0 TDI 150ch"
    `year_start`            SMALLINT(4)      UNSIGNED DEFAULT NULL,
    `year_end`              SMALLINT(4)      UNSIGNED DEFAULT NULL,
    `active`                TINYINT(1)       NOT NULL DEFAULT 1,
    `position`              INT(10)          UNSIGNED NOT NULL DEFAULT 0,
    `date_add`              DATETIME         NOT NULL,
    `date_upd`              DATETIME         NOT NULL,
    PRIMARY KEY (`id_do_vehicle_engine`),
    KEY `idx_model`         (`id_do_vehicle_model`),
    KEY `idx_active`        (`active`),
    CONSTRAINT `fk_dve_model`
        FOREIGN KEY (`id_do_vehicle_model`)
        REFERENCES `PREFIX_do_vehicle_model` (`id_do_vehicle_model`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- 3. FAMILLES PRODUIT (multi-niveaux via parent_id)
--    Niveau 4 = Famille, Niveau 5 = Sous-famille (parent_id non null)
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `PREFIX_do_product_family` (
    `id_do_product_family`  INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_parent`             INT(10) UNSIGNED DEFAULT NULL,       -- NULL = famille racine
    `name`                  VARCHAR(128)     NOT NULL,
    `active`                TINYINT(1)       NOT NULL DEFAULT 1,
    `position`              INT(10)          UNSIGNED NOT NULL DEFAULT 0,
    `date_add`              DATETIME         NOT NULL,
    `date_upd`              DATETIME         NOT NULL,
    PRIMARY KEY (`id_do_product_family`),
    KEY `idx_parent`        (`id_parent`),
    KEY `idx_active`        (`active`),
    CONSTRAINT `fk_dpf_parent`
        FOREIGN KEY (`id_parent`)
        REFERENCES `PREFIX_do_product_family` (`id_do_product_family`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- 4. TABLE DE LIAISON : Produit <> Compatibilité véhicule
--    Un produit peut être lié à PLUSIEURS motorisations
--    On peut aussi lier à marque seule ou modèle seul (champs nullable)
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `PREFIX_do_product_vehicle_compat` (
    `id_compat`             INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_product`            INT(10) UNSIGNED NOT NULL,
    `id_manufacturer`       INT(10) UNSIGNED DEFAULT NULL,       -- marque seule possible
    `id_do_vehicle_model`   INT(10) UNSIGNED DEFAULT NULL,       -- modèle seul possible
    `id_do_vehicle_engine`  INT(10) UNSIGNED DEFAULT NULL,       -- motorisation précise
    `note`                  VARCHAR(255)     DEFAULT NULL,       -- ex: "Uniquement coupé"
    `date_add`              DATETIME         NOT NULL,
    PRIMARY KEY (`id_compat`),
    KEY `idx_product`               (`id_product`),
    KEY `idx_manufacturer`          (`id_manufacturer`),
    KEY `idx_model`                 (`id_do_vehicle_model`),
    KEY `idx_engine`                (`id_do_vehicle_engine`),
    -- Empêcher les doublons exacts
    UNIQUE KEY `uq_product_compat`  (`id_product`,`id_manufacturer`,`id_do_vehicle_model`,`id_do_vehicle_engine`),
    CONSTRAINT `fk_dpvc_product`
        FOREIGN KEY (`id_product`)
        REFERENCES `PREFIX_product` (`id_product`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_dpvc_manufacturer`
        FOREIGN KEY (`id_manufacturer`)
        REFERENCES `PREFIX_manufacturer` (`id_manufacturer`)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_dpvc_model`
        FOREIGN KEY (`id_do_vehicle_model`)
        REFERENCES `PREFIX_do_vehicle_model` (`id_do_vehicle_model`)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_dpvc_engine`
        FOREIGN KEY (`id_do_vehicle_engine`)
        REFERENCES `PREFIX_do_vehicle_engine` (`id_do_vehicle_engine`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- 5. TABLE DE LIAISON : Produit <> Famille/Sous-famille
--    Un produit peut appartenir à PLUSIEURS familles
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `PREFIX_do_product_family_link` (
    `id_product`            INT(10) UNSIGNED NOT NULL,
    `id_do_product_family`  INT(10) UNSIGNED NOT NULL,
    `date_add`              DATETIME         NOT NULL,
    PRIMARY KEY (`id_product`, `id_do_product_family`),
    KEY `idx_family`        (`id_do_product_family`),
    CONSTRAINT `fk_dpfl_product`
        FOREIGN KEY (`id_product`)
        REFERENCES `PREFIX_product` (`id_product`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_dpfl_family`
        FOREIGN KEY (`id_do_product_family`)
        REFERENCES `PREFIX_do_product_family` (`id_do_product_family`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- 6. DONNÉES DE DÉMO — Familles produit
-- ─────────────────────────────────────────────
INSERT INTO `PREFIX_do_product_family`
    (`id_parent`, `name`, `active`, `position`, `date_add`, `date_upd`)
VALUES
    (NULL, 'Accessoires',                1, 1, NOW(), NOW()),
    (NULL, 'Look & Covering',          1, 2, NOW(), NOW()),
    (NULL, 'Carrosserie',                1, 3, NOW(), NOW()),
    (NULL, 'Échappement',           1, 4, NOW(), NOW()),
    (NULL, 'Éclairage',                 1, 5, NOW(), NOW()),
    (NULL, 'Liaison au sol',      1, 6, NOW(), NOW()),
    (NULL, 'Moteur',      1, 7, NOW(), NOW());

-- Sous-familles Échappement (id_parent = 4)
INSERT INTO `PREFIX_do_product_family`
    (`id_parent`, `name`, `active`, `position`, `date_add`, `date_upd`)
VALUES
    (4, 'Kit complet',          1, 1, NOW(), NOW()),
    (4, 'Ligne cat-back',       1, 2, NOW(), NOW()),
    (4, 'Silencieux arrière',   1, 3, NOW(), NOW()),
    (4, 'Downpipe',             1, 4, NOW(), NOW());

-- Sous-familles Liaison au sol (id_parent = 6)
INSERT INTO `PREFIX_do_product_family`
    (`id_parent`, `name`, `active`, `position`, `date_add`, `date_upd`)
VALUES
    (6, 'Ressorts',             1, 1, NOW(), NOW()),
    (6, 'Amortisseurs',         1, 2, NOW(), NOW()),
    (6, 'Combinés filetés',     1, 3, NOW(), NOW()),
    (6, 'Barres stabilisatrices',  1, 4, NOW(), NOW());
