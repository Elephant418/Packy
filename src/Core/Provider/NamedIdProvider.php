<?php

namespace Model418\Core\Provider;

abstract class NamedIdProvider extends NoRelationProvider
{


    /* ATTRIBUTES
     *************************************************************************/
    protected $idField = 'name';


    /* SETTER
     *************************************************************************/
    public function setIdField($idField)
    {
        $this->idField = $idField;
        return $this;
    }


    /* PROTECTED SOURCE FILE METHODS
     *************************************************************************/
    protected function findAvailableIdByData($data) {
        $name = '';
        if (isset($data[$this->idField]) && is_string($data[$this->idField])) {
            $name = $data[$this->idField];
        }
        $suffix = 0;
        do {
            $id = $this->getIdByNameAndSuffix($name, $suffix);
            $suffix++;
        } while (!$this->isIdAvailable($id));
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

    protected function isIdAvailable($id)
    {
        throw new \LogicException('This method must be overridden');
    }
}