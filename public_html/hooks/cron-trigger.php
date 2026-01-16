<?php
/**
 * file path: hooks/cron-trigger.php 
 * Project: Tocsin Data
 * Framework: UserSpice 5
 * Date: 2025-11-29
 * Copyright: (c) Tocsin Data
 */

// file: hooks/cron-trigger.php
set_time_limit(300);
$start_time = time() ;
ini_set('max_execution_time', 300);
$isCli = (PHP_SAPI === 'cli' || php_sapi_name() === 'cli' || empty($_SERVER['DOCUMENT_ROOT']));
$out = array() ;

if ($isCli) {
  // Shim minimal web-style environment so users/init.php & security_headers.php can compute paths.
  if (empty($_SERVER['DOCUMENT_ROOT'])) {
    // /home/.../public_html (one level up from /hooks)
    $_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__);
  }
  if (!isset($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = 'localhost';
  }
  if (!isset($_SERVER['REQUEST_URI'])) {
    $_SERVER['REQUEST_URI'] = '/hooks/cron-trigger.php';
  }
  if (!isset($_SERVER['SCRIPT_NAME'])) {
    $_SERVER['SCRIPT_NAME'] = '/hooks/cron-trigger.php';
  }
  if (!isset($_SERVER['PHP_SELF'])) {
    $_SERVER['PHP_SELF'] = '/hooks/cron-trigger.php';
  }
  // Make security_headers.php think we are already HTTPS on 443 so it does NOT redirect.
  if (!isset($_SERVER['SERVER_PORT'])) {
    $_SERVER['SERVER_PORT'] = 443;
  }
  if (!isset($_SERVER['HTTPS'])) {
    $_SERVER['HTTPS'] = 'on';
  }
  if (!isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
    $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
  }
    // Provide a sane default URL root for init.php when running via CLI
  if (!isset($us_url_root) || $us_url_root === '') {
    $us_url_root = '/';
  }
} else {
  header('Content-Type: application/json; charset=utf-8');
  $TOCSIN_DEBUG = false;
if (isset($_GET['debug']) && $_GET['debug'] === '1') {
    $TOCSIN_DEBUG = true;
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    ini_set('log_errors', '0');

    $out['debug'] = "Tocsin Data DEBUG MODE: ?debug=1 &mdash; PHP errors will be displayed on this page.";
}}

// Bootstrap UserSpice (path relative to this file)
require_once __DIR__ . '/../users/init.php';

// Positively mark the include context for cron jobs
if (!defined('USING_CRON_TRIGGER')) {
  define('USING_CRON_TRIGGER', true);
}

// Secondary marker (in case some host/edge case mishandles constants)
$_SERVER['TD_CRON_TRIGGER'] = '1';
$out = ['status' => 'error', 'message' => 'unknown'];
$db  = DB::getInstance();


// -------------------- CONFIG --------------------
$max_running           = td_settings('cron_max_running'); // cap concurrent jobs, good for when we have a large cpu etc
$invalid_token_literal = 'xxxx-xxxx-xxxx-xxxx';   // if token == this, reject (WEB ONLY)
$admin_user_id         = 1;                       // restrict token lookup to admin
$stale_minutes         = 10;                      // running > this => reset running=0
// ------------------------------------------------


/**
 * 1) TOKEN HANDLING
 * - WEB: token is REQUIRED via ?token=... and validated.
 * - CLI: token is NOT required at all.
 */
if (!$isCli) {
  // --- WEB CONTEXT: enforce token ---
  if (!isset($_GET['token'])) {
    $out['message'] = 'Missing token.';
    echo json_encode($out, JSON_PRETTY_PRINT);
    exit;
  }

  $token = Input::get('token');

    if(!function_exists('get_apibld_key')){
    function get_apibld_key($user_id) {
        $db = DB::getInstance();
        $sql = "SELECT apibld_key FROM users WHERE id = ?";
        $result = $db->query($sql, [$user_id]);
        if ($result->count() > 0) {
            return $result->first()->apibld_key;
        } else {
            return false ; 
        }
    }
    }
 
  // Explicitly reject the dev literal
  if ($token === $invalid_token_literal) {
    $out['message'] = 'Invalid token. Access denied.';
    echo json_encode($out, JSON_PRETTY_PRINT);
    exit;
  }

  if (!function_exists('get_apibld_key')) {
    $out['message'] = 'Token validator not available.';
    echo json_encode($out, JSON_PRETTY_PRINT);
    exit;
  }

  $valid_token = get_apibld_key(1); // user id 1
  if (!$valid_token || $token !== $valid_token) {
    $out['message'] = 'Invalid token. Access denied.';
    echo json_encode($out, JSON_PRETTY_PRINT);
    exit;
  }
} else {
  // --- CLI CONTEXT: no token required ---
  // Optional: allow passing a token for debugging, but do not enforce it.
  // Example: php cron-trigger.php SOME_TOKEN
  global $argv;
  if (!empty($argv[1])) {
    // Currently ignored; you could log it if you like.
    $cliToken = $argv[1];
  }
}


// 2) Clear stale running jobs (> $stale_minutes old)
// NOTE: We don't have a dedicated "running_since" column, so we proxy using
// GREATEST(last_ran, created). If a job was set running long ago and crashed,
// this will free it.
$db->query("
  UPDATE cron_settings
     SET running = 0
   WHERE running = 1
     AND UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(GREATEST(IFNULL(last_ran, '2000-01-01 00:00:00'), created)) > ?
", [$stale_minutes * 60]);
$out['stale_cleanup'] = $db->error() ? $db->errorString() : 'stale cleanup OK';


// 3) Busy guard — if running count >= max_running, bail out
$rc              = $db->query("SELECT COUNT(*) AS c FROM cron_settings WHERE running = 1")->first();
$current_running = (int)($rc->c ?? 0);
if ($current_running >= $max_running) {
  $out['status']  = 'busy';
  $out['message'] = 'Cron runner is busy.';
  $out['running'] = $current_running;
  $out['max']     = $max_running;
  echo json_encode($out, JSON_PRETTY_PRINT);
  exit;
}


// 4) Find ONE due job (active, not running, due by seconds since last_ran)
$sql = "
  SELECT id, filename, seconds, last_ran, hits, created
    FROM cron_settings
   WHERE active = 1
     AND running = 0
     AND filename IS NOT NULL
     AND filename <> ''
     AND seconds > 0
     AND (UNIX_TIMESTAMP(NOW()) >= UNIX_TIMESTAMP(IFNULL(last_ran, '2000-01-01 00:00:00')) + seconds)
ORDER BY last_ran ASC
   LIMIT 1
";
$job = $db->query($sql)->first();

if (!$job) {
  $out['status']    = 'success';
  $out['message']   = 'No jobs due.';
  $out['next_cron'] = null;
  echo json_encode($out, JSON_PRETTY_PRINT);
  exit;
}


// 5) Atomically claim it (avoid races)
$claim = $db->query("UPDATE cron_settings SET running = 1 WHERE id = ? AND running = 0", [$job->id]);
if ($db->error() || $db->count() !== 1) {
  // Someone else grabbed it
  $out['status']    = 'success';
  $out['message']   = 'Job contention; another worker claimed the job.';
  $out['next_cron'] = null;
  echo json_encode($out, JSON_PRETTY_PRINT);
  exit;
}


// 6) Validate filename and path
$fname = (string)$job->filename;
if (!preg_match('/^[A-Za-z0-9._-]+\.php$/', $fname) || $fname[0] === '.') {
  // Unsafe; deactivate and release
  $db->query("UPDATE cron_settings SET active = 0, running = 0 WHERE id = ?", [$job->id]);
  $out['status']  = 'error';
  $out['message'] = 'Unsafe cron filename; deactivated.';
  $out['job']     = ['id' => (int)$job->id, 'filename' => $fname];
  echo json_encode($out, JSON_PRETTY_PRINT);
  exit;
}

$cronDir = rtrim($abs_us_root . $us_url_root . 'users/cron2/', '/') . '/';
$jobPath = $cronDir . $fname;

if (!file_exists($jobPath)) {
  // Missing file; deactivate and release
  $db->query("UPDATE cron_settings SET active = 0, running = 0 WHERE id = ?", [$job->id]);
  $out['status']  = 'error';
  $out['message'] = 'Cron file not found; entry deactivated.';
  $out['job']     = ['id' => (int)$job->id, 'filename' => $fname];
  echo json_encode($out, JSON_PRETTY_PRINT);
  exit;
}


// 7) Run the job; ALWAYS release running afterwards
set_time_limit(120);
if (!defined('CRON_OK')) {
  define('CRON_OK', true);
}

$ok     = true;
$errMsg = null;
$output = '';

try {
  ob_start();
  require $jobPath; // job may echo output
  $output = ob_get_clean();
} catch (Throwable $e) {
  if (ob_get_level() > 0) {
    @ob_end_clean();
  }
  $ok     = false;
  $errMsg = $e->getMessage();
  $out['error_line'] = $e->getLine();
  $out['error_file'] = $e->getFile();

    logger(0, 'USPlugins', "cron job {$fname} error: " . $errMsg);
  
} finally {
  // Always release the running lock
  $db->query("UPDATE cron_settings SET running = 0 WHERE id = ?", [$job->id]);
}


// 8) On success, stamp last_ran and increment hits
if ($ok) {
  $db->query("UPDATE cron_settings SET last_ran = NOW(), hits = hits + 1 WHERE id = ?", [$job->id]);
}


// 9) Response
$out['status']  = $ok ? 'success' : 'error';
$out['message'] = $ok ? 'Job executed.' : ('Job error: ' . $errMsg . ' on line ' . ($out['error_line'] ?? 'unknown'));


$out['job']     = [
  'id'       => (int)$job->id,
  'filename' => $fname,
  'interval' => [
    'seconds' => (int)$job->seconds,
    'minutes' => (int)round(((int)$job->seconds) / 60),
  ],
];

if ($output !== '') {
  $max = 4000;
  if (strlen($output) > $max) {
    $output = substr($output, 0, $max) . "\n...[truncated]...";
  }
  $out['job_output'] = $output;
}

echo json_encode($out, JSON_PRETTY_PRINT);
$end_time = time() ;
$total_time = td_total_time($start_time, $end_time);
$total = $end_time - $start_time ;
$sql = "INSERT INTO `td_cron_time_log` (`timestamp`, `crontime`) VALUES (CURRENT_TIMESTAMP, '".$total."');";  $db->query($sql) ;
$sql = "UPDATE `td_settings` SET `value` = '".$total_time."' WHERE `td_settings`.`id` = 2;"; $db->query($sql) ;
$sql = "SELECT AVG(`crontime`) AS avg_crontime FROM `td_cron_time_log` WHERE `timestamp` >= (UTC_TIMESTAMP() - INTERVAL 1 DAY);" ; 
$db->query($sql) ;
$results = $db->results() ;
foreach($results as $row) {
  $avg_crontime = $row->avg_crontime ;
  $sql2 = "UPDATE `td_settings` SET `value` = '".trim($avg_crontime)."' WHERE `td_settings`.`id` = 3;";
  $db->query($sql2) ;
}