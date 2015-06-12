<?php

namespace AppBundle\Model\Ode\PublicSchema;

use PommProject\ModelManager\Model\Model;
use PommProject\ModelManager\Model\ModelTrait\WriteQueries;
use AppBundle\Model\Ode\PublicSchema\AutoStructure\Users as UsersStructure;

/**
 * UsersModel.
 *
 * Model class for table users.
 *
 * @see Model
 */
class UsersModel extends Model
{
    use WriteQueries;

    /**
     * __construct().
     *
     * Model constructor
     */
    public function __construct()
    {
        $this->structure = new UsersStructure();
        $this->flexible_entity_class = '\AppBundle\Model\Ode\PublicSchema\Users';
    }
}
