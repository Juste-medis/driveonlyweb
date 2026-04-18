<?php
if (!defined('_PS_VERSION_')) { exit; }
class AdminSuppliersController extends AdminSuppliersControllerCore
{
    /*
    * module: ets_imagecompressor
    * date: 2025-07-10 14:56:21
    * version: 2.2.5
    */
    protected function afterImageUpload()
    {
        parent::afterImageUpload();
        if(Module::isInstalled('ets_imagecompressor') && Module::isEnabled('ets_imagecompressor'))
        {
            $id_supplier = (int)Tools::getValue('id_supplier');
            if($id_supplier)
                Ets_imagecompressor_optimize::optimizeImageSupplier($id_supplier);
        }
    }
}