<?php
  if (!defined('_PS_VERSION_')){
    exit;
  }
  class Configuration extends ConfigurationCore
  {
    /*
    * module: simpleimportproduct
    * date: 2024-11-13 15:23:19
    * version: 6.7.5
    */
    public static function getGlobalValue($key, $id_lang = null)
    {
      if( strpos(Tools::strtolower($key), 'gomakoil') !== false || strpos(Tools::strtolower($key), 'mpm_') !== false ){
        $sql = 'SELECT `value`, `id_shop`, `id_shop_group` FROM `'._DB_PREFIX_.'configuration` WHERE `name` = "'.pSQL($key).'"';
        $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        foreach ( $res as $row ){
          if ( !$row['id_shop'] && !$row['id_shop_group'] ){
            return $row['value'];
          }
        }
        return false;
      }
      return parent::getGlobalValue($key, $id_lang = null);
    }
  }