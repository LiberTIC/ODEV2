<?php

namespace AppBundle\Model\Ode\PublicSchema;

use PommProject\ModelManager\Model\Model;
use PommProject\ModelManager\Model\Projection;
use PommProject\ModelManager\Model\ModelTrait\WriteQueries;

use PommProject\Foundation\Where;

use AppBundle\Model\Ode\PublicSchema\AutoStructure\Calendarchanges as CalendarchangesStructure;
use AppBundle\Model\Ode\PublicSchema\Calendarchanges;

/**
 * CalendarchangesModel
 *
 * Model class for table calendarchanges.
 *
 * @see Model
 */
class CalendarchangesModel extends Model
{
    use WriteQueries;

    /**
     * __construct()
     *
     * Model constructor
     *
     * @access public
     */
    public function __construct()
    {
        $this->structure = new CalendarchangesStructure;
        $this->flexible_entity_class = '\AppBundle\Model\Ode\PublicSchema\Calendarchanges';
    }
}
