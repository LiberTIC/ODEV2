<?php

namespace AppBundle\Backend;

class ESManager
{
    private $client;

    public $index = 'caldav';

    public function __construct($client)
    {
        $this->client = $client;
    }

    /**
     * Simple Get mean we get a result with the id specified.
     */
    public function simpleGet($type, $id)
    {
        $getParams = array();
        $getParams['index'] = $this->index;
        $getParams['type'] = $type;
        $getParams['id'] = $id;

        return $this->client->get($getParams);
    }

    /**
     * Simple Query mean that it will return a result only if it find the EXACT value.
     */
    public function simpleQuery($type, $params)
    {
        $searchParams = array();
        $searchParams['index'] = $this->index;
        $searchParams['type'] = $type;

        if (count($params) == 1) {
            $searchParams['body']['query']['filtered']['filter'][(count($params[key($params)]) > 1) ? 'terms' : 'term'][key($params)] = $params[key($params)];
        } else {
            foreach ($params as $key => $value) {
                $searchParams['body']['query']['filtered']['filter']['bool']['must'][] = [count($value) > 1 ? 'terms' : 'term' => [$key => $value]];
            }
        }

        $retDoc = $this->client->search($searchParams);

        return $retDoc['hits']['hits'];
    }

    /**
     * Complex Query let you provide the parameters for the query.
     */
    public function complexQuery($type, $params, $sort = null)
    {
        $searchParams = array();
        $searchParams['index'] = $this->index;
        $searchParams['type'] = $type;
        $searchParams['body'] = $params;

        if (!$sort) {
            foreach ($sort as $name => $mode) {
                $searchParams['sort'][$name]['order'] = $mode;
            }
        }

        $retDoc = $this->client->search($searchParams);

        return $retDoc['hits']['hits'];
    }

    /**
     * Simple Index mean that we index with the parameters given
     * Return the new id.
     */
    public function simpleIndex($type, $id, $params)
    {
        $indexParams = array();
        $indexParams['index'] = $this->index;
        $indexParams['type'] = $type;
        $indexParams['id'] = $id;
        $indexParams['body'] = $params;
        $indexParams['refresh'] = true;

        $this->incIdOf($type);

        $this->client->index($indexParams);
    }

    /**
     * Delete based on id.
     */
    public function simpleDelete($type, $id)
    {
        $deleteParams = array();
        $deleteParams['index'] = $this->index;
        $deleteParams['type'] = $type;
        $deleteParams['id'] = $id;

        $this->client->delete($deleteParams);
    }

    /**
     * Return the NEXT index to be used.
     */
    public function nextIdOf($type)
    {
        return $this->simpleGet('auto_increments', 1)['_source'][$type];
    }

    /**
     * Increment the index to be used.
     */
    private function incIdOf($type)
    {
        $updateParams = array();
        $updateParams['index'] = $this->index;
        $updateParams['type'] = 'auto_increments';
        $updateParams['id'] = 1;
        $updateParams['body']['script'] = 'ctx._source.'.$type.'+=1';

        $this->client->update($updateParams);
    }

    /**
     * Return the synctoken of an object.
     */
    public function synctokenOf($calendarId)
    {
        return $this->simpleGet('calendars', $calendarId)['_source']['synctoken'];
    }

    /**
     * Increment the synctoken of an object.
     */
    public function incSynctokenOf($calendarId)
    {
        $updateParams = array();
        $updateParams['index'] = $this->index;
        $updateParams['type'] = 'calendars';
        $updateParams['id'] = $calendarId;
        $updateParams['body']['script'] = 'ctx._source.synctoken+=1';

        $this->client->update($updateParams);
    }
}
