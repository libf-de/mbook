<?php

function get_standard_ferien()
{
    $savedVal = get_option('standard_ferien');
    return is_numeric($savedVal) ? $savedVal : 1;
}


/*
 * TODO:
 * Consider saving the PDF instead of outputting it?
 * Display date and time + code?
 * Display correct ferien title
 * Better format?
 */
function handle_admin_ferien_print()
{
    global $plugin_root;
    require_once($plugin_root . 'inc/print/printout.php');
    global $wpdb;
    $template = db_ferientemplates;
    $termin = db_ferientermine;
    $ferien = db_ferien;
    $pdf=new exFPDF('P', 'mm', 'A4');
    //$pdf->AddFont('lato','','assets/lib/font/Lato-Regular.php');
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 26);

    $pdf->Write(10, 'Herbstferien 2022');
    $pdf->SetFont('helvetica', '', 16);
    $pdf->Ln();

    foreach ($wpdb->get_results("SELECT `$termin`.*, `$template`.TITLE FROM `$termin` INNER JOIN `$template` ON `$termin`.`TEMPLATE` = `$template`.`ID` WHERE `$termin`.`DATESTART` >= CURDATE() ORDER BY `$termin`.`DATESTART`") as $key => $row) {
        printTable($pdf, $row->MAX_PARTICIPANTS, $row->TITLE);
    }

    //printTable($pdf, 10, "Wanderritt");
    $pdf->Output();
}

function handle_admin_ferien_delete()
{
    global $plugin_root;
    require_once($plugin_root . 'inc/calendar/caltest.php');
}


function handle_admin_ferien_export()
{
    global $plugin_root;
    require_once($plugin_root . 'inc/calendar/caltest.php');
    global $wpdb;
    $template = db_ferientemplates;
    $termin = db_ferientermine;
    $ferien = db_ferien;

    $gca = new GoogleCalenderAdapter();

    foreach ($wpdb->get_results("SELECT `$termin`.*, `$template`.TITLE FROM `$termin` INNER JOIN `$template` ON `$termin`.`TEMPLATE` = `$template`.`ID` WHERE `$termin`.`DATESTART` >= CURDATE() ORDER BY `$termin`.`DATESTART`") as $key => $row) {
        $eventId = $gca->update_calendar_event($row);
        if ($eventId != null) {
            if ($wpdb->update(db_ferientermine, array('CALENDAR_EVENT_ID' => $eventId), array('ID' => $row->ID), array('%s'), array('%d')) !== false) {
                echo "Created event with id: $eventId<br>";
            } else {
                echo "Created event with id: $eventId, but could not save to database<br>";
            }
        } else {
            echo "Event failed!<br>";
        }
    }
}

function handle_admin_ferien_import()
{
    include __DIR__ . "/views/ferien_import.php";
}

function handle_admin_ferien_import_post()
{
    //TODO: nur aktuelles Jahr?
    global $wpdb;
    if (!isset($_POST['laender'])) {
        echo "<html><head><title>nuBook Ferienimport</title><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">";
        echo "</head><body><center><h1>Ferienimport</h1></center><p>Es wurden keine Bundesländer ausgewählt!</p>";
        echo "<br><h2><a href=\"" . admin_url('admin.php?page=mb-options-menu&action=ferien') . "\">Zurück zur Verwaltung</a></body></html>";
    }
    $bundeslaender = $_POST['laender'];
    $jahre = isset($_POST['jahre']) ? $_POST['jahre'] : array("");
    $blFerien = array();

    foreach ($jahre as $jahr) {
        foreach ($bundeslaender as $bundesland) {
            $ferien = file_get_contents("https://ferien-api.de/api/v1/holidays/" . $bundesland . "/" . $jahr);
            $fJson = array_filter(json_decode($ferien), function ($v) {
                $v->label = explode(" ", $v->name)[0];
                $v->end = new DateTime($v->end);
                $v->start = new DateTime($v->start);
                return $v->end > new DateTime();
            });

            foreach ($fJson as $nFerie) {
                if (str_contains($nFerie->name, "beweglicher")) {
                    continue;
                }
                foreach ($blFerien as $oFerie) {
                    //check for overlap
                    //der spätere beginn muss kleiner sein als das fühere ende
                    if ((max($oFerie->start, $nFerie->start) < min($oFerie->end, $nFerie->end))) {
                        if ($oFerie->label == $nFerie->label) {
                            $oFerie->start = min($oFerie->start, $nFerie->start);
                            $oFerie->end = max($oFerie->end, $nFerie->end);
                            $oFerie->stateCode .= "/" . $nFerie->stateCode;
                            continue 2;
                        }
                    }
                }
                array_push($blFerien, $nFerie);
            }
        }
    }

    $skipped = array();
    echo "<html><head><title>nuBook Ferienimport</title><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">";
    echo "<style>.ok { color: green; } .skip { color: orange; } .err { color: red; }</style>";
    echo "</head><body><center><h1>Ferienimport</h1></center>";
    echo "<p>Importiert wurden Ferien für " . implode(", ", $bundeslaender) . (isset($_GET['jahre']) ? " für die Jahre " . implode(", ", $jahre) : "") . "</p>";
    echo "<p>Folgende Ferien wurden importiert:</p><ul>";
    foreach ($blFerien as $ferie) {
        if ($wpdb->get_row($wpdb->prepare("SELECT * FROM " . db_ferien . " WHERE STARTDATE = %s AND ENDDATE = %s", $ferie->start->format("Y-m-d"), $ferie->end->format("Y-m-d"))) != null) {
            array_push($skipped, sprintf("<li class=\"skip\">%s %d %s (%s - %s) &rarr; existiert schon</li>", ucfirst($ferie->label), $ferie->year, $ferie->stateCode, $ferie->start->format("d.m."), $ferie->end->format("d.m.Y")));
            continue;
        }

        $dbData = array( 'LABEL' => strip_tags(sprintf("%s %d %s", ucfirst($ferie->label), $ferie->year, $ferie->stateCode)), 'STARTDATE' => $ferie->start->format("Y-m-d"), 'ENDDATE' => $ferie->end->format("Y-m-d"));
        $dbType = array('%s', '%s', '%s');
        if ($wpdb->insert(db_ferien, $dbData, $dbType) !== false) {
            //array_push($imported, );
            //echo sprintf("<div class=\"manage-controls mcok\"><p>Die Ferien #%d \"%s %d %s\" von %s bis %s wurden erstellt</p></div><br>", $wpdb->insert_id, ucfirst($ferie->label), $ferie->year, $ferie->stateCode, $ferie->start->format("d.m.Y"), $ferie->end->format("d.m.Y"));
            echo sprintf("<li class=\"ok\">%s %d %s (%s - %s)</li>", ucfirst($ferie->label), $ferie->year, $ferie->stateCode, $ferie->start->format("d.m."), $ferie->end->format("d.m.Y"));
        } else {
            array_push($skipped, sprintf("<li class=\"err\">%s %d %s (%s - %s) &rarr; Datenbankfehler</li>", ucfirst($ferie->label), $ferie->year, $ferie->stateCode, $ferie->start->format("d.m."), $ferie->end->format("d.m.Y")));
            //echo sprintf("<div class=\"manage-controls mcerr\"><p>Die Ferien \"%s %d %s\" konnten nicht erstellt werden (Datenbankfehler)!</p></div><br>", ucfirst($ferie->label), $ferie->year, $ferie->stateCode);
        }
    }
    echo "</ul><br><p>Folgende wurden übersprungen:</p><ul>";
    foreach ($skipped as $skp) {
        echo $skp;
    }
    echo "</ul><br><h2><a href=\"" . admin_url('admin.php?page=mb-options-menu&action=ferien') . "\">Zurück zur Verwaltung</a></body></html>";
}


/* Ferien-Management */
function handle_admin_ferien_list()
{
    global $wpdb;
    wp_localize_script('mb-ferien-js', 'WPURL', array('festandard' => admin_url('admin-post.php?action=mb_fe_standard'), 'fedelete' => admin_url('admin-post.php?action=mb_fe_delete')));
    wp_enqueue_script('mb-ferien-js');
    include __DIR__ . "/views/ferien_list.php";
}

function handle_admin_ferien_standard()
{
    if (!is_numeric($_POST['id'])) {
        status_header(400);
        exit("Invalid request: invalid parameter(s) datatype(s)");
    }
    update_option('standard_ferien', $_POST['id']);
    status_header(200);
    exit("OK");
}


function handle_admin_ferien_clean() {
    global $wpdb;

    $legacyObjs = $wpdb->get_results("SELECT * FROM " . db_ferien . " WHERE ENDDATE <= CURDATE()");
    
}

function handle_admin_ferien_clean_post()
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

    $goneObj = $wpdb->get_row($wpdb->prepare("SELECT FID, LABEL FROM " . db_ferien . " WHERE FID = %d", $_POST['id']));
    if ($goneObj == null) {
        status_header(400);
        exit("Invalid request: id not found!");
    }

    if ($goneObj->FID == get_option('standard_ferien')) {
        update_option('standard_ferien', 1);
    }

    $goneObjs = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . db_ferientermine . " WHERE FERIEN = %d", $_POST['id']));
    $goneCnt = 0;
    foreach ($goneObjs as $key => $row) {
        if ($row->CALENDAR_EVENT_ID != null) {
            require_once(dirname(__DIR__) . '/calendar/caltest.php');
            $gca = new GoogleCalenderAdapter();
            if ($gca->delete_calendar_event($row)) {
                $goneCnt = $goneCnt + 1;
            }
        }
    }

    if ($wpdb->delete($termin, array( 'ID' => $_POST['id']), array('%d')) !== false) {
        wp_redirect(add_query_arg(array(
            'action' => 'ferien',
            'msg' => urlencode($goneObj->LABEL) . '-Ferien wurden gelöscht',
            'msgcol' => 'green',
        ), admin_url('admin.php?page=mb-options-menu')));
    } else {
        wp_redirect(add_query_arg(array(
          'action' => 'ferien',
          'msg' => urlencode($goneObj->LABEL) . '-Ferien konnten nicht gelöscht werden',
          'msgcol' => 'red',
        ), admin_url('admin.php?page=mb-options-menu')));
    }
    exit;
}


function handle_admin_ferien_delete_post()
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

    $goneObj = $wpdb->get_row($wpdb->prepare("SELECT FID, LABEL FROM " . db_ferien . " WHERE FID = %d", $_POST['id']));
    if ($goneObj == null) {
        status_header(400);
        exit("Invalid request: id not found!");
    }

    if ($goneObj->FID == get_option('standard_ferien')) {
        update_option('standard_ferien', 1);
    }

    $goneObjs = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . db_ferientermine . " WHERE FERIEN = %d", $_POST['id']));
    $goneCnt = 0;
    foreach ($goneObjs as $key => $row) {
        if ($row->CALENDAR_EVENT_ID != null) {
            require_once(dirname(__DIR__) . '/calendar/caltest.php');
            $gca = new GoogleCalenderAdapter();
            if ($gca->delete_calendar_event($row)) {
                $goneCnt = $goneCnt + 1;
            }
        }
    }

    if ($wpdb->delete($termin, array( 'ID' => $_POST['id']), array('%d')) !== false) {
        wp_redirect(add_query_arg(array(
            'action' => 'ferien',
            'msg' => urlencode($goneObj->LABEL) . '-Ferien wurden gelöscht',
            'msgcol' => 'green',
        ), admin_url('admin.php?page=mb-options-menu')));
    } else {
        wp_redirect(add_query_arg(array(
          'action' => 'ferien',
          'msg' => urlencode($goneObj->LABEL) . '-Ferien konnten nicht gelöscht werden',
          'msgcol' => 'red',
        ), admin_url('admin.php?page=mb-options-menu')));
    }
    exit;
}

function handle_admin_ferien_add()
{
    include __DIR__ . "/views/ferien_modify.php";
}

function handle_admin_ferien_edit($id)
{
    global $wpdb;
    if (!is_numeric($id)) {
        echo "ERROR: Invalid id (non-numeric)!";
        return;
    }
    $template = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . db_ferien . " WHERE FID = %d", $id));
    if ($template == null) {
        echo "ERROR: Invalid id (not found)";
        return;
    }
    include __DIR__ . "/views/ferien_modify.php";
}

function handle_admin_ferien_modify_post()
{
    global $wpdb;
    if (!isset($_POST['startDate']) or !isset($_POST['endDate']) or !isset($_POST['title'])) {
        status_header(400);
        exit("Invalid request: Missing parameter(s)!");
    }

    $dbData = array( 'LABEL' => strip_tags($_POST['title']), 'STARTDATE' => strip_tags($_POST['startDate']), 'ENDDATE' => strip_tags($_POST['endDate']));
    $dbType = array('%s', '%s', '%s');
    if (isset($_POST['id'])) {
        if ($wpdb->update(db_ferien, $dbData, array('FID' => $_POST['id']), $dbType, array('%d')) !== false) {
            wp_redirect(add_query_arg(array(
              'action' => 'ferien',
              'msg' => urlencode("Die Ferien \""  . strip_tags($_POST['title']) . "\" #" . intval($_POST['id']) . " wurden bearbeitet!"),
              'msgcol' => 'green',
            ), admin_url('admin.php?page=mb-options-menu')));
        } else {
            wp_redirect(add_query_arg(array(
              'action' => 'fktemplates',
              'msg' => urlencode("Die Ferien \""  . strip_tags($_POST['title']) . "\" konnten nicht bearbeitet werden (Datenbankfehler)!"),
              'msgcol' => 'red',
            ), admin_url('admin.php?page=mb-options-menu')));
        }
    } else {
        if ($wpdb->insert(db_ferien, $dbData, $dbType) !== false) {
            wp_redirect(add_query_arg(array(
              'action' => 'ferien',
              'msg' => urlencode("Die Ferien \""  . strip_tags($_POST['title']) . "\" #$wpdb->insert_id wurden erstellt!"),
              'msgcol' => 'green',
            ), admin_url('admin.php?page=mb-options-menu')));
        } else {
            wp_redirect(add_query_arg(array(
              'action' => 'ferien',
              'msg' => urlencode("Die Ferien \""  . strip_tags($_POST['title']) . "\" konnten nicht erstellt werden (Datenbankfehler)!"),
              'msgcol' => 'red',
            ), admin_url('admin.php?page=mb-options-menu')));
        }
    }
    exit;
}

function handle_admin_ferien_edit_post()
{
    global $wpdb;
    if (!isset($_POST['startDate']) or !isset($_POST['endDate']) or !isset($_POST['title'])) {
        echo "ERROR: Invalid form data (fields missing)";
        return;
    }

    $dbData = array( 'LABEL' => strip_tags($_POST['title']), 'STARTDATE' => strip_tags($_POST['startDate']), 'ENDDATE' => strip_tags($_POST['endDate']));
    $dbType = array('%s', '%s', '%s');
    if (isset($_POST['id'])) {
        if ($wpdb->update(db_ferien, $dbData, array('FID' => $_POST['id']), $dbType, array('%d')) !== false) {
            echo "<div class=\"manage-controls mcok\"><p>Die Ferien \""  . strip_tags($_POST['title']) . "\" #", intval($_POST['id']), " wurden bearbeitet!</p></div><br>";
        } else {
            echo "<div class=\"manage-controls mcerr\"><p>Fehler: Die Ferien \""  . strip_tags($_POST['title']) . "\" konnten nicht bearbeitet werden (Datenbankfehler)!</p></div><br>";
            return handle_admin_ferientemplate_edit();
        }
    } else {
        if ($wpdb->insert(db_ferien, $dbData, $dbType) !== false) {
            echo "<div class=\"manage-controls mcok\"><p>Die Ferien \""  . strip_tags($_POST['title']) . "\" #$wpdb->insert_id wurden erstellt - <a href=\"?page=mb-options-menu&action=ferien\">zur Übersicht</a></p></div><br>";
        } else {
            echo "<div class=\"manage-controls mcerr\"><p>Fehler: Die Ferien \""  . strip_tags($_POST['title']) . "\" konnten nicht erstellt werden (Datenbankfehler)!</p></div><br>";
            return;
        }
    }
    echo "<script>updateUrl('ferien-edit', 'ferien');</script>";
    return handle_admin_ferien_list();
}
