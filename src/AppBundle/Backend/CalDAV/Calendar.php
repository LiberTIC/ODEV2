<?php

namespace AppBundle\Backend\CalDAV;

use Sabre\CalDAV;
use Sabre\CalDAV\Backend\AbstractBackend;
use Sabre\CalDAV\Backend\SyncSupport;
use Sabre\CalDAV\Backend\SubscriptionSupport;
use Sabre\CalDAV\Backend\SchedulingSupport;
use Sabre\VObject;

use PommProject\Foundation\Where;

use AppBundle\Entity\Event;
use AppBundle\Entity\Calendar as Cal;

class Calendar extends AbstractBackend implements SyncSupport, SubscriptionSupport, SchedulingSupport
{

    protected $manager;
    protected $converter;


    public $propertyMap = [
        '{DAV:}displayname'                                   => 'displayname',
        '{urn:ietf:params:xml:ns:caldav}calendar-description' => 'description',
        '{urn:ietf:params:xml:ns:caldav}calendar-timezone'    => 'timezone',
        '{http://apple.com/ns/ical/}calendar-order'           => 'calendarorder',
        '{http://apple.com/ns/ical/}calendar-color'           => 'calendarcolor',
    ];


    public function __construct($manager, $converter) {
        $this->manager = $manager;
        $this->converter = $converter;
    }

    /* CALENDAR */

    public function getCalendarById($calendarId) {

        $calendar = $this->manager->findById('public','calendar',$calendarId);

        return $calendar;
    }

    public function getCalendarsForUser($principalUri) {

        $where = Where::create("principaluri = $*",[$principalUri]);

        $calendars = $this->manager->findWhere('public','calendar',$where);

        $raws = [];

        foreach($calendars as $calendar) {
            $raw = [
                'id'                                                             => $calendar->uid,
                'uri'                                                            => $calendar->uri,
                'principaluri'                                                   => $calendar->principaluri,
                '{'.CalDAV\Plugin::NS_CALENDARSERVER.'}getctag'                  => 'http://sabre.io/ns/sync/'.($calendar->synctoken ?: '0'),
                '{http://sabredav.org/ns}sync-token'                             => $calendar->synctoken ?: '0',
                '{'.CalDAV\Plugin::NS_CALDAV.'}supported-calendar-component-set' => new CalDAV\Xml\Property\SupportedCalendarComponentSet($calendar->components),
                '{'.CalDAV\Plugin::NS_CALDAV.'}schedule-calendar-transp'         => new CalDAV\Xml\Property\ScheduleCalendarTransp($calendar->transparent ? 'transparent' : 'opaque'),
            ];

            foreach ($this->propertyMap as $xmlName => $dbName) {
                $raw[$xmlName] = $calendar->__get($dbName);
            }

            $raws[] = $raw;
        }

        return $raws;
    }

    public function getAllCalendars() {

        $calendars = $this->manager->findAll('public','calendar');

        return $calendars;
    }

    public function createCalendar($principalUri, $calendarUri, array $properties) {

        $values = [
            'principaluri' => $principalUri,
            'uri'          => $calendarUri,
            'synctoken'    => 1,
            'transparent'  => 0,
        ];

        // Default value
        $sccs = '{urn:ietf:params:xml:ns:caldav}supported-calendar-component-set';
        if (!isset($properties[$sccs])) {
            $values['components'] = ['VEVENT','VTODO'];
        } else {
            if (!($properties[$sccs] instanceof CalDAV\Xml\Property\SupportedCalendarComponentSet)) {
                throw new DAV\Exception('The ' . $sccs . ' property must be of type: \Sabre\CalDAV\Xml\Property\SupportedCalendarComponentSet');
            }
            $values['components'] = $properties[$sccs]->getValue();
        }
        $transp = '{' . CalDAV\Plugin::NS_CALDAV . '}schedule-calendar-transp';
        if (isset($properties[$transp])) {
            $values['transparent'] = $properties[$transp]->getValue() === 'transparent';
        }

        foreach ($this->propertyMap as $xmlName => $dbName) {
            if (isset($properties[$xmlName])) {

                $values[$dbName] = $properties[$xmlName];
            }
        }

        $calendar = $this->manager->insertOne('public','calendar',$values);

        return $calendar->uid;
    }

    public function updateCalendar($calendarId, \Sabre\DAV\PropPatch $propPatch) {

        $supportedProperties = array_keys($this->propertyMap);
        $supportedProperties[] = '{' . CalDAV\Plugin::NS_CALDAV . '}schedule-calendar-transp';

        $manager = $this->manager;

        $propPatch->handle($supportedProperties, function($mutations) use ($calendarId,$manager) {
            $newValues = [];
            foreach ($mutations as $propertyName => $propertyValue) {

                switch ($propertyName) {
                    case '{' . CalDAV\Plugin::NS_CALDAV . '}schedule-calendar-transp' :
                        $fieldName = 'transparent';
                        $newValues[$fieldName] = $propertyValue->getValue() === 'transparent';
                        break;
                    default :
                        $fieldName = $this->propertyMap[$propertyName];
                        $newValues[$fieldName] = $propertyValue;
                        break;
                }

            }

            $calendar = $manager->findById('public','calendar',$calendarId);

            foreach($newValues as $name => $value) {
                $calendar->$name = $value;
            }


            $manager->updateOne('public','calendar',$calendar,array_keys($newValues));

            $this->addChange($calendarId, "", 2);

            return true;

        });

    }

    public function deleteCalendar($calendarId) {

        $this->manager->query('DELETE FROM calendarchange WHERE calendarid = '.$calendarId);

        $this->manager->query('DELETE FROM calendarobject WHERE calendarid = '.$calendarId);

        $this->manager->query('DELETE FROM calendar WHERE uid = '.$calendarId);
        
    }

    /* CALENDAR OBJECTS */

    public function getCalendarObjects($calendarId) {

        $where = Where::create("calendarid = $*",[$calendarId]);

        $calendarObjects = $this->manager->findWhere('public','calendarobject',$where);

        $raws = [];

        foreach($calendarObjects as $object) {

            $raws[] = [
                'id'           => $object->uid,
                'uri'          => $object->uri,
                'lastmodified' => $object->lastmodified,
                'etag'         => '"' . $object->etag . '"',
                'calendarid'   => $object->calendarid,
                'size'         => (int)$object->size,
                'component'    => strtolower($object->component),
            ];
        }

        return $raws;
    }

    public function getCalendarObject($calendarId, $objectUri) {

        $where = Where::create("calendarid = $*",[$calendarId])
            ->andWhere("uri = $*",[$objectUri]);

        $calendarObject = $this->manager->findWhere('public','calendarobject',$where);

        if ($calendarObject->count() == 0)
            return null;

        $calendarObject = $calendarObject->get(0);

        $raw = [
            'id'           => $calendarObject->uid,
            'uri'          => $calendarObject->uri,
            'lastmodified' => $calendarObject->lastmodified,
            'etag'         => '"' . $calendarObject->etag . '"',
            'calendarid'   => $calendarObject->calendarid,
            'size'         => (int)$calendarObject->size,
            'calendardata' => $calendarObject->calendardata,
            'component'    => strtolower($calendarObject->component),
        ];

        return $raw;
    }

    public function getMultipleCalendarObjects($calendarId, array $uris) {

        $where = Where::createWhereIn('uri',$uris)
            ->andWhere('calendarid = $*',[$calendarId]);

        $calendarObjects = $this->manager->findWhere('public','calendarobject',$where);

        $raws = [];

        foreach($calendarObjects as $object) {

            $raws[] = [
                'id'           => $object->uid,
                'uri'          => $object->uri,
                'lastmodified' => $object->lastmodified,
                'etag'         => '"' . $object->etag . '"',
                'calendarid'   => $object->calendarid,
                'size'         => (int)$object->size,
                'calendardata' => $object->calendardata,
                'component'    => strtolower($object->component),
            ];
        }

        return $raws;
    }

    public function getAllCalendarObjects() {

        $calendarObjects = $this->manager->findAll('public','calendarobject');

        return $calendarObjects;
    }

    public function createCalendarObject($calendarId, $objectUri, $calendarData) {

        $vCal = VObject\Reader::read($calendarData);

        $this->addURL($vCal,$objectUri);

        $this->extractAppleGeo($vCal);

        $calendarData = $vCal->serialize();

        $values = [
            'uri' => $objectUri,
            'lastmodified' => time(),
            'calendarid' => $calendarId,
            'calendardata' => $calendarData,
            'etag' => md5($calendarData),
            'size' => strlen($calendarData),
            'component' => 'vevent',
            'uid' => $vCal->VEVENT->UID->__toString(),
            'extracted_data' => $this->converter->extractToLobject($vCal)
        ];

        $calendar = $this->manager->insertOne('public','calendarobject',$values);
    }

    protected function addURL($vCal,$id)
    {
        $url = 'projet-ode.fr/event/'.substr($id,0,-4);
        $vCal->VEVENT->add('URL', $url, ['VALUE'=>"URI"]);
    }

    /* Apple Calendar use a custom property: X-APPLE-STRUCTURED-LOCATION
     * Even if they also add a LOCATION property, they should also add a GEO property
     * They don't do it, so we do it.
     */
    protected function extractAppleGeo($vCal) {
        $struct = $vCal->VEVENT->__get('X-APPLE-STRUCTURED-LOCATION');
        
        if ($struct == null)
            return null;

        $geo = substr($struct->getValue(),4);

        if ($vCal->VEVENT->__get('GEO') == null)
        {
            $vCal->VEVENT->add('GEO',explode(',',$geo));
        }
        else 
        {
            $vCal->VEVENT->GEO->setParts(explode(',',$geo));
        }
    }

    public function updateCalendarObject($calendarId, $objectUri, $calendarData) {

    }

    protected function getDenormalizedData($calendarData) {

        $vObject = VObject\Reader::read($calendarData);
        $componentType = null;
        $component = null;
        $firstOccurence = null;
        $lastOccurence = null;
        $uid = null;
        foreach ($vObject->getComponents() as $component) {
            if ($component->name !== 'VTIMEZONE') {
                $componentType = $component->name;
                $uid = (string)$component->UID;
                break;
            }
        }
        if (!$componentType) {
            throw new \Sabre\DAV\Exception\BadRequest('Calendar objects must have a VJOURNAL, VEVENT or VTODO component');
        }
        if ($componentType === 'VEVENT') {
            $firstOccurence = $component->DTSTART->getDateTime()->getTimeStamp();
            // Finding the last occurence is a bit harder
            if (!isset($component->RRULE)) {
                if (isset($component->DTEND)) {
                    $lastOccurence = $component->DTEND->getDateTime()->getTimeStamp();
                } elseif (isset($component->DURATION)) {
                    $endDate = clone $component->DTSTART->getDateTime();
                    $endDate->add(VObject\DateTimeParser::parse($component->DURATION->getValue()));
                    $lastOccurence = $endDate->getTimeStamp();
                } elseif (!$component->DTSTART->hasTime()) {
                    $endDate = clone $component->DTSTART->getDateTime();
                    $endDate->modify('+1 day');
                    $lastOccurence = $endDate->getTimeStamp();
                } else {
                    $lastOccurence = $firstOccurence;
                }
            } else {
                $it = new VObject\Recur\EventIterator($vObject, (string)$component->UID);
                $maxDate = new \DateTime(self::MAX_DATE);
                if ($it->isInfinite()) {
                    $lastOccurence = $maxDate->getTimeStamp();
                } else {
                    $end = $it->getDtEnd();
                    while ($it->valid() && $end < $maxDate) {
                        $end = $it->getDtEnd();
                        $it->next();

                    }
                    $lastOccurence = $end->getTimeStamp();
                }

            }
        }

        return [
            'etag'           => md5($calendarData),
            'size'           => strlen($calendarData),
            'componentType'  => $componentType,
            'firstOccurence' => $firstOccurence,
            'lastOccurence'  => $lastOccurence,
            'uid'            => $uid,
        ];

    }

    public function deleteCalendarObject($calendarId, $objectUri) {

        //echo "dco";
    }

    public function getCalendarObjectByUID($principalUri, $uid) {

        $object = $this->manager->findById('public','calendarobject',$uid);

        if ($object != null) {
            $calendar = $this->manager->findById('public','calendar',$object->calendarid);

            if ($calendar != null) {
                return $calendar->uri."/".$object->uri;
            }
        }

    }

    /* CHANGES */

    public function getChangesForCalendar($calendarId, $syncToken, $syncLevel, $limit = null) {

        //echo "gcfc";
        return [];
    }

    protected function addChange($calendarId,$objectUri, $operation) {

        $calendar = $this->manager->findById('public','calendar',$calendarId);

        $change = [
            'uri' => $objectUri,
            'synctoken' => $calendar->synctoken,
            'calendarid' => $calendarId,
            'operation' => $operation
        ];
        
        $this->manager->insertOne('public','calendarchange',$change);
    }

    /* OTHER */

    public function getSubscriptionsForUser($principalUri) {

        //Method called at /calendars/admin/ in browser
        return [];
    }

    public function createSubscription($principalUri, $uri, array $properties) {

        //echo "cs";
        return "null";
    }

    public function updateSubscription($subscriptionId, \Sabre\DAV\PropPatch $propPatch) {

        //echo "us";
    }

    public function deleteSubscription($subscriptionId) {

        //echo "ds";
    }

    public function getSchedulingObject($principalUri, $objectUri) {

        //Method called by Apple Calendar Client
        return [];
    }

    public function getSchedulingObjects($principalUri) {

        //Method called by Apple Calendar Client
        return [];
    }

    public function deleteSchedulingObject($principalUri, $objectUri) {

        //echo "dso";
    }

    public function createSchedulingObject($principalUri, $objectUri, $objectData) {

        //echo "cso";
    }
}