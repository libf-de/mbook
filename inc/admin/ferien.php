<?php

/**
 * Returns the ID of default Ferien (or "default"-Ferien if none set)
 * @return int
 */
function get_standard_ferien(): int
{
    $savedVal = get_option('standard_ferien');
    return is_numeric($savedVal) ? $savedVal : 1;
}

function get_ferien_title(): string
{
    global $wpdb;
    $ferien = db_ferien;

    $displayMode = 0;
    if ($displayMode == 1) {
        $activeFerien = $wpdb->get_results("SELECT * FROM `$ferien` WHERE ACTIVE = 1 AND ENDDATE >= CURDATE();", 'ARRAY_A');
    } elseif ($displayMode == 2) {
        $activeFerien = $wpdb->get_results("SELECT * FROM `$ferien` WHERE ACTIVE = 1 AND ENDDATE >= CURDATE() AND STARTDATE <= CURDATE();", 'ARRAY_A');
    } else {
        $activeFerien = $wpdb->get_results("SELECT * FROM `$ferien` WHERE ACTIVE = 1;", 'ARRAY_A');
    }

    if (empty($activeFerien)) {
        return "Keine aktiven Ferien!";
    }

    $activeFerienTitles = array_column($activeFerien, 'LABEL');
    natcasesort($activeFerienTitles);
    $ftitle = array_pop($activeFerienTitles);
    foreach (array_reverse($activeFerienTitles) as $ferie) {
        if ((stripos($ferie, 'ferien') + 6) - strlen($ferie) == 0) {
            $ftitle = str_ireplace("ferien", "-, ", $ferie) . $ftitle;
        } else {
            $ftitle = $ferie . ", " . $ftitle;
        }
    }

    return $ftitle;
}

/*
 * TODO:
 * Consider saving the PDF instead of outputting it?
 * Display date and time + code?
 * Display correct ferien title
 * Better format?
 */
/**
 * Generates a PDF for Ferienprogramm to register participants
 * -- (admin_post_print)
 * @return void ok
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
    $pdf->Write(10, get_ferien_title());

    $pdf->SetFont('helvetica', '', 16);
    $pdf->Ln();

    foreach ($wpdb->get_results("SELECT `$termin`.*, `$template`.TITLE FROM `$termin` INNER JOIN `$template` ON `$termin`.`TEMPLATE` = `$template`.`ID` WHERE `$termin`.`DATESTART` >= CURDATE() ORDER BY `$termin`.`DATESTART`") as $row) {
        printTable($pdf, $row->MAX_PARTICIPANTS, $row->TITLE, $row->SHORTCODE);
    }

    //printTable($pdf, 10, "Wanderritt");
    $pdf->Output();
}

/**
 * @return void
 * @deprecated
 */
function handle_admin_ferien_delete()
{
    global $plugin_root;
    require_once($plugin_root . 'inc/calendar/caltest.php');
}


/**
 * Exports the Ferienkurse to Google Calendar
 * @deprecated
 * @return void
 */
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

/**
 * Displays the Import Ferien page
 * -- (?page=nb-options-menu&action=ferien-imp)
 * @return void ok
 */
function handle_admin_ferien_import()
{
    nb_load_fa();
    include __DIR__ . "/views/ferien_import.php";
}

/**
 * Performs the Ferien import
 * $_POST['laender']: (array[str]) bundeslander to import
 * $_POST['jahre']: (array[int]) years to import
 * -- (admin_post_nb_fe_import)
 * @throws Exception
 * @return void ok
 */
function handle_admin_ferien_import_post()
{
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
    $skipped = array();
    $curYear = date('Y');


    foreach ($jahre as $jahr) {
        if(!is_numeric($jahr)) {
            $skipped[] = sprintf( "<li class=\"err\">Jahr \"%s\" &rarr; Keine Jahreszahl!</li>", $jahr );
            continue;
        }
        if($jahr < $curYear) {
            $skipped[] = sprintf( "<li class=\"err\">Jahr \"%s\" &rarr; liegt in Vergangenheit!</li>", $jahr );
            continue;
        }

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

/**
 * Displays the Ferien list
 * -- (?page=nb-options-menu&action=ferien)
 * @return void ok
 */
function handle_admin_ferien_list()
{
    global $wpdb;
    nb_load_fa();
    wp_localize_script('nb-ferien-js', 'WPURL', array('feactive' => admin_url('admin-post.php?action=nb_fe_active'), 'festandard' => admin_url('admin-post.php?action=nb_fe_standard'), 'fedelete' => admin_url('admin-post.php?action=nb_fe_delete')));
    wp_enqueue_script('nb-ferien-js');
    wp_enqueue_style("nb-flist-css");
    include __DIR__ . "/views/ferien_list.php";
}

/**
 * Toggles visibility of given Ferien on frontend
 * $_POST['id']: (int) id of Ferien to toggle
 * $_POST['val']: (int) 0/1 - visibility
 * -- (admin_post_nb_fe_active)
 * @return void ok/invalid request
 */
function handle_admin_ferien_active_post()
{
    global $wpdb;
    if (!is_numeric($_POST['id']) || !is_numeric($_POST['val'])) {
        status_header(400);
        exit("Invalid request: invalid parameter(s) datatype(s)");
    }
    if ($wpdb->get_row($wpdb->prepare("SELECT FID FROM " . db_ferien . " WHERE FID = %d", $_POST['id'])) == null ) {
        status_header(400);
        exit("Invalid request: invalid ferien specified");
    }
    if ($wpdb->update(db_ferien, array('ACTIVE' => intval($_POST['val'])), array('FID' => $_POST['id']), array('%d'), array('%d')) !== false) {
        status_header(200);
        exit("OK");
    } else {
        status_header(500);
        exit("FAIL");
    }
}

/**
 * Sets the default selected Ferien in admin interface
 * $_POST['id']: (int) id to set
 * -- (admin_post_nb_fe_standard)
 * @return void ok/invalid request
 */
function handle_admin_ferien_standard_post()
{
    global $wpdb;
    if (!is_numeric($_POST['id'])) {
        status_header(400);
        exit("Invalid request: invalid parameter(s) datatype(s)");
    }
    if ($wpdb->get_row($wpdb->prepare("SELECT FID FROM " . db_ferien . " WHERE FID = %d", $_POST['id'])) == null ) {
        status_header(400);
        exit("Invalid request: invalid ferien specified");
    }
    update_option('standard_ferien', $_POST['id']);
    status_header(200);
    exit("OK");
}

/**
 * Deletes the given Ferien (+ resets default Ferien, and deletes calendar event if necessary)
 * $_POST['id']: (int) id of Ferien to delete
 * -- (admin_post_nb_fe_delete)
 * @return void redirect/invalid request
 */
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

/**
 * Displays the Ferien edit/creation page
 * @param $id int|null id of Ferien to edit or null to add
 * -- (?page=nb-options-menu&action=ferien-modify[&id=xxxx])
 * @return void ok
 */
function handle_admin_ferien_edit($id)
{
    global $wpdb;
    nb_load_fa();
    if ($id != null) {
        if (!is_numeric($id)) {
            echo "ERROR: Invalid id (non-numeric)!";
            return;
        }
        $template = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . db_ferien . " WHERE FID = %d", $id));
        if ($template == null) {
            echo "ERROR: Invalid id (not found)";
            return;
        }
    }
    include __DIR__ . "/views/ferien_modify.php";
}

/**
 * Modifies the given Ferien with given data
 * $_POST['id']: (int) id of ferien to edit
 * $_POST['title']: (str) title
 * $_POST['startDate']: (str:"Y-m-d") start date
 * $_POST['endDate']: (str:"Y-m-d") end date
 * -- (admin_post_nb_fe_modify)
 * @return void redirect/invalid request
 */
function handle_admin_ferien_modify_post()
{
    global $wpdb;
    if (!isset($_POST['id']) or !isset($_POST['startDate']) or !isset($_POST['endDate']) or !isset($_POST['title'])) {
        status_header(400);
        exit("Invalid request: Missing parameter(s)!");
    }

    if (!is_numeric($_POST['id'])) {
        status_header(400);
        exit("Invalid request: Parameter 'id' must be numeric!");
    }

    if (!DateTime::createFromFormat('Y-m-d', $_POST['startDate']) or !DateTime::createFromFormat('Y-m-d', $_POST['endDate'])) {
        status_header(400);
        exit("Invalid request: Parameters 'startDate' and 'endDate' must be in YYYY-MM-DD format!");
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