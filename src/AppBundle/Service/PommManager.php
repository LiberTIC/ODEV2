<?php

namespace AppBundle\Service;

use PommProject\Foundation\Pomm;
use PommProject\Foundation\Where;
use PommProject\ModelManager\Model\CollectionIterator;
use PommProject\ModelManager\Model\FlexibleEntity\FlexibleEntityInterface;
use PommProject\ModelManager\Model\Model;
use PommProject\Foundation\Session\ResultHandler;

/**
 * Class PommManager
 *
 * @package AppBundle\Service
 */
class PommManager
{

    /**
     * @var Pomm
     */
    private $pomm;

    /**
     * @param Pomm $pomm
     */
    public function __construct(Pomm $pomm)
    {
        $this->pomm = $pomm;
    }

    /**
     * @param string $schema
     * @param string $table
     *
     * @return Model
     */
    public function getModel($schema, $table)
    {
        return $this->pomm['ODE']->getModel('\AppBundle\Model\Ode\\'.ucfirst($schema).'Schema\\'.ucfirst($table).'Model');
    }

    /**
     * @param string $schema
     * @param string $table
     *
     * @return CollectionIterator|null
     */
    public function findAll($schema, $table)
    {
        return $this->getModel($schema, $table)->findAll();
    }

    /**
     * @param string $schema
     * @param string $table
     * @param string $id
     *
     * @return FlexibleEntityInterface|null
     */
    public function findById($schema, $table, $id)
    {
        return $this->getModel($schema, $table)->findByPK(['uid' => $id]);
    }

    /**
     * @param string $schema
     * @param string $table
     * @param Where  $where
     * @param string $suffix
     *
     * @return CollectionIterator|null
     */
    public function findWhere($schema, $table, $where, $suffix = '')
    {
        return $this->getModel($schema, $table)->findWhere($where, [], $suffix);
    }

    // Create AND save
    /**
     * @param string $schema
     * @param string $table
     * @param array  $fields
     *
     * @return Model|null
     */
    public function insertOne($schema, $table, $fields)
    {
        return $this->getModel($schema, $table)->createAndSave($fields);
    }

    /**
     * @param string $schema
     * @param string $table
     * @param array  $fields
     *
     * @return FlexibleEntityInterface|null
     */
    public function createOne($schema, $table, $fields)
    {
        return $this->getModel($schema, $table)->createEntity($fields);
    }

    /**
     * @param string                  $schema
     * @param string                  $table
     * @param FlexibleEntityInterface $entity
     * @param array                   $fieldsKeys
     *
     * @return Model|null
     */
    public function updateOne($schema, $table, $entity, $fieldsKeys)
    {
        return $this->getModel($schema, $table)->updateOne($entity, $fieldsKeys);
    }

    /**
     * @param string $sql
     *
     * @return ResultHandler|array
     */
    public function query($sql)
    {
        return $this->pomm['ODE']->getConnection()->executeAnonymousQuery($sql);
    }
}
