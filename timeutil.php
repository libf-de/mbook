<?php

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