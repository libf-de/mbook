<?php
const strict_bit = true; //Throw error if Ferienkurse are outside of selected ferien

/**
 * Shows the Ferienkurs creation page
 * [$_POST['fe']: Selected Ferien]
 * -- (?page=nb-options-menu&action=fkurs-add)
 * @return void ok
 */
function handle_admin_ferienkurs_add()
  {
      // Load FontAwesome, jQuery UI (+datepicker +multiDatesPicker),
      // nubook.ferienkurs.add.css and nubook.ferienkurs.add.js
      nb_load_fa();
      wp_enqueue_style('jqueryui');
      wp_enqueue_style('jqueryui-theme');
      wp_enqueue_script('jquery-ui-multidate');
      wp_enqueue_style('nb-fkadd-css'); //calcmode: 1=PM=blocks +- days; 0=FLT=filters selected dates
      wp_localize_script('nb-fkadd-js', 'WPURL', array('queryurl' => admin_url('admin-post.php?action=nb_fk_query'), 
                                                       'calcmode' => get_option("nb_calcmode") ?? 0));
      wp_enqueue_script('nb-fkadd-js');
      wp_enqueue_script('nbfkjs');

      global $wpdb;
      $ferien = db_ferien;
      $selectedFerien = (isset($_GET['fe']) and is_numeric($_GET['fe'])) ? $_GET['fe'] : get_standard_ferien();
      include __DIR__ . "/views/ferienkurs_add.php";
  }

/**
 * Displays the Ferienkurs list
 * [$_POST['fe']: Selected Ferien]
 * -- (?page=nb-options-menu&action=fkurs-manage)
 * @return void ok
 */
function handle_admin_ferienkurs_list()
  {
      // Load jQuery UI (+dialog), nubook.ferienkurs.list.css
      // and nubook.ferienkurs.list.js
      nb_load_fa();
      wp_enqueue_style('jqueryui');
      wp_enqueue_style('jqueryui-theme');
      wp_enqueue_script('jquery-ui-dialog');
      wp_enqueue_style('nb-fklist-css'); //TODO: Maybe only load if showing numeric participants input [-|123|+]
      wp_localize_script('nb-fklist-js', 'WPURL', array('fkdelete' => admin_url('admin-post.php?action=nb_fk_delete')));
      wp_enqueue_script('nb-fklist-js');

      global $wpdb;
      $template = db_ferientemplates;
      $termin = db_ferientermine;
      $ferien = db_ferien;

      $selectedFerien = (isset($_GET['fe']) and is_numeric($_GET['fe'])) ? $_GET['fe'] : get_standard_ferien();
      $partMode = intval(get_option("nb_partmode")) ?? 0;

      include __DIR__ . "/views/ferienkurs_list.php";
  }

/**
 * Displays the list for given Ferien to insert into @link handle_admin_ferienkurs_list()
 * $_POST['fe']: Ferien to display [not verified, will show "No courses" for invalid ferien]
 * @return void ok
 */
function handle_ajax_ferienkurs()
  {
      global $wpdb;
      $template = db_ferientemplates;
      $termin = db_ferientermine;
      $ferien = db_ferien;
      $selectedFerien = $_POST['fe'];
      $partMode = intval(get_option("nb_partmode")) ?? 0;
      include __DIR__ . "/views/ferienkurs_ajax_list.php";
      wp_die();
  }

/**
 * Modifies the given Ferienkurs with given data
 * $_POST['id']: (int) id to edit
 * $_POST['startdate']: (str:"DD.MM.YYYY") start date
 * $_POST['start']: (str:"hh:mm") start time
 * $_POST['maxparts']: (int) maximum participants
 * [$_POST['cancelled']: Kurs is cancelled]
 *
 * EITHER: $_POST['end']: (str:"YYYY-MM-DD\Thh:mm") end datetime
 * OR: $_POST['openEnd']: Kurs is open end
 *
 * -- (admin_post_nb_fk_edit)
 *
 * @return void redirect/invalid request
 */
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

      if(!$startDate or (!$endDate and !$isOpenEnd)) {
        status_header(400);
        exit("Invalid request: invalid parameter(s) startDate, start or endDate: must be in DD.MM.YYYY, hh:mm or YYYY-MM-DD\Thh:mm format!");
      }

      if($startDate > $endDate and $endDate != null) {
        wp_redirect(add_query_arg(array(
            'action' => 'fkurs-manage',
            'fe' => $_POST['fe'],
            'msg' => urlencode("Fehler beim Bearbeiten: Start muss vor Ende liegen - versuche es erneut."),
            'msgcol' => 'red',
            'hl' => $_POST['id'],
          ), admin_url('admin.php?page=nb-options-menu')));
        exit;
      }

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
                  $wpdb->update(db_ferientermine, array('CALENDAR_EVENT_ID' => $eventId), array('ID' => $modEvent->ID), array('%s'), array('%d'));
              }
          }

          wp_redirect(add_query_arg(array(
              'action' => 'fkurs-manage',
              'fe' => $_POST['fe'],
              'msg' => urlencode("Der Ferienkurs am \""  . strip_tags($_POST['startdate']) . "\", Nr. #" . intval($_POST['id']) . " wurde bearbeitet!"),
              'msgcol' => 'green',
              'hl' => $_POST['id'],
            ), admin_url('admin.php?page=nb-options-menu')));
      } else {
          wp_redirect(add_query_arg(array(
            'action' => 'fkurs-manage',
            'fe' => $_POST['fe'],
            'msg' => urlencode("Der Ferienkurs am \""  . strip_tags($_POST['startdate']) . "\", Nr. #" . intval($_POST['id']) . " konnte nicht bearbeitet werden (Datenbankfehler)!"),
            'msgcol' => 'red',
            'hl' => $_POST['id'],
          ), admin_url('admin.php?page=nb-options-menu')));
      }
      exit;
  }


/**
 * Yields the dates for a given template, month, year, ferien on which a Kurs takes place (JSON-encoded Y-m-d list)
 * [$_POST['m']: (int) month]
 * [$_POST['y']: (int) year]
 * [$_POST['t']: (int) template]
 * [$_POST['f']: (int) ferien]
 * invalid parameters will be ignored!
 * -- (admin_post_nb_fk_query)
 * @return void ok -> {'Y-m-d',…}
 */
function handle_admin_get_occupation_for_month()
  {
      global $wpdb;

      $occdates = array();

      $flt = " WHERE ";
      $args = array();
      if (is_numeric($_POST["m"])) {
          $flt    .= "MONTH(DATESTART) = %d AND ";
          $args[] = intval( $_POST["m"] );
      }
      if (is_numeric($_POST["y"])) {
          $flt    .= "YEAR(DATESTART) = %d AND ";
          $args[] = intval( $_POST["y"] );
      }
      if (is_numeric($_POST["t"])) {
          $flt    .= "TEMPLATE = %d AND ";
          $args[] = intval( $_POST["t"] );
      }
      if (is_numeric($_POST["f"])) {
          $flt    .= "FERIEN = %d AND ";
          $args[] = intval( $_POST["f"] );
      }
      $flt       .= "ID >= %d";
      $args[]    = 1;
      $sql_kurse = $wpdb->get_results($wpdb->prepare("SELECT ID, DATESTART, DATEEND, TEMPLATE, FERIEN FROM " . db_ferientermine . $flt, $args));

      foreach ($sql_kurse as $kurs) {
          if ($kurs->DATEEND != null) {
              $period = new DatePeriod(
                  DateTime::createFromFormat(mysql_date, $kurs->DATESTART),
                  new DateInterval('P1D'),
                  DateTime::createFromFormat(mysql_date, $kurs->DATEEND)
              );
              foreach ($period as $value) {
                  $occdates[] = $value->format( "Y-m-d" );
              }
          } else {
              $occdates[] = DateTime::createFromFormat( mysql_date, $kurs->DATESTART )->format( "Y-m-d" );
          }
      }

      status_header(200);
      exit(json_encode($occdates));
  }


/**
 * Deletes the given Ferienkurs
 * $_POST['id']: (int) id to delete
 * -- (admin_post_nb_fk_delete)
 * @return void redirect/invalid request
 */
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
              'action' => 'fkurs-manage',
              'fe' => $_POST['fe'],
              'msg' => 'Kurs ' . urlencode($goneObj->TITLE) . ' am ' . $goneDate->format("d.m.Y, H.i") . ' Uhr wurde gelöscht' . ( !$googleResult ? ", konnte jedoch nicht aus dem Google Kalender gelöscht werden" : ""),
              'msgcol' => 'green',
          ), admin_url('admin.php?page=nb-options-menu')));
      } else {
          wp_redirect(add_query_arg(array(
            'action' => 'fkurs-manage',
            'fe' => $_POST['fe'],
            'msg' => 'Kurs ' . urlencode($goneObj->TITLE) . ' am ' . $goneDate->format("d.m.Y, H.i") . ' Uhr konnte nicht gelöscht werden',
            'msgcol' => 'red',
          ), admin_url('admin.php?page=nb-options-menu')));
      }
      exit;
  }

/**
 * Creates a Ferienkurs with given data
 * (also generates Shortcode and creates calendar event)
 * $_POST['template']: (int) template
 * $_POST['dates']: (array) Kurse
 * --> ['date']: (str:"YYYY-mm-dd") start date
 * --> ['start']: (str:"HH:MM") start time
 * --> ['end']: (str:"Y-m-d\THH:MM") end datetime *OR*
 * --> [['openEnd']: Kurs is open End]
 *
 * -- (admin_post_nb_fk_add)
 * @return void redirect/invalid request
 */
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
          $ferienStartDate->setTime(0, 0 );
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

          // Cancel creation if course is outside selected ferien (+- 2 days for weekend) and strict mode is enabled
          if (strict_bit and isset($ferienStartDate) and isset($ferienEndDate) and
              ($startDate < $ferienStartDate->modify("-2 days") or $endDate > $ferienEndDate->modify("+2 days")) ) {
              wp_redirect(add_query_arg(array(
              'action' => 'fkurs-manage',
              'fe' => $_POST['ferien'],
              'msg' => urlencode("Der " . $template->TITLE . "-Kurs am " . $startDate->format("d.m.Y H.i") . " - " . str_replace(":", ".", $delta) . " liegt außerhalb der gewählten Ferien und STRICT_BIT ist gesetzt!"),
              'msgcol' => 'red',
              ), admin_url('admin.php?page=nb-options-menu')));
              exit;
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
        'action' => 'fkurs-manage',
        'fe' => $_POST['ferien'],
        'msg' => urlencode($success ? "Es wurden erfolgreich $eventNr " . $template->TITLE . "-Kurse erstellt!" : "MIndestens ein Kurs konnte nicht erstellt werden. Es wurden jedoch $eventNr " . $template->TITLE . "-Kurse erfolgreich erstellt."),
        'msgcol' => $success ? 'green' : 'red',
      ), admin_url('admin.php?page=nb-options-menu')));
      exit;
  }

/**
 * Displays the cleanup Ferienkurs page
 * @return void ok
 */
function handle_admin_ferienkurs_clean()
  {   
      nb_load_fa();

      global $wpdb;
      include __DIR__ . "/views/ferienkurs_clean.php";
  }

/**
 * Perform cleanup: Deletes Ferien/-kurse older than given days
 * $_POST['timespan']: (int) days
 * -- (admin_post_nb_fk_clean)
 * @return void redirect/invalid request
 */
function handle_admin_ferienkurs_clean_post()
  {
      global $wpdb;

      $template = db_ferientemplates;
      $termin = db_ferientermine;
      $ferien = db_ferien;

      if (!is_numeric($_POST['timespan'])) {
          status_header(400);
          exit("Invalid request: invalid parameter timespan");
      }

      if (intval($_POST['timespan']) < 0) {
          status_header(400);
          exit("Invalid request: invalid parameter timespan");
      }

      //Step 1: delete all rows older than n days
      $wpdb->query($wpdb->prepare("DELETE FROM `$termin` WHERE DATESTART < NOW() - INTERVAL %d DAY", intval($_POST['timespan'])));

      //Step 2: delete old, empty Ferien:
      $wpdb->query($wpdb->prepare("DELETE FROM `$ferien` WHERE FID NOT IN (SELECT FERIEN FROM `$termin` WHERE FERIEN IS NOT NULL) AND ENDDATE < NOW() - INTERVAL %d DAY", intval($_POST['timespan'])));

      if ($wpdb->get_row($wpdb->prepare("SELECT FID FROM `$ferien` WHERE FID = %d", get_standard_ferien())) == null) {
          update_option('standard_ferien', 1);
      }

      wp_redirect(add_query_arg(array(
        'action' => 'fkurs-manage',
        'msg' => urlencode("Ferienkurse und Ferien wurden bereinigt!"),
        'msgcol' => 'green',
      ), admin_url('admin.php?page=nb-options-menu')));
      exit;
  }


/**
 * Displays the copy Ferienkurs "select src/dst" page (clone Kurses relative to startdate of Ferien)
 * -- (?page=nb-options-menu&action=fkurs-copy)
 * @return void ok
 */
function handle_admin_ferienkurs_copy()
  {
      nb_load_fa();

      global $wpdb;
      $template = db_ferientemplates;
      $termin = db_ferientermine;
      $ferien = db_ferien;
      include __DIR__ . "/views/ferienkurs_copy.php";
  }

/**
 * Displays the copy Ferienkurs preview page (clone Kurses relative to startdate of Ferien)
 * $_GET['ferien-src']: (int) source ferien id
 * $_GET['ferien-dst']: (int) destination ferien id
 * -- (?page=nb-options-menu&action=fkurs-copy-prv)
 * @return void ok
 */
function handle_admin_ferienkurs_copy_preview()
  {
      nb_load_fa();
      wp_enqueue_style("nb-fkcopy-css");

      global $wpdb;
      $template = db_ferientemplates;
      $termin = db_ferientermine;
      $ferien = db_ferien;

      if(intval($_GET['ferien-src']) == intval($_GET['ferien-dst'])) {
        echo "<h1 style=\"text-align: center; color: red;\">Quell- und Zielferien müssen unterschiedlich sein!</h1><br>";
        return handle_admin_ferienkurs_copy();
     }

      include __DIR__ . "/views/ferienkurs_copy_preview.php";
  }


/**
 * Performs Ferienkurs copy (clone relative to startdate of src-Ferien)
 * $_POST['ferien-src']: (int) source ferien id
 * $_POST['ferien-dst']: (int) destination ferien id
 * -- (admin_post_nb_fk_copy)
 * @return void redirect/invalid request
 */
function handle_admin_ferienkurs_copy_post()
  {
      global $wpdb;
      $template = db_ferientemplates;
      $termin = db_ferientermine;
      $ferien = db_ferien;
      $success = true;

      if (!is_numeric($_POST['ferien-src']) || !is_numeric($_POST['ferien-dst'])) {
          status_header(400);
          exit("<h1>Invalid paramters: ferien-src, ferien-dst must be numeric!</h1>");
      }

      if(intval($_POST['ferien-src']) == intval($_POST['ferien-dst'])) {
          status_header(400);
          exit("<h1>Quell- und Zielferien müssen unterschiedlich sein!</h1>");
      }

      $src_ferien = $wpdb->get_row($wpdb->prepare("SELECT * FROM `$ferien` WHERE FID = %d", intval($_POST["ferien-src"])));
      $dst_ferien = $wpdb->get_row($wpdb->prepare("SELECT * FROM `$ferien` WHERE FID = %d", intval($_POST["ferien-dst"])));

      if ($src_ferien == null || $dst_ferien == null) {
          status_header(400);
          exit("<h1>Invalid ferien!</h1>");
      }

      $srcf_start = DateTime::createFromFormat('Y-m-d', $src_ferien->STARTDATE);
      $dstf_start = DateTime::createFromFormat('Y-m-d', $dst_ferien->STARTDATE);

      $src_kurse = $wpdb->get_results($wpdb->prepare("SELECT `$termin`.*, `$template`.TITLE, `$template`.SHORTHAND, `$template`.EXP_LEVEL_MIN,
      `$template`.EXP_LEVEL_MAX FROM `$termin` INNER JOIN `$template` ON `$termin`.`TEMPLATE` = `$template`.`ID` WHERE
      `$termin`.FERIEN = %d ORDER BY `$termin`.`DATESTART` >= CURDATE() DESC, `$termin`.`DATESTART`", intval($_POST['ferien-src'])));

      $eventNr = 0;
      foreach ($src_kurse as $key => $row) {
          $src_start = DateTime::createFromFormat(mysql_date, $row->DATESTART);

          $daysDelta = $srcf_start->diff($src_start)->days;
          $target = $src_start->format('l');
          $dst_start = $dstf_start->modify("+{$daysDelta} days")->modify("-5 days")->modify("next $target");
          $dst_start->setTime($src_start->format('H'), $src_start->format("i"));

          if (!$row->IS_OPEN_END) {
              $delta_diff = DateTime::createFromFormat(mysql_date, $row->DATEEND)->diff($src_start);
              $dst_end = clone $dst_start;
              $dst_end->sub($delta_diff);
          } else {
              $dst_end = null;
          }

          $delta = $row->IS_OPEN_END ? "irgendwann" : $dst_end->format($dst_end->diff($dst_start)->days > 0 ? 'd.m.Y, H:i' : 'H:i');
          $short_root = $row->SHORTHAND . $dst_start->format('ymd'); //Shortcode format: [TPL_SHORT]YYMMDD[a,b,..,aa,..]
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

          if ($wpdb->insert(db_ferientermine, array('TEMPLATE' => $row->TEMPLATE, 'SHORTCODE' => $nextShort, 'FERIEN' => $_POST['ferien-dst'], 'IS_OPEN_END' => $row->IS_OPEN_END, 'DATESTART' => $dst_start->format('Y-m-d H:i:s'), 'DATEEND' => $dst_end == null ? null : $dst_end->format('Y-m-d H:i:s'), 'MAX_PARTICIPANTS' => $row->MAX_PARTICIPANTS)) !== false) {
              $eventNr = $eventNr + 1;
          } else {
              $success = false;
          }

          //TODO: Verify if enabled, better error logging
          require_once(dirname(__DIR__) . '/calendar/caltest.php');

          $gca = new GoogleCalenderAdapter();
          $newEvent = $wpdb->get_row($wpdb->prepare("SELECT `$termin`.*, `$template`.TITLE FROM `$termin` INNER JOIN `$template` ON `$termin`.`TEMPLATE` = `$template`.`ID` WHERE `$termin`.ID = %d", $wpdb->insert_id));
          $eventId = $gca->update_calendar_event($newEvent);
          if ($eventId != null) {
              $wpdb->update(db_ferientermine, array('CALENDAR_EVENT_ID' => $eventId), array('ID' => $newEvent->ID), array('%s'), array('%d'));
          }
      }

      wp_redirect(add_query_arg(array(
        'action' => 'fkurs-manage',
        'fe' => $_POST['ferien-dst'],
        'msg' => urlencode($success ? "Es wurden erfolgreich $eventNr Ferienkurse kopiert!" : "Mindestens ein Kurs konnte nicht kopiert werden. Es wurden jedoch $eventNr erfolgreich kopiert."),
        'msgcol' => $success ? 'green' : 'red',
      ), admin_url('admin.php?page=nb-options-menu')));
      exit;
  }
