<?php

namespace AppBundle\Backend\CalDAV;

use Sabre\CalDAV;
use Sabre\CalDAV\Backend\AbstractBackend;
use Sabre\CalDAV\Backend\SchedulingSupport;
use Sabre\CalDAV\Backend\SubscriptionSupport;
use Sabre\CalDAV\Backend\SyncSupport;
use Sabre\CalDAV\Plugin;
use Sabre\DAV\Exception as DAVException;
use Sabre\DAV\Exception\BadRequest;
use Sabre\DAV\PropPatch;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\DateTimeParser;
use Sabre\VObject\Reader;
use Sabre\VObject\Recur\EventIterator;
use Cocur\Slugify\Slugify;
use PommProject\Foundation\Where;
use PommProject\ModelManager\Model\FlexibleEntity\FlexibleEntityInterface;
use PommProject\ModelManager\Model\CollectionIterator;
use AppBundle\Service\PommManager;
use AppBundle\Entity\Event;

/**
 * Class Calendar.
 */
class Calendar extends AbstractBackend implements SyncSupport, SubscriptionSupport, SchedulingSupport
{
    /**
     * @var PommManager
     */
    protected $manager;

    /**
     * @var Slugify
     */
    protected $slugify;

    /**
     * @var string
     */
    protected $pathForUrl;

    /**
     * @var array
     */
    public $propertyMap = [
        '{DAV:}displayname' => 'displayname',
        '{urn:ietf:params:xml:ns:caldav}calendar-description' => 'description',
        '{urn:ietf:params:xml:ns:caldav}calendar-timezone' => 'timezone',
        '{http://apple.com/ns/ical/}calendar-order' => 'calendarorder',
        '{http://apple.com/ns/ical/}calendar-color' => 'calendarcolor',
    ];

    /**
     * @param PommManager $manager
     * @param string      $pathForUrl
     * @param Slugify     $slugify
     */
    public function __construct($manager, $pathForUrl = null, Slugify $slugify = null)
    {
        $this->manager = $manager;
        $this->slugify = $slugify;
        $this->pathForUrl = $pathForUrl;
    }

    /* CALENDAR */

    /**
     * @param string $calendarId
     *
     * @return FlexibleEntityInterface|null
     */
    public function getCalendarById($calendarId)
    {
        $calendar = $this->manager->findById('public', 'calendar', $calendarId);

        return $calendar;
    }

    /**
     * {@inheritdoc}
     */
    public function getCalendarsForUser($principalUri)
    {
        $where = Where::create('principaluri = $*', [$principalUri]);

        $calendars = $this->manager->findWhere('public', 'calendar', $where);

        $raws = [];

        foreach ($calendars as $calendar) {
            $raw = [
                'id' => $calendar->uid,
                'uri' => $calendar->uri,
                'principaluri' => $calendar->principaluri,
                '{'.Plugin::NS_CALENDARSERVER.'}getctag' => 'http://sabre.io/ns/sync/'.($calendar->synctoken ?: '0'),
                '{http://sabredav.org/ns}sync-token' => $calendar->synctoken ?: '0',
                '{'.Plugin::NS_CALDAV.'}supported-calendar-component-set' => new CalDAV\Xml\Property\SupportedCalendarComponentSet($calendar->components),
                '{'.Plugin::NS_CALDAV.'}schedule-calendar-transp' => new CalDAV\Xml\Property\ScheduleCalendarTransp($calendar->transparent ? 'transparent' : 'opaque'),
            ];

            foreach ($this->propertyMap as $xmlName => $dbName) {
                $raw[$xmlName] = $calendar->__get($dbName);
            }

            $raws[] = $raw;
        }

        return $raws;
    }

    /**
     * @return CollectionIterator|null
     */
    public function getAllCalendars()
    {
        $calendars = $this->manager->findAll('public', 'calendar');

        return $calendars;
    }

    /**
     * {@inheritdoc}
     * @throws DAVException
     */
    public function createCalendar($principalUri, $calendarUri, array $properties)
    {
        $values = [
            'principaluri' => $principalUri,
            'uri' => $calendarUri,
            'synctoken' => 1,
            'transparent' => 0,
            'slug' => $this->generateSlug($properties['{DAV:}displayname'], 'calendar'),
        ];

        // Default value
        $sccs = '{urn:ietf:params:xml:ns:caldav}supported-calendar-component-set';
        if (!isset($properties[$sccs])) {
            $values['components'] = ['VEVENT','VTODO'];
        } else {
            if (!($properties[$sccs] instanceof CalDAV\Xml\Property\SupportedCalendarComponentSet)) {
                throw new DAVException('The '.$sccs.' property must be of type: \Sabre\CalDAV\Xml\Property\SupportedCalendarComponentSet');
            }
            $values['components'] = $properties[$sccs]->getValue();
        }
        $transp = '{'.Plugin::NS_CALDAV.'}schedule-calendar-transp';
        if (isset($properties[$transp])) {
            $values['transparent'] = $properties[$transp]->getValue() === 'transparent';
        }

        foreach ($this->propertyMap as $xmlName => $dbName) {
            if (isset($properties[$xmlName])) {
                $values[$dbName] = $properties[$xmlName];
            }
        }

        $calendar = $this->manager->insertOne('public', 'calendar', $values);

        return $calendar->uid;
    }

    /**
     * Generate a unique calendarSlug, duplicates become *-n, as in foo, foo-1, foo-2, etc.
     *
     * @param $str
     * @param $table
     *
     * @todo: this is not ACID-compliant & not scalable, we need a SQL procedure instead
     *
     * @return string
     */
    public function generateSlug($str, $table)
    {
        $calendarUri = $this->slugify->slugify($str);

        if ($calendarUri == "")
            $calendarUri = 'null';

        $i = -1;
        do {
            $i++;
            $where = Where::create('slug = $*', [$calendarUri.($i == 0 ? '' : '-'.$i)]);
            $calendars = $this->manager->findWhere('public', $table, $where);
        } while (sizeof($calendars->extract()) != 0);

        return $calendarUri.($i == 0 ? '' : '-'.$i);
    }

    /**
     * @return string
     * @link http://php.net/manual/fr/function.uniqid.php#94959
     */
    public function generateCalendarUri()
    {
        return strtoupper(sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        ));
    }


    /**
     * {@inheritdoc}
     */
    public function updateCalendar($calendarId, PropPatch $propPatch)
    {
        $supportedProperties = array_keys($this->propertyMap);
        $supportedProperties[] = '{'.Plugin::NS_CALDAV.'}schedule-calendar-transp';

        $manager = $this->manager;

        $propPatch->handle($supportedProperties, function ($mutations) use ($calendarId, $manager) {
            $newValues = [];
            foreach ($mutations as $propertyName => $propertyValue) {
                switch ($propertyName) {
                    case '{'.Plugin::NS_CALDAV.'}schedule-calendar-transp' :
                        $fieldName = 'transparent';
                        $newValues[$fieldName] = $propertyValue->getValue() === 'transparent';
                        break;
                    default :
                        $fieldName = $this->propertyMap[$propertyName];
                        $newValues[$fieldName] = $propertyValue;
                        break;
                }
            }

            $calendar = $manager->findById('public', 'calendar', $calendarId);

            foreach ($newValues as $name => $value) {
                $calendar->$name = $value;
            }

            if ($calendar->displayname != $newValues['displayname']) {
                $newValues['slug'] = true;
                $calendar->slug = $this->generateSlug($calendar->displayname, 'calendar');
            }

            $manager->updateOne('public', 'calendar', $calendar, array_keys($newValues));

            $this->addChange($calendarId, '', 2);

            return true;

        });
    }

    /**
     * {@inheritdoc}
     */
    public function deleteCalendar($calendarId)
    {
        $this->manager->query('DELETE FROM calendarchange WHERE calendarid = '.$calendarId);

        $this->manager->query('DELETE FROM calendarobject WHERE calendarid = '.$calendarId);

        $this->manager->query('DELETE FROM calendar WHERE uid = '.$calendarId);
    }

    /**
     * {@inheritdoc}
     */
    public function getCalendarObjects($calendarId)
    {
        $where = Where::create('calendarid = $*', [$calendarId]);

        $calendarObjects = $this->manager->findWhere('public', 'calendarobject', $where);

        $raws = [];

        foreach ($calendarObjects as $object) {
            $raws[] = [
                'id' => $object->uid,
                'uri' => $object->uri,
                'lastmodified' => $object->lastmodified,
                'etag' => '"'.$object->etag.'"',
                'calendarid' => $object->calendarid,
                'size' => (int) $object->size,
                'component' => strtolower($object->component),
            ];
        }

        return $raws;
    }

    /**
     * {@inheritdoc}
     */
    public function getCalendarObject($calendarId, $objectUri)
    {
        $where = Where::create('calendarid = $*', [$calendarId])
            ->andWhere('uri = $*', [$objectUri]);

        $calendarObject = $this->manager->findWhere('public', 'calendarobject', $where);

        if ($calendarObject->count() == 0) {
            return;
        }

        $calendarObject = $calendarObject->get(0);

        $raw = [
            'id' => $calendarObject->uid,
            'uri' => $calendarObject->uri,
            'lastmodified' => $calendarObject->lastmodified,
            'etag' => '"'.$calendarObject->etag.'"',
            'calendarid' => $calendarObject->calendarid,
            'size' => (int) $calendarObject->size,
            'calendardata' => $calendarObject->calendardata,
            'component' => strtolower($calendarObject->component),
        ];

        return $raw;
    }

    /**
     * {@inheritdoc}
     */
    public function getMultipleCalendarObjects($calendarId, array $uris)
    {
        $where = Where::createWhereIn('uri', $uris)
            ->andWhere('calendarid = $*', [$calendarId]);

        $calendarObjects = $this->manager->findWhere('public', 'calendarobject', $where);

        $raws = [];

        foreach ($calendarObjects as $object) {
            $raws[] = [
                'id' => $object->uid,
                'uri' => $object->uri,
                'lastmodified' => $object->lastmodified,
                'etag' => '"'.$object->etag.'"',
                'calendarid' => $object->calendarid,
                'size' => (int) $object->size,
                'calendardata' => $object->calendardata,
                'component' => strtolower($object->component),
            ];
        }

        return $raws;
    }

    /**
     * @return CollectionIterator|null
     */
    public function getAllCalendarObjects()
    {
        $calendarObjects = $this->manager->findAll('public', 'calendarobject');

        return $calendarObjects;
    }

    /**
     * We mangle the calendar-data, so the result of a subsequent GET to this object is not
     * the exact same as this request body. This is why we don't return anything here (no ETag).
     * {@inheritdoc}
     */
    public function createCalendarObject($calendarId, $objectUri, $calendarData)
    {
        $vCal = Reader::read($calendarData);

        $slug = $this->generateSlug($vCal->VEVENT->SUMMARY->getValue(), 'calendarobject');

        $this->addURL($vCal, $slug);

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
            'extracted_data' => Event::extractData($vCal),
            'slug' => $slug,
        ];

        $this->addChange($calendarId, $objectUri, 1);
        $this->manager->insertOne('public', 'calendarobject', $values);
    }

    /**
     * @param $vCal
     * @param $slug
     */
    protected function addURL($vCal, $slug)
    {
        $url = $this->pathForUrl.'/'.$slug;
        $vCal->VEVENT->add('URL', $url, ['VALUE' => 'URI']);
    }

    /**
     * @param $vCal
     * @param $slug
     */
    protected function updateURL($vCal, $slug)
    {
        $url = $this->pathForUrl.'/'.$slug;
        $vCal->VEVENT->URL->setValue($url);
    }

    /**
     * Apple Calendar use a custom property: X-APPLE-STRUCTURED-LOCATION
     * Even if they also add a LOCATION property, they should also add a GEO property
     * They don't do it, so we do it.
     *
     * @param VCalendar $vCal
     * @return void
     */
    protected function extractAppleGeo(VCalendar $vCal)
    {
        $struct = $vCal->VEVENT->__get('X-APPLE-STRUCTURED-LOCATION');

        if ($struct == null) {
            return;
        }
        // X-APPLE-STRUCTURED-LOCATION returns a "geo:" prefixed string,
        // we omit that prefix.
        $geo = substr($struct->getValue(), 4);

        if ($vCal->VEVENT->__get('GEO') == null) {
            $vCal->VEVENT->add('GEO', explode(',', $geo));
        } else {
            $vCal->VEVENT->GEO->setParts(explode(',', $geo));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updateCalendarObject($calendarId, $objectUri, $calendarData)
    {
        $where = Where::create('calendarid = $*', [$calendarId])
            ->andWhere('uri = $*', [$objectUri]);

        $calendarobjects = $this->manager->findWhere('public', 'calendarobject', $where);

        if ($calendarobjects->count() == 0) {
            return;
        }

        $object = $calendarobjects->get(0);

        $vCal = Reader::read($calendarData);

        $this->extractAppleGeo($vCal);

        if ($object->extracted_data['name'] != $vCal->VEVENT->SUMMARY) {
            $object->slug = $this->generateSlug($vCal->VEVENT->SUMMARY, 'calendarobject');
            $this->updateURL($vCal,$object->slug);
        }

        $calendarData = $vCal->serialize();

        $object->lastmodified = time();
        $object->calendardata = $calendarData;
        $object->etag = md5($calendarData);
        $object->extracted_data = Event::extractData($vCal);
        $object->size = strlen($calendarData);

        $this->manager->updateOne('public', 'calendarobject', $object,
            ['lastmodified', 'etag', 'calendardata', 'extracted_data', 'size', 'slug']);

        $this->addChange($calendarId, $objectUri, 2);
    }

    /**
     * not used yet.
     * @param string $calendarData
     *
     * @return array
     *
     * @throws BadRequest
     */
    protected function getDenormalizedData($calendarData)
    {
        $vObject = Reader::read($calendarData);
        $componentType = null;
        $component = null;
        $firstOccurence = null;
        $lastOccurence = null;
        $uid = null;
        foreach ($vObject->getComponents() as $component) {
            if ($component->name !== 'VTIMEZONE') {
                $componentType = $component->name;
                $uid = (string) $component->UID;
                break;
            }
        }
        if (!$componentType) {
            throw new BadRequest('Calendar objects must have a VJOURNAL, VEVENT or VTODO component');
        }
        if ($componentType === 'VEVENT') {
            $firstOccurence = $component->DTSTART->getDateTime()->getTimeStamp();
            // Finding the last occurence is a bit harder
            if (!isset($component->RRULE)) {
                if (isset($component->DTEND)) {
                    $lastOccurence = $component->DTEND->getDateTime()->getTimeStamp();
                } elseif (isset($component->DURATION)) {
                    $endDate = clone $component->DTSTART->getDateTime();
                    $endDate->add(DateTimeParser::parse($component->DURATION->getValue()));
                    $lastOccurence = $endDate->getTimeStamp();
                } elseif (!$component->DTSTART->hasTime()) {
                    $endDate = clone $component->DTSTART->getDateTime();
                    $endDate->modify('+1 day');
                    $lastOccurence = $endDate->getTimeStamp();
                } else {
                    $lastOccurence = $firstOccurence;
                }
            } else {
                $it = new EventIterator($vObject, (string) $component->UID);
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
            'etag' => md5($calendarData),
            'size' => strlen($calendarData),
            'componentType' => $componentType,
            'firstOccurence' => $firstOccurence,
            'lastOccurence' => $lastOccurence,
            'uid' => $uid,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function deleteCalendarObject($calendarId, $objectUri)
    {
        $this->manager->query('DELETE FROM calendarobject WHERE uri = \''.$objectUri.'\' AND calendarid = '.$calendarId);

        $this->addChange($calendarId, $objectUri, 3);
    }

    /**
     * {@inheritdoc}
     */
    public function getCalendarObjectByUID($principalUri, $uid)
    {
        $object = $this->manager->findById('public', 'calendarobject', $uid);

        if ($object != null) {
            $calendar = $this->manager->findById('public', 'calendar', $object->calendarid);

            if ($calendar != null) {
                return $calendar->uri.'/'.$object->uri;
            }
        }
    }

    /* CHANGES */

    /**
     * {@inheritdoc}
     */
    public function getChangesForCalendar($calendarId, $syncToken, $syncLevel, $limit = null)
    {

        // Current synctoken
        $calendar = $this->manager->findById('public', 'calendar', $calendarId);
        $currentToken = $calendar->synctoken;

        if (is_null($currentToken)) {
            return;
        }

        $result = [
            'syncToken' => $currentToken,
            'added' => [],
            'modified' => [],
            'deleted' => [],
        ];

        if ($syncToken) {
            $where = Where::create('synctoken >= $*', [$syncToken])
                        ->andWhere('synctoken < $*', [$currentToken])
                        ->andWhere('calendarid = $*', [$calendarId]);

            // Fetching all changes
            $calendarChanges = $this->manager->findWhere('public', 'calendarchange', $where, 'ORDER BY synctoken');

            $changes = [];

            // This loop ensures that any duplicates are overwritten, only the
            // last change on a node is relevant.
            foreach ($calendarChanges as $change) {
                $changes[$change->uri] = $change->operation;
            }

            foreach ($changes as $uri => $operation) {
                switch ($operation) {
                    case 1 :
                        $result['added'][] = $uri;
                        break;
                    case 2 :
                        $result['modified'][] = $uri;
                        break;
                    case 3 :
                        $result['deleted'][] = $uri;
                        break;
                }
            }
        } else {

            // No synctoken supplied, this is the initial sync.
            $objects = $this->manager->findAll('public', 'calendarobject');

            foreach ($objects as $object) {
                $result['added'] = $object->uri;
            }
        }

        return $result;
    }

    /**
     * @param $calendarId
     * @param $objectUri
     * @param $operation
     */
    protected function addChange($calendarId, $objectUri, $operation)
    {
        $calendar = $this->manager->findById('public', 'calendar', $calendarId);

        $change = [
            'uri' => $objectUri,
            'synctoken' => $calendar->synctoken,
            'calendarid' => $calendarId,
            'operation' => $operation,
        ];

        $this->manager->insertOne('public', 'calendarchange', $change);

        $sql = 'UPDATE calendar SET synctoken = synctoken + 1 WHERE uid = '.$calendarId;
        $this->manager->query($sql);
    }

    /**
     * Method called at /calendars/admin/ in browser
     * {@inheritdoc}
     */
    public function getSubscriptionsForUser($principalUri)
    {

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function createSubscription($principalUri, $uri, array $properties)
    {
        return 'null';
    }

    /**
     * {@inheritdoc}
     */
    public function updateSubscription($subscriptionId, PropPatch $propPatch)
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteSubscription($subscriptionId)
    {
        return;
    }

    /**
     * Method called on Sync by Calendar Client
     * {@inheritdoc}
     */
    public function getSchedulingObject($principalUri, $objectUri)
    {
        return [];
    }

    /**
     * Method called on Sync by Calendar Client
     * {@inheritdoc}
     */
    public function getSchedulingObjects($principalUri)
    {

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function deleteSchedulingObject($principalUri, $objectUri)
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function createSchedulingObject($principalUri, $objectUri, $objectData)
    {
        return;
    }
}
