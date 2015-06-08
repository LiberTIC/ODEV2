<?php

namespace AppBundle\Backend\CalDAV;

use Sabre\CalDAV;
use Sabre\CalDAV\Backend\AbstractBackend;
use Sabre\CalDAV\Backend\SyncSupport;
use Sabre\CalDAV\Backend\SubscriptionSupport;
use Sabre\CalDAV\Backend\SchedulingSupport;



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

    public function getCalendarsForUser($principalUri) {

        $calendars = $this->manager->findWhere('public','calendar','principaluri', $principalUri);

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

    public function createCalendar($principalUri, $calendarUri, array $properties) {

        return "null";
    }

    public function updateCalendar($calendarId, \Sabre\DAV\PropPatch $propPatch) {

    }

    public function deleteCalendar($calendarId) {

    }

    /* EVENTS */

    public function getCalendarObjects($calendarId) {

        return [];
    }

    public function getCalendarObject($calendarId, $objectUri) {

        return null;
    }

    public function getMultipleCalendarObjects($calendarId, array $uris) {

        return [];
    }

    public function createCalendarObject($calendarId, $objectUri, $calendarData) {

        return "null";
    }

    public function updateCalendarObject($calendarId, $objectUri, $calendarData) {

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

    }

    public function getCalendarObjectByUID($principalUri, $uid) {

        return null;
    }

    public function getChangesForCalendar($calendarId, $syncToken, $syncLevel, $limit = null) {

        return [];
    }

    protected function addChange($calendarId,$objectUri, $operation) {

    }

    public function getSubscriptionsForUser($principalUri) {

        return [];
    }

    public function createSubscription($principalUri, $uri, array $properties) {

        return "null";
    }

    public function updateSubscription($subscriptionId, \Sabre\DAV\PropPatch $propPatch) {

    }

    public function deleteSubscription($subscriptionId) {

    }

    public function getSchedulingObject($principalUri, $objectUri) {

        return [];
    }

    public function getSchedulingObjects($principalUri) {

        return [];
    }

    public function deleteSchedulingObject($principalUri, $objectUri) {

    }

    public function createSchedulingObject($principalUri, $objectUri, $objectData) {

    }
}