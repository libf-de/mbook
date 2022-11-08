<?php
  define("strict_bit", true); //Throw error if Ferienkurse are outside of selected ferien


  function handle_admin_ferienkurs_add()
  {
      global $wpdb;
      $ferien = db_ferien;
      $selectedFerien = (isset($_GET['fe']) and is_numeric($_GET['fe'])) ? $_GET['fe'] : get_standard_ferien();
      include __DIR__ . "/views/ferienkurs_add.php";
  }


  function handle_admin_ferienkurs_add_post_local()
  {
      global $wpdb;
      $templatedb = db_ferientemplates;
      $termin = db_ferientermine;
      $ferien = db_ferien;
      if (!isset($_POST['template']) or !isset($_POST['dates'])) {
          echo "<b>FATAL: Missing POST parameter(s) TEMPLATE and/or DATES!</b>";
          return;
      }
      if (!is_numeric($_POST['template'])) {
          echo "<b>FATAL: Non-numeric POST parameter TEMPLATE!</b>";
          return;
      }
      $template = $wpdb->get_row($wpdb->prepare("SELECT TITLE, SHORTHAND FROM " . db_ferientemplates . " WHERE ID = %d", $_POST['template']));
      //$template_title = $wpdb->get_var($wpdb->prepare("SELECT TITLE FROM " . db_ferientemplates . " WHERE ID = %d", $_POST['template']));
      if ($template == null) {
          echo "<b>FATAL: Invalid POST parameter TEMPLATE!</b>";
          return;
      }
      $eventNr = 1;
      foreach ($_POST['dates'] as $event) {
          if (!isset($event['date']) or !isset($event['start']) or (!isset($event['end']) and !isset($event['openEnd']))) {
              echo "<b>FATAL: Invalid POST parameter DATES!</b>";
              return;
          }

          $isOpenEnd = isset($event['openEnd']);
          $startDate = DateTime::createFromFormat('Y-m-d\TH:i', $event['date'] . "T" . $event['start']);
          $endDate = $isOpenEnd ? null : (DateTime::createFromFormat('Y-m-d\TH:i', $event['end']));
          $delta = $isOpenEnd ? "irgendwann" : $endDate->format($endDate->diff($startDate)->days > 0 ? 'd.m.Y, H:i' : 'H:i');

          //Find free shortcode
          $short_root = $template->SHORTHAND . $startDate->format('ymd'); //Shortcode format: [TPL_SHORT]YYMMDD[a,b,..,aa,..]
          $lastShort = $wpdb->get_var($wpdb->prepare("SELECT SHORTCODE FROM " . db_ferientermine . " WHERE SHORTCODE LIKE %s ORDER BY SHORTCODE DESC LIMIT 1", $short_root . "%"));
          if ($lastShort == null) {
              $nextShort = $short_root;
          } else {
              if (preg_match('/(?<=\d)[a-z]+(?!.*\d)/', $lastShort, $matches)) {
                  $nextShort = $short_root . (++$matches[0]);
              } else {
                  $nextShort = $short_root . "a";
              }
          }

          if ($wpdb->insert(db_ferientermine, array('TEMPLATE' => $_POST['template'], 'SHORTCODE' => $nextShort, 'FERIEN' => 1, 'IS_OPEN_END' => $isOpenEnd, 'DATESTART' => $startDate->format('Y-m-d H:i:s'), 'DATEEND' => $endDate == null ? null : $endDate->format('Y-m-d H:i:s'), 'MAX_PARTICIPANTS' => $_POST['max-participants'])) !== false) {
              echo "<div class=\"manage-controls mcok\"><p>Der \"", $template->TITLE, "\"-Ferienkurs #$wpdb->insert_id am ", $startDate->format("d.m.Y, H:i"), " bis ", $delta, " wurde erstellt</p></div><br>";

              //TODO: Verify if enabled, better error logging
              require_once(dirname(__DIR__) . '/calendar/caltest.php');
              $gca = new GoogleCalenderAdapter();
              $newEvent = $wpdb->get_row($wpdb->prepare("SELECT `$termin`.*, `$templatedb`.TITLE FROM `$termin` INNER JOIN `$templatedb` ON `$termin`.`TEMPLATE` = `$templatedb`.`ID` WHERE `$termin`.ID = %d", $wpdb->insert_id));
              $eventId = $gca->update_calendar_event($newEvent);
              if ($eventId != null) {
                  $wpdb->update(db_ferientermine, array('CALENDAR_EVENT_ID' => $eventId), array('ID' => $newEvent->ID), array('%s'), array('%d'));
              }
          } else {
              echo "<div class=\"manage-controls mcerr\"><p>Fehler: Der \"", $template->TITLE, "\"-Ferienkurs #$wpdb->insert_id am ", $startDate->format("d.m.Y, H:i"), " bis ", $delta, " konnte nicht erstellt werden!</p></div><br>";
          }
      }
      echo "<a href=\"?page=mb-options-menu&action=managefk\" class=\"button button-primary\">Zurück zur Übersicht</a>";
  }


  /* TODO: Implement Ferien selection */
  function handle_admin_ferienkurs_list()
  {
      wp_localize_script('mb-fklist-js', 'WPURL', array('fkdelete' => admin_url('admin-post.php?action=mb_fk_delete')));
      wp_enqueue_script('mb-fklist-js');
      global $wpdb;
      $template = db_ferientemplates;
      $termin = db_ferientermine;
      $ferien = db_ferien;

      $selectedFerien = (isset($_GET['fe']) and is_numeric($_GET['fe'])) ? $_GET['fe'] : get_standard_ferien();

      include __DIR__ . "/views/ferienkurs_list.php";
  }


  function handle_ajax_ferienkurs() {
    global $wpdb;
    $template = db_ferientemplates;
    $termin = db_ferientermine;
    $ferien = db_ferien;
    $selectedFerien = $_POST['fe'];
    include __DIR__ . "/views/ferienkurs_ajax_list.php";
    wp_die();
  }

  function handle_admin_ferienkurs_edit_post()
  {
      global $wpdb;
      $template = db_ferientemplates;
      $termin = db_ferientermine;
      $ferien = db_ferien;

      if (!isset($_POST['id']) or !isset($_POST['start']) or !isset($_POST['end']) or !isset($_POST['maxparts'])) {
          status_header(400);
          exit("Invalid request: missing parameter(s) (id, start, end, maxparts)"); //TODO: Remove?
      }
      if (!is_numeric($_POST['id']) or !is_numeric($_POST['maxparts'])) {
          status_header(400);
          exit("Invalid request: invalid parameter(s) datatype(s)");
      }

      //print_r($_POST);
      //exit($_POST['startdate'] . "T" . $_POST['start']);
      $isOpenEnd = isset($_POST['openEnd']);
      $startDate = DateTime::createFromFormat('d.m.Y\TH:i', $_POST['startdate'] . "T" . $_POST['start']);
      $endDate = $isOpenEnd ? null : (DateTime::createFromFormat('Y-m-d\TH:i', $_POST['end']));

      $dbData = array(
        'DATESTART' => $startDate->format('Y-m-d H:i:s'),
        'DATEEND' => $isOpenEnd ? null : $endDate->format('Y-m-d H:i:s'),
        'MAX_PARTICIPANTS' => intval($_POST['maxparts']),
        'IS_OPEN_END' => $isOpenEnd,
        'IS_CANCELLED' => isset($_POST['cancelled']));
      $dbType = array('%s', '%s', '%d', '%d', '%d');
      if ($wpdb->update(db_ferientermine, $dbData, array('ID' => $_POST['id']), $dbType, array('%d')) !== false) {
          require_once(dirname(__DIR__) . '/calendar/caltest.php');
          $gca = new GoogleCalenderAdapter();
          $modEvent = $wpdb->get_row($wpdb->prepare("SELECT `$termin`.*, `$template`.TITLE FROM `$termin` INNER JOIN `$template` ON `$termin`.`TEMPLATE` = `$template`.`ID` WHERE `$termin`.ID = %d", $_POST['id']));
          if ($modEvent != null) {
              $eventId = $gca->update_calendar_event($modEvent);
              if ($eventId != null && $modEvent->CALENDAR_EVENT_ID != $eventId) {
                  $wpdb->update(db_ferientermine, array('CALENDAR_EVENT_ID' => $eventId), array('ID' => $newEvent->ID), array('%s'), array('%d'));
              }
          }

          wp_redirect(add_query_arg(array(
              'action' => 'managefk',
              'fe' => $_POST['fe'],
              'msg' => urlencode("Der Ferienkurs am \""  . strip_tags($_POST['startdate']) . "\", Nr. #" . intval($_POST['id']) . " wurde bearbeitet!"),
              'msgcol' => 'green',
              'hl' => $_POST['id'],
            ), admin_url('admin.php?page=mb-options-menu')));
      } else {
          wp_redirect(add_query_arg(array(
            'action' => 'managefk',
            'fe' => $_POST['fe'],
            'msg' => urlencode("Der Ferienkurs am \""  . strip_tags($_POST['startdate']) . "\", Nr. #" . intval($_POST['id']) . " konnte nicht bearbeitet werden (Datenbankfehler)!"),
            'msgcol' => 'red',
            'hl' => $_POST['id'],
          ), admin_url('admin.php?page=mb-options-menu')));
      }
      exit;
  }


  function handle_admin_debug() {
    status_header(200);
    exit("hi");
  }

  function handle_admin_get_occupation_for_month() {
    global $wpdb;

    $occdates = array();

    $flt = " WHERE ";
    $args = array();
    if(isset($_POST["m"])) {
        $flt .= "MONTH(DATESTART) = %d AND ";
        array_push($args, intval($_POST["m"]));
    }
    if(isset($_POST["y"])) {
        $flt .= "YEAR(DATESTART) = %d AND ";
        array_push($args, intval($_POST["y"]));
    }
    if(isset($_POST["t"])) {
        $flt .= "TEMPLATE = %d AND ";
        array_push($args, intval($_POST["t"]));
    }
    if(isset($_POST["f"])) {
        $flt .= "FERIEN = %d AND ";
        array_push($args, intval($_POST["f"]));
    }
    $flt .= "ID >= %d";
    array_push($args, 1);
    

    $sql_kurse = $wpdb->get_results($wpdb->prepare("SELECT ID, DATESTART, DATEEND, TEMPLATE, FERIEN FROM " . db_ferientermine . $flt, $args));

    foreach($sql_kurse as $kurs) {
        if($kurs->DATEEND != null) {
            $period = new DatePeriod(
                DateTime::createFromFormat(mysql_date, $kurs->DATESTART),
                new DateInterval('P1D'),
                DateTime::createFromFormat(mysql_date, $kurs->DATEEND));
            foreach ($period as $key => $value) {
                array_push($occdates, $value->format("Y-m-d"));
            }
        } else {
            array_push($occdates, DateTime::createFromFormat(mysql_date, $kurs->DATESTART)->format("Y-m-d"));
        }
    }

    status_header(200);
    exit(json_encode($occdates));
  }


  function handle_admin_ferienkurs_delete_post()
  {
      global $wpdb;
      $template = db_ferientemplates;
      $termin = db_ferientermine;

      if (!isset($_POST['id'])) {
          status_header(400);
          exit("Invalid request: missing parameter(s)!");
      }
      if (!is_numeric($_POST['id'])) {
          status_header(400);
          exit("Invalid request: invalid parameter(s) type(s)!");
      }

      $goneObj = $wpdb->get_row($wpdb->prepare("SELECT `$termin`.*, `$template`.TITLE FROM `$termin` INNER JOIN `$template` ON `$termin`.`TEMPLATE` = `$template`.`ID` WHERE `$termin`.ID = %d", $_POST['id']));
      $goneDate = DateTime::createFromFormat(mysql_date, $goneObj->DATESTART);
      if ($goneObj == null) {
          status_header(400);
          exit("Invalid request: id not found!");
      }

      $googleResult = null;
      if ($goneObj->CALENDAR_EVENT_ID != null) {
          require_once(dirname(__DIR__) . '/calendar/caltest.php');
          $gca = new GoogleCalenderAdapter();
          $googleResult = $gca->delete_calendar_event($goneObj);
      }

      if ($wpdb->delete($termin, array( 'ID' => $_POST['id']), array('%d')) !== false) {
          wp_redirect(add_query_arg(array(
              'action' => 'managefk',
              'fe' => $_POST['fe'],
              'msg' => 'Kurs ' . urlencode($goneObj->TITLE) . ' am ' . $goneDate->format("d.m.Y, H.i") . ' Uhr wurde gelöscht' . ($googleResult == false ? ", konnte jedoch nicht aus dem Google Kalender gelöscht werden" : ""),
              'msgcol' => 'green',
          ), admin_url('admin.php?page=mb-options-menu')));
      } else {
          wp_redirect(add_query_arg(array(
            'action' => 'managefk',
            'fe' => $_POST['fe'],
            'msg' => 'Kurs ' . urlencode($goneObj->TITLE) . ' am ' . $goneDate->format("d.m.Y, H.i") . ' Uhr konnte nicht gelöscht werden',
            'msgcol' => 'red',
          ), admin_url('admin.php?page=mb-options-menu')));
      }
      exit;
  }

  function handle_admin_ferienkurs_add_post()
  {
      global $wpdb;
      $dbtemplate = db_ferientemplates;
      $dbtermin = db_ferientermine;
      $success = true;
      //print_r($_POST);
      //exit();

      if (!isset($_POST['template']) or !isset($_POST['dates'])) {
          status_header(400);
          exit("Invalid request: missing parameter(s)");
      }
      if (!is_numeric($_POST['template'])) {
          status_header(400);
          exit("Invalid request: invalid parameter (template NaN)");
      }
      $template = $wpdb->get_row($wpdb->prepare("SELECT TITLE, SHORTHAND FROM " . db_ferientemplates . " WHERE ID = %d", $_POST['template']));
      if ($template == null) {
          status_header(400);
          exit("Invalid request: invalid parameter (template null)");
      }

      if (isset($_POST['ferien'])) {
          if (!is_numeric($_POST['ferien'])) {
              status_header(400);
              exit("Invalid request: invalid parameter (ferien NaN)");
          }
          $ferien_row = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . db_ferien . " WHERE FID = %d", $_POST['ferien']));
          if ($ferien_row == null) {
              status_header(400);
              exit("Invalid request: invalid parameter (ferien null)");
          }
          $ferienStartDate = DateTime::createFromFormat("Y-m-d", $ferien_row->STARTDATE);
          $ferienStartDate->setTime(0, 0, 0, 0);
          $ferienEndDate = DateTime::createFromFormat("Y-m-d", $ferien_row->ENDDATE);
          $ferienEndDate->setTime(23, 59, 59);
      }

      $eventNr = 0;
      foreach ($_POST['dates'] as $event) {
          if (!isset($event['date']) or !isset($event['start']) or (!isset($event['end']) and !isset($event['openEnd']))) {
              status_header(400);
              exit("Invalid request: missing paramters on event $eventNr");
          }

          $isOpenEnd = isset($event['openEnd']);
          $startDate = DateTime::createFromFormat('Y-m-d\TH:i', $event['date'] . "T" . $event['start']);
          $endDate = $isOpenEnd ? null : (DateTime::createFromFormat('Y-m-d\TH:i', $event['end']));
          $delta = $isOpenEnd ? "irgendwann" : $endDate->format($endDate->diff($startDate)->days > 0 ? 'd.m.Y, H:i' : 'H:i');

          if (isset($ferienStartDate) and isset($ferienEndDate) and strict_bit) {
              //TODO: This is ugly. Figure out the logic table wenn ich Kopf dafür hab.
              $strict_fail = false;
              if ($startDate < $ferienStartDate) {
                  $strict_fail = true;
              }
              if ($endDate != null) {
                  if ($endDate > $ferienEndDate) {
                      $strict_fail = true;
                  }
              }

              if ($strict_fail) {
                  wp_redirect(add_query_arg(array(
                    'action' => 'managefk',
                    'fe' => $_POST['fe'],
                    'msg' => urlencode("Der " . $template->TITLE . "-Kurs am " . $startDate->format("d.m.Y H:i") . " - " . $delta . " liegt außerhalb der gewählten Ferien und STRICT_BIT ist gesetzt!"),
                    'msgcol' => $success ? 'green' : 'red',
                  ), admin_url('admin.php?page=mb-options-menu')));
                  exit;
              }
          }

          //Find free shortcode
          $short_root = $template->SHORTHAND . $startDate->format('ymd'); //Shortcode format: [TPL_SHORT]YYMMDD[a,b,..,aa,..]
          $lastShort = $wpdb->get_var($wpdb->prepare("SELECT SHORTCODE FROM " . db_ferientermine . " WHERE SHORTCODE LIKE %s ORDER BY SHORTCODE DESC LIMIT 1", $short_root . "%"));
          if ($lastShort == null) {
              $nextShort = $short_root;
          } else {
              if (preg_match('/(?<=\d)[a-z]+(?!.*\d)/', $lastShort, $matches)) {
                  $nextShort = $short_root . (++$matches[0]);
              } else {
                  $nextShort = $short_root . "a";
              }
          }

          if ($wpdb->insert(db_ferientermine, array('TEMPLATE' => $_POST['template'], 'SHORTCODE' => $nextShort, 'FERIEN' => $_POST['ferien'], 'IS_OPEN_END' => $isOpenEnd, 'DATESTART' => $startDate->format('Y-m-d H:i:s'), 'DATEEND' => $endDate == null ? null : $endDate->format('Y-m-d H:i:s'), 'MAX_PARTICIPANTS' => $_POST['max-participants'])) !== false) {
              $eventNr = $eventNr + 1;
          } else {
              $success = false;
          }

          //TODO: Verify if enabled, better error logging
          require_once(dirname(__DIR__) . '/calendar/caltest.php');

          $gca = new GoogleCalenderAdapter();
          $newEvent = $wpdb->get_row($wpdb->prepare("SELECT `$dbtermin`.*, `$dbtemplate`.TITLE FROM `$dbtermin` INNER JOIN `$dbtemplate` ON `$dbtermin`.`TEMPLATE` = `$dbtemplate`.`ID` WHERE `$dbtermin`.ID = %d", $wpdb->insert_id));
          $eventId = $gca->update_calendar_event($newEvent);
          if ($eventId != null) {
              $wpdb->update(db_ferientermine, array('CALENDAR_EVENT_ID' => $eventId), array('ID' => $newEvent->ID), array('%s'), array('%d'));
          }
      }


      wp_redirect(add_query_arg(array(
        'action' => 'managefk',
        'fe' => $_POST['fe'],
        'msg' => urlencode($success ? "Es wurden erfolgreich $eventNr " . $template->TITLE . "-Kurse erstellt!" : "MIndestens ein Kurs konnte nicht erstellt werden. Es wurden jedoch $eventNr " . $template->TITLE . "-Kurse erfolgreich erstellt."),
        'msgcol' => $success ? 'green' : 'red',
      ), admin_url('admin.php?page=mb-options-menu')));
      exit;
  }
