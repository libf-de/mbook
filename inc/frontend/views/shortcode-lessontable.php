<?php

    $ret = '';

    $daysArray = array_rotate(["mon", "tue", "wed", "thu", "fri", "sat", "sun"], date('N')-1);

    $sqllessons = $wpdb->get_results("SELECT * FROM `{$dbLessons}` INNER JOIN `{$dbTemplates}` ON `{$dbLessons}`.`TEMPLATE` = `{$dbTemplates}`.`ID` ORDER BY `{$dbLessons}`.WEEKDAY,`{$dbLessons}`.NUM");

    $lessons = array();

    foreach ($sqllessons as $key => $row) {
        $lessons[$row->WEEKDAY][$key] = $row;
    }


    $ret .= "<div class=\"nb-lessontable-outer\">";
    foreach ($daysArray as $dayNum => $dayName) {
        $dayDate = date("d.m.Y", strtotime($dayName));
        $dayNameGer = weekday_names[$dayNum];
        $ret .= "    <div class=\"nb-lessontable-inner\">";
        $ret .= "        <table class=\"form-table\">";
        $ret .= "            <thead>";
        $ret .= "                <tr>";
        $ret .= "                    <th colspan=\"2\" class=\"ws-header\">";
        $ret .= "{$dayNameGer}, den {$dayDate}";
        $ret .= "                    </th>";
        $ret .= "                </tr>";
        $ret .= "            </thead>";
        $ret .= "            <tbody class=\"ws-table-content\">";
        if (empty($lessons[$dayNum])) {
            $ret .= "                <tr class=\"ws-last\" colspan=\"2\">";
            $ret .= "                    <td>";
            $ret .= "                        <p class=\"ws-std-title\">Keine Stunden</p>";
            $ret .= "                    </td>";
            $ret .= "                </tr>";
            $ret .= "            </tbody>";
            $ret .= "                </table>";
            $ret .= "                </div>";
            continue;
        }

        foreach ($lessons[$dayNum] as $key => $row) {
            if ($row->IS_CANCELLED) {
                $OVC = "ws-std-can";
                $OVT = "Fällt aus";
            } elseif ($row->PARTICIPANTS == $row->MAX_PARTICIPANTS) {
                $OVC = "ws-std-full";
                $OVT = "Belegt";
            } else {
                $OVC = "ws-std-free";
                $OVT = "Frei";
            }
            $LCL = (next($lessons[$dayNum])) ? "" : "ws-last";

            $ret .= "            <tr class=\"{$LCL}\" data-id=\"{$row->ID}\">";
            $ret .= "                <td>";
            $ret .= "                    <p class=\"ws-std-title\">";
            $ret .= "                            <a href=\"{$row->LINKURL}\">{$row->TITLE} {$row->NUM}</a>";
            $ret .= "                    </p><small>";
            $ret .= "                        " . substr($row->START, 0, -3) . " &ndash; " . substr($row->END, 0, -3) . " Uhr";
            $ret .= "                    </small>";
            $ret .= "                </td>";
            $ret .= "                <td>";
            $ret .= "                    <input type=\"text\" value=\"{$OVT}\" readonly class=\"ws-std-state {$OVC}\" size=\"5\">";
            $ret .= "                </td>";
            $ret .= "            </tr>";
        }

        $ret .= "            </tbody>";
        $ret .= "        </table>";
        $ret .= "    </div>";
    }

    $ret .= "</div>";


    $ret .= nb_get_pfooter();
    return $ret;

    /* <tr class="<?= $LCL ?>"><td>
        <p class="ws-fpr-title"><a
                href="<?= $row->LINKURL ?>"><?= $row->TITLE ?>
        </p><small><?= $row->START ?> -
        <?= $row->END ?> Uhr</small>
    </td> */
