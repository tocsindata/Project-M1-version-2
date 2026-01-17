<?php
$label = "SAMPLE CRON";
$startedAt = microtime(true);

// file public_html/users/cron2/cron-sample.php
// Deny *direct* web hits to this file, allow only when included.
// 1) If the executing script is *this* file, it's a direct hit → block.
$executedDirectly = realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__;

// 2) Allow if cron-trigger included us (constant or server flag).
$triggeredByCron = (defined('USING_CRON_TRIGGER') && USING_CRON_TRIGGER === true)
                   || (($_SERVER['TD_CRON_TRIGGER'] ?? '') === '1');

if ($executedDirectly && !$triggeredByCron) {
  // Block direct access in web context
  if (PHP_SAPI !== 'cli') {
    http_response_code(403);
  }
  exit('This script can only be run via the cron trigger.');
}
if(!isset($out)) {
    $out = [] ;
}
$out[][$label][] = "Started at: ".date("Y-m-d H:i:s"); // MUST USE THIS FORMAT!

$db = DB::getInstance();

// ======================================================================
// Local helpers (cron-scope only) STARTS
// ======================================================================
if(!function_exists('foobar')){
    function foobar() {
        $db = DB::getInstance();
        $foobar = '' ;
        $sql = "SELECT * FROM users WHERE id = 1";
        $db->query($sql) ;
        $results = $db->results() ;
        return $foobar ;
    }
}


// ======================================================================
// Local helpers (cron-scope only) ENDS
// ======================================================================
$elapsed = round((microtime(true) - $startedAt), 3);
logger(0, $label.' completed in {$elapsed}s.");