<?php
$ferien_filter = isset($_GET['f']) ? $_GET['f'] : "1"; //TODO: Somehow store currently default Ferien somewhere
$ferien_title = $wpdb->get_var($wpdb->prepare("SELECT LABEL FROM " . db_ferien . " WHERE FID = %d LIMIT 1", $ferien_filter . "%"));

$dapp = "";

$sqltemplates = $wpdb->get_results("SELECT * FROM `$template` WHERE ID IN (SELECT TEMPLATE FROM `$termin` WHERE FERIEN = $ferien_filter $dapp) ORDER BY TITLE");

$sqlkurse = $wpdb->get_results("SELECT * FROM `$termin` $dapp ORDER BY DATESTART");

$kurse = array();

foreach($sqlkurse as $key => $item) {
    $kurse[$item->TEMPLATE][$key] = convertKursDT($item);
}

?>
<?php foreach($sqltemplates as $tkey => $trow): ?>
<div class="user-templates-outer">
    <div class="user-template-box">
        <div class="user-template-titlebox">
            <h5 class="user-template-title"><?= $trow->TITLE; ?>
            </h5>
            <i class="user-template-dropdown fa-solid fa-square-caret-down"></i>
        </div>
        <div class="user-template-spoiler">
            <div class="user-template-flex">
                <?= get_detail_html($kurse[$trow->ID], $trow->DESCRIPTION); ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <script type="text/javascript" defer>
        jQuery(document).ready(function($) {
            initDropdown();
        });
    </script>