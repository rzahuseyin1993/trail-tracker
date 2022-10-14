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
    $trailRows = csvToArray("data/trail_lines.csv");
    $line = array();
    foreach ($trailRows as $row) {
        $line[] = array((float) $row[3], (float) $row[4]);
    }
    $trackerRows = csvToArray("data/tracker_logs.csv");
    $points = array();
    foreach ($trackerRows as $row) {
        $points[] = array((float) $row[7], (float) $row[6]);
    }
    $geospatial = new GeoSpatial();
    $results = $geospatial->pointsInLineBuffer($points, $line, 10, 'meters');
    echo json_encode($results);
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
