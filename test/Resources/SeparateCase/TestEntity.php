<?php

namespace Test\Elephant418\Packy\Resources\SeparateCase;

use Elephant418\Packy\DataConnector;

trait TestEntity
{



    /* CONSTRUCTOR
     *************************************************************************/
    protected function initDataConnector()
    {
        $dataConnector = new DataConnector();
        $dataConnector->setDataFolder(__DIR__.'/data');
        $this->setDataConnector($dataConnector);
    }


    /* FETCHING METHODS
     *************************************************************************/
    public function fetchTest()
    {
        return $this->fetchById('test');
    }
}