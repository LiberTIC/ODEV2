<?php

namespace AppBundle\Service;

class PommManager
{
    private $pomm;

    public function __construct($pomm)
    {
        $this->pomm = $pomm;
    }

    public function getModel($schema,$table) {
        return $this->pomm['ODE']->getModel('\AppBundle\Model\Ode\\'.ucfirst($schema).'Schema\\'.ucfirst($table).'Model');
    }

    public function findAll($schema,$table) {

        $res = $this->getModel($schema,$table)->findAll();

        return $res;
    }

    public function findById($schema,$table,$id) {

        $res = $this->getModel($schema,$table)->findByPK(['uid' => $id]);

        return $res;
    }

    public function findWhere($schema,$table,$where) {

        $res = $this->getModel($schema,$table)->findWhere($where);

        return $res;
    }

    // Create AND save
    public function insertOne($schema,$table,$fields) {

        $res = $this->getModel($schema,$table)->createAndSave($fields);

        return $res;
    }

    public function createOne($schema,$table,$fields) {

        $res = $this->getModel($schema,$table)->createEntity($fields);

        return $res;
    }

    public function updateOne($schema,$table,$entity,$fields_keys) {

        $res = $this->getModel($schema,$table)->updateOne($entity,$fields_keys);

        return $res;
    }
}