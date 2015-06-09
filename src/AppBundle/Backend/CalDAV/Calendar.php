<?php

namespace AppBundle\Backend\CalDAV;

use Sabre\CalDAV;
use Sabre\CalDAV\Backend\AbstractBackend;
use Sabre\CalDAV\Backend\SyncSupport;
use Sabre\CalDAV\Backend\SubscriptionSupport;
use Sabre\CalDAV\Backend\SchedulingSupport;

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

        echo "cc";
        return "null";
    }

    public function updateCalendar($calendarId, \Sabre\DAV\PropPatch $propPatch) {

        echo "uc";
    }

    public function deleteCalendar($calendarId) {

        echo "dc";
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
            ->andWhere('calendarid = $*',$calendarId);

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

        echo "cco";
        return "null";
    }

    public function updateCalendarObject($calendarId, $objectUri, $calendarData) {

        echo "uco";
        return "null";
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

        echo "dco";
    }

    public function getCalendarObjectByUID($principalUri, $uid) {

        echo "gcobu";
        return null;
    }

    /* CHANGES */

    public function getChangesForCalendar($calendarId, $syncToken, $syncLevel, $limit = null) {

        echo "gcfc";
        return [];
    }

    protected function addChange($calendarId,$objectUri, $operation) {

        echo "ac";
    }

    /* OTHER */

    public function getSubscriptionsForUser($principalUri) {

        //Method called at /calendars/admin/ in browser
        return [];
    }

    public function createSubscription($principalUri, $uri, array $properties) {

        echo "cs";
        return "null";
    }

    public function updateSubscription($subscriptionId, \Sabre\DAV\PropPatch $propPatch) {

        echo "us";
    }

    public function deleteSubscription($subscriptionId) {

        echo "ds";
    }

    public function getSchedulingObject($principalUri, $objectUri) {

        echo "gso";
        return [];
    }

    public function getSchedulingObjects($principalUri) {

        echo "gso";
        return [];
    }

    public function deleteSchedulingObject($principalUri, $objectUri) {

        echo "dso";
    }

    public function createSchedulingObject($principalUri, $objectUri, $objectData) {

        echo "cso";
    }
}