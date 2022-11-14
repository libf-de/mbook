<?php
  function handle_admin_ferientemplate_list() {
    global $wpdb;
    wp_localize_script('nbfkjs', 'WPURL', array('ftdelete' => admin_url( 'admin-post.php?action=nb_ft_delete' )));
    wp_enqueue_script('nbfkjs');
    include __DIR__ . "/views/ferientemplate_list.php";
  }
  
  function handle_admin_ferientemplate_add() {
    include __DIR__ . "/views/ferientemplate_modify.php";
  }

  /* handle_admin_ferientemplate_edit($id)
   * Edits the template with given id
   */
  function handle_admin_ferientemplate_edit($id) {
    global $wpdb;
    if(!is_numeric($id)) {
      echo "ERROR: Invalid id (non-numeric)!";
      return;
    }
    $template = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . db_ferientemplates . " WHERE ID = %d", $id));
    if($template == null) {
      echo "ERROR: Invalid id (not found)";
      return;
    }
    list($durationDays, $durationHours, $durationMins, $isOpenEnd) = mins_to_duration($template->DEFAULT_DURATION);

    include __DIR__ . "/views/ferientemplate_modify.php";
  }

  //DEPRECATED!
  
  
  //TODO: Verify parameters!
  function handle_admin_ferientemplate_modify_post() {
    global $wpdb;
    if(!isset($_POST['openEnd'])) {
      if(!is_numeric($_POST['duration-days']) or !is_numeric($_POST['duration-hours']) or !is_numeric($_POST['duration-mins'])) {
        status_header(400);
        exit("Invalid request: Invalid form data (duration NaN)");
      }
    }
    if(!is_numeric($_POST['weekday'])) { exit("Invalid request: Invalid form data (weekday NaN)"); }
    if(!is_numeric($_POST['minExp']) or !is_numeric($_POST['maxExp'])) { exit("Invalid request: Invalid form data (experience NaN)"); }

    $durationInt = isset($_POST['openEnd']) ? -1 : duration_to_mins($_POST['duration-days'], $_POST['duration-hours'], $_POST['duration-mins']);
    $dbData = array( 'TITLE' => strip_tags($_POST['title']), 'LINKURL' => strip_tags($_POST['linkurl']), 'SHORTHAND' => strip_tags($_POST['shorthand']), 'DESCRIPTION' => preg_replace("/\r\n|\r|\n/",'<br/>', strip_tags($_POST['description'])), 'DEFAULT_DURATION' => $durationInt, 'DEFAULT_STARTTIME' => hh_mm_to_mins($_POST['startTime']), 'DEFAULT_WEEKDAY' => intval($_POST['weekday']), 'DEFAULT_MAX_PARTICIPANTS' => intval($_POST['maxparts']), 'EXP_LEVEL_MIN' => intval($_POST['minExp']), 'EXP_LEVEL_MAX' => intval($_POST['maxExp']));
    $dbType = array('%s', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%d');
    if(isset($_POST['id'])) {
      if($wpdb->update(db_ferientemplates, $dbData, array('ID' => $_POST['id']), $dbType, array('%d')) !== FALSE) {
        wp_redirect( add_query_arg(array(
          'action' => 'fktemplates',
          'msg' => urlencode("Die Ferienkurs-Vorlage \""  . strip_tags($_POST['title']) . "\" #" . intval($_POST['id']) . " wurde bearbeitet!"),
          'msgcol' => 'green',
        ), admin_url( 'admin.php?page=nb-options-menu') ) );
      } else {
        wp_redirect( add_query_arg(array(
          'action' => 'fktemplates',
          'msg' => urlencode("Fehler: Die Ferienkurs-Vorlage \""  . strip_tags($_POST['title']) . "\" #", intval($_POST['id']) . " konnte nicht bearbeitet werden (Datenbankfehler)!"),
          'msgcol' => 'red',
        ), admin_url( 'admin.php?page=nb-options-menu') ) );
      }
    } else {
      if($wpdb->insert(db_ferientemplates, $dbData, $dbType) !== FALSE) {
        wp_redirect( add_query_arg(array(
          'action' => 'fktemplates',
          'msg' => urlencode("Die Ferienkurs-Vorlage \""  . strip_tags($_POST['title']) . "\" #$wpdb->insert_id wurde erstellt!"),
          'msgcol' => 'green',
        ), admin_url( 'admin.php?page=nb-options-menu') ) );
      } else {
        wp_redirect( add_query_arg(array(
          'action' => 'fktemplates',
          'msg' => urlencode("Fehler: Die Ferienkurs-Vorlage \""  . strip_tags($_POST['title']) . "\" #", intval($_POST['id']) . " konnte nicht erstellt werden (Datenbankfehler)!"),
          'msgcol' => 'red',
        ), admin_url( 'admin.php?page=nb-options-menu') ) );
      }
    }
    exit;
  }

  function handle_admin_ferientemplate_delete_post() {
    global $wpdb;
    if(!isset($_POST['id'])) {
      status_header(400);
      exit("Invalid request: missing parameter(s)!");
    }
    if(!is_numeric($_POST['id'])) {
      status_header(400);
      exit("Invalid request: invalid parameter(s) type(s)!");
    }

    $goneObj = $wpdb->get_row($wpdb->prepare("SELECT ID, TITLE FROM " . db_ferientemplates . " WHERE ID = %d", $_POST['id']));

    if($goneObj == null) {
      status_header(400);
      exit("Invalid request: id not found!");
    }

    if($wpdb->delete(db_ferientemplates, array( 'ID' => $_POST['id']), array('%d')) !== FALSE) {
      wp_redirect( add_query_arg(array(
          'action' => 'fktemplates',
          'msg' => 'Vorlage ' . urlencode($goneObj->TITLE) . ' wurde gelöscht',
          'msgcol' => 'green',
      ), admin_url( 'admin.php?page=nb-options-menu') ) );
    } else {
      wp_redirect( add_query_arg( array(
        'action' => 'fktemplates',
        'msg' => 'Vorlage ' . urlencode($goneObj->TITLE) . ' konnte nicht gelöscht werden',
        'msgcol' => 'red',
      ), admin_url( 'admin.php?page=nb-options-menu') ) );
    }
    exit;
  }
?>