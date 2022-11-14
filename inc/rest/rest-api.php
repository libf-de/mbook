<?php

/*** REST API functions ***/
function nb_api_init()
{
    register_rest_route('nubook/v1', '/set-parts', array(
      'methods' => 'POST',
      'callback' => 'handle_api_ferientermine_parts',
      'permission_callback' => 'nb_api_admin_perms',
    ));

    register_rest_route('nubook/v1', '/fk/list', array(
        'methods' => 'POST',
        'callback' => 'handle_api_ferienkurse_list',
        'permission_callback' => 'nb_api_admin_perms',
    ));

    register_rest_route('nubook/v1', '/fk/detail', array(
        'methods' => 'POST',
        'callback' => 'handle_api_ferienkurse_detail',
        'permission_callback' => 'nb_api_admin_perms',
    ));

    register_rest_route('nubook/v1', '/fk/test', array(
        'methods' => 'POST',
        'callback' => 'handle_api_test',
        'permission_callback' => 'nb_api_admin_perms',
    ));


    /*register_rest_route('nubook/v1', '/get-prints', array(
      'methods' => 'GET',
      'callback' => 'handle_api_ferientermine_print',
      //'permission_callback' => 'nb_api_admin_perms',
    ));*/
}

function nb_api_admin_perms()
{
    return current_user_can('manage_options');
}

function handle_api_test() {
    wp_send_json($_POST);
    return;
}

function handle_api_ferientermine_print()
{
    global $wpdb;
    $template = db_ferientemplates;
    $termin = db_ferientermine;
    $ferien = db_ferien;
    $pr = $wpdb->get_results("SELECT `$termin`.*, `$template`.TITLE FROM `$termin` INNER JOIN `$template` ON `$termin`.`TEMPLATE` = `$template`.`ID` WHERE `$termin`.`DATESTART` >= CURDATE() ORDER BY `$termin`.`DATESTART`");
    wp_send_json($pr);
    return;
}

function handle_api_ferienkurse_detail()
{
    global $wpdb;
    $template = db_ferientemplates;
    $termin = db_ferientermine;
    $ferien = db_ferien;

    $filter = "WHERE ";
    $params = array();

    if (isset($_POST["sh"])) {
        if (!preg_match("/[a-zA-Z]{1,5}[0-9]{6,8}[a-zA-Z]*/", $_POST["sh"])) {
            die("{ \"status\": 400, \"msg\": \"paramter sh is no shortcode\"}");
            return;
        }
        $filter .= "`$termin`.SHORTCODE = %s AND ";
        $params[] = $_POST["sh"];
    }

    //Startdate later than given date, yyyy-mm-dd string
    if (isset($_POST["sd"])) {
        if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $_POST["sd"])) {
            die("{ \"status\": 400, \"msg\": \"paramter sd must be in format yyyy-mm-dd\"}");
            return;
        }
        $filter .= "DATE(`$termin`.DATESTART) >= %s AND ";
        $params[] = $_POST["sd"];
    }
    //Enddate before given date, yyyy-mm-dd string
    if (isset($_POST["ed"])) {
        if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $_POST["ed"])) {
            die("{ \"status\": 400, \"msg\": \"paramter ed must be in format yyyy-mm-dd\"}");
            return;
        }
        $filter .= "DATE(`$termin`.DATEEND) <= %s AND ";
        $params[] = $_POST["ed"];
    }
    //Show past kurse
    if (!isset($_POST["past"])) {
        $filter .= "DATE(`$termin`.DATESTART) >= CURDATE() AND ";
    }
    //Template, numeric id
    if (isset($_POST["t"])) {
        if (!is_numeric($_POST["t"])) {
            die("{ \"status\": 400, \"msg\": \"paramter t must be numeric, use tn for string\"}");
            return;
        }
        $filter .= "`$termin`.TEMPLATE = %d AND ";
        $params[] = intval($_POST["t"]);
    }
    //Template, by title, urlencoded string
    if (isset($_POST["tn"])) {
        if (!is_string($_POST["tn"])) {
            die("{ \"status\": 400, \"msg\": \"paramter t must be string and urlencoded\"}");
            return;
        }
        $filter .= "`$template`.TITLE LIKE %s AND ";
        $params[] = urldecode($_POST["tn"]);
    }
    $filter .= "`$termin`.ID >= %d";
    $params[] = 1;

    $rspObj = new StdClass();

    $pr = $wpdb->get_row($wpdb->prepare("SELECT `$termin`.*, `$template`.*, `$ferien`.* FROM `$termin` INNER " . 
                                            "JOIN `$template` ON `$termin`.`TEMPLATE` = `$template`.`ID` INNER " . 
                                            "JOIN `$ferien` ON `$termin`.FERIEN = `$ferien`.FID $filter ORDER " . 
                                            "BY `$termin`.`DATESTART` LIMIT 1", $params));

    if($pr == null) {
        $rspObj->status = 204;
        $rspObj->msg = "No course found";
        $rspObj->payload = new StdClass();
        $rspObj->payload->ID = -1;
        $rspObj->payload->TEMPLATE = -1;
        $rspObj->payload->FERIEN = -1;
        $rspObj->payload->SHORTCODE = "";
        $rspObj->payload->MAX_PARTICIPANTS = -1;
        $rspObj->payload->PARTICIPANTS = -1;
        $rspObj->payload->IS_OPEN_END = 0;
        $rspObj->payload->IS_CANCELLED = 0;
        $rspObj->payload->CALENDAR_EVENT_ID = null;
        $rspObj->payload->TITLE = "NOTFOUND";
        $rspObj->payload->DESCRIPTION = "NOTFOUND";
    } else {
        $rspObj->status = 200;
        $rspObj->payload = $pr;
    }
    
    
    wp_send_json($rspObj);
    return;
}

function handle_api_ferienkurse_list()
{
    global $wpdb;
    $template = db_ferientemplates;
    $termin = db_ferientermine;
    $ferien = db_ferien;

    $filter = "WHERE ";
    $params = array();
    //Ferien, numeric id
    if (isset($_POST["f"])) {
        if (!is_numeric($_POST["f"])) {
            die("{ \"status\": 400, \"msg\": \"paramter f must be numeric\"}");
            return;
        }
        $filter .= "`$termin`.FERIEN = %d AND ";
        $params[] = intval($_POST["f"]);
    }

    //Maybe implement string-like ferien


    if (isset($_POST["sh"])) {
        if (!preg_match("/[a-zA-Z]{1,5}[0-9]{6,8}[a-zA-Z]*/", $_POST["sh"])) {
            die("{ \"status\": 400, \"msg\": \"paramter sh is no shortcode\"}");
            return;
        }
        $filter .= "`$termin`.SHORTCODE = %s AND ";
        $params[] = $_POST["sh"];
    }

    //Startdate later than given date, yyyy-mm-dd string
    if (isset($_POST["sd"])) {
        if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $_POST["sd"])) {
            die("{ \"status\": 400, \"msg\": \"paramter sd must be in format yyyy-mm-dd\"}");
            return;
        }
        $filter .= "DATE(`$termin`.DATESTART) >= %s AND ";
        $params[] = $_POST["sd"];
    }
    //Enddate before given date, yyyy-mm-dd string
    if (isset($_POST["ed"])) {
        if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $_POST["ed"])) {
            die("{ \"status\": 400, \"msg\": \"paramter ed must be in format yyyy-mm-dd\"}");
            return;
        }
        $filter .= "DATE(`$termin`.DATEEND) <= %s AND ";
        $params[] = $_POST["ed"];
    }
    //Show past kurse
    if (!isset($_POST["past"])) {
        $filter .= "DATE(`$termin`.DATESTART) >= CURDATE() AND ";
    }
    //Template, numeric id
    if (isset($_POST["t"])) {
        if (!is_numeric($_POST["t"])) {
            die("{ \"status\": 400, \"msg\": \"paramter t must be numeric, use tn for string\"}");
            return;
        }
        $filter .= "`$termin`.TEMPLATE = %d AND ";
        $params[] = intval($_POST["t"]);
    }
    //Template, by title, urlencoded string
    if (isset($_POST["tn"])) {
        if (!is_string($_POST["tn"])) {
            die("{ \"status\": 400, \"msg\": \"paramter t must be string and urlencoded\"}");
            return;
        }
        $filter .= "`$template`.TITLE LIKE %s AND ";
        $params[] = urldecode($_POST["tn"]);
    }
    //Level, show courses with where EXP_LEVEL_MIN is smaller and EXP_LEVEL_MAX is greater than given numeric value
    if (isset($_POST["l"])) {
        if (!is_numeric($_POST["l"])) {
            die("{ \"status\": 400, \"msg\": \"paramter ml must be numeric\"}");
            return;
        }
        $filter .= "`$template`.EXP_LEVEL_MIN >= %d AND `$template`.EXP_LEVEL_MAX <= %d AND ";
        $params[] = intval($_POST["l"]);
        $params[] = intval($_POST["l"]);
    }
    $filter .= "`$termin`.ID >= %d";
    $params[] = 1;

    $rspObj = new StdClass();

    $pr = $wpdb->get_results($wpdb->prepare("SELECT `$termin`.*, `$template`.*, `$ferien`.* FROM `$termin` INNER " . 
                                            "JOIN `$template` ON `$termin`.`TEMPLATE` = `$template`.`ID` INNER " . 
                                            "JOIN `$ferien` ON `$termin`.FERIEN = `$ferien`.FID $filter ORDER " . 
                                            "BY `$termin`.`DATESTART`", $params));

    if($pr == null) {
        $rspObj->status = 204;
        $rspObj->msg = "No course(s) found";
    } else {
        $rspObj->status = 200;
        $rspObj->payload = $pr;
    }
    
    
    wp_send_json($rspObj);
    return;
}

function handle_api_ferientermine_parts()
{
    global $wpdb;
    $template = db_ferientemplates;
    $termin = db_ferientermine;
    $ferien = db_ferien;

    if (!isset($_POST['id']) or !isset($_POST['val'])) {
        wp_send_json(array("status" => 400, "code" => "fail", "msg" => "Missing POST parameter(s) ID and/or VAL", "data" => array("status" => 400) ));
        return;
    } elseif (intval($_POST['id']) == -1) {
        wp_send_json(array("status" => 204, "code" => "fail", "msg" => "Course not found", "data" => array("status" => 400) ));
        return;
    } elseif (!is_numeric($_POST['id']) or !is_numeric($_POST['val'])) {
        wp_send_json(array("status" => 204, "code" => "fail", "msg" => "POST parameter(s) ID and/or VAL must be numeric!", "data" => array("status" => 400) ));
        return;
    } elseif ($wpdb->update(db_ferientermine, array('PARTICIPANTS' => intval($_POST['val'])), array('ID' => $_POST['id']), array('%d'), array('%d')) !== false) {
        require_once(dirname(__DIR__) . '/calendar/caltest.php');
        $gca = new GoogleCalenderAdapter();
        $modEvent = $wpdb->get_row($wpdb->prepare("SELECT ID, SHORTCODE, CALENDAR_EVENT_ID, PARTICIPANTS, MAX_PARTICIPANTS FROM " . db_ferientermine ." WHERE ID = %d", $_POST['id']));
        if ($modEvent != null) {
            if ($gca->update_calendar_event_occupation($modEvent)) {
                wp_send_json(array("status" => 200, "code" => "ok", "msg" => "Update participants OK", "data" => array("status" => 200, "id" => intval($_POST['id']), "value" => intval($_POST['val']))));
            } else {
                wp_send_json(array("status" => 206, "code" => "ok", "msg" => "Update participants OK, GCAL ERROR", "data" => array("status" => 200, "id" => intval($_POST['id']), "value" => intval($_POST['val']))));
            }
        } else {
            wp_send_json(array("status" => 200, "code" => "ok", "msg" => "Update participants OK, GCAL ERROR", "data" => array("status" => 200, "id" => intval($_POST['id']), "value" => intval($_POST['val']))));
        }

        return;
    }

    wp_send_json(array("status" => 500, "code" => "fail", "msg" => "Database error!", "data" => array("status" => 500) ));
    return;
}
