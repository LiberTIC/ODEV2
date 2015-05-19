<?php

namespace AppBundle\Service;

use Sabre\VObject;

class FormatConverter
{

    private $supportedFormat = ["icalendar","json","xml","csv"];

    public function convert($formatSource,$formatDest,$data) {

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

            $retData = $this->convertICalToJson($data);

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

            $retData = $this->convertJsonToICal($retData);
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

    private function convertICalToJson($data) {

        if ($data instanceof VObject\Component\VCalendar) {
            $data = $data->jsonSerialize();
        } else {
            $data = VObject\Reader::read($data)->jsonSerialize();
        }
        return self::jCalFix($data);

    }

    private function convertJsonToICal($data) {

        $data = self::jCalUnfix($data);
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