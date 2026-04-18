<?php
if (!defined('_PS_VERSION_')) { exit; }
class AdminManufacturersController extends AdminManufacturersControllerCore
{
    /*
    * module: ets_imagecompressor
    * date: 2025-07-10 14:56:21
    * version: 2.2.5
    */
    protected function afterImageUpload()
    {
        parent::afterImageUpload();
        if(Module::isInstalled('ets_imagecompressor') && Module::isEnabled('ets_imagecompressor') && $ets_imagecompressor= Module::getInstanceByName('ets_imagecompressor'))
        {
            $id_manufacturer = (int)Tools::getValue('id_manufacturer');
            if($id_manufacturer)
                Ets_imagecompressor_optimize::optimizeManufacturerImage($id_manufacturer);
        }
        
    }
}