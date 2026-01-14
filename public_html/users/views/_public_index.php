<!doctype html><?php
/**
 * Project: Project M1 Dashboard
 * Framework: UserSpice x
 * File: public_html/users/views/_public_index.php
 * Date: 2026-01-07
 * Version 2
 * Copyright: (c) TransitSecurityReport.com / TocsinData.com
 */
?>
 
  <style>
    html, body { height: 100%; margin: 0; }
    body { background: #0b0f14; overflow: hidden; font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; }

    .video-wrap { position: fixed; inset: 0; z-index: 1; }
    .video-wrap video { width: 100%; height: 100%; object-fit: cover; }

    .overlay {
      position: fixed; inset: 0; z-index: 2;
      display: flex; align-items: center; justify-content: center;
      text-align: center; padding: 24px;
      background: linear-gradient(180deg, rgba(0,0,0,0.55), rgba(0,0,0,0.65));
      color: #e9eef5;
    }

    .panel {
      max-width: 760px;
      background: rgba(10, 14, 20, 0.55);
      border: 1px solid rgba(255,255,255,0.10);
      border-radius: 14px;
      padding: 26px 22px;
      backdrop-filter: blur(6px);
    }

    .title { font-size: 22px; font-weight: 700; margin: 0 0 10px; letter-spacing: 0.2px; }
    .sub { font-size: 14px; opacity: 0.85; margin: 0 0 18px; }

    .btn-sm {
      padding: 7px 12px;
      font-size: 13px;
      border-radius: 8px;
      background: rgba(255,255,255,0.10);
      border: 1px solid rgba(255,255,255,0.20);
      color: #e9eef5;
    }
    .btn-sm:hover { background: rgba(255,255,255,0.18); }

    .btn-row {
      display: flex;
      gap: 10px;
      justify-content: center;
      margin-top: 10px;
    }

    .btn-login {
      display: inline-block;
      padding: 12px 20px;
      font-size: 15px;
      font-weight: 600;
      border-radius: 12px;
      border: 1px solid rgba(255,255,255,0.22);
      color: #ffffff;
      text-decoration: none;
      background: rgba(40, 120, 255, 0.85);
    }
    .btn-login:hover {
      background: rgba(40, 120, 255, 1);
    }


  </style>
 
  <div class="video-wrap" aria-hidden="true"
     style="background: url('<?=htmlspecialchars($us_url_root, ENT_QUOTES, 'UTF-8');?>assets/images/project-m1-landing.png') center center / cover no-repeat;">
</div>


  <div class="overlay">
    <div class="panel">
      <h1 class="title">Members 1st Security and Alert Dashboard</h1>
	  <img src="/assets/images/lg-logo.svg" style="width:250px; max-width:100%; height:auto;" alt="Logo">

      <p class="sub">Please login</p>
            <a class="btn-login" href="<?=htmlspecialchars($us_url_root, ENT_QUOTES, 'UTF-8');?>login">Login</a>


      <div class="btn-row">
        <a class="btn-sm" href="<?=htmlspecialchars($us_url_root, ENT_QUOTES, 'UTF-8');?>register">Register</a>
        <a class="btn-sm" href="<?=htmlspecialchars($us_url_root, ENT_QUOTES, 'UTF-8');?>help">Help</a>
      </div>

    </div>
  </div> 