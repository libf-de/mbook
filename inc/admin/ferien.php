<?php
/*
 * TODO:
 * Consider saving the PDF instead of outputting it?
 * Display date and time + code?
 * Display correct ferien title
 * Better format?
 */
function handle_admin_ferien_print() {
    global $plugin_root;
    require_once($plugin_root . 'inc/print/printout.php');
    global $wpdb;
    $template = db_ferientemplates;
    $termin = db_ferientermine;
    $ferien = db_ferien;
    $pdf=new exFPDF('P','mm','A4');
    //$pdf->AddFont('lato','','assets/lib/font/Lato-Regular.php');
    $pdf->AddPage();
    $pdf->SetFont('helvetica','',26);
  
    $pdf->Write(10, 'Herbstferien 2022');
    $pdf->SetFont('helvetica','',16);
    $pdf->Ln();
  
    foreach( $wpdb->get_results("SELECT `$termin`.*, `$template`.TITLE FROM `$termin` INNER JOIN `$template` ON `$termin`.`TEMPLATE` = `$template`.`ID` WHERE `$termin`.`DATESTART` >= CURDATE() ORDER BY `$termin`.`DATESTART`") as $key => $row) {
      printTable($pdf, $row->MAX_PARTICIPANTS, $row->TITLE);
   }
  
    //printTable($pdf, 10, "Wanderritt");
    $pdf->Output();
  }

  function handle_admin_ferien_delete() {
    global $plugin_root;
    require_once($plugin_root . 'inc/calendar/caltest.php');
    
  }


  function handle_admin_ferien_export() {
    global $plugin_root;
    require_once($plugin_root . 'inc/calendar/caltest.php');
    global $wpdb;
    $template = db_ferientemplates;
    $termin = db_ferientermine;
    $ferien = db_ferien;

    $gca = new GoogleCalenderAdapter();
    
    foreach( $wpdb->get_results("SELECT `$termin`.*, `$template`.TITLE FROM `$termin` INNER JOIN `$template` ON `$termin`.`TEMPLATE` = `$template`.`ID` WHERE `$termin`.`DATESTART` >= CURDATE() ORDER BY `$termin`.`DATESTART`") as $key => $row) {
      $eventId = $gca->update_calendar_event($row);
      if($eventId != null) {
        if($wpdb->update(db_ferientermine, array('CALENDAR_EVENT_ID' => $eventId), array('ID' => $row->ID), array('%s'), array('%d')) !== false) {
          echo "Created event with id: $eventId<br>";
        } else {
          echo "Created event with id: $eventId, but could not save to database<br>";
        }
      } else {
        echo "Event failed!<br>";
      }
   }
  }

  
  /* Ferien-Management */
  function handle_admin_ferien_list() {
    global $wpdb;
    include __DIR__ . "/views/ferien_list.php";
  }
  
  function handle_admin_ferien_add() {
    include __DIR__ . "/views/ferien_modify.php";
  }
  
  function handle_admin_ferien_edit($id) {
    global $wpdb;
    if(!is_numeric($id)) {
      echo "ERROR: Invalid id (non-numeric)!";
      return;
    }
    $template = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . db_ferien . " WHERE FID = %d", $id));
    if($template == null) {
      echo "ERROR: Invalid id (not found)";
      return;
    }
    include __DIR__ . "/views/ferien_modify.php";
  }

  function handle_admin_ferien_modify_post() {
    global $wpdb;
    if(!isset($_POST['startDate']) or !isset($_POST['endDate']) or !isset($_POST['title'])) {
      status_header(400);
      exit("Invalid request: Missing parameter(s)!");
    }
    
    $dbData = array( 'LABEL' => strip_tags($_POST['title']), 'STARTDATE' => strip_tags($_POST['startDate']), 'ENDDATE' => strip_tags($_POST['endDate']));
    $dbType = array('%s', '%s', '%s');
    if(isset($_POST['id'])) {
      if($wpdb->update(db_ferien, $dbData, array('FID' => $_POST['id']), $dbType, array('%d')) !== FALSE) {
        wp_redirect( add_query_arg(array(
          'action' => 'ferien',
          'msg' => urlencode("Die Ferien \""  . strip_tags($_POST['title']) . "\" #" . intval($_POST['id']) . " wurden bearbeitet!"),
          'msgcol' => 'green',
        ), admin_url( 'admin.php?page=mb-options-menu') ) );
      } else {
        wp_redirect( add_query_arg(array(
          'action' => 'fktemplates',
          'msg' => urlencode("Die Ferien \""  . strip_tags($_POST['title']) . "\" konnten nicht bearbeitet werden (Datenbankfehler)!"),
          'msgcol' => 'red',
        ), admin_url( 'admin.php?page=mb-options-menu') ) );
      }
    } else {
      if($wpdb->insert(db_ferien, $dbData, $dbType) !== FALSE) {
        wp_redirect( add_query_arg(array(
          'action' => 'ferien',
          'msg' => urlencode("Die Ferien \""  . strip_tags($_POST['title']) . "\" #$wpdb->insert_id wurden erstellt!"),
          'msgcol' => 'green',
        ), admin_url( 'admin.php?page=mb-options-menu') ) );
      } else {
        wp_redirect( add_query_arg(array(
          'action' => 'ferien',
          'msg' => urlencode("Die Ferien \""  . strip_tags($_POST['title']) . "\" konnten nicht erstellt werden (Datenbankfehler)!"),
          'msgcol' => 'red',
        ), admin_url( 'admin.php?page=mb-options-menu') ) );
      }
    }
    exit;
  }
  
  function handle_admin_ferien_edit_post() {
    global $wpdb;
    if(!isset($_POST['startDate']) or !isset($_POST['endDate']) or !isset($_POST['title'])) {
      echo "ERROR: Invalid form data (fields missing)";
      return;
    }
    
    $dbData = array( 'LABEL' => strip_tags($_POST['title']), 'STARTDATE' => strip_tags($_POST['startDate']), 'ENDDATE' => strip_tags($_POST['endDate']));
    $dbType = array('%s', '%s', '%s');
    if(isset($_POST['id'])) {
      if($wpdb->update(db_ferien, $dbData, array('FID' => $_POST['id']), $dbType, array('%d')) !== FALSE) {
        echo "<div class=\"manage-controls mcok\"><p>Die Ferien \""  . strip_tags($_POST['title']) . "\" #", intval($_POST['id']), " wurden bearbeitet!</p></div><br>";
      } else {
        echo "<div class=\"manage-controls mcerr\"><p>Fehler: Die Ferien \""  . strip_tags($_POST['title']) . "\" konnten nicht bearbeitet werden (Datenbankfehler)!</p></div><br>";
        return handle_admin_ferientemplate_edit();
      }
    } else {
      if($wpdb->insert(db_ferien, $dbData, $dbType) !== FALSE) {
        echo "<div class=\"manage-controls mcok\"><p>Die Ferien \""  . strip_tags($_POST['title']) . "\" #$wpdb->insert_id wurden erstellt - <a href=\"?page=mb-options-menu&action=ferien\">zur Übersicht</a></p></div><br>";
      } else {
        echo "<div class=\"manage-controls mcerr\"><p>Fehler: Die Ferien \""  . strip_tags($_POST['title']) . "\" konnten nicht erstellt werden (Datenbankfehler)!</p></div><br>";
        return;
      }
    }
    echo "<script>updateUrl('ferien-edit', 'ferien');</script>";
    return handle_admin_ferien_list();
  }
?>