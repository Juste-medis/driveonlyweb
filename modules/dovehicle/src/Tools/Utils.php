<?php

namespace DoVehicle\Tools;

class Utils
{
    public static function log($value): void
    {
        $logDir = _PS_ROOT_DIR_ . '/modules/dovehicle';
        $logFile = $logDir . '/myprint.log';

        if (!is_dir($logDir)) {
            mkdir($logDir, 0775, true);
        }

        $content = is_scalar($value) || $value === null
            ? (string) $value
            : print_r($value, true);

        $line = sprintf("[%s] %s%s", date('Y-m-d H:i:s'), $content, PHP_EOL);

        file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
    }
}


        // $sql = new DbQuery();
        // $sql->select('id_tab, id_parent, class_name, position')
        //     ->from('tab')
        //     ->orderBy('id_parent ASC, position ASC');

        // $tabs = Db::getInstance()->executeS($sql);

        // Utils::log($tabs);