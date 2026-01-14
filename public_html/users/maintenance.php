<?php
/**
 * Project: Project M1 Dashboard
 * Framework: UserSpice x
 * File: public_html/users/maintenance.php
 * Date: 2026-01-07
 * Project version 2
 * Copyright: (c) TransitSecurityReport.com / TocsinData.com
 */

require_once '../users/init.php';
//require_once $abs_us_root.$us_url_root.'users/includes/template/prep.php';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Maintenance</title>
  <style>
    html,body{height:100%;margin:0}
    body{display:flex;align-items:center;justify-content:center;font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;background:#0b0f14;color:#e6edf3}
    .card{max-width:520px;padding:28px 26px;border:1px solid rgba(255,255,255,.12);border-radius:14px;background:rgba(255,255,255,.04)}
    h1{margin:0 0 8px;font-size:20px}
    p{margin:0;color:rgba(230,237,243,.82);line-height:1.45}
    .small{margin-top:14px;font-size:12px;color:rgba(230,237,243,.6)}
  </style>
</head>
<body>
  <div class="card" role="status" aria-live="polite">
    <h1>Scheduled Maintenance</h1>
    <p>This service is temporarily unavailable while we perform routine maintenance. Please check back shortly.</p>
    <div class="small">Thank you for your patience.</div>
  </div>
</body>
</html>