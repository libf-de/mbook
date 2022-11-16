<?php
class GoogleCalenderAdapter {
    private $client;
    private $service;

    const CALENDAR_ID = 'ad6e63659c7ee231eddf5ca6f22c334c59ba1409db12c4bc1a1e527008268b0c@group.calendar.google.com';

    function __construct() {
        global $plugin_root;
        
        require_once $plugin_root . 'vendor/autoload.php';
    
        $this->client = new Google_Client();
        //The json file you got after creating the service account
        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $plugin_root . 'inc/calendar/mbook-dev-3c51e99497c8.json');
        $this->client->useApplicationDefaultCredentials();
        $this->client->setApplicationName("test_calendar");
        $this->client->setScopes(Google_Service_Calendar::CALENDAR);
        $this->client->setAccessType('offline');

        $this->service = new Google_Service_Calendar($this->client);
    }

    function update_calendar_event_occupation($kurs): bool {
        if(!isset($kurs->CALENDAR_EVENT_ID) || !isset($kurs->PARTICIPANTS) || !isset($kurs->MAX_PARTICIPANTS) || !isset($kurs->SHORTCODE)) return FALSE;
        try {
            $event = $this->service->events->get(self::CALENDAR_ID, $kurs->CALENDAR_EVENT_ID);
            $event->setLocation(sprintf("#%s - %s", $kurs->SHORTCODE, ($kurs->PARTICIPANTS == $kurs->MAX_PARTICIPANTS ? "BELEGT" : "FREI")));
            $event = $this->service->events->update(self::CALENDAR_ID, $event->getId(), $event);
        } catch(Exception $e) {
            return FALSE;
        }
        return TRUE;
    }

    function update_calendar_event($kurs) {
        $isNew = !isset($kurs->CALENDAR_EVENT_ID);
        $startDate = DateTime::createFromFormat(mysql_date, $kurs->DATESTART);
    
        if($kurs->DATEEND != null) {
            $endDate = DateTime::createFromFormat(mysql_date, $kurs->DATEEND);
        } else {
            $endDate = DateTime::createFromFormat(mysql_date, $kurs->DATESTART);
            $endDate->add(new DateInterval('PT10M'));
        }

        if($isNew)
            $event = new Google_Service_Calendar_Event();
        else
            $event = $this->service->events->get(self::CALENDAR_ID, $kurs->CALENDAR_EVENT_ID);

        $startEdt = new Google_Service_Calendar_EventDateTime();
        $startEdt->setDateTime($startDate->format(\DateTime::RFC3339));

        $endEdt = new Google_Service_Calendar_EventDateTime();
        $endEdt->setDateTime($endDate->format(\DateTime::RFC3339));

        $event->setStart($startEdt);
        $event->setEnd($endEdt);
        $event->setSummary($kurs->TITLE);

        if(!$isNew) {
            try {
                $rscMeta = json_decode($event->getDescription());
            } catch(Exception $e) {
                $rscMeta = new stdClass();
                $rscMeta->createDate = time();
                $rscMeta->createUser = "nuBook server";
            }    
        } else {
            $rscMeta = new stdClass();
            $rscMeta->createDate = time();
            $rscMeta->createUser = "nuBook server";
        }

        $rscMeta->hasCancelled = boolval($kurs->IS_CANCELLED);
        $rscMeta->modifyDate = time();
        $rscMeta->modifyUser = "nuBook server";
        $rscMeta->description = "Ferienprogramm";

        $event->setDescription(json_encode($rscMeta));
        $event->setLocation(sprintf("#%s - %s", $kurs->SHORTCODE, ($kurs->PARTICIPANTS == $kurs->MAX_PARTICIPANTS ? "BELEGT" : "FREI")));

        try {
            if($isNew)
                $event = $this->service->events->insert(self::CALENDAR_ID, $event);
            else
                $event = $this->service->events->update(self::CALENDAR_ID, $event->getId(), $event);
        } catch(Exception $e) {
            return null;
        }
        
        return $event->id;
    }

    function delete_calendar_event($kurs) {
        if(!isset($kurs->CALENDAR_EVENT_ID))
            return null;
        if($kurs->CALENDAR_EVENT_ID == null)
            return null;
        try {
            $this->service->events->delete(self::CALENDAR_ID, $kurs->CALENDAR_EVENT_ID);
        } catch(Exception $e) {
            return FALSE;
        }
        return TRUE;
    }

    /*function create_calendar_event($kurs) {
        $startDate = DateTime::createFromFormat(mysql_date, $kurs->DATESTART);
    
        if($kurs->DATEEND != null) {
            $endDate = DateTime::createFromFormat(mysql_date, $kurs->DATEEND);
        } else {
            $endDate = DateTime::createFromFormat(mysql_date, $kurs->DATESTART);
            $endDate->add(new DateInterval('PT10M'));
        }
    
        $event = new Google_Service_Calendar_Event(array(
            'summary' => $kurs->TITLE,
            'description' => $kurs->SHORTCODE,
            'start' => array(
                'dateTime' => $startDate->format(\DateTime::RFC3339)
            ),
            'end' => array(
                'dateTime' => $endDate->format(\DateTime::RFC3339)
            )
        ));
      
        try {
            $event = $this->service->events->insert(self::CALENDAR_ID, $event);
        } catch(Exception $e) {
            return FALSE;
        }
        
        return $event->id;
    }*/
}