<?php

namespace AppBundle\Service;

use Sabre\VObject;

class FormatConverter
{

    private $supportedFormat = ["icalendar","json","xml","csv"];

    public function convert($formatSource,$formatDest,$data,$fix = true) {

        $formatSource = strtolower($formatSource);
        $formatDest = strtolower($formatDest);

        if ($formatSource == $formatDest) {
            throw new \Exception("Source format must not be the same as destination format.");
        }

        if (!in_array($formatSource,$this->supportedFormat)) {
            throw new \Exception("Source format not supported");
        }

        if (!in_array($formatDest,$this->supportedFormat)) {
            throw new \Exception("Destination format not supported");
        }

        $retData = null;

        if ($formatSource == "icalendar") { // iCal => Json => formatDest

            $retData = $this->convertICalToJson($data, $fix);

            switch($formatDest) {
                case "json":
                    break;
                case "xml":
                    $retData = $this->convertJsonToXML($retData);
                    break;
                case "csv":
                    $retData = $this->convertJsonToCSV($retData);
                    break;
            }

        } else if ($formatDest == "icalendar") { // formatSource => Json => iCal

            switch($formatSource) {
                case "json":
                    $retData = $data;
                    break;
                case "xml":
                    $retData = $this->convertXMLToJson($data);
                    break;
                case "csv":
                    $retData = $this->convertCSVToJson($data);
                    break;
            }

            $retData = $this->convertJsonToICal($retData,$fix);
        } else {
            if ($formatSource == "json" && $formatDest == "xml") {
                $retData = $this->convertJsonToXML($data);
            } elseif ($formatSource == "json" && $formatDest == "csv") {
                $retData = $this->convertJsonToCSV($data);
            } elseif ($formatSource == "xml" && $formatDest == "json") {
                $retData = $this->convertXMLToJson($data);
            } elseif ($formatSource == "csv" && $formatDest == "json") {
                $retData = $this->convertCSVToJson($data);
            } elseif ($formatSource == "xml" && $formatDest == "csv") {
                $retData = $this->convertXMLToJson($data);
                $retData = $this->convertJsonToCSV($retData);
            } elseif ($formatSource == "csv" && $formatDest == "xml") {
                $retData = $this->convertCSVToJson($data);
                $retData = $this->convertJsonToXML($retData);
            }
        }

        return $retData;
    }

    // --------------- Converter functions ----------------

    private function convertICalToJson($data, $fix=true) {

        if (!$data instanceof VObject\Component\VCalendar) {
            $data = VObject\Reader::read($data);
        }

        $data = $data->jsonSerialize();

        if ($fix)
        {
            $data = self::jCalFix($data);
        }
            
        return $data;

    }

    private function convertJsonToICal($data, $fix=true) {

        if ($fix)
        {
            $data = self::jCalUnfix($data);
        }
        
        return VObject\Reader::readJson($data);

    }

    private function convertXMLToJson($data) {
        throw new \Exception("Not supported yet");
    }

    private function convertJsonToXML($data) {
        throw new \Exception("Not supported yet");
    }

    private function convertCSVToJson($data) {
        throw new \Exception("Not supported yet");
    }

    private function convertJsonToCSV($data) {
        throw new \Exception("Not supported yet");
    }






    ///////////// EXTRACT /////////////

    // https://github.com/LiberTIC/ODEV2/blob/master/doc/Thibaud_Printemps2015/Modele_Evenement.md
    public $lookupTable = [
        "name"               => "SUMMARY",
        "id"                 => "UID",
        "description"        => "DESCRIPTION",
        "date_start"         => "DTSTART",
        "date_end"           => "DTEND",
        "date_created"       => "CREATED",
        "date_modified"      => "LAST-MODIFIED",
        "location_name"      => "LOCATION",
        "location_precision" => "X-ODE-LOCATION-PRECISION",
        "geo"                => "GEO",
        "location_capacity"  => "X-ODE-LOCATION-CAPACITY",
        "attendees"          => "X-ODE-ATTENDEES",
        "duration"           => "X-ODE-DURATION",
        "status"             => "STATUS",
        "promoter"           => "X-ODE-PROMOTER",
        "subevent"           => "X-ODE-SUBEVENT",
        "superevent"         => "X-ODE-SUPEREVENT",
        "url"                => "URL",
        "url_promoter"       => "X-ODE-URL-PROMOTER",
        "urls_medias"        => "X-ODE-URLS-MEDIAS",
        "language"           => "X-ODE-LANGUAGE",
        "price_standard"     => "X-ODE-PRICE-STANDARD",
        "price_reduced"      => "X-ODE-PRICE-REDUCED",
        "price_children"     => "X-ODE-PRICE-CHILDREN",
        "contact_name"       => "X-ODE-CONTACT-NAME",
        "contact_email"      => "X-ODE-CONTACT-EMAIL",
        "category"           => "X-ODE-CATEGORY",
        "tags"               => "X-ODE-TAGS"
    ];

    public function extractToVobject($lobject) {
        // TODO
    }

    public function extractToLobject($vobject) {
        $vevent = $vobject->VEVENT;

        $lobject = [];

        foreach($this->lookupTable as $jsonName => $icalName) {
            if ($data = $vevent->__get($icalName)) {
                $lobject[$jsonName] = $data->__toString();
            }
        }

        return $lobject;
    }




    // why ? Because: https://github.com/elastic/elasticsearch/issues/9282

    public static function jCalFix($data)
    {
        if ($data instanceof \stdClass)
        {
            return (array)$data;
        }

        if (!is_array($data))
        {
            return (string)$data;
        }

        $i = 1;
        $ret = [];
        foreach($data as $d) 
        {
            $ret[$i] = self::jCalFix($d);
            $i++;
        }

        return $ret;
    }

    public static function jCalUnfix($data)
    {
        if (!is_array($data))
        {
            return (string)$data;
        }

        $ret = [];
        foreach($data as $key => $d)
        {
            if (!is_string($key))
            {
                $ret[] = self::jCalUnfix($d);
            }
            else {
                $ret[$key] = self::jCalUnfix($d);
            }
        }

        return $ret;
    }
}