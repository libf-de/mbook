<div class="manage-controls">
    <table class="form-table">
        <thead>
            <th class="box-header" colspan="2">
                <h1>Ferien</h1>
                <div class="mctop mctools-div">
                    <a href="?page=mb-options-menu&action=ferien-add" class="button button-primary">Neu hinzufügen</a>
                </div>
            </th>
        </thead>
        <tbody>
            <?php foreach($wpdb->get_results("SELECT FID, LABEL, STARTDATE, ENDDATE FROM " . db_ferien . " WHERE FID <> 1 ORDER BY STARTDATE DESC") as $key => $row): ?>
            <tr>
                <td>
                    <div class="fktermine-outer manage-entry manage-table">
                        <div class="fktermine-inner-title">
                            <p class="title"><a
                                    href="?page=mb-options-menu&action=ferien-edit&id=<?= $row->FID ?>"><?= $row->LABEL; ?></a></p>
                            <small><?= $row->STARTDATE ?> - <?= $row->ENDDATE ?></small>
                        </div>

                        <div class="fktermine-inner-modify">
                            <a class="button button-primary fe-list-edit" href="?page=mb-options-menu&action=ferien-edit&id=<?= $row->FID ?>">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <a class="button button-warn fe-delete-course" data-id="<?= $row->FID ?>" data-title="<?= $row->LABEL ?>" href="#">
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