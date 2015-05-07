<?php

namespace AppBundle\Backend;

use
    Sabre\VObject,
    Sabre\CalDAV,
    Sabre\DAV,
    Sabre\DAV\Exception\Forbidden,
    Sabre\CalDAV\Backend\AbstractBackend,
    Sabre\CalDAV\Backend\SyncSupport,
    Sabre\CalDAV\Backend\SubscriptionSupport,
    Sabre\CalDAV\Backend\SchedulingSupport;

class ES extends AbstractBackend implements SyncSupport, SubscriptionSupport, SchedulingSupport {

    const MAX_DATE = '2038-01-01';

    protected $pdo;
    protected $client;

    public $index = "caldav";

    public $calendarTableName = 'calendars';

    public $calendarObjectTableName = 'calendarobjects';

    public $calendarChangesTableName = 'calendarchanges';

    public $schedulingObjectTableName = 'schedulingobjects';

    public $calendarSubscriptionsTableName = 'calendarsubscriptions';

    public $propertyMap = [
        '{DAV:}displayname'                          => 'displayname',
        '{urn:ietf:params:xml:ns:caldav}calendar-description' => 'description',
        '{urn:ietf:params:xml:ns:caldav}calendar-timezone'    => 'timezone',
        '{http://apple.com/ns/ical/}calendar-order'  => 'calendarorder',
        '{http://apple.com/ns/ical/}calendar-color'  => 'calendarcolor',
    ];

    public $subscriptionPropertyMap = [
        '{DAV:}displayname'                                           => 'displayname',
        '{http://apple.com/ns/ical/}refreshrate'                      => 'refreshrate',
        '{http://apple.com/ns/ical/}calendar-order'                   => 'calendarorder',
        '{http://apple.com/ns/ical/}calendar-color'                   => 'calendarcolor',
        '{http://calendarserver.org/ns/}subscribed-strip-todos'       => 'striptodos',
        '{http://calendarserver.org/ns/}subscribed-strip-alarms'      => 'stripalarms',
        '{http://calendarserver.org/ns/}subscribed-strip-attachments' => 'stripattachments',
    ];

    function __construct($client, \PDO $pdo) {

        $this->client = $client;
        $this->pdo = $pdo;

        $this->manager = new ESManager($client);
    }

    function getCalendarsForUser($principalUri) {

        $searchResult = $this->manager->simpleQuery($this->calendarTableName,["principaluri" => $principalUri]);

        if (!$searchResult) return [];

        $calendars = [];

        foreach($searchResult as $cal) {

        	$src = $cal["_source"];

	        $calendar = array('id' => $cal["_id"],
	        		  'uri' => $src["uri"],
	        		  'principaluri' => $src["principaluri"],
	        		  '{' . CalDAV\Plugin::NS_CALENDARSERVER . '}getctag' => 'http://sabre.io/ns/sync/' . $src["synctoken"],
	        		  '{http://sabredav.org/ns}sync-token' => $src["synctoken"],
	        		  '{' . CalDAV\Plugin::NS_CALDAV . '}supported-calendar-component-set' => new CalDAV\Property\SupportedCalendarComponentSet($src["components"]),
	        		  '{' . CalDAV\Plugin::NS_CALDAV . '}schedule-calendar-transp' => new CalDAV\Property\ScheduleCalendarTransp($src['transparent']?'transparent':'opaque'),
	        		  '{DAV:}displayname' => $src["displayname"],
	        		  '{urn:ietf:params:xml:ns:caldav}calendar-description' => $src["description"],
	        		  '{urn:ietf:params:xml:ns:caldav}calendar-timezone' => $src["timezone"],
					  '{http://apple.com/ns/ical/}calendar-order' => $src["calendarorder"],
					  '{http://apple.com/ns/ical/}calendar-color' =>  $src["calendarcolor"]
	        	);

			$calendars[] = $calendar;
	    }

        return $calendars;

    }

    /**
     * Creates a new calendar for a principal.
     *
     * If the creation was a success, an id must be returned that can be used
     * to reference this calendar in other methods, such as updateCalendar.
     *
     * @param string $principalUri
     * @param string $calendarUri
     * @param array $properties
     * @return string
     */
    function createCalendar($principalUri, $calendarUri, array $properties) {

        /*$fieldNames = [
            'principaluri',
            'uri',
            'synctoken',
            'transparent',
        ];
        $values = [
            ':principaluri' => $principalUri,
            ':uri'          => $calendarUri,
            ':synctoken'    => 1,
            ':transparent'  => 0,
        ];

        // Default value
        $sccs = '{urn:ietf:params:xml:ns:caldav}supported-calendar-component-set';
        $fieldNames[] = 'components';
        if (!isset($properties[$sccs])) {
            $values[':components'] = 'VEVENT,VTODO';
        } else {
            if (!($properties[$sccs] instanceof CalDAV\Property\SupportedCalendarComponentSet)) {
                throw new DAV\Exception('The ' . $sccs . ' property must be of type: \Sabre\CalDAV\Property\SupportedCalendarComponentSet');
            }
            $values[':components'] = implode(',',$properties[$sccs]->getValue());
        }
        $transp = '{' . CalDAV\Plugin::NS_CALDAV . '}schedule-calendar-transp';
        if (isset($properties[$transp])) {
            $values[':transparent'] = $properties[$transp]->getValue()==='transparent';
        }

        foreach($this->propertyMap as $xmlName=>$dbName) {
            if (isset($properties[$xmlName])) {

                $values[':' . $dbName] = $properties[$xmlName];
                $fieldNames[] = $dbName;
            }
        }

        $stmt = $this->pdo->prepare("INSERT INTO ".$this->calendarTableName." (".implode(', ', $fieldNames).") VALUES (".implode(', ',array_keys($values)).")");
        $stmt->execute($values);

        return $this->pdo->lastInsertId();*/
    }

    /**
     * Updates properties for a calendar.
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
     * @param string $calendarId
     * @param \Sabre\DAV\PropPatch $propPatch
     * @return void
     */
    function updateCalendar($calendarId, \Sabre\DAV\PropPatch $propPatch) {

        /*$supportedProperties = array_keys($this->propertyMap);
        $supportedProperties[] = '{' . CalDAV\Plugin::NS_CALDAV . '}schedule-calendar-transp';

        $propPatch->handle($supportedProperties, function($mutations) use ($calendarId) {
            $newValues = [];
            foreach($mutations as $propertyName=>$propertyValue) {

                switch($propertyName) {
                    case '{' . CalDAV\Plugin::NS_CALDAV . '}schedule-calendar-transp' :
                        $fieldName = 'transparent';
                        $newValues[$fieldName] = $propertyValue->getValue()==='transparent';
                        break;
                    default :
                        $fieldName = $this->propertyMap[$propertyName];
                        $newValues[$fieldName] = $propertyValue;
                        break;
                }

            }
            $valuesSql = [];
            foreach($newValues as $fieldName=>$value) {
                $valuesSql[] = $fieldName . ' = ?';
            }

            $stmt = $this->pdo->prepare("UPDATE " . $this->calendarTableName . " SET " . implode(', ',$valuesSql) . " WHERE id = ?");
            $newValues['id'] = $calendarId;
            $stmt->execute(array_values($newValues));

            $this->addChange($calendarId, "", 2);

            return true;

        });*/

    }

    /**
     * Delete a calendar and all it's objects
     *
     * @param string $calendarId
     * @return void
     */
    function deleteCalendar($calendarId) {

        /*$stmt = $this->pdo->prepare('DELETE FROM '.$this->calendarObjectTableName.' WHERE calendarid = ?');
        $stmt->execute([$calendarId]);

        $stmt = $this->pdo->prepare('DELETE FROM '.$this->calendarTableName.' WHERE id = ?');
        $stmt->execute([$calendarId]);

        $stmt = $this->pdo->prepare('DELETE FROM '.$this->calendarChangesTableName.' WHERE id = ?');
        $stmt->execute([$calendarId]);*/

    }

    function getCalendarObjects($calendarId) {

        $searchResult = $this->manager->simpleQuery($this->calendarObjectTableName,["calendarid" => $calendarId]);

        if (!$searchResult) return [];

        $objects = [];

        foreach($searchResult as $obj) {
        	$src = $obj["_source"];

        	 $object = [
	        	'id' => $obj["_id"],
	        	'uri' => $src["uri"],
	        	'lastmodified' => $src["lastmodified"],
	        	'etag' => '"' . $src['etag'] . '"',
	        	'calendarid' => $src["calendarid"],
	        	'size' => $src["size"],
	        	'component' => strtolower($src["component"])
	        ];

	        $objects[] = $object;
        }

        return $objects;

    }

    function getCalendarObject($calendarId,$objectUri) {

        $hit = $this->manager->simpleQuery($this->calendarObjectTableName,["calendarid" => $calendarId, "uri" => $objectUri]);

        if ($hit == null) return null;

        $row = $hit[0]["_source"];

        return [
            'id'            => $hit[0]['_id'],
            'uri'           => $row['uri'],
            'lastmodified'  => $row['lastmodified'],
            'etag'          => '"' . $row['etag'] . '"',
            'calendarid'    => $row['calendarid'],
            'size'          => $row['size'],
            'calendardata'  => $row['calendardata'],
            'component'     => strtolower($row['component'])
         ];
    }

    function getMultipleCalendarObjects($calendarId, array $uris) {

        $searchResult = $this->manager->simpleQuery($this->calendarObjectTableName,["calendarid" => $calendarId, "uri" => $uris]);

        if (!$searchResult) return [];

        $objects = [];

        foreach($searchResult as $obj) {
            $src = $obj["_source"];

             $object = [
                'id' => $obj["_id"],
                'uri' => $src["uri"],
                'lastmodified' => $src["lastmodified"],
                'etag' => '"' . $src['etag'] . '"',
                'calendarid' => $src["calendarid"],
                'size' => $src["size"],
                'component' => strtolower($src["component"])
            ];

            $objects[] = $object;
        }

        return $objects;
    }


    /**
     * Creates a new calendar object.
     *
     * The object uri is only the basename, or filename and not a full path.
     *
     * It is possible return an etag from this function, which will be used in
     * the response to this PUT request. Note that the ETag must be surrounded
     * by double-quotes.
     *
     * However, you should only really return this ETag if you don't mangle the
     * calendar-data. If the result of a subsequent GET to this object is not
     * the exact same as this request body, you should omit the ETag.
     *
     * @param mixed $calendarId
     * @param string $objectUri
     * @param string $calendarData
     * @return string|null
     */
    function createCalendarObject($calendarId,$objectUri,$calendarData) {

        /*$extraData = $this->getDenormalizedData($calendarData);

        $stmt = $this->pdo->prepare('INSERT INTO '.$this->calendarObjectTableName.' (calendarid, uri, calendardata, lastmodified, etag, size, componenttype, firstoccurence, lastoccurence, uid) VALUES (?,?,?,?,?,?,?,?,?,?)');
        $stmt->execute([
            $calendarId,
            $objectUri,
            $calendarData,
            time(),
            $extraData['etag'],
            $extraData['size'],
            $extraData['componentType'],
            $extraData['firstOccurence'],
            $extraData['lastOccurence'],
            $extraData['uid'],
        ]);
        $this->addChange($calendarId, $objectUri, 1);

        return '"' . $extraData['etag'] . '"';*/

    }

    /**
     * Updates an existing calendarobject, based on it's uri.
     *
     * The object uri is only the basename, or filename and not a full path.
     *
     * It is possible return an etag from this function, which will be used in
     * the response to this PUT request. Note that the ETag must be surrounded
     * by double-quotes.
     *
     * However, you should only really return this ETag if you don't mangle the
     * calendar-data. If the result of a subsequent GET to this object is not
     * the exact same as this request body, you should omit the ETag.
     *
     * @param mixed $calendarId
     * @param string $objectUri
     * @param string $calendarData
     * @return string|null
     */
    function updateCalendarObject($calendarId,$objectUri,$calendarData) {

        /*$extraData = $this->getDenormalizedData($calendarData);

        $stmt = $this->pdo->prepare('UPDATE '.$this->calendarObjectTableName.' SET calendardata = ?, lastmodified = ?, etag = ?, size = ?, componenttype = ?, firstoccurence = ?, lastoccurence = ?, uid = ? WHERE calendarid = ? AND uri = ?');
        $stmt->execute([$calendarData, time(), $extraData['etag'], $extraData['size'], $extraData['componentType'], $extraData['firstOccurence'], $extraData['lastOccurence'], $extraData['uid'], $calendarId, $objectUri]);

        $this->addChange($calendarId, $objectUri, 2);

        return '"' . $extraData['etag'] . '"';*/

    }

    /**
     * Parses some information from calendar objects, used for optimized
     * calendar-queries.
     *
     * Returns an array with the following keys:
     *   * etag - An md5 checksum of the object without the quotes.
     *   * size - Size of the object in bytes
     *   * componentType - VEVENT, VTODO or VJOURNAL
     *   * firstOccurence
     *   * lastOccurence
     *   * uid - value of the UID property
     *
     * @param string $calendarData
     * @return array
     */
    protected function getDenormalizedData($calendarData) {

        /*$vObject = VObject\Reader::read($calendarData);
        $componentType = null;
        $component = null;
        $firstOccurence = null;
        $lastOccurence = null;
        $uid = null;
        foreach($vObject->getComponents() as $component) {
            if ($component->name!=='VTIMEZONE') {
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
                $it = new VObject\RecurrenceIterator($vObject, (string)$component->UID);
                $maxDate = new \DateTime(self::MAX_DATE);
                if ($it->isInfinite()) {
                    $lastOccurence = $maxDate->getTimeStamp();
                } else {
                    $end = $it->getDtEnd();
                    while($it->valid() && $end < $maxDate) {
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
            'lastOccurence'  => $lastOccurence,
            'uid' => $uid,
        ];*/

    }

    /**
     * Deletes an existing calendar object.
     *
     * The object uri is only the basename, or filename and not a full path.
     *
     * @param string $calendarId
     * @param string $objectUri
     * @return void
     */
    function deleteCalendarObject($calendarId,$objectUri) {

        /*$stmt = $this->pdo->prepare('DELETE FROM '.$this->calendarObjectTableName.' WHERE calendarid = ? AND uri = ?');
        $stmt->execute([$calendarId, $objectUri]);

        $this->addChange($calendarId, $objectUri, 3);*/

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
     * @param array $filters
     * @return array
     */
    function calendarQuery($calendarId, array $filters) {

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

    /**
     * Searches through all of a users calendars and calendar objects to find
     * an object with a specific UID.
     *
     * This method should return the path to this object, relative to the
     * calendar home, so this path usually only contains two parts:
     *
     * calendarpath/objectpath.ics
     *
     * If the uid is not found, return null.
     *
     * This method should only consider * objects that the principal owns, so
     * any calendars owned by other principals that also appear in this
     * collection should be ignored.
     *
     * @param string $principalUri
     * @param string $uid
     * @return string|null
     */
    function getCalendarObjectByUID($principalUri, $uid) {

        $searchResult = $this->manager->simpleQuery($this->calendarTableName,["principaluri" => $principalUri]);

        if (!$searchResult) return null;

        $calendarsUri = [];

        foreach($searchResult as $cal) {
            $calendarsUri[$cal["_id"]] = $cal["_source"]["uri"];
        }

        $searchResult = $this->manager->simpleQuery($this->calendarObjectTableName,["calendarid" => array_keys($calendarsUri)]);

        if (!$searchResult) return null;

        return $calendarsUri[$searchResult["_source"]["calendarid"]] . '/' . $searchResult["_source"]["uri"];

    }

    /**
     * The getChanges method returns all the changes that have happened, since
     * the specified syncToken in the specified calendar.
     *
     * This function should return an array, such as the following:
     *
     * [
     *   'syncToken' => 'The current synctoken',
     *   'added'   => [
     *      'new.txt',
     *   ],
     *   'modified'   => [
     *      'modified.txt',
     *   ],
     *   'deleted' => [
     *      'foo.php.bak',
     *      'old.txt'
     *   ]
     * ];
     *
     * The returned syncToken property should reflect the *current* syncToken
     * of the calendar, as reported in the {http://sabredav.org/ns}sync-token
     * property this is needed here too, to ensure the operation is atomic.
     *
     * If the $syncToken argument is specified as null, this is an initial
     * sync, and all members should be reported.
     *
     * The modified property is an array of nodenames that have changed since
     * the last token.
     *
     * The deleted property is an array with nodenames, that have been deleted
     * from collection.
     *
     * The $syncLevel argument is basically the 'depth' of the report. If it's
     * 1, you only have to report changes that happened only directly in
     * immediate descendants. If it's 2, it should also include changes from
     * the nodes below the child collections. (grandchildren)
     *
     * The $limit argument allows a client to specify how many results should
     * be returned at most. If the limit is not specified, it should be treated
     * as infinite.
     *
     * If the limit (infinite or not) is higher than you're willing to return,
     * you should throw a Sabre\DAV\Exception\TooMuchMatches() exception.
     *
     * If the syncToken is expired (due to data cleanup) or unknown, you must
     * return null.
     *
     * The limit is 'suggestive'. You are free to ignore it.
     *
     * @param string $calendarId
     * @param string $syncToken
     * @param int $syncLevel
     * @param int $limit
     * @return array
     */
    function getChangesForCalendar($calendarId, $syncToken, $syncLevel, $limit = null) {

        $getResult = $this->manager->simpleGet($this->calendarTableName,$calendarid);

        if (!$getResult["found"]) return null;

        $currentToken = $getResult["_source"]["synctoken"];

        $result = [
            'syncToken' => $currentToken,
            'added'     => [],
            'modified'  => [],
            'deleted'   => []
        ];

        if ($syncToken) {

            $params["query"]["filtered"]["filter"]["bool"]["must"]["term"]["calendarid"] = $calendarId;
            $params["query"]["filtered"]["filter"]["bool"]["must"]["range"]["syncToken"] = ["gte" => $syncToken, "lt" => $currentToken];

            $searchResult = $this->manager->complexQuery($this->calendarChangesTableName,$params,["synctoken" => "asc"]);

            if (!$searchResult) return $result;

            $changes = [];

            foreach($searchResult as $ch) {
                $changes[$ch["uri"]] = $ch["operation"];
            }

            foreach($changes as $uri => $operation) {

                switch($operation) {
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
            $searchResult = $this->manager->simpleRequest($this->calendarObjectTableName,["calendarid" => $calendarId]);

            if (!$searchResult) return null;

            foreach($searchResult as $ch) {
                $result['added'][] = $ch["_source"]["uri"];
            }
        }

        return $result;
    }

    /**
     * Adds a change record to the calendarchanges table.
     *
     * @param mixed $calendarId
     * @param string $objectUri
     * @param int $operation 1 = add, 2 = modify, 3 = delete.
     * @return void
     */
    protected function addChange($calendarId, $objectUri, $operation) {

        /*$stmt = $this->pdo->prepare('INSERT INTO ' . $this->calendarChangesTableName .' (uri, synctoken, calendarid, operation) SELECT ?, synctoken, ?, ? FROM ' . $this->calendarTableName .' WHERE id = ?');
        $stmt->execute([
            $objectUri,
            $calendarId,
            $operation,
            $calendarId
        ]);
        $stmt = $this->pdo->prepare('UPDATE ' . $this->calendarTableName . ' SET synctoken = synctoken + 1 WHERE id = ?');
        $stmt->execute([
            $calendarId
        ]);*/

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
     * @return array
     */
    function getSubscriptionsForUser($principalUri) {

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
     * @param array $properties
     * @return mixed
     */
    function createSubscription($principalUri, $uri, array $properties) {

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
     * Updates a subscription
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
     * @param mixed $subscriptionId
     * @param \Sabre\DAV\PropPatch $propPatch
     * @return void
     */
    function updateSubscription($subscriptionId, DAV\PropPatch $propPatch) {

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
     * Deletes a subscription
     *
     * @param mixed $subscriptionId
     * @return void
     */
    function deleteSubscription($subscriptionId) {

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
     * @return array
     */
    function getSchedulingObject($principalUri, $objectUri) {

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
     * @return array
     */
    function getSchedulingObjects($principalUri) {

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
     * Deletes a scheduling object
     *
     * @param string $principalUri
     * @param string $objectUri
     * @return void
     */
    function deleteSchedulingObject($principalUri, $objectUri) {

        /*$stmt = $this->pdo->prepare('DELETE FROM '.$this->schedulingObjectTableName.' WHERE principaluri = ? AND uri = ?');
        $stmt->execute([$principalUri, $objectUri]);*/

    }

    /**
     * Creates a new scheduling object. This should land in a users' inbox.
     *
     * @param string $principalUri
     * @param string $objectUri
     * @param string $objectData
     * @return void
     */
    function createSchedulingObject($principalUri, $objectUri, $objectData) {

        /*$stmt = $this->pdo->prepare('INSERT INTO '.$this->schedulingObjectTableName.' (principaluri, calendardata, uri, lastmodified, etag, size) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$principalUri, $objectData, $objectUri, time(), md5($objectData), strlen($objectData) ]);*/

    }

}
