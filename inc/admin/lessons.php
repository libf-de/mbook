<?php
  function handle_admin_lessons_add()
  {
      wp_enqueue_style('nb-lessons-css');
      wp_enqueue_script('nb-lsadd-js');
      global $wpdb;
      include __DIR__ . "/views/lessons_add.php";
  }


  /* TODO: Implement Ferien selection */
  function handle_admin_lessons_list()
  {
      wp_enqueue_style('nb-lessons-css');
      wp_localize_script('nb-lslist-js', 'WPURL', array('lsdelete' => admin_url('admin-post.php?action=nb_ls_delete')));
      wp_enqueue_script('nb-lslist-js');
      global $wpdb;
      $dbTemplate = db_lessontemplates;
      $dbLesson = db_lessons;

      $objs = $wpdb->get_results("SELECT ID, TEMPLATE, WEEKDAY, `START` FROM `wp_nubook_lessons` WHERE TEMPLATE = 2 AND WEEKDAY = 1 ORDER BY `START` ASC");


      //renumber_lessons();
      //die();
      include __DIR__ . "/views/lessons_list.php";
  }


  function handle_admin_lessons_edit_post()
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

      $dbData = array(
        'WEEKDAY' => $_POST['weekday'],
        'START' => $_POST['start'],
        'END' => $_POST['end'],
        'MAX_PARTICIPANTS' => intval($_POST['maxparts']),
        'IS_CANCELLED' => isset($_POST['cancelled']));
      $dbType = array('%d', '%s', '%s', '%d', '%d', );
      if ($wpdb->update(db_lessons, $dbData, array('ID' => $_POST['id']), $dbType, array('%d')) !== false) {
          renumber_lessons();
          wp_redirect(add_query_arg(array(
              'action' => 'lessons',
              'fe' => $_POST['fe'],
              'msg' => urlencode("Die Unterrichtsstunde am \""  . weekday_names[$_POST['weekday']] . "\", Nr. #" . intval($_POST['id']) . " wurde bearbeitet!"),
              'msgcol' => 'green',
              'hl' => $_POST['id'],
            ), admin_url('admin.php?page=nb-options-lessons')));
      } else {
          wp_redirect(add_query_arg(array(
            'action' => 'lessons',
            'fe' => $_POST['fe'],
            'msg' => urlencode("Die Unterrichtsstunde am \""  . weekday_names[$_POST['weekday']] . "\", Nr. #" . intval($_POST['id']) . " konnte nicht bearbeitet werden (Datenbankfehler)!"),
            'msgcol' => 'red',
            'hl' => $_POST['id'],
          ), admin_url('admin.php?page=nb-options-lessons')));
      }
      exit;
  }

  function renumber_lessons()
  {
      global $wpdb;
      $dbTemplate = db_lessontemplates;
      $dbLesson = db_lessons;

      foreach ($wpdb->get_results("SELECT ID FROM `$dbTemplate`") as $tplKey => $tpl) {
          for ($wd = 0; $wd < 8; $wd++) {
              $cnt = 1;
              $lsns = array();
              foreach ($wpdb->get_results($wpdb->prepare("SELECT ID, TEMPLATE, WEEKDAY, `START` FROM `$dbLesson` WHERE TEMPLATE = %d AND WEEKDAY = %d ORDER BY `START` ASC", $tpl->ID, $wd)) as $lsnKey => $lsn) {
                  $wpdb->update(db_lessons, array('NUM' => $cnt), array('ID' => $lsn->ID), array('%d'), array('%d'));
                  $cnt++;
              }
          }
      }
  }




  function handle_admin_lessons_delete_post()
  {
      global $wpdb;
      $dbTemplate = db_lessontemplates;
      $dbLesson = db_lessons;

      if (!isset($_POST['id'])) {
          status_header(400);
          exit("Invalid request: missing parameter(s)!");
      }
      if (!is_numeric($_POST['id'])) {
          status_header(400);
          exit("Invalid request: invalid parameter(s) type(s)!");
      }

      $goneObj = $wpdb->get_row($wpdb->prepare("SELECT `$dbLesson`.*, `$dbTemplate`.TITLE FROM `$dbLesson` INNER JOIN `$dbTemplate` ON `$dbLesson`.`TEMPLATE` = `$dbTemplate`.`ID` WHERE `$dbLesson`.ID = %d", $_POST['id']));
      $st = explode(":", $goneObj->START);
      $en = explode(":", $goneObj->END);
      if ($goneObj == null) {
          status_header(400);
          exit("Invalid request: id not found!");
      }

      if ($wpdb->delete($dbLesson, array( 'ID' => $_POST['id']), array('%d')) !== false) {
          renumber_lessons();
          wp_redirect(add_query_arg(array(
              'action' => 'lessons',
              'msg' => urlencode($goneObj->TITLE) . ' am ' . weekday_names[$goneObj->WEEKDAY] . ' von ' . $st[0] . "-" . $st[1] . ' bis ' . $en[0] . "-" . $en[1] . ' Uhr wurde gelöscht',
              'msgcol' => 'green',
          ), admin_url('admin.php?page=nb-options-lessons')));
      } else {
          wp_redirect(add_query_arg(array(
            'action' => 'lessons',
            'msg' => urlencode($goneObj->TITLE) . ' am ' . weekday_names[$goneObj->WEEKDAY] . ' von ' . $st[0] . "." . $st[1] . ' bis ' . $en[0] . "." . $en[1] . ' Uhr konnte nicht gelöscht werden',
            'msgcol' => 'red',
          ), admin_url('admin.php?page=nb-options-lessons')));
      }
      exit;
  }








  function handle_admin_lessons_add_post()
  {
      global $wpdb;
      $dbtemplate = db_ferientemplates;
      $dbtermin = db_ferientermine;
      $success = true;

      if (!isset($_POST['template']) or !isset($_POST['dates'])) {
          status_header(400);
          exit("Invalid request: missing parameter(s)");
      }
      if (!is_numeric($_POST['template'])) {
          status_header(400);
          exit("Invalid request: invalid parameter (template NaN)");
      }
      $template = $wpdb->get_row($wpdb->prepare("SELECT TITLE, SHORTHAND FROM " . db_lessontemplates . " WHERE ID = %d", $_POST['template']));
      if ($template == null) {
          status_header(400);
          exit("Invalid request: invalid parameter (template null)");
      }

      $eventNr = 0;
      foreach ($_POST['dates'] as $event) {
          if (!isset($event['weekday']) or !isset($event['start']) or !isset($event['end'])) {
              status_header(400);
              exit("Invalid request: missing paramters on event $eventNr");
          }

          $startDate = DateTime::createFromFormat('H:i', $event['start']);
          $endDate = DateTime::createFromFormat('H:i', $event['end']);

          //TODO: Create/Update when numerating lessons
          foreach ($event['weekday'] as $singleDay) {
            //Find free shortcode
              $short_root = $template->SHORTHAND . weekday_names_shortest[$singleDay]; //Shortcode format: [TPL_SHORT][WEEKDAY][1,2,3…]
              $lastShort = $wpdb->get_var($wpdb->prepare("SELECT SHORTCODE FROM " . db_lessons . " WHERE SHORTCODE LIKE %s ORDER BY SHORTCODE DESC LIMIT 1", $short_root . "%"));
              if ($lastShort == null) {
                  $nextShort = $short_root . "1";
              } else {
                  if (preg_match('/(?<=[a-z])\d+(?!.*[a-z])/', $lastShort, $matches)) {
                      $nextShort = $short_root . (++$matches[0]);
                  } else {
                      $nextShort = $short_root . "1";
                  }
              }

              if ($wpdb->insert(db_lessons, array('TEMPLATE' => $_POST['template'], 'SHORTCODE' => $nextShort, 'START' => $event['start'], 'END' => $event['end'], 'WEEKDAY' => $singleDay, 'MAX_PARTICIPANTS' => intval($_POST['max-participants']), 'PARTICIPANTS' => 0, 'IS_CANCELLED' => 0)) !== false) {
                  $eventNr = $eventNr + 1;
              } else {
                  $success = false;
              }
          }
      }

      renumber_lessons();

      wp_redirect(add_query_arg(array(
        'action' => 'lessons',
        'msg' => urlencode($success ? "Es wurden erfolgreich $eventNr " . $template->TITLE . " erstellt!" : "Mindestens eine Unterrichtsstunde konnte nicht erstellt werden. Es wurden jedoch $eventNr " . $template->TITLE . " erfolgreich erstellt."),
        'msgcol' => $success ? 'green' : 'red',
      ), admin_url('admin.php?page=nb-options-lessons')));
      exit;
  }
