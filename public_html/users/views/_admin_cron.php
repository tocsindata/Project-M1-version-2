<?php
// file: users/views/_admin_cron2.php  
// requires entry in users/modules/views.php and usersc/widgets/tools/widget.php
// Purpose: Manage custom cron entries stored in `cron_settings`.
// - Actions are POST-only and CSRF-protected 
// - Interval edited in MINUTES (persisted in DB field `seconds`)
// - Optional "sync" to discover PHP files in users/cron2/ and insert if missing

$errors = $successes = [];
$form_valid = true;
if (!hasPerm(1)) {
  die("You do not have permission to view this page.");
}
// CSRF token for this render
$cs = Token::generate();

$db = DB::getInstance();
// Human-readable interval helper (e.g., "1 day, 2 hours", "5 minutes")
if (!function_exists('td_human_interval')) {
  function td_human_interval($seconds, $style = 'long') {
    $seconds = (int)$seconds;
    if ($seconds < 1) return $style === 'short' ? '0s' : '0 seconds';

    $units = [
      'week'   => 7 * 24 * 3600,
      'day'    => 24 * 3600,
      'hour'   => 3600,
      'minute' => 60,
      'second' => 1,
    ];

    $parts = []; 
    foreach ($units as $name => $size) {
      if ($seconds >= $size) {
        $val = intdiv($seconds, $size);
        $seconds %= $size;
        if ($style === 'short') {
          // w d h m s
          $abbr = $name[0];
          $parts[] = $val.$abbr;
        } else {
          $parts[] = $val.' '.$name.($val === 1 ? '' : 's');
        }
      }
      // Stop after 3 parts for readability in 'long', 2 parts in 'short'
      if ($style !== 'short' && count($parts) >= 3) break;
      if ($style === 'short' && count($parts) >= 2) break;
    }

    return implode($style === 'short' ? ' ' : ', ', $parts);
  }
}

// ---------- Handle POST actions ----------
if (!empty($_POST)) {
  $token = Input::get('csrf');
  if (!Token::check($token)) {
    // Per UserSpice pattern: include error partial then stop processing this request
    include($abs_us_root . $us_url_root . 'usersc/scripts/token_error.php');
    return;
  }

  $action = Input::get('action');

  // Toggle active (POST-only)
  if ($action === 'toggle' && Input::exists('post')) {
    $id = (int) Input::get('id');
    $state = (int) Input::get('state');

    if ($id > 0 && ($state === 0 || $state === 1)) {
      $db->update('cron_settings', $id, ['active' => $state]);
      if (!$db->error()) {
        $successes[] = "Cron ID #{$id} " . ($state ? "re-activated" : "de-activated") . ".";
      } else {
        $errors[] = "Error updating cron ID #{$id}. Please try again.";
        if (isset($user) && method_exists($user, 'data')) {
          logger($user->data()->id, 'USPlugins', "cron_settings toggle failed for id {$id}: ".$db->errorString());
        }
      }
    } else {
      $errors[] = "Invalid toggle request.";
    }
  }

  // Set interval in minutes (stores seconds in DB)
  if ($action === 'set_interval' && Input::exists('post')) {
    $id = (int) Input::get('id');
    $minutes = (int) Input::get('minutes');

    // sane bounds: 1 min to 24 hours
    if ($id > 0 && $minutes >= 1 && $minutes <= 604800) {
      $seconds = $minutes * 60;
      $db->update('cron_settings', $id, ['seconds' => $seconds]);
      if (!$db->error()) {
        $successes[] = "Cron ID #{$id} interval updated to {$minutes} minute(s).";
      } else {
        $errors[] = "Failed to update interval for ID #{$id}.";
        if (isset($user) && method_exists($user, 'data')) {
          logger($user->data()->id, 'USPlugins', "cron_settings interval update failed for id {$id}: ".$db->errorString());
        }
      }
    } else {
      $errors[] = "Invalid interval request.";
    }
  }

  // Sync cron files from folder into cron_settings
  if ($action === 'sync' && Input::exists('post')) {
    $cron_folder = rtrim($abs_us_root . $us_url_root . 'users/cron2/', '/') . '/';
    if (!is_dir($cron_folder)) {
      $errors[] = "Cron folder not found: " . htmlspecialchars($cron_folder, ENT_QUOTES, 'UTF-8');
    } else {
      $added = 0;
      $files = glob($cron_folder . '*.php') ?: [];
      foreach ($files as $fullpath) {
        $file = substr($fullpath, strlen($cron_folder));
        // Insert if not present
        $exists = $db->query("SELECT id FROM cron_settings WHERE filename = ?", [$file])->count();
        if ($exists === 0) {
          $ins = $db->insert('cron_settings', [
            'filename' => $file,
            'running'  => 0,
            'created'  => date('Y-m-d H:i:s'),
            'last_ran' => '2000-01-01 00:00:00',
            'seconds'  => 300, // default 5 min
            'hits'     => 0,
            'active'   => 1,
          ]);
          if ($ins) {
            $added++;
          } else {
            if (isset($user) && method_exists($user, 'data')) {
              logger($user->data()->id, 'USPlugins', "cron_settings insert failed for file {$file}: ".$db->errorString());
            }
          }
        }
      }
      $successes[] = "Cron sync complete" . ($added ? " — {$added} new file(s) added." : " — no new files.");
    }
  }
}
// ---------- Auto-discover new cron files (runs on every render) ----------
try {
  $cron_folder = rtrim($abs_us_root . $us_url_root . 'users/cron2/', '/') . '/';
  if (is_dir($cron_folder)) {
    // Build a set of existing filenames in DB to avoid N queries
    $existing = [];
    $rows = $db->query("SELECT filename FROM cron_settings")->results();
    foreach ($rows as $r) {
      // normalize to exact stored name
      $existing[$r->filename] = true;
    }

    // Scan the folder for *.php files
    $files = glob($cron_folder . '*.php') ?: [];
    $added = 0;

    foreach ($files as $fullpath) {
      // Defensive: only accept regular files & *.php
      if (!is_file($fullpath)) continue;

      $file = substr($fullpath, strlen($cron_folder));
      // Normalize and double-check extension
      $base = basename($file);
      if (strtolower(pathinfo($base, PATHINFO_EXTENSION)) !== 'php') continue;

      if (!isset($existing[$base])) {
        // Insert with sane defaults
        $ins = $db->insert('cron_settings', [
          'filename' => $base,
          'running'  => 0,
          'created'  => date('Y-m-d H:i:s'),
          'last_ran' => '2000-01-01 00:00:00',
          'seconds'  => 300, // default 5 min
          'hits'     => 0,
          'active'   => 1,
        ]);

        if ($ins) {
          $added++;
          $existing[$base] = true; // keep set in-sync
        } else {
          // Quiet UI; log details
          if (isset($user) && method_exists($user, 'data')) {
            logger($user->data()->id, 'USPlugins', "cron_settings auto-insert failed for file {$base}: ".$db->errorString());
          }
        }
      }
    }

    if ($added > 0) {
      $successes[] = "Auto-discovered {$added} new cron file(s) in users/cron2/.";
    }
  } else {
    // Only surface a message if admin might expect it to exist
    // (Comment this next line out if you prefer silent failure.)
    // $errors[] = "Cron folder not found: " . htmlspecialchars($cron_folder, ENT_QUOTES, 'UTF-8'); 
  }
} catch (Throwable $e) {
  if (isset($user) && method_exists($user, 'data')) {
    logger($user->data()->id, 'USPlugins', 'cron_settings auto-discover threw: '.$e->getMessage());
  }
}

// ---------- Load rows ----------
$results = $db->query("SELECT * FROM cron_settings ORDER BY last_ran DESC")->results();

?>
<h2>Custom Cron Manager</h2>
<p>Current Max Number of Running Cron Jobs is <b><?php echo td_settings('cron_max_running'); ?></b> , which last took <?php echo td_settings('cron_last_duration');  ?> to complete the most recent cronjob.</p>
<p>Average Total Loop: <?php echo td_human_seconds(td_settings('cron_avg_duration')) ; ?></p>
<?php if (!empty($errors) || !empty($successes)) { ?>
  <div id="messages" class="mb-3">
    <?php foreach ($successes as $m) { ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($m, ENT_QUOTES, 'UTF-8'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php } ?>
    <?php foreach ($errors as $m) { ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($m, ENT_QUOTES, 'UTF-8'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php } ?>
  </div>
<?php } ?>

<div class="card">
  <div class="card-body">
    <div class="d-flex justify-content-end gap-2 mb-2">
      <!-- Open modal -->
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addcron">
        <i class="fa fa-plus"></i> add
      </button>

      <!-- Sync cron files -->
      <form action="" method="POST" class="d-inline">
        <input type="hidden" name="csrf" value="<?= $cs; ?>">
        <input type="hidden" name="action" value="sync">
        <button type="submit" class="btn btn-outline-secondary">
          <i class="fa fa-rotate"></i> sync cron files
        </button>
      </form>
    </div>

    <table class="table table-bordered align-middle">
      <thead>
        <tr>
          <th class="text-center">ID</th>
          <th>Filename</th>
          <th class="text-center">Running</th>
          <th class="text-center">Created</th>
          <th class="text-center">Last Ran</th>
          <th class="text-center">Interval (min)</th>
          <th class="text-center">Hits</th>
          <th class="text-center">Active</th>
          <th class="text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($results as $row) { ?>
        <tr>
          <td class="text-center"><?= (int)$row->id; ?></td>
          <td><?= htmlspecialchars($row->filename, ENT_QUOTES, 'UTF-8'); ?></td>
          <td class="text-center"><?= (int)$row->running; ?></td>
          <td class="text-center"><?= htmlspecialchars($row->created, ENT_QUOTES, 'UTF-8'); ?></td>
          <td class="text-center"><?= htmlspecialchars($row->last_ran, ENT_QUOTES, 'UTF-8'); ?></td>

          <!-- Interval editor in MINUTES -->
          <td class="text-center">
            <?php $mins = max(1, (int) round(((int)$row->seconds) / 60)); ?>
            <form action="" method="POST" class="d-inline-flex align-items-center" style="gap:.4rem">
              <input type="hidden" name="csrf" value="<?= $cs; ?>">
              <input type="hidden" name="action" value="set_interval">
              <input type="hidden" name="id" value="<?= (int)$row->id; ?>">
              <input
                type="number"
                name="minutes"
                min="1"
                max="604800"
                step="1"
                value="<?= $mins; ?>"
                class="form-control form-control-sm text-end"
                style="width: 5.5rem;"
                aria-label="Interval in minutes"
                title="Interval in minutes (stored as seconds)"
              >
              <button type="submit" class="btn btn-sm btn-outline-primary">Save</button>
            </form>
            <?php
                $secs     = (int)$row->seconds;
                $friendly = td_human_interval($secs, 'long');  // e.g., "1 day, 2 hours"
                $compact  = td_human_interval($secs, 'short'); // e.g., "1d 2h"
              ?>
              <div class="text-muted small mt-1">
                ≈ <?= $secs; ?> sec • <?= htmlspecialchars($friendly, ENT_QUOTES, 'UTF-8'); ?>
                <span class="ms-1">(<?= htmlspecialchars($compact, ENT_QUOTES, 'UTF-8'); ?>)</span>
              </div>

          </td>

          <td class="text-center"><?= (int)$row->hits; ?></td>

          <td class="text-center">
            <?php if ((int)$row->active === 1) { ?>
              <span class="badge bg-success">Active</span>
            <?php } else { ?>
              <span class="badge bg-secondary">Inactive</span>
            <?php } ?>
          </td>

          <td class="text-center">
            <form action="" method="POST" class="d-inline">
              <input type="hidden" name="csrf" value="<?= $cs; ?>">
              <input type="hidden" name="action" value="toggle">
              <input type="hidden" name="id" value="<?= (int)$row->id; ?>">
              <input type="hidden" name="state" value="<?= (int)$row->active === 1 ? 0 : 1; ?>">
              <?php if ((int)$row->active === 1) { ?>
                <button type="submit" class="btn btn-warning btn-sm">De-Activate</button>
              <?php } else { ?>
                <button type="submit" class="btn btn-success btn-sm">Re-Activate</button>
              <?php } ?>
            </form>
          </td>
        </tr>
      <?php } ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add Cron modal (kept minimal; “sync” populates rows) -->
<div id="addcron" class="modal fade" role="dialog" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title mb-0">New Cron</h4>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" aria-label="Close">&times;</button>
      </div>
      <div class="modal-body">
        <p>Use <strong>Sync cron files</strong> to import PHP files from <code>users/cron2/</code>. You can then edit intervals and flags inline.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript" src="<?= $us_url_root ?>users/js/oce.js?v2"></script>
<script type="text/javascript">
  // Optional convenience messages sink for AJAX-based editors (if you add any)
  function messages(data) {
    try { data = (typeof data === 'string') ? JSON.parse(data) : data; } catch(e) { return; }
    const wrap = document.getElementById('messages');
    if (!wrap) return;
    const div = document.createElement('div');
    div.className = 'alert alert-' + (data.success === "true" ? 'success' : 'danger') + ' alert-dismissible fade show';
    div.innerText = data.msg || '';
    const btn = document.createElement('button');
    btn.type = 'button'; btn.className = 'btn-close'; btn.setAttribute('data-bs-dismiss','alert'); btn.setAttribute('aria-label','Close');
    div.appendChild(btn);
    wrap.appendChild(div);
    setTimeout(()=>{ div.remove(); }, 3000);
  }

  function success(resp) {
    console.log(resp);
    messages(resp);
  }

  document.addEventListener('DOMContentLoaded', function() {
    // If you later use oneClickEdit for inline fields:
    if (typeof jQuery !== 'undefined' && typeof jQuery.fn.oneClickEdit === 'function') {
      var options = {
        url: 'parsers/cron_post.php',
        token: '<?= $cs; ?>'
      };
      jQuery('.txt').oneClickEdit(options, success);

      var active = {
        url: 'parsers/cron_post.php',
        selectOptions: { 0: 'Inactive', 1: 'Active' },
        token: '<?= $cs; ?>'
      };
      jQuery('.cronactive').oneClickEdit(active, success);
    }

    // Example delete handler if you implement it in cron_post.php
    jQuery(document).on("click", "#deleteCron", function() {
      var deleteMe = jQuery(this).attr("data-value");
      jQuery.ajax({
        type: 'POST',
        url: 'parsers/cron_post.php',
        data: { value: deleteMe, field: 'deleteMe', token: '<?= $cs; ?>' },
        dataType: 'json'
      }).done(function() {
        window.location.assign(document.URL);
      });
    });
  });
</script>