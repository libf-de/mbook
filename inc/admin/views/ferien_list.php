<div class="manage-controls">
    <table class="form-table">
        <thead>
            <th class="nb-listhead-toolbox" colspan="2">
                <h1>Ferien</h1>
                <div class="nb-listhead-toolbox-div">
                    <a href="?page=nb-options-menu&action=ferien-add" class="button button-primary">Neu hinzufügen</a>&nbsp;
                    <a href="?page=nb-options-menu&action=ferien-imp" class="button button-primary">Importieren</a>&nbsp;
                    <a href="?page=nb-options-menu&action=fkurs-clear" class="button button-red">Alte löschen</a>
                </div>
            </th>
        </thead>
        <tbody>
            <?php $curDate = date("Y-m-d"); ?>
            <?php foreach($wpdb->get_results("SELECT FID, LABEL, STARTDATE, ENDDATE, ACTIVE FROM " . db_ferien . " WHERE FID <> 1 ORDER BY ACTIVE DESC, STARTDATE DESC") as $key => $row): 
                $thisStandard = get_option('standard_ferien') == $row->FID;
                $sd = explode("-", $row->STARTDATE);
                $ed = explode("-", $row->ENDDATE);
                
                ?>
            <tr>
                <td>
                    <div class="nb-listelem-outer manage-entry <?= $row->ACTIVE ? "nb-ferien-active" : "" ?> <?= $row->ENDDATE < $curDate ? "nb-list-past" : ( $row->STARTDATE > $curDate ? "nb-list-future" : "nb-list-current" ) ?>" data-id="<?= $row->FID ?>">
                        <div class="nb-listelem-inner-title">
                            <p class="title"><a href="?page=nb-options-menu&action=fkurs-manage&fe=<?= $row->FID ?>"><?= $row->LABEL; ?></a></p>
                            <?php  ?>
                            <small><?= sprintf("%02d.%02d.%d", $sd[2], $sd[1], $sd[0]); ?> - <?= sprintf("%02d.%02d.%d", $ed[2], $ed[1], $ed[0]); ?></small>
                        </div>

                        <div class="nb-listelem-inner-modify">
                            <a class="button button-primary fe-active-course" title="Ferien de-/aktivieren" href="#">
                                <i class="fa-solid <?= $row->ACTIVE ? "fa-eye" : "fa-eye-slash" ?>"></i>
                            </a>
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