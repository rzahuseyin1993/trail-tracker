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
        $results = array();
        foreach ($trailRows as $row) {
            $results[$row[1]][] = $row;
        }
        echo json_encode($results);
    } elseif (isset($_GET["type"]) && $_GET["type"] == 'tracker') {
        $bufferSize = 50;
        if (isset($_GET["buffer"])) {
            $bufferSize = (float) $_GET['buffer'];
        }
        $trailRows = csvToArray("data/trail_lines.csv");
        $trackerRows = csvToArray("data/tracker_logs.csv");

        $lines = array();
        foreach ($trailRows as $row) {
            $lines[$row[1]][] = array((float) $row[3], (float) $row[4]);
        }

        $geospatial = new GeoSpatial();
        if (isset($_GET["class"]) && $_GET["class"] == 'inside') {
            $results = array();
            foreach ($trackerRows as $row) {
                $point = array((float) $row[7], (float) $row[6]);
                foreach ($lines as $key => $line) {
                    $dist = $geospatial->pointToLineDistance($point, $line, "meters");
                    if ($dist <= $bufferSize) {
                        $results[] = $row;
                        break;
                    }
                }
            }
            echo json_encode($results);
        } elseif (isset($_GET["class"]) && $_GET["class"] == 'outside') {
            $results = array();
            foreach ($trackerRows as $row) {
                $point = array((float) $row[7], (float) $row[6]);
                $flag = 0;
                foreach ($lines as $key => $line) {
                    $dist = $geospatial->pointToLineDistance($point, $line, "meters");
                    if ($dist <= $bufferSize) {
                        $flag = 1;
                    }
                }
                if ($flag == 0) {
                    $results[] = $row;
                }
            }
            echo json_encode($results);
        }
    }

}

function updateTrails()
{
    $bufferSize = 50;
    $geospatial = new GeoSpatial();
    // get trails
    $trailRows = csvToArray("data/trail_lines.csv");
    $trailLines = array();
    foreach ($trailRows as $row) {
        $trailLines[$row[1]][] = $row;
    }
    // get latest trackers
    $trackerRows = csvToArray("data/tracker_logs.csv");
    foreach ($trackerRows as $trackerRow) {
        // tracker point
        $trackerPoint = array((float) $trackerRow[7], (float) $trackerRow[6]);
        foreach ($trailLines as $key => $trailLine) {
            for ($i = 0; $i < count($trailLine) - 1; $i++) {
                $pt1 = array($trailLine[$i][3], $trailLine[$i][4]);
                $pt2 = array($trailLine[$i + 1][3], $trailLine[$i + 1][4]);
                $dist = $geospatial->distanceToSegment($trackerPoint, $pt1, $pt2, "meters");
                if ($dist <= $bufferSize) {
                    $d1 = $geospatial->distance($trackerPoint, $pt1, "meters");
                    $d2 = $geospatial->distance($trackerPoint, $pt2, "meters");
                    if ($d1 < $d2) {
                        $trailId = $trailLine[$i][0];
                        // please update trail line with id
                    } else {
                        $trailId = $trailLine[$i + 1][0];
                        // please update trail line with id
                    }
                    break;
                }
            }

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
