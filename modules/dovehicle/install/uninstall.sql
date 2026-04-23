-- ============================================================
-- MODULE DOVEHICLE — uninstall.sql
-- Supprime les tables dans l'ordre inverse (respect FK)
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `PREFIX_do_product_family_link`;
DROP TABLE IF EXISTS `PREFIX_do_product_vehicle_compat`;
DROP TABLE IF EXISTS `PREFIX_do_vehicle_engine`;
DROP TABLE IF EXISTS `PREFIX_do_vehicle_model`;
DROP TABLE IF EXISTS `PREFIX_do_product_family`;

SET FOREIGN_KEY_CHECKS = 1;
