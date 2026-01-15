<?php
/**
 * Project: Project M1 Dashboard
 * Framework: UserSpice x
 * File: public_html/index.php
 * Date: 2026-01-07
 * Project version 2
 * Copyright: (c) TransitSecurityReport.com / TocsinData.com
 */

declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
$header_calls = [] ; // must be right after strict_types 
/*
header_calls can be blank if you are not using a specific function that is not normally included in UserSpice
otherwise ...
$header_calls['css'][] = 'esri-style.css?v=' . date("YmdH"); // example of  a speciel esri css file
$header_calls['js'][] = 'esri-dashboard-nav.js?v=' . date("YmdH"); // example of a special javascript flat file for an esri based map
$header_calls['func'][] = 'm1-main-dashboard.php' ; // to include the function file source in usersc/includes/m1-main-dashboard.php this way we dont have to always include them and slow things down only the filename is needed
$header_calls['script'][] = 'esri-dashboard-nav.js' ; // to include the file source as <script>CONTENT</script> in /assets/js/esri-dashboard-nav.js in the header
*/
// $header_calls['func'][] = "usersc/includes/m1-main-dashboard.php"; // relative path!
$header_calls['func'][] = "m1-main-dashboard.php"; // function file
$header_calls['func'][] = "m1-esri-map-header.php"; // function file
$header_calls['script'][] = "m1-esri-map-header.js"; // script file


require_once 'usersc/includes/m1-header-calls.php'; // MUST BE LOADED BEFORE PREP

// debug and discovery...
//header_calls_path_helper() ;

require_once 'users/init.php'; // do not call the header_calls here, they are called in the header directly
require_once $abs_us_root . $us_url_root . 'users/includes/template/prep.php'; // header_calls must be set before this line
// we now include a direct require once for all function files instead of auto include via init (speed related reason) or use header_calls for things that must be inside <head></head> tags
require_once 'usersc/includes/custom_functions.php';
require_once $abs_us_root . $us_url_root . 'usersc/includes/m1-dashboard-functions.php';
require_once $abs_us_root . $us_url_root . 'usersc/includes/m1-display-functions.php';
	// is there a user logged in?
	if (isset($user) && $user->isLoggedIn()) {
		$user_in = 1 ;
		$this_user_id = get_user_id() ; // get_user_id() so that api key can be used as well
	} else {
		$user_in = 0 ;
		$this_user_id = 0 ;
	}
 
    if($user_in == 1) {
	// second level nav menu starts here id is 3
	$menu = new Menu(3);
	$menu->display();
 
    // CORE BODY STARTS HERE
    // display_dashboard_12cells($this_user_id) ; // example layout 12 cells 3 rows by 4 widget cells
	display_main_dashboard($this_user_id) ; /// multi layout examples
    // CORE BODY ENDs HERE
    } else {
    require_once $abs_us_root . $us_url_root . 'users/views/_public_index.php';
    }
require_once $abs_us_root . $us_url_root . 'users/includes/html_footer.php'; 
?>