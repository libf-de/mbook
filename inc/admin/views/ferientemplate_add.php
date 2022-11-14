<div class="manage-controls">
    <form method="post" action="">
        <input type="hidden" name="action" value="fktemplates-edit">
        <table class="form-table mb-modify-table">
            <thead>
                <th class="nb-listhead-toolbox" colspan="2">
                    <h1>Ferienkurs-Vorlage erstellen</h1>
                </th>
            </thead>
            <tbody>
                <tr valign="top">
                    <th scope="row"><strong>Titel</strong></th>
                    <td>
                        <input type="text" pattern=".{5,50}" required title="Der Titel sollte mindestens 5 und max. 50 Zeichen lang sein" name="title" placeholder='Ferienkurs-Titel'>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><strong>Kürzel</strong></th>
                    <td>
                        <input type="text" pattern="[A-Za-z]{1,5}" required title="Das Kürzel sollte mindestens 1 und max. 5 Buchstaben lang sein" name="shorthand" placeholder='Ferienkurs-Kürzel'>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><strong>Beschreibung</strong></th>
                    <td>
                        <textarea pattern=".{5,}" required title="Die Beschreibung sollte mindestens 5 Zeichen lang sein" name="description" cols="22" rows="6"></textarea>
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
                        <input type="text" name="linkurl" placeholder='Kurs-URL (optional)'>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><strong>Vorgabe-Startzeit</strong></th>
                    <td>
                        <input type="time" class="startTime" required min="00:00" max="23:59" name="startTime" required value="12:00"> Uhr
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><strong>Vorgabe-Dauer</strong></th>
                    <td>
                        <input class="duration-input" type="number" name="duration-days" min="0" max="14" value="0" required> Tage, 
                        <input type="number" required class="duration-input" name="duration-hours" min="0" max="23" value="2"> Stunden, 
                        <input type="number" class="duration-input" required name="duration-mins" min="0" max="59" value="0"> Minuten
                        <br>
                        <div class="openend-div">
                            <input type="checkbox" id="openEnd" data-disables-class="duration-input" name="openEnd"> offenes Ende
                        </div>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><strong>Vorgabe-Teilnehmermax.</strong></th>
                    <td>
                        <input type="number" required min="1" max="99" name="maxparts" value="1">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><strong>Vorgabe-Wochentag</strong></th>
                    <td>
                        <select name="weekday">
                            <?= weekday_dropdown(-1); ?>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><strong>Benötigtes Level</strong></th>
                    <td>
                        <input type="number" class='exp-input' required min="0" max="99" name="minExp" value="0"> mind., 
                        <input type="number" class='exp-input' required min="0" max="99" name="maxExp" value="99"> max.
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row" class="form-table-btmrow">
                        <input type="submit" class="button button-primary" value="Erstellen">
                        
                    </th>
                </tr>
            </tbody>
        </table>
    </form>
</div>
<script>initAddFTemplate();</script>