<?php

class GeoSpatial
{
    public function pointToLineDistance($pt, $line, $units)
    {
        for ($i = 0; $i < count($line) - 1; $i++) {
            $a = $line[$i];
            $b = $line[$i + 1];
            $d = $this->distanceToSegment($pt, $a, $b, $units);
            $dists[] = $d;
        }
        return min($dists);

    }
    private function distanceToSegment($p, $a, $b, $units)
    {
        $v = array($b[0] - $a[0], $b[1] - $a[1]);
        $w = array($p[0] - $a[0], $p[1] - $a[1]);
        $c1 = $this->dot($w, $v);
        if ($c1 <= 0) {
            return $this->distance($p, $a, $units);
        }
        $c2 = $this->dot($v, $v);
        if ($c2 <= $c1) {
            return $this->distance($p, $b, $units);
        }
        $b2 = $c1 / $c2;
        $Pb = array($a[0] + $b2 * $v[0], $a[1] + $b2 * $v[1]);
        return $this->distance($p, $Pb, $units);
    }

    private function dot($u, $v)
    {
        return $u[0] * $v[0] + $u[1] * $v[1];
    }
    public function distance($from, $to, $units)
    {
        $dLat = $this->degreesToRadians($to[1] - $from[1]);
        $dLng = $this->degreesToRadians($to[0] - $from[0]);
        $lat1 = $this->degreesToRadians($from[1]);
        $lat2 = $this->degreesToRadians($to[1]);
        $a = pow(sin($dLat / 2), 2) + pow(sin($dLng / 2), 2) * cos($lat1) * cos($lat2);
        return $this->factors($units) * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    private function degreesToRadians($degrees)
    {
        $radians = fmod($degrees, 360);
        return $radians * pi() / 180;
    }

    private function factors($units)
    {
        $earthRadius = 6371008.8;
        switch ($units) {
            case 'kilometers':
                return $earthRadius / 1000;
                break;
            case 'meters':
                return $earthRadius;
                break;
            case 'degrees':
                return 360 / (2 * pi());
                break;
            case 'feet':
                return $earthRadius * 3.28084;
                break;
            case 'inches':
                return $earthRadius * 39.37;
                break;
            case 'miles':
                return $earthRadius / 1609.344;
                break;
            case 'nauticalmiles':
                return $earthRadius / 1852;
                break;
            case 'radians':
                return 1;
                break;
            case 'yards':
                return $earthRadius * 1.0936;
                break;
            default:
                return $earthRadius;
                break;
        }
    }
}
