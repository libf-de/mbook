<?php
const mysql_date = 'Y-m-d H:i:s';
const weekday_names = array( "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag", "Sonntag" );
const weekday_names_short = array( "Mon", "Die", "Mit", "Don", "Fre", "Sam", "Son" );
const weekday_names_shortest = array( "Mo", "Di", "Mi", "Do", "Fr", "Sa", "So" );
const month_names = array(
	"Januar",
	"Februar",
	"März",
	"April",
	"Mai",
	"Juni",
	"Juli",
	"August",
	"September",
	"Oktober",
	"November",
	"Dezember"
);
const month_names_short = array( "Jan", "Feb", "Mär", "Apr", "Mai", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Dez" );
const lesson_types = array( "Einzelstunde", "Gruppenstunde", "variabel", "sonstige" );

/**
 * Converts given DateTime to long German string (Montag, den 01.01. [12:00 Uhr])
 *
 * @param $date DateTime the DateTime to convert
 * @param $withTime bool whether to include time
 *
 * @return string Converted date string
 * @throws Exception
 */
function formatDateLongGerman(DateTime $date, bool $withTime): string {
	return weekday_names[$date->format("N")-1] . ", den " . $date->format("d. ") . month_names[$date->format("n")-1] . ($withTime ? $date->format(" H:i") . " Uhr" : "");
}

/**
 * Retrieves the datestring for the given Ferienkurs, e.g.:
 * Mon, 01.01. 12:00 Uhr - 14:00 Uhr
 * Mon, 01.01. ab 12:00 Uhr (when OpenEnd)
 * Mon, 01.01. - Die, 02.01. 14:00 Uhr (multi-day)
 *
 * @param $kurs StdClass input course object
 * @param $withTime bool whether to include time
 *
 * @return string datestring
 */
function formatKursShortGerman( StdClass $kurs, bool $withTime): string {
  if ($kurs->DATESTART->format("d.m.Y") == $kurs->DATEEND->format("d.m.Y")) {
    return weekday_names_shortest[$kurs->DATESTART->format("N")-1] 
     . $kurs->DATESTART->format(", d.m." . ($withTime ? ($kurs->IS_OPEN_END ? " \a\b" : "" ) . " H:i \U\h\\r" : ""))
     . (!$kurs->IS_OPEN_END ? " - " . ($withTime ? $kurs->DATEEND->format("H:i \U\h\\r") : "") : "");
  } else {
    return weekday_names_shortest[$kurs->DATESTART->format("N")-1]
     . $kurs->DATESTART->format(", d.m." . ($withTime ? ($kurs->IS_OPEN_END ? " \a\b" : "" ) . " H:i \U\h\\r" : ""))
     . (!$kurs->IS_OPEN_END ? " - " . weekday_names_shortest[$kurs->DATEEND->format("N")-1] . ", " . $kurs->DATEEND->format(($withTime ? "d.m. H:i \U\h\\r" : "d.m.")) : "");
  }
  
}

/**
 * Generates HTML to display the Ferienkurs's state (free/full)
 * @param $kurs StdClass input course object
 * @param $fmt int format (1=input, 2=div, 3=bookbox div)
 * @param $multi bool whether to include date (multi-course view) or not (single course view)
 *
 * @return string|void
 */
function courseState($kurs, $fmt, $multi = false) {
  $fmt1 = "<input type=\"text\" value=\"%s\" title=\"Qty\" readonly class=\"ws-std-state %s\" size=\"5\">";
  $fmt2 = "<div class=\"ws-fpr-state %s\">%s</div>";
  $fmt3 = "<div class=\"ws-fpr-state %s\" data-code=\"%s\">%s - Buche mit: <div><input type=\"text\" class=\"ws-fpr-bookbox\" value=\"#%s\" title=\"Buchungscode - klicken zum Kopieren\" readonly size=\"10\"><input type=\"button\" class=\"ws-fpr-bookbtn\" value=\" per WhatsApp\"></div></div>";
  //$free_slots = "%free% von %total% Plätzen frei";
  $free_slots = "Plätze frei";
  if ($kurs->IS_CANCELLED) {
    if($fmt == 1) return sprintf($fmt1, "Fällt aus", "ws-std-can");
    elseif($fmt == 2 || $fmt == 3) return sprintf($fmt2, "ws-fpr-can", "<span class=\"ws-fpr-date\">" . ($multi ? $kurs->DATESTART->format("d.m.: ") : "") . "</span>Fällt aus");
  } elseif ($kurs->PARTICIPANTS < $kurs->MAX_PARTICIPANTS) {
    if($fmt == 1) 
      return sprintf($fmt1, 
        str_replace(
          array("%free%", "%used%", "%total%"),
          array(($kurs->MAX_PARTICIPANTS - $kurs->PARTICIPANTS), $kurs->PARTICIPANTS, $kurs->MAX_PARTICIPANTS),
          $free_slots
        ), "ws-std-free");
    elseif($fmt == 2) 
      return sprintf($fmt2, "ws-fpr-free", "<span class=\"ws-fpr-date\">" . ($multi ? $kurs->DATESTART->format("d.m.: ") : "") . "</span>"
        . str_replace( 
            array("%free%", "%used%", "%total%"),
            array(($kurs->MAX_PARTICIPANTS - $kurs->PARTICIPANTS), $kurs->PARTICIPANTS, $kurs->MAX_PARTICIPANTS),
            $free_slots));
    elseif($fmt == 3) 
      return sprintf($fmt3, "ws-fpr-free ws-fpr-book", $kurs->SHORTCODE, "<span class=\"ws-fpr-date\">" . ($multi ? $kurs->DATESTART->format("d.m.: ") : "") . "</span>"
        . str_replace( 
            array("%free%", "%used%", "%total%"),
            array(($kurs->MAX_PARTICIPANTS - $kurs->PARTICIPANTS), $kurs->PARTICIPANTS, $kurs->MAX_PARTICIPANTS),
            $free_slots), $kurs->SHORTCODE);
    
  } else {
    if($fmt == 1) return sprintf($fmt1, "Belegt", "ws-std-full");
    elseif($fmt == 2||$fmt == 3) return sprintf($fmt2, "ws-fpr-full", "<span class=\"ws-fpr-date\">" . ($multi ? $kurs->DATESTART->format("d.m.: ") : "") . "</span>Belegt");
  }
}

/**
 * Compares if all courses happen on one day each and at the same time
 *
 * @param $kurs StdClass single course (e.g. first in array)
 * @param $kurse array other courses to compare to
 *
 * @return bool[] [same day; same time]
 */
function compareKurse( StdClass $kurs, array $kurse): array {
  $one_day = TRUE;
  $same_time = TRUE;
  $open_end = $kurs->IS_OPEN_END;
  $single_start = $kurs->DATESTART->format("H:i");
  $single_end = $kurs->DATEEND->format("H:i");
  try {
    foreach($kurse as $further_kurs) {
      if($open_end != $further_kurs->IS_OPEN_END) return array(FALSE, FALSE); //OpenEnd ungleich => beides nein
      if($open_end)
        if($single_start != $further_kurs->DATESTART->format("H:i")) return array(FALSE, FALSE); //OpenEnd, Startzeit ungleich => beides nein
        else continue;

      if(($further_kurs->DATESTART->format("Y-m-d") != $further_kurs->DATEEND->format("Y-m-d"))) {
        $one_day = FALSE;
        break; //Don't compare the rest, multiple days format is the same for one/many times
      }
      if($single_start != $further_kurs->DATESTART->format("H:i") || $single_end != $further_kurs->DATEEND->format("H:i")) $same_time = FALSE;
      if(!($one_day || $same_time)) break;
    }
    return array($one_day, $same_time);
  } catch(Exception $e) {
    return array(FALSE, FALSE);
  }
}

/**
 * Converts the string date attributes of given Ferienkurs(es) to DateTime objects
 *
 * @param $kurs StdClass|array Ferienkurs(es) to convert
 *
 * @return StdClass|array converted Ferienkurs(es)
 */
function convertKursDT( $kurs ) {
  if (is_array($kurs)) {
    foreach($kurs as $k) {
      if(!($k->DATESTART instanceof DateTime)) $k->DATESTART = DateTime::createFromFormat(mysql_date, $k->DATESTART);
      if(!($k->DATEEND instanceof DateTime)) $k->DATEEND = DateTime::createFromFormat(mysql_date, $k->DATEEND);
    }
    return $kurs;
  } elseif(is_object($kurs)) {
    if(!($kurs->DATESTART instanceof DateTime)) $kurs->DATESTART = DateTime::createFromFormat(mysql_date, $kurs->DATESTART);
    if(!($kurs->DATEEND instanceof DateTime)) $kurs->DATEEND = DateTime::createFromFormat(mysql_date, $kurs->DATEEND);
    return $kurs;
  }
}


/**
 * Creates a weekday dropdown list with given day preselected
 *
 * @param $selected int preselected weekday
 *
 * @return string html <select> dropdown options
 */
function weekday_dropdown( int $selected): string {
  if(!is_numeric($selected)) {
    $selected = 0;
  }
  $selected = intval($selected);
  $retstr = "";
  foreach(range(0, count(weekday_names)-1) as $weekday) {
    $retstr .= "<option value=\"$weekday\" " . ($selected == $weekday ? 'selected' : '') . ">" . weekday_names[$weekday] . "</option>";
  }
  return $retstr;
}

/**
 * Rotates array by given positions, preserving keys
 *
 * @param $iarray array input
 * @param $by int rotation
 *
 * @return array output array
 * @throws Exception
 */
function array_rotate( array $iarray, int $by): array {
  if(!is_numeric($by)) throw new Exception('In rotate_array: by-paramter must be numeric!');
  $array = $iarray;
  for($i = 0; $i < $by; $i++) {
    $keys = array_keys($array);
    $val = $array[$keys[0]];
    unset($array[$keys[0]]);
    $array[$keys[0]] = $val;
  }
  return $array;
}