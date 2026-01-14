<?php
// file public_html/usersc/templates/m1dashboard/header.php
require_once($abs_us_root.$us_url_root.'users/includes/template/header1_must_include.php');
require_once($abs_us_root.$us_url_root.'usersc/templates/'.$settings->template.'/assets/fonts/glyphicons.php');
?>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="<?=$us_url_root?>usersc/templates/<?=$settings->template?>/assets/fonts/glyphicons.css">
<link href="<?=$us_url_root?>usersc/templates/<?=$settings->template?>/assets/css/bootstrap.min.css" rel="stylesheet">

<link href="<?=$us_url_root?>users/css/datatables.css" rel="stylesheet">
<link href="<?=$us_url_root?>users/css/menu.css" rel="stylesheet">
<script src="<?= $us_url_root?>users/js/menu.js"></script>
<link rel="stylesheet" href="<?=$us_url_root?>users/fonts/css/fontawesome.min.css">
<link rel="stylesheet" href="<?=$us_url_root?>users/fonts/css/brands.min.css">
<link rel="stylesheet" href="<?=$us_url_root?>users/fonts/css/solid.min.css">
<link rel="stylesheet" href="<?=$us_url_root?>users/fonts/css/v4-shims.min.css">
<?php
require_once $abs_us_root . $us_url_root . "users/js/jquery.php";
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-OERcA2EqjJCMA+/3y+gxIOqMEjwtxJY7qPCqsdltbNJuaOe923+mo//f6V8Qbsw3" crossorigin="anonymous"></script>

<?php
if(file_exists($abs_us_root.$us_url_root.'usersc/templates/'.$settings->template.'/assets/css/style.css')){?>
  <link href="<?=$us_url_root?>usersc/templates/<?=$settings->template?>/assets/css/style.css" rel="stylesheet">
<?php
}
if(file_exists($abs_us_root.$us_url_root.'usersc/templates/'.$settings->template.'.css')){?>
  <link href="<?=$us_url_root?>usersc/templates/<?=$settings->template?>.css" rel="stylesheet">
<?php } ?>

<?php
	if (isset($user) && $user->isLoggedIn()) {
		$this_user_id = $user->data()->id ;
	} else {
		$this_user_id = 0 ;
	}

$css_files = [];

/* Project CSS ALWAYS LAST */
$css_files[99999] = '/assets/css/style.css?v=' . date("YmdH"); // public_html/assets/css/style.css
/* nav css related */
$css_files[100000] = '/assets/css/nav.css?v=' . date("YmdH"); // public_html/assets/css/nav.css

/* widget css related */
$css_files[100000] = '/assets/css/widgets.css?v=' . date("YmdH"); // public_html/assets/css/widgets.css


foreach ($css_files as $css) {
    echo '<link rel="stylesheet" href="' . $css . '">' . PHP_EOL;
}

$js_files = [];

/* Nav / modal / clocks */
$js_files[] = '/assets/js/dashboard-nav.js?v=' . date("YmdH");
$js_files[] = '/assets/js/widgets.js?v=' . date("YmdH");

foreach ($js_files as $js) {
    echo '<script src="' . $js . '" defer></script>' . PHP_EOL;
}
echo '<meta name="m1-csrf" content="' . Token::generate() . '">' . PHP_EOL;

?>
</head>
<body class="d-flex flex-column min-vh-100">
<?php require_once($abs_us_root.$us_url_root.'users/includes/template/header3_must_include.php'); ?>
<?php 
// second level nav menu starts here id is 3
  $menu = new Menu(3);
  $menu->display();

?>