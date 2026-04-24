<?php

if (!function_exists('myprint')) {
    function myprint($value, $erase = false): void
    {
        $logDir = _PS_ROOT_DIR_ . '/var/logs';
        $logFile = $logDir . '/myprint.log';

        if (!is_dir($logDir)) {
            mkdir($logDir, 0775, true);
        }

        $content = is_scalar($value) || $value === null
            ? (string) $value
            : print_r($value, true);

        $line = sprintf("[%s] %s%s", date('Y-m-d H:i:s'), $content, PHP_EOL);

        if ($erase === true) {
            file_put_contents($logFile, $line, LOCK_EX);
        } else {
            file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
        }
    }
}