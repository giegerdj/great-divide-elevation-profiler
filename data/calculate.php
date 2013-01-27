<?php
/**
 * NOTE: all measures of distance and elevation are in feet
 */

define('ABS_PATH', dirname(dirname(__FILE__)) . '/' );

define('KM_TO_MI', 0.621371);
define('EARTH_RADIUS', 6371);
define('METER_TO_FEET', 3.28084);
define('PI', 3.141592653589793);

$file_contents = file_get_contents(ABS_PATH . 'data/td-2012.gpx');
$lines = explode("\n", $file_contents);


$num_lines = sizeof($lines);
$total_distance = 0;
$total_ascent = 0;
$total_descent = 0;

$data = array();

for($x=0; $x < $num_lines; $x++) {
    
    $split = explode(' ', $lines[$x]);
    $lat = $split[0];
    $lng = $split[1];
    $elevation = round( meterToFeet($split[2]), 2);
    $minor_ascent = 0;
    $minor_descent = 0;
    
    if($x==0) {
        $minor_distance = 0;
        $delta_elevation = 0;
        $percent_grade = 0;
    } else {
        $split = explode(' ', $lines[$x-1]);
        $prev_lat = $split[0];
        $prev_lng = $split[1];
        $prev_elevation = round( meterToFeet($split[2]), 2);
        
        /**
         * calculate changes from last point
         */
        $minor_distance = distBetweenLatLngs($prev_lat, $prev_lng, $lat, $lng);
        $minor_distance = kmToMi($minor_distance) * 5280;
        $delta_elevation = round($elevation - $prev_elevation);
        
        $percent_grade = @round($delta_elevation / $minor_distance,4) * 100;
        if($percent_grade > 0) {
            $minor_ascent = $delta_elevation;
        } else {
            $minor_descent = abs($delta_elevation);
        }
        /**
         * if the grade is less than 1%, do not count the ascent/descent values
         * we'll take care of this later 
        if( abs($percent_grade) > 1) {
            if($percent_grade > 0) {
                $minor_ascent = $delta_elevation;
            } else {
                $minor_descent = abs($delta_elevation);
            }
        }
        */
    }
    /**
     * update the running totals with the values since last point
     */
    $total_ascent += $minor_ascent;
    $total_descent += $minor_descent;
    $total_distance += $minor_distance;
    
    /**
     * print all data
     */
    $data[] = array(
        'id' => $x,
        'lat' => $lat,
        'lng' => $lng,
        'elevation' => $elevation,
        'cumulative' => array(
            'distance' => $total_distance,
            'ascent' => $total_ascent,
            'descent' => $total_descent
        ),
        'hop' => array(
            'distance' => $minor_distance,
            'grade' => $percent_grade,
            'delta_elevation' => $delta_elevation,
            'ascent' => $minor_ascent,
            'descent' => $minor_descent
        )
    );
}



//printAtomicDataSQL($data);

//printSummarizedData($data, 3.2);
//printSummarizedData($data, 6.4);
printSummarizedData($data, 12.8);
/**
 *  START HELPER FUNCTIONS
 */


function printLatLng($data) {
    $num_points = sizeof($data);
    
    for($x=0; $x < $num_points; $x++) {
        echo $data[$x]['lat'] , ' ' , $data[$x]['lng'] , PHP_EOL;
    }
    
}

function printAll($data) {
    $num_points = sizeof($data);
    
    for($x=0; $x < $num_points; $x++) {
        echo str_pad(round($data[$x]['cumulative']['distance'],2), 12) .
        str_pad($data[$x]['lat'], 12) .
        str_pad($data[$x]['lng'], 12) .
        str_pad($data[$x]['elevation'], 12) .
        str_pad(round($data[$x]['hop']['distance'],2), 12) .
        str_pad($data[$x]['hop']['delta_elevation'], 12) .
        str_pad($data[$x]['hop']['grade'], 12) .
        str_pad($data[$x]['hop']['ascent'], 12) .
        str_pad($data[$x]['hop']['descent'], 12) .
        str_pad($data[$x]['cumulative']['ascent'], 12) .
        str_pad($data[$x]['cumulative']['descent'], 12) . PHP_EOL;
    }
    
}

/**
 * pipe this to a file to get the sql to populate the coordinates_atomic table
 */
function printAtomicDataSQL($data) {
    $num_points = sizeof($data);
    
    for($x=0; $x < $num_points; $x++) {
        
        if( $x%250 == 0 ) {
            //start a new SQL insert statement
            echo PHP_EOL , 'INSERT INTO coordinates_atomic (id, lat, lng, elevation, banff_distance) VALUES';
        }
        if( $x%250 != 0 ) {
            echo ',';
        }
        echo '(' , $data[$x]['id'] , ',' , $data[$x]['lat'] , ',' , $data[$x]['lng'] , ',' ,
            round($data[$x]['elevation']) , ',' , round($data[$x]['cumulative']['distance']) , ')';
        
        echo ($x%250 == 249) ? ';' : '';
        
    }
    echo ';';
}

function printSummarizedData($data, $segment_granularity) {
    $num_points = sizeof($data);
    $segments = array();
    
    $table_suffix = $segment_granularity*10;
    $segment_granularity *= 5280;
    
    $segment_length = 0;
    $segment_ascent = 0;
    $segment_descent = 0;
    
    for($x=0; $x < $num_points; $x++) {
        
        $segment_length += $data[$x]['hop']['distance'];
        
        if( $data[$x]['hop']['grade'] >= 1 ) {
            $segment_ascent += $data[$x]['hop']['ascent'];
        } else if( $data[$x]['hop']['grade'] <= -1 ) {
            $segment_descent += abs($data[$x]['hop']['descent']);
        }
        
        if($segment_length >= $segment_granularity) {
            $segments[] = array(
                'id' => $data[$x]['id'],
                'ascent' => $segment_ascent,
                'descent' => $segment_descent,
                'distance' => $data[$x]['cumulative']['distance']
            );
            
            $segment_length = 0;
            $segment_ascent = 0;
            $segment_descent = 0;
        }
        
    }
    
    $num_segments = sizeof($segments);
    
    for($x=0; $x < $num_segments; $x++) {
        
        if( $x%250 == 0 ) {
            //start a new SQL insert statement
            echo PHP_EOL , 'INSERT INTO coordinates_' , $table_suffix , ' (id, ascent, descent, banff_distance) VALUES';
        }
        if( $x%250 != 0 ) {
            echo ',';
        }
        echo '(' , $segments[$x]['id'] , ',' , $segments[$x]['ascent'] , ',' , $segments[$x]['descent'] , ',' ,
            round($segments[$x]['distance']) , ')';
        
        echo ($x%250 == 249) ? ';' : '';
        
    }
    echo ';';
    
    
}

function distBetweenLatLngs($lat1, $lng1, $lat2, $lng2) {
    
    $dLat = degreesToRadians($lat2-$lat1);  // deg2rad below
    $dLng = degreesToRadians($lng2-$lng1);
    $a = 
      sin($dLat/2) * sin($dLat/2) + 
      cos(degreesToRadians($lat1)) * cos(degreesToRadians($lat2)) * 
      sin($dLng/2) * sin($dLng/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a) );
    $d = EARTH_RADIUS * $c; // Distance in km
    return $d;
    
}

function kmToMi($km) {
    return $km * KM_TO_MI;
}

function meterToFeet($meter) {
    return $meter * METER_TO_FEET;
}

function degreesToRadians($deg) {
    return $deg * (PI / 180);
}
