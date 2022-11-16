<div class="nb-manage-controls"><form method="post" action=""><input type="hidden" name="action" value="config"><table class="form-table cfg-table">
<tbody>
<tr valign="top"><td colspan="2"><h3>Angebot-Links</h3></td></tr>
<tr valign="top"><th scope="row"><strong>Link bei Ponyführstunden</strong></th><td><input type="text" name="std1" value="<?= esc_attr(get_option('std1')) ?>"/></td></tr>
<tr valign="top"><th scope="row"><strong>Link bei Shettyreitstunden</strong></th><td><input type="text" name="std2" value="<?= esc_attr(get_option('std2')) ?>"/></td></tr>
<tr valign="top"><th scope="row"><strong>Link bei Gruppenreitstunden</strong></th><td><input type="text" name="std3" value="<?= esc_attr(get_option('std3')) ?>"/></td></tr>
<tr valign="top"><th scope="row"><strong>Link bei Erwachsenenreitstunden</strong></th><td><input type="text" name="std4" value="<?= esc_attr(get_option('std4')) ?>"/></td></tr>
<tr valign="top"><th scope="row"><strong>Link bei Pferdezeiten</strong></th><td><input type="text" name="std5" value="<?= esc_attr(get_option('std5')) ?>"/></td></tr>
<tr valign="top"><th scope="row"><strong>Link bei Voltigierstunden</strong></th><td><input type="text" name="std7" value="<?= esc_attr(get_option('std7')) ?>"/></td></tr>
<tr valign="top"><th scope="row"><strong>Link bei sonstige</strong></th><td><input type="text" disabled name="std6" value="nicht verwendet" <?= esc_attr(get_option('std6')) ?>/></td></tr>
<tr valign="top"><td colspan="2" class="cfg-spacer"><hr></td></tr>
<tr valign="top"><td colspan="2"><h3>Anzeige-Einstellungen</h3></td></tr>
<tr valign="top"><th scope="row"><strong>Standard-Anzeigeart</strong></th><td><input type="checkbox" name="show_all_days" value="alldays" <?= (get_option('show_all_days') == 'TRUE' ? 'checked' : '') ?>> Alle Tage zeigen</td></tr>
<tr valign="top"><th scope="row"><strong>Angezeigte Tage</strong></th><td><input type="checkbox" name="show_saturday" value="showsat" <?= (get_option('show_saturday') == 'TRUE' ? 'checked' : '') ?>> Samstag zeigen</td></tr>
<tr valign="top"><th scope="row">&nbsp;</th><td><input type="checkbox" name="show_sunday" value="showsun" <?= (get_option('show_sunday') == 'TRUE' ? 'checked' : '') ?>> Sonntag zeigen</td></tr>
<tr valign="top"><td colspan="2" class="cfg-spacer"><hr></td></tr>
<tr valign="top"><td colspan="2"><h3 id="ferien">Ferien-Einstellungen</h3></td></tr>
<tr valign="top"><th scope="row"><strong>Ferien-Titel</strong></th><td><input type="text" name="ferientitel" value="<?= esc_attr(get_option('ferientitel')) ?>"/></td></tr>
<tr valign="top"><th scope="row"><strong>Anzeigeart</strong></th><td><input type="checkbox" name="ferien_following" value="follow"<?= (get_option('ferien_following') == 'TRUE' ? 'checked' : '') ?>> Nur zukünftige Kurse anzeigen</td></tr>
<tr valign="top"><th scope="row"><strong>Teilnehmer-Anzeige</strong></th><td><input type="checkbox" name="show_max_tn" value="follow"<?= (get_option('show_max_tn') == 'TRUE' ? 'checked' : '') ?>> Maximale Teilnehmer anzeigen</td></tr>
<tr valign="top"><th scope="row" class="form-table-btmrow"><input type="submit" class="button button-primary" value="Speichern"></th></tr>
</tbody></table></form></div>
