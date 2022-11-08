<?php

// Pretty-prints times, omitting :00:00's. Input is hh:mm:ss as string
function pretty_time($timeStr) {
    try {
        return pretty_time_arr(explode(":", $timeStr));
    } catch(Exception $e) {
        return $timeStr;
    }
}

// Pretty-prints times, omitting :00:00's. Input is hh,mm(,ss) as array
function pretty_time_arr($timeArr) {
    if(count($timeArr) == 2) return $timeArr[0] . ($timeArr[1] > 0 ? ":" . $timeArr[1] : ""); //hh,mm input
    elseif(count($timeArr) == 3) return $timeArr[0] . ($timeArr[1] > 0 || $timeArr[2] > 0 ? ":" . $timeArr[1] : "") . ($timeArr[2] > 0 ? ":" . $timeArr[2] : ""); //hh,mm,ss input
    else return implode(":", $timeArr);
}

// Converts HH:MM to minutes. Returns int or null if invalid input is provided
function hh_mm_to_mins($input) {
    $input = strip_tags($input);
    if(!preg_match("/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/", $input)) {
        return null;
    } else {
        $input = explode(':', $input);
        return ($input[0]*60) + ($input[1]);
    }
}

// Converts minutes to HH:MM. Returns null if input is invalid.
function mins_to_hh_mm($input) {
    if(!is_numeric($input)) {
        return null;
    }
    return sprintf('%02d:%02d', floor($input / 60), $input % 60);
}

function duration_to_mins($days, $hours, $mins) {
    if(!is_numeric($days) || !is_numeric($hours) || !is_numeric($mins)) {
        return null;
    }
    return (1440*intval($days)) + (60*intval($hours)) + intval($mins);
}

function mins_to_duration($input) {
    if(!is_numeric($input)) {
        return null;
    }
    if($input == -1) {
        return [0, 0, 0, true];
    }
    return [floor($input / 1440), floor($input % 1440 / 60), floor($input % 1440 % 60), false];
}

?>