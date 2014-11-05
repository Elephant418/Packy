<?php

namespace Elephant418\Model418\DataConnection;

use Elephant418\Model418\IDataConnection;

class FileDataConnection implements IDataConnection
{


    /* ATTRIBUTES
     *************************************************************************/
    protected $fileDataRequest;
    protected $idField = 'name';
    protected $dataFolderList = array();


    /* SETTER
     *************************************************************************/
    public function setIdField($idField)
    {
        $this->idField = $idField;
        return $this;
    }


    /* DATA FOLDER METHODS
     *************************************************************************/
    public function setDataFolder($dataFolder)
    {
        $this->setDataFolderList(array($dataFolder));
        return $this;
    }
    
    public function setDataFolderList($dataFolderList)
    {
        $validDataFolderList = array();
        foreach ($dataFolderList as $dataFolder) {
            $validDataFolderList[] = $this->validDataFolder($dataFolder);
        }
        $this->dataFolderList = $validDataFolderList;
        return $this;
    }

    protected function validDataFolder($dataFolder)
    {
        $realDataFolder = realpath($dataFolder);
        if (!$realDataFolder) {
            throw new \RuntimeException('This data folder does not exist: ' . $dataFolder);
        }
        return $realDataFolder;
    }

    public function getWritableDataFolder()
    {
        return reset($this->dataFolderList);
    }


    /* DATA FOLDER METHODS
     *************************************************************************/
    public function setFileDataRequest($fileDataRequest)
    {
        if (is_string($fileDataRequest)) {
            $fileDataRequest = (new FileDataRequestFactory)->get($fileDataRequest);
        }
        $this->fileDataRequest = $fileDataRequest;
        return $this;
    }

    public function getFileDataRequest()
    {
        if (!$this->fileDataRequest) {
            return new JSONFileDataRequest;
        }
        return $this->fileDataRequest;
    }


    /* FETCHING METHODS
     *************************************************************************/
    public function fetchById($id)
    {
        foreach ($this->dataFolderList as $dataFolder) {
            $data = $this->getFileDataRequest()->getContents($dataFolder, $id);
            if ($data) {
                return $data;
            }
        }
        return null;
    }

    public function fetchByIdList($idList)
    {
        $dataList = array();
        foreach ($idList as $id) {
            $data = $this->fetchById($id);
            if ($data) {
                $dataList[$id] = $data;
            }
        }
        return $dataList;
    }

    public function fetchAll($limit = null, $offset = null, &$count = false)
    {
        $idList = $this->getAllIds();
        if ($count !== false) {
            $count = count($idList);
        }
        if (is_null($offset)) {
            $offset = 0;
        }
        if (!is_null($limit)) {
            $idList = array_slice($idList, $offset, $limit);
        }
        return $this->fetchByIdList($idList);
    }


    /* SAVE METHODS
     *************************************************************************/
    public function saveById($id, $data)
    {
        $exists = !is_null($id);
        if (!$exists) {
            $name = '';
            if (isset($data[$this->idField]) && is_string($data[$this->idField])) {
                $name = $data[$this->idField];
            }
            $id = $this->findAvailableIdByName($name);
        }
        $this->getFileDataRequest()->putContents($this->getWritableDataFolder(), $id, $data);
        return $id;
    }

    public function deleteById($id)
    {
        return $this->getFileDataRequest()->unlink($this->getWritableDataFolder(), $id);
    }


    /* PROTECTED SOURCE FILE METHODS
     *************************************************************************/
    protected function findAvailableIdByName($name) {
        $suffix = 0;
        do {
            $id = $this->getIdByNameAndSuffix($name, $suffix);
            $suffix++;
        } while ($this->getFileDataRequest()->exists($this->getWritableDataFolder(), $id));
        return $id;
    }

    protected function getIdByNameAndSuffix($name = '', $suffix = 0)
    {
        $id = array();
        if (!empty($name)) {
            $id[] = $name;
        }
        if ($suffix > 0 || empty($id)) {
            $id[] = $suffix+1;
        }
        return implode('-', $id);
    }

    protected function getAllIds()
    {
        $idList = array();
        foreach ($this->dataFolderList as $dataFolder) {
            $fileList = $this->getFileDataRequest()->getFolderList($dataFolder);
            foreach ($fileList as $file) {
                $file = basename($file);
                $id = substr($file, 0, strrpos($file, '.'));
                $idList[] = $id;
            }
        }
        return array_unique($idList);
    }
}

(new FileDataRequestFactory)
    ->register('Elephant418\\Model418\\DataConnection\\JSONFileDataRequest')
    ->register('Elephant418\\Model418\\DataConnection\\YamlFileDataRequest');