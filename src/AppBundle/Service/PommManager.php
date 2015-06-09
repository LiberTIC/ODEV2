<?php

namespace AppBundle\Service;

class PommManager
{
    private $pomm;

    public function __construct($pomm)
    {
        $this->pomm = $pomm;
    }

    public function findAll($schema,$table) {

        $res = $this->pomm['ODE']
            ->getModel('\AppBundle\Model\Ode\\'.ucfirst($schema).'Schema\\'.ucfirst($table).'Model')
            ->findAll();

        return $res;
    }

    public function findById($schema,$table,$id) {

        $res = $this->pomm['ODE']
            ->getModel('\AppBundle\Model\Ode\\'.ucfirst($schema).'Schema\\'.ucfirst($table).'Model')
            ->findByPK(['uid' => $id]);

        return $res;
    }

    public function findWhere($schema,$table,$where) {

        $res = $this->pomm['ODE']
            ->getModel('\AppBundle\Model\Ode\\'.ucfirst($schema).'Schema\\'.ucfirst($table).'Model')
            ->findWhere($where);

        return $res;
    }

    public function insertOne($schema,$table,$fields) {

        $res = $this->pomm['ODE']
            ->getModel('\AppBundle\Model\Ode\\'.ucfirst($schema).'Schema\\'.ucfirst($table).'Model')
            ->createAndSave($fields);

        return $res;
    }

    public function createOne($schema,$table,$fields) {

        $res = $this->pomm['ODE']
            ->getModel('\AppBundle\Model\Ode\\'.ucfirst($schema).'Schema\\'.ucfirst($table).'Model')
            ->createEntity($fields);

        return $res;
    }

    public function updateOne($schema,$table,$entity,$fields_keys) {

        $res = $this->pomm['ODE']
            ->getModel('\AppBundle\Model\Ode\\'.ucfirst($schema).'Schema\\'.ucfirst($table).'Model')
            ->updateOne($entity,$fields_keys);

        return $res;

    }
}