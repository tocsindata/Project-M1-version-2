<?php
/**
 * Project: Project M1 Dashboard
 * Framework: UserSpice x
 * File: public_html/geojson.php
 * Date: 2026-01-07
 * Project version 2
 * Copyright: (c) TransitSecurityReport.com / TocsinData.com
 */

// THIS SCRIPT RETURNS DATA DIRECTLY FROM THE DATABASE, NOT REMOTE URL

require_once 'users/init.php'; // do not call the header_calls here, they are called in the header directly
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header('Content-Type: application/json; charset=utf-8');
$data = [] ;
$original_data = [] ;

if(get_user_id()){
$user_id = get_user_id() ; // will use ?api=XXXXX-XXXXX-XXXXX-XXXXX UserSpice API Tokens to get user id, is user's permission is 0 it will return false.


$db = m1_db();

$sql = "SELECT * FROM `events` WHERE 1 ;";
$db->query($sql) ;
$results = $db->results() ;
foreach($results as $row){
    $newdata = (array) $row ; // missing header column field names?
    $original_data[] = $newdata ;
}

					$features = array();
					foreach($original_data as $i => $data) {
					    $features[$i] = array(
					        'type' => 'Feature',
					        'properties' => $data,
					        'geometry' => array(
					             'type' => 'Point', 
					             'coordinates' => array(
					                  floatval($data['longitude']),
					                  floatval($data['latitude'])
					             ),
					         ),
					    );
					}
					
					$new_data = array(
					    'type' => 'FeatureCollection',
					    'features' => $features,
					);
					
					$final_data = json_encode($new_data, true);
					$data = $final_data;

} else {
    $data['error'][] = "Access Denied";
    $data['error'][] = "Invalid Token";
}

echo json_encode($data, true); 