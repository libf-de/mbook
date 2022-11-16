<?php

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
$ret .= nb_get_pfooter();

$ret .= "<script type=\"text/javascript\" defer>jQuery(document).ready(function($) { initBooking(); });</script>";
return $ret;
