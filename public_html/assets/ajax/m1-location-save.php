<?php
declare(strict_types=1);

/**
 * Project: Project M1 Dashboard
 * Framework: UserSpice 6
 * File: assets/ajax/m1-location-save.php
 * Date: 2026-01-16
 * Copyright: (c) TransitSecurityReport.com / TocsinData.com
 */

// Boot UserSpice
require_once $_SERVER['DOCUMENT_ROOT'] . '/users/init.php';

header('Content-Type: application/json; charset=UTF-8');

$out = [
  'success' => false,
  'message' => 'Unknown error.',
];

try {
  if (!$user->isLoggedIn()) {
    http_response_code(401);
    $out['message'] = 'Not logged in.';
    echo json_encode($out);
    exit;
  }

  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $out['message'] = 'Method not allowed.';
    echo json_encode($out);
    exit;
  }

  // CSRF check
  if (!Token::check(Input::get('csrf'))) {
    http_response_code(400);
    $out['message'] = 'Security token mismatch. Please refresh and try again.';
    echo json_encode($out);
    exit;
  }

  $this_user_id = (int)$user->data()->id;

  $posted_user_id = (int)Input::get('user_id');
  $location_id = (int)Input::get('location_id');

  // Enforce self-only updates
  if ($posted_user_id !== $this_user_id) {
    http_response_code(403);
    $out['message'] = 'Permission denied.';
    echo json_encode($out);
    exit;
  }

  if ($location_id < 1) {
    http_response_code(400);
    $out['message'] = 'Invalid location.';
    echo json_encode($out);
    exit;
  }

  $db = DB::getInstance();

  // Confirm location exists and active
  $locQ = $db->query(
    "SELECT id, name
     FROM m1_locations
     WHERE id = ? AND active = 1
     LIMIT 1",
    [$location_id]
  );

  if (!$locQ || $locQ->count() !== 1) {
    http_response_code(400);
    $out['message'] = 'Location not found or inactive.';
    echo json_encode($out);
    exit;
  }

  $locName = (string)$locQ->first()->name;

  // Enforce single selection regardless of current unique index state:
  $db->query("DELETE FROM users_dashboard_location WHERE user_id = ?", [$this_user_id]);

  $ins = $db->query(
    "INSERT INTO users_dashboard_location (user_id, location_id) VALUES (?, ?)",
    [$this_user_id, $location_id]
  );

  if (!$ins) {
    http_response_code(500);
    $out['message'] = 'Failed to save selection.';
    logger($this_user_id, 'ProjectM1', 'Location save failed: ' . $db->errorString());
    echo json_encode($out);
    exit;
  }

  logger($this_user_id, 'ProjectM1', 'Location updated to ID ' . $location_id . ' (' . $locName . ').');

  $out['success'] = true;
  $out['message'] = 'Saved.';
  $out['location_id'] = $location_id;

  // Rotate CSRF token for subsequent saves without refresh
  $out['csrf'] = Token::generate();

  echo json_encode($out);
  exit;

} catch (Throwable $e) {
  http_response_code(500);
  $out['message'] = 'Server error.';
  if (isset($user) && $user && $user->isLoggedIn()) {
    logger((int)$user->data()->id, 'ProjectM1', 'Location save exception: ' . $e->getMessage());
  }
  echo json_encode($out);
  exit;
}
