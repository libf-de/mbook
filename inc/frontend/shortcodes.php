<?php

function get_detail_html($kurse, $description, $withState = TRUE)
{
    if($kurse == null) return "nonono";
    if(empty($kurse)) return "- findet aktuell nicht statt -";

    $ret = "<p class=\"ws-fpr-description\">";
    $single_kurs = array_shift($kurse);
    if (empty($kurse)) {
        if ($single_kurs->IS_OPEN_END) {
            /*** Ein Kurs, open end ***
             *Am Montag, den 01.01. findet ab 10:00 Uhr .... */
            $ret .= str_replace(
                array("%am", "%findet"),
                array("Am " . formatDateLongGerman($single_kurs->DATESTART, false), "findet ab " . $single_kurs->DATESTART->format("H:i") . " Uhr"),
                $description
            );
        } elseif (($single_kurs->DATESTART->format("Y-m-d") == $single_kurs->DATEEND->format("Y-m-d"))) {
            /*** Ein Kurs, ein Tag ***
             *Am Montag, den 01.01. findet von 10:00 - 18:00 Uhr .... */
            $ret .= str_replace(
                array("%am", "%findet"),
                array("Am " . formatDateLongGerman($single_kurs->DATESTART, false), "findet von " . $single_kurs->DATESTART->format("H:i") . " bis " . $single_kurs->DATEEND->format("H:i") . " Uhr"),
                $description
            );
        } else {
            /*** Ein Kurs, mehrere Tage ***
             *Von Montag, den 01.01. 10:00 Uhr
             *bis Dienstag, den 02.02. 18:00 Uhr
             *
             *findet ein.... */
            $ret .= str_replace(
                array("%am", "%findet"),
                array("Von " . formatDateLongGerman($single_kurs->DATESTART, true) . "<br>bis " . formatDateLongGerman($single_kurs->DATEEND, true) . "<br><br>", "findet"),
                $description
            );
        }
        $ret .= "</p>";
        if($withState) $ret .= "<div class=\"ws-fpr-states\">" . courseState($single_kurs, 3, false) . "</div>";
    } else {
        $stat = compareKurse($single_kurs, $kurse);
        if ($stat == array(true, true)) {
            /*** Mehrere Kurse, ein Tag, eine Zeit oder alle OpenEnd ***
             *Am Montag, den 01.01.,
             *sowie
             *am Montag, den 08.01.,
             *und
             *am Montag, den 16.01.
             *
             *findet jeweils von 10:00 bis 18:00 Uhr ein.... */
            $am_repl = "Am " . formatDateLongGerman($single_kurs->DATESTART, false) . ",<br>sowie<br>";
            $post = courseState($single_kurs, 3, true);
            foreach ($kurse as $further_kurs) {
                $am_repl .= "am " . formatDateLongGerman($further_kurs->DATESTART, false) . (next($kurse) === false ? "<br><br>" : ",<br>und<br>");
                $post .= courseState($further_kurs, 3, true);
            }
            $ret .= str_replace(
                array("%am", "%findet"),
                $single_kurs->IS_OPEN_END
                  ? array($am_repl, "findet jeweils ab " . $single_kurs->DATESTART->format("H:i") . " Uhr")
                  : array($am_repl, "findet jeweils von " . $single_kurs->DATESTART->format("H:i") . " bis " . $single_kurs->DATEEND->format("H:i") . " Uhr"),
                $description
            );
        } elseif ($stat == array(true, false)) {
            /*** Mehrere Kurse, ein Tag, mehrere Zeiten: ***
             *Am Montag, den 01.01. von 10:00 - 18:00 Uhr,
             *sowie
             *am Montag, den 08.01. von 11:00 - 19:00 Uhr,
             *und
             *am Montag, den 16.01. von 12:00 - 20:00 Uhr
             *
             *findet ein.... */
            $am_repl = "Am " . formatDateLongGerman($single_kurs->DATESTART, false) . " von " . $single_kurs->DATESTART->format("H:i - ") . $single_kurs->DATEEND->format("H:i") . " Uhr,<br>sowie<br>";
            $post = courseState($single_kurs, 3, true);
            foreach ($kurse as $further_kurs) {
                $am_repl .= "am " . formatDateLongGerman($further_kurs->DATESTART, false) . " von " . $further_kurs->DATESTART->format(" H:i - ") . $further_kurs->DATEEND->format("H:i") . (next($kurse) === false ? " Uhr<br><br>" : " Uhr,<br>und<br>");
                $post .= courseState($further_kurs, 3, true);
            }
            $ret .= str_replace(
                array("%am", "%findet"),
                array($am_repl, "findet"),
                $description
            ) . $post;
        } else {
            /*** Mehrere Kurse, mehrere Tage: ***
             *Von Mo, 01.01. 10:00 Uhr - Di, 02.01. 18:00 Uhr
             *sowie
             *von Mo, 08.01. 11:00 Uhr - Di, 09.01. 18:00 Uhr
             *
             *findet ein.... */
            $am_repl = "Von " . formatKursShortGerman($single_kurs, true) . ",<br>sowie<br>";
            $post = courseState($single_kurs, 3, true);
            foreach ($kurse as $further_kurs) {
                $am_repl .= "von " . formatKursShortGerman($further_kurs, true) . (next($kurse) === false ? "<br><br>" : ",<br>und<br>");
                $post .= courseState($further_kurs, 3, true);
            }
            $ret .= str_replace(
                array("%am", "%findet"),
                array($am_repl, "findet"),
                $description
            ) . $post;
        }
        $ret .= "</p>";
        if($withState) $ret .= "<div class=\"ws-fpr-states\">" . $post . "</div>";
    }
    return $ret;
}

function handle_user_templates()
{
    global $wpdb;
    $template = db_ferientemplates;
    $termin = db_ferientermine;
    $ferien = db_ferien;
    wp_enqueue_style('sc-ddtemplates');
    wp_enqueue_script("jquery");
    wp_enqueue_script('sc-ddtemplates');
    wp_enqueue_style('fa');
    wp_enqueue_style('fa-solid');
    include(__DIR__ . "/views/shortcode-ddtemplates.php");
}

function handle_user_ferienkurs_details()
{
    wp_enqueue_script("jquery");
    wp_enqueue_script('mbuserjs');

    global $wpdb;
    $ret = '';
    setlocale(LC_ALL, 'de_DE@euro');
    $template = db_ferientemplates;
    $termin = db_ferientermine;
    $ferien = db_ferien;

    $dapp = get_option('ferien_following') == 'TRUE' ? "AND `$termin`.`DATESTART` >= CURDATE() " : "";

    if (isset($_GET['id'])) {
        $single_kurs = convertKursDT($wpdb->get_row("SELECT `$termin`.*, `$template`.TITLE, `$template`.DESCRIPTION, `$template`.LINKURL FROM `$termin` INNER JOIN `$template` ON `$termin`.`TEMPLATE` = `$template`.`ID` WHERE `$termin`.ID = " . $_GET["id"] . " $dapp"));
        if ($single_kurs == null) {
            return '<p>Angegebener Kurs wurde nicht gefunden!</p>';
        }
        $kurse = convertKursDT($wpdb->get_results("SELECT `$termin`.ID, `$termin`.TEMPLATE, `$termin`.PARTICIPANTS, `$termin`.MAX_PARTICIPANTS, `$termin`.FERIEN, `$termin`.DATESTART, `$termin`.DATEEND, `$termin`.IS_OPEN_END, `$termin`.IS_CANCELLED FROM `$termin` WHERE `$termin`.TEMPLATE = " . $single_kurs->TEMPLATE . " AND `$termin`.FERIEN = " . $single_kurs->FERIEN . " AND `$termin`.ID <> " . $single_kurs->ID . " ORDER BY `$termin`.DATESTART $dapp"));
    } elseif (isset($_GET['t'])) {
        $kurse = convertKursDT($wpdb->get_results("SELECT `$termin`.*, `$template`.TITLE, `$template`.DESCRIPTION, `$template`.LINKURL FROM `$termin` INNER JOIN `$template` ON `$termin`.`TEMPLATE` = `$template`.`ID` WHERE `$termin`.TEMPLATE = " . $_GET["t"] . " $dapp"));
        if ($kurse == null) {
            return '<p>Angegebener Kurs wurde nicht gefunden!</p>';
        }
        $single_kurs = array_shift($kurse);
    }

    $ret = '<h2><a href="?main" style="text-decoration: none !important; box-shadow: none;">&#x2B05;&nbsp;</a>' . $single_kurs->TITLE . '</h2>';

    //$ret .= print_r($single_kurs, true) . "<br><br>";

    if (empty($kurse)) {
        if ($single_kurs->IS_OPEN_END) {
            /*** Ein Kurs, open end ***
             *Am Montag, den 01.01. findet ab 10:00 Uhr .... */
            $ret .= str_replace(
                array("%am", "%findet"),
                array("Am " . formatDateLongGerman($single_kurs->DATESTART, false), "findet ab " . $single_kurs->DATESTART->format("H:i") . " Uhr"),
                $single_kurs->DESCRIPTION
            );
        } elseif (($single_kurs->DATESTART->format("Y-m-d") == $single_kurs->DATEEND->format("Y-m-d"))) {
            /*** Ein Kurs, ein Tag ***
             *Am Montag, den 01.01. findet von 10:00 - 18:00 Uhr .... */
            $ret .= str_replace(
                array("%am", "%findet"),
                array("Am " . formatDateLongGerman($single_kurs->DATESTART, false), "findet von " . $single_kurs->DATESTART->format("H:i") . " bis " . $single_kurs->DATEEND->format("H:i") . " Uhr"),
                $single_kurs->DESCRIPTION
            );
        } else {
            /*** Ein Kurs, mehrere Tage ***
             *Von Montag, den 01.01. 10:00 Uhr
             *bis Dienstag, den 02.02. 18:00 Uhr
             *
             *findet ein.... */
            $ret .= str_replace(
                array("%am", "%findet"),
                array("Von " . formatDateLongGerman($single_kurs->DATESTART, true) . "<br>bis " . formatDateLongGerman($single_kurs->DATEEND, true) . "<br><br>", "findet"),
                $single_kurs->DESCRIPTION
            );

            /*  kp wohin es muss
            echo "<div class=\"qty btns_added\"><input type=\"button\" value=\"-\" class=\"minus fk-list-btns\">";
      echo "<input class=\"fk-list-parts input-text qt text\" type=\"number\" data-id=\"" . $row->ID . "\" id=\"parts" . $row->ID . "\" min=\"-1\" max=\"" . $row->MAX_PARTICIPANTS . "\" value=\"" . $row->PARTICIPANTS . "\" title=\"Qty\" size=\"5\" pattern=\"\" inputmode=\"\">";
      echo "<input type=\"button\" value=\"+\" class=\"plus fk-list-btns\"></div>";
            */
        }
        $ret .= "<div class=\"ws-fpr-states\">" . courseState($single_kurs, 3, false) . "</div>";
    } else {
        $stat = compareKurse($single_kurs, $kurse);
        if ($stat == array(true, true)) {
            /*** Mehrere Kurse, ein Tag, eine Zeit oder alle OpenEnd ***
             *Am Montag, den 01.01.,
             *sowie
             *am Montag, den 08.01.,
             *und
             *am Montag, den 16.01.
             *
             *findet jeweils von 10:00 bis 18:00 Uhr ein.... */
            $am_repl = "Am " . formatDateLongGerman($single_kurs->DATESTART, false) . ",<br>sowie<br>";
            $post = courseState($single_kurs, 3, true);
            foreach ($kurse as $further_kurs) {
                $am_repl .= "am " . formatDateLongGerman($further_kurs->DATESTART, false) . (next($kurse) === false ? "<br><br>" : ",<br>und<br>");
                $post .= courseState($further_kurs, 3, true);
            }
            $ret .= str_replace(
                array("%am", "%findet"),
                $single_kurs->IS_OPEN_END
                  ? array($am_repl, "findet jeweils ab " . $single_kurs->DATESTART->format("H:i") . " Uhr")
                  : array($am_repl, "findet jeweils von " . $single_kurs->DATESTART->format("H:i") . " bis " . $single_kurs->DATEEND->format("H:i") . " Uhr"),
                $single_kurs->DESCRIPTION
            );
        } elseif ($stat == array(true, false)) {
            /*** Mehrere Kurse, ein Tag, mehrere Zeiten: ***
             *Am Montag, den 01.01. von 10:00 - 18:00 Uhr,
             *sowie
             *am Montag, den 08.01. von 11:00 - 19:00 Uhr,
             *und
             *am Montag, den 16.01. von 12:00 - 20:00 Uhr
             *
             *findet ein.... */
            $am_repl = "Am " . formatDateLongGerman($single_kurs->DATESTART, false) . " von " . $single_kurs->DATESTART->format("H:i - ") . $single_kurs->DATEEND->format("H:i") . " Uhr,<br>sowie<br>";
            $post = courseState($single_kurs, 3, true);
            foreach ($kurse as $further_kurs) {
                $am_repl .= "am " . formatDateLongGerman($further_kurs->DATESTART, false) . " von " . $further_kurs->DATESTART->format(" H:i - ") . $further_kurs->DATEEND->format("H:i") . (next($kurse) === false ? " Uhr<br><br>" : " Uhr,<br>und<br>");
                $post .= courseState($further_kurs, 3, true);
            }
            $ret .= str_replace(
                array("%am", "%findet"),
                array($am_repl, "findet"),
                $single_kurs->DESCRIPTION
            ) . $post;
        } else {
            /*** Mehrere Kurse, mehrere Tage: ***
             *Von Mo, 01.01. 10:00 Uhr - Di, 02.01. 18:00 Uhr
             *sowie
             *von Mo, 08.01. 11:00 Uhr - Di, 09.01. 18:00 Uhr
             *
             *findet ein.... */
            $am_repl = "Von " . formatKursShortGerman($single_kurs, true) . ",<br>sowie<br>";
            $post = courseState($single_kurs, 3, true);
            foreach ($kurse as $further_kurs) {
                $am_repl .= "von " . formatKursShortGerman($further_kurs, true) . (next($kurse) === false ? "<br><br>" : ",<br>und<br>");
                $post .= courseState($further_kurs, 3, true);
            }
            $ret .= str_replace(
                array("%am", "%findet"),
                array($am_repl, "findet"),
                $single_kurs->DESCRIPTION
            ) . $post;
        }
        $ret .= "<div class=\"ws-fpr-states\">" . $post . "</div>";
    }
    $ret .= get_pfooter();

    $ret .= "<script type=\"text/javascript\" defer>jQuery(document).ready(function($) { initBooking(); });</script>";
    return $ret;
}

function handle_user_categorytable()
{
    wp_enqueue_style('notitle', plugins_url('/assets/css/notitle.css', __FILE__));
    //TODO: js-ify display of kurs-details
    if (isset($_GET['id']) || isset($_GET["t"])) {
        return handle_user_ferienkurs_details();
    }

    global $wpdb;
    $ret = '';
    setlocale(LC_ALL, 'de_DE@euro');
    $template = db_ferientemplates;
    $termin = db_ferientermine;
    $ferien = db_ferien;

    $ferien_filter = isset($_GET['f']) ? $_GET['f'] : "1"; //TODO: Somehow store currently default Ferien somewhere
    $ferien_title = $wpdb->get_var($wpdb->prepare("SELECT LABEL FROM " . db_ferien . " WHERE FID = %d LIMIT 1", $ferien_filter . "%"));

    $cfg_titel = get_option('ferientitel');
    $ret .= "<h2>" . ($ferien_title != "default" ? $ferien_title : "Ferienprogramm") . "</h2>";
    $ret .= "<p style=\"margin: 0 !important\">Termine für...</p>";

    $ret .= '<table class="form-table">';
    $dapp = get_option('ferien_following') == 'TRUE' ? "AND DATESTART >= CURDATE()" : "";
    //$arr = $wpdb->get_results("SELECT DISTINCT TITEL FROM $db_ferientermine WHERE KDATUM >= CURDATE()");
    $res = $wpdb->get_results("SELECT * FROM `$template` WHERE ID IN (SELECT TEMPLATE FROM `$termin` WHERE FERIEN = $ferien_filter $dapp) ORDER BY TITLE");

    $ret .= "<tbody class=\"ws-table-content\">";
    foreach ($res as $key => $row) {
        $ret .= sprintf(
            "<tr class=\"%s\"><td><p class=\"ws-fp-title\"><a href=\"?t=%d\">%s</a></p></td></tr>",
            (next($res) ? "" : "ws-last"),
            $row->ID,
            $row->TITLE
        );
    }

    if (empty($res)) {
        $ret .= "<tr class=\"ws-last\" colspan=\"2\"><td><p class=\"ws-std-title\">Keine Stunden</p></td></tr>";
    }

    $ret .= "</tbody>";
    $ret .= "</table>";
    $ret .= get_pfooter();
    return $ret;
}

function handle_user_ferientable()
{
    wp_enqueue_style('notitle', plugins_url('/assets/css/notitle.css', __FILE__));

    if (isset($_GET['id']) || isset($_GET["t"])) {
        return handle_user_ferienkurs_details();
    }

    global $wpdb;
    $ret = '';
    setlocale(LC_ALL, 'de_DE@euro');
    $template = db_ferientemplates;
    $termin = db_ferientermine;
    $ferien = db_ferien;
    $free_slots = "%free% von %total% Plätzen frei";

    $cfg_titel = get_option('ferientitel');
    $ret .= "<h2>" . (strlen($cfg_titel) > 5 ? $cfg_titel : "Ferienprogramm") . "</h2>";

    $ret .= '<table class="form-table">';
    $dapp = "";
    $dapp .= get_option('ferien_following') == 'TRUE' ? "WHERE `$termin`.`DATESTART` >= CURDATE() " : "";

    //Level Filter
    if (isset($_GET["l"])) {
        if (is_numeric($_GET["l"])) {
            $dapp .= ($dapp != "" ? "AND " : "WHERE ") . $_GET["l"] . " >= `$template`.EXP_LEVEL_MIN AND " . $_GET["l"] . " <= `$template`.EXP_LEVEL_MAX ";
        }
    }

    //Ferien filter (currently unused?)
    if (isset($_GET["f"])) {
        if (is_numeric($_GET["f"])) {
            $dapp .= ($dapp != "" ? "AND " : "WHERE ") . "`$termin`.FERIEN = " . $_GET["f"] . " ";
        }
    }

    $res = $wpdb->get_results("SELECT `$termin`.*, `$template`.TITLE, `$template`.EXP_LEVEL_MIN, `$template`.EXP_LEVEL_MAX FROM `$termin` INNER JOIN `$template` ON `$termin`.`TEMPLATE` = `$template`.`ID` $dapp ORDER BY `$termin`.`DATESTART`");

    $PREVDATE = null;

    foreach ($res as $key => $row) {
        $startDate = DateTime::createFromFormat(mysql_date, $row->DATESTART);
        $endDate = DateTime::createFromFormat(mysql_date, $row->DATEEND);

        if ($PREVDATE != $startDate->format('Y-m-d')) {
            //$ret .= "<thead><tr><th colspan=\"2\" class=\"ws-header\">" . $TNAME[date("N", $KDM)] . ", " . date("d", $KDM) . ". " . $MNAME[date("n", $KDM)] . ". " . date("Y", $KDM) . "</th></tr></thead><tbody class=\"ws-table-content\">";
            $ret .= "<thead><tr><th colspan=\"2\" class=\"ws-header\">";
            $ret .= weekday_names_short[$startDate->format("N")] . $startDate->format(", d. ");
            $ret .= month_names_short[$startDate->format("n")] . $startDate->format(" Y");
            $ret .= "</th></tr></thead><tbody class=\"ws-table-content\">";
            $PREVDATE = $startDate->format('Y-m-d');
        }

        $ret .= sprintf(
            "<tr class=\"%s\"><td><p class=\"ws-fp-title\"><a href=\"?id=%d\">%s</a></p><small>",
            (next($res) ? "ws-row" : "ws-last"),
            $row->ID,
            $row->TITLE
        );

        /* kp wohin
        echo "<div class=\"fktermine-inner-modify\">";
        echo "<a class=\"button button-primary fk-list-edit\"><i class=\"fa-solid fa-pen\"></i></a><a class=\"button button-warn\" href=\"\"><i class=\"fa-solid fa-trash-can\"></i></a>";
        echo "</div>";*/

        if ($row->IS_OPEN_END) {
            //Open end (ab 9:00 Uhr)
            $ret .= "ab " . $startDate->format("G:i") . " Uhr";
        } elseif ($startDate->format("Y-m-d") == $endDate->format("Y-m-d")) {
            //Single day, show time only //TODO: Show leading zero? (->H statt G)
            $ret .= $startDate->format("G:i") . " &ndash; " . $endDate->format("G:i") . " Uhr";
        } else {
            //Multiple days, show time+date //TODO: Show year?
            $ret .= $startDate->format("d.m. G:i") . " &ndash; " . $endDate->format("d.m. G:i");
        }

        $ret .= "</small></td><td>";
        $ret .= courseState($row, 1, false);
        //$ret .= "<input type=\"text\" value=\"" . $OVT . "\" title=\"Qty\" readonly class=\"ws-std-state $OVC\" size=\"5\">";
        $ret .= "</td></tr>";
    }
    echo "</tbody></table><script>initToggles();</script>"; //Close outer table, initialize Participants input javascript
}
