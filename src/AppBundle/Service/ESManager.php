<?php

namespace AppBundle\Service;

class ESManager
{
    private $client;

    public function __construct($client)
    {
        $this->client = $client;
    }

    /**
     * Simple Get mean we get a result with the id specified.
     */
    public function simpleGet($index, $type, $id)
    {
        $getParams = array();
        $getParams['index'] = $index;
        $getParams['type'] = $type;
        $getParams['id'] = $id;

        return $this->client->get($getParams);
    }

    /**
     * Simple Query mean that it will return a result only if it find the EXACT value.
     */
    public function simpleQuery($index, $type, $params)
    {
        $searchParams = array();
        $searchParams['index'] = $index;
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
    public function complexQuery($index, $type, $params, $sort = null)
    {
        $searchParams = array();
        $searchParams['index'] = $index;
        $searchParams['type'] = $type;
        $searchParams['body'] = $params;

        if ($sort != null) {
            foreach ($sort as $name => $mode) {
                $searchParams['sort'][$name]['order'] = $mode;
            }
        }

        $retDoc = $this->client->search($searchParams);

        return $retDoc['hits']['hits'];
    }

    /**
     * Simple Search mean that we return ALL documents found.
     */
    public function simpleSearch($index, $type)
    {
        $searchParams = array();
        $searchParams['index'] = $index;
        $searchParams['type'] = $type;

        $retDoc = $this->client->search($searchParams);

        return $retDoc['hits']['hits'];
    }

    /**
     * Simple Index mean that we index with the parameters given
     * Return the new id.
     */
    public function simpleIndex($index, $type, $id, $params)
    {
        $indexParams = array();
        $indexParams['index'] = $index;
        $indexParams['type'] = $type;
        if ($id != null)
        {
            $indexParams['id'] = $id;
        }
        $indexParams['body'] = $params;

        $indexParams['refresh'] = true;

        //$this->incIdOf($index, $type);

        $this->client->index($indexParams);
    }

    /**
     * Delete based on id.
     */
    public function simpleDelete($index, $type, $id)
    {
        $deleteParams = array();
        $deleteParams['index'] = $index;
        $deleteParams['type'] = $type;
        $deleteParams['id'] = $id;

        $this->client->delete($deleteParams);
    }

    /**
     * Return the synctoken of an object.
     */
    public function synctokenOf($calendarId)
    {
        return $this->simpleGet('caldav','calendars', $calendarId)['_source']['synctoken'];
    }

    /**
     * Increment the synctoken of an object.
     */
    public function incSynctokenOf($calendarId)
    {
        $updateParams = array();
        $updateParams['index'] = 'caldav';
        $updateParams['type'] = 'calendars';
        $updateParams['id'] = $calendarId;
        $updateParams['body']['script'] = 'ctx._source.synctoken+=1';

        $this->client->update($updateParams);
    }
}
