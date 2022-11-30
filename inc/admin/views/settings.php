<?php $calTest = $gca->test_calendar(); $authTest = $gca->test_auth(); ?>
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
                    <th scope="row"><strong>Google-Kalender &rarr; Ferienkalender-ID <?= $calTest ? "<span class=\"ok\"><i class=\"fa-solid fa-check\"></i></span>" : "<span class=\"fail\"><i class=\"fa-solid fa-xmark\"></i></span>" ?></strong></th>
                    <td>
                        <input autocomplete="off" type="text" class="<?= $calTest ? "ok" : "fail" ?>" name="gcferienid" value="<?= get_option('nb_gc_ferien') ?? "" ?>">
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
<br>
<div class="nb-manage-controls">
        <table class="form-table nb-modify-table">
            <thead>
                <th class="nb-listhead-toolbox nb-listhead-google" colspan="2">
                    <h1>Google-Anmeldedaten</h1>
                    <span>Lade hier deine .json-Anmeldedaten hoch um die Google-Kalender-Integration zu verwenden.</span>
                </th>
            </thead>
            <tbody class="nb-tbody-google">
                <form action="<?= admin_url( 'admin-post.php?action=nb_gc_upload') ?>" method="post" enctype="multipart/form-data">
                <?php if(file_exists($plugin_root . 'inc/calendar/' . $wpdb->prefix . '.gc.json')): ?>
                <tr valign="top">
                    <td>
                        <p>Es wurde bereits eine JSON-Datei hochgeladen, <?= $authTest ? "<span class=\"ok\">die funktioniert!</span>" : "<span class=\"fail\">die nicht funktioniert &rarr;</span>" ?> Sie können diese <button type="submit" name="delete" class="button button-warn button-smol">Löschen</button> oder folgend eine neue hochladen:</p>
                    </td>
                </tr>
                <?php endif; ?>
                <tr valign="top">
                    <td>
                        <input type="file" name="gcauth" id="gcauth">
                        <button type="submit" name="upload" class="button button-primary"><i class="fa-solid fa-cloud-arrow-up"></i> Hochladen</button>
                    </td>
                </tr>
                </form>
            </tbody>
        </table>
    </form>
</div>