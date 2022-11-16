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
	$pdf=new exFPDF('P', 'mm', 'A4');
    //$pdf->AddFont('lato','','assets/lib/font/Lato-Regular.php');
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 26);

    $pdf->Write(10, 'Herbstferien 2022');
    $pdf->SetFont('helvetica', '', 16);
    $pdf->Ln();

    foreach ($wpdb->get_results("SELECT `$termin`.*, `$template`.TITLE FROM `$termin` INNER JOIN `$template` ON `$termin`.`TEMPLATE` = `$template`.`ID` WHERE `$termin`.`DATESTART` >= CURDATE() ORDER BY `$termin`.`DATESTART`") as $row) {
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

	$gca = new GoogleCalenderAdapter();

    foreach ($wpdb->get_results("SELECT `$termin`.*, `$template`.TITLE FROM `$termin` INNER JOIN `$template` ON `$termin`.`TEMPLATE` = `$template`.`ID` WHERE `$termin`.`DATESTART` >= CURDATE() ORDER BY `$termin`.`DATESTART`") as $row) {
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

/**
 * @throws Exception
 */
function handle_admin_ferien_import_post()
{
    //TODO: nur aktuelles Jahr?
    global $wpdb;
    if (!isset($_POST['laender'])) {
        echo "
<html lang=\"de\">
	<head>
		<title>nuBook Ferienimport</title>
		<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    </head>
    <body>
    	<h1 style='text-align: center;'>Ferienimport</h1>
    	<p>Es wurden keine Bundesländer ausgewählt!</p>
        <br>
        <h2><a href=\"" . admin_url('admin.php?page=nb-options-menu&action=ferien') . "\">Zurück zur Verwaltung</a></h2>
    </body>
</html>";
        die();
    }
    $bundeslaender = $_POST['laender'];
    $jahre = $_POST['jahre'] ?? array( "" );
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
                $blFerien[] = $nFerie;
            }
        }
    }

    $skipped = array();
    echo "
<html>
	<head>
		<title>nuBook Ferienimport</title>
		<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    	<style>.ok { color: green; } .skip { color: orange; } .err { color: red; }</style>
    </head>
    <body>
    	<h1 style='text-align: center;'>Ferienimport</h1>
    	<p>Importiert wurden Ferien für " . implode(", ", $bundeslaender) . (isset($_GET['jahre']) ? " für die Jahre " . implode(", ", $jahre) : "") . "</p>
    	<p>Folgende Ferien wurden importiert:</p><ul>";
    foreach ($blFerien as $ferie) {
        if ($wpdb->get_row($wpdb->prepare("SELECT * FROM " . db_ferien . " WHERE STARTDATE = %s AND ENDDATE = %s", $ferie->start->format("Y-m-d"), $ferie->end->format("Y-m-d"))) != null) {
            $skipped[] = sprintf( "<li class=\"skip\">%s %d %s (%s - %s) &rarr; existiert schon</li>", ucfirst( $ferie->label ), $ferie->year, $ferie->stateCode, $ferie->start->format( "d.m." ), $ferie->end->format( "d.m.Y" ) );
            continue;
        }

        $dbData = array( 'LABEL' => strip_tags(sprintf("%s %d %s", ucfirst($ferie->label), $ferie->year, $ferie->stateCode)), 'STARTDATE' => $ferie->start->format("Y-m-d"), 'ENDDATE' => $ferie->end->format("Y-m-d"));
        $dbType = array('%s', '%s', '%s');
        if ($wpdb->insert(db_ferien, $dbData, $dbType) !== false) {
            echo sprintf("<li class=\"ok\">%s %d %s (%s - %s)</li>", ucfirst($ferie->label), $ferie->year, $ferie->stateCode, $ferie->start->format("d.m."), $ferie->end->format("d.m.Y"));
        } else {
            $skipped[] = sprintf( "<li class=\"err\">%s %d %s (%s - %s) &rarr; Datenbankfehler</li>", ucfirst( $ferie->label ), $ferie->year, $ferie->stateCode, $ferie->start->format( "d.m." ), $ferie->end->format( "d.m.Y" ) );
        }
    }
    echo "</ul><br><p>Folgende wurden übersprungen:</p><ul>";
    foreach ($skipped as $skp) {
        echo $skp;
    }
    echo "</ul><br><h2><a href=\"" . admin_url('admin.php?page=nb-options-menu&action=ferien') . "\">Zurück zur Verwaltung</a></body></html>";
}


/* Ferien-Management */
function handle_admin_ferien_list()
{
    global $wpdb;
    wp_localize_script('nb-ferien-js', 'WPURL', array('feactive' => admin_url('admin-post.php?action=nb_fe_active'), 'festandard' => admin_url('admin-post.php?action=nb_fe_standard'), 'fedelete' => admin_url('admin-post.php?action=nb_fe_delete')));
    wp_enqueue_script('nb-ferien-js');
    wp_enqueue_style("nb-flist-css");
    include __DIR__ . "/views/ferien_list.php";
}

function handle_admin_ferien_active()
{
    global $wpdb;
    if (!is_numeric($_POST['id']) || !is_numeric($_POST['val'])) {
        status_header(400);
        exit("Invalid request: invalid parameter(s) datatype(s)");
    }
    if ($wpdb->update(db_ferien, array('ACTIVE' => intval($_POST['val'])), array('FID' => $_POST['id']), array('%d'), array('%d')) !== false) {
        status_header(200);
        exit("OK");
    } else {
        status_header(500);
        exit("FAIL");
    }
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
        ), admin_url('admin.php?page=nb-options-menu')));
    } else {
        wp_redirect(add_query_arg(array(
          'action' => 'ferien',
          'msg' => urlencode($goneObj->LABEL) . '-Ferien konnten nicht gelöscht werden',
          'msgcol' => 'red',
        ), admin_url('admin.php?page=nb-options-menu')));
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
            ), admin_url('admin.php?page=nb-options-menu')));
        } else {
            wp_redirect(add_query_arg(array(
              'action' => 'fktemplates',
              'msg' => urlencode("Die Ferien \""  . strip_tags($_POST['title']) . "\" konnten nicht bearbeitet werden (Datenbankfehler)!"),
              'msgcol' => 'red',
            ), admin_url('admin.php?page=nb-options-menu')));
        }
    } else {
        if ($wpdb->insert(db_ferien, $dbData, $dbType) !== false) {
            wp_redirect(add_query_arg(array(
              'action' => 'ferien',
              'msg' => urlencode("Die Ferien \""  . strip_tags($_POST['title']) . "\" #$wpdb->insert_id wurden erstellt!"),
              'msgcol' => 'green',
            ), admin_url('admin.php?page=nb-options-menu')));
        } else {
            wp_redirect(add_query_arg(array(
              'action' => 'ferien',
              'msg' => urlencode("Die Ferien \""  . strip_tags($_POST['title']) . "\" konnten nicht erstellt werden (Datenbankfehler)!"),
              'msgcol' => 'red',
            ), admin_url('admin.php?page=nb-options-menu')));
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
            echo "<div class=\"nb-manage-controls mcok\"><p>Die Ferien \""  . strip_tags($_POST['title']) . "\" #", intval($_POST['id']), " wurden bearbeitet!</p></div><br>";
        } else {
            echo "<div class=\"nb-manage-controls mcerr\"><p>Fehler: Die Ferien \""  . strip_tags($_POST['title']) . "\" konnten nicht bearbeitet werden (Datenbankfehler)!</p></div><br>";
            return handle_admin_ferientemplate_edit();
        }
    } else {
        if ($wpdb->insert(db_ferien, $dbData, $dbType) !== false) {
            echo "<div class=\"nb-manage-controls mcok\"><p>Die Ferien \""  . strip_tags($_POST['title']) . "\" #$wpdb->insert_id wurden erstellt - <a href=\"?page=nb-options-menu&action=ferien\">zur Übersicht</a></p></div><br>";
        } else {
            echo "<div class=\"nb-manage-controls mcerr\"><p>Fehler: Die Ferien \""  . strip_tags($_POST['title']) . "\" konnten nicht erstellt werden (Datenbankfehler)!</p></div><br>";
            return;
        }
    }
    echo "<script>updateUrl('ferien-edit', 'ferien');</script>";
    return handle_admin_ferien_list();
}
