<div class="manage-controls">
    <form method="post" action="<?= admin_url( 'admin-post.php?action=mb_fe_modify') ?>">
        <?php if(isset($id)): ?><input type="hidden" name="id" value="<?= $id ?>"><?php endif; ?>
        <table class="form-table manage-table">
            <tbody>
                <tr valign="top">
                    <th scope="row"><strong>Bezeichnung</strong></th>
                    <td>
                        <input type="text" pattern=".{5,250}" required title="Der Titel sollte mindestens 5 und max. 250 Zeichen lang sein" name="title" placeholder='Ferien-Titel' value="<?= isset($template->LABEL) ? $template->LABEL : ""?>">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><strong>Beginn</strong></th>
                    <td>
                        <input type="date" class="startDate" required name="startDate" value="<?= isset($template->STARTDATE) ? $template->STARTDATE : ""?>">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><strong>Ende</strong></th>
                    <td>
                        <input type="date" class="endDate" required name="endDate" value="<?= isset($template->ENDDATE) ? $template->ENDDATE : ""?>">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row" class="btmrow">
                        <button type="submit" class="button button-primary"><i class="fa-solid fa-floppy-disk"></i> Speichern</button>
                        <a class="button button-warn" href="<?= add_query_arg('action', 'ferien', admin_url( 'admin.php?page=mb-options-menu')) ?>">Abbrechen</a>
                    </th>
                </tr>
            </tbody>
        </table>
    </form>
</div>