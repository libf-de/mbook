<?php



/**
 * Pretty-prints times, omitting :00:00's. Input is hh:mm:ss as string
 *
 * @param $timeStr string input time string in hh:mm[:ss]
 *
 * @return string time string
 */
function pretty_time( string $timeStr): string {
    try {
        return pretty_time_arr(explode(":", $timeStr));
    } catch(Exception $e) {
        return $timeStr;
    }
}

//
/**
 * Pretty-prints times, omitting :00:00's. Input is hh,mm(,ss) as array
 *
 * @param $timeArr array input time as hh:mm[:ss] array
 *
 * @return string time string
 */
function pretty_time_arr( array $timeArr): string {
    if(count($timeArr) == 2) return $timeArr[0] . ($timeArr[1] > 0 ? ":" . $timeArr[1] : ""); //hh,mm input
    elseif(count($timeArr) == 3) return $timeArr[0] . ($timeArr[1] > 0 || $timeArr[2] > 0 ? ":" . $timeArr[1] : "") . ($timeArr[2] > 0 ? ":" . $timeArr[2] : ""); //hh,mm,ss input
    else return implode(":", $timeArr);
}


/**
 * Converts HH:MM to minutes. Returns int or null if invalid input is provided
 *
 * @param $input string time string in hh:mm
 *
 * @return int|null time in minutes or null (invalid input)
 */
function hh_mm_to_mins( string $input) {
    $input = strip_tags($input);
    if(!preg_match("/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/", $input)) {
        return null;
    } else {
        $input = explode(':', $input);
        return ($input[0]*60) + ($input[1]);
    }
}


/**
 * // Converts minutes to HH:MM. Returns null if input is invalid.
 *
 * @param $input int time in minutes
 *
 * @return string|null time string or null (invalid input)
 */
function mins_to_hh_mm( int $input) {
    if(!is_numeric($input)) {
        return null;
    }
    return sprintf('%02d:%02d', floor($input / 60), $input % 60);
}

/**
 * Converts duration (days, hours, minutes) to minutes
 *
 * @param $days int
 * @param $hours int
 * @param $mins int
 *
 * @return int|null time in minutes or null (invalid input)
 */
function duration_to_mins( int $days, int $hours, int $mins) {
    if(!is_numeric($days) || !is_numeric($hours) || !is_numeric($mins)) {
        return null;
    }
    return (1440*intval($days)) + (60*intval($hours)) + intval($mins);
}

/**
 * Converts minutes to duration array
 *
 * @param $input int time in minutes
 *
 * @return array|null [days,hours,mins,openEnd] or null (invalid input)
 */
function mins_to_duration( int $input) {
    if(!is_numeric($input)) {
        return null;
    }
    if($input == -1) {
        return [0, 0, 0, true];
    }
    return [floor($input / 1440), floor($input % 1440 / 60), floor($input % 1440 % 60), false];
}