<?php
/*** REST API functions ***/
function mb_api_init() {
    register_rest_route( 'mbook/v1', '/set-parts', array(
      'methods' => 'POST',
      'callback' => 'handle_api_ferientermine_parts',
      'permission_callback' => 'mb_api_admin_perms',
    ) );
  
    register_rest_route( 'mbook/v1', '/get-prints', array(
      'methods' => 'GET',
      'callback' => 'handle_api_ferientermine_print',
      //'permission_callback' => 'mb_api_admin_perms',
    ) );
  }
  
  function mb_api_admin_perms()  { return current_user_can( 'manage_options' ); }
  
  function handle_api_ferientermine_print() {
    global $wpdb;
    $template = db_ferientemplates;
    $termin = db_ferientermine;
    $ferien = db_ferien;
    $pr = $wpdb->get_results("SELECT `$termin`.*, `$template`.TITLE FROM `$termin` INNER JOIN `$template` ON `$termin`.`TEMPLATE` = `$template`.`ID` WHERE `$termin`.`DATESTART` >= CURDATE() ORDER BY `$termin`.`DATESTART`");
    wp_send_json( $pr );
    return;
  }
  
  function handle_api_ferientermine_parts() {
    global $wpdb;
    if(!isset($_POST['id']) or !isset($_POST['val'])) {
      wp_send_json( array("code" => "fail", "message" => "Missing POST parameter(s) ID and/or VAL", "data" => array("status" => 400) ) );
      return;
    } else if(!is_numeric($_POST['id']) or !is_numeric($_POST['val'])) {
      wp_send_json( array("code" => "fail", "message" => "POST parameter(s) ID and/or VAL must be numeric!", "data" => array("status" => 400) ) );
      return;
    } else if($wpdb->update(db_ferientermine, array('PARTICIPANTS' => intval($_POST['val'])), array('ID' => $_POST['id']), array('%d'), array('%d')) !== FALSE) {
      wp_send_json( array("code" => "ok", "message" => "Update participants OK", "data" => array("status" => 200, "id" => intval($_POST['id']), "value" => intval($_POST['val']))) );
      return;
    }
  
    wp_send_json( array("code" => "fail", "message" => "Database error!", "data" => array("status" => 500) ) );
    return;
  }
?>