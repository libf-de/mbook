<?php
/**
 * Displays the lesson creation page
 * -- (?page=nb-options-lessons&action=lessons-add)
 * @return void ok
 */
function handle_admin_settings()
  {
      // Load FontAwesome, nubook.lessons.css and nubook.lessons.add.js
      //nb_load_fa();
      //wp_enqueue_style('nb-lessons-css');
      //wp_enqueue_script('nb-lsadd-js');
      wp_enqueue_style('nb-config-css');

      global $wpdb;
      include __DIR__ . "/views/settings.php";
  }

function handle_admin_settings_post()
 {
  if( !(is_numeric($_POST['calcmode']) and $_POST['calcmode'] <= 1 and $_POST['calcmode'] >= 0) ) {
    status_header(400);
    exit("Invalid request: missing/invalid parameter 'calcmode'");
  }

  if( !(is_numeric($_POST['partmode']) and $_POST['partmode'] <= 1 and $_POST['partmode'] >= 0) ) {
    status_header(400);
    exit("Invalid request: missing/invalid parameter 'partmode'");
  }

  update_option('nb_calcmode', intval($_POST['calcmode']));
  update_option('nb_partmode', intval($_POST['partmode']));

  wp_redirect(add_query_arg(array(
    'action' => 'config',
    'msg' => urlencode("Einstellungen gespeichert"),
    'msgcol' => 'green',
  ), admin_url('admin.php?page=nb-options-menu')));

 }