<?php

namespace AppBundle\Model\Caldav\PublicSchema;

use PommProject\ModelManager\Model\Model;
use PommProject\ModelManager\Model\Projection;
use PommProject\ModelManager\Model\ModelTrait\WriteQueries;

use PommProject\Foundation\Where;

use AppBundle\Model\Caldav\PublicSchema\AutoStructure\Calendars as CalendarsStructure;
use AppBundle\Model\Caldav\PublicSchema\Calendars;

/**
 * CalendarsModel
 *
 * Model class for table calendars.
 *
 * @see Model
 */
class CalendarsModel extends Model
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
        $this->structure = new CalendarsStructure;
        $this->flexible_entity_class = '\AppBundle\Model\Caldav\PublicSchema\Calendars';
    }
}
