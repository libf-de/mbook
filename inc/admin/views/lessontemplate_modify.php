<div class="manage-controls">
    <form method="post" action="<?= admin_url( 'admin-post.php?action=mb_lt_modify') ?>">
        <?php if(isset($id)): ?><input type="hidden" name="id" value="<?= isset($id) ? $id : "" ?>"><?php endif; ?>
        <table class="form-table mb-modify-table">
            <thead>
                <th width="100px" class="nb-listhead-toolbox" colspan="2">
                    <h1>Unterrichts-Vorlage <?= isset($id) ? "bearbeiten" : "erstellen" ?></h1>
                </th>
            </thead>
            <tbody><!-- ID TITLE TYP SHORTHAND DESCRIPTION LINKURL DEFAULT_DURATION DEFAULT_MAX_PARTICIPANTS EXP_LEVEL_MIN EXP_LEVEL_MAX -->
            <!-- DEFAULT_DURATION DEFAULT_MAX_PARTICIPANTS EXP_LEVEL_MIN EXP_LEVEL_MAX -->
                <tr valign="top">
                    <th scope="row"><strong>Titel</strong></th>
                    <td>
                        <input type="text" pattern=".{5,50}" required title="Der Titel sollte mindestens 5 und max. 50 Zeichen lang sein" name="title" placeholder='Unterrichts-Titel' value="<?= isset($template->TITLE) ? $template->TITLE : "" ?>">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><strong>Typ</strong></th>
                    <td>
                        <select name="typ">
                            <?php foreach(lesson_types as $tNum => $tName): ?>
                                <option value="<?= $tNum; ?>" <?= ($tNum == $template->TYP) ? " selected" : "" ?>><?= $tName; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><strong>Kürzel</strong></th>
                    <td>
                        <input type="text" pattern="[A-Za-z]{1,5}" required title="Das Kürzel sollte mindestens 1 und max. 5 Buchstaben lang sein" name="shorthand" placeholder='Unterrichts-Kürzel' value="<?= isset($template->SHORTHAND) ? $template->SHORTHAND : ""; ?>">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><strong>Beschreibung</strong></th>
                    <td>
                        <textarea pattern=".{5,}" required title="Die Beschreibung sollte mindestens 5 Zeichen lang sein" name="description" cols="22" rows="6"><?= isset($template->DESCRIPTION) ? str_replace("<br/>", "\n", $template->DESCRIPTION) : "" ?></textarea>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><strong><i>Kurs-URL</i></strong></th>
                    <td>
                        <input type="text" name="linkurl" placeholder='Kurs-URL (optional, jedoch empfohlen)' value="<?= isset($template->LINKURL) ? $template->LINKURL : "" ?>">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><strong>Vorgabe-Dauer</strong></th>
                    <td>
                        <input type="hidden" name="duration-days" value="0">
                        <nobr><input class="duration-input" type="number" name="duration-hours" min="0" max="23" value="<?= isset($durationHours) ? $durationHours : "2" ?>" <?= isset($isOpenEnd) ? ($isOpenEnd ? 'disabled' : 'required') : "required" ?>> Stunden,</nobr> <wbr>
                        <nobr><input class="duration-input" type="number" name="duration-mins" min="0" max="59" value="<?= isset($durationMins) ? $durationMins : "0" ?>" <?= isset($isOpenEnd) ? ($isOpenEnd ? 'disabled' : 'required') : "required" ?>> Minuten</nobr>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><strong>Vorgabe-Teilnehmermax.</strong></th>
                    <td>
                        <input type="number" required min="1" max="99" name="maxparts" value="<?= isset($template->DEFAULT_MAX_PARTICIPANTS) ? $template->DEFAULT_MAX_PARTICIPANTS : "1" ?>">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><strong>Benötigtes Level</strong></th>
                    <td>
                        <nobr><input type="number" class='exp-input' required min="0" max="99" name="minExp" size="4" value="<?= isset($template->EXP_LEVEL_MIN) ? $template->EXP_LEVEL_MIN : "0" ?>"> mind.,</nobr> 
                        <nobr><input type="number" class='exp-input' required min="0" max="99" name="maxExp" size="4" value="<?= isset($template->EXP_LEVEL_MAX) ? $template->EXP_LEVEL_MAX : "99" ?>"> max.</nobr>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row" class="form-table-btmrow">
                        <button type="submit" class="button button-primary"><i class="fa-solid fa-floppy-disk"></i> Speichern</button>
                        <a class="button button-warn" href="<?= add_query_arg('action', 'lstemplates', admin_url( 'admin.php?page=mb-options-lessons')) ?>">Abbrechen</a>
                    </th>
                </tr>
            </tbody>
        </table>
    </form>
</div>
<!--<script>initAddFTemplate();</script><i class=""></i>-->