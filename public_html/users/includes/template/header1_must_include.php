<?php
ob_start();
/*
UserSpice 5
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

?>
<!DOCTYPE html>
<html lang="<?=$html_lang ?>">
<head>
<!-- moved favicon to usersc/includes/head_tags.php -->
	 <?php 
	 if(isset($header_calls)){
	echo PHP_EOL.'<!-- header calls starts -->'.PHP_EOL;
	require_once $abs_us_root.$us_url_root.'usersc/includes/m1-header-calls.php';
	header_calls($header_calls); 
	echo PHP_EOL.'<!-- header calls ends -->'.PHP_EOL;
	 }
	 ?>

	<?php
	if(file_exists($abs_us_root.$us_url_root.'usersc/includes/head_tags.php')){
		require_once $abs_us_root.$us_url_root.'usersc/includes/head_tags.php';
	}

if(!file_exists($abs_us_root.$us_url_root."usersc/templates/".$settings->template."/assets/v2template.php")){
//the snippet below is meant to provide a basic btn-close class for bs 4 templates that don't have it

	?>
	<style>
	.close {
	  position: absolute;
	  right: 2rem;
	  top: 2rem;
	  width: 2rem;
	  height: 2rem;
	  opacity: 0.3;
	}
	.close:hover {
	  opacity: 1;
	}
	.close:before, .close:after {
	  position: absolute;
	  left: 15px;
	  content: ' ';
	  height: 1.25rem;
	  width: 2px;
	  background-color: #333;
	}
	.close:before {
	  transform: rotate(45deg);
	}
	.close:after {
	  transform: rotate(-45deg);
	}
	</style>
<?php } //end v2 template check


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
$css_files[100001] = '/assets/css/widgets.css?v=' . date("YmdH"); // public_html/assets/css/widgets.css


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
	<script defer src="<?=$us_url_root?>users/js/messages.js"></script>
	<title><?= (($pageTitle != '') ? $pageTitle : ''); ?> <?=$settings->site_name?></title>