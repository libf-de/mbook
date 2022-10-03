<?php

$FERIENKURSE_TITEL = array("Dressurkurs", "Springkurs", "Gelassenheitstraining", "Shetty-Club", "Tagesreitkurs", "Wanderritt", "!Pferdenacht", "!Camping at the horse stable", "!Tagesritt mit Pferdenacht", "Zirkuslektionen und Gelassenheitstraining", "Anfänger-Wanderritt", "Voltigierkurs");
$FERIENKURSE_TEXTE = array("findet ein Dressurkurs statt.<br><br>Max. 4 Teilnehmer<br><br>Preis: 38,-&euro;", "findet ein Springkurs statt.<br><br>Max. 4 Teilnehmer<br><br>Preis: 38,-&euro;", "findet ein GHT-Kurs statt. Hierbei trainieren wir verschiedene Stresssituationen am Boden und auf dem Pferd um im Anschluss einen ganzen Trail-Parcours mit unserem Pferd meistern zu können.<br><br>Preis: 38,-&euro;", "findet speziell für unsere kleinen Reitschüler ein Shetty-Nachmittag statt. Wir werden gemeinsam putzen, reiten, spielen und etwas Schönes basteln.<br><br>Preis: 38,-&euro; incl. Verpflegung", "findet wieder einer unserer beliebten Tagesreitkurse statt. Hier kann jeder unabhängig vom derzeitigen Leistungsstand teilnehmen!<br><br>Wir werden sowohl vormittags als auch nachmittags viel Zeit mit unseren Pferden verbringen, gemeinsam unser eigenes Mittagessen kochen und etwas Schönes basteln.<br><br>Preis: 48,-&euro; incl. Verpflegung", "findet ein großer Wanderritt für Fortgeschrittene statt.<br><br>Wir putzen und satteln gemeinsam unsere Pferde und machen uns auf große Tour. <br><br>Anschließend lassen wir uns unser wohlverdientes Mittagessen schmecken.<br><br>Preis: 48,-&euro; incl. Verpflegung", "Von ... auf ... findet unsere findet unsere x. Pferdenacht in diesem Jahr statt<br><br>Alle Kids die sich trauen dürfen daran teilnehmen.<br><br>Wir werden ganz viel Spaß haben - Reiten, basteln, und abends wieder einen tollen Pferdefilm schauen...lasst euch überraschen. <br><br>Preis: 65,-&euro; incl. Verpflegung", "Von Samstag, den xx.xx.<br>bis<br>Montag, den xx.xx.<br><br>und<br><br>von Samstag, den xx.xx.<br>bis<br>Montag, den 12.08.<br><br>finden diesen Sommer unsere kleinen Zeltlager im Stall statt.<br><br>Ihr zeltet gemeinsam direkt bei uns auf dem Hof, inklusive Vollpension, 1 Reitstunde pro Tag, tollen Workshops rund um unseren Stall und einem bunten Abendprogramm.<br><br>Beginn: jeweils Samstag 16:00 Uhr<br>Ende: jeweils Montag 15:00 Uhr<br><br>Preis: 185,-&euro;", "Am Samstag, den xx.xx. wollen wir wieder einer unserer beliebten Pferdenächte veranstalten.<br><br>Weil es uns letztes Jahr soo unglaublich gut gefallen hat werden wir wieder gemeinsam mit unseren Pferden nach Rotheul zu Violetta nach Hause „reisen“ und dort in Zelten schlafen. Die Pferde verbringen die Nacht auf einer naheliegenden Koppel. Am nächsten Tag treten wir dann gemeinsam den Rückweg an.<br><br>Beginn: Samstag, xx.xx., ca. 17 Uhr<br>Ende: Sonntag, xx.xx., ca. 18 Uhr<br>Treffpunkt jeweils am Reitstall<br>Preis: 65€ inkl. Verpflegung", "findet ein Gelassenheitstraining und Zirkuslektionen-Kurs statt. Hierbei trainieren wir verschiedene Stresssituationen auf dem Pferd und am Boden zu meistern und lernen den Pferden verschiedene Zirkuslektionen, zum Beispiel sich zu verbeugen oder auf Kommando zu lachen.<br><br>Preis: 38,-&euro;", "findet ein kleiner, wenn nötig geführter Wanderritt für Kinder und Erwachsene statt.<br><br>Wir putzen und satteln gemeinsam unsere Pferde und Ponys und machen uns auf Wanderschaft. <br><br>Zurück am Stall essen und trinken wir noch eine Kleinigkeit und lassen den Sonntag Nachmittag in Ruhe ausklingen.<br><br>Preis: 20,-&euro; incl. Verpflegung", "Innerhalb unseres Voltigierkurses entwickelt Ihr Kind durch verschiedene Turnübungen und Spiele einen ausbalancierten Reitsitz und eine positive Körperspannung auf dem Pferd. Das Voltigieren ist ein toller ganzheitlicher Sport für Groß und Klein, welcher sich bestens als Vorstufe zum Einstieg in den Reitsport oder zusätzlich zum Reitunterricht eignet.<br><br>Preis: 16,-&euro;");

define('mysql_date', 'Y-m-d H:i:s');
define("weekday_names", array("Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag", "Sonntag"));
define("weekday_names_short", array("Mon", "Die", "Mit", "Don", "Fre", "Sam", "Son"));
define("month_names", array("Januar", "Februar", "März", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember"));
define("month_names_short", array("Jan", "Feb", "Mär", "Apr", "Mai", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Dez"));

/*function formatDateLongGerman($date) {
  if(!($date instanceof DateTime)) throw new Exception('In formatDateLongGerman: date-parameter must be DateTime!');
  return weekday_names[$date->format("N")] . ", den " . $date->format("d. ") . month_names[$date->format("n")] . $date->format(" Y");
}*/

function formatDateLongGerman($date, $withTime) {
  if(!($date instanceof DateTime)) throw new Exception('In formatDateLongGerman: date-parameter must be DateTime!');
  return weekday_names[$date->format("N")-1] . ", den " . $date->format("d. ") . month_names[$date->format("n")-1] . ($withTime ? $date->format(" H:i") . " Uhr" : "");
}

function formatDateShortGerman($date, $withTime) {
  if(!($date instanceof DateTime)) throw new Exception('In formatDateShortGerman: date-parameter must be DateTime!');
  return weekday_names_short[$date->format("N")-1] . ", " . $date->format("d. ") . $date->format("m") . ($withTime ? $date->format(" H:i") . " Uhr" : "");
}

function formatKursShortGerman($kurs, $withTime) {
  return weekday_names_short[$kurs->DATESTART->format("N")-1] . $kurs->DATESTART->format(", d.m." . ($withTime ? ($kurs->IS_OPEN_END ? " \a\b" : "" ) . " H:i \U\h\\r" : ""))
     . (!$kurs->IS_OPEN_END ? " - " . weekday_names_short[$kurs->DATEEND->format("N")-1] . ", " . $kurs->DATEEND->format(($withTime ? "d.m. H:i \U\h\\r" : "d.m.")) : "");
}

function courseState($kurs, $fmt, $multi = false) {
  $fmt1 = "<input type=\"text\" value=\"%s\" title=\"Qty\" readonly class=\"ws-std-state %s\" size=\"5\">";
  $fmt2 = "<div class=\"ws-fpr-state %s\">%s</div>";
  $free_slots = "%free% von %total% Plätzen frei";
  if ($kurs->IS_CANCELLED) {
    if($fmt == 1) return sprintf($fmt1, "Fällt aus", "ws-std-can");
    elseif($fmt == 2) return sprintf($fmt2, "ws-fpr-can", ($multi ? $kurs->DATESTART->format("d.m.: ") : "") . "Fällt aus");
  } elseif ($kurs->PARTICIPANTS < $kurs->MAX_PARTICIPANTS) {
    if($fmt == 1) 
      return sprintf($fmt1, 
        str_replace(
          array("%free%", "%used%", "%total%"),
          array(($kurs->MAX_PARTICIPANTS - $kurs->PARTICIPANTS), $kurs->PARTICIPANTS, $kurs->MAX_PARTICIPANTS),
          $free_slots
        ), "ws-std-free");
    elseif($fmt == 2) 
      return sprintf($fmt2, "ws-fpr-free", ($multi ? $kurs->DATESTART->format("d.m.: ") : "")
        . str_replace( 
            array("%free%", "%used%", "%total%"),
            array(($kurs->MAX_PARTICIPANTS - $kurs->PARTICIPANTS), $kurs->PARTICIPANTS, $kurs->MAX_PARTICIPANTS),
            $free_slots));
    
  } elseif ($kurs->PARTICIPANTS >= $kurs->MAX_PARTICIPANTS) {
    if($fmt == 1) return sprintf($fmt1, "Belegt", "ws-std-full");
    elseif($fmt == 2) return sprintf($fmt2, "ws-fpr-full", ($multi ? $kurs->DATESTART->format("d.m.: ") : "") . "Belegt");
  } else {
    if($fmt == 1) return sprintf($fmt1, "unbekannt", "ws-std-full");
    elseif($fmt == 2) return sprintf($fmt2, "ws-fpr-full", ($multi ? $kurs->DATESTART->format("d.m.: ") : "") . "Unbekannt");
  }
}

// Compares if all courses happen on one day each and at the same time
// Returns: array(1DAY, 1TIME)
function compareKurse($kurs, $kurse) {
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

/*function convertSqlDateTime( &$obj ) {
  foreach(get_object_vars($obj) as $key => &$val) {
    if(stripos($key, "date") === FALSE) {
      continue;
    } else {
      $val = DateTime::createFromFormat(mysql_date, $val);
    }
  }
}*/

function endsWith( $haystack, $needle ) {
  $length = strlen( $needle );
  if( !$length ) {
      return true;
  }
  return substr( $haystack, -$length ) === $needle;
}

function startsWith( $haystack, $needle ) {
  $length = strlen( $needle );
  return substr( $haystack, 0, $length ) === $needle;
}

function weekday_name($id) {
  if(!is_int($id)) { return ""; }
  return weekday_names[$id];
}

function weekday_dropdown($selected) {
  if(!is_numeric($selected)) {
    $selected = 0;
  }
  $selected = intval($selected);
  $retstr = "";
  foreach(range(0, count(weekday_names)-1) as $weekday) {
    $retstr .= "<option value=\"$weekday\" " . ($selected == $weekday ? 'selected' : '') . ">" . weekday_names[$weekday] . "</option>";
  }
  return $retstr;
  return "<option value=\"0\" " . ($selected == 0 ? 'selected' : '') . ">Montag</option>
  <option value='1' " . ($selected == 1 ? 'selected' : '') . ">Dienstag</option>
  <option value='2' " . ($selected == 2 ? 'selected' : '') . ">Mittwoch</option>
  <option value='3' " . ($selected == 3 ? 'selected' : '') . ">Donnerstag</option>
  <option value='4' " . ($selected == 4 ? 'selected' : '') . ">Freitag</option>
  <option value='5' " . ($selected == 5 ? 'selected' : '') . ">Samstag</option>
  <option value='6' " . ($selected == 6 ? 'selected' : '') . ">Sonntag</option>";
}

function array_rotate($iarray, $by) {
  if(!is_numeric($by)) throw new Exception('In rotate_array: by-paramter must be numeric!');
  if(!is_array($iarray)) throw new Exception('In rotate_array: iarray-paramter must be an array!');
  $array = $iarray;
  for($i = 0; $i < $by; $i++) {
    $keys = array_keys($array);
    $val = $array[$keys[0]];
    unset($array[$keys[0]]);
    $array[$keys[0]] = $val;
  }
  return $array;
}

function typn($inpt, $plural = FALSE) {
    switch($inpt) {
      case 1:
        if($plural) {
          return "Ponyführstunden";
        } else {
          return "Ponyführstunde";
        }
      case 2:
        if($plural) {
          return "Shettyreitstunden";
        } else {
          return "Shettyreitstunde";
        }
      case 3:
        if($plural) {
          return "Gruppenreitstunden";
        } else {
          return "Gruppenreitstunde";
        }
      case 4:
        if($plural) {
          return "Erwachsenenreitstunden";
        } else {
          return "Erwachsenenreitstunde";
        }
      case 5:
        if($plural) {
          return "Pferdezeiten";
        } else {
          return "Pferdezeit";
        }
      case 6:
        return "Ferienprogramm";
      case 7:
        if($plural) {
          return "Voltigierstunden";
        } else {
          return "Voltigierstunde";
        }
      default:
        return "Stunde";
    }
  }
?>