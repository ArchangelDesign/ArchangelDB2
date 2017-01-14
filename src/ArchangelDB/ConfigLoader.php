<?php
/**
 * Archangel DB 2
 * www.archangel-design.com
 * @author Rafal Martinez-Marjanski
 */

namespace ArchangelDB;

use Zend\Db\Exception\ErrorException;

class ConfigLoader {
    const CONFIG_FILE = "adbConfig.php";
    const PATH_SEPARATOR = "/";

    /**
     * @param array|null $configArray
     *
     * @return array|mixed
     * @throws ErrorException
     */
    public static function getConfig($configArray = null)
    {
        $moduleRoot = dirname(dirname(__DIR__)) . self::PATH_SEPARATOR;
        $projectRoot = dirname(dirname($moduleRoot)) . self::PATH_SEPARATOR;

        if (is_array($configArray)) {
            $config = $configArray;
        } elseif (file_exists($moduleRoot . self::CONFIG_FILE)) {
            $config = require($moduleRoot . self::CONFIG_FILE);
        } elseif (file_exists($projectRoot . self::CONFIG_FILE)){
            $config = require($projectRoot . self::CONFIG_FILE);
        } else {
            throw new ErrorException("No configuration supplied.");
        }
        return $config;
    }
}