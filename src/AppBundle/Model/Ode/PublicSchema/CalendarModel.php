<?php

namespace AppBundle\Model\Ode\PublicSchema;

use PommProject\ModelManager\Model\Model;
use PommProject\ModelManager\Model\ModelTrait\WriteQueries;
use AppBundle\Model\Ode\PublicSchema\AutoStructure\Calendar as CalendarStructure;

/**
 * CalendarModel.
 *
 * Model class for table calendar.
 *
 * @see Model
 */
class CalendarModel extends Model
{
    use WriteQueries;

    /**
     * __construct().
     *
     * Model constructor
     */
    public function __construct()
    {
        $this->structure = new CalendarStructure();
        $this->flexible_entity_class = '\AppBundle\Model\Ode\PublicSchema\Calendar';
    }
}
