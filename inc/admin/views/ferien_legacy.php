<?php nb_load_fa(); ?>
<div class="nb-manage-controls">
    <table class="form-table">
        <thead>
            <th class="nb-listhead-toolbox" colspan="2">
                <h1>Vergangene Kurse löschen</h1>
            </th>
        </thead>
        <tbody>
            <p>Möchtest du wirklich die folgenden verganenen Ferien <b>inklusive zugehöriger Ferienkurse</b> löschen?</p>
            <?php foreach($wpdb->get_results("SELECT * FROM " . db_ferien . " WHERE ENDDATE <= CURDATE()") as $key => $row): ?>
            <tr>
                <td>
                    <div class="nb-listelem-outer manage-entry" data-id="<?= $row->FID ?>">
                        <div class="nb-listelem-inner-title">
                            <p class="title"><a href="?page=nb-options-menu&action=fkurs-manage&fe=<?= $row->FID ?>"><?= $row->LABEL; ?></a></p>
                            <?php $sd = explode("-", $row->STARTDATE); $ed = explode("-", $row->ENDDATE); ?>
                            <small><?= sprintf("%02d.%02d.%d", $sd[2], $sd[1], $sd[0]); ?> - <?= sprintf("%02d.%02d.%d", $ed[2], $ed[1], $ed[0]); ?></small>
                        </div>

                        <div class="nb-listelem-inner-modify">
                            <?php $thisStandard = get_option('standard_ferien') == $row->FID ?>
                            <a class="button <?= $thisStandard ? "button-green" : "button-primary" ?> fe-standard-course" title="Standardferien setzen" href="#">
                                <i class="fa-solid <?= $thisStandard ? "fa-heart-circle-check" : "fa-heart" ?>"></i>
                            </a>
                            <a class="button button-primary fe-list-edit" title="Ferien bearbeiten" href="?page=nb-options-menu&action=ferien-edit&id=<?= $row->FID ?>">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <a class="button button-warn fe-delete-course" title="Ferien löschen" data-id="<?= $row->FID ?>" data-title="<?= $row->LABEL ?>" href="#">
                                <i class="fa-solid fa-trash-can"></i>
                            </a>
                        </div>
                    </div>
                </td>
            </tr>


            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script type="text/javascript" defer>jQuery(document).ready(function($) { initList(); });</script>