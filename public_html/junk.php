<?php
// file public_html/junk.php (tmp)

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
require_once 'users/init.php';
require_once 'usersc/includes/custom_functions.php';
require_once $abs_us_root . $us_url_root . 'usersc/includes/m1-dashboard-functions.php';
require_once $abs_us_root . $us_url_root . 'usersc/includes/m1-display-functions.php';
$sql = "CREATE TABLE IF NOT EXISTS events (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  track_id VARCHAR(64) NOT NULL,
  title VARCHAR(255) NOT NULL,
  event_gmt_time DATETIME NOT NULL,
  event_type VARCHAR(255) NOT NULL,
  country VARCHAR(128) NOT NULL,
  location VARCHAR(255) NOT NULL,
  severity VARCHAR(64) NOT NULL,
  infrastructure VARCHAR(128) DEFAULT NULL,
  url TEXT NOT NULL,
  description TEXT,
  latitude DECIMAL(10,7) NOT NULL,
  longitude DECIMAL(10,7) NOT NULL,
  granularity VARCHAR(128) DEFAULT NULL,
  icon_url VARCHAR(255) DEFAULT NULL,
  geom POINT SRID 4326 NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_track_id (track_id),
  KEY idx_event_time (event_gmt_time),
  KEY idx_event_type (event_type),
  SPATIAL KEY idx_geom (geom)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
$db = m1_db() ;
$db->query($sql) ;

