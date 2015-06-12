<?php
/**
 * This file has been automaticaly generated by Pomm's generator.
 * You MIGHT NOT edit this file as your changes will be lost at next
 * generation.
 */

namespace AppBundle\Model\Ode\PublicSchema\AutoStructure;

use PommProject\ModelManager\Model\RowStructure;

/**
 * Calendarobject
 *
 * Structure class for relation public.calendarobject.
 * 
 * Class and fields comments are inspected from table and fields comments.
 * Just add comments in your database and they will appear here.
 * @see http://www.postgresql.org/docs/9.0/static/sql-comment.html
 *
 *
 *
 * @see RowStructure
 */
class Calendarobject extends RowStructure
{
    /**
     * __construct
     *
     * Structure definition.
     *
     * @access public
     */
    public function __construct()
    {
        $this
            ->setRelation('public.calendarobject')
            ->setPrimaryKey(['uid'])
            ->addField('uri', 'text')
            ->addField('lastmodified', 'int4')
            ->addField('calendarid', 'int4')
            ->addField('calendardata', 'text')
            ->addField('etag', 'text')
            ->addField('size', 'int4')
            ->addField('extracted_data', 'json')
            ->addField('uid', 'text')
            ->addField('component', 'text')
            ->addField('slug', 'text')
            ;
    }
}
