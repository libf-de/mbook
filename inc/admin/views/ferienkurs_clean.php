<div class="manage-controls">
    <form id="clear-form" method="post" action="<?= admin_url('admin-post.php?action=nb_fk_clean'); ?>">
        <input type="hidden" name="fe" value="<?= $selectedFerien ?>">
        <table class="form-table nb-modify-table">
            <thead>
                <th width="100px" class="nb-listhead-toolbox" colspan="2">
                    <h1>Ferienkurse bereinigen</h1>
                </th>
            </thead>
            <tbody>
                <tr valign="top">
                    <td colspan="2">
                        <p style="font-size: larger;">Bitte wähle, für welchen Zeitraum Ferien und Ferienkurse gelöscht werden sollen:</p>
                    </td>
                </tr>
                <tr class="dates-line" valign="top">
                    <th scope="row"><strong>Lösche Einträge älter als..</strong></th>
                    <td>
                        <select name="timespan" autocomplete="off">
                            <option value="7">1 Woche</option>
                            <option value="30">1 Monat</option>
                            <option value="90">3 Monate</option>
                            <option value="180">6 Monate</option>
                            <option value="365">1 Jahr</option>
                            <option value="730">2 Jahre</option>
                            <option value="1095">3 Jahre</option>
                            <option value="1825">5 Jahre</option>
                        </select>
                    </td>
                </tr>
                <tr class="dates-line" valign="top">
                    <th scope="row" class="form-table-btmrow" colspan="2">
                        <button type="submit" tip="Es wurden keine Daten ausgewählt!" class="button button-warn"><i
                                class="fa-solid fa-broom"></i> Bereinigen</button>
                        <a class="button button-primary"
                            href="<?= add_query_arg('action', 'fkurs-manage', admin_url('admin.php?page=nb-options-menu')) ?>">Abbrechen</a>
                    </th>
                </tr>
            </tbody>
        </table>
    </form>
</div>
<script type="text/javascript" defer>jQuery(document).ready(function($) { $('#clear-form').submit(function() { return confirm("Möchten Sie den angegebenen Zeitraum bereinigen? GELÖSCHTE KURSE KÖNNEN NICHT WIEDERHERGESTELLT WERDEN!"); }); });</script>