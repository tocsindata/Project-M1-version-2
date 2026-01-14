<?php
/**
 * Project: Project M1 Dashboard
 * Framework: UserSpice 6
 * File: public_html/usersc/includes/m1-dashboard-functions.php
 * Date: 2026-01-12
 * Copyright: (c) TransitSecurityReport.com / TocsinData.com
 */

declare(strict_types=1);

/*
  Purpose:
  - Dashboard helper functions loaded on every dashboard page
  - Active location resolution (user_state -> default location id)
  - Widget settings read/write wrappers (m1_widget_settings)
*/

if (!defined('M1_DEFAULT_LOCATION_ID')) {
  define('M1_DEFAULT_LOCATION_ID', 45); // Exeter Branch
}

if (!function_exists('m1_db')) {
  function m1_db() {
    return DB::getInstance();
  }
}

if (!function_exists('m1_now_utc')) {
  function m1_now_utc(): string {
    return gmdate('Y-m-d H:i:s');
  }
}

if (!function_exists('m1_active_location_id')) {
  function m1_active_location_id(int $user_id, int $default_location_id = M1_DEFAULT_LOCATION_ID): int {
    if ($user_id <= 0) return $default_location_id;

    $db = m1_db();
    $q  = $db->query("SELECT last_location_id FROM m1_user_state WHERE user_id = ?", [$user_id]);

    if ($q && $q->count() > 0) {
      $row = $q->first();
      $lid = (int)($row->last_location_id ?? 0);
      if ($lid > 0) return $lid;
    }

    return $default_location_id;
  }
}

if (!function_exists('m1_ensure_user_state_row')) {
  function m1_ensure_user_state_row(int $user_id, int $default_location_id = M1_DEFAULT_LOCATION_ID): void {
    if ($user_id <= 0) return;

    $db = m1_db();
    $q  = $db->query("SELECT user_id FROM m1_user_state WHERE user_id = ? LIMIT 1", [$user_id]);

    if ($q && $q->count() > 0) return;

    $now = m1_now_utc();
    // Insert a default row so later updates are simple.
    $db->query(
      "INSERT INTO m1_user_state (user_id, last_location_id, created_utc, updated_utc)
       VALUES (?, ?, ?, ?)",
      [$user_id, $default_location_id, $now, $now]
    );
  }
}

if (!function_exists('m1_widget_settings_get')) {
  function m1_widget_settings_get(int $user_id, string $widget_key, ?int $location_id = null): array {
    if ($user_id <= 0 || $widget_key === '') return [];

    $db = m1_db();
    $lid = $location_id; // can be NULL for “global” settings later

    $q = $db->query(
      "SELECT settings_json
         FROM m1_widget_settings
        WHERE user_id = ?
          AND widget_key = ?
          AND ((location_id IS NULL AND ? IS NULL) OR location_id = ?)
        LIMIT 1",
      [$user_id, $widget_key, $lid, $lid]
    );

    if (!$q || $q->count() === 0) return [];

    $raw = (string)($q->first()->settings_json ?? '');
    if ($raw === '') return [];

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
  }
}

if (!function_exists('m1_widget_settings_upsert')) {
  function m1_widget_settings_upsert(int $user_id, string $widget_key, ?int $location_id, array $settings): bool {
    if ($user_id <= 0 || $widget_key === '') return false;

    $db  = m1_db();
    $now = m1_now_utc();

    $json = json_encode($settings, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if (!is_string($json) || $json === '') $json = '{}';

    // Upsert using the UNIQUE KEY uq_m1_widget_settings_user_widget_loc
    // (user_id, widget_key, location_id)
    $db->query(
      "INSERT INTO m1_widget_settings
         (user_id, widget_key, location_id, settings_json, created_utc, updated_utc)
       VALUES (?, ?, ?, ?, ?, ?)
       ON DUPLICATE KEY UPDATE
         settings_json = VALUES(settings_json),
         updated_utc   = VALUES(updated_utc)",
      [$user_id, $widget_key, $location_id, $json, $now, $now]
    );

    return !$db->error();
  }
}
