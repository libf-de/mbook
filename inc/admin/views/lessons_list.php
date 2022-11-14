<?php $BTNMODE = true; ?>
<div id="edit-dialog" title="Unterrichtsstunde bearbeiten">
    <form id="edit-form" method="post"
        action="<?= admin_url('admin-post.php?action=mb_ls_edit') ?>">
        <input type="hidden" name="id" id="edit-dialog-id" value="-1">
        <input type="hidden" name="fe"
            value="<?= $selectedFerien ?>">
        <table>
            <tr>
                <td><label for="end">Wochentag: </label></td>
                <td><select id="edit-dialog-weekday" name="weekday" style="margin-left: 6px;"><?= weekday_dropdown(-1); ?></select>
                </td>
            </tr>
            <tr>
                <td><label for="start">Startzeit: </label></td>
                <td><input type="time" step="60" name="start" id="edit-dialog-start"
                        ></td>
            </tr>
            <tr>
                <td><label for="end">Ende: </label></td>
                <td><input type="time" step="60" name="end" id="edit-dialog-end"></td>
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
            <th class="nb-listhead-toolbox" colspan="2">
                <h1>Unterrichtsstunden</h1>
                <div class="nb-listhead-toolbox-div">
                    <a href="?page=mb-options-lessons&action=lessons-add" class="button button-primary">Erstellen</a>&nbsp;
                    <!-- <a href="<?= admin_url('admin-post.php?action=print') ?>"
                    class="button button-primary">Ausdruck</a>-->
                </div>
            </th>
        </thead>

        <?php $BTNMODE = true; ?>
        <tbody id="fklist-body">
            <?php $sql_kurse = $wpdb->get_results("SELECT `$dbLesson`.*, `$dbTemplate`.TITLE, `$dbTemplate`.EXP_LEVEL_MIN,
        `$dbTemplate`.EXP_LEVEL_MAX FROM `$dbLesson` INNER JOIN `$dbTemplate` ON `$dbLesson`.`TEMPLATE` = `$dbTemplate`.`ID` ORDER BY `$dbLesson`.`WEEKDAY`, `$dbLesson`.`START`"); ?>
            <?php if(empty($sql_kurse)): ?>
            <tr>
                <td>
                    <h3>Keine Kurse</h3>
                    <small>Wähle oben andere Ferien aus oder erstelle neue Kurse</small>
                </td>
            </tr>
            <?php else: ?>
            <?php $prevDay = -1; ?>
            <?php foreach($sql_kurse as $key => $row): ?>
            <?php if($prevDay <> $row->WEEKDAY): ?>
            <tr>
                <th class="lessons-list-weekday">
                    <h2><?= weekday_names[$row->WEEKDAY] ?></h2>
                </th>
            </tr>
            <?php $prevDay = $row->WEEKDAY; endif; ?>
            <tr>
                <td>
                    <div class="mb-listelem-outer manage-entry <?= (isset($_GET['hl']) ? ($_GET['hl'] == $row->ID ? "mb-listelem-highlight" : "") : "") ?>"
                        data-id="<?= $row->ID ?>"
                        data-start="<?= $row->START ?>"
                        data-end="<?= $row->END ?>"
                        data-cancelled="<?= $row->IS_CANCELLED ?>"
                        data-weekday="<?= $row->WEEKDAY ?>"
                        data-maxparts="<?= $row->MAX_PARTICIPANTS ?>">
                        <div class="mb-listelem-inner-title">
                            <p class="title"><a
                                    href="#"><?= $row->TITLE ?>
                                    <?= $row->NUM ?></a>
                            </p><small>
                                <?= pretty_time($row->START); ?> Uhr -
                                <?= pretty_time($row->END); ?> Uhr
                            </small>
                        </div>
                        <div class="mb-listelem-inner-parts">
                            <div class="toggle-full"><label>
                                    <input class="ls-list-parts" type="checkbox"
                                        data-maxparts="<?= $row->MAX_PARTICIPANTS ?>"
                                        value="<?= $row->PARTICIPANTS == $row->MAX_PARTICIPANTS ?>"
                                        data-id="<?= $row->ID ?>"
                                        <?= ($row->PARTICIPANTS == $row->MAX_PARTICIPANTS ? "checked" : "") ?>>
                                    <span></span></label></div>
                        </div>
                        <div class="mb-listelem-inner-modify">
                            <a class="button button-primary ls-list-edit"><i class="fa-solid fa-pen"></i></a>
                            <a class="button button-warn ls-delete-course" href="#"><i
                                    class="fa-solid fa-trash-can"></i></a>
                        </div>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>


    </table>
</div>
<script type="text/javascript" defer>
    jQuery(document).ready(function($) {
        initButtons();
    });
</script>