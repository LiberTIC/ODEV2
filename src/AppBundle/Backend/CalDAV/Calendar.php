<?php

namespace AppBundle\Backend\CalDAV;

use Sabre\VObject;
use Sabre\CalDAV;
use Sabre\DAV;
use Sabre\DAV\Exception\Forbidden;
use Sabre\CalDAV\Backend\AbstractBackend;
use Sabre\CalDAV\Backend\SyncSupport;
use Sabre\CalDAV\Backend\SubscriptionSupport;
use Sabre\CalDAV\Backend\SchedulingSupport;

use AppBundle\Service\FormatConverter;

class Calendar extends AbstractBackend implements SyncSupport, SubscriptionSupport, SchedulingSupport
{
    const MAX_DATE = '2038-01-01';

    protected $manager;
    protected $converter;

    public $calendarTableName = 'calendars';

    public $calendarObjectTableName = 'calendarobjects';

    public $calendarChangesTableName = 'calendarchanges';

    public $schedulingObjectTableName = 'schedulingobjects';

    public $calendarSubscriptionsTableName = 'calendarsubscriptions';

    public $propertyMap = [
        '{DAV:}displayname' => 'displayname',
        '{urn:ietf:params:xml:ns:caldav}calendar-description' => 'description',
        '{urn:ietf:params:xml:ns:caldav}calendar-timezone' => 'timezone',
        '{http://apple.com/ns/ical/}calendar-order' => 'calendarorder',
        '{http://apple.com/ns/ical/}calendar-color' => 'calendarcolor',
    ];

    public $subscriptionPropertyMap = [
        '{DAV:}displayname' => 'displayname',
        '{http://apple.com/ns/ical/}refreshrate' => 'refreshrate',
        '{http://apple.com/ns/ical/}calendar-order' => 'calendarorder',
        '{http://apple.com/ns/ical/}calendar-color' => 'calendarcolor',
        '{http://calendarserver.org/ns/}subscribed-strip-todos' => 'striptodos',
        '{http://calendarserver.org/ns/}subscribed-strip-alarms' => 'stripalarms',
        '{http://calendarserver.org/ns/}subscribed-strip-attachments' => 'stripattachments',
    ];

    public function __construct($manager, FormatConverter $converter)
    {
        $this->manager = $manager;
        $this->converter = $converter;
    }

    public function getCalendarsForUser($principalUri)
    {
        $searchResult = $this->manager->simpleQuery('caldav',$this->calendarTableName, ['principaluri' => $principalUri]);

        if (!$searchResult) {
            return [];
        }

        $calendars = [];

        foreach ($searchResult as $cal) {
            $src = $cal['_source'];

            $calendar = array(  
                      'id' => $cal['_id'],
                      'uri' => $src['uri'],
                      'principaluri' => $src['principaluri'],
                      '{'.CalDAV\Plugin::NS_CALENDARSERVER.'}getctag' => 'http://sabre.io/ns/sync/'.$src['synctoken'],
                      '{http://sabredav.org/ns}sync-token' => $src['synctoken'],
                      '{'.CalDAV\Plugin::NS_CALDAV.'}supported-calendar-component-set' => new CalDAV\Property\SupportedCalendarComponentSet($src['components']),
                      '{'.CalDAV\Plugin::NS_CALDAV.'}schedule-calendar-transp' => new CalDAV\Property\ScheduleCalendarTransp($src['transparent'] ? 'transparent' : 'opaque'),
                      '{DAV:}displayname' => $src['displayname'],
                      '{urn:ietf:params:xml:ns:caldav}calendar-description' => $src['description'],
                      '{urn:ietf:params:xml:ns:caldav}calendar-timezone' => $src['timezone'],
                      '{http://apple.com/ns/ical/}calendar-order' => $src['calendarorder'],
                      '{http://apple.com/ns/ical/}calendar-color' => $src['calendarcolor'],
                );

            $calendars[] = $calendar;
        }

        return $calendars;
    }

    public function createCalendar($principalUri, $calendarUri, array $properties)
    {
        $indexValues = [
            'principaluri' => $principalUri,
            'displayname' => null,
            'uri' => $calendarUri,
            'synctoken' => 1,
            'description' => null,
            'calendarorder' => 0,
            'calendarcolor' => null,
            'timezone' => null,
            'components' => null,
            'transparent' => 0,
        ];

        // Default value
        $sccs = '{urn:ietf:params:xml:ns:caldav}supported-calendar-component-set';
        if (!isset($properties[$sccs])) {
            $indexValues['components'] = ['VEVENT','VTODO'];
        } else {
            if (!($properties[$sccs] instanceof CalDAV\Property\SupportedCalendarComponentSet)) {
                throw new DAV\Exception('The '.$sccs.' property must be of type: \Sabre\CalDAV\Property\SupportedCalendarComponentSet');
            }
            $indexValues['components'] = $properties[$sccs]->getValue();
        }
        $transp = '{'.CalDAV\Plugin::NS_CALDAV.'}schedule-calendar-transp';
        if (isset($properties[$transp])) {
            $indexValues['transparent'] = $properties[$transp]->getValue() === 'transparent';
        }

        foreach ($this->propertyMap as $xmlName => $dbName) {
            if (isset($properties[$xmlName])) {
                $indexValues[$dbName] = $properties[$xmlName];
            }
        }

        $ret = $this->manager->simpleIndex('caldav',$this->calendarTableName, null, $indexValues);

        return $ret['_id'];
    }

    public function updateCalendar($calendarId, \Sabre\DAV\PropPatch $propPatch)
    {
        $searchResult = $this->manager->simpleGet('caldav',$this->calendarTableName, $calendarId);

        if ($searchResult == null) {
            return;
        }

        $values = $searchResult['_source'];

        $supportedProperties = array_keys($this->propertyMap);
        $supportedProperties[] = '{'.CalDAV\Plugin::NS_CALDAV.'}schedule-calendar-transp';

        $propPatch->handle($supportedProperties, function ($mutations) use ($calendarId, $values) {
            $newValues = [];
            foreach ($mutations as $propertyName => $propertyValue) {
                switch ($propertyName) {
                    case '{'.CalDAV\Plugin::NS_CALDAV.'}schedule-calendar-transp':
                        $fieldName = 'transparent';
                        $newValues[$fieldName] = $propertyValue->getValue() === 'transparent';
                        break;
                    default:
                        $fieldName = $this->propertyMap[$propertyName];
                        $newValues[$fieldName] = $propertyValue;
                        break;
                }
            }

            foreach ($newValues as $fieldName => $value) {
                $values[$fieldName] = $value;
            }

            $this->manager->simpleIndex('caldav',$this->calendarTableName, $calendarId, $values);

            $this->addChange($calendarId, '', 2);

            return true;

        });
    }

    public function deleteCalendar($calendarId)
    {
        $searchResult = $this->manager->simpleQuery('caldav',$this->calendarObjectTableName, ['calendarid' => $calendarId]);

        if ($searchResult != null) {
            foreach ($searchResult as $obj) {
                $id = $obj['_id'];
                $this->manager->simpleDelete('caldav',$this->calendarObjectTableName, $id);
            }
        }

        $this->manager->simpleDelete('caldav',$this->calendarObjectTableName, $calendarId);

        $searchResult = $this->manager->simpleQuery('caldav',$this->calendarTableName, ['calendarid' => $calendarId]);

        if ($searchResult != null) {
            foreach ($searchResult as $chg) {
                $id = $obj['_id'];
                $this->manager->simpleDelete('caldav',$this->calendarChangesTableName, $id);
            }
        }
    }

    public function getCalendarObjects($calendarId)
    {
        $searchResult = $this->manager->simpleQuery('caldav',$this->calendarObjectTableName, ['calendarid' => $calendarId]);

        if (!$searchResult) {
            return [];
        }

        $objects = [];

        foreach ($searchResult as $obj) {
            $src = $obj['_source'];

            $object = [
                'id' => $obj['_id'],
                'uri' => $src['uri'],
                'lastmodified' => $src['lastmodified'],
                'etag' => '"'.$src['etag'].'"',
                'calendarid' => $src['calendarid'],
                'size' => $src['size'],
                'component' => strtolower($src['component']),
            ];

            $objects[] = $object;
        }

        return $objects;
    }

    public function getCalendarObject($calendarId, $objectUri)
    {
        $hit = $this->manager->simpleQuery('caldav',$this->calendarObjectTableName, ['calendarid' => $calendarId, 'uri' => $objectUri]);

        if ($hit == null) {
            return;
        }

        $row = $hit[0]['_source'];

        return [
            'id' => $hit[0]['_id'],
            'uri' => $row['uri'],
            'lastmodified' => $row['lastmodified'],
            'etag' => '"'.$row['etag'].'"',
            'calendarid' => $row['calendarid'],
            'size' => $row['size'],
            'calendardata' => $row['calendardata'],
            'component' => strtolower($row['component']),
         ];
    }

    public function getMultipleCalendarObjects($calendarId, array $uris)
    {
        $searchResult = $this->manager->simpleQuery('caldav',$this->calendarObjectTableName, ['calendarid' => $calendarId, 'uri' => $uris]);

        if (!$searchResult) {
            return [];
        }

        $objects = [];

        foreach ($searchResult as $obj) {
            $src = $obj['_source'];

            $object = [
                'id' => $obj['_id'],
                'uri' => $src['uri'],
                'lastmodified' => $src['lastmodified'],
                'etag' => '"'.$src['etag'].'"',
                'calendarid' => $src['calendarid'],
                'size' => $src['size'],
                'component' => strtolower($src['component']),
            ];

            $objects[] = $object;
        }

        return $objects;
    }

    public function createCalendarObject($calendarId, $objectUri, $calendarData)
    {

        $vCal = VObject\Reader::read($calendarData);

        $this->addURL($vCal,md5($objectUri));

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
            'lobject' => $this->converter->extractToLobject($vCal)
        ];

        $this->manager->simpleIndex('caldav',$this->calendarObjectTableName, null, $values);

        $this->addChange($calendarId, $objectUri, 1);
        
    }

    protected function addURL($vCal,$id)
    {
        $url = 'projet-ode.fr/event/'.$id;
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

    public function updateCalendarObject($calendarId, $objectUri, $calendarData)
    {
        $vCal = VObject\Reader::read($calendarData);

        $searchResult = $this->manager->simpleQuery('caldav',$this->calendarObjectTableName, ['uid' => $vCal->VEVENT->UID->__toString()]);

        if ($searchResult == null) {
            return;
        }

        $id = $searchResult[0]['_id'];

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
            'lobject' => $this->converter->extractToLobject($vCal),
        ];

        $this->manager->simpleIndex('caldav',$this->calendarObjectTableName, $id, $values);

        $this->addChange($calendarId, $objectUri, 2);
    }

    public function deleteCalendarObject($calendarId, $objectUri)
    {
        $searchResult = $this->manager->simpleQuery('caldav',$this->calendarObjectTableName, ['uri' => $objectUri]);

        if ($searchResult == null) {
            return;
        }

        $id = $searchResult[0]['_id'];

        $this->manager->simpleDelete('caldav',$this->calendarObjectTableName, $id);

        $this->addChange($calendarId, $objectUri, 3);
    }

    /**
     * Performs a calendar-query on the contents of this calendar.
     *
     * The calendar-query is defined in RFC4791 : CalDAV. Using the
     * calendar-query it is possible for a client to request a specific set of
     * object, based on contents of iCalendar properties, date-ranges and
     * iCalendar component types (VTODO, VEVENT).
     *
     * This method should just return a list of (relative) urls that match this
     * query.
     *
     * The list of filters are specified as an array. The exact array is
     * documented by \Sabre\CalDAV\CalendarQueryParser.
     *
     * Note that it is extremely likely that getCalendarObject for every path
     * returned from this method will be called almost immediately after. You
     * may want to anticipate this to speed up these requests.
     *
     * This method provides a default implementation, which parses *all* the
     * iCalendar objects in the specified calendar.
     *
     * This default may well be good enough for personal use, and calendars
     * that aren't very large. But if you anticipate high usage, big calendars
     * or high loads, you are strongly adviced to optimize certain paths.
     *
     * The best way to do so is override this method and to optimize
     * specifically for 'common filters'.
     *
     * Requests that are extremely common are:
     *   * requests for just VEVENTS
     *   * requests for just VTODO
     *   * requests with a time-range-filter on a VEVENT.
     *
     * ..and combinations of these requests. It may not be worth it to try to
     * handle every possible situation and just rely on the (relatively
     * easy to use) CalendarQueryValidator to handle the rest.
     *
     * Note that especially time-range-filters may be difficult to parse. A
     * time-range filter specified on a VEVENT must for instance also handle
     * recurrence rules correctly.
     * A good example of how to interprete all these filters can also simply
     * be found in \Sabre\CalDAV\CalendarQueryFilter. This class is as correct
     * as possible, so it gives you a good idea on what type of stuff you need
     * to think of.
     *
     * This specific implementation (for the PDO) backend optimizes filters on
     * specific components, and VEVENT time-ranges.
     *
     * @param string $calendarId
     * @param array  $filters
     *
     * @return array
     */
    public function calendarQuery($calendarId, array $filters)
    {

        /*$componentType = null;
        $requirePostFilter = true;
        $timeRange = null;

        // if no filters were specified, we don't need to filter after a query
        if (!$filters['prop-filters'] && !$filters['comp-filters']) {
            $requirePostFilter = false;
        }

        // Figuring out if there's a component filter
        if (count($filters['comp-filters']) > 0 && !$filters['comp-filters'][0]['is-not-defined']) {
            $componentType = $filters['comp-filters'][0]['name'];

            // Checking if we need post-filters
            if (!$filters['prop-filters'] && !$filters['comp-filters'][0]['comp-filters'] && !$filters['comp-filters'][0]['time-range'] && !$filters['comp-filters'][0]['prop-filters']) {
                $requirePostFilter = false;
            }
            // There was a time-range filter
            if ($componentType == 'VEVENT' && isset($filters['comp-filters'][0]['time-range'])) {
                $timeRange = $filters['comp-filters'][0]['time-range'];

                // If start time OR the end time is not specified, we can do a
                // 100% accurate mysql query.
                if (!$filters['prop-filters'] && !$filters['comp-filters'][0]['comp-filters'] && !$filters['comp-filters'][0]['prop-filters'] && (!$timeRange['start'] || !$timeRange['end'])) {
                    $requirePostFilter = false;
                }
            }

        }

        if ($requirePostFilter) {
            $query = "SELECT uri, calendardata FROM ".$this->calendarObjectTableName." WHERE calendarid = :calendarid";
        } else {
            $query = "SELECT uri FROM ".$this->calendarObjectTableName." WHERE calendarid = :calendarid";
        }

        $values = [
            'calendarid' => $calendarId,
        ];

        if ($componentType) {
            $query.=" AND componenttype = :componenttype";
            $values['componenttype'] = $componentType;
        }

        if ($timeRange && $timeRange['start']) {
            $query.=" AND lastoccurence > :startdate";
            $values['startdate'] = $timeRange['start']->getTimeStamp();
        }
        if ($timeRange && $timeRange['end']) {
            $query.=" AND firstoccurence < :enddate";
            $values['enddate'] = $timeRange['end']->getTimeStamp();
        }

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($values);

        $result = [];
        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            if ($requirePostFilter) {
                if (!$this->validateFilterForObject($row, $filters)) {
                    continue;
                }
            }
            $result[] = $row['uri'];

        }

        return $result;*/
    }

    public function getCalendarObjectByUID($principalUri, $uid)
    {
        $searchResult = $this->manager->simpleQuery('caldav',$this->calendarTableName, ['principaluri' => $principalUri]);

        if (!$searchResult) {
            return;
        }

        $calendarsUri = [];

        foreach ($searchResult as $cal) {
            $calendarsUri[$cal['_id']] = $cal['_source']['uri'];
        }

        $searchResult = $this->manager->simpleQuery('caldav',$this->calendarObjectTableName, ['calendarid' => array_keys($calendarsUri)]);

        if (!$searchResult) {
            return;
        }

        return $calendarsUri[$searchResult['_source']['calendarid']].'/'.$searchResult['_source']['uri'];
    }

    public function getChangesForCalendar($calendarId, $syncToken, $syncLevel, $limit = null)
    {
        $getResult = $this->manager->simpleQuery('caldav',$this->calendarTableName, ['id' => $calendarId]);

        if (!$getResult['found']) {
            return;
        }

        $currentToken = $getResult['_source']['synctoken'];

        $result = [
            'syncToken' => $currentToken,
            'added' => [],
            'modified' => [],
            'deleted' => [],
        ];

        if ($syncToken) {
            $params['query']['filtered']['filter']['bool']['must']['term']['calendarid'] = $calendarId;
            $params['query']['filtered']['filter']['bool']['must']['range']['syncToken'] = ['gte' => $syncToken, 'lt' => $currentToken];

            $searchResult = $this->manager->complexQuery('caldav',$this->calendarChangesTableName, $params, ['synctoken' => 'asc']);

            if (!$searchResult) {
                return $result;
            }

            $changes = [];

            foreach ($searchResult as $ch) {
                $changes[$ch['uri']] = $ch['operation'];
            }

            foreach ($changes as $uri => $operation) {
                switch ($operation) {
                    case 1:
                        $result['added'][] = $uri;
                        break;
                    case 2:
                        $result['modified'][] = $uri;
                        break;
                    case 3:
                        $result['deleted'][] = $uri;
                        break;
                }
            }
        } else {
            $searchResult = $this->manager->simpleRequest('caldav',$this->calendarObjectTableName, ['calendarid' => $calendarId]);

            if (!$searchResult) {
                return;
            }

            foreach ($searchResult as $ch) {
                $result['added'][] = $ch['_source']['uri'];
            }
        }

        return $result;
    }

    protected function addChange($calendarId, $objectUri, $operation)
    {
        $synctoken = $this->manager->synctokenOf($calendarId);

        $values = [
            //'id' => $id,
            'uri' => $objectUri,
            'synctoken' => $synctoken,
            'calendarid' => $calendarId,
            'operation' => $operation,
        ];

        $this->manager->simpleIndex('caldav',$this->calendarChangesTableName, null, $values);

        $this->manager->incSynctokenOf($calendarId);
    }

    /**
     * Returns a list of subscriptions for a principal.
     *
     * Every subscription is an array with the following keys:
     *  * id, a unique id that will be used by other functions to modify the
     *    subscription. This can be the same as the uri or a database key.
     *  * uri. This is just the 'base uri' or 'filename' of the subscription.
     *  * principaluri. The owner of the subscription. Almost always the same as
     *    principalUri passed to this method.
     *  * source. Url to the actual feed
     *
     * Furthermore, all the subscription info must be returned too:
     *
     * 1. {DAV:}displayname
     * 2. {http://apple.com/ns/ical/}refreshrate
     * 3. {http://calendarserver.org/ns/}subscribed-strip-todos (omit if todos
     *    should not be stripped).
     * 4. {http://calendarserver.org/ns/}subscribed-strip-alarms (omit if alarms
     *    should not be stripped).
     * 5. {http://calendarserver.org/ns/}subscribed-strip-attachments (omit if
     *    attachments should not be stripped).
     * 7. {http://apple.com/ns/ical/}calendar-color
     * 8. {http://apple.com/ns/ical/}calendar-order
     * 9. {urn:ietf:params:xml:ns:caldav}supported-calendar-component-set
     *    (should just be an instance of
     *    Sabre\CalDAV\Property\SupportedCalendarComponentSet, with a bunch of
     *    default components).
     *
     * @param string $principalUri
     *
     * @return array
     */
    public function getSubscriptionsForUser($principalUri)
    {

        /*$fields = array_values($this->subscriptionPropertyMap);
        $fields[] = 'id';
        $fields[] = 'uri';
        $fields[] = 'source';
        $fields[] = 'principaluri';
        $fields[] = 'lastmodified';

        // Making fields a comma-delimited list
        $fields = implode(', ', $fields);
        $stmt = $this->pdo->prepare("SELECT " . $fields . " FROM " . $this->calendarSubscriptionsTableName . " WHERE principaluri = ? ORDER BY calendarorder ASC");
        $stmt->execute([$principalUri]);

        $subscriptions = [];
        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {

            $subscription = [
                'id'           => $row['id'],
                'uri'          => $row['uri'],
                'principaluri' => $row['principaluri'],
                'source'       => $row['source'],
                'lastmodified' => $row['lastmodified'],

                '{' . CalDAV\Plugin::NS_CALDAV . '}supported-calendar-component-set' => new CalDAV\Property\SupportedCalendarComponentSet(['VTODO', 'VEVENT']),
            ];

            foreach($this->subscriptionPropertyMap as $xmlName=>$dbName) {
                if (!is_null($row[$dbName])) {
                    $subscription[$xmlName] = $row[$dbName];
                }
            }

            $subscriptions[] = $subscription;

        }

        return $subscriptions;*/

        return [];
    }

    /**
     * Creates a new subscription for a principal.
     *
     * If the creation was a success, an id must be returned that can be used to reference
     * this subscription in other methods, such as updateSubscription.
     *
     * @param string $principalUri
     * @param string $uri
     * @param array  $properties
     *
     * @return mixed
     */
    public function createSubscription($principalUri, $uri, array $properties)
    {

        /*$fieldNames = [
            'principaluri',
            'uri',
            'source',
            'lastmodified',
        ];

        if (!isset($properties['{http://calendarserver.org/ns/}source'])) {
            throw new Forbidden('The {http://calendarserver.org/ns/}source property is required when creating subscriptions');
        }

        $values = [
            ':principaluri' => $principalUri,
            ':uri'          => $uri,
            ':source'       => $properties['{http://calendarserver.org/ns/}source']->getHref(),
            ':lastmodified' => time(),
        ];

        foreach($this->subscriptionPropertyMap as $xmlName=>$dbName) {
            if (isset($properties[$xmlName])) {

                $values[':' . $dbName] = $properties[$xmlName];
                $fieldNames[] = $dbName;
            }
        }

        $stmt = $this->pdo->prepare("INSERT INTO " . $this->calendarSubscriptionsTableName . " (".implode(', ', $fieldNames).") VALUES (".implode(', ',array_keys($values)).")");
        $stmt->execute($values);

        return $this->pdo->lastInsertId();*/
    }

    /**
     * Updates a subscription.
     *
     * The list of mutations is stored in a Sabre\DAV\PropPatch object.
     * To do the actual updates, you must tell this object which properties
     * you're going to process with the handle() method.
     *
     * Calling the handle method is like telling the PropPatch object "I
     * promise I can handle updating this property".
     *
     * Read the PropPatch documenation for more info and examples.
     *
     * @param mixed                $subscriptionId
     * @param \Sabre\DAV\PropPatch $propPatch
     */
    public function updateSubscription($subscriptionId, DAV\PropPatch $propPatch)
    {

        /*$supportedProperties = array_keys($this->subscriptionPropertyMap);
        $supportedProperties[] = '{http://calendarserver.org/ns/}source';

        $propPatch->handle($supportedProperties, function($mutations) use ($subscriptionId) {

            $newValues = [];

            foreach($mutations as $propertyName=>$propertyValue) {

                if ($propertyName === '{http://calendarserver.org/ns/}source') {
                    $newValues['source'] = $propertyValue->getHref();
                } else {
                    $fieldName = $this->subscriptionPropertyMap[$propertyName];
                    $newValues[$fieldName] = $propertyValue;
                }

            }

            // Now we're generating the sql query.
            $valuesSql = [];
            foreach($newValues as $fieldName=>$value) {
                $valuesSql[] = $fieldName . ' = ?';
            }

            $stmt = $this->pdo->prepare("UPDATE " . $this->calendarSubscriptionsTableName . " SET " . implode(', ',$valuesSql) . ", lastmodified = ? WHERE id = ?");
            $newValues['lastmodified'] = time();
            $newValues['id'] = $subscriptionId;
            $stmt->execute(array_values($newValues));

            return true;

        });*/
    }

    /**
     * Deletes a subscription.
     *
     * @param mixed $subscriptionId
     */
    public function deleteSubscription($subscriptionId)
    {

        /*$stmt = $this->pdo->prepare('DELETE FROM ' . $this->calendarSubscriptionsTableName . ' WHERE id = ?');
        $stmt->execute([$subscriptionId]);*/
    }

    /**
     * Returns a single scheduling object.
     *
     * The returned array should contain the following elements:
     *   * uri - A unique basename for the object. This will be used to
     *           construct a full uri.
     *   * calendardata - The iCalendar object
     *   * lastmodified - The last modification date. Can be an int for a unix
     *                    timestamp, or a PHP DateTime object.
     *   * etag - A unique token that must change if the object changed.
     *   * size - The size of the object, in bytes.
     *
     * @param string $principalUri
     * @param string $objectUri
     *
     * @return array
     */
    public function getSchedulingObject($principalUri, $objectUri)
    {

        /*$stmt = $this->pdo->prepare('SELECT uri, calendardata, lastmodified, etag, size FROM '.$this->schedulingObjectTableName.' WHERE principaluri = ? AND uri = ?');
        $stmt->execute([$principalUri, $objectUri]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if(!$row) return null;

        return [
            'uri'          => $row['uri'],
            'calendardata' => $row['calendardata'],
            'lastmodified' => $row['lastmodified'],
            'etag'         => '"' . $row['etag'] . '"',
            'size'         => (int)$row['size'],
         ];*/

         return [];
    }

    /**
     * Returns all scheduling objects for the inbox collection.
     *
     * These objects should be returned as an array. Every item in the array
     * should follow the same structure as returned from getSchedulingObject.
     *
     * The main difference is that 'calendardata' is optional.
     *
     * @param string $principalUri
     *
     * @return array
     */
    public function getSchedulingObjects($principalUri)
    {

        /*$stmt = $this->pdo->prepare('SELECT id, calendardata, uri, lastmodified, etag, size FROM '.$this->schedulingObjectTableName.' WHERE principaluri = ?');
        $stmt->execute([$principalUri]);

        $result = [];
        foreach($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $result[] = [
                'calendardata' => $row['calendardata'],
                'uri'          => $row['uri'],
                'lastmodified' => $row['lastmodified'],
                'etag'         => '"' . $row['etag'] . '"',
                'size'         => (int)$row['size'],
            ];
        }

        return $result;*/

        return [];
    }

    /**
     * Deletes a scheduling object.
     *
     * @param string $principalUri
     * @param string $objectUri
     */
    public function deleteSchedulingObject($principalUri, $objectUri)
    {

        /*$stmt = $this->pdo->prepare('DELETE FROM '.$this->schedulingObjectTableName.' WHERE principaluri = ? AND uri = ?');
        $stmt->execute([$principalUri, $objectUri]);*/
    }

    /**
     * Creates a new scheduling object. This should land in a users' inbox.
     *
     * @param string $principalUri
     * @param string $objectUri
     * @param string $objectData
     */
    public function createSchedulingObject($principalUri, $objectUri, $objectData)
    {

        /*$stmt = $this->pdo->prepare('INSERT INTO '.$this->schedulingObjectTableName.' (principaluri, calendardata, uri, lastmodified, etag, size) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$principalUri, $objectData, $objectUri, time(), md5($objectData), strlen($objectData) ]);*/
    }
}
