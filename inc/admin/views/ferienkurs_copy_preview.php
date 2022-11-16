<?php
if(!is_numeric($_GET['ferien-src']) || !is_numeric($_GET['ferien-dst'])) {
    die("<h1>Invalid paramters: ferien-src, ferien-dst must be numeric!</h1>");
}

$src_ferien = $wpdb->get_row($wpdb->prepare("SELECT * FROM `$ferien` WHERE FID = %d", intval($_GET["ferien-src"])));
$dst_ferien = $wpdb->get_row($wpdb->prepare("SELECT * FROM `$ferien` WHERE FID = %d", intval($_GET["ferien-dst"])));

if($src_ferien == null || $dst_ferien == null) {
    die("<h1>Invalid ferien!</h1>");
}

$srcf_start = DateTime::createFromFormat('Y-m-d', $src_ferien->STARTDATE);
$dstf_start = DateTime::createFromFormat('Y-m-d', $dst_ferien->STARTDATE);

$src_kurse = $wpdb->get_results($wpdb->prepare("SELECT `$termin`.*, `$template`.TITLE, `$template`.EXP_LEVEL_MIN,
  `$template`.EXP_LEVEL_MAX FROM `$termin` INNER JOIN `$template` ON `$termin`.`TEMPLATE` = `$template`.`ID` WHERE
  `$termin`.FERIEN = %d ORDER BY `$termin`.`DATESTART` >= CURDATE() DESC, `$termin`.`DATESTART`", intval($_GET['ferien-src'])));
?>

<div class="nb-manage-controls">
    <form id="clear-form" method="post"
        action="<?= admin_url('admin-post.php?action=nb_fk_copy'); ?>">
        <input type="hidden" name="ferien-src" value="<?= $_GET['ferien-src']; ?>">
        <input type="hidden" name="ferien-dst" value="<?= $_GET['ferien-dst']; ?>">
        <table class="form-table nb-modify-table">
            <thead>
                <th class="nb-listhead-toolbox" colspan="3">
                    <h1>Ferienprogramm kopieren - <b>Vorschau</b></h1>
                </th>
            </thead>
            <tbody>
                <tr valign="top">
                    <td class="phead shrink">
                        <p style="font-size: larger;">Quelle:
                            <b><?= $src_ferien->LABEL ?></b></p>
                    </td>
                    <td class="phead expand">
                        <i class="fa-solid fa-arrow-right desktop"></i>
                        <i class="fa-solid fa-arrow-down mobile"></i>
                    </td>
                    <td class="phead shrink">
                        <p style="font-size: larger;">Ziel:
                            <b><?= $dst_ferien->LABEL ?></b></p>
                    </td>
                </tr>
                <?php foreach ($src_kurse as $key => $row):
                    $src_start = DateTime::createFromFormat(mysql_date, $row->DATESTART);

                    $daysDelta = $srcf_start->diff($src_start)->days;
                    $target = $src_start->format('l');
                    $dst_start = $dstf_start->modify("+{$daysDelta} days")->modify("-5 days")->modify("next $target");
                    $dst_start->setTime($src_start->format('H'), $src_start->format("i")); ?>
                <tr class="inner-tr">
                    <td class="shrink">
                        <div class="nb-listelem-outer manage-entry">
                            <div class="nb-listelem-inner-title">
                                <p class="title"><a href="#"><?= $row->TITLE ?></a>
                                </p><small>
                                    <?php if ($row->IS_OPEN_END): ?>
                                    ab
                                    <?= $src_start->format("d.m.Y, H:i") ?>
                                    Uhr
                                    <?php else:
                                        $endDate = DateTime::createFromFormat(mysql_date, $row->DATEEND); ?>
                                    <?= $src_start->format("d.m.Y, H:i") ?>
                                    Uhr -
                                    <?= $endDate->format($endDate->diff($src_start)->days > 0 ? 'd.m.Y, H:i' : 'H:i') ?>
                                    Uhr
                                    <?php endif; ?>
                                </small>
                            </div>
                        </div>
                    </td>
                    <td class="expand">
                        <i class="fa-solid fa-arrow-right desktop"></i>
                        <i class="fa-solid fa-arrow-down mobile"></i>
                    </td>
                    <td class="shrink">
                        <div class="nb-listelem-outer manage-entry">
                            <div class="nb-listelem-inner-title">
                                <p class="title"><a href="#"><?= $row->TITLE ?></a>
                                </p><small>
                                    <?php if ($row->IS_OPEN_END): ?>
                                    ab
                                    <?= $dst_start->format("d.m.Y, H:i") ?>
                                    Uhr
                                    <?php else:
                                        $delta_diff = DateTime::createFromFormat(mysql_date, $row->DATEEND)->diff($src_start);
                                        $dst_end = clone $dst_start;
                                        $dst_end->sub($delta_diff); ?>
                                    <?= $dst_start->format("d.m.Y, H:i") ?>
                                    Uhr -
                                    <?= $dst_end->format($dst_end->diff($dst_start)->days > 0 ? 'd.m.Y, H:i' : 'H:i') ?>
                                    Uhr
                                    <?php endif; ?>
                                </small>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <tr class="dates-line" valign="top">
                    <th scope="row" class="form-table-btmrow" colspan="2">
                        <button type="submit" tip="Es wurden keine Daten ausgewählt!" class="button button-primary"><i
                                class="fa-solid fa-clone"></i> Kopieren</button>
                        <a class="button button-warn"
                            href="<?= add_query_arg('action', 'fkurs-manage', admin_url('admin.php?page=nb-options-menu')) ?>">Abbrechen</a>
                    </th>
                </tr>
            </tbody>
        </table>
    </form>
</div>