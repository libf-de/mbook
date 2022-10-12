<?php $BTNMODE = true; ?>
<div id="edit-dialog" title="Kurs bearbeiten">
    <form id="edit-form" method="post"
        action="<?= admin_url('admin-post.php?action=mb_fk_edit') ?>">
        <input type="hidden" name="id" id="edit-dialog-id" value="-1">
        <table>
            <tr>
                <td><label for="start">Startzeit: </label></td>
                <td><input type="text" name="startdate" id="edit-dialog-date" size="8" readonly
                        style="text-align: center; margin-right: 3px;"><input type="time" name="start"
                        id="edit-dialog-start" style="position: relative; top: -1px; margin-left: 0px;"></td>
            </tr>
            <tr>
                <td><label for="end">Ende: </label></td>
                <td><input type="datetime-local" name="end" id="edit-dialog-end"></td>
            </tr>
            <tr>
                <td><label for="openEnd">-oder-</label></td>
                <td><input type="checkbox" name="openEnd" id="edit-dialog-openend">offenes Ende</td>
            </tr>
            <tr style="height: 0.5rem;"></tr>
            <tr>
                <td><label for="cancelled" style="margin-top: 2rem;">Absagen: </label></td>
                <td><input type="checkbox" name="cancelled" id="edit-dialog-cancelled"> abgesagt</td>
            </tr>
            <tr style="height: 0.5rem;"></tr>
            <tr>
                <td><label for="maxparts">Max. Teiln.: </label></td>
                <td><input type="number" name="maxparts" id="edit-dialog-maxparts"></td>
            </tr>
        </table>
    </form>
</div>


<div class="manage-controls">
    <table class="form-table">
        <thead>
            <th class="box-header" colspan="2">
                <h1>Ferienkurse</h1>
                <div class="mctop mctools-div">
                    <nobr><label for="ferien-select">Ferien:</label><select id="ferien-select">
                        <?php foreach($wpdb->get_results("SELECT `$ferien`.* FROM `$ferien` WHERE `$ferien`.`ENDDATE` >= CURDATE() ORDER BY `$ferien`.`STARTDATE`") as $key => $row): ?>
                        <option value="<?= $row->FID ?>"><?= $row->LABEL ?>
                        </option>
                        <?php endforeach; ?>
                        <option value="-1">+ Neu erstellen</option>
                    </select></nobr>
                </div>
                <div class="mctop mctools-div">
                    <a href="?page=mb-options-menu&action=addfk" class="button button-primary">Erstellen</a>&nbsp;
                    <a href="?page=mb-options-menu&action=clrfk" class="button button-primary">Vergangene löschen</a>&nbsp;
                    <a href="?page=mb-options-menu&action=wipefk" class="button button-primary">Alle löschen</a>&nbsp;
                    <a href="?page=mb-options-menu&action=oldfk" class="button button-primary">Archiv</a>&nbsp;
                    <a class="button button-primary"
                        href="<?= admin_url('admin-post.php?action=print') ?>">Ausdruck</a>
                </div>
            </th>
        </thead>
        <tbody>
            <?php foreach($wpdb->get_results("SELECT `$termin`.*, `$template`.TITLE, `$template`.EXP_LEVEL_MIN,
        `$template`.EXP_LEVEL_MAX FROM `$termin` INNER JOIN `$template` ON `$termin`.`TEMPLATE` = `$template`.`ID` WHERE
        `$termin`.`DATESTART` >= CURDATE() ORDER BY `$termin`.`DATESTART`") as $key => $row): ?>
            <?php $startDate = DateTime::createFromFormat(mysql_date, $row->DATESTART);
                $endDate = DateTime::createFromFormat(mysql_date, $row->DATEEND); ?>
            <tr>
                <td>
                    <div class="fktermine-outer manage-entry manage-table <?= ($_GET['hl'] == $row->ID) ? "table-highlight" : "" ?>"
                        data-id="<?= $row->ID ?>"
                        data-start="<?= $startDate->format("H:i")?>"
                        data-end="<?= $row->IS_OPEN_END ? "" : $endDate->format("Y-m-d\TH:i:s") ?>"
                        data-openend="<?= $row->IS_OPEN_END ?>"
                        data-cancelled="<?= $row->IS_CANCELLED ?>"
                        data-date="<?= $startDate->format("d.m.Y") ?>"
                        data-maxparts="<?= $row->MAX_PARTICIPANTS ?>">
                        <div class="fktermine-inner-title">
                            <p class="title"><a href="?page=mb-options-menu&action=editfk&id=<?= $row->ID ?>"><?= $row->TITLE ?></a></p>
                            <small>
                                <?php if ($row->IS_OPEN_END): ?>
                                ab <?= $startDate->format("d.m.Y, H:i") ?>
                                Uhr
                                <?php else: ?>
                                <?= $startDate->format("d.m.Y, H:i") ?>
                                Uhr -
                                <?= $endDate->format($endDate->diff($startDate)->days > 0 ? 'd.m.Y, H:i' : 'H:i') ?>
                                Uhr
                                <?php endif; ?>
                            </small>
                        </div>

                        <div class="fktermine-inner-parts">
                            <?php if ($row->IS_CANCELLED): ?>
                            <div class="toggle-full">
                                <label>
                                    <input class="fk-list-parts" type="button"
                                        data-maxparts="<?= $row->MAX_PARTICIPANTS ?>"
                                        value="<?= $row->PARTICIPANTS == $row->MAX_PARTICIPANTS ?>"
                                        data-id="<?= $row->ID ?>"
                                        <?= $row->PARTICIPANTS == $row->MAX_PARTICIPANTS ? "checked" : ""?>>
                                    <span></span>
                                </label>
                            </div>
                            <?php elseif ($row->MAX_PARTICIPANTS == 1 || $BTNMODE == true): ?>
                            <div class="toggle-full">
                                <label>
                                    <input class="fk-list-parts" type="checkbox"
                                        data-maxparts="<?= $row->MAX_PARTICIPANTS ?>"
                                        value="<?= $row->PARTICIPANTS == $row->MAX_PARTICIPANTS ?>"
                                        data-id="<?= $row->ID ?>"
                                        <?= $row->PARTICIPANTS == $row->MAX_PARTICIPANTS ? "checked" : ""?>>
                                    <span></span>
                                </label>
                            </div>
                            <?php else: ?>
                            <div class="qty btns_added">
                                <input type="button" value="-" class="minus fk-list-btns">
                                <input class="fk-list-parts input-text qt text" type="number"
                                    data-id="<?= $row->ID ?>"
                                    id="parts<?= $row->ID ?>"
                                    min="-1"
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
        </tbody>
    </table>
</div>
<script type="text/javascript" defer>jQuery(document).ready(function($) { initToggles(); });</script>