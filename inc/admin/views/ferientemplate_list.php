<div class="manage-controls">
<table class="form-table">
    <thead>
        <tr>
            <th class="nb-listhead-toolbox" colspan="2">
                <h1>Ferienkurs-Vorlagen</h1><div class="nb-listhead-toolbox-div">
                    <a href="?page=nb-options-menu&action=fktemplates-add" class="button button-primary">Erstellen</a>
                </div>
            </th>
        </tr>
    </thead>
    <tbody>
        <?php foreach( $wpdb->get_results("SELECT ID, TITLE, EXP_LEVEL_MIN, DEFAULT_WEEKDAY, DEFAULT_STARTTIME, DEFAULT_DURATION FROM " . db_ferientemplates .  " ORDER BY EXP_LEVEL_MIN,TITLE") as $key => $row): ?>
        <tr>
            <td>
                <div class="nb-listelem-outer manage-entry">
                    <div class="nb-listelem-inner-title">
                        <p class="title"><a href="?page=nb-options-menu&action=fktemplates-edit&id=<?= $row->ID; ?>"><?= $row->TITLE; ?></a></p>
                        <?php list($durationDays, $durationHours, $durationMins, $isOpenEnd) = mins_to_duration($row->DEFAULT_DURATION); ?>
                        <small><?= weekday_name(intval($row->DEFAULT_WEEKDAY)); ?>, ab <?= mins_to_hh_mm($row->DEFAULT_STARTTIME); ?>  Uhr
                            <?php if(!$isOpenEnd) {
                                echo " + ";
                                if($durationDays > 0) echo $durationDays . "d ";
                                if($durationHours > 0) echo $durationHours . "h ";
                                if($durationMins > 0) echo $durationMins . "min ";
                            } ?>
                        </small>
                    </div>
  
                    <div class="nb-listelem-inner-modify">
                        <a class="button button-primary ft-list-edit" href="?page=nb-options-menu&action=fktemplates-edit&id=<?= $row->ID; ?>"><i class="fa-solid fa-pen"></i></a>
                        <a class="button button-warn ft-delete-course" data-id="<?= $row->ID ?>" data-title="<?= $row->TITLE ?>" href="#"><i class="fa-solid fa-trash-can"></i></a>
                    </div>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
                        </div>
<script type="text/javascript" defer>jQuery(document).ready(function($) { initListFTemplate(); });</script>