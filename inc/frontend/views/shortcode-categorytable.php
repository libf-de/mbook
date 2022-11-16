<?php

$ret = '';

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
$ret .= nb_get_pfooter();
return $ret;
