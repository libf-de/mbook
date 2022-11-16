<?php
if (basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"]))
	die("do not call directly!");

$ret = '';

$savedFerien = get_option('standard_ferien');
$displayMode = 0; //0 = ALL, 1 = FOLLOWING, 2 = CURRENTLY

if ($displayMode == 1) {
    $activeFerien = $wpdb->get_results("SELECT * FROM `$ferien` WHERE ACTIVE = 1 AND ENDDATE >= CURDATE();", 'ARRAY_A');
} elseif ($displayMode == 2) {
    $activeFerien = $wpdb->get_results("SELECT * FROM `$ferien` WHERE ACTIVE = 1 AND ENDDATE >= CURDATE() AND STARTDATE <= CURDATE();", 'ARRAY_A');
} else {
    $activeFerien = $wpdb->get_results("SELECT * FROM `$ferien` WHERE ACTIVE = 1;", 'ARRAY_A');
}

$dapp = "";

/*$sqltemplates = $wpdb->get_results("SELECT * FROM `$template` WHERE ID IN (SELECT TEMPLATE FROM `$termin` WHERE FERIEN = $ferien_filter $dapp) ORDER BY TITLE");*/
$sqltemplates = $wpdb->get_results($wpdb->prepare("SELECT * FROM `$template` WHERE ID IN (SELECT TEMPLATE FROM `$termin` WHERE FERIEN IN (%s) $dapp) ORDER BY TITLE", implode(",", array_column($activeFerien, 'FID'))));

$sqlkurse = $wpdb->get_results("SELECT * FROM `$termin` $dapp ORDER BY DATESTART");

$kurse = array();

if (empty($sqlkurse) || empty($sqltemplates)) {
    return "<h4>Zur Zeit findet kein Ferienprogramm statt</h4>";
}

foreach ($sqlkurse as $key => $item) {
    $kurse[$item->TEMPLATE][$key] = convertKursDT($item);
}

$activeFerienTitles = array_column($activeFerien, 'LABEL');
natcasesort($activeFerienTitles);
$ftitle = array_pop($activeFerienTitles);
foreach (array_reverse($activeFerienTitles) as $ferie) {
    if ((stripos($ferie, 'ferien') + 6) - strlen($ferie) == 0) {
        $ftitle = str_ireplace("ferien", "-, ", $ferie) . $ftitle;
    } else {
        $ftitle = $ferie . ", " . $ftitle;
    }
}


$ret .= "<h3>{$ftitle}</h3>";
foreach ($sqltemplates as $tkey => $trow) {
    $ret .= "<div class=\"user-templates-outer\">
    <div class=\"user-template-box\">
        <div class=\"user-template-titlebox\">
            <h5 class=\"user-template-title\">{$trow->TITLE}
            </h5>
            <i class=\"user-template-dropdown fa-solid fa-square-caret-down\"></i>
        </div>
        <div class=\"user-template-spoiler\">
            <div class=\"user-template-flex\">
                " . get_detail_html($kurse[$trow->ID], $trow->DESCRIPTION) . "
            </div>
        </div>
    </div>";
}
$ret .= "<script type=\"text/javascript\" defer>
        jQuery(document).ready(function($) {
            initDropdown();
        });";
$ret .= "</script>";
return $ret;