<?php

namespace AppBundle\Backend;

class ESManager {

	private $client;

	public $index = "caldav";

	public function __construct($client) {
		$this->client = $client;
	}


	/**
	*	Simple Get mean we get a result with the id specified
	*/
	public function simpleGet($type,$id) {
		$getParams = array();
		$getParams['index'] = $this->index;
		$getParams['type'] = $type;
		$getParams['id'] = $id;

		return $client->get($getParams);
	}

	/**
	* Simple Query mean that it will return a result only if it find the EXACT value
	*/
	public function simpleQuery($type,$params) {
		$searchParams = array();
        $searchParams['index'] = $this->index;
        $searchParams['type'] = $type;

        if (count($params) == 1) {
			$searchParams['body']["query"]["filtered"]["filter"][(count($params[key($params)])>1)?"terms":"term"][key($params)] = $params[key($params)];
		} else {
			foreach($params as $key => $value) {
				$searchParams['body']["query"]["filtered"]["filter"]["bool"]["must"][] = [count($value)>1?"terms":"term" => [$key => $value]];
			}
		}

        $retDoc = $this->client->search($searchParams);

        return $retDoc["hits"]["hits"];
	}

	/**
	* Complex Query let you provide the parameters for the query
	*/
	public function complexQuery($type,$params,$sort = null) {
		$searchParams = array();
        $searchParams['index'] = $this->index;
        $searchParams['type'] = $type;
        $searchParams['body'] = $params;

        if (!$sort) {
        	foreach($sort as $name => $mode) {
        		$searchParams['sort'][$name]['order'] = $mode;
        	}
        }

        $retDoc = $this->client->search($searchParams);

        return $retDoc["hits"]["hits"];
	}
}