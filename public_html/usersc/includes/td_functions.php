<?php
// public_html/usersc/includes/td_functions.php
/*
* These functions are related to customized version of Userspice v6
* Userspice will upgrade and updated without effect on the functions below
* Customization of some core features might revert to standard userspice 
* methods when updated with the main repo
* backup first.
*
* - Tocsindata.com
*/


if (!function_exists('td_settings')) {
    function td_settings($name = '', $default = '1', $update = null) {
        $db = DB::getInstance();

        if (empty($name)) {
            return $default;
        }

        $name = trim((string)$name);

        // SET mode
        if ($update !== null) {
            // Ensure row exists
            $exists = $db->query("SELECT `id` FROM `td_settings` WHERE `name` = ? LIMIT 1", [$name]);
            if ($exists->count() === 0) {
                $db->query(
                    "INSERT INTO `td_settings` (`name`, `value`) VALUES (?, ?)",
                    [$name, (string)$default]
                );
            }

            $db->query(
                "UPDATE `td_settings` SET `value` = ? WHERE `name` = ?",
                [(string)$update, $name]
            );

            return $update;
        }

        // GET mode
        $query = $db->query(
            "SELECT `value` FROM `td_settings` WHERE `name` = ? LIMIT 1",
            [$name]
        );

        // Auto-create missing keys with default
        if ($query->count() === 0) {
            $db->query(
                "INSERT INTO `td_settings` (`name`, `value`) VALUES (?, ?)",
                [$name, (string)$default]
            );
            return $default;
        }

        $val = $query->first()->value;

        // Normalize NULL/empty stored values to default if you want (optional)
        if ($val === null || $val === '') {
            return $default;
        }

        return $val;
    }
}
