<?php

class Coordinates {
    private function __construct() { }
    
    public static function getSegmentPoints($start_mile, $end_mile) {
        
        $db = DatabaseConnection::getInstance();
        
        $is_reverse = ($start_mile > $end_mile);
        $distance = abs( $start_mile - $end_mile );
        
        $granularity = Coordinates::getGranularity($distance);
        $granular_table = 'coordinates_' . $granularity;
        
        $sql = '
SELECT
    coordinates_atomic.elevation, coordinates_atomic.banff_distance
FROM
    coordinates_atomic, ' . $granular_table . '
WHERE
    coordinates_atomic.id = ' . $granular_table . '.id AND
    coordinates_atomic.banff_distance >= :start_mile AND
    coordinates_atomic.banff_distance <= :end_mile
ORDER BY
    coordinates_atomic.id ' . ($is_reverse ? 'DESC' : 'ASC') . ';';
        
        if($is_reverse) {
            $start_mile = $start_mile + $end_mile - ($end_mile=$start_mile);
        }
        
        $sql_params = array(
            ':start_mile' => ($start_mile*5280),
            ':end_mile' => ($end_mile*5280),
        );
        
        try {
            $statement = $db->db_conn->prepare($sql);
            $statement->execute($sql_params);
        } catch(Exception $e) {
            error_log( $e->getMessage() );
            throw new ESRGD\DatabaseException('');
            
        }
        
        $data = $statement->fetchAll();
        if( $data === false ) {
            error_log('fetchall => false');
            throw new ESRGD\DatabaseException('');
            
        } else if( sizeof($data) == 0 ) {
            error_log('fetchall => empty');
            throw new ESRGD\NotFoundException('');
            
        }
        
        $return = array(
            'elevations' => array(),
            'distance' => array()
        );
        
        $num_rows = sizeof($data);
        $offset = $data[0]['banff_distance'];
        
        for($x=0; $x < $num_rows; $x++) {
            $return['elevations'][] = $data[$x]['elevation'];
            
            if($is_reverse) {
                $tmp = ($offset - $data[$x]['banff_distance']) + $start_mile*5280;
                //$tmp = $data[$x]['banff_distance'];
                $return['distances'][] = abs($tmp/5280);
            } else {
                $return['distances'][] = $data[$x]['banff_distance']/5280;
            }
        }
        /*
        error_log($return['distances'][0] . ' => ' . $return['elevations'][0]);
        error_log($return['distances'][sizeof($return['distances'])-1] . ' => ' . $return['elevations'][sizeof($return['elevations'])-1]);
        */
        return $return;
        
        
    }//end method getSegmentPoints 
    
    /**
     *
     */
    public static function getGranularity($dist) {
        
        if( $dist == 0) {
            return 2;
        }
        
        $p = floor( log($dist/20, 2) );
       
        $granularity = pow(2, $p);
        
        return ($granularity > 64) ? 64 : (($granularity < 2) ? 2 : $granularity);
    }//end method getGranularity
    
    /**
     *
     */
    public static function getSegmentStats($start_mile, $end_mile) {
        
        $db = DatabaseConnection::getInstance();
        
        $is_reverse = ($start_mile > $end_mile);
        $distance = abs( $start_mile - $end_mile );
        
        $granularity = Coordinates::getGranularity($distance);
        $granular_table = 'coordinates_' . $granularity;
        
        $sql = '
SELECT
    SUM(ascent) AS total_ascent,
    SUM(descent) as total_descent
FROM
    ' . $granular_table . '
WHERE
    banff_distance >= :start_mile AND
    banff_distance <= :end_mile';
        
        $sql_params = array(
            'start_mile' => min($start_mile, $end_mile) * 5280,
            'end_mile' => max($start_mile, $end_mile) * 5280
        );
        
        try {
            $statement = $db->db_conn->prepare($sql);
            $statement->execute($sql_params);
        } catch(Exception $e) {
            error_log( $e->getMessage() );
            throw new ESRGD\DatabaseException('');
            
        }
        
        $data = $statement->fetchAll();
        
        if( $data === false ) {
            error_log('fetchall => false');
            throw new Exception('');
            
        } else if( sizeof($data) == 0 ) {
            error_log('fetchall => empty');
            throw new ESRGD\NotFoundException('');
            
        }
        
        $meta = ElevationProfile::getGraphMetadata($start_mile, $end_mile);
        
        return array(
            'ascent' => (int)$data[0]['total_ascent'],
            'descent' => (int)$data[0]['total_descent'],
            'distance' => abs($start_mile - $end_mile),
            'net' => abs($data[0]['total_ascent']-$data[0]['total_descent']),
            'absolute_start_mile' => $meta['absolute_start_mile'],
            'absolute_end_mile' => $meta['absolute_end_mile'],
            'relative_start_mile' => $meta['relative_start_mile'],
            'relative_end_mile' => $meta['relative_end_mile']
        );
        
    }//end method getSegmentStats
    
    /**
     * 
     */
    public static function getClosestCoord($lat, $lng) {
        
        $db = DatabaseConnection::getInstance();
        
        $radius = 50;
        
        $sql = '
SELECT
    id, lat, lng, banff_distance, SQRT(
        POW(lat-:lat,2) + POW(lng-:lng,2)
    ) AS dist
FROM
    coordinates_atomic
WHERE
    lat BETWEEN :lat_min AND :lat_max AND
    lng BETWEEN :lng_min AND :lng_max
ORDER BY
    dist ASC
LIMIT 1';

        $sql_params = array(
            'lat' => $lat,
            'lng' => $lng,
            'lat_min' => $lat-$radius,
            'lat_max' => $lat+$radius,
            'lng_min' => $lng-$radius,
            'lng_max' => $lng+$radius
        );
        
        try {
            $statement = $db->db_conn->prepare($sql);
            $statement->execute($sql_params);
        } catch(Exception $e) {
            error_log( $e->getMessage() );
            throw new ESRGD\DatabaseException('');
            
        }
        
        $data = $statement->fetch();
        
        if( $data === false ) {
            error_log('fetchall => false');
            throw new ESRGD\DatabaseException('');
            
        }
        
        return array(
            'banff_distance' => $data['banff_distance']/5280,
            'lat' => $data['lat'],
            'lng' => $data['lng']
        );
    }
    
    /**
     *
     */
    public static function getRouteDistance() {
        $db = DatabaseConnection::getInstance();
        
        $sql = '
SELECT
    banff_distance
FROM
    coordinates_atomic
ORDER BY
    banff_distance DESC
LIMIT 1';
        try {
            $statement = $db->db_conn->prepare($sql);
            $statement->execute();
        } catch(Exception $e) {
            error_log( $e->getMessage() );
            throw new ESRGD\DatabaseException('');
            
        }
        
        $data = $statement->fetch();
        
        if( $data === false ) {
            error_log('fetchall => false');
            throw new ESRGD\DatabaseException('');
            
        }
        
        return $data['banff_distance']/5280;
    }//end method getRouteDistance
}
