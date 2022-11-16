<?php
$DATEFLTR = "WHERE ENDDATE >= CURDATE()";

$DATEFLTR = "";
?>
<div class="nb-manage-controls">
    <form method="post" action="<?= admin_url('admin-post.php?action=nb_fk_add'); ?>">
        <input type="hidden" name="fe" value="<?= $selectedFerien ?>">
        <table class="form-table nb-modify-table">
            <thead>
                <th width="100px" class="nb-listhead-toolbox" colspan="2">
                    <h1>Ferienkurs erstellen</h1>
                </th>
            </thead>
            <tbody>
                <tr valign="top">
                    <th scope="row"><strong>Ferien</strong></th>
                    <td>
                        <select id="ferien-select" name="ferien" autocomplete="off">
                            <?php foreach($wpdb->get_results("SELECT * FROM `$ferien` $DATEFLTR ORDER BY STARTDATE") as $key => $row): ?>
                            <?php $start = DateTime::createFromFormat("Y-m-d", $row->STARTDATE);
                            $end = DateTime::createFromFormat("Y-m-d", $row->ENDDATE);
                            $today = new DateTime();
                            $today->setTime(0, 0, 0, 0); ?>
                            <option
                                <?= $row->FID == $selectedFerien ? "selected" : "" ?>
                                data-dstart="<?= $today->diff($start)->format("%r%a") ?>"
                                data-dend="<?= $today->diff($end)->format("%r%a") ?>"
                                value="<?= $row->FID ?>"><?= $row->LABEL ?> (<?= $start->format("d.m.") ?> - <?= $end->format("d.m.Y") ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><strong>Vorlage</strong></th>
                    <td><select name="template" id="template">
                            <?php foreach ($wpdb->get_results("SELECT ID, TITLE, DEFAULT_STARTTIME,
                            DEFAULT_DURATION, DEFAULT_WEEKDAY, DEFAULT_MAX_PARTICIPANTS FROM " . db_ferientemplates . "
                            ORDER BY DEFAULT_WEEKDAY,TITLE") as $key => $row): ?>
                            <?php $end_minutes = $row->DEFAULT_STARTTIME + $row->DEFAULT_DURATION; ?>
                            <option value="<?= $row->ID ?>"
                                data-day="<?= $row->DEFAULT_WEEKDAY ?>"
                                data-start="<?=
                                $row->DEFAULT_STARTTIME ?>"
                                data-maxparts="<?= $row->DEFAULT_MAX_PARTICIPANTS ?>"
                                data-duration="<?= $row->DEFAULT_DURATION ?>"
                                data-endtime="<?= mins_to_hh_mm($end_minutes
                                % 1440) ?>"
                                data-days="<?= floor($end_minutes / 1440) ?>">
                                <?= $row->TITLE ?> (<?= weekday_names[intval($row->DEFAULT_WEEKDAY)] ?>s)
                            </option>
                            <?php endforeach; ?>
                        </select></td>
                </tr>
                <tr valign="top" id="dates-root" class="selected-dates">
                    <th scope="row"><strong>Datum/Uhrzeit</strong><small class="selected-dates-subheading"><span class="sds-occ">Belegt</span> <span class="sds-dis">Block</span> <span class="sds-tod">Heute</span> <span class="sds-def">St</span> <span class="sds-sel">Ausw</span> <span class="sds-pst">Vergang</span></small></th>
                    <td>
                        <div class="fktermine-dates-div" id="dates"></div>
                        <!-- disabled for now <div class="fkurse-buttons" style="display: inline-block;"><a href="#" id="clear-dates" class="button button-warn button-small">Leeren</a></div>-->
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><strong>Max. Teilnehmer</strong></th>
                    <td><input type="number" required min="1" max="99" name="max-participants" value="1"></td>
                </tr>
                <tr valign="top">
                    <th scope="row" class="form-table-btmrow">
                        <button type="submit" tip="Es wurden keine Daten ausgewählt!" class="button button-primary"><i
                                class="fa-solid fa-floppy-disk"></i> Speichern</button>
                        <a class="button button-warn"
                            href="<?= add_query_arg('action', 'fkurs-manage', admin_url('admin.php?page=nb-options-menu' . (isset($_GET['fe']) ? "&fe=$selectedFerien" : ""))) ?>">Abbrechen</a>
                    </th>
                </tr>
            </tbody>
        </table>
    </form>
</div>
<script type="text/javascript" defer>jQuery(document).ready(function($) { ferienkursAddInit(); });</script>