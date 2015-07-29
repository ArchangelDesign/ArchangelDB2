<?php
/**
 * Archangel DB 2
 * www.archangel-design.com
 * @author Rafal Martinez-Marjanski
 */

namespace ArchangelDB;


use Zend\Db\Adapter\Adapter;

class ADB2 {

    private $_conf;
    private $_adapter;
    private $_driver;

    public function __construct($config = null)
    {
        $this->_conf = ConfigLoader::getConfig($config);
        $this->_adapter = new Adapter($this->_conf);
        $this->_driver = $this->_adapter->getDriver();
    }
}