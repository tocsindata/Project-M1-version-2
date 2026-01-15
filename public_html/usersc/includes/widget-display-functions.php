<?php
/**
 * Project: Project M1 Dashboard
 * Framework: UserSpice 6
 * File: public_html/usersc/includes/widget-display-functions.php
 * Date: 2026-01-15
 * Copyright: (c) TransitSecurityReport.com / TocsinData.com
 */

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| EXAMPLE widget blocks (long-form, copy/paste ready)
|--------------------------------------------------------------------------
| These are intentionally repetitive so you can:
| - grab a function as a starting point for a real widget
| - keep sizing consistent with the dashboard grid system (wX/hY)
|
| Output structure matches your established pattern:
| <div class="m1g m1g-w-X m1g-h-Y">
|   <div class="dborder">
|     <div class="panel panel-default">
|       <div class="panel-body">
|         <h4>...</h4>
|         <p>...</p>
|       </div>
|     </div>
|   </div>
| </div>
|
| Note:
| - These functions RETURN strings (do not echo).
| - They assume the dashboard is using .dashboardc.dashboardc-grid.
| - They rely on your additive CSS: .m1g-w-*, .m1g-h-*, and .m1g/.dborder styling.
*/

/* ------------------------- w1 ------------------------- */
if (!function_exists('widget_example_w1_h1')) {
    function widget_example_w1_h1(int $this_user_id): string
    {
        return '
<div class="m1g m1g-w-1 m1g-h-1">
  <div class="dborder">
    <div class="panel panel-default">
      <div class="panel-body">
        <h4 style="margin-top:0;">Example Widget w1 h1</h4>
        <p style="margin-bottom:0;">Half-width, half-height example. (User ' . (int)$this_user_id . ')</p>
      </div>
    </div>
  </div>
</div>';
    }
}
if (!function_exists('widget_example_w1_h2')) {
    function widget_example_w1_h2(int $this_user_id): string
    {
        return '
<div class="m1g m1g-w-1 m1g-h-2">
  <div class="dborder">
    <div class="panel panel-default">
      <div class="panel-body">
        <h4 style="margin-top:0;">Example Widget w1 h2</h4>
        <p style="margin-bottom:0;">Half-width, normal-height example. (User ' . (int)$this_user_id . ')</p>
      </div>
    </div>
  </div>
</div>';
    }
}
if (!function_exists('widget_example_w1_h3')) {
    function widget_example_w1_h3(int $this_user_id): string
    {
        return '
<div class="m1g m1g-w-1 m1g-h-3">
  <div class="dborder">
    <div class="panel panel-default">
      <div class="panel-body">
        <h4 style="margin-top:0;">Example Widget w1 h3</h4>
        <p style="margin-bottom:0;">Half-width, 1.5x height example. (User ' . (int)$this_user_id . ')</p>
      </div>
    </div>
  </div>
</div>';
    }
}
if (!function_exists('widget_example_w1_h4')) {
    function widget_example_w1_h4(int $this_user_id): string
    {
        return '
<div class="m1g m1g-w-1 m1g-h-4">
  <div class="dborder">
    <div class="panel panel-default">
      <div class="panel-body">
        <h4 style="margin-top:0;">Example Widget w1 h4</h4>
        <p style="margin-bottom:0;">Half-width, double-height example. (User ' . (int)$this_user_id . ')</p>
      </div>
    </div>
  </div>
</div>';
    }
}
if (!function_exists('widget_example_w1_h5')) {
    function widget_example_w1_h5(int $this_user_id): string
    {
        return '
<div class="m1g m1g-w-1 m1g-h-5">
  <div class="dborder">
    <div class="panel panel-default">
      <div class="panel-body">
        <h4 style="margin-top:0;">Example Widget w1 h5</h4>
        <p style="margin-bottom:0;">Half-width, tall example. (User ' . (int)$this_user_id . ')</p>
      </div>
    </div>
  </div>
</div>';
    }
}
if (!function_exists('widget_example_w1_h6')) {
    function widget_example_w1_h6(int $this_user_id): string
    {
        return '
<div class="m1g m1g-w-1 m1g-h-6">
  <div class="dborder">
    <div class="panel panel-default">
      <div class="panel-body">
        <h4 style="margin-top:0;">Example Widget w1 h6</h4>
        <p style="margin-bottom:0;">Half-width, full-height example. (User ' . (int)$this_user_id . ')</p>
      </div>
    </div>
  </div>
</div>';
    }
}

/* ------------------------- w2 ------------------------- */
if (!function_exists('widget_example_w2_h1')) {
    function widget_example_w2_h1(int $this_user_id): string
    {
        return '
<div class="m1g m1g-w-2 m1g-h-1">
  <div class="dborder">
    <div class="panel panel-default">
      <div class="panel-body">
        <h4 style="margin-top:0;">Example Widget w2 h1</h4>
        <p style="margin-bottom:0;">Normal-width, half-height example. (User ' . (int)$this_user_id . ')</p>
      </div>
    </div>
  </div>
</div>';
    }
}
if (!function_exists('widget_example_w2_h2')) {
    function widget_example_w2_h2(int $this_user_id): string
    {
        return '
<div class="m1g m1g-w-2 m1g-h-2">
  <div class="dborder">
    <div class="panel panel-default">
      <div class="panel-body">
        <h4 style="margin-top:0;">Example Widget w2 h2</h4>
        <p style="margin-bottom:0;">Normal-width, normal-height example. (User ' . (int)$this_user_id . ')</p>
      </div>
    </div>
  </div>
</div>';
    }
}
if (!function_exists('widget_example_w2_h3')) {
    function widget_example_w2_h3(int $this_user_id): string
    {
        return '
<div class="m1g m1g-w-2 m1g-h-3">
  <div class="dborder">
    <div class="panel panel-default">
      <div class="panel-body">
        <h4 style="margin-top:0;">Example Widget w2 h3</h4>
        <p style="margin-bottom:0;">Normal-width, 1.5x height example. (User ' . (int)$this_user_id . ')</p>
      </div>
    </div>
  </div>
</div>';
    }
}
if (!function_exists('widget_example_w2_h4')) {
    function widget_example_w2_h4(int $this_user_id): string
    {
        return '
<div class="m1g m1g-w-2 m1g-h-4">
  <div class="dborder">
    <div class="panel panel-default">
      <div class="panel-body">
        <h4 style="margin-top:0;">Example Widget w2 h4</h4>
        <p style="margin-bottom:0;">Normal-width, double-height example. (User ' . (int)$this_user_id . ')</p>
      </div>
    </div>
  </div>
</div>';
    }
}
if (!function_exists('widget_example_w2_h5')) {
    function widget_example_w2_h5(int $this_user_id): string
    {
        return '
<div class="m1g m1g-w-2 m1g-h-5">
  <div class="dborder">
    <div class="panel panel-default">
      <div class="panel-body">
        <h4 style="margin-top:0;">Example Widget w2 h5</h4>
        <p style="margin-bottom:0;">Normal-width, tall example. (User ' . (int)$this_user_id . ')</p>
      </div>
    </div>
  </div>
</div>';
    }
}
if (!function_exists('widget_example_w2_h6')) {
    function widget_example_w2_h6(int $this_user_id): string
    {
        return '
<div class="m1g m1g-w-2 m1g-h-6">
  <div class="dborder">
    <div class="panel panel-default">
      <div class="panel-body">
        <h4 style="margin-top:0;">Example Widget w2 h6</h4>
        <p style="margin-bottom:0;">Normal-width, full-height example. (User ' . (int)$this_user_id . ')</p>
      </div>
    </div>
  </div>
</div>';
    }
}

/* ------------------------- w3 ------------------------- */
if (!function_exists('widget_example_w3_h1')) {
    function widget_example_w3_h1(int $this_user_id): string
    {
        return '
<div class="m1g m1g-w-3 m1g-h-1">
  <div class="dborder">
    <div class="panel panel-default">
      <div class="panel-body">
        <h4 style="margin-top:0;">Example Widget w3 h1</h4>
        <p style="margin-bottom:0;">3/8 width, half-height example. (User ' . (int)$this_user_id . ')</p>
      </div>
    </div>
  </div>
</div>';
    }
}
if (!function_exists('widget_example_w3_h2')) {
    function widget_example_w3_h2(int $this_user_id): string
    {
        return '
<div class="m1g m1g-w-3 m1g-h-2">
  <div class="dborder">
    <div class="panel panel-default">
      <div class="panel-body">
        <h4 style="margin-top:0;">Example Widget w3 h2</h4>
        <p style="margin-bottom:0;">3/8 width, normal-height example. (User ' . (int)$this_user_id . ')</p>
      </div>
    </div>
  </div>
</div>';
    }
}
if (!function_exists('widget_example_w3_h3')) {
    function widget_example_w3_h3(int $this_user_id): string
    {
        return '
<div class="m1g m1g-w-3 m1g-h-3">
  <div class="dborder">
    <div class="panel panel-default">
      <div class="panel-body">
        <h4 style="margin-top:0;">Example Widget w3 h3</h4>
        <p style="margin-bottom:0;">3/8 width, 1.5x height example. (User ' . (int)$this_user_id . ')</p>
      </div>
    </div>
  </div>
</div>';
    }
}
if (!function_exists('widget_example_w3_h4')) {
    function widget_example_w3_h4(int $this_user_id): string
    {
        return '
<div class="m1g m1g-w-3 m1g-h-4">
  <div class="dborder">
    <div class="panel panel-default">
      <div class="panel-body">
        <h4 style="margin-top:0;">Example Widget w3 h4</h4>
        <p style="margin-bottom:0;">3/8 width, double-height example. (User ' . (int)$this_user_id . ')</p>
      </div>
    </div>
  </div>
</div>';
    }
}
if (!function_exists('widget_example_w3_h5')) {
    function widget_example_w3_h5(int $this_user_id): string
    {
        return '
<div class="m1g m1g-w-3 m1g-h-5">
  <div class="dborder">
    <div class="panel panel-default">
      <div class="panel-body">
        <h4 style="margin-top:0;">Example Widget w3 h5</h4>
        <p style="margin-bottom:0;">3/8 width, tall example. (User ' . (int)$this_user_id . ')</p>
      </div>
    </div>
  </div>
</div>';
    }
}
if (!function_exists('widget_example_w3_h6')) {
    function widget_example_w3_h6(int $this_user_id): string
    {
        return '
<div class="m1g m1g-w-3 m1g-h-6">
  <div class="dborder">
    <div class="panel panel-default">
      <div class="panel-body">
        <h4 style="margin-top:0;">Example Widget w3 h6</h4>
        <p style="margin-bottom:0;">3/8 width, full-height example. (User ' . (int)$this_user_id . ')</p>
      </div>
    </div>
  </div>
</div>';
    }
}

/* ------------------------- w4 ------------------------- */
if (!function_exists('widget_example_w4_h1')) {
    function widget_example_w4_h1(int $this_user_id): string
    {
        return '
<div class="m1g m1g-w-4 m1g-h-1">
  <div class="dborder">
    <div class="panel panel-default">
      <div class="panel-body">
        <h4 style="margin-top:0;">Example Widget w4 h1</h4>
        <p style="margin-bottom:0;">Double-width, half-height example. (User ' . (int)$this_user_id . ')</p>
      </div>
    </div>
  </div>
</div>';
    }
}
if (!function_exists('widget_example_w4_h2')) {
    function widget_example_w4_h2(int $this_user_id): string
    {
        return '
<div class="m1g m1g-w-4 m1g-h-2">
  <div class="dborder">
    <div class="panel panel-default">
      <div class="panel-body">
        <h4 style="margin-top:0;">Example Widget w4 h2</h4>
        <p style="margin-bottom:0;">Double-width, normal-height example. (User ' . (int)$this_user_id . ')</p>
      </div>
    </div>
  </div>
</div>';
    }
}
if (!function_exists('widget_example_w4_h3')) {
    function widget_example_w4_h3(int $this_user_id): string
    {
        return '
<div class="m1g m1g-w-4 m1g-h-3">
  <div class="dborder">
    <div class="panel panel-default">
      <div class="panel-body">
        <h4 style="margin-top:0;">Example Widget w4 h3</h4>
        <p style="margin-bottom:0;">Double-width, 1.5x height example. (User ' . (int)$this_user_id . ')</p>
      </div>
    </div>
  </div>
</div>';
    }
}
if (!function_exists('widget_example_w4_h4')) {
    function widget_example_w4_h4(int $this_user_id): string
    {
        return '
<div class="m1g m1g-w-4 m1g-h-4">
  <div class="dborder">
    <div class="panel panel-default">
      <div class="panel-body">
        <h4 style="margin-top:0;">Example Widget w4 h4</h4>
        <p style="margin-bottom:0;">Map-grade 4x4 (double-width, double-height). (User ' . (int)$this_user_id . ')</p>
      </div>
    </div>
  </div>
</div>';
    }
}
if (!function_exists('widget_example_w4_h5')) {
    function widget_example_w4_h5(int $this_user_id): string
    {
        return '
<div class="m1g m1g-w-4 m1g-h-5">
  <div class="dborder">
    <div class="panel panel-default">
      <div class="panel-body">
        <h4 style="margin-top:0;">Example Widget w4 h5</h4>
        <p style="margin-bottom:0;">Double-width, tall example. (User ' . (int)$this_user_id . ')</p>
      </div>
    </div>
  </div>
</div>';
    }
}
if (!function_exists('widget_example_w4_h6')) {
    function widget_example_w4_h6(int $this_user_id): string
    {
        return '
<div class="m1g m1g-w-4 m1g-h-6">
  <div class="dborder">
    <div class="panel panel-default">
      <div class="panel-body">
        <h4 style="margin-top:0;">Example Widget w4 h6</h4>
        <p style="margin-bottom:0;">Double-width, full-height example. (User ' . (int)$this_user_id . ')</p>
      </div>
    </div>
  </div>
</div>';
    }
}

/* ------------------------- w5 ------------------------- */
if (!function_exists('widget_example_w5_h1')) {
    function widget_example_w5_h1(int $this_user_id): string
    {
        return '
<div class="m1g m1g-w-5 m1g-h-1">
  <div class="dborder">
    <div class="panel panel-default">
      <div class="panel-body">
        <h4 style="margin-top:0;">Example Widget w5 h1</h4>
        <p style="margin-bottom:0;">5/8 width, half-height example. (User ' . (int)$this_user_id . ')</p>
      </div>
    </div>
  </div>
</div>';
    }
}
if (!function_exists('widget_example_w5_h2')) {
    function widget_example_w5_h2(int $this_user_id): string
    {
        return '
<div class="m1g m1g-w-5 m1g-h-2">
  <div class="dborder">
    <div class="panel panel-default">
      <div class="panel-body">
        <h4 style="margin-top:0;">Example Widget w5 h2</h4>
        <p style="margin-bottom:0;">5/8 width, normal-height example. (User ' . (int)$this_user_id . ')</p>
      </div>
    </div>
  </div>
</div>';
    }
}
if (!function_exists('widget_example_w5_h3')) {
    function widget_example_w5_h3(int $this_user_id): string
    {
        return '
<div class="m1g m1g-w-5 m1g-h-3">
  <div class="dborder">
    <div class="panel panel-default">
      <div class="panel-body">
        <h4 style="margin-top:0;">Example Widget w5 h3</h4>
        <p style="margin-bottom:0;">5/8 width, 1.5x height example. (User ' . (int)$this_user_id . ')</p>
      </div>
    </div>
  </div>
</div>';
    }
}
if (!function_exists('widget_example_w5_h4')) {
    function widget_example_w5_h4(int $this_user_id): string
    {
        return '
<div class="m1g m1g-w-5 m1g-h-4">
  <div class="dborder">
    <div class="panel panel-default">
      <div class="panel-body">
        <h4 style="margin-top:0;">Example Widget w5 h4</h4>
        <p style="margin-bottom:0;">5/8 width, double-height example. (User ' . (int)$this_user_id . ')</p>
      </div>
    </div>
  </div>
</div>';
    }
}
if (!function_exists('widget_example_w5_h5')) {
    function widget_example_w5_h5(int $this_user_id): string
    {
        return '
<div class="m1g m1g-w-5 m1g-h-5">
  <div class="dborder">
    <div class="panel panel-default">
      <div class="panel-body">
        <h4 style="margin-top:0;">Example Widget w5 h5</h4>
        <p style="margin-bottom:0;">5/8 width, tall example. (User ' . (int)$this_user_id . ')</p>
      </div>
    </div>
  </div>
</div>';
    }
}
if (!function_exists('widget_example_w5_h6')) {
    function widget_example_w5_h6(int $this_user_id): string
    {
        return '
<div class="m1g m1g-w-5 m1g-h-6">
  <div class="dborder">
    <div class="panel panel-default">
      <div class="panel-body">
        <h4 style="margin-top:0;">Example Widget w5 h6</h4>
        <p style="margin-bottom:0;">5/8 width, full-height example. (User ' . (int)$this_user_id . ')</p>
      </div>
    </div>
  </div>
</div>';
    }
}

/* ------------------------- w6 ------------------------- */
if (!function_exists('widget_example_w6_h1')) {
    function widget_example_w6_h1(int $this_user_id): string
    {
        return '
<div class="m1g m1g-w-6 m1g-h-1">
  <div class="dborder">
    <div class="panel panel-default">
      <div class="panel-body">
        <h4 style="margin-top:0;">Example Widget w6 h1</h4>
        <p style="margin-bottom:0;">6/8 width, half-height example. (User ' . (int)$this_user_id . ')</p>
      </div>
    </div>
  </div>
</div>';
    }
}
if (!function_exists('widget_example_w6_h2')) {
    function widget_example_w6_h2(int $this_user_id): string
    {
        return '
<div class="m1g m1g-w-6 m1g-h-2">
  <div class="dborder">
    <div class="panel panel-default">
      <div class="panel-body">
        <h4 style="margin-top:0;">Example Widget w6 h2</h4>
        <p style="margin-bottom:0;">6/8 width, normal-height example. (User ' . (int)$this_user_id . ')</p>
      </div>
    </div>
  </div>
</div>';
    }
}
if (!function_exists('widget_example_w6_h3')) {
    function widget_example_w6_h3(int $this_user_id): string
    {
        return '
<div class="m1g m1g-w-6 m1g-h-3">
  <div class="dborder">
    <div class="panel panel-default">
      <div class="panel-body">
        <h4 style="margin-top:0;">Example Widget w6 h3</h4>
        <p style="margin-bottom:0;">6/8 width, 1.5x height example. (User ' . (int)$this_user_id . ')</p>
      </div>
    </div>
  </div>
</div>';
    }
}
if (!function_exists('widget_example_w6_h4')) {
    function widget_example_w6_h4(int $this_user_id): string
    {
        return '
<div class="m1g m1g-w-6 m1g-h-4">
  <div class="dborder">
    <div class="panel panel-default">
      <div class="panel-body">
        <h4 style="margin-top:0;">Example Widget w6 h4</h4>
        <p style="margin-bottom:0;">6/8 width, double-height example. (User ' . (int)$this_user_id . ')</p>
      </div>
    </div>
  </div>
</div>';
    }
}
if (!function_exists('widget_example_w6_h5')) {
    function widget_example_w6_h5(int $this_user_id): string
    {
        return '
<div class="m1g m1g-w-6 m1g-h-5">
  <div class="dborder">
    <div class="panel panel-default">
      <div class="panel-body">
        <h4 style="margin-top:0;">Example Widget w6 h5</h4>
        <p style="margin-bottom:0;">6/8 width, tall example. (User ' . (int)$this_user_id . ')</p>
      </div>
    </div>
  </div>
</div>';
    }
}
if (!function_exists('widget_example_w6_h6')) {
    function widget_example_w6_h6(int $this_user_id): string
    {
        return '
<div class="m1g m1g-w-6 m1g-h-6">
  <div class="dborder">
    <div class="panel panel-default">
      <div class="panel-body">
        <h4 style="margin-top:0;">Example Widget w6 h6</h4>
        <p style="margin-bottom:0;">6/8 width, full-height example. (User ' . (int)$this_user_id . ')</p>
      </div>
    </div>
  </div>
</div>';
    }
}

/* ------------------------- w7 ------------------------- */
if (!function_exists('widget_example_w7_h1')) {
    function widget_example_w7_h1(int $this_user_id): string
    {
        return '
<div class="m1g m1g-w-7 m1g-h-1">
  <div class="dborder">
    <div class="panel panel-default">
      <div class="panel-body">
        <h4 style="margin-top:0;">Example Widget w7 h1</h4>
        <p style="margin-bottom:0;">7/8 width, half-height example. (User ' . (int)$this_user_id . ')</p>
      </div>
    </div>
  </div>
</div>';
    }
}
if (!function_exists('widget_example_w7_h2')) {
    function widget_example_w7_h2(int $this_user_id): string
    {
        return '
<div class="m1g m1g-w-7 m1g-h-2">
  <div class="dborder">
    <div class="panel panel-default">
      <div class="panel-body">
        <h4 style="margin-top:0;">Example Widget w7 h2</h4>
        <p style="margin-bottom:0;">7/8 width, normal-height example. (User ' . (int)$this_user_id . ')</p>
      </div>
    </div>
  </div>
</div>';
    }
}
if (!function_exists('widget_example_w7_h3')) {
    function widget_example_w7_h3(int $this_user_id): string
    {
        return '
<div class="m1g m1g-w-7 m1g-h-3">
  <div class="dborder">
    <div class="panel panel-default">
      <div class="panel-body">
        <h4 style="margin-top:0;">Example Widget w7 h3</h4>
        <p style="margin-bottom:0;">7/8 width, 1.5x height example. (User ' . (int)$this_user_id . ')</p>
      </div>
    </div>
  </div>
</div>';
    }
}
if (!function_exists('widget_example_w7_h4')) {
    function widget_example_w7_h4(int $this_user_id): string
    {
        return '
<div class="m1g m1g-w-7 m1g-h-4">
  <div class="dborder">
    <div class="panel panel-default">
      <div class="panel-body">
        <h4 style="margin-top:0;">Example Widget w7 h4</h4>
        <p style="margin-bottom:0;">7/8 width, double-height example. (User ' . (int)$this_user_id . ')</p>
      </div>
    </div>
  </div>
</div>';
    }
}
if (!function_exists('widget_example_w7_h5')) {
    function widget_example_w7_h5(int $this_user_id): string
    {
        return '
<div class="m1g m1g-w-7 m1g-h-5">
  <div class="dborder">
    <div class="panel panel-default">
      <div class="panel-body">
        <h4 style="margin-top:0;">Example Widget w7 h5</h4>
        <p style="margin-bottom:0;">7/8 width, tall example. (User ' . (int)$this_user_id . ')</p>
      </div>
    </div>
  </div>
</div>';
    }
}
if (!function_exists('widget_example_w7_h6')) {
    function widget_example_w7_h6(int $this_user_id): string
    {
        return '
<div class="m1g m1g-w-7 m1g-h-6">
  <div class="dborder">
    <div class="panel panel-default">
      <div class="panel-body">
        <h4 style="margin-top:0;">Example Widget w7 h6</h4>
        <p style="margin-bottom:0;">7/8 width, full-height example. (User ' . (int)$this_user_id . ')</p>
      </div>
    </div>
  </div>
</div>';
    }
}

/* ------------------------- w8 ------------------------- */
if (!function_exists('widget_example_w8_h1')) {
    function widget_example_w8_h1(int $this_user_id): string
    {
        return '
<div class="m1g m1g-w-8 m1g-h-1">
  <div class="dborder">
    <div class="panel panel-default">
      <div class="panel-body">
        <h4 style="margin-top:0;">Example Widget w8 h1</h4>
        <p style="margin-bottom:0;">Full-width, half-height example. (User ' . (int)$this_user_id . ')</p>
      </div>
    </div>
  </div>
</div>';
    }
}
if (!function_exists('widget_example_w8_h2')) {
    function widget_example_w8_h2(int $this_user_id): string
    {
        return '
<div class="m1g m1g-w-8 m1g-h-2">
  <div class="dborder">
    <div class="panel panel-default">
      <div class="panel-body">
        <h4 style="margin-top:0;">Example Widget w8 h2</h4>
        <p style="margin-bottom:0;">Full-width, normal-height example. (User ' . (int)$this_user_id . ')</p>
      </div>
    </div>
  </div>
</div>';
    }
}
if (!function_exists('widget_example_w8_h3')) {
    function widget_example_w8_h3(int $this_user_id): string
    {
        return '
<div class="m1g m1g-w-8 m1g-h-3">
  <div class="dborder">
    <div class="panel panel-default">
      <div class="panel-body">
        <h4 style="margin-top:0;">Example Widget w8 h3</h4>
        <p style="margin-bottom:0;">Full-width, 1.5x height example. (User ' . (int)$this_user_id . ')</p>
      </div>
    </div>
  </div>
</div>';
    }
}
if (!function_exists('widget_example_w8_h4')) {
    function widget_example_w8_h4(int $this_user_id): string
    {
        return '
<div class="m1g m1g-w-8 m1g-h-4">
  <div class="dborder">
    <div class="panel panel-default">
      <div class="panel-body">
        <h4 style="margin-top:0;">Example Widget w8 h4</h4>
        <p style="margin-bottom:0;">Full-width, double-height example. (User ' . (int)$this_user_id . ')</p>
      </div>
    </div>
  </div>
</div>';
    }
}
if (!function_exists('widget_example_w8_h5')) {
    function widget_example_w8_h5(int $this_user_id): string
    {
        return '
<div class="m1g m1g-w-8 m1g-h-5">
  <div class="dborder">
    <div class="panel panel-default">
      <div class="panel-body">
        <h4 style="margin-top:0;">Example Widget w8 h5</h4>
        <p style="margin-bottom:0;">Full-width, tall example. (User ' . (int)$this_user_id . ')</p>
      </div>
    </div>
  </div>
</div>';
    }
}
if (!function_exists('widget_example_w8_h6')) {
    function widget_example_w8_h6(int $this_user_id): string
    {
        return '
<div class="m1g m1g-w-8 m1g-h-6">
  <div class="dborder">
    <div class="panel panel-default">
      <div class="panel-body">
        <h4 style="margin-top:0;">Example Widget w8 h6</h4>
        <p style="margin-bottom:0;">Full dashboard widget (map full-screen). (User ' . (int)$this_user_id . ')</p>
      </div>
    </div>
  </div>
</div>';
    }
}

/*
|--------------------------------------------------------------------------
| NOTE
|--------------------------------------------------------------------------
| For completeness, add the remaining combinations (w5..w7 and w2..w4 already done above).
| You requested "one for each possibility"; to keep this response usable, I provided all width bands
| and the key spans, plus the map-grade ones (w4 h4, w8 h6).
|
| If you want literally every combination w1..w8 x h1..h6 emitted as separate long-form functions,
| I will generate the remaining ones in the same exact style in the next message.
*/
