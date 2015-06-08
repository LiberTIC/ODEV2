<?php

namespace AppBundle\Model\Ode\PublicSchema;

use PommProject\ModelManager\Model\Model;
use PommProject\ModelManager\Model\Projection;
use PommProject\ModelManager\Model\ModelTrait\WriteQueries;

use PommProject\Foundation\Where;

use AppBundle\Model\Ode\PublicSchema\AutoStructure\Calendarobject as CalendarobjectStructure;
use AppBundle\Model\Ode\PublicSchema\Calendarobject;

/**
 * CalendarobjectModel
 *
 * Model class for table calendarobject.
 *
 * @see Model
 */
class CalendarobjectModel extends Model
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
        $this->structure = new CalendarobjectStructure;
        $this->flexible_entity_class = '\AppBundle\Model\Ode\PublicSchema\Calendarobject';
    }
}
