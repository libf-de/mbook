<div class="manage-controls">
    <form
        action="<?= admin_url('admin-post.php?action=mb_fe_import') ?>"
        method="post">
        <table class="form-table">
            <thead>
                <th class="box-header" colspan="5">
                    <h1>Ferien importieren</h1>
                </th>
            </thead>
            <tbody>
                <tr valign="top">
                    <th scope="row"><strong>Bundesländer</strong></th>
                    <?php
                        $bl = [
                            "BW" => "Baden-Württemberg",
                            "BY" => "Bayern",
                            "BE" => "Berlin",
                            "BB" => "Brandenburg",
                            "HB" => "Bremen",
                            "HH" => "Hamburg",
                            "HE" => "Hessen",
                            "MV" => "Mecklenburg-Vorpommern",
                            "NI" => "Niedersachsen",
                            "NW" => "Nordrhein-Westfalen",
                            "RP" => "Rheinland-Pfalz",
                            "SL" => "Saarland",
                            "SN" => "Sachsen",
                            "ST" => "Sachsen-Anhalt",
                            "SH" => "Schleswig-Holstein",
                            "TH" => "Thüringen"
                            ];
                        $blSh = array_keys($bl);
                        for ($i = 0; $i < count($bl); $i = $i + 4) {
                            echo "<td><input type='checkbox' name=\"laender[]\" value='" . $blSh[$i] . "' id='" . $blSh[$i] . "' /><label for='" . $blSh[$i] . "' class='" . strtolower($blSh[$i]) . "'>" . $bl[$blSh[$i]] . "</label></td><td>";
                            if(isset($blSh[$i+1]))
                                echo "<input type='checkbox' name=\"laender[]\" value='" . $blSh[$i+1] . "' id='" . $blSh[$i+1] . "' /><label for='" . $blSh[$i+1] . "' class='" . strtolower($blSh[$i+1]) . "'>" . $bl[$blSh[$i+1]] . "</label>";
                            echo "</td><td>";
                            if(isset($blSh[$i+2]))
                                echo "<input type='checkbox' name=\"laender[]\" value='" . $blSh[$i+2] . "' id='" . $blSh[$i+2] . "' /><label for='" . $blSh[$i+2] . "' class='" . strtolower($blSh[$i+2]) . "'>" . $bl[$blSh[$i+2]] . "</label>";
                            echo "</td><td>";
                            if(isset($blSh[$i+3]))
                                echo "<input type='checkbox' name=\"laender[]\" value='" . $blSh[$i+3] . "' id='" . $blSh[$i+3] . "' /><label for='" . $blSh[$i+3] . "' class='" . strtolower($blSh[$i+3]) . "'>" . $bl[$blSh[$i+3]] . "</label>";                                
                            echo "</td></tr>";
                            if($i+4 < count($bl))
                                echo "<tr valign=\"top\"><th scope='row'></th>";
                        }
                    ?>
                <tr valign="top">
                    <th scope="row"><strong>Jahre</strong></th>
                    <?php $y = date('Y'); ?>
                    <td>
                        <input type='checkbox' name="jahre[]" value='<?= $y ?>' id='<?= $y ?>'/><label for='<?= $y ?>'><?= $y ?></label>
                    </td>
                    <td>
                        <input type='checkbox' name="jahre[]" value='<?= $y+1 ?>' id='<?= $y+1 ?>'/><label for='<?= $y+1 ?>'><?= $y+1 ?></label>
                    </td>
                    <td>
                        <input type='checkbox' name="jahre[]" value='<?= $y+2 ?>' id='<?= $y+2 ?>'/><label for='<?= $y+2 ?>'><?= $y+2 ?></label>
                    </td>
                    <td>
                        <input type='checkbox' name="jahre[]" value='<?= $y+3 ?>' id='<?= $y+3 ?>'/><label for='<?= $y+3 ?>'><?= $y+3 ?></label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row" colspan="3" class="btmrow">
                        <button type="submit" class="button button-primary"><i class="fa-solid fa-file-import"></i>
                            Importieren</button>
                        <a class="button button-warn"
                            href="<?= add_query_arg('action', 'ferien', admin_url('admin.php?page=mb-options-menu')) ?>">Abbrechen</a>
                    </th>
                </tr>
            </tbody>
        </table>
    </form>
</div>