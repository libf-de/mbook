<?php
/***
* Plugin Name: nuBook
* Plugin URI: https://xorg.ga/
* Description: Reitbuch für Wordpress
* Version: 2.1
* Author: Fabian Schillig
* License: GNU GPL
*/

require_once 'strutils.php';
require_once 'timeutil.php';

global $wpdb;
define('db_lessontemplates', $wpdb->prefix . "nubook_lessontemplates");
define('db_lessons', $wpdb->prefix . "nubook_lessons");
define('db_ferientemplates', $wpdb->prefix . "nubook_ferientemplates");
define('db_ferientermine', $wpdb->prefix . "nubook_ferientermine");
define('db_ferien', $wpdb->prefix . "nubook_ferien");

global $FERIENKURSE_TITEL;

global $nb_db_version;
$nb_db_version = '21';

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$plugin_root = trailingslashit( untrailingslashit( __DIR__ ));

foreach(glob($plugin_root . "inc/common/*.php") as $commonscript) {
  require_once($commonscript);
}

foreach(glob($plugin_root . "inc/admin/*.php") as $adminscript) {
  require_once($adminscript);
}

foreach(glob($plugin_root . "inc/frontend/*.php") as $frontendscript) {
  require_once($frontendscript);
}

foreach(glob($plugin_root . "inc/rest/*.php") as $restscript) {
  require_once($restscript);
}


function nb_menu() {
  add_menu_page( 'Reitbuch-Einstellungen', 'Reitbuch', 'manage_options', 'nb-options-menu', 'nb_options_ferien' );
  add_submenu_page( 'nb-options-menu', 'Ferienprogramm verwalten — nuBook', 'Ferienprogramm', 'manage_options', 'nb-options-menu', 'nb_options_ferien');
  add_submenu_page( 'nb-options-menu', 'Unterricht verwalten — nuBook', 'Unterricht', 'manage_options', 'nb-options-lessons', 'nb_options_lessons');
}

function legacy_linkx($inpt, $text) {
  $link = get_option('std' . $inpt);
  if(!is_null($link) && strlen($link) > 5) {
    return "<a href=\"" . $link . "\">" . $text . "</a>";
  } else {
    return $text;
  }
}

function legacy_linkf($text, $url) {
  return (!is_null($url) && strlen($url) > 5) ? "<a href=\"" . urlencode($url) . "\">$text</a>" : $text;
}

function legacy_dnum($inpt) {
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

function legacy_tnum($inpt) {
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

function nb_styles_init() {
  //wp_register_style( 'admins', plugins_url('/assets/css/admin.css',__FILE__ ) );
  //wp_enqueue_style('admins');
  wp_register_style( 'fa', plugins_url('/assets/css/fontawesome.min.css',__FILE__ ) );
  wp_register_style( 'fa-solid', plugins_url('/assets/css/solid.min.css',__FILE__ ) );
  wp_register_style( 'jqueryui', plugins_url('/assets/css/jquery-ui.min.css',__FILE__ ) );
  wp_register_style( 'jqueryui-theme', plugins_url('/assets/css/jquery-ui.theme.min.css',__FILE__ ) );
  wp_register_script( 'nbadminjs', plugins_url('/assets/js/nubook.admin.js', __FILE__) );
  wp_register_script( 'jquery-ui-multidate', plugins_url('/assets/js/jquery-ui.multidatespicker.js', __FILE__), array( 'jquery', 'jquery-ui-datepicker' ) );
  
  //neo-imports
  //Common
  wp_register_style( 'nb-common-css', plugins_url('/assets/css/common.css',__FILE__ ) );


  //Unterricht
  wp_register_style( 'nb-lessons-css', plugins_url('/assets/css/lessons.css',__FILE__ ) );
  wp_register_script( 'nb-lsadd-js', plugins_url('/assets/js/nubook.lessons.add.js', __FILE__) , array( 'jquery' ) );
  wp_register_script( 'nb-lslist-js', plugins_url('/assets/js/nubook.lessons.list.js', __FILE__) , array( 'jquery', 'wp-api' ) );

  //Ferien
  wp_register_script( 'nb-ltlist-js', plugins_url('/assets/js/nubook.lessontemplate.list.js', __FILE__) , array( 'jquery', 'wp-api' ) );

  wp_register_style( 'nb-flist-css', plugins_url('/assets/css/nubook.ferien.list.css',__FILE__ ) );

  wp_register_script( 'nb-fkadd-js', plugins_url('/assets/js/nubook.ferienkurs.add.js', __FILE__) , array( 'jquery', 'wp-api' ) );
  wp_register_script( 'nb-fklist-js', plugins_url('/assets/js/nubook.ferienkurs.list.js', __FILE__) , array( 'jquery', 'wp-api' ) );
  wp_register_style( 'nb-fklist-css', plugins_url('/assets/css/nubook.ferienkurs.list.css',__FILE__ ) );

  wp_register_style( 'nb-fkadd-css', plugins_url('/assets/css/nubook.ferienkurs.add.css',__FILE__ ) );

  wp_register_style( 'nb-fkcopy-css', plugins_url('/assets/css/nubook.ferienkurs.copy.css',__FILE__ ) );



  wp_register_script( 'nb-ferien-js', plugins_url('/assets/js/nubook.ferien.js', __FILE__) , array( 'wp-api' ) );

  wp_register_script( 'nbfkjs', plugins_url('/assets/js/nubook.ferienadmin.js', __FILE__), array( 'wp-api' ) );




  
  if(isset($_GET['action'])) {
    if($_GET['action'] == 'fkurs-add' or $_GET['action'] == 'fktemplates-add' or $_GET['action'] == 'fktemplates-edit') {
      wp_localize_script('nbfkjs', 'WPURL', array('queryurl' => admin_url( 'admin-post.php?action=nb_fk_query' )));
      wp_enqueue_script('nbfkjs');
      //wp_enqueue_script('nbfkjs');
    } else if($_GET['action'] == 'fkurs-manage') {
      
      
    }
  }
}

function nb_init_frontend() {
  wp_register_style( 'user', plugins_url('/assets/css/user.css',__FILE__ ) );
  wp_enqueue_style('user');


  wp_register_style( 'sc-ddtemplates', plugins_url('/assets/css/user.ddtemplates.css',__FILE__ ) );
  wp_register_script('sc-ddtemplates', plugins_url('/assets/js/user.ddtemplates.js', __FILE__) );
  wp_register_style( 'fa', plugins_url('/assets/css/fontawesome.min.css',__FILE__ ) );
  wp_register_style( 'fa-solid', plugins_url('/assets/css/solid.min.css',__FILE__ ) );
  

  wp_register_script( 'nbuserjs', plugins_url('/assets/js/nubook.user.js', __FILE__) );
  wp_register_style( 'nb-user-lessontable-css', plugins_url('/assets/css/nubook.user.lessontable.css',__FILE__ ) );
  wp_register_style( 'nb-user-lesson-css', plugins_url('/assets/css/nubook.user.lesson.css',__FILE__ ) );
  //wp_enqueue_script("jquery");
  //wp_enqueue_script('nbuserjs');
}

/*function show_book() {
  if(get_option('show_all_days') == 'TRUE') {
    show_book_all();
  } else {
    show_book_sd();
  }
}*/

/*function show_book_sd() {
  global $wpdb;
  $utname = $wpdb->prefix . "wnb_ust";
  if(!isset($_POST['wtag'])) {
    $day = date('N');
  } else {
    $day = $_POST['wtag'];
  }

  $dayte = date('Ymd', strtotime(legacy_dnum($day)));

  echo "<div class=\"nb-manage-controls mctop\"><form method=\"post\" action=\"" . $_SERVER['REQUEST_URI'] . "\"><label class=\"selected-control\" for=\"day\">Wähle einen Tag aus:</label><select class=\"ws-selector\" name=\"wtag\" id=\"wtag\">";
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
    echo "<tr class=\"cfg-last\"><td><p class=\"ws-std-title\">" . legacy_linkx($row->TYP, $row->TITEL) . "</p><small>" . date('G:i', strtotime($row->ZEITVON)) . " - " . date('G:i', strtotime($row->ZEITBIS)) . " Uhr</small></td>";
    echo "<td><input type=\"text\" value=\"" . $OVT . "\" title=\"Qty\" readonly class=\"ws-std-state $OVC\" size=\"5\"></td></tr>";
  }
  echo "</tbody></table>";
}*/

function legacy_current_day_array($cday) {
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

/*function show_book_all() {
  global $wpdb;
  $utname = $wpdb->prefix . "wnb_ust";

  $TNAME = array('', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag');

  $SSAT = (get_option('show_saturday', 'TRUE') == 'TRUE') ? TRUE : FALSE;
  $SSUN = (get_option('show_sunday', 'TRUE') == 'TRUE') ? TRUE : FALSE;

  $TAGE = legacy_current_day_array(date('N'));

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
    $dayte = date('Ymd', strtotime(legacy_dnum($TAGG)));
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
        echo "<tr class=\"" . $LCL . "\"><td><p class=\"ws-std-title\">" . legacy_linkx($row->TYP, $row->TITEL) . "</p><small>" . date('G:i', strtotime($row->ZEITVON)) . " - " . date('G:i', strtotime($row->ZEITBIS)) . " Uhr</small></td>";
        echo "<td><input type=\"text\" value=\"" . $OVT . "\" title=\"Qty\" readonly class=\"ws-std-state $OVC\" size=\"5\"></td></tr></tbody>";
      }
    }



  }

  echo "</table>";

  nb_show_footer();
}*/

/*function show_ftable() {
  if(isset($_GET["detail"])) {
    return showfk($_GET["detail"]);
  } else {
    return show_tab_fpo();
  }
}*/

/*function show_ftable_cat() {
  if(isset($_GET["detail"])) {
    if(isset($_GET['table'])) {
      return showfk_table($_GET["detail"]);
    } else {
      return showfk($_GET["detail"]);
    }
  } elseif (isset($_GET['table'])) {
    return show_tab_fpo();
  } else {
    return show_tab_fpc();
  }
}*/

//-----------------------------------------
/*function show_tab_fpc() {
  global $wpdb;
  $ret = '';
  setlocale(LC_ALL, 'de_DE@euro');
  $utname = $wpdb->prefix . "wnb_ust";
  $db_ferientermine = $wpdb->prefix . "wnb_fpr";

  $TNAME = array('', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa', 'So');
  $MNAME = array('', 'Jan', 'Feb', 'Mär', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez');

  $cfg_titel = get_option('ferientitel');
  $ret .= "<h2>" . (strlen($cfg_titel) > 5 ? $cfg_titel : "Ferienprogramm") . "</h2>";
  $ret .= "<p style=\"margin: 0 !important\">Termine für...</p>";

  $ret .= '<table class="form-table">';
  $dapp = get_option('ferien_following') == 'TRUE' ? "WHERE KDATUM >= CURDATE()" : "";
  $arr = $wpdb->get_results("SELECT DISTINCT TITEL FROM $db_ferientermine WHERE KDATUM >= CURDATE()");

  $ret .= "<tbody class=\"ws-table-content\">";
  foreach($arr as $key => $row) {
    $LCL = (next($arr)) ? "" : "ws-last";
    $ret .= "<tr class=\"" . $LCL . "\"><td><p class=\"ws-fp-title\"><a href=\"?detail=" . urlencode($row->TITEL) . "\">" . str_replace("!","",$row->TITEL) . "</a></p></td></tr>";
  }

  if(empty($arr)) {
    $ret .= "<tr class=\"ws-last\" colspan=\"2\"><td><p class=\"ws-std-title\">Keine Stunden</p></td></tr>";
  }

  $ret .= "</tbody>";
  $ret .= "</table>";
  $ret .= "<br><br><small><a class=\"daily\" href=\"?table\">Tagesansicht</a></small><br><br>";
  $ret .= nb_get_pfooter();
  return $ret;
}*/
//------------------------------------------

//++++++++++++++++++++++++++++++++++++++++++
/*function show_tab_fpo() {
  global $wpdb;
  $ret = '';
  setlocale(LC_ALL, 'de_DE@euro');
  $utname = $wpdb->prefix . "wnb_ust";
  $db_ferientermine = $wpdb->prefix . "wnb_fpr";

  $TNAME = array('', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa', 'So');
  $MNAME = array('', 'Jan', 'Feb', 'Mär', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez');

  $cfg_titel = get_option('ferientitel');
  $ret .= "<h2>" . (strlen($cfg_titel) > 5 ? $cfg_titel : "Ferienprogramm") . "</h2>";

  $SSAT = (get_option('show_saturday', 'TRUE') == 'TRUE') ? TRUE : FALSE;
  $SSUN = (get_option('show_sunday', 'TRUE') == 'TRUE') ? TRUE : FALSE;

  $TAGE = legacy_current_day_array(date('N'));

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

  $ret .= '<table class="form-table">';
  $dapp = get_option('ferien_following') == 'TRUE' ? "WHERE KDATUM >= CURDATE()" : "";
  $arr = $wpdb->get_results("SELECT TITEL, BESCHREIBUNG, KDATUM, ZEITVON, ZEITBIS, STD_MAX_KINDER, STD_KINDER, LINKURL FROM $db_ferientermine $dapp ORDER BY KDATUM, ZEITVON");

  $PREVDATE = "";

  foreach($arr as $key => $row) {
    $KDM = strtotime($row->KDATUM);
    if ($PREVDATE != $row->KDATUM) {
      $ret .= "<thead><tr><th colspan=\"2\" class=\"ws-header\">" . $TNAME[date("N", $KDM)] . ", " . date("d", $KDM) . ". " . $MNAME[date("n", $KDM)] . ". " . date("Y", $KDM) . "</th></tr></thead><tbody class=\"ws-table-content\">";
      $PREVDATE = $row->KDATUM;
    }
    $TN = $row->STD_KINDER;

    if ($TN == -1) {
      $OVC = "ws-std-can";
      $OVT = "Fällt aus";
    } elseif($TN == $row->STD_MAX_KINDER) {
      $OVC = "ws-std-full";
      $OVT = "Belegt";
    } elseif( ($row->STD_MAX_KINDER == 1) && $TN == 0 ) {
      $OVC = "ws-std-free";
      $OVT = "Frei";
    } elseif($TN < $row->STD_MAX_KINDER) {
      $OVC = "ws-std-free";
      $OVT = ($row->STD_MAX_KINDER - $TN) . (get_option('show_max_tn') == 'TRUE' ? " von " . $row->STD_MAX_KINDER . " Plätzen" : " Plätze") . " frei";
    } else {
      $OVC = "ws-std-full";
      $OVT = "unbekannt";
    }

    $LCL = (next($arr)) ? "" : "ws-last";
    $ret .= "<tr class=\"" . $LCL . "\"><td><p class=\"ws-fp-title\"><a href=\"?detail=" . urlencode($row->TITEL) . "\">" . str_replace("!", "", $row->TITEL) . "</a></p><small>" . date('G:i', strtotime($row->ZEITVON)) . " - " . date('G:i', strtotime($row->ZEITBIS)) . " Uhr</small></td>";
    $ret .= "<td>";
    $ret .= "<input type=\"text\" value=\"" . $OVT . "\" title=\"Qty\" readonly class=\"ws-std-state $OVC\" size=\"5\">";
    $ret .= "</td></tr>";
  }

  if(empty($arr)) {
    $ret .= "<tr class=\"ws-last\" colspan=\"2\"><td><p class=\"ws-std-title\">Keine Stunden</p></td></tr></tbody>";
  }

  $ret .= "</tbody>";
  $ret .= "</table>";
  $ret .= "<br><br><small><a href=\"?main\">Kategorieansicht</a></small><br><br>";
  $ret .= nb_get_pfooter();
  return $ret;
}*/
//++++++++++++++++++++++++++++++++++++++++++


//TODO: Samstag/Sonntag zeigen geht falsch herum
//TODO: Plätze frei bei Reitbuch


/*function show_cal_all() {
  global $wpdb;
  $utname = $wpdb->prefix . "wnb_ust";
  $db_ferientermine = $wpdb->prefix . "wnb_fpr";

  $TNAME = array('', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag');

  $SSAT = (get_option('show_saturday', 'TRUE') == 'TRUE') ? TRUE : FALSE;
  $SSUN = (get_option('show_sunday', 'TRUE') == 'TRUE') ? TRUE : FALSE;

  $TAGE = legacy_current_day_array(date('N'));

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
    $tagdatum = date('Y-m-d', strtotime(legacy_dnum($TAGG)));

    $arr = $wpdb->get_results("SELECT * FROM ( SELECT 1 AS ID, TITEL, 6 AS TYP, BESCHREIBUNG, LINKURL, ZEITVON, ZEITBIS, STD_KINDER, STD_MAX_KINDER, NULL AS OVR_DATUM, NULL AS OVR_KINDER FROM $db_ferientermine WHERE KDATUM = '$tagdatum' UNION ALL SELECT 2 AS ID, TITEL, TYP, NULL AS BESCHREIBUNG, NULL AS LINKURL, ZEITVON, ZEITBIS, STD_KINDER, STD_MAX_KINDER, OVR_DATUM, OVR_KINDER FROM $utname WHERE TAG = $TAGG ) a ORDER BY ZEITVON");

    echo "<thead><tr><th colspan=\"2\" class=\"ws-header\">" . $TNAME[$TAGG] . ", den " . date('d.m.Y', strtotime(legacy_dnum($TAGG))) . "</th></tr></thead><tbody class=\"ws-table-content\">";

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
        $OVT = ($row->STD_MAX_KINDER - $TN) . (get_option('show_max_tn') == 'TRUE' ? " von " . $row->STD_MAX_KINDER . " Plätzen" : " Plätze") . " frei";
      } else {
        $OVC = "ws-std-full";
        $OVT = "unbekannt";
      }

      $LCL = (next($arr)) ? "" : "ws-last";
      echo ($row->TYP == 6) ? "<tr class=\"" . $LCL . "\"><td><p class=\"ws-fpr-title\">" . legacy_linkf($row->TITEL, $row->LINKURL) . "</p><small>" . date('G:i', strtotime($row->ZEITVON)) . " - " . date('G:i', strtotime($row->ZEITBIS)) . " Uhr</small></td>" : "<tr class=\"" . $LCL . "\"><td><p class=\"ws-std-title\">" . legacy_linkx($row->TYP, $row->TITEL) . "</p><small>" . date('G:i', strtotime($row->ZEITVON)) . " - " . date('G:i', strtotime($row->ZEITBIS)) . " Uhr</small></td>";
      echo "<td><input type=\"text\" value=\"" . $OVT . "\" title=\"Qty\" readonly class=\"ws-std-state $OVC\" size=\"5\"></td></tr>";
    }

    if(empty($arr)) {
      echo "<tr class=\"ws-last\" colspan=\"2\"><td><p class=\"ws-std-title\">Keine Stunden</p></td></tr></tbody>";
    }

    echo "</tbody>";

  }

  echo "</table>";
  nb_show_footer();
}*/

/*function show_cal_fpo() {
  global $wpdb;
  $utname = $wpdb->prefix . "wnb_ust";
  $db_ferientermine = $wpdb->prefix . "wnb_fpr";

  $TNAME = array('', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag');

  $SSAT = (get_option('show_saturday', 'TRUE') == 'TRUE') ? TRUE : FALSE;
  $SSUN = (get_option('show_sunday', 'TRUE') == 'TRUE') ? TRUE : FALSE;

  $TAGE = legacy_current_day_array(date('N'));

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
    $tagdatum = date('Y-m-d', strtotime(legacy_dnum($TAGG)));

    $arr = $wpdb->get_results("SELECT * FROM ( SELECT 1 AS ID, TITEL, 6 AS TYP, 0 AS DBVON, BESCHREIBUNG, LINKURL, ZEITVON, ZEITBIS, STD_KINDER, STD_MAX_KINDER, NULL AS OVR_DATUM, NULL AS OVR_KINDER FROM $db_ferientermine WHERE KDATUM = '$tagdatum' UNION ALL SELECT 2 AS ID, TITEL, TYP, 1 AS DBVON, NULL AS BESCHREIBUNG, NULL AS LINKURL, ZEITVON, ZEITBIS, STD_KINDER, STD_MAX_KINDER, OVR_DATUM, OVR_KINDER FROM $utname WHERE TAG = $TAGG ) a ORDER BY ZEITVON");

    echo "<thead><tr><th colspan=\"2\" class=\"ws-header\">" . $TNAME[$TAGG] . ", den " . date('d.m.Y', strtotime(legacy_dnum($TAGG))) . "</th></tr></thead><tbody class=\"ws-table-content\">";

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
        $OVT = "Frei";
      } elseif($TN < $row->STD_MAX_KINDER) {
        $OVC = "ws-std-free";
        $OVT = ($row->STD_MAX_KINDER - $TN) . (get_option('show_max_tn') == 'TRUE' ? " von " . $row->STD_MAX_KINDER . " Plätzen" : " Plätze") . " frei";
      } else {
        $OVC = "ws-std-full";
        $OVT = "unbekannt";
      }

      $LCL = (next($arr)) ? "" : "ws-last";
      echo ($row->TYP == 6) ? "<tr class=\"" . $LCL . "\"><td><p class=\"ws-fpr-title\">" . legacy_linkf($row->TITEL, $row->LINKURL) . "</p><small>" . date('G:i', strtotime($row->ZEITVON)) . " - " . date('G:i', strtotime($row->ZEITBIS)) . " Uhr</small></td>" : "<tr class=\"" . $LCL . "\"><td><p class=\"ws-std-title\">" . legacy_linkx($row->TYP, $row->TITEL) . "</p><small>" . date('G:i', strtotime($row->ZEITVON)) . " - " . date('G:i', strtotime($row->ZEITBIS)) . " Uhr</small></td>";
      echo "<td>";
      if( $row->DBVON == 0) { echo "<input type=\"text\" value=\"" . $OVT . "\" title=\"Qty\" readonly class=\"ws-std-state $OVC\" size=\"5\">"; } else { echo "&nbsp;"; }
      echo "</td></tr>";
    }

    if(empty($arr)) {
      echo "<tr class=\"ws-last\" colspan=\"2\"><td><p class=\"ws-std-title\">Keine Stunden</p></td></tr></tbody>";
    }

    echo "</tbody>";

  }

  echo "</table>";
  nb_show_footer();
}*/



/*function show_cal_nop() {
  global $wpdb;
  $utname = $wpdb->prefix . "wnb_ust";
  $db_ferientermine = $wpdb->prefix . "wnb_fpr";

  $TNAME = array('', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag');

  $SSAT = (get_option('show_saturday', 'TRUE') == 'TRUE') ? TRUE : FALSE;
  $SSUN = (get_option('show_sunday', 'TRUE') == 'TRUE') ? TRUE : FALSE;

  $TAGE = legacy_current_day_array(date('N'));

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
    $tagdatum = date('Y-m-d', strtotime(legacy_dnum($TAGG)));

    $arr = $wpdb->get_results("SELECT * FROM ( SELECT 1 AS ID, TITEL, 6 AS TYP, BESCHREIBUNG, LINKURL, ZEITVON, ZEITBIS, STD_KINDER, STD_MAX_KINDER, NULL AS OVR_DATUM, NULL AS OVR_KINDER FROM $db_ferientermine WHERE KDATUM = '$tagdatum' UNION ALL SELECT 2 AS ID, TITEL, TYP, NULL AS BESCHREIBUNG, NULL AS LINKURL, ZEITVON, ZEITBIS, STD_KINDER, STD_MAX_KINDER, OVR_DATUM, OVR_KINDER FROM $utname WHERE TAG = $TAGG ) a ORDER BY ZEITVON");

    echo "<thead><tr><th colspan=\"1\" class=\"ws-header\">" . $TNAME[$TAGG] . ", den " . date('d.m.Y', strtotime(legacy_dnum($TAGG))) . "</th></tr></thead><tbody class=\"ws-table-content\">";

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
        $OVT = ($row->STD_MAX_KINDER - $TN) . (get_option('show_max_tn') == 'TRUE' ? " von " . $row->STD_MAX_KINDER . " Plätzen" : " Plätze") . " frei";
      } else {
        $OVC = "ws-std-full";
        $OVT = "unbekannt";
      }

      $LCL = (next($arr)) ? "" : "ws-last";
      echo ($row->TYP == 6) ? "<tr class=\"" . $LCL . "\"><td><p class=\"ws-fpr-title\">" . legacy_linkf($row->TITEL, $row->LINKURL) . "</p><small>" . date('G:i', strtotime($row->ZEITVON)) . " - " . date('G:i', strtotime($row->ZEITBIS)) . " Uhr</small></td>" : "<tr class=\"" . $LCL . "\"><td><p class=\"ws-std-title\">" . legacy_linkx($row->TYP, $row->TITEL) . "</p><small>" . date('G:i', strtotime($row->ZEITVON)) . " - " . date('G:i', strtotime($row->ZEITBIS)) . " Uhr</small></td>";
      echo "</tr>";
    }

    if(empty($arr)) {
      echo "<tr class=\"ws-last\" colspan=\"1\"><td><p class=\"ws-std-title\">Keine Stunden</p></td></tr></tbody>";
    }

    echo "</tbody>";

  }

  echo "</table>";
  nb_show_footer();
}*/



/*function show_cal_today() {
  global $wpdb;
  $utname = $wpdb->prefix . "wnb_ust";
  $db_ferientermine = $wpdb->prefix . "wnb_fpr";

  $TNAME = array('', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag');

  echo '<table class="form-table">';
  $TAGG = date('N');
  $tagdatum = date('Y-m-d');
  $arr = $wpdb->get_results("SELECT * FROM ( SELECT 1 AS ID, TITEL, 6 AS TYP, BESCHREIBUNG, LINKURL, ZEITVON, ZEITBIS, STD_KINDER, STD_MAX_KINDER, NULL AS OVR_DATUM, NULL AS OVR_KINDER FROM $db_ferientermine WHERE KDATUM = '$tagdatum' UNION ALL SELECT 2 AS ID, TITEL, TYP, NULL AS BESCHREIBUNG, NULL AS LINKURL, ZEITVON, ZEITBIS, STD_KINDER, STD_MAX_KINDER, OVR_DATUM, OVR_KINDER FROM $utname WHERE TAG = $TAGG ) a ORDER BY ZEITVON");

  echo "<thead><tr><th colspan=\"2\" class=\"ws-header\">" . $TNAME[$TAGG] . ", den " . date('d.m.Y') . "</th></tr></thead><tbody class=\"ws-table-content\">";

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
      $OVT = ($row->STD_MAX_KINDER - $TN) . (get_option('show_max_tn') == 'TRUE' ? " von " . $row->STD_MAX_KINDER . " Plätzen" : " Plätze") . " frei";
    } else {
      $OVC = "ws-std-full";
      $OVT = "unbekannt";
    }

    $LCL = (next($arr)) ? "" : "ws-last";
    echo ($row->TYP == 6) ? "<tr class=\"" . $LCL . "\"><td><p class=\"ws-fpr-title\">" . legacy_linkf($row->TITEL, $row->LINKURL) . "</p><small>" . date('G:i', strtotime($row->ZEITVON)) . " - " . date('G:i', strtotime($row->ZEITBIS)) . " Uhr</small></td>" : "<tr class=\"" . $LCL . "\"><td><p class=\"ws-std-title\">" . legacy_linkx($row->TYP, $row->TITEL) . "</p><small>" . date('G:i', strtotime($row->ZEITVON)) . " - " . date('G:i', strtotime($row->ZEITBIS)) . " Uhr</small></td>";
    echo "<td><input type=\"text\" value=\"" . $OVT . "\" title=\"Qty\" readonly class=\"ws-std-state $OVC\" size=\"5\"></td></tr>";
  }

  if(empty($arr)) {
    echo "<tr class=\"ws-last\" colspan=\"2\"><td><p class=\"ws-std-title\">Keine Stunden</p></td></tr></tbody>";
  }

  echo "</tbody>";



  echo "</table>";
  nb_show_footer();
}*/


function legacy_str_replace_first( $haystack, $needle, $replace ) {
  $pos = strpos($haystack, $needle);
  if ($pos !== false) {
    return substr_replace($haystack, $replace, $pos, strlen($needle));
  } else {
    return $haystack;
  }
}

/*function show_ferienkurse() {
  global $wpdb;
  $db_ferientermine = $wpdb->prefix . "wnb_fpr";
  $TNAME = array('', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag');
  $dapp = get_option('ferien_following') == 'TRUE' ? "WHERE KDATUM >= CURDATE() AND " : "WHERE ";

  $kursn = $wpdb->get_results("SELECT DISTINCT TITEL FROM $db_ferientermine WHERE KDATUM >= CURDATE()");

  foreach( $kursn as $kkey => $krow) {
    $kurs = $krow->TITEL;
    $sql = "SELECT BESCHREIBUNG, KDATUM, ZEITVON, ZEITBIS, STD_MAX_KINDER, STD_KINDER FROM $db_ferientermine $dapp LOWER(TITEL) LIKE LOWER('" . $kurs . "') ORDER BY KDATUM, ZEITVON";
    $kurse = $wpdb->get_results($sql);

    echo (!empty($kurse)) ? '<h2>' . str_replace("!", "", $kurs) . '</h2>' : '<p>Es wurde(n) kein(e) ' . str_replace("!", "", $kurs) . ' gefunden!</p>';
    //<table class="form-table"><thead><tr><th colspan="2">' . legacy_typname($a['angebot'], TRUE) . '</th></tr></thead><tbody>
    $PRE = "<p>A";
    $POST = "<p>";
    $BESCH = "<p>findet ein Ferienkurs statt (keine Beschreibung?)</p>";
    $MEHRMALS = (count($kurse) > 1);
    foreach( $kurse as $key => $row) {
      $KTIME = strtotime($row->KDATUM);
      $TAGNUM = date('N', $KTIME);
      $HASNEXT = next($kurse);
      $PRE .= "m " . $TNAME[$TAGNUM] . ", den " . date('d.m.', $KTIME);
      $PRE .= ($MEHRMALS) ? " von " . date('G:i', strtotime($row->ZEITVON)) . " &ndash; " . date('G:i', strtotime($row->ZEITBIS)) . " Uhr" : "";
      //TODO: Einzelner Kurs besseres Deutsch
      $PRE .= ($HASNEXT) ? ",<br>sowie<br>a" : "</p>";


      //$BESCH = (count($kurse) > 1) ? "<p>" . $row->BESCHREIBUNG . "</p>" : "<p>" .  . "</p>";
      $LABEL = ($MEHRMALS) ? date('d.m.', $KTIME) . ": " : "";
      if ($row->STD_KINDER == -1) {
        $POST .= "<font class=\"ws-fpr-can\">" . $LABEL . "Fällt aus</font>";
      } elseif($row->STD_KINDER == $row->STD_MAX_KINDER) {
        $POST .= "<font class=\"ws-fpr-full\">" . $LABEL . "Belegt</font>";
      } elseif( ($row->STD_MAX_KINDER == 1) && $row->STD_KINDER == 0 ) {
        $POST .= "<font class=\"ws-fpr-free\">" . $LABEL . "Plätze frei</font>";
      } elseif($row->STD_KINDER < $row->STD_MAX_KINDER) {
        $POST .= "<font class=\"ws-fpr-free\">" . $LABEL . ($row->STD_MAX_KINDER - $row->STD_KINDER) . (get_option('show_max_tn') == 'TRUE' ? " von " . $row->STD_MAX_KINDER . " Plätzen " : " Plätze ") . "frei</font>";
      } else {
        $POST .= "unbekannt";
      }
      $POST .= ($HASNEXT) ? "<br>" : "</p>";
    }

    echo (str_starts_with($krow->TITEL, "!")) ? "" : $PRE;
	$TIMESTR = "findet ";
	if( strtotime($row->ZEITBIS) < strtotime($row->ZEITVON)) {
        $TIMESTR .= "ab " . date('G:i', strtotime($row->ZEITVON)) . " Uhr";
	} else {
		$TIMESTR .= "von " . date('G:i', strtotime($row->ZEITVON)) . " &ndash; " . date('G:i', strtotime($row->ZEITBIS)) . " Uhr";
	}
    echo ($MEHRMALS) ? "<p>" . $row->BESCHREIBUNG . "</p>" : "<p>" . legacy_str_replace_first($row->BESCHREIBUNG, "findet", $TIMESTR) . "</p>";
    echo $POST;
    echo "<br><br>";
  }

  nb_show_footer();
}*/




/*function show_ferienkurs( $atts ) {
  global $wpdb;
  $ret = '';
  $db_ferientermine = $wpdb->prefix . "wnb_fpr";
  $a = shortcode_atts( array(
      'titel' => '%',
  ), $atts );
  $TNAME = array('', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag');
  $dapp = get_option('ferien_following') == 'TRUE' ? "WHERE KDATUM >= CURDATE() AND " : "WHERE ";
  $sql = "SELECT BESCHREIBUNG, KDATUM, ZEITVON, ZEITBIS, STD_MAX_KINDER, STD_KINDER FROM $db_ferientermine $dapp LOWER(TITEL) LIKE LOWER('" . $a['titel'] . "') ORDER BY KDATUM, ZEITVON";
  $kurse = $wpdb->get_results($sql);

  $ret .= (!empty($kurse)) ? '<h2>' . str_replace("!", "", $a['titel']) . '</h2>' : '<p>Es wurde(n) kein(e) ' . str_replace("!", "", $a['titel']) . ' gefunden!</p>';
  //<table class="form-table"><thead><tr><th colspan="2">' . legacy_typname($a['angebot'], TRUE) . '</th></tr></thead><tbody>
  $PRE = "<p>A";
  $POST = "<p>";
  $BESCH = "<p>findet ein Ferienkurs statt (keine Beschreibung?)</p>";
  $MEHRMALS = (count($kurse) > 1);
  foreach( $kurse as $key => $row) {
    $KTIME = strtotime($row->KDATUM);
    $TAGNUM = date('N', $KTIME);
    $HASNEXT = next($kurse);
    $PRE .= "m " . $TNAME[$TAGNUM] . ", den " . date('d.m.', $KTIME);
    $PRE .= ($MEHRMALS) ? " von " . date('G:i', strtotime($row->ZEITVON)) . " &ndash; " . date('G:i', strtotime($row->ZEITBIS)) . " Uhr" : "";
    //TODO: Einzelner Kurs besseres Deutsch
    $PRE .= ($HASNEXT) ? ",<br>sowie<br>a" : "</p>";


    //$BESCH = (count($kurse) > 1) ? "<p>" . $row->BESCHREIBUNG . "</p>" : "<p>" .  . "</p>";
    $LABEL = ($MEHRMALS) ? date('d.m.', $KTIME) . ": " : "";
    if ($row->STD_KINDER == -1) {
      $POST .= "<font class=\"ws-fpr-can\">" . $LABEL . "Fällt aus</font>";
    } elseif($row->STD_KINDER == $row->STD_MAX_KINDER) {
      $POST .= "<font class=\"ws-fpr-full\">" . $LABEL . "Belegt</font>";
    } elseif( ($row->STD_MAX_KINDER == 1) && $row->STD_KINDER == 0 ) {
      $POST .= "<font class=\"ws-fpr-free\">" . $LABEL . "Plätze frei</font>";
    } elseif($row->STD_KINDER < $row->STD_MAX_KINDER) {
      $POST .= "<font class=\"ws-fpr-free\">" . $LABEL . ($row->STD_MAX_KINDER - $row->STD_KINDER) . (get_option('show_max_tn') == 'TRUE' ? " von " . $row->STD_MAX_KINDER . " Plätzen " : " Plätze ") . "frei</font>";
    } else {
      $POST .= "unbekannt";
    }
    $POST .= ($HASNEXT) ? "<br>" : "</p>";
  }

  $ret .= (str_starts_with($row->TITEL, "!")) ? "" : $PRE;
  $TIMESTR = "findet ";
  if( strtotime($row->ZEITBIS) < strtotime($row->ZEITVON)) {
    $TIMESTR .= "ab " . date('G:i', strtotime($row->ZEITVON)) . " Uhr";
  } else {
	$TIMESTR .= "von " . date('G:i', strtotime($row->ZEITVON)) . " &ndash; " . date('G:i', strtotime($row->ZEITBIS)) . " Uhr";
  }
  $ret .= ($MEHRMALS) ? "<p>" . $row->BESCHREIBUNG . "</p>" : "<p>" . legacy_str_replace_first($row->BESCHREIBUNG, "findet", $TIMESTR) . "</p>";
  //echo "<p>";
  //echo $row->BESCHREIBUNG;
  //echo "</p>";
  //echo $BESCH;
  $ret .= $POST;
  $ret .= nb_get_pfooter();
  return $ret;
}*/

//http_build_query(array_merge($_GET, array("like"=>"like")))

/*function showfk( $name ) {
  global $wpdb;
  $ret = '';
  $db_ferientermine = $wpdb->prefix . "wnb_fpr";
  $TNAME = array('', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag');
  $dapp = get_option('ferien_following') == 'TRUE' ? "WHERE KDATUM >= CURDATE() AND " : "WHERE ";
  $sql = "SELECT TITEL, BESCHREIBUNG, KDATUM, ZEITVON, ZEITBIS, STD_MAX_KINDER, STD_KINDER FROM $db_ferientermine $dapp LOWER(TITEL) LIKE LOWER('" . $name . "') ORDER BY KDATUM, ZEITVON";
  $kurse = $wpdb->get_results($sql);

  $ret .= (!empty($kurse)) ? '<h2><a href="?main" style="text-decoration: none !important; box-shadow: none;">&#x2B05;</a> ' . str_replace("!", "", $name) . '</h2>' : '<p>Es wurde(n) kein(e) ' . str_replace("!", "", $name) . ' gefunden!</p>';
  //<table class="form-table"><thead><tr><th colspan="2">' . legacy_typname($a['angebot'], TRUE) . '</th></tr></thead><tbody>
  $PRE = "<p>A";
  $POST = "<p>";
  $BESCH = "<p>findet ein Ferienkurs statt (keine Beschreibung?)</p>";
  $MEHRMALS = (count($kurse) > 1);
  foreach( $kurse as $key => $row) {
    $KTIME = strtotime($row->KDATUM);
    $TAGNUM = date('N', $KTIME);
    $HASNEXT = next($kurse);
    $PRE .= "m " . $TNAME[$TAGNUM] . ", den " . date('d.m.', $KTIME);
    $PRE .= ($MEHRMALS) ? " von " . date('G:i', strtotime($row->ZEITVON)) . " &ndash; " . date('G:i', strtotime($row->ZEITBIS)) . " Uhr" : "";
    //TODO: Einzelner Kurs besseres Deutsch
    $PRE .= ($HASNEXT) ? ",<br>sowie<br>a" : "</p>";


    //$BESCH = (count($kurse) > 1) ? "<p>" . $row->BESCHREIBUNG . "</p>" : "<p>" .  . "</p>";
    $LABEL = ($MEHRMALS) ? date('d.m.', $KTIME) . ": " : "";
    if ($row->STD_KINDER == -1) {
      $POST .= "<font class=\"ws-fpr-can\">" . $LABEL . "Fällt aus</font>";
    } elseif($row->STD_KINDER == $row->STD_MAX_KINDER) {
      $POST .= "<font class=\"ws-fpr-full\">" . $LABEL . "Belegt</font>";
    } elseif( ($row->STD_MAX_KINDER == 1) && $row->STD_KINDER == 0 ) {
      $POST .= "<font class=\"ws-fpr-free\">" . $LABEL . "Plätze frei</font>";
    } elseif($row->STD_KINDER < $row->STD_MAX_KINDER) {
      $POST .= "<font class=\"ws-fpr-free\">" . $LABEL . ($row->STD_MAX_KINDER - $row->STD_KINDER) . (get_option('show_max_tn') == 'TRUE' ? " von " . $row->STD_MAX_KINDER . " Plätzen " : " Plätze ") . "frei</font>";
    } else {
      $POST .= "unbekannt";
    }
    $POST .= ($HASNEXT) ? "<br>" : "</p>";
  }

  $ret .= (str_starts_with($row->TITEL, "!")) ? "" : $PRE;
  $TIMESTR = "findet ";
  if( strtotime($row->ZEITBIS) < strtotime($row->ZEITVON)) {
    $TIMESTR .= "ab " . date('G:i', strtotime($row->ZEITVON)) . " Uhr";
  } else {
	$TIMESTR .= "von " . date('G:i', strtotime($row->ZEITVON)) . " &ndash; " . date('G:i', strtotime($row->ZEITBIS)) . " Uhr";
  }
  $ret .= ($MEHRMALS) ? "<p>" . $row->BESCHREIBUNG . "</p>" : "<p>" . legacy_str_replace_first($row->BESCHREIBUNG, "findet", $TIMESTR) . "</p>";
  //echo "<p>";
  //echo $row->BESCHREIBUNG;
  //echo "</p>";
  //echo $BESCH;
  $ret .= $POST;
  $ret .= nb_get_pfooter();
  return $ret;
}*/

/*function showfk_table( $name ) {
  global $wpdb;
  $ret = '';
  $db_ferientermine = $wpdb->prefix . "wnb_fpr";
  $TNAME = array('', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag');
  $dapp = get_option('ferien_following') == 'TRUE' ? "WHERE KDATUM >= CURDATE() AND " : "WHERE ";
  $sql = "SELECT TITEL, BESCHREIBUNG, KDATUM, ZEITVON, ZEITBIS, STD_MAX_KINDER, STD_KINDER FROM $db_ferientermine $dapp LOWER(TITEL) LIKE LOWER('" . $name . "') ORDER BY KDATUM, ZEITVON";
  $kurse = $wpdb->get_results($sql);

  $ret .= (!empty($kurse)) ? '<h2><a href="?table" style="text-decoration: none !important; box-shadow: none;">&#x2B05;</a> ' . str_replace("!", "", $name) . '</h2>' : '<p>Es wurde(n) kein(e) ' . str_replace("!", "", $name) . ' gefunden!</p>';
  //<table class="form-table"><thead><tr><th colspan="2">' . legacy_typname($a['angebot'], TRUE) . '</th></tr></thead><tbody>
  $PRE = "<p>A";
  $POST = "<p>";
  $BESCH = "<p>findet ein Ferienkurs statt (keine Beschreibung?)</p>";
  $MEHRMALS = (count($kurse) > 1);
  foreach( $kurse as $key => $row) {
    $KTIME = strtotime($row->KDATUM);
    $TAGNUM = date('N', $KTIME);
    $HASNEXT = next($kurse);
    $PRE .= "m " . $TNAME[$TAGNUM] . ", den " . date('d.m.', $KTIME);
    $PRE .= ($MEHRMALS) ? " von " . date('G:i', strtotime($row->ZEITVON)) . " &ndash; " . date('G:i', strtotime($row->ZEITBIS)) . " Uhr" : "";
    //TODO: Einzelner Kurs besseres Deutsch
    $PRE .= ($HASNEXT) ? ",<br>sowie<br>a" : "</p>";


    //$BESCH = (count($kurse) > 1) ? "<p>" . $row->BESCHREIBUNG . "</p>" : "<p>" .  . "</p>";
    $LABEL = ($MEHRMALS) ? date('d.m.', $KTIME) . ": " : "";
    if ($row->STD_KINDER == -1) {
      $POST .= "<font class=\"ws-fpr-can\">" . $LABEL . "Fällt aus</font>";
    } elseif($row->STD_KINDER == $row->STD_MAX_KINDER) {
      $POST .= "<font class=\"ws-fpr-full\">" . $LABEL . "Belegt</font>";
    } elseif( ($row->STD_MAX_KINDER == 1) && $row->STD_KINDER == 0 ) {
      $POST .= "<font class=\"ws-fpr-free\">" . $LABEL . "Plätze frei</font>";
    } elseif($row->STD_KINDER < $row->STD_MAX_KINDER) {
      $POST .= "<font class=\"ws-fpr-free\">" . $LABEL . ($row->STD_MAX_KINDER - $row->STD_KINDER) . (get_option('show_max_tn') == 'TRUE' ? " von " . $row->STD_MAX_KINDER . " Plätzen " : " Plätze ") . "frei</font>";
    } else {
      $POST .= "unbekannt";
    }
    $POST .= ($HASNEXT) ? "<br>" : "</p>";
  }

  $ret .= (str_starts_with($row->TITEL, "!")) ? "" : $PRE;
  $TIMESTR = "findet ";
  if( strtotime($row->ZEITBIS) < strtotime($row->ZEITVON)) {
    $TIMESTR .= "ab " . date('G:i', strtotime($row->ZEITVON)) . " Uhr";
  } else {
	$TIMESTR .= "von " . date('G:i', strtotime($row->ZEITVON)) . " &ndash; " . date('G:i', strtotime($row->ZEITBIS)) . " Uhr";
  }
   $ret .= ($MEHRMALS) ? "<p>" . $row->BESCHREIBUNG . "</p>" : "<p>" . legacy_str_replace_first($row->BESCHREIBUNG, "findet", $TIMESTR) . "</p>";
  //echo "<p>";
  //echo $row->BESCHREIBUNG;
  //echo "</p>";
  //echo $BESCH;
  $ret .=  $POST;
  $ret .= nb_get_pfooter();
  return $ret;
}*/

/*function show_ferienprogramm() {
  global $wpdb;
  $db_ferientermine = $wpdb->prefix . "wnb_fpr";
  $TNAME = array('', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag');
  $dapp = get_option('ferien_following') == 'TRUE' ? "WHERE KDATUM >= CURDATE()" : "";
  $sql = "SELECT TITEL, BESCHREIBUNG, KDATUM, ZEITVON, ZEITBIS, STD_MAX_KINDER, STD_KINDER FROM $db_ferientermine $dapp ORDER BY KDATUM, ZEITVON";
  $kurse = $wpdb->get_results($sql);
  $cfg_titel = get_option('ferientitel');

  echo "<h2>" . (strlen($cfg_titel) > 5 ? $cfg_titel : "Ferienprogramm") . "</h2>";
  echo (!empty($kurse)) ? '' : '<p>Es wurden keine Ferienkurse gefunden!</p>';
  //<table class="form-table"><thead><tr><th colspan="2">' . legacy_typname($a['angebot'], TRUE) . '</th></tr></thead><tbody>
  foreach( $kurse as $key => $row) {
    $KTIME = strtotime($row->KDATUM);
    $TAGNUM = date('N', $KTIME);
    echo "<h3>" . $row->TITEL . "</h3>";
    echo "<p>Am " . $TNAME[$TAGNUM] . ", den " . date('d.m.', $KTIME) . "</p>";
	$TIMESTR = "findet ";
    if( strtotime($row->ZEITBIS) < strtotime($row->ZEITVON)) {
      $TIMESTR .= "ab " . date('G:i', strtotime($row->ZEITVON)) . " Uhr";
    } else {
      $TIMESTR .= "von " . date('G:i', strtotime($row->ZEITVON)) . " &ndash; " . date('G:i', strtotime($row->ZEITBIS)) . " Uhr";
    }
    echo "<p>" . legacy_str_replace_first($row->BESCHREIBUNG, "findet", $TIMESTR) . "</p>";
    if ($row->STD_KINDER == -1) {
      echo "<p class=\"ws-fpr-can\">Fällt aus</p>";
    } elseif($row->STD_KINDER == $row->STD_MAX_KINDER) {
      echo "<p class=\"ws-fpr-full\">Belegt</p>";
    } elseif( ($row->STD_MAX_KINDER == 1) && $row->STD_KINDER == 0 ) {
      echo "<p class=\"ws-fpr-free\">Plätze frei</p>";
    } elseif($row->STD_KINDER < $row->STD_MAX_KINDER) {
      echo "<p class=\"ws-fpr-free\">" . ($row->STD_MAX_KINDER - $row->STD_KINDER) . (get_option('show_max_tn') == 'TRUE' ? " von " . $row->STD_MAX_KINDER . " Plätzen " : " Plätze ") . "frei</p>";
    } else {
      echo "<p>Belegung unbekannt</p>";
    }
    echo "<br><br>";
  }

  echo "<h4>Viel Spaß!</h4>";
  nb_show_footer();
}*/

/*function horse_age( $atts ) {
  global $wpdb;
  $pfname = $wpdb->prefix . "wnb_pfd";
  $a = shortcode_atts( array(
      'name' => '%',
  ), $atts );

  $sql = "SELECT NAME, TIMESTAMPDIFF(YEAR, GEBURT, CURDATE()) AS AGE FROM $pfname WHERE LOWER(NAME) LIKE LOWER('" . $a['name'] . "')";
  $pferd = $wpdb->get_row($sql);

  echo (empty($pferd)) ? $pferd->NAME . ' wurde nicht gefunden' : "<p>Alter: " . $pferd->AGE . " Jahre</p>";
}*/

/*function horse_birth( $atts ) {
  global $wpdb;
  $pfname = $wpdb->prefix . "wnb_pfd";
  $a = shortcode_atts( array(
      'name' => '%',
  ), $atts );

  $sql = "SELECT NAME, GEBURT FROM $pfname WHERE LOWER(NAME) LIKE LOWER('" . $a['name'] . "')";
  $pferd = $wpdb->get_row($sql);

  echo (empty($pferd)) ? $pferd->NAME . ' wurde nicht gefunden' : "<p>Geboren am " . date("d.m.Y", strtotime($pferd->GEBURT) . "</p>");
}*/

function nb_show_footer() {
  global $nb_db_version;
  echo "<span class=\"nb-footer-text\">powered by nuBook " . $nb_db_version . " &copy; Fabian Schillig 2022</span>";
}

function nb_get_pfooter() {
  global $nb_db_version;
  return "<span class=\"nb-footer-text\">powered by nuBook " . $nb_db_version . " &copy; Fabian Schillig 2022</span>";
}

/*function show_stunden( $atts ) {
  global $wpdb;
  $ret = '';
  $TNAME = array('', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag');
  $utname = $wpdb->prefix . "wnb_ust";
  $a = shortcode_atts( array(
      'angebot' => 3,
  ), $atts );

  $ret .= '<table class="form-table"><thead><tr><th colspan="2">' . legacy_typname($a['angebot'], TRUE) . '</th></tr></thead><tbody>';
  foreach( $wpdb->get_results("SELECT ID, TITEL, TAG, ZEITVON, ZEITBIS, STD_MAX_KINDER, STD_KINDER, OVR_DATUM, OVR_KINDER FROM $utname WHERE TYP = " . $a['angebot'] . " ORDER BY TAG, ZEITVON") as $key => $row) {
    //echo "<tr><td>" . $row->TITEL . "</td>";
    if (!is_null($row->OVR_DATUM)) {
      if((date('Ymd', strtotime(legacy_dnum($row->TAG))) == date('Ymd', strtotime($row->OVR_DATUM))) && !($row->OVR_KINDER == $row->STD_KINDER)) {
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
      //$OVC = "ws-std-free";
      $OVC = '';
      $OVT = '';
      //$OVT = $OVN . " Plätze frei";

    }
    $ret .= "<tr><td><p class=\"ws-std-title\">" . $TNAME[$row->TAG] . "</p><small>" . date('G:i', strtotime($row->ZEITVON)) . " - " . date('G:i', strtotime($row->ZEITBIS)) . " Uhr</small></td>";
    //$ret .= "<td><input type=\"text\" value=\"" . $OVT . "\" title=\"Qty\" readonly class=\"ws-std-state $OVC\" size=\"5\"></td></tr>";
    $ret .= "<td><p>&nbsp;</p></td></tr>";
  }
  $ret .= "</tbody></table>";
  return $ret;
}*/

register_activation_hook( __FILE__, 'nb_init' );
add_action( 'admin_menu', 'nb_menu' );
add_action('admin_enqueue_scripts', 'nb_styles_init');

add_action('admin_post_nb_lt_modify', 'handle_admin_lessontemplate_modify_post');
add_action('admin_post_nb_lt_delete', 'handle_admin_lessontemplate_delete_post');

add_action('admin_post_nb_ls_add', 'handle_admin_lessons_add_post');
add_action('admin_post_nb_ls_edit', 'handle_admin_lessons_edit_post');
add_action('admin_post_nb_ls_delete', 'handle_admin_lessons_delete_post');

add_action('admin_post_print', 'handle_admin_ferien_print' );
add_action('admin_post_export', 'handle_admin_ferien_export' );
add_action('admin_post_edelete', 'handle_admin_ferien_delete');

add_action('admin_post_nb_ft_delete', 'handle_admin_ferientemplate_delete_post');
add_action('admin_post_nb_ft_modify', 'handle_admin_ferientemplate_modify_post');

add_action('admin_post_nb_fe_modify', 'handle_admin_ferien_modify_post');
add_action('admin_post_nb_fe_standard', 'handle_admin_ferien_standard');
add_action('admin_post_nb_fe_active', 'handle_admin_ferien_active');
add_action('admin_post_nb_fe_delete', 'handle_admin_ferien_delete_post');
add_action('admin_post_nb_fe_import', 'handle_admin_ferien_import_post');

add_action('admin_post_nb_fk_add', 'handle_admin_ferienkurs_add_post');
add_action('admin_post_nb_fk_edit', 'handle_admin_ferienkurs_edit_post');
add_action('admin_post_nb_fk_delete', 'handle_admin_ferienkurs_delete_post');
add_action('admin_post_nb_fk_query', 'handle_admin_get_occupation_for_month');
add_action('admin_post_nb_fk_clean', 'handle_admin_ferienkurs_clean_post');
add_action('admin_post_nb_fk_copy', 'handle_admin_ferienkurs_copy_post');


add_action( 'wp_ajax_nb_get_kurse', 'handle_ajax_ferienkurs' );

add_action('wp_enqueue_scripts', 'nb_init_frontend');
add_action( 'rest_api_init', 'nb_api_init' );

add_shortcode('lesson-table', 'handle_user_lessontable');

add_shortcode('ftemplates', 'handle_user_templates');
add_shortcode('kategorietabelle', 'handle_user_categorytable');
add_shortcode('ferientabelle', 'handle_user_ferientable');
add_shortcode('stunden', 'handle_user_lesson');

/*add_shortcode('reitbuch_et', 'show_book_sd');
add_shortcode('reitbuch_all', 'show_book_all');
add_shortcode('reitbuch', 'show_book');
add_shortcode('reitkalender', 'show_cal_all');
add_shortcode('reitkalender_fpo', 'show_cal_fpo');*/

/*add_shortcode('ferientabelle_cat', 'show_ftable_cat');
add_shortcode('reitkalender_nop', 'show_cal_nop');
add_shortcode('rk_heute', 'show_cal_today');
add_shortcode('stunden', 'show_stunden');
add_shortcode('ferienkurs', 'show_ferienkurs');
add_shortcode('ferienprogramm', 'show_ferienprogramm');
add_shortcode('ferienkurse', 'show_ferienkurse');
add_shortcode('pferd_alter', 'horse_age');
add_shortcode('pferd_geburtstag', 'horse_birth');*/
 ?>
