<?php

function mb_options_lessons()
{
    if (!current_user_can('manage_options')) {
        die("boooh!");
        //wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    if (isset($_POST['action'])) {
        die("invalid POST header!");
    }

    $activeClass = isset($_GET['msgcol']) ? "nav-tab-active bg-color-" . preg_replace("/[^A-Za-z0-9#]/", '', urldecode($_GET['msgcol'])) : "nav-tab-active ";

    if (isset($_POST['action'])) {
        $action = "POST_" . $_POST['action'];
    } elseif (isset($_GET['action'])) {
        $action = $_GET['action'];
    } else {
        $action = "lessons";
    }

    echo "<div class=\"wrap\"><h2>Unterricht verwalten &ndash; nuBook</h2><h2 class=\"nav-tab-wrapper\">";
    echo "<a href=\"?page=mb-options-lessons&action=lessons\" class=\"nav-tab " . ($action == 'lessons' ? $activeClass : '') . "\">Unterrichtsstunden</a>";
    echo "<a href=\"?page=mb-options-lessons&action=lstemplates\" class=\"nav-tab " . ($action == 'lstemplates' ? $activeClass : '') .  "\">Unterrichts-Vorlagen</a>";
    /*echo "<a href=\"?page=mb-options-lessons&action=config\" class=\"nav-tab " . ($action == 'config' ? 'nav-tab-active' : '') .  "\">Konfiguration</a>";
    echo "<a href=\"?page=mb-options-lessons&action=shortcode\" class=\"nav-tab " . ($action == 'shortcode' ? 'nav-tab-active' : '') . "\">Kurzcodes</a>";*/
    echo "</h2>";

    if (isset($_GET['msg'])) {
        echo "<div class=\"manage-controls manage-controls-msg bg-color-" . preg_replace("/[^A-Za-z0-9#]/", '', urldecode($_GET['msgcol'])) . "\"><p>" . preg_replace("/[^A-Za-z0-9äöüÄÖÜß.\-# ]/", '', urldecode($_GET['msg'])) . "</p></div>";
    }

    //echo "<div class=\"settings_page\" style=\"margin-top: 1em;\">";

    switch($action) {
        case "lessons-add":
            handle_admin_lessons_add();
            break;
        case "lstemplates":
            handle_admin_lessontemplate_list();
            break;
        case "lstemplates-add":
            handle_admin_lessontemplate_add();
            break;
        case "lstemplates-edit":
            handle_admin_lessontemplate_edit($_GET['id']);
            break;
        case "lessons":
        default:
            handle_admin_lessons_list();
            break;
    }

    echo "</div>";
}

function mb_options_ferien()
{
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    if (isset($_POST['action'])) {
        die("do not POST to this url!");
    }

    $activeClass = isset($_GET['msgcol']) ? "nav-tab-active bg-color-" . preg_replace("/[^A-Za-z0-9#]/", '', urldecode($_GET['msgcol'])) : "nav-tab-active ";

    if (isset($_POST['action'])) {
        $action = "POST_" . $_POST['action'];
    } elseif (isset($_GET['action'])) {
        $action = $_GET['action'];
    } else {
        $action = "manage";
    }

    echo "<div class=\"wrap\"><h2>Ferienprogramm verwalten <nobr>&ndash; nuBook</nobr></h2><h2 class=\"nav-tab-wrapper\">";
    #echo "<a href=\"?page=mb-options-menu&action=manage\" class=\"nav-tab " .  ($action == 'manage' ? 'nav-tab-active' : '') . "\">Unterricht</a>";
    echo "<a href=\"?page=mb-options-menu&action=ferien\" class=\"nav-tab " . ($action == 'ferien' ? $activeClass : '') . "\">Ferien</a>";
    echo "<a href=\"?page=mb-options-menu&action=managefk\" class=\"nav-tab " . ($action == 'managefk' ? $activeClass : '') . "\">Ferienkurse</a>";
    echo "<a href=\"?page=mb-options-menu&action=fktemplates\" class=\"nav-tab " . ($action == 'fktemplates' ? $activeClass : '') . "\">Ferienkurs-Vorlagen</a>";
    echo "<a href=\"?page=mb-options-menu&action=config\" class=\"nav-tab " . (($action == 'config' || $action == 'POST_config') ? $activeClass : '') . "\">Konfiguration</a>";
    echo "<a href=\"?page=mb-options-menu&action=shortcode\" class=\"nav-tab " . (($action == 'shortcode' || $action == 'POST_shortcode') ? $activeClass : '') . "\">Kurzcodes</a>";
    echo "</h2>";

    if (isset($_GET['msg'])) {
        echo "<div class=\"manage-controls manage-controls-msg bg-color-" . preg_replace("/[^A-Za-z0-9#]/", '', urldecode($_GET['msgcol'])) . "\"><p>" . preg_replace("/[^A-Za-z0-9äöüÄÖÜß.\-# ]/", '', urldecode($_GET['msg'])) . "</p></div>";
    }

    #echo "<div class=\"settings_page\" style=\"margin-top: 1em;\">";
    switch($action) {
        case "print":
            echo admin_url('admin-post.php?action=handle_admin_ferien_print');
            break;
        case "ferien":
            handle_admin_ferien_list();
            break;
        case "ferien-add":
            handle_admin_ferien_add();
            break;
        case "ferien-edit":
            handle_admin_ferien_edit($_GET['id']);
            break;
        case "ferien-imp":
            handle_admin_ferien_import();
            break;
        case "POST_ferien-edit":
            handle_admin_ferien_edit_post();
            break;
        case "POST_api-set-ft-parts":
            handle_api_ferientermine_parts(); //?
            break;
        case "fktemplates":
            handle_admin_ferientemplate_list();
            break;
        case "fktemplates-add":
            handle_admin_ferientemplate_add();
            break;
        case "POST_fktemplates-edit":
            handle_admin_ferientemplate_edit_post_local();
            break;
        case "fktemplates-edit":
            handle_admin_ferientemplate_edit($_GET['id']);
            break;
    }
    echo "</div>";
}

function mb_options()
{
    global $wpdb;
    $utname = $wpdb->prefix . "wmb_ust";
    $db_ferientermine = $wpdb->prefix . "wmb_fpr";
    $pfname = $wpdb->prefix . "wmb_pfd";
    $termin = db_ferientermine;
    $template = db_ferientemplates;

    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    if (isset($_POST['action'])) {
        $action = "POST_" . $_POST['action'];
    } elseif (isset($_GET['action'])) {
        $action = $_GET['action'];
    } else {
        $action = "manage";
    }

    echo "<div class=\"wrap\"><h2>Reitbuch-Einstellungen</h2><h2 class=\"nav-tab-wrapper\"><a href=\"?page=mb-options-menu&action=manage\" class=\"nav-tab ";
    echo $action == 'manage' ? 'nav-tab-active' : '';
    echo "\">Unterricht</a><a href=\"?page=mb-options-menu&action=ferien\" class=\"nav-tab ";
    echo $action == 'ferien' ? 'nav-tab-active' : '';
    echo "\">Ferien</a><a href=\"?page=mb-options-menu&action=managefk\" class=\"nav-tab ";
    echo $action == 'managefk' ? 'nav-tab-active' : '';
    echo "\">Ferienkurse</a><a href=\"?page=mb-options-menu&action=fktemplates\" class=\"nav-tab ";
    echo $action == 'fktemplates' ? 'nav-tab-active' : '';
    echo "\">Ferienkurs-Vorlagen</a><a href=\"?page=mb-options-menu&action=config\" class=\"nav-tab ";
    echo ($action == 'config' || $action == 'POST_config') ? 'nav-tab-active' : '';
    echo "\">Konfiguration</a><a href=\"?page=mb-options-menu&action=shortcode\" class=\"nav-tab ";
    echo ($action == 'shortcode' || $action == 'POST_shortcode') ? 'nav-tab-active' : '';
    echo "\">Kurzcodes</a></h2>";

    if (isset($_GET['msg'])) {
        echo "<div class=\"manage-controls\" style=\"color: white; background-color: " . preg_replace("/[^A-Za-z0-9# ]/", '', $_GET['msgcol']) . "\"><p>" . preg_replace("/[^A-Za-z0-9äöüÄÖÜß.\-# ]/", '', urldecode($_GET['msg'])) . "</p></div>";
    }

    echo "<div class=\"settings_page\" style=\"margin-top: 1em;\">";

    switch($action) {
        case "print":
            echo admin_url('admin-post.php?action=handle_admin_ferien_print');
            break;
        case "ferien":
            handle_admin_ferien_list();
            break;
        case "ferien-add":
            handle_admin_ferien_add();
            break;
        case "ferien-edit":
            handle_admin_ferien_edit($_GET['id']);
            break;
        case "ferien-imp":
            handle_admin_ferien_import();
            break;
        case "POST_ferien-edit":
            handle_admin_ferien_edit_post();
            break;
        case "POST_api-set-ft-parts":
            handle_api_ferientermine_parts(); //?
            break;
        case "fktemplates":
            handle_admin_ferientemplate_list();
            break;
        case "fktemplates-add":
            handle_admin_ferientemplate_add();
            break;
        case "POST_fktemplates-edit":
            handle_admin_ferientemplate_edit_post_local();
            break;
        case "fktemplates-edit":
            handle_admin_ferientemplate_edit($_GET['id']);
            break;
        case "lstemplates":
            handle_admin_lessontemplate_list();
            break;
        case "lstemplates-add":
            handle_admin_lessontemplate_add();
            break;
        case "lstemplates-edit":
            handle_admin_lessontemplate_edit($_GET['id']);
            break;
        case "lessons":
            handle_admin_lessons_list();
            break;
        case "lessons-add":
            handle_admin_lessons_add();
            break;
        case "main":
            echo '<table class="form-table"><thead><tr><th colspan="2"><a href="?page=mb-options-menu&action=add" class="button button-primary">Neu hinzufügen</a></th></tr></thead><tbody>';

            foreach ($wpdb->get_results("SELECT ID, TITEL, TAG FROM $utname ORDER BY TAG, ZEITVON") as $key => $row) {
                echo "<tr><td>" . $row->TITEL . "</td><td><a href=\"?page=mb-options-menu&action=edit&id=" . $row->ID . "\" class=\"button button-primary\">Bearb.</a>&nbsp;<a href=\"?page=mb-options-menu&action=delete&id=" . $row->ID . "\" class=\"button button-primary\">Lösch.</a></td></tr>";
            }

            echo "</tbody></table>";
            break;
        case "POST_edit":
            if (strtotime($_POST['zeitvon']) === false || strtotime($_POST['zeitbis']) === false) {
                echo "<div class=\"manage-controls mcerr\"><p>Fehler: Ungültige Start- oder Endzeit!</p></div>";
            } elseif (!is_numeric($_POST['stdkids']) || !is_numeric($_POST['stdkidsmax'])) {
                echo "<div class=\"manage-controls mcerr\"><p>Fehler: Ungültige Teilnehmeranzahl!</p></div>";
            } elseif (!is_numeric($_POST['typ']) || !is_numeric($_POST['tag'])) {
                echo "<div class=\"manage-controls mcerr\"><p>Fehler: Ungültiges Angebot oder Tag!</p></div>";
            } elseif (strlen($_POST['titel']) < 5) {
                echo "<div class=\"manage-controls mcerr\"><p>Fehler: Der Titel sollte mindestens 5 Zeichen lang sein!</p></div>";
            } elseif ($_POST['stdkids'] > $_POST['stdkidsmax']) {
                echo "<div class=\"manage-controls mcerr\"><p>Fehler: Die Teilnehmerzahl ist größer als das Maximum!</p></div>";
            } elseif (strtotime($_POST['zeitbis']) < strtotime($_POST['zeitvon'])) {
                echo "<div class=\"manage-controls mcerr\"><p>Fehler: Die Endzeit ist früher als die Startzeit!</p></div>";
            } else {
                if ($wpdb->update($utname, array( 'TITEL' => $_POST['titel'], 'TYP' => $_POST['typ'], 'TAG' => $_POST['tag'], 'ZEITVON' => $_POST['zeitvon'], 'ZEITBIS' => $_POST['zeitbis'], 'STD_KINDER' => $_POST['stdkids'], 'STD_MAX_KINDER' => $_POST['stdkidsmax']), array('ID' => $_POST['id']), array('%s', '%d', '%d', '%s', '%s', '%d', '%d'), array('%d')) !== false) {
                    echo "<div class=\"manage-controls mcok\"><p>Die Stunde wurde aktualisiert - <a href=\"?page=mb-options-menu\">zur Übersicht</a></p></div>";
                } else {
                    echo "<div class=\"manage-controls mcerr\"><p>Fehler: Die Stunde konnte nicht aktualisiert werden!</p></div>";
                }
            }
            // no break
        case "edit":
            if (!isset($_GET['id'])) {
                echo "Fehlerhafte Anfrage: Keine zu bearbeitende ID angegeben!<br><a href='?page=mb-options-menu'>Startseite</a>";
                return;
            }

            $id = $_GET['id'];

            $row = $wpdb->get_row("SELECT TITEL, TYP, TAG, ZEITVON, ZEITBIS, STD_MAX_KINDER, STD_KINDER FROM $utname WHERE ID = $id");

            $day = $row->TAG;

            echo "<div class=\"manage-controls manage-list\"><h3 class=\"edit-title\">#$id - $row->TITEL</h3><form method=\"post\" action=\"\"><input type=\"hidden\" name=\"action\" value=\"edit\"><input type=\"hidden\" name=\"id\" value=\"$id\"><table class=\"form-table manage-table\">";
            echo "<tbody>";
            echo "<tr valign=\"top\"><th scope=\"row\"><strong>Titel</strong></th><td><input type=\"text\" pattern=\".{5,}\" required title=\"Der Titel sollte mindestens 5 Zeichen lang sein\" name=\"titel\" value=\"$row->TITEL\"></td></tr>";
            echo "<tr valign=\"top\">";
            echo "<th scope=\"row\"><strong>Tag</strong></th><td><select name=\"tag\" id=\"tag\">";
            echo "<option value=\"1\"" . ($day == '1' ? 'selected' : '') . ">Montag</option>";
            echo "<option value=\"2\"" . ($day == '2' ? 'selected' : '') . ">Dienstag</option>";
            echo "<option value=\"3\"" . ($day == '3' ? 'selected' : '') . ">Mittwoch</option>";
            echo "<option value=\"4\"" . ($day == '4' ? 'selected' : '') . ">Donnerstag</option>";
            echo "<option value=\"5\"" . ($day == '5' ? 'selected' : '') . ">Freitag</option>";
            echo "<option value=\"6\"" . ($day == '6' ? 'selected' : '') . ">Samstag</option>";
            echo "<option value=\"7\"" . ($day == '7' ? 'selected' : '') . ">Sonntag</option></select></td></tr>";
            echo "<tr valign=\"top\"><th scope=\"row\"><strong>Angebot:</strong></th><td><select name=\"typ\" id=\"typ\">";
            echo "<option value=\"1\"" . ($row->TYP == '1' ? 'selected' : '') . ">Ponyführstunde</option>";
            echo "<option value=\"2\"" . ($row->TYP == '2' ? 'selected' : '') . ">Shettyreitstunde</option>";
            echo "<option value=\"3\"" . ($row->TYP == '3' ? 'selected' : '') . ">Gruppenreitstunde</option>";
            echo "<option value=\"4\"" . ($row->TYP == '4' ? 'selected' : '') . ">Erwachsenenreitstunde</option>";
            echo "<option value=\"5\"" . ($row->TYP == '5' ? 'selected' : '') . ">Pferdezeit</option>";
            echo "<option value=\"7\"" . ($row->TYP == '7' ? 'selected' : '') . ">Voltigierstunde</option>";
            //echo "<option value=\"6\"" . ($row->TYP == '6' ? 'selected' : '') . ">Ferienprogramm</option>";
            echo "</select></td></tr>";
            echo "<tr valign=\"top\"><th scope=\"row\"><strong>Uhrzeit</strong></th><td><input type=\"time\" required min=\"00:00\" max=\"23:59\" name=\"zeitvon\" value=\"$row->ZEITVON\"> bis <input type=\"time\" required min=\"00:00\" max=\"23:59\" name=\"zeitbis\" value=\"$row->ZEITBIS\"></td></tr>";
            echo "<tr valign=\"top\"><th scope=\"row\"><strong>Teilnehmer</strong></th><td><input type=\"number\" min=\"0\" required max=\"99\" name=\"stdkids\" value=\"$row->STD_KINDER\"> von maximal <input type=\"number\" required min=\"1\" max=\"99\" name=\"stdkidsmax\" value=\"$row->STD_MAX_KINDER\"></td></tr>";
            echo "<tr valign=\"top\"><th scope=\"row\" class=\"btmrow\"><input type=\"submit\" class=\"button button-primary\" value=\"Speichern\"><a href=\"?page=mb-options-menu&action=delete&id=$id\" class=\"button button-warn\">Löschen</a></th></tr>";
            echo "</tbody></table></form></div>";
            break;
        case "delete":
            if (!isset($_GET['id'])) {
                echo "Fehlerhafte Anfrage: Keine zu bearbeitende ID angegeben!<br><a href='?page=mb-options-menu'>Startseite</a>";
                return;
            }
            $id = $_GET['id'];

            $row = $wpdb->get_row("SELECT TITEL, TYP, TAG, ZEITVON, ZEITBIS FROM $utname WHERE ID = $id");

            echo "<div class=\"manage-controls\"><p>Möchten Sie die/das " . typn($row->TYP) . " #$id &quot;" . $row->TITEL . "&quot; am " . tnum($row->TAG) . " von " . $row->ZEITVON . " bis ". $row->ZEITBIS . " Uhr wirklich löschen?</p><form method=\"post\" action=\"\"><input type=\"hidden\" name=\"action\" value=\"delete\"><input type=\"hidden\" name=\"id\" value=\"$id\">";
            echo "<div class=\"del-btns\"><a href=\"?page=mb-options-menu\" class=\"button button-primary\">Abbrechen</a><input type=\"submit\" class=\"button button-warn\" value=\"Löschen\"></div>";
            echo "</form></div>";
            break;
        case "POST_delete":
            if (!isset($_POST['id'])) {
                echo "Fehlerhafte Anfrage: Keine zu bearbeitende ID angegeben!<br><a href='?page=mb-options-menu'>Startseite</a>";
                return;
            }
            if ($wpdb->delete($utname, array( 'ID' => $_POST['id']), array('%d')) !== false) {
                echo "<div class=\"manage-controls mcok\"><p>Die Stunde #" . $_POST['id'] . " wurde gelöscht - <a href=\"?page=mb-options-menu\">zur Übersicht</a></p></div>";
            } else {
                echo "<div class=\"manage-controls mcerr\"><p>Fehler: Die Stunde konnte nicht gelöscht werden!</p></div>";
            }
            break;
        case "POST_add":
            if ($wpdb->insert($utname, array( 'TITEL' => $_POST['titel'], 'TYP' => $_POST['typ'], 'TAG' => $_POST['tag'], 'ZEITVON' => $_POST['zeitvon'], 'ZEITBIS' => $_POST['zeitbis'], 'STD_KINDER' => $_POST['stdkids'], 'STD_MAX_KINDER' => $_POST['stdkidsmax']), array('%s', '%d', '%d', '%s', '%s', '%d', '%d')) !== false) {
                echo "<div class=\"manage-controls mcok\"><p>Die Stunde #$wpdb->insert_id wurde erstellt - <a href=\"?page=mb-options-menu\">zur Übersicht</a></p></div>";
            } else {
                echo "<div class=\"manage-controls mcerr\"><p>Fehler: Die Stunde konnte nicht aktualisiert werden!</p></div>";
            }
            break;
        case "add":
            $day = date('N');
            echo "<div class=\"manage-controls\"><form method=\"post\" action=\"\"><input type=\"hidden\" name=\"action\" value=\"add\"><table class=\"form-table manage-table\">";
            echo "<tbody>";
            echo "<tr valign=\"top\"><th scope=\"row\"><strong>Titel</strong></th><td><input type=\"text\" pattern=\".{5,}\" required title=\"Der Titel sollte mindestens 5 Zeichen lang sein\" name=\"titel\"></td></tr>";
            echo "<tr valign=\"top\">";
            echo "<th scope=\"row\"><strong>Tag</strong></th><td><select name=\"tag\" id=\"tag\">";
            echo "<option value=\"1\"" . ($day == '1' ? 'selected' : '') . ">Montag</option>";
            echo "<option value=\"2\"" . ($day == '2' ? 'selected' : '') . ">Dienstag</option>";
            echo "<option value=\"3\"" . ($day == '3' ? 'selected' : '') . ">Mittwoch</option>";
            echo "<option value=\"4\"" . ($day == '4' ? 'selected' : '') . ">Donnerstag</option>";
            echo "<option value=\"5\"" . ($day == '5' ? 'selected' : '') . ">Freitag</option>";
            echo "<option value=\"6\"" . ($day == '6' ? 'selected' : '') . ">Samstag</option>";
            echo "<option value=\"7\"" . ($day == '7' ? 'selected' : '') . ">Sonntag</option></select></td></tr>";
            echo "<tr valign=\"top\"><th scope=\"row\"><strong>Angebot:</strong></th><td><select name=\"typ\" id=\"typ\">";
            echo "<option value=\"1\">Ponyführstunde</option>";
            echo "<option value=\"2\">Shettyreitstunde</option>";
            echo "<option value=\"3\">Gruppenreitstunde</option>";
            echo "<option value=\"4\">Erwachsenenreitstunde</option>";
            echo "<option value=\"5\">Pferdezeit</option>";
            echo "<option value=\"7\">Voltigierstunde</option>";
            //echo "<option value=\"6\">Ferienprogramm</option>";
            echo "</select></td></tr>";
            echo "<tr valign=\"top\"><th scope=\"row\"><strong>Uhrzeit</strong></th><td><input type=\"time\" required min=\"00:00\" max=\"23:59\" name=\"zeitvon\" value=\"12:00\"> bis <input type=\"time\" required min=\"00:00\" max=\"23:59\" name=\"zeitbis\" value=\"14:00\"></td></tr>";
            echo "<tr valign=\"top\"><th scope=\"row\"><strong>Teilnehmer</strong></th><td><input type=\"number\" min=\"0\" required max=\"99\" name=\"stdkids\" value=\"0\"> von maximal <input type=\"number\" required min=\"1\" max=\"99\" name=\"stdkidsmax\" value=\"10\"></td></tr>";
            echo "<tr valign=\"top\"><th scope=\"row\" class=\"btmrow\"><input type=\"submit\" class=\"button button-primary\" value=\"Hinzufügen\"></th></tr>";
            echo "</tbody></table></form></div>";
            break;
        case "POST_addfk":
            handle_admin_ferienkurs_add_post_local();
            break;
            foreach ($_POST['dates'] as $sevent) {
                if (!isset($sevent['use'])) {
                    continue;
                }
                if ($wpdb->insert($db_ferientermine, array( 'TITEL' => $_POST['titel'], 'BESCHREIBUNG' => preg_replace("/\r\n|\r|\n/", '<br/>', $_POST['beschreibung']), 'LINKURL' => $_POST['linkurl'], 'KDATUM' => $sevent['date'], 'ZEITVON' => $sevent['start'], 'ZEITBIS' => $sevent['end'], 'STD_MAX_KINDER' => $_POST['stdkidsmax']), array('%s', '%s', '%s', '%s', '%s', '%s', '%d')) !== false) {
                    echo "<div class=\"manage-controls mcok\"><p>Der Ferienkurs \""  . $_POST['titel'] . "\" am " . $sevent['date'] . " #$wpdb->insert_id wurde erstellt - <a href=\"?page=mb-options-menu&action=managefk\">zur Übersicht</a></p></div><br>";
                } else {
                    echo "<div class=\"manage-controls mcerr\"><p>Fehler: Der Ferienkurs \""  . $_POST['titel'] . "\" am " . $sevent['date'] . " konnte nicht erstellt werden!</p></div><br>";
                }
            }
            break;
        case "addfk":
            handle_admin_ferienkurs_add();
            break;
            global $FERIENKURSE_TITEL;
            global $FERIENKURSE_TEXTE;

            $TPL_TITEL = (isset($_GET['tpl'])) ? $FERIENKURSE_TITEL[$_GET['tpl']] : "";
            $TPL_BESCH = (isset($_GET['tpl'])) ? $FERIENKURSE_TEXTE[$_GET['tpl']] : "";

            $GDATE = (isset($_COOKIE['FKLastDate'])) ? $_COOKIE['FKLastDate'] : date("Y-m-d");

            $day = date('N');
            echo "<div class=\"manage-controls mctop\"><form method=\"get\"><input type=\"hidden\" name=\"page\" value=\"mb-options-menu\"><input type=\"hidden\" name=\"action\" value=\"addfk\"><label class=\"selected-control\" for=\"tpl\">Wähle eine Vorlage aus:</label><select name=\"tpl\" id=\"tpl\">";
            for ($i = 0; $i < count($FERIENKURSE_TITEL); $i++) {
                echo "<option value=\"" . $i . "\"" . ($_GET['tpl'] ==  $i ? 'selected' : '') . ">" . str_replace("!", "", $FERIENKURSE_TITEL[$i]) . "</option>";
            }
            echo "</select><input type=\"submit\" class=\"button\" value=\"Laden\"></form></div>";
            echo "<div class=\"manage-controls\"><form method=\"post\" action=\"\"><input type=\"hidden\" name=\"action\" value=\"addfk\"><table class=\"form-table manage-table\">";
            echo "<tbody>";
            echo "<tr valign=\"top\"><th scope=\"row\"><strong>Titel</strong></th><td><input type=\"text\" pattern=\".{5,50}\" required title=\"Der Titel sollte mindestens 5 und max. 50 Zeichen lang sein\" name=\"titel\" value=\"" . $TPL_TITEL . "\"></td></tr>";
            echo "<tr valign=\"top\"><th scope=\"row\"><strong>Beschreibung</strong></th><td><textarea pattern=\".{5,}\" required title=\"Die Beschreibung sollte mindestens 5 Zeichen lang sein\" name=\"beschreibung\" cols=\"22\" rows=\"6\">" . preg_replace('/\<br\s*\/?\>/', "\n", $TPL_BESCH) . "</textarea></td></tr>";
            echo "<tr valign=\"top\"><th scope=\"row\"><strong>Link-URL</strong></th><td><input type=\"text\" name=\"linkurl\"></td></tr>";
            echo "<tr valign=\"top\"><th scope=\"row\"><strong>Max. Teilnehmer</strong></th><td><input type=\"hidden\"name=\"stdkids\" value=\"0\"><input type=\"number\" required min=\"1\" max=\"99\" name=\"stdkidsmax\" value=\"1\"></td></tr>";
            echo "<tr valign=\"top\"><th scope=\"row\"><strong>Datum/Uhrzeit</strong></th><td><input type=\"date\" name=\"dates[1][date]\" class=\"datum\" value=\"" . $GDATE . "\">, &nbsp;&nbsp;<input type=\"time\" class=\"startTime\" required min=\"00:00\" max=\"23:59\" name=\"dates[1][start]\" value=\"12:00\"> bis <input type=\"time\" class=\"endTime\" required min=\"00:00\" max=\"23:59\" name=\"dates[1][end]\" value=\"14:00\"><input type=\"checkbox\" name=\"dates[1][use]\" value=\"true\" checked></td></tr>";
            echo "<tr valign=\"top\" id=\"addDateRow\"><th scope=\"row\"><input type=\"hidden\" name=\"datesCount\" id=\"datesCount\" value=\"1\"></th><td><input type=\"button\" class=\"button button-secondary\" onClick=\"addDateField()\" value=\"+\"></td></tr>";
            echo "<tr valign=\"top\"><th scope=\"row\" class=\"btmrow\"><input type=\"submit\" class=\"button button-primary\" value=\"Hinzufügen\"></th></tr>";
            echo "</tbody></table></form></div>";
            break;
        case "POST_editfk":
            if (strtotime($_POST['zeitvon']) === false || strtotime($_POST['zeitbis']) === false) {
                echo "<div class=\"manage-controls mcerr\"><p>Fehler: Ungültige Start- oder Endzeit!</p></div>";
            } elseif (!is_numeric($_POST['stdkidsmax'])) {
                echo "<div class=\"manage-controls mcerr\"><p>Fehler: Ungültige Teilnehmeranzahl!</p></div>";
            } elseif (strlen($_POST['titel']) < 5) {
                echo "<div class=\"manage-controls mcerr\"><p>Fehler: Der Titel sollte mindestens 5 Zeichen lang sein!</p></div>";
            } elseif (strlen($_POST['beschreibung']) < 5) {
                echo "<div class=\"manage-controls mcerr\"><p>Fehler: Die Beschreibung sollte mindestens 5 Zeichen lang sein!</p></div>";
            } else {
                if ($wpdb->update($db_ferientermine, array( 'TITEL' => $_POST['titel'], 'BESCHREIBUNG' => preg_replace("/\r\n|\r|\n/", '<br/>', $_POST['beschreibung']), 'LINKURL' => $_POST['linkurl'], 'KDATUM' => $_POST['datum'], 'ZEITVON' => $_POST['zeitvon'], 'ZEITBIS' => $_POST['zeitbis'], 'STD_MAX_KINDER' => $_POST['stdkidsmax']), array('ID' => $_POST['id']), array('%s', '%s', '%s', '%s', '%s', '%s', '%d'), array('%d')) !== false) {
                    echo "<div class=\"manage-controls mcok\"><p>Der Ferienkurs wurde aktualisiert - <a href=\"?page=mb-options-menu&action=managefk\">zur Ferienkurs-Übersicht</a></p></div>";
                } else {
                    echo "<div class=\"manage-controls mcerr\"><p>Fehler: Der Ferienkurs konnte nicht aktualisiert werden!</p></div>";
                }
            }
            // no break
        case "editfk":
            if (!isset($_GET['id'])) {
                echo "Fehlerhafte Anfrage: Keine zu bearbeitende ID angegeben!<br><a href='?page=mb-options-menu'>Startseite</a>";
                return;
            }

            $id = $_GET['id'];
            $row = $wpdb->get_row("SELECT TITEL, BESCHREIBUNG, LINKURL, KDATUM, ZEITVON, ZEITBIS, STD_MAX_KINDER FROM $db_ferientermine WHERE ID = $id");

            echo "<div class=\"manage-controls manage-list\"><h3 class=\"edit-title\">#$id - $row->TITEL</h3><form method=\"post\" action=\"\"><input type=\"hidden\" name=\"action\" value=\"editfk\"><input type=\"hidden\" name=\"id\" value=\"$id\"><table class=\"form-table manage-table\">";
            echo "<tbody>";
            echo "<tr valign=\"top\"><th scope=\"row\"><strong>Titel</strong></th><td><input type=\"text\" pattern=\".{5,50}\" required title=\"Der Titel sollte mindestens 5 und max. 50 Zeichen lang sein\" name=\"titel\" value=\"" . $row->TITEL . "\"></td></tr>";
            echo "<tr valign=\"top\"><th scope=\"row\"><strong>Beschreibung</strong></th><td><textarea pattern=\".{5,}\" required title=\"Die Beschreibung sollte mindestens 5 Zeichen lang sein\" name=\"beschreibung\" cols=\"22\" rows=\"6\">" . preg_replace('/\<br\s*\/?\>/', "\n", $row->BESCHREIBUNG) . "</textarea></td></tr>";
            echo "<tr valign=\"top\"><th scope=\"row\"><strong>Link-URL</strong></th><td><input type=\"text\" name=\"linkurl\" value=\"" . $row->LINKURL . "\"></td></tr>";
            echo "<tr valign=\"top\"><th scope=\"row\"><strong>Datum</strong></th><td><input type=\"date\" name=\"datum\" id=\"datum\" value=\"" . date("Y-m-d", strtotime($row->KDATUM)) . "\"></td></tr>";
            echo "<tr valign=\"top\"><th scope=\"row\"><strong>Uhrzeit</strong></th><td><input type=\"time\" required min=\"00:00\" max=\"23:59\" name=\"zeitvon\" value=\"" . $row->ZEITVON . "\"> bis <input type=\"time\" required min=\"00:00\" max=\"23:59\" name=\"zeitbis\" value=\"" . $row->ZEITBIS . "\"></td></tr>";
            echo "<tr valign=\"top\"><th scope=\"row\"><strong>Max. Teilnehmer</strong></th><td><input type=\"hidden\"name=\"stdkids\" value=\"0\"><input type=\"number\" required min=\"1\" max=\"99\" name=\"stdkidsmax\" value=\"" . $row->STD_MAX_KINDER . "\"></td></tr>";
            echo "<tr valign=\"top\"><th scope=\"row\" class=\"btmrow\"><input type=\"submit\" class=\"button button-primary\" value=\"Bearbeiten\"><a href=\"?page=mb-options-menu&action=deletefk&id=$id\" class=\"button button-warn\">Löschen</a></th></tr>";
            echo "</tbody></table></form></div>";
            break;
        case "POST_managefk":
            $id = $_POST['subm'];
            $varz = "kids" . $id;
            if (!isset($_POST[$varz])) {
                echo "<div class=\"manage-controls mcerr\"><p>Unbekanntes Feld/Ferienkurs-ID \"" . $varz . "\" - <a href=\"javascript:location.reload()\">zur Ferienkurs-Übersicht</a></p></div><br>";
                return;
            }
            $day = $_POST['wtag'];
            if ($wpdb->update($db_ferientermine, array( 'STD_KINDER' => $_POST[$varz]), array('ID' => $id), array('%d'), array('%d')) !== false) {
                echo "<div class=\"manage-controls mcok\"><p>Die Teilnehmerzahl wurde aktualisiert</p></div><br>";
            } else {
                echo "<div class=\"manage-controls mcerr\"><p>Fehler: Der Teilnehmerzahl konnte nicht aktualisiert werden!</p></div><br>";
            }
            // no break
        case "managefk":
            handle_admin_ferienkurs_list();
            break;
            echo '<table class="form-table"><thead><tr><th colspan="1" class="manage-title"><h3>Ferienkurse</h3></th></tr>';
            echo "<tr><th class=\"mctools-th\"><div class=\"manage-controls mctop mctools-div\"><a href=\"?page=mb-options-menu&action=addfk\" class=\"button button-primary\">Erstellen</a>&nbsp;<a href=\"?page=mb-options-menu&action=clrfk\" class=\"button button-primary\">Vergangene Kurse löschen</a>&nbsp;<a href=\"?page=mb-options-menu&action=wipefk\" class=\"button button-primary\">Alle Kurse löschen</a>&nbsp;<a href=\"?page=mb-options-menu&action=config#ferien\" class=\"button button-primary\">Ferien festlegen</a>&nbsp;<a href=\"?page=mb-options-menu&action=oldfk\" class=\"button button-primary\">Archiv</a></div></th></tr>";
            echo '</thead><tbody>';
            foreach ($wpdb->get_results("SELECT ID, TITEL, STD_MAX_KINDER, STD_KINDER, KDATUM, ZEITVON, ZEITBIS FROM $db_ferientermine WHERE KDATUM >= CURDATE() ORDER BY KDATUM, ZEITVON") as $key => $row) {
                echo "<tr><td><div class=\"manage-controls manage-table\"><table><tr><td><p><a href=\"?page=mb-options-menu&action=editfk&id=" . $row->ID . "\">" . $row->TITEL . "</a><br><small>" . date("d.m.Y", strtotime($row->KDATUM)) . ", " . date('G:i', strtotime($row->ZEITVON)) . " - " . date('G:i', strtotime($row->ZEITBIS)) . " Uhr</small></p></td>";
                echo "<td><div class=\"qty btns_added\"><form method=\"post\" action=\"\"><input type=\"hidden\" name=\"action\" value=\"managefk\"><input type=\"hidden\" name=\"wtag\" value=\"$day\"><input type=\"hidden\" name=\"day\" value=\"" . $row->TAG . "\"><input type=\"button\" value=\"-\" class=\"minus\" onclick=\"document.getElementById('kids" . $row->ID . "').stepDown(1);\">";
                echo "<input type=\"number\" name=\"kids" . $row->ID . "\" id=\"kids" . $row->ID . "\" min=\"-1\" max=\"" . $row->STD_MAX_KINDER . "\" value=\"" . $row->STD_KINDER . "\" title=\"Qty\" class=\"input-text qt text\" size=\"4\" pattern=\"\" inputmode=\"\"><input type=\"button\" value=\"+\" class=\"plus\" onclick=\"document.getElementById('kids" . $row->ID . "').stepUp(1);\">";
                //echo "<a href=\"#\"  class=\"button button-primary\">-</a>&nbsp;<input type=\"number\" >&nbsp;<a href=\"#\"  class=\"button button-primary\">+</a>
                //echo "<input type=\"submit\" class=\"button\" name=\"subm\" value=\" . $row->ID . \" alt=\"OK\"></div></td></tr></table></div></td></tr>";
                echo "<button type=\"submit\" class=\"button\" name=\"subm\" value=\"$row->ID\">OK</button></div></td></tr></table></div></td></tr>";
                #echo "<tr><td><div class=\"manage-controls manage-table\"><table><tr><td>" . $row->TITEL . "</td><td><a href=\"#\" onclick=\"document.getElementById('kids" . $row->ID . "').stepDown(1);\" class=\"button button-primary\">-</a>&nbsp;<input type=\"number\" name=\"kids" . $row->ID . "\" id=\"kids" . $row->ID . "\" min=\"0\" max=\"" . $row->STD_MAX_KINDER . "\" value=\"" . $row->STD_KINDER . "\">&nbsp;<a href=\"#\" onclick=\"document.getElementById('kids" . $row->ID . "').stepUp(1);\" class=\"button button-primary\">+</a></td></tr></table></div></td></tr>";
            }
            echo "</tbody></table>";
            break;
        case "oldfk":
            echo '<table class="form-table"><thead><tr><th colspan="1" class="manage-title"><h3>Alte Ferienkurse</h3></th></tr>';
            echo "<tr><th class=\"mctools-th\"><div class=\"manage-controls mctop mctools-div\"><a href=\"?page=mb-options-menu&action=managefk\" class=\"button button-primary\"><- Aktuelle Kurse</a>&nbsp;<a href=\"?page=mb-options-menu&action=clrfk\" class=\"button button-primary\">Vergangene Kurse löschen</a></div></th></tr>";
            echo '</thead><tbody>';
            foreach ($wpdb->get_results("SELECT ID, TITEL, STD_MAX_KINDER, STD_KINDER, KDATUM, ZEITVON, ZEITBIS FROM $db_ferientermine WHERE KDATUM < CURDATE() ORDER BY KDATUM, ZEITVON") as $key => $row) {
                echo "<tr><td><div class=\"manage-controls manage-table\"><table><tr><td><p><a href=\"?page=mb-options-menu&action=editfk&id=" . $row->ID . "\">" . $row->TITEL . "</a><br><small>" . date("d.m.Y", strtotime($row->KDATUM)) . ", " . date('G:i', strtotime($row->ZEITVON)) . " - " . date('G:i', strtotime($row->ZEITBIS)) . " Uhr</small></p></td>";
                echo "<td><div class=\"qty btns_added\"><form method=\"post\" action=\"\"><input type=\"hidden\" name=\"action\" value=\"managefk\"><input type=\"hidden\" name=\"wtag\" value=\"$day\"><input type=\"hidden\" name=\"day\" value=\"" . $row->TAG . "\"><input type=\"button\" value=\"-\" class=\"minus\" onclick=\"document.getElementById('kids" . $row->ID . "').stepDown(1);\">";
                echo "<input type=\"number\" name=\"kids" . $row->ID . "\" id=\"kids" . $row->ID . "\" min=\"-1\" max=\"" . $row->STD_MAX_KINDER . "\" value=\"" . $row->STD_KINDER . "\" title=\"Qty\" class=\"input-text qt text\" size=\"4\" pattern=\"\" inputmode=\"\"><input type=\"button\" value=\"+\" class=\"plus\" onclick=\"document.getElementById('kids" . $row->ID . "').stepUp(1);\">";
                //echo "<a href=\"#\"  class=\"button button-primary\">-</a>&nbsp;<input type=\"number\" >&nbsp;<a href=\"#\"  class=\"button button-primary\">+</a>
                //echo "<input type=\"submit\" class=\"button\" name=\"subm\" value=\" . $row->ID . \" alt=\"OK\"></div></td></tr></table></div></td></tr>";
                echo "<button type=\"submit\" class=\"button\" name=\"subm\" value=\"$row->ID\">OK</button></div></td></tr></table></div></td></tr>";
                #echo "<tr><td><div class=\"manage-controls manage-table\"><table><tr><td>" . $row->TITEL . "</td><td><a href=\"#\" onclick=\"document.getElementById('kids" . $row->ID . "').stepDown(1);\" class=\"button button-primary\">-</a>&nbsp;<input type=\"number\" name=\"kids" . $row->ID . "\" id=\"kids" . $row->ID . "\" min=\"0\" max=\"" . $row->STD_MAX_KINDER . "\" value=\"" . $row->STD_KINDER . "\">&nbsp;<a href=\"#\" onclick=\"document.getElementById('kids" . $row->ID . "').stepUp(1);\" class=\"button button-primary\">+</a></td></tr></table></div></td></tr>";
            }
            echo "</tbody></table>";
            break;
        case "deletefk":
            if (!isset($_GET['id'])) {
                echo "Fehlerhafte Anfrage: Keine zu löschende Ferienkurs-ID angegeben!<br><a href='?page=mb-options-menu'>Startseite</a>";
                return;
            }
            $id = $_GET['id'];

            $row = $wpdb->get_row("SELECT `$termin`.ID, `$termin`.DATESTART, `$termin`.DATEEND, `$template`.TITLE FROM `$termin` INNER JOIN `$template` ON `$termin`.`TEMPLATE` = `$template`.`ID` WHERE `$termin`.ID = $id ORDER BY `$termin`.`DATESTART`");

            echo "<div class=\"manage-controls\"><p>Möchten Sie den Ferienkurs #$id &quot;" . $row->TITLE . "&quot; am " . date("d.m.Y", strtotime($row->DATESTART)) . " von " . date("H:i", strtotime($row->DATESTART)) . " bis ". date("d.m.Y, H:i", strtotime($row->DATESTART)) . " Uhr wirklich löschen?</p><form method=\"post\" action=\"\"><input type=\"hidden\" name=\"action\" value=\"deletefk\"><input type=\"hidden\" name=\"id\" value=\"$id\">";
            echo "<div class=\"del-btns\"><a href=\"?page=mb-options-menu&action=editfk&id=$id\" class=\"button button-primary\">Abbrechen</a><input type=\"submit\" class=\"button button-warn\" value=\"Löschen\"></div>";
            echo "</form></div>";
            break;
        case "POST_deletefk":
            if (!isset($_POST['id'])) {
                echo "Fehlerhafte Anfrage: Keine zu löschende Ferienkurs-ID angegeben!<br><a href='?page=mb-options-menu'>Startseite</a>";
                return;
            }
            if ($wpdb->delete($termin, array( 'ID' => $_POST['id']), array('%d')) !== false) {
                echo "<div class=\"manage-controls mcok\"><p>Der Ferienkurs #" . $_POST['id'] . " wurde gelöscht - <a href=\"?page=mb-options-menu&action=managefk\">zur Übersicht</a></p></div>";
            } else {
                echo "<div class=\"manage-controls mcerr\"><p>Fehler: Der Ferienkurs konnte nicht gelöscht werden!</p></div>";
            }
            break;
        case "clrfk":
            $leg = $wpdb->get_results("SELECT TITEL, KDATUM, ZEITVON, ZEITBIS FROM $db_ferientermine WHERE KDATUM < CURDATE() ORDER BY KDATUM, ZEITVON");
            echo "<div class=\"manage-controls\"><h3>Möchten Sie die folgenden vergangenen " . count($leg) . " Ferienkurse wirklich löschen?</h3><ul>";
            foreach ($leg as $key => $row) {
                echo "<li>" . $row->TITEL . " (" . date("d.m.Y", strtotime($row->KDATUM)) . ", " . date('G:i', strtotime($row->ZEITVON)) . "-" . date('G:i', strtotime($row->ZEITBIS))  . " Uhr)</li>";
            }
            echo "</ul><form method=\"post\" action=\"\"><input type=\"hidden\" name=\"action\" value=\"clrfk\"><div class=\"del-btns\"><a href=\"?page=mb-options-menu&action=managefk\" class=\"button button-primary\">Abbrechen</a><input type=\"submit\" class=\"button button-warn\" value=\"Löschen\"></div>";
            echo "</form></div>";
            break;
        case "POST_clrfk":
            if ($wpdb->query("DELETE FROM $db_ferientermine WHERE KDATUM < CURDATE()") !== false) {
                echo "<div class=\"manage-controls mcok\"><p>Alle vergangenen Ferienkurse wurden gelöscht - <a href=\"?page=mb-options-menu&action=managefk\">zur Übersicht</a></p></div>";
            } else {
                echo "<div class=\"manage-controls mcerr\"><p>Fehler: Die Ferienkurse konnten nicht gelöscht werden!</p></div>";
            }
            break;
        case "wipefk":
            $leg = $wpdb->get_results("SELECT TITEL, KDATUM, ZEITVON, ZEITBIS FROM $db_ferientermine ORDER BY KDATUM, ZEITVON");
            echo "<div class=\"manage-controls\"><h3>Möchten Sie wirklich ALLE " . count($leg) . " Ferienkurse entgültig löschen?</h3><ul>";
            foreach ($leg as $key => $row) {
                echo "<li>" . $row->TITEL . " (" . date("d.m.Y", strtotime($row->KDATUM)) . ", " . date('G:i', strtotime($row->ZEITVON)) . "-" . date('G:i', strtotime($row->ZEITBIS))  . " Uhr)</li>";
            }
            echo "</ul><form method=\"post\" action=\"\"><input type=\"hidden\" name=\"action\" value=\"clrfk\"><div class=\"del-btns\"><a href=\"?page=mb-options-menu&action=managefk\" class=\"button button-primary\">Abbrechen</a><input type=\"submit\" class=\"button button-warn\" value=\"Löschen\"></div>";
            echo "</form></div>";
            break;
        case "POST_wipefk":
            if ($wpdb->query("DELETE FROM $db_ferientermine") !== false) {
                echo "<div class=\"manage-controls mcok\"><p>Alle Ferienkurse wurden gelöscht - <a href=\"?page=mb-options-menu&action=managefk\">zur Übersicht</a></p></div>";
            } else {
                echo "<div class=\"manage-controls mcerr\"><p>Fehler: Die Ferienkurse konnten nicht gelöscht werden!</p></div>";
            }
            break;
        case "manage":
            if (!isset($_GET['day'])) {
                $day = date('N');
            } else {
                $day = $_GET['day'];
            }
            $dayte = date('Ymd', strtotime(dnum($day)));
            echo "<div class=\"manage-controls mctop\"><form method=\"get\"><input type=\"hidden\" name=\"page\" value=\"mb-options-menu\"><input type=\"hidden\" name=\"action\" value=\"manage\"><label class=\"selected-control\" for=\"day\">Wähle einen Tag aus:</label><select name=\"day\" id=\"day\">";
            echo "<option value=\"1\"" . ($day == '1' ? 'selected' : '') . ">Montag</option>";
            echo "<option value=\"2\"" . ($day == '2' ? 'selected' : '') . ">Dienstag</option>";
            echo "<option value=\"3\"" . ($day == '3' ? 'selected' : '') . ">Mittwoch</option>";
            echo "<option value=\"4\"" . ($day == '4' ? 'selected' : '') . ">Donnerstag</option>";
            echo "<option value=\"5\"" . ($day == '5' ? 'selected' : '') . ">Freitag</option>";
            echo "<option value=\"6\"" . ($day == '6' ? 'selected' : '') . ">Samstag</option>";
            echo "<option value=\"7\"" . ($day == '7' ? 'selected' : '') . ">Sonntag</option>";
            echo "</select><input type=\"submit\" class=\"button\" value=\"Auswählen\"></form></div>";
            echo '<table class="form-table"><thead><tr><th colspan="1" class="manage-title"><h3>Unterrichtsstunden am ' . tnum($day) . ' (' . date("d.m.Y", strtotime(dnum($day))) . ')</h3></th></tr>';
            echo "<tr><th class=\"mctools-th\"><div class=\"manage-controls mctop mctools-div\"><a href=\"?page=mb-options-menu&action=add\" class=\"button button-primary\">Erstellen</a></div></th></tr>";
            echo "</thead><tbody>";
            foreach ($wpdb->get_results("SELECT ID, TITEL, ZEITVON, ZEITBIS, STD_MAX_KINDER, STD_KINDER, OVR_DATUM, OVR_KINDER FROM $utname WHERE TAG = $day ORDER BY ZEITVON") as $key => $row) {
                echo "<tr><td><div class=\"manage-controls manage-table\"><table><tr><td><p><a href=\"?page=mb-options-menu&action=edit&id=" . $row->ID . "\">" . $row->TITEL . "</a><br><small>" . date("G:i", strtotime($row->ZEITVON)) . "-" . date("G:i", strtotime($row->ZEITBIS)) . " Uhr</small></p></td>";
                echo "<td><div class=\"qty btns_added\"><form method=\"post\" action=\"\"><input type=\"hidden\" name=\"action\" value=\"manage\"><input type=\"hidden\" name=\"wtag\" value=\"$day\"><input type=\"hidden\" name=\"day\" value=\"" . $row->TAG . "\"><input type=\"button\" value=\"-\" class=\"minus\" onclick=\"document.getElementById('kids" . $row->ID . "').stepDown(1);\">";
                if (!is_null($row->OVR_DATUM)) {
                    //if((date('Ymd') == date('Ymd', strtotime($row->OVR_DATUM))) && !($row->OVR_KINDER == $row->STD_KINDER)) {
                    if (($dayte == date('Ymd', strtotime($row->OVR_DATUM))) && !($row->OVR_KINDER == $row->STD_KINDER)) {
                        $OVC = "changed";
                        $OVN = $row->OVR_KINDER;
                    } else {
                        $OVC = "";
                        $OVN = $row->STD_KINDER;
                    }
                } else {
                    $OVC = "";
                    $OVN = $row->STD_KINDER;
                }
                echo "<input type=\"number\" name=\"kids" . $row->ID . "\" id=\"kids" . $row->ID . "\" min=\"0\" max=\"" . $row->STD_MAX_KINDER . "\" value=\"" . $OVN . "\" title=\"Qty\" class=\"input-text qt text $OVC\" size=\"4\" pattern=\"\" inputmode=\"\"><input type=\"button\" value=\"+\" class=\"plus\" onclick=\"document.getElementById('kids" . $row->ID . "').stepUp(1);\">";
                //echo "<a href=\"#\"  class=\"button button-primary\">-</a>&nbsp;<input type=\"number\" >&nbsp;<a href=\"#\"  class=\"button button-primary\">+</a>
                //echo "<input type=\"submit\" class=\"button\" name=\"subm\" value=\" . $row->ID . \" alt=\"OK\"></div></td></tr></table></div></td></tr>";
                echo "<button type=\"submit\" class=\"button\" name=\"subm\" value=\"$row->ID\">OK</button></div></td></tr></table></div></td></tr>";
                #echo "<tr><td><div class=\"manage-controls manage-table\"><table><tr><td>" . $row->TITEL . "</td><td><a href=\"#\" onclick=\"document.getElementById('kids" . $row->ID . "').stepDown(1);\" class=\"button button-primary\">-</a>&nbsp;<input type=\"number\" name=\"kids" . $row->ID . "\" id=\"kids" . $row->ID . "\" min=\"0\" max=\"" . $row->STD_MAX_KINDER . "\" value=\"" . $row->STD_KINDER . "\">&nbsp;<a href=\"#\" onclick=\"document.getElementById('kids" . $row->ID . "').stepUp(1);\" class=\"button button-primary\">+</a></td></tr></table></div></td></tr>";
            }
            echo "</tbody></table>";
            break;
        case "POST_manage":
            $id = $_POST['subm'];
            $varz = "kids" . $id;
            if (!isset($_POST[$varz])) {
                echo "<p>Fehlerhafte Anfrage - unbekanntes Feld / unbekannte Stunden-ID \"" . $varz . "\"</p><a href=\"javascript:location.reload()\">Reload</a>";
                return;
            }

            $day = $_POST['wtag'];

            $wpdb->update($utname, array( 'OVR_KINDER' => $_POST[$varz], 'OVR_DATUM' => date('Y-m-d', strtotime(dnum($day)))), array('ID' => $id), array('%d', '%s'), array('%d'));
            echo "<p>POST_manage</p><p>";

            echo $id;
            echo "<br>";
            echo $day;
            //echo $varz;
            echo dnum($day);
            echo "<br>";
            echo $_POST['kids' . $id];
            echo "</p>";
            break;
        case "POST_config":
            update_option('std1', $_POST['std1']);
            update_option('std2', $_POST['std2']);
            update_option('std3', $_POST['std3']);
            update_option('std4', $_POST['std4']);
            update_option('std5', $_POST['std5']);
            update_option('std6', $_POST['std6']);
            update_option('std7', $_POST['std7']);
            update_option('ferientitel', $_POST['ferientitel']);
            update_option('ferien_following', (isset($_POST['ferien_following'])) ? 'TRUE' : 'FALSE');
            update_option('show_max_tn', (isset($_POST['show_max_tn'])) ? 'TRUE' : 'FALSE');
            update_option('show_all_days', (isset($_POST['show_all_days'])) ? 'TRUE' : 'FALSE');
            update_option('show_saturday', (isset($_POST['show_saturday'])) ? 'TRUE' : 'FALSE');
            update_option('show_sunday', (isset($_POST['show_sunday'])) ? 'TRUE' : 'FALSE');
            /*if(isset($_POST['show_all_days'])) {
              echo "TRUE";
              update_option( 'show_all_days' , 'TRUE');
            } else {
              update_option( 'show_all_days' , 'FALSE');
            }*/
            echo "<div class=\"manage-controls mcok\"><p>Die Konfiguration wurde gespeichert!</p></div>";
            // no break
        case "config":
            echo "<div class=\"manage-controls\"><form method=\"post\" action=\"\"><input type=\"hidden\" name=\"action\" value=\"config\"><table class=\"form-table cfg-table\">";
            echo "<tbody>";
            echo "<tr valign=\"top\"><td colspan=\"2\"><h3>Angebot-Links</h3></td></tr>";
            echo "<tr valign=\"top\"><th scope=\"row\"><strong>Link bei Ponyführstunden</strong></th><td><input type=\"text\" name=\"std1\" value=\"" . esc_attr(get_option('std1')) . "\"/></td></tr>";
            echo "<tr valign=\"top\"><th scope=\"row\"><strong>Link bei Shettyreitstunden</strong></th><td><input type=\"text\" name=\"std2\" value=\"" . esc_attr(get_option('std2')) . "\"/></td></tr>";
            echo "<tr valign=\"top\"><th scope=\"row\"><strong>Link bei Gruppenreitstunden</strong></th><td><input type=\"text\" name=\"std3\" value=\"" . esc_attr(get_option('std3')) . "\"/></td></tr>";
            echo "<tr valign=\"top\"><th scope=\"row\"><strong>Link bei Erwachsenenreitstunden</strong></th><td><input type=\"text\" name=\"std4\" value=\"" . esc_attr(get_option('std4')) . "\"/></td></tr>";
            echo "<tr valign=\"top\"><th scope=\"row\"><strong>Link bei Pferdezeiten</strong></th><td><input type=\"text\" name=\"std5\" value=\"" . esc_attr(get_option('std5')) . "\"/></td></tr>";
            echo "<tr valign=\"top\"><th scope=\"row\"><strong>Link bei Voltigierstunden</strong></th><td><input type=\"text\" name=\"std7\" value=\"" . esc_attr(get_option('std7')) . "\"/></td></tr>";
            echo "<tr valign=\"top\"><th scope=\"row\"><strong>Link bei sonstige</strong></th><td><input type=\"text\" disabled name=\"std6\" value=\"nicht verwendet " . esc_attr(get_option('std6')) . "\"/></td></tr>";
            echo "<tr valign=\"top\"><td colspan=\"2\" class=\"cfg-spacer\"><hr></td></tr>";
            echo "<tr valign=\"top\"><td colspan=\"2\"><h3>Anzeige-Einstellungen</h3></td></tr>";
            echo "<tr valign=\"top\"><th scope=\"row\"><strong>Standard-Anzeigeart</strong></th><td><input type=\"checkbox\" name=\"show_all_days\" value=\"alldays\"" . (get_option('show_all_days') == 'TRUE' ? 'checked' : '') . "> Alle Tage zeigen</td></tr>";
            echo "<tr valign=\"top\"><th scope=\"row\"><strong>Angezeigte Tage</strong></th><td><input type=\"checkbox\" name=\"show_saturday\" value=\"showsat\"" . (get_option('show_saturday') == 'TRUE' ? 'checked' : '') . "> Samstag zeigen</td></tr>";
            echo "<tr valign=\"top\"><th scope=\"row\">&nbsp;</th><td><input type=\"checkbox\" name=\"show_sunday\" value=\"showsun\"" . (get_option('show_sunday') == 'TRUE' ? 'checked' : '') . "> Sonntag zeigen</td></tr>";
            echo "<tr valign=\"top\"><td colspan=\"2\" class=\"cfg-spacer\"><hr></td></tr>";
            echo "<tr valign=\"top\"><td colspan=\"2\"><h3 id=\"ferien\">Ferien-Einstellungen</h3></td></tr>";
            echo "<tr valign=\"top\"><th scope=\"row\"><strong>Ferien-Titel</strong></th><td><input type=\"text\" name=\"ferientitel\" value=\"" . esc_attr(get_option('ferientitel')) . "\"/></td></tr>";
            echo "<tr valign=\"top\"><th scope=\"row\"><strong>Anzeigeart</strong></th><td><input type=\"checkbox\" name=\"ferien_following\" value=\"follow\"" . (get_option('ferien_following') == 'TRUE' ? 'checked' : '') . "> Nur zukünftige Kurse anzeigen</td></tr>";
            echo "<tr valign=\"top\"><th scope=\"row\"><strong>Teilnehmer-Anzeige</strong></th><td><input type=\"checkbox\" name=\"show_max_tn\" value=\"follow\"" . (get_option('show_max_tn') == 'TRUE' ? 'checked' : '') . "> Maximale Teilnehmer anzeigen</td></tr>";
            echo "<tr valign=\"top\"><th scope=\"row\" class=\"btmrow\"><input type=\"submit\" class=\"button button-primary\" value=\"Speichern\"></th></tr>";
            echo "</tbody></table></form></div>";
            break;
        case "shortcode":
            echo "<div class=\"manage-controls\"><h4>Shortcode-Generator</h4><p>Um das Reitbuch in eine Seite einzubinden werden sog. Shortcodes verwendet - diese werden einfach als normaler Text im Seiteneditor eingefügt. Verwenden Sie dieses Werkzeug um diese einfach zu generieren!</p><hr>";
            echo "<form method=\"post\" action=\"\"><input type=\"hidden\" name=\"action\" value=\"shortcode\"><table class=\"form-table manage-table\">";
            echo "<tbody>";
            echo "<th scope=\"row\"><strong>Typ</strong></th><td><select name=\"typ\" id=\"typ\">";
            echo "<option value=\"reitbuch_et\">Reitbuch (Tag auswählbar)</option>";
            echo "<option value=\"reitbuch_all\">Reitbuch (Alle Tage)</option>";
            echo "<option value=\"reitbuch\">Reitbuch (eingestellte Anzeigeart)</option>";
            echo "<option value=\"reitkalender\">Reitkalender</option>";
            echo "<option value=\"stunden\">Stunden-Kalender</option>";
            echo "<option value=\"ferienkurs\">Ferienkurs-Kalender</option>";
            echo "<option value=\"ferienprogramm\">Ferienprogramm</option></select></td></tr>";
            echo "<tr valign=\"top\"><th scope=\"row\" class=\"btmrow\"><input type=\"submit\" class=\"button button-primary\" value=\"Weiter\"></th></tr>";
            echo "</tbody></table></form><hr>";
            echo "<h4>Erklärung:</h4>";
            echo "<p><strong>Reitbuch</strong><br>Das Reitbuch zeigt die Reitstunden für jeden Wochentag für die nächsten 7 Tage und deren Belegung an<br><br><strong>Reitkalender</strong><br>Der Reitkalender zeigt die Reitstunden und Ferienkurse für die nächsten 7 Tage und deren Belegung an<br><br><strong>Stunden-Kalender</strong><br>Der Stundenkalender zeigt die Reitstunden eines Typs für die nächsten 7 Tage und deren Belegung an<br><br>";
            echo "<strong>Ferienkurs-Kalender</strong><br>Der Ferienkurs-Kalender zeigt alle Termine und deren Belegung für einen Ferienkurs-Typ an<br><br><strong>Ferienprogramm</strong><br>Das Ferienprogramm zeigt alle Ferienkurse und ihre Belegung an</p></div>";
            break;
        case "POST_shortcode":
            if (!isset($_POST['typ'])) {
                echo "Kein Typ angegeben!";
                break;
            }

            if (in_array($_POST['typ'], array('stunden', 'ferienkurs'))) {
                if (isset($_POST['value'])) {
                    echo "<div class=\"manage-controls\"><h4>Shortcode-Generator</h4><p>Um das Reitbuch in eine Seite einzubinden werden sog. Shortcodes verwendet - diese werden einfach als normaler Text im Seiteneditor eingefügt. Verwenden Sie dieses Werkzeug um diese einfach zu generieren!</p><hr>";
                    echo "<table class=\"form-table manage-table\">";
                    echo "<tbody>";
                    echo "<tr valign=\"top\"><th scope=\"row\"><strong>Shortcode</strong></th><td><input type=\"text\" readonly name=\"sc\" value='[" . $_POST['typ'] . " " . ($_POST['typ'] == "stunden" ? "angebot" : "titel") . "=\"" . ($_POST['value'] == "eigenes" ? $_POST['ovalue'] : $_POST['value']) . "\"]'></td></tr>";
                    echo "</tbody></table></div>";
                } elseif ($_POST['typ'] == "stunden") {
                    echo "<div class=\"manage-controls\"><h4>Shortcode-Generator</h4><p>Um das Reitbuch in eine Seite einzubinden werden sog. Shortcodes verwendet - diese werden einfach als normaler Text im Seiteneditor eingefügt. Verwenden Sie dieses Werkzeug um diese einfach zu generieren!</p><hr>";
                    echo "<form method=\"post\" action=\"\"><input type=\"hidden\" name=\"action\" value=\"shortcode\"><input type=\"hidden\" name=\"typ\" value=\"stunden\"><table class=\"form-table manage-table\">";
                    echo "<tbody>";
                    echo "<tr valign=\"top\"><th scope=\"row\"><strong>Angebot:</strong></th><td><select name=\"value\" id=\"value\">";
                    echo "<option value=\"1\">Ponyführstunde</option>";
                    echo "<option value=\"2\">Shettyreitstunde</option>";
                    echo "<option value=\"3\">Gruppenreitstunde</option>";
                    echo "<option value=\"4\">Erwachsenenreitstunde</option>";
                    echo "<option value=\"5\">Pferdezeit</option>";
                    echo "<option value=\"7\">Voltigierstunden</option>";
                    echo "</select></td></tr>";
                    echo "<tr valign=\"top\"><th scope=\"row\" class=\"btmrow\"><input type=\"submit\" class=\"button button-primary\" value=\"Weiter\"></th></tr>";
                    echo "</tbody></table></form></div>";
                } elseif ($_POST['typ'] == "ferienkurs") {
                    echo "<div class=\"manage-controls\"><h4>Shortcode-Generator</h4><p>Um das Reitbuch in eine Seite einzubinden werden sog. Shortcodes verwendet - diese werden einfach als normaler Text im Seiteneditor eingefügt. Verwenden Sie dieses Werkzeug um diese einfach zu generieren!</p><hr>";
                    echo "<form method=\"post\" action=\"\"><input type=\"hidden\" name=\"action\" value=\"shortcode\"><input type=\"hidden\" name=\"typ\" value=\"stunden\"><table class=\"form-table manage-table\">";
                    echo "<tbody>";
                    echo "<tr valign=\"top\"><th scope=\"row\"><strong>Ferienkurs:</strong></th><td><select name=\"value\" id=\"value\">";
                    foreach ($wpdb->get_results("SELECT DISTINCT TITEL FROM $db_ferientermine ORDER BY TITEL") as $key => $row) {
                        echo "<option>" . $row->TITEL . "</option>";
                    }
                    echo "<option>eigenes</option>";
                    echo "</select></td></tr>";
                    echo "<tr valign=\"top\"><th scope=\"row\"><strong>Eigener Titel</strong></th><td><input type=\"text\" name=\"ovalue\"></td></tr>";
                    echo "<tr valign=\"top\"><th scope=\"row\" class=\"btmrow\"><input type=\"submit\" class=\"button button-primary\" value=\"Weiter\"></th></tr>";
                    echo "</tbody></table></form></div>";
                }
            } else {
                echo "<div class=\"manage-controls\"><h4>Shortcode-Generator</h4><p>Um das Reitbuch in eine Seite einzubinden werden sog. Shortcodes verwendet - diese werden einfach als normaler Text im Seiteneditor eingefügt. Verwenden Sie dieses Werkzeug um diese einfach zu generieren!</p><hr>";
                echo "<table class=\"form-table manage-table\">";
                echo "<tbody>";
                echo "<tr valign=\"top\"><th scope=\"row\"><strong>Shortcode</strong></th><td><input type=\"text\" readonly name=\"sc\" value=\"[" . $_POST['typ'] . "]\"></td></tr>";
                echo "</tbody></table></div>";
            }
            break;

        case "horses":
            echo '<table class="form-table"><thead><tr><th colspan="1" class="manage-title"><h3>Pferde</h3><p><small>Diese Funktion wird noch nicht aktiv verwendet!</small></p></th></tr>';
            echo "<tr><th class=\"mctools-th\"><div class=\"manage-controls mctop mctools-div\"><a href=\"?page=mb-options-menu&action=addpf\" class=\"button button-primary\">Hinzufügen</a></div></th></tr>";
            echo "</thead><tbody>";
            //ID INT UNSIGNED NOT NULL AUTO_INCREMENT, NAME VARCHAR(50), LEVEL FLOAT, LINKURL VARCHAR(99), GEBURT DATE
            foreach ($wpdb->get_results("SELECT ID, NAME, LEVEL FROM $pfname ORDER BY NAME") as $key => $row) {
                echo "<tr><td><div class=\"manage-controls manage-table\"><table><tr><td><p><a href=\"?page=mb-options-menu&action=editpf&id=" . $row->ID . "\">" . $row->NAME . "</a><br><small>Schwierigkeitsgrad " . $row->LEVEL . "</small></p></td>";
                echo "</table></div></td></tr>";
            }
            echo "</tbody></table>";
            break;
        case "addpf":
            echo "<div class=\"manage-controls\"><form method=\"post\" action=\"\"><input type=\"hidden\" name=\"action\" value=\"addpf\"><table class=\"form-table manage-table\">";
            echo "<tbody>";
            echo "<tr valign=\"top\"><th scope=\"row\"><strong>Name</strong></th><td><input type=\"text\" pattern=\".{5,}\" required name=\"pfname\"></td></tr>";
            echo "<tr valign=\"top\"><th scope=\"row\"><strong>Schwierigkeit</strong></th><td><input type=\"range\" min=\"0\" max=\"10\" step=\"1\" name=\"lvl\"></td></tr>";
            echo "<tr valign=\"top\"><th scope=\"row\"><strong>Link-URL</strong></th><td><input type=\"text\" name=\"linkurl\"></td></tr>";
            echo "<tr valign=\"top\"><th scope=\"row\"><strong>Geburtsdatum</strong></th><td><input type=\"date\" required name=\"birth\" value=\"" . date("Y-m-d") . "\"></td></tr>";
            echo "<tr valign=\"top\"><th scope=\"row\" class=\"btmrow\"><input type=\"submit\" class=\"button button-primary\" value=\"Hinzufügen\"></th></tr>";
            echo "</tbody></table></form></div>";
            break;
        case "POST_addpf":
            if ($wpdb->insert($pfname, array( 'NAME' => $_POST['pfname'], 'LEVEL' => $_POST['lvl'], 'GEBURT' => $_POST['birth'], 'LINKURL' => $_POST['linkurl']), array('%s', '%d', '%s', '%s')) !== false) {
                echo "<div class=\"manage-controls mcok\"><p>Das Pferd #$wpdb->insert_id wurde erstellt - <a href=\"?page=mb-options-menu&action=horses\">zur Übersicht</a></p></div>";
            } else {
                echo "<div class=\"manage-controls mcerr\"><p>Fehler: Das Pferd konnte nicht erstellt werden!</p></div>";
            }
            break;
        case "editpf":
            if (!isset($_GET['id'])) {
                echo "Keine ID angegeben!";
                break;
            }

            $id = $_GET['id'];

            $row = $wpdb->get_row("SELECT NAME, LEVEL, GEBURT, LINKURL FROM $pfname WHERE ID = $id");

            echo "<div class=\"manage-controls\"><form method=\"post\" action=\"\"><input type=\"hidden\" name=\"action\" value=\"addpf\"><table class=\"form-table manage-table\">";
            echo "<tbody>";
            echo "<tr valign=\"top\"><th scope=\"row\"><strong>Name</strong></th><td><input type=\"text\" pattern=\".{5,}\" required name=\"pfname\" value=\"" . $row->NAME . "\"></td></tr>";
            echo "<tr valign=\"top\"><th scope=\"row\"><strong>Schwierigkeit</strong></th><td><input type=\"range\" min=\"0\" max=\"10\" step=\"1\" name=\"lvl\" value=\"" . $row->LEVEL . "\"></td></tr>";
            echo "<tr valign=\"top\"><th scope=\"row\"><strong>Link-URL</strong></th><td><input type=\"text\" name=\"linkurl\" value=\"" . $row->LINKURL . "\"></td></tr>";
            echo "<tr valign=\"top\"><th scope=\"row\"><strong>Geburtsdatum</strong></th><td><input type=\"date\" required name=\"birth\" value=\"" . date("Y-m-d", strtotime($row->GEBURT)) . "\"></td></tr>";
            echo "<tr valign=\"top\"><th scope=\"row\" class=\"btmrow\"><input type=\"submit\" class=\"button button-primary\" value=\"Bearbeiten\"><a href=\"?page=mb-options-menu&action=deletepf&id=$id\" class=\"button button-warn\">Löschen</a></th></tr>";
            echo "</tbody></table></form></div>";
            break;
        case "POST_editpf":
            if ($wpdb->update($pfname, array( 'NAME' => $_POST['pfname'], 'LEVEL' => $_POST['lvl'], 'GEBURT' => $_POST['birth'], 'LINKURL' => $_POST['linkurl']), array('ID' => $_POST['id']), array('%s', '%d', '%s', '%s'), array('%d')) !== false) {
                echo "<div class=\"manage-controls mcok\"><p>Das Pferd wurde aktualisiert - <a href=\"?page=mb-options-menu&action=horses\">zur Übersicht</a></p></div>";
            } else {
                echo "<div class=\"manage-controls mcerr\"><p>Fehler: Das Pferd konnte nicht aktualisiert werden!</p></div>";
            }
            break;
        case "deletepf":
            if (!isset($_GET['id'])) {
                echo "Fehlerhafte Anfrage: Keine zu bearbeitende ID angegeben!<br><a href='?page=mb-options-menu&action=horses'>Startseite</a>";
                return;
            }
            $id = $_GET['id'];

            $row = $wpdb->get_row("SELECT NAME FROM $pfname WHERE ID = $id");

            echo "<div class=\"manage-controls\"><p>Möchten Sie \"" . $row->NAME . "\" (#$id) wirklich löschen?</p><form method=\"post\" action=\"\"><input type=\"hidden\" name=\"action\" value=\"deletepf\"><input type=\"hidden\" name=\"id\" value=\"$id\">";
            echo "<div class=\"del-btns\"><a href=\"?page=mb-options-menu&action=editpf&id=$id\" class=\"button button-primary\">Abbrechen</a><input type=\"submit\" class=\"button button-warn\" value=\"Löschen\"></div>";
            echo "</form></div>";
            break;
        case "POST_deletepf":
            if (!isset($_POST['id'])) {
                echo "Fehlerhafte Anfrage: Keine zu bearbeitende ID angegeben!<br><a href='?page=mb-options-menu&action=horses'>Startseite</a>";
                return;
            }
            if ($wpdb->delete($pfname, array( 'ID' => $_POST['id']), array('%d')) !== false) {
                echo "<div class=\"manage-controls mcok\"><p>Das Pferd #" . $_POST['id'] . " wurde gelöscht - <a href=\"?page=mb-options-menu&action=horses\">zur Übersicht</a></p></div>";
            } else {
                echo "<div class=\"manage-controls mcerr\"><p>Fehler: Das Pferd konnte nicht gelöscht werden!</p></div>";
            }
            break;
    }

    echo "</div></div>";
}
