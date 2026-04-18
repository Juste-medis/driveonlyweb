<?php

// 24.04.19 - Webbax | TUTO 75 - SPAM compte client
class Validate extends ValidateCore
{
    public static function isCustomerName($name)
    {
        if (preg_match(Tools::cleanNonUnicodeSupport('/www|http/ui'),$name))
           return false;

        return preg_match(Tools::cleanNonUnicodeSupport('/^[^0-9!\[\]<>,;?=+()@#"°{}_$%:\/\\\*\^]*$/u'), $name);
    }
}