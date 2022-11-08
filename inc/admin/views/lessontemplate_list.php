<div class="manage-controls">
<table class="form-table">
    <thead>
        <tr>
            <th class="box-header" colspan="2">
                <h1>Unterrichts-Vorlagen</h1><div class="mctop mctools-div">
                    <a href="?page=mb-options-lessons&action=lstemplates-add" class="button button-primary">Erstellen</a>
                </div>
            </th>
        </tr>
    </thead>
    <tbody>
        <?php foreach( $wpdb->get_results("SELECT ID, TITLE, TYP, DEFAULT_DURATION, DEFAULT_MAX_PARTICIPANTS FROM " . db_lessontemplates .  " ORDER BY TITLE") as $key => $row): ?>
        <tr>
            <td>
                <div class="mb-listelem-outer manage-entry manage-table">
                    <div class="fktermine-inner-title">
                        <?php list($durationDays, $durationHours, $durationMins, $isOpenEnd) = mins_to_duration($row->DEFAULT_DURATION); ?>
                        <p class="title"><a href="?page=mb-options-lessons&action=lstemplates-edit&id=<?= $row->ID; ?>"><?= $row->TITLE; ?></a></p>
                        <small><?= lesson_types[$row->TYP]; ?>, max. <?= $row->DEFAULT_MAX_PARTICIPANTS ?> Teilnehmer, 
                            <?php 
                                if($durationDays > 0) echo $durationDays . "d ";
                                if($durationHours > 0) echo $durationHours . "h ";
                                if($durationMins > 0) echo $durationMins . "min "; ?>
                        </small>
                    </div>
  
                    <div class="fktermine-inner-modify">
                        <a class="button button-primary ft-list-edit" href="?page=mb-options-lessons&action=lstemplates-edit&id=<?= $row->ID; ?>"><i class="fa-solid fa-pen"></i></a>
                        <a class="button button-warn lt-delete-lesson" data-id="<?= $row->ID ?>" data-title="<?= $row->TITLE ?>" href="#"><i class="fa-solid fa-trash-can"></i></a>
                    </div>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
                        </div>
<script type="text/javascript" defer>jQuery(document).ready(function($) { initListLTemplate(); });</script>