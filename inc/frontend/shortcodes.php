<?php

/**
 * Returns the description html for given courses [with booking state]
 *
 * @param $kurse array List of stdClass Kurs objects
 * @param $description string the description from template with placeholders
 * @param $withState bool whether to include booking state
 *
 * @return string description html
 *
 * @throws Exception
 */
function get_detail_html( array $kurse, string $description, bool $withState = true): string
{
    if ($kurse == null) {
        return "nonono";
    }
    if (empty($kurse)) {
        return "- findet aktuell nicht statt -";
    }

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
        if ($withState) {
            $ret .= "<div class=\"ws-fpr-states\">" . courseState($single_kurs, 3 ) . "</div>";
        }
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
        if ($withState) {
            $ret .= "<div class=\"ws-fpr-states\">" . $post . "</div>";
        }
    }
    return $ret;
}

/**
 * Returns a collapsible list of templates containing the description and booking states
 * e.g.:
 * [ Springkurs               v ]
 * ______________________________
 * | Tagesreitkurs            ^ |
 * | description.. [12.12. frei]|
 * ------------------------------
 * [ Wanderritt               v ]
 *
 * -- (Shortcode: ftemplates)
 * @return string
 */
function handle_user_templates(): string {
    global $wpdb;
    $template = db_ferientemplates;
    $termin = db_ferientermine;
    $ferien = db_ferien;
    wp_enqueue_style('sc-ddtemplates');
    wp_enqueue_script("jquery");
    wp_enqueue_script('nbuserjs');
    wp_enqueue_script('sc-ddtemplates');
    nb_load_fa();
    return include(__DIR__ . "/views/shortcode-ddtemplates.php");
}

/**
 * Returns a table of lessons grouped per day
 * e.g.:
 * Freitag, den 01.01.2022
 * =====================================
 * Gruppenreitstunde 1         [FREI]
 * 13:30 - 14:30 Uhr
 * -------------------------------------
 * Gruppenreitstunde 2         [FREI]
 * 14:30 - 15:30 Uhr
 *
 * -- (Shortcode: lesson-table)
 * @return string
 */
function handle_user_lessontable(): string
{
    global $wpdb;
    $dbLessons = db_lessons;
    $dbTemplates = db_lessontemplates;

    wp_enqueue_style('sc-ddtemplates');
    wp_enqueue_style('nb-user-lessontable-css');
    wp_enqueue_script("jquery");
    wp_enqueue_script('nbuserjs');
    wp_enqueue_script('sc-ddtemplates');
    nb_load_fa();
    return include(__DIR__ . "/views/shortcode-lessontable.php");
}

function handle_user_ferienkurs_details()
{
    wp_enqueue_script("jquery");
    wp_enqueue_script('nbuserjs');

    global $wpdb;
    $ret = '';
    setlocale(LC_ALL, 'de_DE@euro');
    $template = db_ferientemplates;
    $termin = db_ferientermine;
    $ferien = db_ferien;

    return include(__DIR__ . "/views/shortcode-fkursdetails.php");
}

function handle_user_categorytable()
{
    wp_enqueue_style('notitle', plugins_url('/assets/css/notitle.css', __FILE__));
    //TODO: js-ify display of kurs-details
    if (isset($_GET['id']) || isset($_GET["t"])) {
        return handle_user_ferienkurs_details();
    }

    global $wpdb;
    setlocale(LC_ALL, 'de_DE@euro');
    $template = db_ferientemplates;
    $termin = db_ferientermine;
    $ferien = db_ferien;

    return include(__DIR__ . "/views/shortcode-categorytable.php");
}



function handle_user_ferientable()
{
    nb_load_fa();
    wp_enqueue_style('notitle', plugins_url('/assets/css/notitle.css', __FILE__));

    if (isset($_GET['id']) || isset($_GET["t"])) {
        return handle_user_ferienkurs_details();
    }

    global $wpdb;
    setlocale(LC_ALL, 'de_DE@euro');
    $template = db_ferientemplates;
    $termin = db_ferientermine;
    $ferien = db_ferien;
    return include(__DIR__ . "/views/shortcode-ferientable.php");
}


function handle_user_lesson($atts): string
{
    global $wpdb;
    $dbLessons = db_lessons;
    $dbTemplates = db_lessontemplates;

    wp_enqueue_style("nb-user-lesson-css");

    $ret = '';

    $a = shortcode_atts(array(
        'id' => 1,
        'show_occupation' => 1,
    ), $atts);

    $sqltemplate = $wpdb->get_row($wpdb->prepare("SELECT ID, TITLE FROM `$dbTemplates` WHERE ID = %d", $a['id']));
    if ($sqltemplate == null) {
        return "<h4>ungültiges Angebot :(</h4>";
    }

    $ret .= "<table class=\"form-table\"><thead><tr><th colspan=\"2\">{$sqltemplate->TITLE}</th></tr></thead><tbody>";

    $lsns = array();

    foreach ($wpdb->get_results($wpdb->prepare("SELECT ID, NUM, TEMPLATE, START, END, WEEKDAY, MAX_PARTICIPANTS, PARTICIPANTS, IS_CANCELLED FROM `$dbLessons` WHERE TEMPLATE = %d ORDER BY WEEKDAY, START", $a['id'])) as $key => $row) {
        $lsns[$row->WEEKDAY][$key] = $row;
    }

    foreach ($lsns as $weekday => $lessons) {
        $ret .= "<tr><td><p class=\"ws-std-title\">" . weekday_names[$weekday] . "s</p></td><td>";
        foreach ($lessons as $row) {
            $ret .= "<p class=\"ws-std-entry ";
            $ret .= ($a['show_occupation'] == 0 ? "" : ($row->MAX_PARTICIPANTS == $row->PARTICIPANTS ? "ws-std-entry-full" : "ws-std-entry-free"));
            $ret .= "\"><small>" . substr($row->START, 0, -3) . " &ndash; " .  substr($row->END, 0, -3) . " Uhr</small></p>";
        }
        $ret .= "</td></tr>";
    }
    $ret .= "</tbody></table>";
    return $ret;
}
