<?php

namespace AppBundle\Model\Ode\PublicSchema;

use PommProject\ModelManager\Model\Model;
use PommProject\ModelManager\Model\ModelTrait\WriteQueries;
use AppBundle\Model\Ode\PublicSchema\AutoStructure\Calendarchange as CalendarchangeStructure;

/**
 * CalendarchangeModel.
 *
 * Model class for table calendarchange.
 *
 * @see Model
 */
class CalendarchangeModel extends Model
{
    use WriteQueries;

    /**
     * __construct().
     *
     * Model constructor
     */
    public function __construct()
    {
        $this->structure = new CalendarchangeStructure();
        $this->flexible_entity_class = '\AppBundle\Model\Ode\PublicSchema\Calendarchange';
    }
}
