<?php
if (basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"]))
	die("do not call directly!");

    $ret = '';

	$daysArray = [ "mon", "tue", "wed", "thu", "fri", "sat", "sun" ];
	try {
		$daysArray = array_rotate( $daysArray, date( 'N' ) - 1 );
	} catch ( Exception $e ) { }

$sqllessons = $wpdb->get_results("SELECT * FROM `{$dbLessons}` INNER JOIN `{$dbTemplates}` ON `{$dbLessons}`.`TEMPLATE` = `{$dbTemplates}`.`ID` ORDER BY `{$dbLessons}`.WEEKDAY,`{$dbLessons}`.NUM");
    $lessons = array();

    foreach ($sqllessons as $key => $row) {
        $lessons[$row->WEEKDAY][$key] = $row;
    }

    $ret .= "<div class=\"nb-lessontable-outer\">";

    foreach ($daysArray as $dayNum => $dayName) {
        $dayDate = date("d.m.Y", strtotime($dayName));
        $dayNameGer = weekday_names[$dayNum];
        $ret .= "
    <div class=\"nb-lessontable-inner\">
        <table class=\"form-table\">
            <thead>
                <tr>
                    <th colspan=\"2\" class=\"ws-header\">
{$dayNameGer}, den {$dayDate}
                    </th>
                </tr>
            </thead>
            <tbody class=\"ws-table-content\">";
        if (empty($lessons[$dayNum])) {
            $ret .= "
                <tr class=\"ws-last\">
                    <td colspan=\"2\">
                        <p class=\"ws-std-title\">Keine Stunden</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>";
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

            $ret .= "
            <tr class=\"{$LCL}\" data-id=\"{$row->ID}\">
                <td>
                    <p class=\"ws-std-title\">
                            <a href=\"{$row->LINKURL}\">{$row->TITLE} {$row->NUM}</a>
                    </p><small>
                        " . substr($row->START, 0, -3) . " &ndash; " . substr($row->END, 0, -3) . " Uhr
                    </small>
                </td>
                <td>
                    <input type=\"text\" value=\"{$OVT}\" readonly class=\"ws-std-state {$OVC}\" size=\"5\">
                </td>
            </tr>";
        }

        $ret .= "
            </tbody>
        </table>
    </div>";
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
