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

// we now include a direct require once for all function files instead of auto include via init
require_once 'usersc/includes/custom_functions.php';

	// is there a user logged in?
	if (isset($user) && $user->isLoggedIn()) {
		$user_in = 1 ;
		$this_user_id = get_user_id() ; // get_user_id() so that api key can be used as well
	} else {
		$user_in = 0 ;
		$this_user_id = 0 ;
	}

    