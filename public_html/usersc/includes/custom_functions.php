<?php
/**
 * Project: Project M1 Dashboard
 * Framework: UserSpice x
 * File: public_html/usersc/includes/custom_functions.php
 * Date: 2026-01-12
 * Project version 2
 * Copyright: (c) TransitSecurityReport.com / TocsinData.com
 */


declare(strict_types=1);

require_once $abs_us_root . $us_url_root . 'usersc/includes/m1-header-calls.php';
require_once $abs_us_root . $us_url_root . 'usersc/includes/m1-data-source-helpers.php';
require_once $abs_us_root . $us_url_root . 'usersc/includes/td_functions.php';

if(!function_exists('get_users_location_id')){
    function get_users_location_id($this_user_id){
        $db = m1_db() ;
        $sql = "SELECT COUNT(*) as `is_set` FROM `users_dashboard_location` WHERE `user_id` = ".(int) $this_user_id ;
        $db->query($sql) ;
        $results = $db->results() ;
        foreach($results as $row){
            $is_set = $row->is_set ;
        }

        if($is_set == 1){
        $sql = "SELECT `location_id` FROM `users_dashboard_location` WHERE `user_id` = ".(int) $this_user_id ;
        $db->query($sql) ;
        $results = $db->results() ;
        foreach($results as $row){
            return $row->location_id ;
        }
        }

        $sql = "INSERT INTO `users_dashboard_location` (`user_id`, `location_id`) VALUES (".(int) $this_user_id.", 2);";
        $db->query($sql) ;
        return 2;
    }
}

if(!function_exists('get_location_title')){
    function get_location_title($location_id) {
        $db = m1_db() ;
        $name = "(New) Location" ;
        $sql = "SELECT `name` FROM `m1_locations` WHERE `id` = " . (int) $location_id;
        $db->query($sql) ;
        $results = $db->results() ;
        foreach($results as $row){
            $name = $row->name ;
        }
        return $name ;
    }
}


if(!function_exists('widget_title_settings')){
    function widget_title_settings($this_user_id, $widget_title, $btn_link){
        $db = m1_db(); // useful cheat for db class
        $out = '<div class="widgettitle"><h5>'.strtoupper($widget_title).'</h5><span><a type="button" class="btn" href="'.$btn_link.'"><i class="fas fa-ellipsis-h"></i></a></span></div>'.PHP_EOL;
        return $out ;
    }
}

if (!function_exists('m1_is_valid_key')) {
    function m1_is_valid_key(string $key): bool
    {
        $key = strtoupper(trim($key));
        // required format: AAAAA-BBBBB-CCCCC-DDDDD-EEEEE (A-Z0-9 only)
        return (bool)preg_match('/^[A-Z0-9]{5}(?:-[A-Z0-9]{5}){4}$/', $key);
    }
}

if (!function_exists('is_api_valid')) {
    /**
     * Returns true if the api key is valid format AND exists for an allowed user.
     */
    function is_api_valid(?string $api = null): bool
    {
        if ($api === null) {
            return false;
        }

        $api = strtoupper(trim($api));
        if ($api === '' || !m1_is_valid_key($api)) {
            return false;
        }

        $db = DB::getInstance();

        // You used permissions = 1 in your intent; keeping that rule.
        $sql = "SELECT id FROM users WHERE permissions = ? AND apibld_key = ? LIMIT 1";
        $db->query($sql, [1, $api]);

        return ($db->count() === 1);
    }
}

if (!function_exists('get_user_id')) {
    /**
     * Returns:
     * - int user_id when logged in OR valid api token maps to a user
     * - false otherwise
     */
    function get_user_id()
    {
        global $user;

        // Logged-in path
        if (isset($user) && $user && method_exists($user, 'isLoggedIn') && $user->isLoggedIn()) {
            return (int)$user->data()->id;
        }

        // API token path
        $api = null;
        if (isset($_GET['api']) || isset($_POST['api'])) {
            $api = (string)Input::get('api');
        }

        if ($api === null || trim($api) === '') {
            return false;
        }

        $api = strtoupper(trim($api));

        // Validate format early
        if (!m1_is_valid_key($api)) {
            return false;
        }

        // Validate existence (permissions rule)
        if (!is_api_valid($api)) {
            return false;
        }

        // Fetch user id via SQL row
        $db = DB::getInstance();
        $sql = "SELECT id FROM users WHERE permissions = ? AND apibld_key = ? LIMIT 1";
        $db->query($sql, [1, $api]);

        if ($db->count() !== 1) {
            return false;
        }

        $row = $db->first();
        return (int)$row->id;
    }
}
