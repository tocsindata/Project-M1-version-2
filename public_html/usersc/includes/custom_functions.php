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
