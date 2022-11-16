<?php
    $ret = '';
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
            $ret .= weekday_names_short[$startDate->format("N")-1] . $startDate->format(", d. ");
            $ret .= month_names_short[$startDate->format("n")-1] . $startDate->format(" Y");
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
        echo "<div class=\"nb-listelem-inner-modify\">";
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
    
    $ret .= "</tbody></table><script>initToggles();</script>"; //Close outer table, initialize Participants input javascript
    return $ret;