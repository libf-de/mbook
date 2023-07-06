<?php
/**
 * Displays the lesson creation page
 * -- (?page=nb-options-lessons&action=lessons-add)
 * @return void ok
 */
function handle_admin_settings()
  {
      global $wpdb;
      global $plugin_root;
      // Load FontAwesome, nubook.settings.css and nubook.settings.js
      nb_load_fa();
      wp_enqueue_style('nb-config-css');
      wp_localize_script('nb-config-js', 'WPURL', array('jsonURL' => plugin_dir_url(__DIR__) . "calendar/" . $wpdb->prefix . ".gc.json"));
      wp_enqueue_script('nb-config-js');

      
      require_once($plugin_root . 'inc/calendar/caltest.php');
      $gca = new GoogleCalenderAdapter();
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
  update_option('nb_gc_ferien', strip_tags($_POST['gcferienid']));
  update_option('nb_wa_phone', intval($_POST['nbwaphone']));

  wp_redirect(add_query_arg(array(
    'action' => 'config',
    'msg' => urlencode("Einstellungen gespeichert"),
    'msgcol' => 'green',
  ), admin_url('admin.php?page=nb-options-menu')));

 }

function handle_admin_gcauth_post() {
  global $plugin_root;
  global $wpdb;
  $target_file = $plugin_root . 'inc/calendar/' . $wpdb->prefix . '.gc.json';

  if(isset($_POST['delete'])) {
    status_header(200);
    echo "<html><head><title>Bestaetigen - nuBook primitive auth manager</title></head><body><form method=\"post\"><h1>Hochgeladene Anmeldeinformationen wirklich löschen?</h1><button type=\"submit\" name=\"goback\" style=\"width: 50%; height: 70%; background-color: lime; font-size: 300%;\">Nein</button><button type=\"submit\" name=\"yesdelete\" style=\"width: 50%; height: 70%; background-color: red; font-size: 300%;\">Ja, löschen!</button></form></body></html>";
    exit;
  }

  if(isset($_POST['goback'])) {
    wp_redirect(add_query_arg(array(
      'action' => 'config',
    ), admin_url('admin.php?page=nb-options-menu')));
    exit;
  }

  if(isset($_POST['yesdelete'])) {
    if(unlink($target_file)) {
      wp_redirect(add_query_arg(array(
        'action' => 'config',
        'msg' => urlencode("Datei erfolgreich gelöscht"),
        'msgcol' => 'green',
      ), admin_url('admin.php?page=nb-options-menu')));
    } else {
      wp_redirect(add_query_arg(array(
        'action' => 'config',
        'msg' => urlencode("Löschen fehlgeschlagen - Ueberpruefen Sie Schreibrechte in " . str_replace("/", "--", $plugin_root) . "?"),
        'msgcol' => 'red',
      ), admin_url('admin.php?page=nb-options-menu')));
    }
  }

  if(isset($_FILES["gcauth"])) {
    if($_FILES["gcauth"]["type"] != "application/json" or $_FILES["gcauth"]["size"] > 10000) {
      wp_redirect(add_query_arg(array(
        'action' => 'config',
        'msg' => urlencode("Ungültige Datei - kein JSON oder zu gross"),
        'msgcol' => 'red',
      ), admin_url('admin.php?page=nb-options-menu')));
      return;
    }
    if (move_uploaded_file($_FILES["gcauth"]["tmp_name"], $target_file)) {
      wp_redirect(add_query_arg(array(
        'action' => 'config',
        'msg' => urlencode("Datei erfolgreich hochgeladen"),
        'msgcol' => 'green',
      ), admin_url('admin.php?page=nb-options-menu')));
    } else {
      wp_redirect(add_query_arg(array(
        'action' => 'config',
        'msg' => urlencode("Upload fehlgeschlagen - Ueberpruefen Sie Schreibrechte in " . str_replace("/", "--", $plugin_root) . "?"),
        'msgcol' => 'red',
      ), admin_url('admin.php?page=nb-options-menu')));
    }
  }
}