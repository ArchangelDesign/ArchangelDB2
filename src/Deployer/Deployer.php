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
class Deployer extends AbstractDeployer
{
    private $_deployFile = null;
    private $_deployFileName = null;
    private $_currentVersion = null;

    public function __construct(ADB2 $adb, $filename = null)
    {
        $this->_adb = $adb;
        if ($filename === null) {
            return;
        }

        if (!file_exists($filename)) {
            throw new \Exception("Deployment file not found in $filename");
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

    public function deployDatabaseStructure()
    {
        if (empty($this->_deployFile)) {
            throw new \Exception("No deployment data loaded.");
        }

        $data = new \SimpleXMLElement($this->_deployFile);

        if (!$this->_currentVersion) {
            $this->_currentVersion = $this->getDatabaseVersion();
        }
        print (print_r($data));
    }

    public function updateDatabaseStructure()
    {

    }
}