<div class="nb-manage-controls">
    <form method="post" action="<?= admin_url( 'admin-post.php?action=nb_cf_modify') ?>">
        <table class="form-table nb-modify-table">
            <thead>
                <th class="nb-listhead-toolbox" colspan="2">
                    <h1>Konfiguration</h1>
                </th>
            </thead>
            <tbody>
                <tr valign="top">
                    <th scope="row"><strong>Ferienkurs erstellen &rarr; Kalenderverhalten (mehrtägige Kurse)</strong></th>
                    <td>
                        <select id="calcmode" name="calcmode" autocomplete="off">
                            <option <?= get_option('nb_calcmode') == 0 ? "selected" : "" ?> value="0">nur nachher blockieren, existierende zulassen</option>
                            <option <?= get_option('nb_calcmode') == 1 ? "selected" : "" ?> value="1">Tage vor-/nachher blockieren</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><strong>Ferienkursliste &rarr; Teilnehmer</strong></th>
                    <td>
                        <select id="partmode" name="partmode" autocomplete="off">
                            <option <?= get_option('nb_partmode') == 0 ? "selected" : "" ?> value="0">Teilnehmeranzahl verwenden</option>
                            <option <?= get_option('nb_partmode') == 1 ? "selected" : "" ?> value="1">nur Frei/Belegt verwenden</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row" class="form-table-btmrow">
                        <button type="submit" class="button button-primary"><i class="fa-solid fa-floppy-disk"></i> Speichern</button>
                    </th>
                </tr>
            </tbody>
        </table>
    </form>
</div>