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
        $action = "fkurs-manage";
    }

    echo "<div class=\"wrap\"><h2>Ferienprogramm verwalten <nobr>&ndash; nuBook</nobr></h2><h2 class=\"nav-tab-wrapper\">";
    #echo "<a href=\"?page=mb-options-menu&action=manage\" class=\"nav-tab " .  ($action == 'manage' ? 'nav-tab-active' : '') . "\">Unterricht</a>";
    echo "<a href=\"?page=mb-options-menu&action=ferien\" class=\"nav-tab " . (startsWith($action, 'ferien') ? $activeClass : '') . "\">Ferien</a>";
    echo "<a href=\"?page=mb-options-menu&action=fkurs-manage\" class=\"nav-tab " . (startsWith($action,'fkurs') ? $activeClass : '') . "\">Ferienkurse</a>";
    echo "<a href=\"?page=mb-options-menu&action=fktemplates\" class=\"nav-tab " . (startsWith($action, 'fktemplates') ? $activeClass : '') . "\">Ferienkurs-Vorlagen</a>";
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
    }
    echo "</div>";
}

function mb_options()
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

    echo "<div class=\"wrap\"><h2>Reitbuch-Einstellungen</h2><h2 class=\"nav-tab-wrapper\"><a href=\"?page=mb-options-menu&action=manage\" class=\"nav-tab ";
    echo $action == 'manage' ? 'nav-tab-active' : '';
    echo "\">Unterricht</a><a href=\"?page=mb-options-menu&action=ferien\" class=\"nav-tab ";
    echo $action == 'ferien' ? 'nav-tab-active' : '';
    echo "\">Ferien</a><a href=\"?page=mb-options-menu&action=fkurs-manage\" class=\"nav-tab ";
    echo $action == 'fkurs-manage' ? 'nav-tab-active' : '';
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
        case "config": //TODO: Load nubook.legacy.config.css!!
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
            echo "<tr valign=\"top\"><th scope=\"row\" class=\"form-table-btmrow\"><input type=\"submit\" class=\"button button-primary\" value=\"Speichern\"></th></tr>";
            echo "</tbody></table></form></div>";
            break;
    }

    echo "</div></div>";
}
