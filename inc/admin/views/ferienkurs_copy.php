<div class="nb-manage-controls">
    <form id="clear-form" method="get" action="">
        <input type="hidden" name="page" value="nb-options-menu">
        <input type="hidden" name="action" value="fkurs-copy-prv">
        <table class="form-table nb-modify-table">
            <thead>
                <th width="100px" class="nb-listhead-toolbox" colspan="2">
                    <h1>Ferienprogramm kopieren</h1>
                </th>
            </thead>
            <tbody>
                <tr valign="top">
                    <td colspan="2">
                        <p style="font-size: larger;">Kopiert Kurse relativ zum Ferienstart von Quelle- nach
                            Ziel-Ferien:</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><strong>Quelle-Ferien</strong></th>
                    <td>
                        <select name="ferien-src" autocomplete="off">
                            <?php foreach($wpdb->get_results("SELECT * FROM `$ferien` ORDER BY STARTDATE") as $key => $row): ?>
                            <?php $start = DateTime::createFromFormat("Y-m-d", $row->STARTDATE);
                                $end = DateTime::createFromFormat("Y-m-d", $row->ENDDATE);
                                $today = new DateTime();
                                $today->setTime(0, 0, 0, 0); ?>
                            <option <?= $row->FID == $selectedFerien ? "selected" : "" ?>
                                data-dstart="<?= $today->diff($start)->format("%r%a") ?>"
                                data-dend="<?= $today->diff($end)->format("%r%a") ?>"
                                value="<?= $row->FID ?>"><?= $row->LABEL ?>
                                (<?= $start->format("d.m.") ?>
                                -
                                <?= $end->format("d.m.Y") ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><strong>Ziel-Ferien</strong></th>
                    <td>
                        <select name="ferien-dst" autocomplete="off">
                            <?php foreach($wpdb->get_results("SELECT * FROM `$ferien` ORDER BY STARTDATE") as $key => $row): ?>
                            <?php $start = DateTime::createFromFormat("Y-m-d", $row->STARTDATE);
                                $end = DateTime::createFromFormat("Y-m-d", $row->ENDDATE);
                                $today = new DateTime();
                                $today->setTime(0, 0, 0, 0); ?>
                            <option <?= $row->FID == $selectedFerien ? "selected" : "" ?>
                                data-dstart="<?= $today->diff($start)->format("%r%a") ?>"
                                data-dend="<?= $today->diff($end)->format("%r%a") ?>"
                                value="<?= $row->FID ?>"><?= $row->LABEL ?>
                                (<?= $start->format("d.m.") ?>
                                -
                                <?= $end->format("d.m.Y") ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr class="dates-line" valign="top">
                    <th scope="row" class="form-table-btmrow" colspan="2">
                        <button type="submit" tip="Es wurden keine Daten ausgewählt!" class="button button-primary"><i
                                class="fa-solid fa-clone"></i> Kopieren</button>
                        <a class="button button-warn"
                            href="<?= add_query_arg('action', 'fkurs-manage', admin_url('admin.php?page=nb-options-menu')) ?>">Abbrechen</a>
                    </th>
                </tr>
            </tbody>
        </table>
    </form>
</div>