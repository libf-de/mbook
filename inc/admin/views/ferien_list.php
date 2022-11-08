<div class="manage-controls">
    <table class="form-table">
        <thead>
            <th class="box-header" colspan="2">
                <h1>Ferien</h1>
                <div class="mctop mctools-div">
                    <a href="?page=mb-options-menu&action=ferien-add" class="button button-primary">Neu hinzufügen</a>&nbsp;
                    <a href="?page=mb-options-menu&action=ferien-imp" class="button button-primary">Importieren</a>&nbsp;
                    <a href="?page=mb-options-menu&action=ferien-clr" class="button button-red">Alte löschen</a>
                </div>
            </th>
        </thead>
        <tbody>
            <?php foreach($wpdb->get_results("SELECT FID, LABEL, STARTDATE, ENDDATE FROM " . db_ferien . " WHERE FID <> 1 ORDER BY STARTDATE DESC") as $key => $row): ?>
            <tr>
                <td>
                    <div class="mb-listelem-outer manage-entry manage-table" data-id="<?= $row->FID ?>">
                        <div class="fktermine-inner-title">
                            <p class="title"><a href="?page=mb-options-menu&action=managefk&fe=<?= $row->FID ?>"><?= $row->LABEL; ?></a></p>
                            <?php $sd = explode("-", $row->STARTDATE); $ed = explode("-", $row->ENDDATE); ?>
                            <small><?= sprintf("%02d.%02d.%d", $sd[2], $sd[1], $sd[0]); ?> - <?= sprintf("%02d.%02d.%d", $ed[2], $ed[1], $ed[0]); ?></small>
                        </div>

                        <div class="fktermine-inner-modify">
                            <?php $thisStandard = get_option('standard_ferien') == $row->FID ?>
                            <a class="button <?= $thisStandard ? "button-green" : "button-primary" ?> fe-standard-course" title="Standardferien setzen" href="#">
                                <i class="fa-solid <?= $thisStandard ? "fa-heart-circle-check" : "fa-heart" ?>"></i>
                            </a>
                            <a class="button button-primary fe-list-edit" title="Ferien bearbeiten" href="?page=mb-options-menu&action=ferien-edit&id=<?= $row->FID ?>">
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