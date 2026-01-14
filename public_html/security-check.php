<?php
/**
 * Project: UserSpice Security Check
 * Framework: UserSpice 5
 * File: public_html/security-check.php
 * Date: 2026-01-14
 * Copyright: (c) Your Organization
 */

declare(strict_types=1);

require_once __DIR__ . '/users/init.php';

/*
  --- security_permission_types
  1: cpanel account  (/public_html)
  2: lightsail cpanel account (/public_html)
  3: lightsail ubuntu direct account (/public)
  4: aws ec2 ubuntu direct account  (/public)
*/

$security_permission_type = 1;
$user_id_restricted = 1; // UserSpice user_id allowed to access this script

// ?law=1 = snapshot mode (treat this host as canonical; output details, no PASS/FAIL)
$law_mode = (isset($_GET['law']) && (string)$_GET['law'] === '1');

// Safety for audits
@set_time_limit(0);

// ------------------------------------------------------------
// Auth (UserSpice session OR your get_user_id() helper if present)
// ------------------------------------------------------------
$this_user_id = 0;

if (isset($user) && $user->isLoggedIn()) {
  if (function_exists('get_user_id')) {
    $this_user_id = (int)get_user_id();
  } else {
    $this_user_id = (int)$user->data()->id;
  }
}

if ($this_user_id !== (int)$user_id_restricted) {
  http_response_code(403);
  echo 'Access Denied';
  exit;
}

// ------------------------------------------------------------
// Helpers
// ------------------------------------------------------------
function h(string $s): string {
  return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function stat_octal_perm(string $path): ?string {
  clearstatcache(true, $path);
  $st = @stat($path);
  if ($st === false) return null;
  return substr(sprintf('%04o', $st['mode'] & 07777), -4);
}

function stat_owner_name(string $path): ?string {
  clearstatcache(true, $path);
  $uid = @fileowner($path);
  if ($uid === false) return null;

  if (function_exists('posix_getpwuid')) {
    $pw = @posix_getpwuid($uid);
    if (is_array($pw) && isset($pw['name'])) return (string)$pw['name'];
  }

  return (string)$uid;
}

function stat_group_name(string $path): ?string {
  clearstatcache(true, $path);
  $gid = @filegroup($path);
  if ($gid === false) return null;

  if (function_exists('posix_getgrgid')) {
    $gr = @posix_getgrgid($gid);
    if (is_array($gr) && isset($gr['name'])) return (string)$gr['name'];
  }

  return (string)$gid;
}

function is_symlink_path(string $path): bool {
  clearstatcache(true, $path);
  return is_link($path);
}

function build_chmod_cmd(string $path, string $octalPerm): string {
  return "chmod {$octalPerm} " . escapeshellarg($path);
}

function build_chown_cmd(string $path, string $owner, string $group): string {
  return "chown {$owner}:{$group} " . escapeshellarg($path);
}

function li(string $status, string $label, string $detail = ''): void {
  // status: PASS|WARN|FAIL|INFO
  $valid = ['PASS','WARN','FAIL','INFO'];
  if (!in_array($status, $valid, true)) $status = 'INFO';

  echo '<li><strong>' . h($status) . '</strong> — ' . h($label);
  if ($detail !== '') {
    echo '<br><code>' . h($detail) . '</code>';
  }
  echo "</li>\n";
}

function recommend_block(string $title, array $cmds): void {
  echo '<h4>' . h($title) . "</h4>\n";
  echo "<pre style=\"white-space:pre-wrap;\">".h(implode("\n", $cmds))."</pre>";
}

function add_issue(
  array &$issues,
  array &$recommend_cmds_root,
  array &$recommend_cmds_user,
  string $severity, // FAIL|WARN
  string $path,
  string $reason,
  ?string $cmd_root = null,
  ?string $cmd_user = null
): void {
  $issues[] = [
    'severity' => $severity,
    'path' => $path,
    'reason' => $reason,
    'cmd_root' => $cmd_root,
    'cmd_user' => $cmd_user,
  ];
  if ($cmd_root) $recommend_cmds_root[] = $cmd_root;
  if ($cmd_user) $recommend_cmds_user[] = $cmd_user;
}

function print_issue_summary(array $issues): void {
  if (count($issues) === 0) return;

  echo '<h4>ISSUES (Explicit)</h4>';
  echo '<ol>';
  foreach ($issues as $i) {
    echo '<li><strong>' . h($i['severity']) . '</strong> — <code>' . h($i['path']) . "</code><br>";
    echo h($i['reason']) . "</li>\n";
  }
  echo '</ol>';
}

/**
 * Controlled recursive snapshot within public_html only:
 * - Scans with max entry cap (always finishes)
 * - Prints top patterns + outliers
 */
function snapshot_tree_controlled(
  string $label,
  string $root,
  int $max_entries,
  int $max_outliers
): void {
  echo '<h4>' . h($label) . '</h4>';
  echo '<p><code>' . h($root) . "</code></p>";

  if (!file_exists($root)) {
    echo '<p><strong>MISSING</strong></p>';
    return;
  }
  if (is_symlink_path($root)) {
    echo '<p><strong>SYMLINK</strong> (not expanded)</p>';
    return;
  }

  $counts = [];
  $samples = [];
  $total = 0;

  $root_real = rtrim($root, '/');
  $it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root_real, FilesystemIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
  );

  foreach ($it as $info) {
    if ($total >= $max_entries) break;

    /** @var SplFileInfo $info */
    $path = $info->getPathname();
    if (is_link($path)) continue;

    $type = $info->isDir() ? 'dir' : ($info->isFile() ? 'file' : 'other');
    if ($type === 'other') continue;

    $owner = stat_owner_name($path) ?? 'NULL';
    $group = stat_group_name($path) ?? 'NULL';
    $perm  = stat_octal_perm($path) ?? 'NULL';

    $key = "{$type}|{$owner}|{$group}|{$perm}";
    $counts[$key] = ($counts[$key] ?? 0) + 1;
    if (!isset($samples[$key])) $samples[$key] = $path;

    $total++;
  }

  arsort($counts);

  echo '<p>Entries scanned: <strong>' . h((string)$total) . '</strong> | Max entries cap: <strong>' . h((string)$max_entries) . "</strong></p>";

  echo '<table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;">';
  echo '<tr><th>Count</th><th>Type</th><th>Owner</th><th>Group</th><th>Perm</th><th>Example Path</th></tr>';

  $rank = 0;
  foreach ($counts as $key => $count) {
    $rank++;
    if ($rank > 12) break;
    [$type, $owner, $group, $perm] = explode('|', $key, 4);

    echo '<tr>';
    echo '<td>' . h((string)$count) . '</td>';
    echo '<td>' . h($type) . '</td>';
    echo '<td>' . h($owner) . '</td>';
    echo '<td>' . h($group) . '</td>';
    echo '<td><code>' . h($perm) . '</code></td>';
    echo '<td><code>' . h($samples[$key]) . '</code></td>';
    echo '</tr>';
  }
  echo '</table>';

  // Majority per type
  $topDirKey = null;
  $topFileKey = null;
  foreach ($counts as $key => $count) {
    if (strpos($key, 'dir|') === 0 && $topDirKey === null) $topDirKey = $key;
    if (strpos($key, 'file|') === 0 && $topFileKey === null) $topFileKey = $key;
    if ($topDirKey !== null && $topFileKey !== null) break;
  }

  $outliers = [];
  if ($total > 0) {
    $seen = 0;
    $it2 = new RecursiveIteratorIterator(
      new RecursiveDirectoryIterator($root_real, FilesystemIterator::SKIP_DOTS),
      RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($it2 as $info2) {
      if ($seen >= $max_entries) break;
      /** @var SplFileInfo $info2 */
      $path = $info2->getPathname();
      if (is_link($path)) continue;

      $type = $info2->isDir() ? 'dir' : ($info2->isFile() ? 'file' : 'other');
      if ($type === 'other') continue;

      $owner = stat_owner_name($path) ?? 'NULL';
      $group = stat_group_name($path) ?? 'NULL';
      $perm  = stat_octal_perm($path) ?? 'NULL';
      $key = "{$type}|{$owner}|{$group}|{$perm}";

      $isOutlier = ($type === 'dir' && $topDirKey !== null && $key !== $topDirKey)
        || ($type === 'file' && $topFileKey !== null && $key !== $topFileKey);

      if ($isOutlier) {
        $outliers[] = "{$type} {$perm} {$owner}:{$group} {$path}";
        if (count($outliers) >= $max_outliers) break;
      }

      $seen++;
    }
  }

  echo '<h5>Outliers (first ' . h((string)$max_outliers) . ')</h5>';
  if (count($outliers) === 0) {
    echo '<p>None.</p>';
  } else {
    echo "<pre style=\"white-space:pre-wrap;\">" . h(implode("\n", $outliers)) . "</pre>";
  }
}

/**
 * Checks a path exact with PASS/WARN/FAIL outcome and collects issues.
 */
function check_path_exact_tri(
  string $label,
  string $path,
  string $expected_type, // file|dir
  string $expected_owner,
  array $expected_groups, // list of allowed groups
  array $expected_perms,  // list of allowed perms
  array $warn_perms,      // list of perms that should WARN (not fail)
  array &$issues,
  array &$recommend_cmds_root,
  array &$recommend_cmds_user
): void {
  if (!file_exists($path)) {
    li('FAIL', "{$label} missing", $path);
    add_issue($issues, $recommend_cmds_root, $recommend_cmds_user, 'FAIL', $path, "{$label} missing");
    return;
  }
  if (is_symlink_path($path)) {
    li('FAIL', "{$label} is a symlink (not allowed)", $path);
    add_issue($issues, $recommend_cmds_root, $recommend_cmds_user, 'FAIL', $path, "{$label} is a symlink (not allowed)");
    return;
  }

  $okType = ($expected_type === 'dir') ? is_dir($path) : is_file($path);
  if (!$okType) {
    li('FAIL', "{$label} type check", "expected={$expected_type}");
    add_issue($issues, $recommend_cmds_root, $recommend_cmds_user, 'FAIL', $path, "{$label}: wrong type, expected {$expected_type}");
    return;
  }

  $perm  = stat_octal_perm($path) ?? 'NULL';
  $owner = stat_owner_name($path) ?? 'NULL';
  $group = stat_group_name($path) ?? 'NULL';

  li('INFO', "{$label} stat snapshot", "owner={$owner} group={$group} perm={$perm}");

  if ($owner !== $expected_owner) {
    li('FAIL', "{$label} owner check", "expected={$expected_owner} actual={$owner}");
    add_issue(
      $issues, $recommend_cmds_root, $recommend_cmds_user,
      'FAIL', $path,
      "{$label}: owner is {$owner} but should be {$expected_owner}",
      build_chown_cmd($path, $expected_owner, $expected_groups[0] ?? $expected_owner),
      null
    );
  } else {
    li('PASS', "{$label} owner check", "expected={$expected_owner} actual={$owner}");
  }

  if (!in_array($group, $expected_groups, true)) {
    li('FAIL', "{$label} group check", "expected in=[" . implode(',', $expected_groups) . "] actual={$group}");
    add_issue(
      $issues, $recommend_cmds_root, $recommend_cmds_user,
      'FAIL', $path,
      "{$label}: group is {$group} but should be one of [" . implode(',', $expected_groups) . "]",
      build_chown_cmd($path, $expected_owner, $expected_groups[0] ?? $expected_owner),
      null
    );
  } else {
    li('PASS', "{$label} group check", "expected in=[" . implode(',', $expected_groups) . "] actual={$group}");
  }

  if (in_array($perm, $expected_perms, true)) {
    li('PASS', "{$label} perm check", "expected in=[" . implode(',', $expected_perms) . "] actual={$perm}");
    return;
  }

  if (in_array($perm, $warn_perms, true)) {
    li('WARN', "{$label} perm check", "preferred=" . implode(',', $expected_perms) . " actual={$perm}");
    add_issue(
      $issues, $recommend_cmds_root, $recommend_cmds_user,
      'WARN', $path,
      "{$label}: perm is {$perm} (allowed but not preferred). Preferred: [" . implode(',', $expected_perms) . "]",
      build_chmod_cmd($path, $expected_perms[0]),
      build_chmod_cmd($path, $expected_perms[0])
    );
    return;
  }

  li('FAIL', "{$label} perm check", "expected in=[" . implode(',', $expected_perms) . "] actual={$perm}");
  add_issue(
    $issues, $recommend_cmds_root, $recommend_cmds_user,
    'FAIL', $path,
    "{$label}: perm is {$perm} but should be one of [" . implode(',', $expected_perms) . "]",
    build_chmod_cmd($path, $expected_perms[0]),
    build_chmod_cmd($path, $expected_perms[0])
  );
}

/**
 * Uniform tree check with a single default rule and explicit exceptions:
 * - default dirs: expected_dir_perm
 * - default files: expected_file_perm
 * - exceptions: exact relative path => allowlist perms (and optional warn perms)
 */
function check_tree_uniform_tri(
  string $label,
  string $root,
  string $expected_owner,
  string $expected_group,
  string $expected_dir_perm,
  string $expected_file_perm,
  array $file_perm_exceptions, // rel => ['pass'=>[...], 'warn'=>[...]]
  array &$issues,
  array &$recommend_cmds_root,
  array &$recommend_cmds_user
): void {
  if (!file_exists($root)) {
    li('FAIL', "{$label} missing", $root);
    add_issue($issues, $recommend_cmds_root, $recommend_cmds_user, 'FAIL', $root, "{$label} missing");
    return;
  }
  if (is_symlink_path($root)) {
    li('FAIL', "{$label} root is a symlink (not allowed)", $root);
    add_issue($issues, $recommend_cmds_root, $recommend_cmds_user, 'FAIL', $root, "{$label} root is a symlink (not allowed)");
    return;
  }

  $root_real = rtrim($root, '/');
  $it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root_real, FilesystemIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
  );

  foreach ($it as $info) {
    /** @var SplFileInfo $info */
    $path = $info->getPathname();
    if (is_link($path)) continue;

    $isDir = $info->isDir();
    $isFile = $info->isFile();
    if (!$isDir && !$isFile) continue;

    $rel = substr($path, strlen($root_real) + 1);

    $owner = stat_owner_name($path) ?? 'NULL';
    $group = stat_group_name($path) ?? 'NULL';
    $perm  = stat_octal_perm($path) ?? 'NULL';

    // Owner/group: must match exactly in these core trees
    if ($owner !== $expected_owner) {
      li('FAIL', "{$label}: owner mismatch", "{$path} expected={$expected_owner} actual={$owner}");
      add_issue(
        $issues, $recommend_cmds_root, $recommend_cmds_user,
        'FAIL', $path,
        "{$label}: owner is {$owner} but should be {$expected_owner}",
        build_chown_cmd($path, $expected_owner, $expected_group),
        null
      );
    }

    if ($group !== $expected_group) {
      li('FAIL', "{$label}: group mismatch", "{$path} expected={$expected_group} actual={$group}");
      add_issue(
        $issues, $recommend_cmds_root, $recommend_cmds_user,
        'FAIL', $path,
        "{$label}: group is {$group} but should be {$expected_group}",
        build_chown_cmd($path, $expected_owner, $expected_group),
        null
      );
    }

    // Perms
    if ($isDir) {
      if ($perm !== $expected_dir_perm) {
        li('FAIL', "{$label}: dir perm mismatch", "{$path} expected={$expected_dir_perm} actual={$perm}");
        add_issue(
          $issues, $recommend_cmds_root, $recommend_cmds_user,
          'FAIL', $path,
          "{$label}: dir perm is {$perm} but should be {$expected_dir_perm}",
          build_chmod_cmd($path, $expected_dir_perm),
          build_chmod_cmd($path, $expected_dir_perm)
        );
      }
      continue;
    }

    // File perms (default + exception rules)
    $passPerms = [$expected_file_perm];
    $warnPerms = [];

    if (isset($file_perm_exceptions[$rel])) {
      $ex = $file_perm_exceptions[$rel];
      if (isset($ex['pass']) && is_array($ex['pass']) && count($ex['pass']) > 0) $passPerms = $ex['pass'];
      if (isset($ex['warn']) && is_array($ex['warn'])) $warnPerms = $ex['warn'];
    }

    if (in_array($perm, $passPerms, true)) {
      // ok
      continue;
    }

    if (in_array($perm, $warnPerms, true)) {
      li('WARN', "{$label}: file perm (allowed but not preferred)", "{$path} preferred=" . implode(',', $passPerms) . " actual={$perm}");
      add_issue(
        $issues, $recommend_cmds_root, $recommend_cmds_user,
        'WARN', $path,
        "{$label}: file perm is {$perm} (allowed but not preferred). Preferred: [" . implode(',', $passPerms) . "]",
        build_chmod_cmd($path, $passPerms[0]),
        build_chmod_cmd($path, $passPerms[0])
      );
      continue;
    }

    li('FAIL', "{$label}: file perm mismatch", "{$path} expected in=[" . implode(',', $passPerms) . "] actual={$perm}");
    add_issue(
      $issues, $recommend_cmds_root, $recommend_cmds_user,
      'FAIL', $path,
      "{$label}: file perm is {$perm} but should be one of [" . implode(',', $passPerms) . "]",
      build_chmod_cmd($path, $passPerms[0]),
      build_chmod_cmd($path, $passPerms[0])
    );
  }
}

// ------------------------------------------------------------
// Start output
// ------------------------------------------------------------
echo "<!doctype html><html><body>";
echo "<h3>UserSpice Permission Check</h3>";

if (!isset($abs_us_home) || !is_string($abs_us_home) || trim($abs_us_home) === '') {
  echo '<h3>OVERALL: FAIL</h3>';
  echo '<p>$abs_us_home is not set.</p>';
  echo "</body></html>";
  exit;
}

$abs_us_home = rtrim($abs_us_home, '/') . '/';
$home_path = rtrim($abs_us_home, '/');
$cpanel_user = basename($home_path);
$public_html = $home_path . '/public_html';

// ------------------------------------------------------------
// LAW SNAPSHOT MODE (public_html only)
// ------------------------------------------------------------
if ($law_mode) {
  echo '<h3>LAW SNAPSHOT MODE (law=1) — public_html only</h3>';
  echo '<p><strong>' . h($cpanel_user) . '</strong></p>';

  snapshot_tree_controlled('public_html snapshot', $public_html, 40000, 200);
  snapshot_tree_controlled('public_html/users snapshot', $public_html . '/users', 40000, 200);
  snapshot_tree_controlled('public_html/usersc snapshot', $public_html . '/usersc', 40000, 200);
  snapshot_tree_controlled('public_html/usersc/templates snapshot', $public_html . '/usersc/templates', 40000, 250);
  snapshot_tree_controlled('public_html/usersc/templates/customizer snapshot', $public_html . '/usersc/templates/customizer', 40000, 250);

  echo "</body></html>";
  exit;
}

// ------------------------------------------------------------
// Normal validation mode (Type 1 core rules)
// ------------------------------------------------------------
$issues = [];
$recommend_cmds_root = [];
$recommend_cmds_user = [];

echo "<ol>";
li('PASS', '$abs_us_home detected', $abs_us_home);
li('PASS', 'Derived account username (from $abs_us_home)', $cpanel_user);

$looks_like_home = (strpos($abs_us_home, '/home/') === 0);
li($looks_like_home ? 'PASS' : 'FAIL', 'Home path starts with /home/', $abs_us_home);
if (!$looks_like_home) {
  add_issue($issues, $recommend_cmds_root, $recommend_cmds_user, 'FAIL', $abs_us_home, 'Home path does not start with /home/');
}

// Top-level type-1 directory expectations (previously established for type 1)
li('INFO', 'Type 1 expectations (LAW)', 'home=0711 owner=<user> group=<user>; public_html=0750 owner=<user> group=nobody');

// Home dir: exact (FAIL only)
check_path_exact_tri(
  'Home directory',
  $home_path,
  'dir',
  $cpanel_user,
  [$cpanel_user],
  ['0711'],
  [],
  $issues,
  $recommend_cmds_root,
  $recommend_cmds_user
);

// public_html: allow group nobody or user (as seen in the wild), but perm must be 0750
check_path_exact_tri(
  'public_html',
  $public_html,
  'dir',
  $cpanel_user,
  ['nobody', $cpanel_user],
  ['0750'],
  [],
  $issues,
  $recommend_cmds_root,
  $recommend_cmds_user
);

// public_html core top-level items
li('INFO', 'public_html core top-level items', 'index.php & z_us_root.php = 0644 user:user; users & usersc = 0755 user:user');

check_path_exact_tri(
  'public_html/index.php',
  $public_html . '/index.php',
  'file',
  $cpanel_user,
  [$cpanel_user],
  ['0644'],
  [],
  $issues,
  $recommend_cmds_root,
  $recommend_cmds_user
);

check_path_exact_tri(
  'public_html/z_us_root.php',
  $public_html . '/z_us_root.php',
  'file',
  $cpanel_user,
  [$cpanel_user],
  ['0644'],
  [],
  $issues,
  $recommend_cmds_root,
  $recommend_cmds_user
);

check_path_exact_tri(
  'public_html/users',
  $public_html . '/users',
  'dir',
  $cpanel_user,
  [$cpanel_user],
  ['0755'],
  [],
  $issues,
  $recommend_cmds_root,
  $recommend_cmds_user
);

check_path_exact_tri(
  'public_html/usersc',
  $public_html . '/usersc',
  'dir',
  $cpanel_user,
  [$cpanel_user],
  ['0755'],
  [],
  $issues,
  $recommend_cmds_root,
  $recommend_cmds_user
);

// Core tree checks (only users/ and usersc/ and templates/ are enforced)
// users/ tree: default file=0644 dir=0755; exception: init.php PASS=0644, WARN=0766
$users_file_exceptions = [
  'init.php' => [
    'pass' => ['0644'],
    'warn' => ['0766'],
  ],
];

check_tree_uniform_tri(
  'users/ tree',
  $public_html . '/users',
  $cpanel_user,
  $cpanel_user,
  '0755',
  '0644',
  $users_file_exceptions,
  $issues,
  $recommend_cmds_root,
  $recommend_cmds_user
);

// usersc/ tree: default file=0644 dir=0755, owner/group user:user
check_tree_uniform_tri(
  'usersc/ tree',
  $public_html . '/usersc',
  $cpanel_user,
  $cpanel_user,
  '0755',
  '0644',
  [],
  $issues,
  $recommend_cmds_root,
  $recommend_cmds_user
);

// templates (if present): same rule
check_tree_uniform_tri(
  'usersc/templates tree',
  $public_html . '/usersc/templates',
  $cpanel_user,
  $cpanel_user,
  '0755',
  '0644',
  [],
  $issues,
  $recommend_cmds_root,
  $recommend_cmds_user
);

// customizer exists sometimes; we do NOT enforce it unless it exists, and we keep it informational
$customizer_path = $public_html . '/usersc/templates/customizer';
if (is_dir($customizer_path)) {
  li('INFO', 'customizer template detected', $customizer_path);
  // Intentionally not enforced here yet; can be added later when you define its LAW by snapshot.
} else {
  li('INFO', 'customizer template missing', 'skipped');
}

echo "</ol>";

// ------------------------------------------------------------
// OVERALL verdict
// - FAIL if any FAIL issues exist
// - otherwise WARN if any WARN issues exist
// - else PASS
// ------------------------------------------------------------
$fail_count = 0;
$warn_count = 0;
foreach ($issues as $i) {
  if ($i['severity'] === 'FAIL') $fail_count++;
  if ($i['severity'] === 'WARN') $warn_count++;
}

if ($fail_count > 0) {
  echo '<h3>OVERALL: FAIL</h3>';
} elseif ($warn_count > 0) {
  echo '<h3>OVERALL: WARN</h3>';
} else {
  echo '<h3>OVERALL: PASS</h3>';
}

echo '<p>FAIL items: <strong>' . h((string)$fail_count) . '</strong> | WARN items: <strong>' . h((string)$warn_count) . '</strong></p>';

print_issue_summary($issues);

$unique_root = array_values(array_unique($recommend_cmds_root));
$unique_user = array_values(array_unique($recommend_cmds_user));

if (count($unique_root) > 0) {
  recommend_block('Recommended fix commands (run as root / WHM terminal)', array_merge(
    ["# NOTE: Suggestions only. Review before running."],
    $unique_root
  ));
}

if (count($unique_user) > 0) {
  recommend_block('Recommended fix commands (run as account user in cPanel terminal)', array_merge(
    ["# NOTE: No chown here (you likely cannot chown as the account user)."],
    $unique_user
  ));
}

echo "</body></html>";
