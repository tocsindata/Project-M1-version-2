<?php
/**
 * Project: Project M1 Dashboard
 * Framework: UserSpice 6
 * File: public_html/usersc/includes/m1-display-functions.php
 * Date: 2026-01-15
 * Copyright: (c) TransitSecurityReport.com / TocsinData.com
 */
 

// display_dashboard_12cells example only dashboard
if (!function_exists('display_dashboard_12cells')) {
    function display_dashboard_12cells(int $this_user_id = 0): void
    {
        global $user, $abs_us_home, $abs_us_root, $abs_us_url;

        if ($this_user_id === 0) {
            echo '<div class="row m1-row"><p>FATAL ERROR: You are not logged in.</p></div>' . PHP_EOL;
            exit;
        }
 

echo '
<div class="dashboardc container-fluid">


  <!-- Row 1 -->
  <div class="row m1-row">

    <div class="col-xs-12 col-sm-6 col-md-3">
         <div class="dborder">
      <div class="panel panel-default">
        <div class="panel-body">
          <h4 style="margin-top:0;">Widget 1</h4>
          <p style="margin-bottom:0;">Example content for row 1, column 1.</p>
        </div>
      </div>
    </div>
    </div>

    <div class="col-xs-12 col-sm-6 col-md-3">
         <div class="dborder">
      <div class="panel panel-default">
        <div class="panel-body">
          <h4 style="margin-top:0;">Widget 2</h4>
          <p style="margin-bottom:0;">Example content for row 1, column 2.</p>
        </div>
      </div>
    </div>
    </div>

    <div class="col-xs-12 col-sm-6 col-md-3">
         <div class="dborder">
      <div class="panel panel-default">
        <div class="panel-body">
          <h4 style="margin-top:0;">Widget 3</h4>
          <p style="margin-bottom:0;">Example content for row 1, column 3.</p>
        </div>
      </div>
    </div>
    </div>

    <div class="col-xs-12 col-sm-6 col-md-3">
         <div class="dborder">
      <div class="panel panel-default">
        <div class="panel-body">
          <h4 style="margin-top:0;">Widget 4</h4>
          <p style="margin-bottom:0;">Example content for row 1, column 4.</p>
        </div>
      </div>
    </div>
    </div>

  </div>

  <!-- Row 2 -->
  <div class="row m1-row">

    <div class="col-xs-12 col-sm-6 col-md-3">
         <div class="dborder">
      <div class="panel panel-default">
        <div class="panel-body">
          <h4 style="margin-top:0;">Widget 5</h4>
          <p style="margin-bottom:0;">Example content for row 2, column 1.</p>
        </div>
      </div>
    </div>
    </div>

    <div class="col-xs-12 col-sm-6 col-md-3">
         <div class="dborder">
      <div class="panel panel-default">
        <div class="panel-body">
          <h4 style="margin-top:0;">Widget 6</h4>
          <p style="margin-bottom:0;">Example content for row 2, column 2.</p>
        </div>
      </div>
    </div>
    </div>

    <div class="col-xs-12 col-sm-6 col-md-3">
         <div class="dborder">
      <div class="panel panel-default">
        <div class="panel-body">
          <h4 style="margin-top:0;">Widget 7</h4>
          <p style="margin-bottom:0;">Example content for row 2, column 3.</p>
        </div>
      </div>
    </div>
    </div>

    <div class="col-xs-12 col-sm-6 col-md-3">
         <div class="dborder">
      <div class="panel panel-default">
        <div class="panel-body">
          <h4 style="margin-top:0;">Widget 8</h4>
          <p style="margin-bottom:0;">Example content for row 2, column 4.</p>
        </div>
      </div>
    </div>
    </div>

  </div>

  <!-- Row 3 -->
  <div class="row m1-row">

    <div class="col-xs-12 col-sm-6 col-md-3">
         <div class="dborder">
      <div class="panel panel-default">
        <div class="panel-body">
          <h4 style="margin-top:0;">Widget 9</h4>
          <p style="margin-bottom:0;">Example content for row 3, column 1.</p>
        </div>
      </div>
    </div>
    </div>

    <div class="col-xs-12 col-sm-6 col-md-3">
         <div class="dborder">
      <div class="panel panel-default">
        <div class="panel-body">
          <h4 style="margin-top:0;">Widget 10</h4>
          <p style="margin-bottom:0;">Example content for row 3, column 2.</p>
        </div>
      </div>
    </div>
    </div>

    <div class="col-xs-12 col-sm-6 col-md-3">
         <div class="dborder">
      <div class="panel panel-default">
        <div class="panel-body">
          <h4 style="margin-top:0;">Widget 11</h4>
          <p style="margin-bottom:0;">Example content for row 3, column 3.</p>
        </div>
      </div>
    </div>
    </div>

    <div class="col-xs-12 col-sm-6 col-md-3">
         <div class="dborder">
      <div class="panel panel-default">
        <div class="panel-body">
          <h4 style="margin-top:0;">Widget 12</h4>
          <p style="margin-bottom:0;">Example content for row 3, column 4.</p>
        </div>
      </div>
    </div>
    </div>

  </div>

</div>
';


    }
}
 
// display_dashboard_spans example-only dashboard (additive grid mode)
if (!function_exists('display_dashboard_spans')) {
    function display_dashboard_spans(int $this_user_id = 0): void
    {
        if ($this_user_id === 0) {
            echo '<div class="row m1-row"><p>FATAL ERROR: You are not logged in.</p></div>' . PHP_EOL;
            exit;
        }

        echo '
<div class="dashboardc dashboardc-grid container-fluid">
<div class="m1g m1g-4x4">
  <h4 style="margin-top:0;">Regional Map</h4>
  <div class="mapView">Map goes here</div>
</div>

  <!-- Normal widgets: 1/4 width x 1/3 height -->
  <div class="m1g m1g-w-2 m1g-h-2"><h4 style="margin-top:0;">Normal (w2 h2)</h4><p style="margin-bottom:0;">1/4 width, 1/3 height.</p></div>
  <div class="m1g m1g-w-2 m1g-h-2"><h4 style="margin-top:0;">Normal (w2 h2)</h4><p style="margin-bottom:0;">1/4 width, 1/3 height.</p></div>
  <div class="m1g m1g-w-2 m1g-h-2"><h4 style="margin-top:0;">Normal (w2 h2)</h4><p style="margin-bottom:0;">1/4 width, 1/3 height.</p></div>
  <div class="m1g m1g-w-2 m1g-h-2"><h4 style="margin-top:0;">Normal (w2 h2)</h4><p style="margin-bottom:0;">1/4 width, 1/3 height.</p></div>

  <!-- Half-width: 1/8 width -->
  <div class="m1g m1g-w-1 m1g-h-2"><h4 style="margin-top:0;">Half-width (w1 h2)</h4><p style="margin-bottom:0;">1/8 width.</p></div>
  <div class="m1g m1g-w-1 m1g-h-2"><h4 style="margin-top:0;">Half-width (w1 h2)</h4><p style="margin-bottom:0;">1/8 width.</p></div>

  <!-- Double-width: 1/2 width -->
  <div class="m1g m1g-w-4 m1g-h-2"><h4 style="margin-top:0;">Double-width (w4 h2)</h4><p style="margin-bottom:0;">1/2 width.</p></div>

  <!-- Two rows tall + double wide -->
  <div class="m1g m1g-w-4 m1g-h-4"><h4 style="margin-top:0;">2 rows tall + 2x wide (w4 h4)</h4><p style="margin-bottom:0;">Spans 2 normal rows, half width.</p></div>

  <!-- Two rows tall, normal width -->
  <div class="m1g m1g-w-2 m1g-h-4"><h4 style="margin-top:0;">2 rows tall (w2 h4)</h4><p style="margin-bottom:0;">Spans 2 normal rows, 1/4 width.</p></div>

  <!-- Extra combos you will likely want soon -->
  <div class="m1g m1g-w-8 m1g-h-1"><h4 style="margin-top:0;">Full-width banner (w8 h1)</h4><p style="margin-bottom:0;">Full width, half-height band.</p></div>
  <div class="m1g m1g-w-3 m1g-h-2"><h4 style="margin-top:0;">3/8 width (w3 h2)</h4><p style="margin-bottom:0;">Useful for maps + side panels.</p></div>
  <div class="m1g m1g-w-5 m1g-h-2"><h4 style="margin-top:0;">5/8 width (w5 h2)</h4><p style="margin-bottom:0;">Pairs with 3/8 for clean splits.</p></div>

</div>
';
    }
}
