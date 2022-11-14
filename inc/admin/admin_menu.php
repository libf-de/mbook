<?php

function nb_options_lessons()
{
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    load_common_scripts();

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
    echo "<a href=\"?page=nb-options-lessons&action=lessons\" class=\"nav-tab " . ($action == 'lessons' ? $activeClass : '') . "\">Unterrichtsstunden</a>";
    echo "<a href=\"?page=nb-options-lessons&action=lstemplates\" class=\"nav-tab " . ($action == 'lstemplates' ? $activeClass : '') .  "\">Unterrichts-Vorlagen</a>";
    /*echo "<a href=\"?page=nb-options-lessons&action=config\" class=\"nav-tab " . ($action == 'config' ? 'nav-tab-active' : '') .  "\">Konfiguration</a>";
    echo "<a href=\"?page=nb-options-lessons&action=shortcode\" class=\"nav-tab " . ($action == 'shortcode' ? 'nav-tab-active' : '') . "\">Kurzcodes</a>";*/
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

function nb_options_ferien()
{
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    load_common_scripts();

    if (isset($_POST['action']) && $_POST['action'] != "shortcode") {
        die("do not POST to this url!");
    }

    $activeClass = isset($_GET['msgcol']) ? "nav-tab-active bg-color-" . preg_replace("/[^A-Za-z0-9#]/", '', urldecode($_GET['msgcol'])) : "nav-tab-active ";

    if (isset($_POST['action'])) {
        $action = "POST_" . $_POST['action'];
    } elseif (isset($_GET['action'])) {
        $action = $_GET['action'];
    } else {
        $action = "fkurs-manage";
    }

    echo "<div class=\"wrap\"><h2>Ferienprogramm verwalten <nobr>&ndash; nuBook</nobr></h2><h2 class=\"nav-tab-wrapper\">";
    #echo "<a href=\"?page=nb-options-menu&action=manage\" class=\"nav-tab " .  ($action == 'manage' ? 'nav-tab-active' : '') . "\">Unterricht</a>";
    echo "<a href=\"?page=nb-options-menu&action=ferien\" class=\"nav-tab " . (str_starts_with($action, 'ferien') ? $activeClass : '') . "\">Ferien</a>";
    echo "<a href=\"?page=nb-options-menu&action=fkurs-manage\" class=\"nav-tab " . (str_starts_with($action, 'fkurs') ? $activeClass : '') . "\">Ferienkurse</a>";
    echo "<a href=\"?page=nb-options-menu&action=fktemplates\" class=\"nav-tab " . (str_starts_with($action, 'fktemplates') ? $activeClass : '') . "\">Ferienkurs-Vorlagen</a>";
    echo "<a href=\"?page=nb-options-menu&action=config\" class=\"nav-tab " . (($action == 'config' || $action == 'POST_config') ? $activeClass : '') . "\">Konfiguration</a>";
    echo "<a href=\"?page=nb-options-menu&action=shortcode\" class=\"nav-tab " . (($action == 'shortcode' || $action == 'POST_shortcode') ? $activeClass : '') . "\">Kurzcodes</a>";
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
        case "POST_api-set-ft-parts":
            handle_api_ferientermine_parts(); //?
            break;
        case "fkurs-add":
            handle_admin_ferienkurs_add();
            break;
        case "fkurs-clear":
            handle_admin_ferienkurs_clean();
            break;
        case "fkurs-copy":
            handle_admin_ferienkurs_copy();
            break;
        case "fkurs-copy-prv":
            handle_admin_ferienkurs_copy_preview();
            break;
        case "fkurs-manage":
        default:
            handle_admin_ferienkurs_list();
            break;
        case "config": //TODO: Load nubook.legacy.config.css!!
            include __DIR__ . "/views/legacy_config.php";
            break;
        case "shortcode":
            legacy_shortcode();
            break;
        case "POST_shortcode":
            legacy_shortcode_post();
            break;
    }
    echo "</div>";
}

function load_common_scripts()
{
    wp_enqueue_style('fa');
    wp_enqueue_style('fa-solid');
    wp_enqueue_script("jquery");
    wp_enqueue_style('jqueryui');
    wp_enqueue_style('jqueryui-theme');
    wp_enqueue_script('nbadminjs');
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_script('jquery-ui-dialog');
    wp_enqueue_script('jquery-ui-multidate');
    wp_enqueue_style('nb-common-css');
}



function legacy_shortcode()
{
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
    echo "<tr valign=\"top\"><th scope=\"row\" class=\"form-table-btmrow\"><input type=\"submit\" class=\"button button-primary\" value=\"Weiter\"></th></tr>";
    echo "</tbody></table></form><hr>";
    echo "<h4>Erklärung:</h4>";
    echo "<p><strong>Reitbuch</strong><br>Das Reitbuch zeigt die Reitstunden für jeden Wochentag für die nächsten 7 Tage und deren Belegung an<br><br><strong>Reitkalender</strong><br>Der Reitkalender zeigt die Reitstunden und Ferienkurse für die nächsten 7 Tage und deren Belegung an<br><br><strong>Stunden-Kalender</strong><br>Der Stundenkalender zeigt die Reitstunden eines Typs für die nächsten 7 Tage und deren Belegung an<br><br>";
    echo "<strong>Ferienkurs-Kalender</strong><br>Der Ferienkurs-Kalender zeigt alle Termine und deren Belegung für einen Ferienkurs-Typ an<br><br><strong>Ferienprogramm</strong><br>Das Ferienprogramm zeigt alle Ferienkurse und ihre Belegung an</p></div>";
}
function legacy_shortcode_post()
{
    if (!isset($_POST['typ'])) {
        echo "Kein Typ angegeben!";
        return;
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
            echo "<tr valign=\"top\"><th scope=\"row\" class=\"form-table-btmrow\"><input type=\"submit\" class=\"button button-primary\" value=\"Weiter\"></th></tr>";
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
            echo "<tr valign=\"top\"><th scope=\"row\" class=\"form-table-btmrow\"><input type=\"submit\" class=\"button button-primary\" value=\"Weiter\"></th></tr>";
            echo "</tbody></table></form></div>";
        }
    } else {
        echo "<div class=\"manage-controls\"><h4>Shortcode-Generator</h4><p>Um das Reitbuch in eine Seite einzubinden werden sog. Shortcodes verwendet - diese werden einfach als normaler Text im Seiteneditor eingefügt. Verwenden Sie dieses Werkzeug um diese einfach zu generieren!</p><hr>";
        echo "<table class=\"form-table manage-table\">";
        echo "<tbody>";
        echo "<tr valign=\"top\"><th scope=\"row\"><strong>Shortcode</strong></th><td><input type=\"text\" readonly name=\"sc\" value=\"[" . $_POST['typ'] . "]\"></td></tr>";
        echo "</tbody></table></div>";
    }
}

function nb_options()
{
    global $wpdb;
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

    echo "<div class=\"wrap\"><h2>Reitbuch-Einstellungen</h2><h2 class=\"nav-tab-wrapper\"><a href=\"?page=nb-options-menu&action=manage\" class=\"nav-tab ";
    echo $action == 'manage' ? 'nav-tab-active' : '';
    echo "\">Unterricht</a><a href=\"?page=nb-options-menu&action=ferien\" class=\"nav-tab ";
    echo $action == 'ferien' ? 'nav-tab-active' : '';
    echo "\">Ferien</a><a href=\"?page=nb-options-menu&action=fkurs-manage\" class=\"nav-tab ";
    echo $action == 'fkurs-manage' ? 'nav-tab-active' : '';
    echo "\">Ferienkurse</a><a href=\"?page=nb-options-menu&action=fktemplates\" class=\"nav-tab ";
    echo $action == 'fktemplates' ? 'nav-tab-active' : '';
    echo "\">Ferienkurs-Vorlagen</a><a href=\"?page=nb-options-menu&action=config\" class=\"nav-tab ";
    echo ($action == 'config' || $action == 'POST_config') ? 'nav-tab-active' : '';
    echo "\">Konfiguration</a><a href=\"?page=nb-options-menu&action=shortcode\" class=\"nav-tab ";
    echo ($action == 'shortcode' || $action == 'POST_shortcode') ? 'nav-tab-active' : '';
    echo "\">Kurzcodes</a></h2>";

    if (isset($_GET['msg'])) {
        echo "<div class=\"manage-controls\" style=\"color: white; background-color: " . preg_replace("/[^A-Za-z0-9# ]/", '', $_GET['msgcol']) . "\"><p>" . preg_replace("/[^A-Za-z0-9äöüÄÖÜß.\-# ]/", '', urldecode($_GET['msg'])) . "</p></div>";
    }

    echo "<div class=\"settings_page\" style=\"margin-top: 1em;\">";

    switch($action) {
    }

    echo "</div></div>";
}
