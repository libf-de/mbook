<?php $BTNMODE = true; ?>
<tbody id="fklist-body">
    <?php $sql_kurse = $wpdb->get_results($wpdb->prepare("SELECT `$termin`.*, `$template`.TITLE, `$template`.EXP_LEVEL_MIN,
        `$template`.EXP_LEVEL_MAX FROM `$termin` INNER JOIN `$template` ON `$termin`.`TEMPLATE` = `$template`.`ID` WHERE
        `$termin`.`DATESTART` >= CURDATE() AND `$termin`.FERIEN = %d ORDER BY `$termin`.`DATESTART`", $selectedFerien)); ?>
    <?php if(empty($sql_kurse)): ?>
    <tr>
        <td>    
            <h3>Keine Kurse</h3>
            <small>Wähle oben andere Ferien aus oder erstelle neue Kurse</small>
        </td>
    </tr>
    <?php else: ?>
    <?php foreach($sql_kurse as $key => $row): ?>
    <?php $startDate = DateTime::createFromFormat(mysql_date, $row->DATESTART);
        $endDate = DateTime::createFromFormat(mysql_date, $row->DATEEND); ?>
    <tr>
        <td>
            <div class="mb-listelem-outer manage-entry manage-table <?= (isset($_GET['hl']) ? ($_GET['hl'] == $row->ID ? "table-highlight" : "") : "") ?>"
                data-id="<?= $row->ID ?>" data-start="<?= $startDate->format("H:i") ?>"
                data-end="<?= ($row->IS_OPEN_END ? "" : $endDate->format("Y-m-d\TH:i:s")) ?>"
                data-openend="<?= $row->IS_OPEN_END ?>"
                data-cancelled="<?= $row->IS_CANCELLED ?>"
                data-date="<?= $startDate->format("d.m.Y") ?>"
                data-maxparts="<?= $row->MAX_PARTICIPANTS ?>">
                <div class="fktermine-inner-title">
                    <p class="title"><a
                            href="?page=mb-options-menu&action=editfk&id=<?= $row->ID ?>"><?= $row->TITLE ?></a></p><small>
                        <?php if ($row->IS_OPEN_END): ?>
                        ab <?= $startDate->format("d.m.Y, H:i") ?> Uhr
                        <?php else: ?>
                        <?= $startDate->format("d.m.Y, H:i") ?> Uhr - <?= $endDate->format($endDate->diff($startDate)->days > 0 ? 'd.m.Y, H:i' : 'H:i') ?> Uhr
                        <?php endif; ?>
                    </small>
                </div>
                <div class="fktermine-inner-parts">
                    <?php if ($row->IS_CANCELLED): ?>
                    <div class="toggle-full"><label><input class="fk-list-parts" type="button"
                                data-maxparts="<?= $row->MAX_PARTICIPANTS ?>"
                                value="<?= $row->PARTICIPANTS == $row->MAX_PARTICIPANTS ?>"
                                data-id="<?= $row->ID ?>" <?= ($row->PARTICIPANTS == $row->MAX_PARTICIPANTS ? "checked" : "") ?>>
                            <span></span></label></div>
                    <?php elseif ($row->MAX_PARTICIPANTS == 1 || $BTNMODE == true): ?>
                    <div class="toggle-full"><label>
                            <input class="fk-list-parts" type="checkbox"
                                data-maxparts="<?= $row->MAX_PARTICIPANTS ?>"
                                value="<?= $row->PARTICIPANTS == $row->MAX_PARTICIPANTS ?>"
                                data-id="<?= $row->ID ?>" <?= ($row->PARTICIPANTS == $row->MAX_PARTICIPANTS ? "checked" : "") ?>>
                            <span></span></label></div>
                    <?php else: ?>
                    <div class="qty btns_added"><input type="button" value="-" class="minus fk-list-btns">
                        <input class="fk-list-parts input-text qt text" type="number"
                            data-id="<?= $row->ID ?>"
                            id="parts<?= $row->ID ?>" min="-1"
                            max="<?= $row->MAX_PARTICIPANTS ?>"
                            value="<?= $row->PARTICIPANTS ?>"
                            title="Qty" size="5" pattern="" inputmode="">
                        <input type="button" value="+" class="plus fk-list-btns">
                    </div>
                    <?php endif; ?>
                </div>
                <div class="fktermine-inner-modify">
                    <a class="button button-primary fk-list-edit"><i class="fa-solid fa-pen"></i></a>
                    <a class="button button-warn fk-delete-course" href="#"><i class="fa-solid fa-trash-can"></i></a>
                </div>
            </div>
        </td>
    </tr>
    <?php endforeach; ?>
    <?php endif; ?>
</tbody>