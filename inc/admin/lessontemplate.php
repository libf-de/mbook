<?php
  function handle_admin_lessontemplate_list() {
    global $wpdb;
    wp_localize_script('mb-ltlist-js', 'WPURL', array('ltdelete' => admin_url( 'admin-post.php?action=mb_lt_delete' )));
    wp_enqueue_script('mb-ltlist-js');
    //wp_localize_script('mbfkjs', 'WPURL', array('ftdelete' => admin_url( 'admin-post.php?action=mb_ft_delete' )));
    //wp_enqueue_script('mbfkjs');
    include __DIR__ . "/views/lessontemplate_list.php";
  }
  
  function handle_admin_lessontemplate_add() {
    $template = new StdClass();
    $template->TYP = 0;
    include __DIR__ . "/views/lessontemplate_modify.php";
  }

  /* handle_admin_lessontemplate_edit($id)
   * Edits the template with given id
   * ID TITLE TYP SHORTHAND DESCRIPTION LINKURL DEFAULT_DURATION DEFAULT_MAX_PARTICIPANTS EXP_LEVEL_MIN EXP_LEVEL_MAX
   */
  function handle_admin_lessontemplate_edit($id) {
    global $wpdb;
    if(!is_numeric($id)) {
      echo "ERROR: Invalid id (non-numeric)!";
      return;
    }
    $template = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . db_lessontemplates . " WHERE ID = %d", $id));
    if($template == null) {
      echo "ERROR: Invalid id (not found)";
      return;
    }
    list($durationDays, $durationHours, $durationMins, $isOpenEnd) = mins_to_duration($template->DEFAULT_DURATION);

    include __DIR__ . "/views/lessontemplate_modify.php";
  }
  
  //TODO: Verify parameters!
  function handle_admin_lessontemplate_modify_post() {
    global $wpdb;
    if(!is_numeric($_POST['duration-days']) or !is_numeric($_POST['duration-hours']) or !is_numeric($_POST['duration-mins'])) {
      status_header(400);
      exit("Invalid request: Invalid form data (duration NaN)");
    }
    if(!is_numeric($_POST['typ'])) { exit("Invalid request: Invalid form data (typ NaN)"); }
    if(!is_numeric($_POST['minExp']) or !is_numeric($_POST['maxExp'])) { exit("Invalid request: Invalid form data (experience NaN)"); }

    $durationInt = duration_to_mins($_POST['duration-days'], $_POST['duration-hours'], $_POST['duration-mins']);
    //ID TITLE TYP SHORTHAND DESCRIPTION LINKURL DEFAULT_DURATION DEFAULT_MAX_PARTICIPANTS EXP_LEVEL_MIN EXP_LEVEL_MAX
    //%d %s %d %s %s %s %d %d %d %d
    $dbData = array( 'TITLE' => strip_tags($_POST['title']), 'TYP' => intval($_POST["typ"]), 'SHORTHAND' => strip_tags($_POST['shorthand']), 'DESCRIPTION' => preg_replace("/\r\n|\r|\n/",'<br/>', strip_tags($_POST['description'])), 'LINKURL' => strip_tags($_POST['linkurl']), 'DEFAULT_DURATION' => $durationInt, 'DEFAULT_MAX_PARTICIPANTS' => intval($_POST['maxparts']), 'EXP_LEVEL_MIN' => intval($_POST['minExp']), 'EXP_LEVEL_MAX' => intval($_POST['maxExp']));
    $dbType = array('%s', '%d', '%s', '%s', '%s', '%d', '%d', '%d', '%d');
    if(isset($_POST['id'])) {
      if($wpdb->update(db_lessontemplates, $dbData, array('ID' => $_POST['id']), $dbType, array('%d')) !== FALSE) {
        wp_redirect( add_query_arg(array(
          'action' => 'lstemplates',
          'msg' => urlencode("Die Unterrichts-Vorlage \""  . strip_tags($_POST['title']) . "\" #" . intval($_POST['id']) . " wurde bearbeitet!"),
          'msgcol' => 'green',
        ), admin_url( 'admin.php?page=mb-options-lessons') ) );
      } else {
        wp_redirect( add_query_arg(array(
          'action' => 'lstemplates',
          'msg' => urlencode("Fehler: Die Unterrichts-Vorlage \""  . strip_tags($_POST['title']) . "\" #", intval($_POST['id']) . " konnte nicht bearbeitet werden (Datenbankfehler)!"),
          'msgcol' => 'red',
        ), admin_url( 'admin.php?page=mb-options-lessons') ) );
      }
    } else {
      if($wpdb->insert(db_lessontemplates, $dbData, $dbType) !== FALSE) {
        wp_redirect( add_query_arg(array(
          'action' => 'lstemplates',
          'msg' => urlencode("Die Unterrichts-Vorlage \""  . strip_tags($_POST['title']) . "\" #$wpdb->insert_id wurde erstellt!"),
          'msgcol' => 'green',
        ), admin_url( 'admin.php?page=mb-options-lessons') ) );
      } else {
        wp_redirect( add_query_arg(array(
          'action' => 'lstemplates',
          'msg' => urlencode("Fehler: Die Unterrichts-Vorlage \""  . strip_tags($_POST['title']) . "\" #", intval($_POST['id']) . " konnte nicht erstellt werden (Datenbankfehler)!"),
          'msgcol' => 'red',
        ), admin_url( 'admin.php?page=mb-options-lessons') ) );
      }
    }
    exit;
  }

  function handle_admin_lessontemplate_delete_post() {
    global $wpdb;
    if(!isset($_POST['id'])) {
      status_header(400);
      exit("Invalid request: missing parameter(s)!");
    }
    if(!is_numeric($_POST['id'])) {
      status_header(400);
      exit("Invalid request: invalid parameter(s) type(s)!");
    }

    $goneObj = $wpdb->get_row($wpdb->prepare("SELECT ID, TITLE FROM " . db_lessontemplates . " WHERE ID = %d", $_POST['id']));

    if($goneObj == null) {
      status_header(400);
      exit("Invalid request: id not found!");
    }

    if($wpdb->delete(db_lessontemplates, array( 'ID' => $_POST['id']), array('%d')) !== FALSE) {
      wp_redirect( add_query_arg(array(
          'action' => 'lstemplates',
          'msg' => 'Vorlage ' . urlencode($goneObj->TITLE) . ' wurde gelöscht',
          'msgcol' => 'green',
      ), admin_url( 'admin.php?page=mb-options-lessons') ) );
    } else {
      wp_redirect( add_query_arg( array(
        'action' => 'lstemplates',
        'msg' => 'Vorlage ' . urlencode($goneObj->TITLE) . ' konnte nicht gelöscht werden',
        'msgcol' => 'red',
      ), admin_url( 'admin.php?page=mb-options-lessons') ) );
    }
    exit;
  }
?>