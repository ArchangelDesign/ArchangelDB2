<?php

/**
 * Archangel DB 2
 * www.archangel-design.com
 * @author Rafal Martinez-Marjanski
 */

namespace ArchangelDB;

/**
 * Class Deployer
 * @package ArchangelDB
 */
class Deployer
{
    private $_deployFile = null;
    private $_deployFileName = null;

    public function __construct($filename = null)
    {
        if ($filename === null) {
            return;
        }

        if (!file_exists($filename)) {
            throw new \Exception("Deployment file not found");
        }
        // @todo: replace with fread to allow big data files
        // for now it is only for table structure and maybe some sample data
        $this->setDeployFile($filename);
    }

    public function setDeployFile($filename)
    {
        if (!file_exists($filename)) {
            throw new \Exception("Deployment file not found");
        }

        $this->_deployFile = file_get_contents($filename);
        $this->_deployFileName = $filename;
    }

    public function getDeployFileName()
    {
        return $this->_deployFileName;
    }
}