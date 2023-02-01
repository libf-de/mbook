<div class="nb-manage-controls">
    <form method="post" action="">
        <input type="hidden" name="action" value="fktemplates-edit">
        <input type="hidden" name="id" value="<?= $id ?>">
        <table class="form-table nb-modify-table">
            <tbody>
                <tr valign="top">
                    <th scope="row"><strong>Titel</strong></th>
                    <td>
                        <input type="text" pattern=".{5,50}" required title="Der Titel sollte mindestens 5 und max. 50 Zeichen lang sein" name="title" placeholder='Ferienkurs-Titel' value="<?= $template->TITLE ?>">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><strong>Kürzel</strong></th>
                    <td>
                        <input type="text" pattern="[A-Za-z]{1,5}" required title="Das Kürzel sollte mindestens 1 und max. 5 Buchstaben lang sein" name="shorthand" placeholder='Ferienkurs-Kürzel' value="<?= isset($template->SHORTHAND) ? $template->SHORTHAND : ""; ?>">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><strong>Beschreibung</strong></th>
                    <td>
                        <textarea pattern=".{5,}" required title="Die Beschreibung sollte mindestens 5 Zeichen lang sein" name="description" cols="22" rows="6"><?= str_replace("<br/>", "\n", $template->DESCRIPTION); ?></textarea>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Platzhalter:</th>
                    <td>
                        <p><strong>%am</strong> &rarr; Am xxx, den xxx...<br><strong>%findet</strong> &rarr; findet von xx:xx bis xx:xx</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><strong><i>Kurs-URL</i></strong></th>
                    <td>
                        <input type="text" name="linkurl" placeholder='Kurs-URL (optional)' value="<?= $template->LINKURL ?>">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><strong>Vorgabe-Startzeit</strong></th>
                    <td>
                        <input type="time" class="startTime" required min="00:00" max="23:59" name="startTime" required value="<?= mins_to_hh_mm($template->DEFAULT_STARTTIME);?>"> Uhr
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><strong>Vorgabe-Dauer</strong></th>
                    <td>
                        <input class="duration-input" type="number" name="duration-days" min="0" max="14" value="<?= $durationDays ?>" <?= $isOpenEnd ? 'disabled' : 'required' ?>> Tage, 
                        <input class="duration-input" type="number" name="duration-hours" min="0" max="23" value="<?= $durationHours ?>" <?= $isOpenEnd ? 'disabled' : 'required' ?>> Stunden, 
                        <input class="duration-input" type="number" name="duration-mins" min="0" max="59" value="<?= $durationMins ?>" <?= $isOpenEnd ? 'disabled' : 'required' ?>> Minuten
                        <br>
                        <div class="openend-div">
                            <input type="checkbox" id="openEnd" data-disables-class="duration-input" name="openEnd" <?= $isOpenEnd ? 'checked' : '' ?>> offenes Ende
                        </div>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><strong>Vorgabe-Teilnehmermax.</strong></th>
                    <td>
                        <input type="number" required min="1" max="99" name="maxparts" value="<?= $template->DEFAULT_MAX_PARTICIPANTS ?>">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><strong>Vorgabe-Wochentag</strong></th>
                    <td>
                        <select name="weekday">
                            <?= weekday_dropdown($template->DEFAULT_WEEKDAY) ?>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><strong>Benötigtes Level</strong></th>
                    <td>
                        <input type="number" class='exp-input' required min="0" max="99" name="minExp" value="<?= $template->EXP_LEVEL_MIN ?>"> mind., 
                        <input type="number" class='exp-input' required min="0" max="99" name="maxExp" value="<?= $template->EXP_LEVEL_MAX ?>"> max.
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row" class="form-table-btmrow">
                        <input type="submit" class="button button-primary" value="Speichern">
                    </th>
                </tr>
            </tbody>
        </table>
    </form>
</div>
<script type="text/javascript" defer>jQuery(document).ready(function($) { initAddFTemplate(); });</script>