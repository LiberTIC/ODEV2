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
}