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

require_once 'users/init.php';
require_once $abs_us_root . $us_url_root . 'users/includes/template/prep.php';
// we now include a direct require once for all function files instead of auto include via init (speed related reason)
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
    // CORE BODY STARTS HERE
    // display_dashboard_12cells($this_user_id) ; // example layout 12 cells 3 rows by 4 widget cells
	display_dashboard_spans($this_user_id) ; /// multi layout examples
    // CORE BODY ENDs HERE
    } else {
    require_once $abs_us_root . $us_url_root . 'users/views/_public_index.php';
    }
require_once $abs_us_root . $us_url_root . 'users/includes/html_footer.php'; 
?>