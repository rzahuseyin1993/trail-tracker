<?php
/*
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
 */

include_once "GeoSpatial.class.php";

$method = $_SERVER['REQUEST_METHOD'];
if ($method == 'GET') {
    if (isset($_GET["type"]) && $_GET["type"] == 'trail') {
        $trailRows = csvToArray("data/trail_lines.csv");
        echo json_encode($trailRows);
    } elseif (isset($_GET["type"]) && $_GET["type"] == 'tracker') {
        $bufferSize = 50;
        if (isset($_GET["buffer"])) {
            $bufferSize = (float) $_GET['buffer'];
        }
        $trailRows = csvToArray("data/trail_lines.csv");
        $line = array();
        foreach ($trailRows as $row) {
            $line[] = array((float) $row[3], (float) $row[4]);
        }
        $trackerRows = csvToArray("data/tracker_logs.csv");

        $geospatial = new GeoSpatial();
        if (isset($_GET["class"]) && $_GET["class"] == 'inside') {
            $results = array();
            foreach ($trackerRows as $row) {
                $point = array((float) $row[7], (float) $row[6]);
                $dist = $geospatial->pointToLineDistance($point, $line, "meters");
                if ($dist <= $bufferSize) {
                    $results[] = $row;
                }
            }
            echo json_encode($results);
        } elseif (isset($_GET["class"]) && $_GET["class"] == 'outside') {
            $results = array();
            foreach ($trackerRows as $row) {
                $point = array((float) $row[7], (float) $row[6]);
                $dist = $geospatial->pointToLineDistance($point, $line, "meters");
                if ($dist > $bufferSize) {
                    $results[] = $row;
                }
            }
            echo json_encode($results);
        }
    }

}

function csvToArray($csvFile)
{
    $file_to_read = fopen($csvFile, 'r');
    while (($row = fgetcsv($file_to_read, 1000, ",")) !== false) {
        $lines[] = $row;
    }
    fclose($file_to_read);
    return $lines;
}
