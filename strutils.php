<?php

$FERIENKURSE_TITEL = array("Dressurkurs", "Springkurs", "Gelassenheitstraining", "Shetty-Club", "Tagesreitkurs", "Wanderritt", "!Pferdenacht", "!Camping at the horse stable", "!Tagesritt mit Pferdenacht", "Zirkuslektionen und Gelassenheitstraining", "Anfänger-Wanderritt", "Voltigierkurs");
$FERIENKURSE_TEXTE = array("findet ein Dressurkurs statt.<br><br>Max. 4 Teilnehmer<br><br>Preis: 38,-&euro;", "findet ein Springkurs statt.<br><br>Max. 4 Teilnehmer<br><br>Preis: 38,-&euro;", "findet ein GHT-Kurs statt. Hierbei trainieren wir verschiedene Stresssituationen am Boden und auf dem Pferd um im Anschluss einen ganzen Trail-Parcours mit unserem Pferd meistern zu können.<br><br>Preis: 38,-&euro;", "findet speziell für unsere kleinen Reitschüler ein Shetty-Nachmittag statt. Wir werden gemeinsam putzen, reiten, spielen und etwas Schönes basteln.<br><br>Preis: 38,-&euro; incl. Verpflegung", "findet wieder einer unserer beliebten Tagesreitkurse statt. Hier kann jeder unabhängig vom derzeitigen Leistungsstand teilnehmen!<br><br>Wir werden sowohl vormittags als auch nachmittags viel Zeit mit unseren Pferden verbringen, gemeinsam unser eigenes Mittagessen kochen und etwas Schönes basteln.<br><br>Preis: 48,-&euro; incl. Verpflegung", "findet ein großer Wanderritt für Fortgeschrittene statt.<br><br>Wir putzen und satteln gemeinsam unsere Pferde und machen uns auf große Tour. <br><br>Anschließend lassen wir uns unser wohlverdientes Mittagessen schmecken.<br><br>Preis: 48,-&euro; incl. Verpflegung", "Von ... auf ... findet unsere findet unsere x. Pferdenacht in diesem Jahr statt<br><br>Alle Kids die sich trauen dürfen daran teilnehmen.<br><br>Wir werden ganz viel Spaß haben - Reiten, basteln, und abends wieder einen tollen Pferdefilm schauen...lasst euch überraschen. <br><br>Preis: 65,-&euro; incl. Verpflegung", "Von Samstag, den xx.xx.<br>bis<br>Montag, den xx.xx.<br><br>und<br><br>von Samstag, den xx.xx.<br>bis<br>Montag, den 12.08.<br><br>finden diesen Sommer unsere kleinen Zeltlager im Stall statt.<br><br>Ihr zeltet gemeinsam direkt bei uns auf dem Hof, inklusive Vollpension, 1 Reitstunde pro Tag, tollen Workshops rund um unseren Stall und einem bunten Abendprogramm.<br><br>Beginn: jeweils Samstag 16:00 Uhr<br>Ende: jeweils Montag 15:00 Uhr<br><br>Preis: 185,-&euro;", "Am Samstag, den xx.xx. wollen wir wieder einer unserer beliebten Pferdenächte veranstalten.<br><br>Weil es uns letztes Jahr soo unglaublich gut gefallen hat werden wir wieder gemeinsam mit unseren Pferden nach Rotheul zu Violetta nach Hause „reisen“ und dort in Zelten schlafen. Die Pferde verbringen die Nacht auf einer naheliegenden Koppel. Am nächsten Tag treten wir dann gemeinsam den Rückweg an.<br><br>Beginn: Samstag, xx.xx., ca. 17 Uhr<br>Ende: Sonntag, xx.xx., ca. 18 Uhr<br>Treffpunkt jeweils am Reitstall<br>Preis: 65€ inkl. Verpflegung", "findet ein Gelassenheitstraining und Zirkuslektionen-Kurs statt. Hierbei trainieren wir verschiedene Stresssituationen auf dem Pferd und am Boden zu meistern und lernen den Pferden verschiedene Zirkuslektionen, zum Beispiel sich zu verbeugen oder auf Kommando zu lachen.<br><br>Preis: 38,-&euro;", "findet ein kleiner, wenn nötig geführter Wanderritt für Kinder und Erwachsene statt.<br><br>Wir putzen und satteln gemeinsam unsere Pferde und Ponys und machen uns auf Wanderschaft. <br><br>Zurück am Stall essen und trinken wir noch eine Kleinigkeit und lassen den Sonntag Nachmittag in Ruhe ausklingen.<br><br>Preis: 20,-&euro; incl. Verpflegung", "Innerhalb unseres Voltigierkurses entwickelt Ihr Kind durch verschiedene Turnübungen und Spiele einen ausbalancierten Reitsitz und eine positive Körperspannung auf dem Pferd. Das Voltigieren ist ein toller ganzheitlicher Sport für Groß und Klein, welcher sich bestens als Vorstufe zum Einstieg in den Reitsport oder zusätzlich zum Reitunterricht eignet.<br><br>Preis: 16,-&euro;");

define("weekday_names", array("Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag", "Sonntag"));

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
  <option value='2' " . ($selected == 2 ? 'selected' : '') . ">Dienstag</option>
  <option value='3' " . ($selected == 3 ? 'selected' : '') . ">Mittwoch</option>
  <option value='4' " . ($selected == 4 ? 'selected' : '') . ">Donnerstag</option>
  <option value='5' " . ($selected == 5 ? 'selected' : '') . ">Freitag</option>
  <option value='6' " . ($selected == 6 ? 'selected' : '') . ">Samstag</option>
  <option value='7' " . ($selected == 7 ? 'selected' : '') . ">Sonntag</option>";
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