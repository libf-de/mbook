<?php

/**
* Plugin Name: MBook
* Plugin URI: https://xorg.ga/
* Description: Einfaches Reitbuch für Wordpress
* Version: 1.0
* Author: Fabian Schillig
* License: GNU GPL
*/


$FERIENKURSE_TITEL = array("Dressurkurs", "Springkurs", "Gelassenheitstraining", "Shetty-Club", "Tagesreitkurs", "Wanderritt", "!Pferdenacht");
$FERIENKURSE_TEXTE = array("findet ein Dressurkurs statt.<br><br>Preis: 35,-&euro;", "findet ein Springkurs statt.<br><br>Preis: 35,-&euro;", "findet ein GHT-Kurs statt. Hierbei trainieren wir verschiedene Stresssituationen am Boden und auf dem Pferd um im Anschluss einen ganzen Trail-Parcours mit unserem Pferd meistern zu können.<br><br>Preis: 35,-&euro;", "findet speziell für unsere kleinen Reitschüler ein Shetty-Nachmittag statt. Wir werden gemeinsam putzen, reiten, spielen und etwas Schönes basteln.<br><br>Preis: 35,-&euro; incl. Verpflegung", "findet wieder einer unserer beliebten Tagesreitkurse statt. Hier kann jeder unabhängig vom derzeitigen Leistungsstand teilnehmen!<br><br>Preis: 48,-&euro; incl. Verpflegung", "findet ein Wanderritt statt.<br><br>Preis: 45,-&euro; incl. Verpflegung", "Von ... auf ... findet unsere findet unsere x. Pferdenacht in diesem Jahr statt<br><br>Alle Kids die sich trauen dürfen daran teilnehmen.<br><br>Wir werden ganz viel Spaß haben - Reiten, basteln, und abends wieder einen tollen Pferdefilm schauen...lasst euch überraschen. <br><br>Preis: 65,-&euro; incl. Verpflegung");
global $FERIENKURSE_TITEL;

global $mb_db_version;
$mb_db_version = '1.0';

function mb_init() {
  global $wpdb;
  global $mb_db_version;

  $utname = $wpdb->prefix . "wmb_ust";
  $ftname = $wpdb->prefix . "wmb_fpr";

  $charset_collate = $wpdb->get_charset_collate();

  $initut = "CREATE TABLE $utname ( ID INT UNSIGNED NOT NULL AUTO_INCREMENT, TITEL VARCHAR(50), TYP TINYINT, TAG TINYINT, ZEITVON TIME, ZEITBIS TIME, STD_MAX_KINDER TINYINT, STD_KINDER TINYINT, OVR_DATUM DATE, OVR_KINDER TINYINT, PRIMARY KEY  (ID)) $charset_collate;";
  $initft = "CREATE TABLE $ftname ( ID INT UNSIGNED NOT NULL AUTO_INCREMENT, TITEL VARCHAR(50), BESCHREIBUNG TEXT, LINKURL VARCHAR(99), KDATUM DATE, ZEITVON TIME, ZEITBIS TIME, STD_MAX_KINDER TINYINT, STD_KINDER TINYINT DEFAULT 0, PRIMARY KEY  (ID)) $charset_collate;";
  //ALTER TABLE `stunden` ADD FOREIGN KEY (`typ`) REFERENCES `urls`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
  //SELECT titel,ST.id FROM `stunden` AS ST JOIN `urls` AS U ON U.id = ST.typ WHERE ST.typ = 1
  //SELECT `unterricht`.`*`, `ferien`.`*` FROM `wp_mbook` AS `unterricht`, `wp_ferien` AS `ferien` WHERE `wp_mbook`.`*` = 'UST' AND `wp_ferien`.`*` = 'FER';


  require_once( ABSPATH . 'wp-admin/includes/upgrade.php');
  dbDelta( $initut );
  dbDelta( $initft );

  add_option( 'mb_db_version', $mb_db_version );
}

function mb_menu() {
  add_options_page( 'Reitbuch-Einstellungen', 'Reitbuch', 'manage_options', 'mb-options-menu', 'mb_options' );
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
    default:
      return "Stunde";
  }
}

function linkx($inpt, $text) {
  $link = get_option('std' . $inpt);
  if(!is_null($link) && strlen($link) > 5) {
    return "<a href=\"" . $link . "\">" . $text . "</a>";
  } else {
    return $text;
  }
}

function linkf($text, $url) {
  return (!is_null($url) && strlen($url) > 5) ? "<a href=\"$url\">$text</a>" : $text;
}

function dnum($inpt) {
  switch($inpt) {
    case 1:
      return "monday";
    case 2:
      return "tuesday";
    case 3:
      return "wednesday";
    case 4:
      return "thursday";
    case 5:
      return "friday";
    case 6:
      return "saturday";
    case 7:
      return "sunday";
    default:
      return "monday";
  }
}

function tnum($inpt) {
  switch($inpt) {
    case 1:
      return "Montag";
    case 2:
      return "Dienstag";
    case 3:
      return "Mittwoch";
    case 4:
      return "Donnerstag";
    case 5:
      return "Freitag";
    case 6:
      return "Samstag";
    case 7:
      return "Sonntag";
    default:
      return "Wochentag";
  }
}

function mb_options() {
  global $wpdb;
  $utname = $wpdb->prefix . "wmb_ust";
  $ftname = $wpdb->prefix . "wmb_fpr";

  if (!current_user_can('manage_options')) {
    wp_die( __('You do not have sufficient permissions to access this page.') );
  }

  if (isset($_POST['action'])) {
    $action = "POST_" . $_POST['action'];
  } elseif(isset($_GET['action'])) {
    $action = $_GET['action'];
  } else {
    $action = "manage";
  }

  echo "<div class=\"wrap\"><h2>Reitbuch-Einstellungen</h2><h2 class=\"nav-tab-wrapper\"><a href=\"?page=mb-options-menu&action=manage\" class=\"nav-tab ";
  echo $action == 'manage' ? 'nav-tab-active' : '';
  echo "\">Unterricht</a><a href=\"?page=mb-options-menu&action=managefk\" class=\"nav-tab ";
  echo $action == 'managefk' ? 'nav-tab-active' : '';
  echo "\">Ferienkurse</a><a href=\"?page=mb-options-menu&action=config\" class=\"nav-tab ";
  echo ($action == 'config' || $action == 'POST_config') ? 'nav-tab-active' : '';
  echo "\">Konfiguration</a><a href=\"?page=mb-options-menu&action=shortcode\" class=\"nav-tab ";
  echo ($action == 'shortcode' || $action == 'POST_shortcode') ? 'nav-tab-active' : '';
  echo "\">Kurscodes</a></h2><div class=\"settings_page\" style=\"margin-top: 1em;\">";

  switch($action) {
    case "main":
      echo '<table class="form-table"><thead><tr><th colspan="2"><a href="?page=mb-options-menu&action=add" class="button button-primary">Neu hinzufügen</a></th></tr></thead><tbody>';

      foreach( $wpdb->get_results("SELECT ID, TITEL, TAG FROM $utname ORDER BY TAG, ZEITVON") as $key => $row) {
        echo "<tr><td>" . $row->TITEL . "</td><td><a href=\"?page=mb-options-menu&action=edit&id=" . $row->ID . "\" class=\"button button-primary\">Bearb.</a>&nbsp;<a href=\"?page=mb-options-menu&action=delete&id=" . $row->ID . "\" class=\"button button-primary\">Lösch.</a></td></tr>";
      }

      echo "</tbody></table>";
      break;
    case "POST_edit":
      if (strtotime($_POST['zeitvon']) === FALSE || strtotime($_POST['zeitbis']) === FALSE) {
        echo "<div class=\"manage-controls mcerr\"><p>Fehler: Ungültige Start- oder Endzeit!</p></div>";
      } elseif ( !is_numeric($_POST['stdkids']) || !is_numeric($_POST['stdkidsmax'])) {
        echo "<div class=\"manage-controls mcerr\"><p>Fehler: Ungültige Teilnehmeranzahl!</p></div>";
      } elseif (!is_numeric($_POST['typ']) || !is_numeric($_POST['tag'])) {
        echo "<div class=\"manage-controls mcerr\"><p>Fehler: Ungültiges Angebot oder Tag!</p></div>";
      } elseif ( strlen($_POST['titel']) < 5 ) {
        echo "<div class=\"manage-controls mcerr\"><p>Fehler: Der Titel sollte mindestens 5 Zeichen lang sein!</p></div>";
      } elseif ( $_POST['stdkids'] > $_POST['stdkidsmax'] ) {
        echo "<div class=\"manage-controls mcerr\"><p>Fehler: Die Teilnehmerzahl ist größer als das Maximum!</p></div>";
      } elseif( strtotime($_POST['zeitbis']) < strtotime($_POST['zeitvon'])) {
        echo "<div class=\"manage-controls mcerr\"><p>Fehler: Die Endzeit ist früher als die Startzeit!</p></div>";
      } else {
        if($wpdb->update($utname, array( 'TITEL' => $_POST['titel'], 'TYP' => $_POST['typ'], 'TAG' => $_POST['tag'], 'ZEITVON' => $_POST['zeitvon'], 'ZEITBIS' => $_POST['zeitbis'], 'STD_KINDER' => $_POST['stdkids'], 'STD_MAX_KINDER' => $_POST['stdkidsmax']), array('ID' => $_POST['id']), array('%s', '%d', '%d', '%s', '%s', '%d', '%d'), array('%d')) !== FALSE) {
          echo "<div class=\"manage-controls mcok\"><p>Die Stunde wurde aktualisiert - <a href=\"?page=mb-options-menu\">zur Übersicht</a></p></div>";
        } else {
          echo "<div class=\"manage-controls mcerr\"><p>Fehler: Die Stunde konnte nicht aktualisiert werden!</p></div>";
        }
      }
    case "edit":
      if(!isset($_GET['id'])) {
        echo "Fehlerhafte Anfrage: Keine zu bearbeitende ID angegeben!<br><a href='?page=mb-options-menu'>Startseite</a>";
        return;
      }

      $id = $_GET['id'];

      $row = $wpdb->get_row("SELECT TITEL, TYP, TAG, ZEITVON, ZEITBIS, STD_MAX_KINDER, STD_KINDER FROM $utname WHERE ID = $id");

      $day = $row->TAG;

      echo "<div class=\"manage-controls\"><h3>#$id - $row->TITEL</h3><form method=\"post\" action=\"\"><input type=\"hidden\" name=\"action\" value=\"edit\"><input type=\"hidden\" name=\"id\" value=\"$id\"><table class=\"form-table manage-table\">";
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
      echo "<option value=\"6\"" . ($row->TYP == '6' ? 'selected' : '') . ">Ferienprogramm</option>";
      echo "</select></td></tr>";
      echo "<tr valign=\"top\"><th scope=\"row\"><strong>Uhrzeit</strong></th><td><input type=\"time\" required min=\"00:00\" max=\"23:59\" name=\"zeitvon\" value=\"$row->ZEITVON\"> bis <input type=\"time\" required min=\"00:00\" max=\"23:59\" name=\"zeitbis\" value=\"$row->ZEITBIS\"></td></tr>";
      echo "<tr valign=\"top\"><th scope=\"row\"><strong>Teilnehmer</strong></th><td><input type=\"number\" min=\"0\" required max=\"99\" name=\"stdkids\" value=\"$row->STD_KINDER\"> von maximal <input type=\"number\" required min=\"1\" max=\"99\" name=\"stdkidsmax\" value=\"$row->STD_MAX_KINDER\"></td></tr>";
      echo "<tr valign=\"top\"><th scope=\"row\" class=\"btmrow\"><input type=\"submit\" class=\"button button-primary\" value=\"Speichern\"></th></tr>";
      echo "</tbody></table></form></div>";
      break;
    case "delete":
      if(!isset($_GET['id'])) {
        echo "Fehlerhafte Anfrage: Keine zu bearbeitende ID angegeben!<br><a href='?page=mb-options-menu'>Startseite</a>";
        return;
      }
      $id = $_GET['id'];

      $row = $wpdb->get_row("SELECT TITEL, TYP, TAG, ZEITVON, ZEITBIS FROM $utname WHERE ID = $id");

      echo "<div class=\"manage-controls\"><p>Möchten Sie die/das " . typn($row->TYP) . " #$id &quot;" . $row->TITEL . "&quot; am " . tnum($row->TAG) . " von " . $row->ZEITVON . " bis ". $row->ZEITBIS . " Uhr wirklich löschen?</p><form method=\"post\" action=\"\"><input type=\"hidden\" name=\"action\" value=\"delete\"><input type=\"hidden\" name=\"id\" value=\"$id\"><table class=\"form-table manage-table\">";
      echo "<tbody><tr valign=\"top\"><th scope=\"row\" class=\"btmrow\"><a href=\"?page=mb-options-menu\" class=\"button button-primary\">Abbrechen</a></th><td><input type=\"submit\" class=\"button button-warning\" value=\"Löschen\"></td></tr>";
      echo "</tbody></table></form></div>";
      break;
    case "POST_delete":
      if(!isset($_POST['id'])) {
        echo "Fehlerhafte Anfrage: Keine zu bearbeitende ID angegeben!<br><a href='?page=mb-options-menu'>Startseite</a>";
        return;
      }
      if($wpdb->delete($utname, array( 'ID' => $_POST['id']), array('%d')) !== FALSE) {
        echo "<div class=\"manage-controls mcok\"><p>Die Stunde #" . $_POST['id'] . " wurde gelöscht - <a href=\"?page=mb-options-menu\">zur Übersicht</a></p></div>";
      } else {
        echo "<div class=\"manage-controls mcerr\"><p>Fehler: Die Stunde konnte nicht gelöscht werden!</p></div>";
      }
      break;
    case "POST_add":
      if($wpdb->insert($utname, array( 'TITEL' => $_POST['titel'], 'TYP' => $_POST['typ'], 'TAG' => $_POST['tag'], 'ZEITVON' => $_POST['zeitvon'], 'ZEITBIS' => $_POST['zeitbis'], 'STD_KINDER' => $_POST['stdkids'], 'STD_MAX_KINDER' => $_POST['stdkidsmax']), array('%s', '%d', '%d', '%s', '%s', '%d', '%d')) !== FALSE) {
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
      echo "<option value=\"6\">Ferienprogramm</option>";
      echo "</select></td></tr>";
      echo "<tr valign=\"top\"><th scope=\"row\"><strong>Uhrzeit</strong></th><td><input type=\"time\" required min=\"00:00\" max=\"23:59\" name=\"zeitvon\" value=\"12:00\"> bis <input type=\"time\" required min=\"00:00\" max=\"23:59\" name=\"zeitbis\" value=\"14:00\"></td></tr>";
      echo "<tr valign=\"top\"><th scope=\"row\"><strong>Teilnehmer</strong></th><td><input type=\"number\" min=\"0\" required max=\"99\" name=\"stdkids\" value=\"0\"> von maximal <input type=\"number\" required min=\"1\" max=\"99\" name=\"stdkidsmax\" value=\"10\"></td></tr>";
      echo "<tr valign=\"top\"><th scope=\"row\" class=\"btmrow\"><input type=\"submit\" class=\"button button-primary\" value=\"Hinzufügen\"></th></tr>";
      echo "</tbody></table></form></div>";
      break;
    case "POST_addfk":
      if($wpdb->insert($ftname, array( 'TITEL' => $_POST['titel'], 'BESCHREIBUNG' => preg_replace("/\r\n|\r|\n/",'<br/>', $_POST['beschreibung']), 'LINKURL' => $_POST['linkurl'], 'KDATUM' => $_POST['datum'], 'ZEITVON' => $_POST['zeitvon'], 'ZEITBIS' => $_POST['zeitbis'], 'STD_MAX_KINDER' => $_POST['stdkidsmax']), array('%s', '%s', '%s', '%s', '%s', '%s', '%d')) !== FALSE) {
        echo "<div class=\"manage-controls mcok\"><p>Der Ferienkurs #$wpdb->insert_id wurde erstellt - <a href=\"?page=mb-options-menu&action=managefk\">zur Übersicht</a></p></div>";
      } else {
        echo "<div class=\"manage-controls mcerr\"><p>Fehler: Der Ferienkurs konnte nicht erstellt werden!</p></div>";
      }
      break;
    case "addfk":
      global $FERIENKURSE_TITEL;
      global $FERIENKURSE_TEXTE;

      $TPL_TITEL = (isset($_GET['tpl'])) ? $FERIENKURSE_TITEL[$_GET['tpl']] : "";
      $TPL_BESCH = (isset($_GET['tpl'])) ? $FERIENKURSE_TEXTE[$_GET['tpl']] : "";

      $day = date('N');
      echo "<div class=\"manage-controls mctop\"><form method=\"get\"><input type=\"hidden\" name=\"page\" value=\"mb-options-menu\"><input type=\"hidden\" name=\"action\" value=\"addfk\"><label class=\"selected-control\" for=\"tpl\">Wähle eine Vorlage aus:</label><select name=\"tpl\" id=\"tpl\">";
      for ($i = 0; $i < count($FERIENKURSE_TITEL); $i++) {
        echo "<option value=\"" . $i . "\"" . ($_GET['tpl'] ==  $i ? 'selected' : '') . ">" . str_replace("!", "", $FERIENKURSE_TITEL[$i]) . "</option>";
      }
      echo "</select><input type=\"submit\" class=\"button\" value=\"Laden\"></form></div>";
      echo "<div class=\"manage-controls\"><form method=\"post\" action=\"\"><input type=\"hidden\" name=\"action\" value=\"addfk\"><table class=\"form-table manage-table\">";
      echo "<tbody>";
      echo "<tr valign=\"top\"><th scope=\"row\"><strong>Titel</strong></th><td><input type=\"text\" pattern=\".{5,}\" required title=\"Der Titel sollte mindestens 5 Zeichen lang sein\" name=\"titel\" value=\"" . $TPL_TITEL . "\"></td></tr>";
      echo "<tr valign=\"top\"><th scope=\"row\"><strong>Beschreibung</strong></th><td><textarea pattern=\".{5,}\" required title=\"Die Beschreibung sollte mindestens 5 Zeichen lang sein\" name=\"beschreibung\" cols=\"22\" rows=\"6\">" . preg_replace('/\<br\s*\/?\>/',"\n", $TPL_BESCH) . "</textarea></td></tr>";
      echo "<tr valign=\"top\"><th scope=\"row\"><strong>Link-URL</strong></th><td><input type=\"text\" name=\"linkurl\"></td></tr>";
      echo "<tr valign=\"top\"><th scope=\"row\"><strong>Datum</strong></th><td><input type=\"date\" name=\"datum\" id=\"datum\" value=\"" . date("Y-m-d") . "\"></td></tr>";
      echo "<tr valign=\"top\"><th scope=\"row\"><strong>Uhrzeit</strong></th><td><input type=\"time\" required min=\"00:00\" max=\"23:59\" name=\"zeitvon\" value=\"12:00\"> bis <input type=\"time\" required min=\"00:00\" max=\"23:59\" name=\"zeitbis\" value=\"14:00\"></td></tr>";
      echo "<tr valign=\"top\"><th scope=\"row\"><strong>Max. Teilnehmer</strong></th><td><input type=\"hidden\"name=\"stdkids\" value=\"0\"><input type=\"number\" required min=\"1\" max=\"99\" name=\"stdkidsmax\" value=\"10\"></td></tr>";
      echo "<tr valign=\"top\"><th scope=\"row\" class=\"btmrow\"><input type=\"submit\" class=\"button button-primary\" value=\"Hinzufügen\"></th></tr>";
      echo "</tbody></table></form></div>";
      break;
    case "POST_editfk":
      if (strtotime($_POST['zeitvon']) === FALSE || strtotime($_POST['zeitbis']) === FALSE) {
        echo "<div class=\"manage-controls mcerr\"><p>Fehler: Ungültige Start- oder Endzeit!</p></div>";
      } elseif ( !is_numeric($_POST['stdkidsmax'])) {
        echo "<div class=\"manage-controls mcerr\"><p>Fehler: Ungültige Teilnehmeranzahl!</p></div>";
      } elseif ( strlen($_POST['titel']) < 5 ) {
        echo "<div class=\"manage-controls mcerr\"><p>Fehler: Der Titel sollte mindestens 5 Zeichen lang sein!</p></div>";
      } elseif ( strlen($_POST['beschreibung']) < 5 ) {
        echo "<div class=\"manage-controls mcerr\"><p>Fehler: Die Beschreibung sollte mindestens 5 Zeichen lang sein!</p></div>";
      } elseif( strtotime($_POST['zeitbis']) < strtotime($_POST['zeitvon'])) {
        echo "<div class=\"manage-controls mcerr\"><p>Fehler: Die Endzeit ist früher als die Startzeit!</p></div>";
      } else {
        if($wpdb->update($ftname, array( 'TITEL' => $_POST['titel'], 'BESCHREIBUNG' => preg_replace("/\r\n|\r|\n/",'<br/>', $_POST['beschreibung']), 'LINKURL' => $_POST['linkurl'], 'KDATUM' => $_POST['datum'], 'ZEITVON' => $_POST['zeitvon'], 'ZEITBIS' => $_POST['zeitbis'], 'STD_MAX_KINDER' => $_POST['stdkidsmax']), array('ID' => $_POST['id']), array('%s', '%s', '%s', '%s', '%s', '%s', '%d'), array('%d')) !== FALSE) {
          echo "<div class=\"manage-controls mcok\"><p>Der Ferienkurs wurde aktualisiert - <a href=\"?page=mb-options-menu&action=managefk\">zur Ferienkurs-Übersicht</a></p></div>";
        } else {
          echo "<div class=\"manage-controls mcerr\"><p>Fehler: Der Ferienkurs konnte nicht aktualisiert werden!</p></div>";
        }
      }
    case "editfk":
      if(!isset($_GET['id'])) {
        echo "Fehlerhafte Anfrage: Keine zu bearbeitende ID angegeben!<br><a href='?page=mb-options-menu'>Startseite</a>";
        return;
      }

      $id = $_GET['id'];
      $row = $wpdb->get_row("SELECT TITEL, BESCHREIBUNG, LINKURL, KDATUM, ZEITVON, ZEITBIS, STD_MAX_KINDER FROM $ftname WHERE ID = $id");

      echo "<div class=\"manage-controls manage-list\"><h3 class=\"edit-title\">#$id - $row->TITEL</h3><form method=\"post\" action=\"\"><input type=\"hidden\" name=\"action\" value=\"editfk\"><input type=\"hidden\" name=\"id\" value=\"$id\"><table class=\"form-table manage-table\">";
      echo "<tbody>";
      echo "<tr valign=\"top\"><th scope=\"row\"><strong>Titel</strong></th><td><input type=\"text\" pattern=\".{5,}\" required title=\"Der Titel sollte mindestens 5 Zeichen lang sein\" name=\"titel\" value=\"" . $row->TITEL . "\"></td></tr>";
      echo "<tr valign=\"top\"><th scope=\"row\"><strong>Beschreibung</strong></th><td><textarea pattern=\".{5,}\" required title=\"Die Beschreibung sollte mindestens 5 Zeichen lang sein\" name=\"beschreibung\" cols=\"22\" rows=\"6\">" . preg_replace('/\<br\s*\/?\>/',"\n", $row->BESCHREIBUNG) . "</textarea></td></tr>";
      echo "<tr valign=\"top\"><th scope=\"row\"><strong>Link-URL</strong></th><td><input type=\"text\" name=\"linkurl\" value=\"" . $row->LINKURL . "\"></td></tr>";
      echo "<tr valign=\"top\"><th scope=\"row\"><strong>Datum</strong></th><td><input type=\"date\" name=\"datum\" id=\"datum\" value=\"" . date("Y-m-d", strtotime($row->KDATUM)) . "\"></td></tr>";
      echo "<tr valign=\"top\"><th scope=\"row\"><strong>Uhrzeit</strong></th><td><input type=\"time\" required min=\"00:00\" max=\"23:59\" name=\"zeitvon\" value=\"" . $row->ZEITVON . "\"> bis <input type=\"time\" required min=\"00:00\" max=\"23:59\" name=\"zeitbis\" value=\"" . $row->ZEITBIS . "\"></td></tr>";
      echo "<tr valign=\"top\"><th scope=\"row\"><strong>Max. Teilnehmer</strong></th><td><input type=\"hidden\"name=\"stdkids\" value=\"0\"><input type=\"number\" required min=\"1\" max=\"99\" name=\"stdkidsmax\" value=\"" . $row->STD_MAX_KINDER . "\"></td></tr>";
      echo "<tr valign=\"top\"><th scope=\"row\" class=\"btmrow\"><input type=\"submit\" class=\"button button-primary\" value=\"Bearbeiten\"><a href=\"?page=mb-options-menu&action=deletefk&id=$id\" class=\"button button-warn\">Löschen</a></th></tr>";
      echo "</tbody></table></form></div>";
      break;
    case "managefk":
      echo '<table class="form-table"><thead><tr><th colspan="1" class="manage-title"><h3>Ferienkurse</h3></th></tr>';
      echo "<tr><th class=\"mctools-th\"><div class=\"manage-controls mctop mctools-div\"><a href=\"?page=mb-options-menu&action=addfk\" class=\"button button-primary\">Erstellen</a><a href=\"?page=mb-options-menu&action=clrfk\" class=\"button button-primary\">Vergangene Kurse löschen</a><a href=\"?page=mb-options-menu&action=wipefk\" class=\"button button-primary\">Alle Kurse löschen</a><a href=\"?page=mb-options-menu&action=config#ferien\" class=\"button button-primary\">Ferien festlegen</a></div></th></tr>";
      echo '</thead><tbody>';
      foreach( $wpdb->get_results("SELECT ID, TITEL, STD_MAX_KINDER, STD_KINDER, KDATUM, ZEITVON, ZEITBIS FROM $ftname ORDER BY KDATUM, ZEITVON") as $key => $row) {
        echo "<tr><td><div class=\"manage-controls manage-table\"><table><tr><td><p><a href=\"?page=mb-options-menu&action=editfk&id=" . $row->ID . "\">" . $row->TITEL . "</a><br><small>" . date("d.m.Y", strtotime($row->KDATUM)) . ", " . date('G:i', strtotime($row->ZEITVON)) . " - " . date('G:i', strtotime($row->ZEITBIS)) . " Uhr</small></p></td>";
        echo "<td><div class=\"qty btns_added\"><form method=\"post\" action=\"\"><input type=\"hidden\" name=\"action\" value=\"managefk\"><input type=\"hidden\" name=\"wtag\" value=\"$day\"><input type=\"hidden\" name=\"day\" value=\"" . $row->TAG . "\"><input type=\"button\" value=\"-\" class=\"minus\" onclick=\"document.getElementById('kids" . $row->ID . "').stepDown(1);\">";
        echo "<input type=\"number\" name=\"kids" . $row->ID . "\" id=\"kids" . $row->ID . "\" min=\"0\" max=\"" . $row->STD_MAX_KINDER . "\" value=\"" . $row->STD_KINDER . "\" title=\"Qty\" class=\"input-text qt text\" size=\"4\" pattern=\"\" inputmode=\"\"><input type=\"button\" value=\"+\" class=\"plus\" onclick=\"document.getElementById('kids" . $row->ID . "').stepUp(1);\">";
        //echo "<a href=\"#\"  class=\"button button-primary\">-</a>&nbsp;<input type=\"number\" >&nbsp;<a href=\"#\"  class=\"button button-primary\">+</a>
        //echo "<input type=\"submit\" class=\"button\" name=\"subm\" value=\" . $row->ID . \" alt=\"OK\"></div></td></tr></table></div></td></tr>";
        echo "<button type=\"submit\" class=\"button\" name=\"subm\" value=\"$row->ID\">OK</button></div></td></tr></table></div></td></tr>";
        #echo "<tr><td><div class=\"manage-controls manage-table\"><table><tr><td>" . $row->TITEL . "</td><td><a href=\"#\" onclick=\"document.getElementById('kids" . $row->ID . "').stepDown(1);\" class=\"button button-primary\">-</a>&nbsp;<input type=\"number\" name=\"kids" . $row->ID . "\" id=\"kids" . $row->ID . "\" min=\"0\" max=\"" . $row->STD_MAX_KINDER . "\" value=\"" . $row->STD_KINDER . "\">&nbsp;<a href=\"#\" onclick=\"document.getElementById('kids" . $row->ID . "').stepUp(1);\" class=\"button button-primary\">+</a></td></tr></table></div></td></tr>";
      }
      echo "</tbody></table>";
      break;
    case "POST_managefk":
      $id = $_POST['subm'];
      $varz = "kids" . $id;
      if(!isset($_POST[$varz])) {
        echo "<p>Fehlerhafte Anfrage - unbekanntes Feld / unbekannte Stunden-ID \"" . $varz . "\"</p><a href=\"javascript:location.reload()\">Reload</a>";
        return;
      }

      $day = $_POST['wtag'];

      $wpdb->update($ftname, array( 'STD_KINDER' => $_POST[$varz]), array('ID' => $id), array('%d'), array('%d'));
      echo "<p>POST_managefk</p><p>";

      echo $id;
      echo "<br>";
      echo $day;
      //echo $varz;
      echo dnum($day);
      echo "<br>";
      echo $_POST['kids' . $id];
      echo "</p>";
      break;
    case "deletefk":
      if(!isset($_GET['id'])) {
        echo "Fehlerhafte Anfrage: Keine zu löschende Ferienkurs-ID angegeben!<br><a href='?page=mb-options-menu'>Startseite</a>";
        return;
      }
      $id = $_GET['id'];

      $row = $wpdb->get_row("SELECT TITEL, KDATUM, ZEITVON, ZEITBIS FROM $ftname WHERE ID = $id");

      echo "<div class=\"manage-controls\"><p>Möchten Sie den Ferienkurs #$id &quot;" . $row->TITEL . "&quot; am " . date("d.m.Y", strtotime($row->KDATUM)) . " von " . $row->ZEITVON . " bis ". $row->ZEITBIS . " Uhr wirklich löschen?</p><form method=\"post\" action=\"\"><input type=\"hidden\" name=\"action\" value=\"deletefk\"><input type=\"hidden\" name=\"id\" value=\"$id\"><table class=\"form-table manage-table\">";
      echo "<tbody><tr valign=\"top\"><th scope=\"row\" class=\"btmrow\"><a href=\"?page=mb-options-menu&action=editfk&id=$id\" class=\"button button-primary\">Abbrechen</a></th><td><input type=\"submit\" class=\"button button-warning\" value=\"Löschen\"></td></tr>";
      echo "</tbody></table></form></div>";
      break;
    case "POST_deletefk":
      if(!isset($_POST['id'])) {
        echo "Fehlerhafte Anfrage: Keine zu löschende Ferienkurs-ID angegeben!<br><a href='?page=mb-options-menu'>Startseite</a>";
        return;
      }
      if($wpdb->delete($ftname, array( 'ID' => $_POST['id']), array('%d')) !== FALSE) {
        echo "<div class=\"manage-controls mcok\"><p>Der Ferienkurs #" . $_POST['id'] . " wurde gelöscht - <a href=\"?page=mb-options-menu&action=managefk\">zur Übersicht</a></p></div>";
      } else {
        echo "<div class=\"manage-controls mcerr\"><p>Fehler: Der Ferienkurs konnte nicht gelöscht werden!</p></div>";
      }
      break;
    case "clrfk":
      $leg = $wpdb->get_results("SELECT TITEL, KDATUM, ZEITVON, ZEITBIS FROM $ftname WHERE KDATUM < CURDATE() ORDER BY KDATUM, ZEITVON");
      echo "<div class=\"manage-controls\"><h3>Möchten Sie die folgenden vergangenen " . count($leg) . " Ferienkurse wirklich löschen?</h3><ul>";
      foreach( $leg as $key => $row) {
        echo "<li>" . $row->TITEL . " (" . date("d.m.Y", strtotime($row->KDATUM)) . ", " . date('G:i', strtotime($row->ZEITVON)) . "-" . date('G:i', strtotime($row->ZEITBIS))  . " Uhr)</li>";
      }
      echo "</ul><form method=\"post\" action=\"\"><input type=\"hidden\" name=\"action\" value=\"clrfk\"><div class=\"del-btns\"><a href=\"?page=mb-options-menu&action=managefk\" class=\"button button-primary\">Abbrechen</a><input type=\"submit\" class=\"button button-warn\" value=\"Löschen\"></div>";
      echo "</form></div>";
      break;
    case "POST_clrfk":
      if($wpdb->query("DELETE FROM $ftname WHERE KDATUM < CURDATE()") !== FALSE) {
        echo "<div class=\"manage-controls mcok\"><p>Alle vergangenen Ferienkurse wurden gelöscht - <a href=\"?page=mb-options-menu&action=managefk\">zur Übersicht</a></p></div>";
      } else {
        echo "<div class=\"manage-controls mcerr\"><p>Fehler: Die Ferienkurse konnten nicht gelöscht werden!</p></div>";
      }
      break;
    case "wipefk":
      $leg = $wpdb->get_results("SELECT TITEL, KDATUM, ZEITVON, ZEITBIS FROM $ftname ORDER BY KDATUM, ZEITVON");
      echo "<div class=\"manage-controls\"><h3>Möchten Sie wirklich ALLE " . count($leg) . " Ferienkurse entgültig löschen?</h3><ul>";
      foreach( $leg as $key => $row) {
        echo "<li>" . $row->TITEL . " (" . date("d.m.Y", strtotime($row->KDATUM)) . ", " . date('G:i', strtotime($row->ZEITVON)) . "-" . date('G:i', strtotime($row->ZEITBIS))  . " Uhr)</li>";
      }
      echo "</ul><form method=\"post\" action=\"\"><input type=\"hidden\" name=\"action\" value=\"clrfk\"><div class=\"del-btns\"><a href=\"?page=mb-options-menu&action=managefk\" class=\"button button-primary\">Abbrechen</a><input type=\"submit\" class=\"button button-warn\" value=\"Löschen\"></div>";
      echo "</form></div>";
      break;
    case "POST_wipefk":
      if($wpdb->query("DELETE FROM $ftname") !== FALSE) {
        echo "<div class=\"manage-controls mcok\"><p>Alle Ferienkurse wurden gelöscht - <a href=\"?page=mb-options-menu&action=managefk\">zur Übersicht</a></p></div>";
      } else {
        echo "<div class=\"manage-controls mcerr\"><p>Fehler: Die Ferienkurse konnten nicht gelöscht werden!</p></div>";
      }
      break;
    case "manage":
      if(!isset($_GET['day'])) {
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
      foreach( $wpdb->get_results("SELECT ID, TITEL, ZEITVON, ZEITBIS, STD_MAX_KINDER, STD_KINDER, OVR_DATUM, OVR_KINDER FROM $utname WHERE TAG = $day ORDER BY ZEITVON") as $key => $row) {
        echo "<tr><td><div class=\"manage-controls manage-table\"><table><tr><td><p><a href=\"?page=mb-options-menu&action=edit&id=" . $row->ID . "\">" . $row->TITEL . "</a><br><small>" . date("G:i", strtotime($row->ZEITVON)) . "-" . date("G:i", strtotime($row->ZEITBIS)) . " Uhr</small></p></td>";
        echo "<td><div class=\"qty btns_added\"><form method=\"post\" action=\"\"><input type=\"hidden\" name=\"action\" value=\"manage\"><input type=\"hidden\" name=\"wtag\" value=\"$day\"><input type=\"hidden\" name=\"day\" value=\"" . $row->TAG . "\"><input type=\"button\" value=\"-\" class=\"minus\" onclick=\"document.getElementById('kids" . $row->ID . "').stepDown(1);\">";
        if (!is_null($row->OVR_DATUM)) {
          //if((date('Ymd') == date('Ymd', strtotime($row->OVR_DATUM))) && !($row->OVR_KINDER == $row->STD_KINDER)) {
          if(($dayte == date('Ymd', strtotime($row->OVR_DATUM))) && !($row->OVR_KINDER == $row->STD_KINDER)) {
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
      if(!isset($_POST[$varz])) {
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
      update_option( 'std1', $_POST['std1']);
      update_option( 'std2', $_POST['std2']);
      update_option( 'std3', $_POST['std3']);
      update_option( 'std4', $_POST['std4']);
      update_option( 'std5', $_POST['std5']);
      update_option( 'std6', $_POST['std6']);
      update_option( 'ferientitel', $_POST['ferientitel']);
      update_option( 'ferien_following', (isset($_POST['ferien_following'])) ? 'TRUE' : 'FALSE');
      update_option( 'show_all_days', (isset($_POST['show_all_days'])) ? 'TRUE' : 'FALSE');
      update_option( 'show_saturday', (isset($_POST['show_saturday'])) ? 'TRUE' : 'FALSE');
      update_option( 'show_sunday', (isset($_POST['show_sunday'])) ? 'TRUE' : 'FALSE');
      /*if(isset($_POST['show_all_days'])) {
        echo "TRUE";
        update_option( 'show_all_days' , 'TRUE');
      } else {
        update_option( 'show_all_days' , 'FALSE');
      }*/
      echo "<div class=\"manage-controls mcok\"><p>Die Konfiguration wurde gespeichert!</p></div>";
    case "config":
      echo "<div class=\"manage-controls\"><form method=\"post\" action=\"\"><input type=\"hidden\" name=\"action\" value=\"config\"><table class=\"form-table cfg-table\">";
      echo "<tbody>";
      echo "<tr valign=\"top\"><td colspan=\"2\"><h3>Angebot-Links</h3></td></tr>";
      echo "<tr valign=\"top\"><th scope=\"row\"><strong>Link bei Ponyführstunden</strong></th><td><input type=\"text\" name=\"std1\" value=\"" . esc_attr( get_option('std1') ) . "\"/></td></tr>";
      echo "<tr valign=\"top\"><th scope=\"row\"><strong>Link bei Shettyreitstunden</strong></th><td><input type=\"text\" name=\"std2\" value=\"" . esc_attr( get_option('std2') ) . "\"/></td></tr>";
      echo "<tr valign=\"top\"><th scope=\"row\"><strong>Link bei Gruppenreitstunden</strong></th><td><input type=\"text\" name=\"std3\" value=\"" . esc_attr( get_option('std3') ) . "\"/></td></tr>";
      echo "<tr valign=\"top\"><th scope=\"row\"><strong>Link bei Erwachsenenreitstunden</strong></th><td><input type=\"text\" name=\"std4\" value=\"" . esc_attr( get_option('std4') ) . "\"/></td></tr>";
      echo "<tr valign=\"top\"><th scope=\"row\"><strong>Link bei Pferdezeiten</strong></th><td><input type=\"text\" name=\"std5\" value=\"" . esc_attr( get_option('std5') ) . "\"/></td></tr>";
      echo "<tr valign=\"top\"><th scope=\"row\"><strong>Link bei sonstige</strong></th><td><input type=\"text\" name=\"std6\" value=\"" . esc_attr( get_option('std6') ) . "\"/></td></tr>";
      echo "<tr valign=\"top\"><td colspan=\"2\" class=\"cfg-spacer\"><hr></td></tr>";
      echo "<tr valign=\"top\"><td colspan=\"2\"><h3>Anzeige-Einstellungen</h3></td></tr>";
      echo "<tr valign=\"top\"><th scope=\"row\"><strong>Standard-Anzeigeart</strong></th><td><input type=\"checkbox\" name=\"show_all_days\" value=\"alldays\"" . (get_option('show_all_days') == 'TRUE' ? 'checked' : '') . "> Alle Tage zeigen</td></tr>";
      echo "<tr valign=\"top\"><th scope=\"row\"><strong>Angezeigte Tage</strong></th><td><input type=\"checkbox\" name=\"show_saturday\" value=\"showsat\"" . (get_option('show_saturday') == 'TRUE' ? 'checked' : '') . "> Samstag zeigen</td></tr>";
      echo "<tr valign=\"top\"><th scope=\"row\">&nbsp;</th><td><input type=\"checkbox\" name=\"show_sunday\" value=\"showsun\"" . (get_option('show_sunday') == 'TRUE' ? 'checked' : '') . "> Sonntag zeigen</td></tr>";
      echo "<tr valign=\"top\"><td colspan=\"2\" class=\"cfg-spacer\"><hr></td></tr>";
      echo "<tr valign=\"top\"><td colspan=\"2\"><h3 id=\"ferien\">Ferien-Einstellungen</h3></td></tr>";
      echo "<tr valign=\"top\"><th scope=\"row\"><strong>Ferien-Titel</strong></th><td><input type=\"text\" name=\"ferientitel\" value=\"" . esc_attr( get_option('ferientitel') ) . "\"/></td></tr>";
      echo "<tr valign=\"top\"><th scope=\"row\"><strong>Anzeigeart</strong></th><td><input type=\"checkbox\" name=\"ferien_following\" value=\"follow\"" . (get_option('ferien_following') == 'TRUE' ? 'checked' : '') . "> Nur zukünftige Kurse anzeigen</td></tr>";
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
      if(!isset($_POST['typ'])) {
        echo "Kein Typ angegeben!";
        break;
      }

      if(in_array($_POST['typ'], array('stunden', 'ferienkurs'))) {
        if(isset($_POST['value'])) {
          echo "<div class=\"manage-controls\"><h4>Shortcode-Generator</h4><p>Um das Reitbuch in eine Seite einzubinden werden sog. Shortcodes verwendet - diese werden einfach als normaler Text im Seiteneditor eingefügt. Verwenden Sie dieses Werkzeug um diese einfach zu generieren!</p><hr>";
          echo "<table class=\"form-table manage-table\">";
          echo "<tbody>";
          echo "<tr valign=\"top\"><th scope=\"row\"><strong>Shortcode</strong></th><td><input type=\"text\" readonly name=\"sc\" value=\"[" . $_POST['typ'] . " " . ($_POST['typ'] == "stunden" ? "angebot" : "titel") . "=" . $_POST['value'] . "]\"></td></tr>";
          echo "</tbody></table></div>"
        } elseif($_POST['typ'] == "stunden") {
          echo "<div class=\"manage-controls\"><h4>Shortcode-Generator</h4><p>Um das Reitbuch in eine Seite einzubinden werden sog. Shortcodes verwendet - diese werden einfach als normaler Text im Seiteneditor eingefügt. Verwenden Sie dieses Werkzeug um diese einfach zu generieren!</p><hr>";
          echo "<form method=\"post\" action=\"\"><input type=\"hidden\" name=\"action\" value=\"shortcode\"><input type=\"hidden\" name=\"typ\" value=\"stunden\"><table class=\"form-table manage-table\">";
          echo "<tbody>";
          echo "<tr valign=\"top\"><th scope=\"row\"><strong>Angebot:</strong></th><td><select name=\"typ\" id=\"typ\">";
          echo "<option value=\"1\">Ponyführstunde</option>";
          echo "<option value=\"2\">Shettyreitstunde</option>";
          echo "<option value=\"3\">Gruppenreitstunde</option>";
          echo "<option value=\"4\">Erwachsenenreitstunde</option>";
          echo "<option value=\"5\">Pferdezeit</option>";
          echo "<option value=\"6\">Ferienprogramm</option>";
          echo "</select></td></tr>";
          echo "<tr valign=\"top\"><th scope=\"row\" class=\"btmrow\"><input type=\"submit\" class=\"button button-primary\" value=\"Weiter\"></th></tr>";
          echo "</tbody></table></form></div>";
        }
      }


  }

  echo "</div></div>";
}

function mb_styles_init() {
  wp_register_style( 'admins', plugins_url('/assets/css/admin.css',__FILE__ ) );
  wp_enqueue_style('admins');
}

function ws_init() {
  wp_register_style( 'user', plugins_url('/assets/css/user.css',__FILE__ ) );
  wp_enqueue_style('user');
}

function show_book() {
  if(get_option('show_all_days') == 'TRUE') {
    show_book_all();
  } else {
    show_book_sd();
  }
}

function show_book_sd() {
  global $wpdb;
  $utname = $wpdb->prefix . "wmb_ust";
  if(!isset($_POST['wtag'])) {
    $day = date('N');
  } else {
    $day = $_POST['wtag'];
  }

  $dayte = date('Ymd', strtotime(dnum($day)));

  echo "<div class=\"manage-controls mctop\"><form method=\"post\" action=\"" . $_SERVER['REQUEST_URI'] . "\"><label class=\"selected-control\" for=\"day\">Wähle einen Tag aus:</label><select class=\"ws-selector\" name=\"wtag\" id=\"wtag\">";
  echo "<option value=\"1\"" . ($day == '1' ? 'selected' : '') . ">Montag</option>";
  echo "<option value=\"2\"" . ($day == '2' ? 'selected' : '') . ">Dienstag</option>";
  echo "<option value=\"3\"" . ($day == '3' ? 'selected' : '') . ">Mittwoch</option>";
  echo "<option value=\"4\"" . ($day == '4' ? 'selected' : '') . ">Donnerstag</option>";
  echo "<option value=\"5\"" . ($day == '5' ? 'selected' : '') . ">Freitag</option>";
  if(get_option('show_saturday') == 'TRUE') { echo "<option value=\"6\"" . ($day == '6' ? 'selected' : '') . ">Samstag</option>"; }
  if(get_option('show_sunday') == 'TRUE') { echo "<option value=\"7\"" . ($day == '7' ? 'selected' : '') . ">Sonntag</option>"; }
  echo "</select><input type=\"submit\" class=\"button ws-button\" value=\"Auswählen\"></form></div><br>";
  echo '<table class="form-table"><thead><tr><th colspan="2">Reitstunde</th></tr></thead><tbody>';
  foreach( $wpdb->get_results("SELECT ID, TITEL, TYP, ZEITVON, ZEITBIS, STD_MAX_KINDER, STD_KINDER, OVR_DATUM, OVR_KINDER FROM $utname WHERE TAG = $day ORDER BY ZEITVON") as $key => $row) {
    //echo "<tr><td>" . $row->TITEL . "</td>";
    if (!is_null($row->OVR_DATUM)) {
      if(($dayte == date('Ymd', strtotime($row->OVR_DATUM))) && !($row->OVR_KINDER == $row->STD_KINDER)) {
        $OVN = ($row->STD_MAX_KINDER - $row->OVR_KINDER);
      } else {
        $OVN = ($row->STD_MAX_KINDER - $row->STD_KINDER);
      }
    } else {
      $OVN = ($row->STD_MAX_KINDER - $row->STD_KINDER);
    }
    if($OVN < 1) {
      $OVC = "ws-std-full";
      $OVT = "Stunde voll";
    } else {
      $OVC = "ws-std-free";
      $OVT = $OVN . " Plätze frei";
    }
    echo "<tr class=\"cfg-last\"><td><p class=\"ws-std-title\">" . linkx($row->TYP, $row->TITEL) . "</p><small>" . date('G:i', strtotime($row->ZEITVON)) . " - " . date('G:i', strtotime($row->ZEITBIS)) . " Uhr</small></td>";
    echo "<td><input type=\"text\" value=\"" . $OVT . "\" title=\"Qty\" readonly class=\"ws-std-state $OVC\" size=\"5\"></td></tr>";
  }
  echo "</tbody></table>";
}

function current_day_array($cday) {
  switch($cday) {
    case 1:
      return array(1, 2, 3, 4, 5, 6, 7);
    case 2:
      return array(2, 3, 4, 5, 6, 7, 1);
    case 3:
      return array(3, 4, 5, 6, 7, 1, 2);
    case 4:
      return array(4, 5, 6, 7, 1, 2, 3);
    case 5:
      return array(5, 6, 7, 1, 2, 3, 4);
    case 6:
      return array(6, 7, 1, 2, 3, 4, 5);
    case 7:
      return array(7, 1, 2, 3, 4, 5, 6);
    default:
      return array(1, 2, 3, 4, 5, 6, 7);
  }
}

function show_book_all() {
  global $wpdb;
  $utname = $wpdb->prefix . "wmb_ust";

  $TNAME = array('', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag');

  $SSAT = (get_option('show_saturday', 'TRUE') == 'TRUE') ? TRUE : FALSE;
  $SSUN = (get_option('show_sunday', 'TRUE') == 'TRUE') ? TRUE : FALSE;

  $TAGE = current_day_array(date('N'));

  if($SSAT) {
    if (($key = array_search(6, $TAGE)) !== false) {
      unset($TAGE[$key]);
    }
  }

  if($SSUN) {
    if (($key = array_search(7, $TAGE)) !== false) {
      unset($TAGE[$key]);
    }
  }

  echo '<table class="form-table">';
  foreach( $TAGE as $TAGG) {
    $dayte = date('Ymd', strtotime(dnum($TAGG)));
    $arr = $wpdb->get_results("SELECT ID, TITEL, TYP, ZEITVON, ZEITBIS, STD_MAX_KINDER, STD_KINDER, OVR_DATUM, OVR_KINDER FROM $utname WHERE TAG = $TAGG ORDER BY ZEITVON");
    echo "<thead><tr><th colspan=\"2\">Reitstunde " . $TNAME[$TAGG] . "</th></tr></thead><tbody>";
    if(empty($arr)) {
      echo "<tr colspan=\"2\"><td><p class=\"ws-std-title\">Keine Stunden</p></td></tr></tbody>";
    } else {
      foreach($arr as $key => $row) {
        //echo "<tr><td>" . $row->TITEL . "</td>";
        if ($row->STD_MAX_KINDER == -1) {
          $OVN = -2;
        } elseif (!is_null($row->OVR_DATUM)) {
          if(($dayte == date('Ymd', strtotime($row->OVR_DATUM))) && !($row->OVR_KINDER == $row->STD_KINDER)) {
            if($row->OVR_KINDER == -1) {
              $OVN = -1;
            } else {
              $OVN = ($row->STD_MAX_KINDER - $row->OVR_KINDER);
            }
          } else {
            $OVN = ($row->STD_MAX_KINDER - $row->STD_KINDER);
          }
        } else {
          $OVN = ($row->STD_MAX_KINDER - $row->STD_KINDER);
        }
        if($OVN == 0) {
          $OVC = "ws-std-full";
          $OVT = "Stunde voll";
        } elseif($OVN == -1) {
          $OVC = "ws-std-can";
          $OVT = "Fällt aus";
        } elseif($OVN == -2) {
          $OVC = "ws-std-free";
          $OVT = "Plätze frei";
        } else {
          $OVC = "ws-std-free";
          $OVT = $OVN . " Plätze frei";
        }
        if( !next( $arr ) ) {
          $LCL = "ws-last";
        } else {
          $LCL = "";
        }
        echo "<tr class=\"" . $LCL . "\"><td><p class=\"ws-std-title\">" . linkx($row->TYP, $row->TITEL) . "</p><small>" . date('G:i', strtotime($row->ZEITVON)) . " - " . date('G:i', strtotime($row->ZEITBIS)) . " Uhr</small></td>";
        echo "<td><input type=\"text\" value=\"" . $OVT . "\" title=\"Qty\" readonly class=\"ws-std-state $OVC\" size=\"5\"></td></tr></tbody>";
      }
    }



  }

  echo "</table>";
  show_footer();
}





function show_cal_all() {
  global $wpdb;
  $utname = $wpdb->prefix . "wmb_ust";
  $ftname = $wpdb->prefix . "wmb_fpr";

  $TNAME = array('', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag');

  $SSAT = (get_option('show_saturday', 'TRUE') == 'TRUE') ? TRUE : FALSE;
  $SSUN = (get_option('show_sunday', 'TRUE') == 'TRUE') ? TRUE : FALSE;

  $TAGE = current_day_array(date('N'));

  if($SSAT) {
    if (($key = array_search(6, $TAGE)) !== false) {
      unset($TAGE[$key]);
    }
  }

  if($SSUN) {
    if (($key = array_search(7, $TAGE)) !== false) {
      unset($TAGE[$key]);
    }
  }

  echo '<table class="form-table">';
  foreach( $TAGE as $TAGG) {
    $tagdatum = date('Y-m-d', strtotime(dnum($TAGG)));

    $arr = $wpdb->get_results("SELECT * FROM ( SELECT 1 AS ID, TITEL, 6 AS TYP, BESCHREIBUNG, LINKURL, ZEITVON, ZEITBIS, STD_KINDER, STD_MAX_KINDER, NULL AS OVR_DATUM, NULL AS OVR_KINDER FROM $ftname WHERE KDATUM = '$tagdatum' UNION ALL SELECT 2 AS ID, TITEL, TYP, NULL AS BESCHREIBUNG, NULL AS LINKURL, ZEITVON, ZEITBIS, STD_KINDER, STD_MAX_KINDER, OVR_DATUM, OVR_KINDER FROM $utname WHERE TAG = $TAGG ) a ORDER BY ZEITVON");

    echo "<thead><tr><th colspan=\"2\" class=\"ws-header\">" . $TNAME[$TAGG] . ", den " . date('d.m.Y', strtotime(dnum($TAGG))) . "</th></tr></thead><tbody class=\"ws-table-content\">";

    foreach($arr as $key => $row) {
      if (!is_null($row->OVR_DATUM)) {
        if(($tagdatum == date('Y-m-d', strtotime($row->OVR_DATUM))) && !($row->OVR_KINDER == $row->STD_KINDER)) {
          $TN = $row->OVR_KINDER;
        } else {
          $TN = $row->STD_KINDER;
        }
      } else {
        $TN = $row->STD_KINDER;
      }

      if ($TN == -1) {
        $OVC = "ws-std-can";
        $OVT = "Fällt aus";
      } elseif($TN == $row->STD_MAX_KINDER) {
        $OVC = "ws-std-full";
        $OVT = "Belegt";
      } elseif( ($row->STD_MAX_KINDER == 1) && $TN == 0 ) {
        $OVC = "ws-std-free";
        $OVT = "Plätze frei";
      } elseif($TN < $row->STD_MAX_KINDER) {
        $OVC = "ws-std-free";
        $OVT = ($row->STD_MAX_KINDER - $TN) . " Plätze frei";
      } else {
        $OVC = "ws-std-full";
        $OVT = "unbekannt";
      }

      $LCL = (next($arr)) ? "" : "ws-last";
      echo ($row->TYP == 6) ? "<tr class=\"" . $LCL . "\"><td><p class=\"ws-fpr-title\">" . linkf($row->TITEL, $row->LINKURL) . "</p><small>" . date('G:i', strtotime($row->ZEITVON)) . " - " . date('G:i', strtotime($row->ZEITBIS)) . " Uhr</small></td>" : "<tr class=\"" . $LCL . "\"><td><p class=\"ws-std-title\">" . linkx($row->TYP, $row->TITEL) . "</p><small>" . date('G:i', strtotime($row->ZEITVON)) . " - " . date('G:i', strtotime($row->ZEITBIS)) . " Uhr</small></td>";
      echo "<td><input type=\"text\" value=\"" . $OVT . "\" title=\"Qty\" readonly class=\"ws-std-state $OVC\" size=\"5\"></td></tr>";
    }

    if(empty($arr)) {
      echo "<tr class=\"ws-last\" colspan=\"2\"><td><p class=\"ws-std-title\">Keine Stunden</p></td></tr></tbody>";
    }

    echo "</tbody>";

  }

  echo "</table>";
  show_footer();
}

function str_replace_first( $haystack, $needle, $replace ) {
  $pos = strpos($haystack, $needle);
  if ($pos !== false) {
    return substr_replace($haystack, $replace, $pos, strlen($needle));
  } else {
    return $haystack;
  }
}

function show_ferienkurs( $atts ) {
  global $wpdb;
  $ftname = $wpdb->prefix . "wmb_fpr";
  $a = shortcode_atts( array(
      'titel' => '%',
  ), $atts );
  $TNAME = array('', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag');

  $sql = "SELECT BESCHREIBUNG, KDATUM, ZEITVON, ZEITBIS, STD_MAX_KINDER, STD_KINDER FROM $ftname WHERE LOWER(TITEL) LIKE LOWER('" . $a['titel'] . "') ORDER BY KDATUM, ZEITVON";
  $kurse = $wpdb->get_results($sql);

  echo (!empty($kurse)) ? '<h2>' . str_replace("!", "", $a['titel']) . '</h2>' : '<p>Es wurde(n) kein(e) ' . str_replace("!", "", $a['titel']) . ' gefunden!</p>';
  //<table class="form-table"><thead><tr><th colspan="2">' . typn($a['angebot'], TRUE) . '</th></tr></thead><tbody>
  $PRE = "<p>A";
  $POST = "<p>";
  $BESCH = "<p>findet ein Ferienkurs statt (keine Beschreibung?)</p>";
  $MEHRMALS = (count($kurse) > 1);
  foreach( $kurse as $key => $row) {
    $KTIME = strtotime($row->KDATUM);
    $TAGNUM = date('N', $KTIME);
    $PRE .= "m " . $TNAME[$TAGNUM] . ", den " . date('d.m.', $KTIME);
    $PRE .= ($MEHRMALS) ? " von " . date('G:i', strtotime($row->ZEITVON)) . " &ndash; " . date('G:i', strtotime($row->ZEITBIS)) . " Uhr" : "";
    //TODO: Einzelner Kurs besseres Deutsch
    $PRE .= (next($kurse)) ? ", sowie<br>a" : "</p>";


    //$BESCH = (count($kurse) > 1) ? "<p>" . $row->BESCHREIBUNG . "</p>" : "<p>" .  . "</p>";

    $POST .= ($MEHRMALS) ? date('d.m.', $KTIME) . ": " : "";
    if ($row->STD_KINDER == -1) {
      $POST .= "Fällt aus";
    } elseif($row->STD_KINDER == $row->STD_MAX_KINDER) {
      $POST .= "Belegt";
    } elseif( ($row->STD_MAX_KINDER == 1) && $row->STD_KINDER == 0 ) {
      $POST .= "Plätze frei";
    } elseif($row->STD_KINDER < $row->STD_MAX_KINDER) {
      $POST .= ($row->STD_MAX_KINDER - $row->STD_KINDER) . " Plätze frei";
    } else {
      $POST .= "unbekannt";
    }
    $POST .= (next($kurse)) ? "<br>" : "</p>";
  }

  echo (startsWith($row->TITEL, "!")) ? "" : $PRE;
  echo ($MEHRMALS) ? "<p>" . $row->BESCHREIBUNG . "</p>" : "<p>" . str_replace_first($row->BESCHREIBUNG, "findet", "findet von " . date('G:i', strtotime($row->ZEITVON)) . " &ndash; " . date('G:i', strtotime($row->ZEITBIS)) . " Uhr") . "</p>";
  //echo "<p>";
  //echo $row->BESCHREIBUNG;
  //echo "</p>";
  //echo $BESCH;
  echo $POST;
  show_footer();
}



function show_ferienprogramm() {
  global $wpdb;
  $ftname = $wpdb->prefix . "wmb_fpr";
  $TNAME = array('', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag');
  $dapp = get_option('ferien_following') == 'TRUE' ? "WHERE KDATUM >= CURDATE()" : "";
  $sql = "SELECT TITEL, BESCHREIBUNG, KDATUM, ZEITVON, ZEITBIS, STD_MAX_KINDER, STD_KINDER FROM $ftname $dapp ORDER BY KDATUM, ZEITVON";
  $kurse = $wpdb->get_results($sql);
  $cfg_titel = get_option('ferientitel');

  echo "<h2>" . (strlen($cfg_titel) > 5 ? $cfg_titel : "Ferienprogramm") . "</h2>";
  echo (!empty($kurse)) ? '' : '<p>Es wurden keine Ferienkurse gefunden!</p>';
  //<table class="form-table"><thead><tr><th colspan="2">' . typn($a['angebot'], TRUE) . '</th></tr></thead><tbody>
  foreach( $kurse as $key => $row) {
    $KTIME = strtotime($row->KDATUM);
    $TAGNUM = date('N', $KTIME);
    echo "<h3>" . $row->TITEL . "</h3>";
    echo "<p>Am " . $TNAME[$TAGNUM] . ", den " . date('d.m.', $KTIME) . "</p>";
    echo "<p>" . str_replace_first($row->BESCHREIBUNG, "findet", "findet von " . date('G:i', strtotime($row->ZEITVON)) . " &ndash; " . date('G:i', strtotime($row->ZEITBIS)) . " Uhr") . "</p>";
    if ($row->STD_KINDER == -1) {
      echo "<p class=\"ws-fpr-can\">Fällt aus</p>";
    } elseif($row->STD_KINDER == $row->STD_MAX_KINDER) {
      echo "<p class=\"ws-fpr-full\">Belegt</p>";
    } elseif( ($row->STD_MAX_KINDER == 1) && $row->STD_KINDER == 0 ) {
      echo "<p class=\"ws-fpr-free\">Plätze frei</p>";
    } elseif($row->STD_KINDER < $row->STD_MAX_KINDER) {
      echo "<p class=\"ws-fpr-free\">" . ($row->STD_MAX_KINDER - $row->STD_KINDER) . " Plätze frei</p>";
    } else {
      echo "<p>Belegung unbekannt</p>";
    }
    echo "<br><br>";
  }

  echo "<h4>Viel Spaß!</h4>";
  show_footer();
}

function show_footer() {
  global $mb_db_version;
  echo "<br><span class=\"wmb-footer-text\">powered by WMBook " . $mb_db_version . " &copy; Fabian Schillig 2018</span>";
}

function startsWith($haystack, $needle)
{
     $length = strlen($needle);
     return (substr($haystack, 0, $length) === $needle);
}


function show_stunden( $atts ) {
  global $wpdb;
  $utname = $wpdb->prefix . "wmb_ust";
  $a = shortcode_atts( array(
      'angebot' => 3,
  ), $atts );

  echo '<table class="form-table"><thead><tr><th colspan="2">' . typn($a['angebot'], TRUE) . '</th></tr></thead><tbody>';
  foreach( $wpdb->get_results("SELECT ID, TITEL, ZEITVON, ZEITBIS, STD_MAX_KINDER, STD_KINDER, OVR_DATUM, OVR_KINDER FROM $utname WHERE TYP = " . $a['angebot'] . " ORDER BY TAG, ZEITVON") as $key => $row) {
    //echo "<tr><td>" . $row->TITEL . "</td>";
    if (!is_null($row->OVR_DATUM)) {
      if((date('Ymd', strtotime(dnum($row->TAG))) == date('Ymd', strtotime($row->OVR_DATUM))) && !($row->OVR_KINDER == $row->STD_KINDER)) {
        $OVN = ($row->STD_MAX_KINDER - $row->OVR_KINDER);
      } else {
        $OVN = ($row->STD_MAX_KINDER - $row->STD_KINDER);
      }
    } else {
      $OVN = ($row->STD_MAX_KINDER - $row->STD_KINDER);
    }
    if($OVN < 1) {
      $OVC = "ws-std-full";
      $OVT = "Stunde voll";
    } else {
      $OVC = "ws-std-free";
      $OVT = $OVN . " Plätze frei";
    }
    echo "<tr><td><p class=\"ws-std-title\">" . $row->TITEL . "</p><small>" . date('G:i', strtotime($row->ZEITVON)) . " - " . date('G:i', strtotime($row->ZEITBIS)) . " Uhr</small></td>";
    echo "<td><input type=\"text\" value=\"" . $OVT . "\" title=\"Qty\" readonly class=\"ws-std-state $OVC\" size=\"5\"></td></tr>";
  }
  echo "</tbody></table>";
}

register_activation_hook( __FILE__, 'mb_init' );
add_action( 'admin_menu', 'mb_menu' );
add_action('admin_enqueue_scripts', 'mb_styles_init');
add_action('wp_enqueue_scripts', 'ws_init');
add_shortcode('reitbuch_et', 'show_book_sd');
add_shortcode('reitbuch_all', 'show_book_all');
add_shortcode('reitbuch', 'show_book');
add_shortcode('reitkalender', 'show_cal_all');
add_shortcode('stunden', 'show_stunden');
add_shortcode('ferienkurs', 'show_ferienkurs');
add_shortcode('ferienprogramm', 'show_ferienprogramm');
 ?>
