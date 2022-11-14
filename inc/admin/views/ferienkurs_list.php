<?php $BTNMODE = true; ?>
<div id="edit-dialog" title="Kurs bearbeiten">
    <form id="edit-form" method="post"
        action="<?= admin_url('admin-post.php?action=nb_fk_edit') ?>">
        <input type="hidden" name="id" id="edit-dialog-id" value="-1">
        <input type="hidden" name="fe" value="<?= $selectedFerien ?>">
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
            <th class="nb-listhead-toolbox" colspan="2">
                <h1>Ferienkurse</h1>
                <div class="nb-listhead-toolbox-div">
                    <nobr><label for="ferien-select">Ferien:</label><select id="ferien-select" autocomplete="off">
                        <optgroup label="Aktuelle Ferien">
                            <?php foreach($wpdb->get_results("SELECT `$ferien`.* FROM `$ferien` WHERE `$ferien`.`ENDDATE` >= CURDATE() ORDER BY `$ferien`.`STARTDATE`") as $key => $row): ?>
                            <option value="<?= $row->FID ?>" <?= $row->FID == $selectedFerien ? "selected" : ""?>><?= $row->LABEL ?>
                            </option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="Vergangene Ferien">
                            <?php foreach($wpdb->get_results("SELECT `$ferien`.* FROM `$ferien` WHERE `$ferien`.`ENDDATE` < CURDATE() ORDER BY `$ferien`.`STARTDATE`") as $key => $row): ?>
                            <option value="<?= $row->FID ?>" <?= $row->FID == $selectedFerien ? "selected" : ""?>><?= $row->LABEL ?>
                            </option>
                            <?php endforeach; ?>
                        </optgroup>
                        <option value="-1">+ Neu erstellen</option>
                    </select></nobr>
                </div>
                <div class="nb-listhead-toolbox-div">
                    <a href="?page=nb-options-menu&action=fkurs-add<?= isset($_GET['fe']) ? "&fe=$selectedFerien" : ""?>" id="nb-fklist-add" class="button button-primary">Erstellen</a>&nbsp;
                    <a href="?page=nb-options-menu&action=fkurs-clear" class="button button-primary">Vergangene löschen</a>&nbsp;
                    <a href="?page=nb-options-menu&action=fkurs-copy" class="button button-primary">Kurse kopieren</a>&nbsp;
                    <a href="<?= admin_url('admin-post.php?action=print') ?>" class="button button-primary">Ausdruck</a>
                </div>
            </th>
        </thead>
        <?php include __DIR__ . "/ferienkurs_ajax_list.php"; ?>
    </table>
</div>
<script type="text/javascript" defer>jQuery(document).ready(function($) { initToggles(); });</script>