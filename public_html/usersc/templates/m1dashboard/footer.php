<?php
require_once $abs_us_root . $us_url_root . 'usersc/templates/' . $settings->template . '/container_close.php';
require_once $abs_us_root . $us_url_root . 'users/includes/page_footer.php';
?>

<footer  id="footer" class="footer mt-auto" style="background-color: transparent;">
  <div class="container">
    <p class="text-center" style="display:inline-grid ;">&copy; <?php echo date("Y"); ?> <img src="/assets/images/gim-logo.png"></p>
  </div>
</footer>

</body>

<?php require_once($abs_us_root.$us_url_root.'users/includes/html_footer.php');?>
