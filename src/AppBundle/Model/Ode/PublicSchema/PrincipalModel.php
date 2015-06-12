<?php

namespace AppBundle\Model\Ode\PublicSchema;

use PommProject\ModelManager\Model\Model;
use PommProject\ModelManager\Model\ModelTrait\WriteQueries;
use AppBundle\Model\Ode\PublicSchema\AutoStructure\Principal as PrincipalStructure;

/**
 * PrincipalModel.
 *
 * Model class for table principal.
 *
 * @see Model
 */
class PrincipalModel extends Model
{
    use WriteQueries;

    /**
     * __construct().
     *
     * Model constructor
     */
    public function __construct()
    {
        $this->structure = new PrincipalStructure();
        $this->flexible_entity_class = '\AppBundle\Model\Ode\PublicSchema\Principal';
    }
}
