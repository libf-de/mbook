<div class="manage-controls">
    <form method="post"
        action="<?= admin_url('admin-post.php?action=nb_ls_add'); ?>">
        <table class="form-table nb-modify-table">
            <thead>
                <th width="100px" class="nb-listhead-toolbox" colspan="2">
                    <h1>Unterrichtsstunde erstellen</h1>
                </th>
            </thead>
            <tbody>
                <tr valign="top">
                    <th scope="row"><strong>Vorlage</strong></th>
                    <td><select name="template" id="template" autocomplete="off">
                            <?php foreach ($wpdb->get_results("SELECT ID, TITLE, TYP, DEFAULT_DURATION,
                            DEFAULT_MAX_PARTICIPANTS FROM " . db_lessontemplates . "
                            ORDER BY TITLE") as $key => $row): ?>
                            <option value="<?= $row->ID ?>"
                                data-type="<?= $row->TYP ?>"
                                data-maxparts="<?= $row->DEFAULT_MAX_PARTICIPANTS ?>"
                                data-duration="<?= $row->DEFAULT_DURATION ?>">
                                <?= $row->TITLE ?>
                            </option>
                            <?php endforeach; ?>
                        </select></td>
                </tr>
                <tr valign="top" id="date0" data-num="0" id="dates-root" class="selected-dates">
                    <th scope="row"><strong>Datum/Uhrzeit</strong></th>
                    <td>
                        <select required name="dates[0][weekday][]" class="weekday lesson-add-weekday" multiple autocomplete="off">
                            <option value="0">Montags</option>
                            <option value="1">Dienstags</option>
                            <option value="2">Mittwochs</option>
                            <option value="3">Donnerstags</option>
                            <option value="4">Freitags</option>
                            <option value="5">Samstags</option>
                            <option value="6">Sonntags</option>
                        </select>
                        <span style="white-space: nowrap;">
                            <input type="time" class="lesson-add-start startTime" required step="60" value="13:00"
                                min="00:00" max="23:59" name="dates[0][start]" autocomplete="off"> &mdash;
                            <input type="time" class="lesson-add-end endTime" required step="60" value="15:00"
                                min="00:00" max="23:59" name="dates[0][end]" autocomplete="off" id="dates-0-end"> Uhr
                        </span>
                    </td>
                </tr>
                <tr valign="top" class="dates-line">
                    <th scope="row"></th>
                    <td>
                        <a class="button button-primary" id="lesson-add-append"><i class="fa-solid fa-plus"></i>
                            Hinzufügen</a>
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
                            href="<?= add_query_arg('action', 'lessons', admin_url('admin.php?page=nb-options-lessons')) ?>">Abbrechen</a>
                    </th>
                </tr>
            </tbody>
        </table>
    </form>
</div>
<script type="text/javascript" defer>
    jQuery(document).ready(function($) {
        initAddLesson();
    });
</script>