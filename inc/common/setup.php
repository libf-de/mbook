<?php
function mb_init() {
    global $wpdb;
    global $mb_db_version;
  
    $utname = $wpdb->prefix . "wmb_ust";
    $pfname = $wpdb->prefix . "wmb_pfd";
  
    $charset_collate = $wpdb->get_charset_collate();

    $sql_lessontemplates_init = "CREATE TABLE " . db_lessontemplates . " (
      `ID` INT UNSIGNED NOT NULL AUTO_INCREMENT,
      `TITLE` VARCHAR(50) NULL,
      `TYP` TINYINT NULL DEFAULT 0,
      `SHORTHAND` VARCHAR(5) NULL,
      `DESCRIPTION` TEXT NULL,
      `LINKURL` TEXT NULL,
      `DEFAULT_DURATION` INT NULL,
      `DEFAULT_MAX_PARTICIPANTS` INT NULL,
      `EXP_LEVEL_MIN` INT NULL DEFAULT 0,
      `EXP_LEVEL_MAX` INT NULL DEFAULT 99,
      PRIMARY KEY (`ID`)) $charset_collate";

    $sql_lessons_init = "CREATE TABLE " . db_lessons . " (
        `ID` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `NUM` INT UNSIGNED NULL,
        `TEMPLATE` INT UNSIGNED NOT NULL,
        `SHORTCODE` TINYTEXT NULL,
        `START` TIME NULL,
        `END` TIME NULL,
        `WEEKDAY` TINYINT NULL,
        `MAX_PARTICIPANTS` INT NULL,
        `PARTICIPANTS` INT NULL DEFAULT 0,
        `IS_CANCELLED` TINYINT NULL DEFAULT 0,
        PRIMARY KEY (`ID`),
        INDEX `ID_idx` (`TEMPLATE` ASC),
        CONSTRAINT `LessonID`
          FOREIGN KEY (`TEMPLATE`)
          REFERENCES `" . db_lessontemplates . "` (`ID`)
          ON DELETE CASCADE
          ON UPDATE CASCADE) $charset_collate";
  
    $sql_ferientemplates_init = "CREATE TABLE " . db_ferientemplates . " (
      `ID` INT UNSIGNED NOT NULL AUTO_INCREMENT,
      `TITLE` VARCHAR(50) NULL,
      `SHORTHAND` VARCHAR(5) NULL,
      `DESCRIPTION` TEXT NULL,
      `LINKURL` TEXT NULL,
      `DEFAULT_DURATION` INT NULL,
      `DEFAULT_STARTTIME` INT NULL,
      `DEFAULT_WEEKDAY` INT NULL,
      `DEFAULT_MAX_PARTICIPANTS` INT NULL,
      `EXP_LEVEL_MIN` INT NULL DEFAULT 0,
      `EXP_LEVEL_MAX` INT NULL DEFAULT 99,
      PRIMARY KEY (`ID`)) $charset_collate";
  
      //IF NOT EXISTS
    $sql_ferien_init = "CREATE TABLE " . db_ferien . " (
      `FID` INT UNSIGNED NOT NULL AUTO_INCREMENT,
      `LABEL` TINYTEXT NULL,
      `STARTDATE` DATE NULL,
      `ENDDATE` DATE NULL,
      PRIMARY KEY (`FID`)) $charset_collate";
    
    $sql_ferientermine_init = "CREATE TABLE " . db_ferientermine . " (
      `ID` INT UNSIGNED NOT NULL AUTO_INCREMENT,
      `TEMPLATE` INT UNSIGNED NOT NULL,
      `SHORTCODE` TINYTEXT NULL,
      `FERIEN` INT UNSIGNED NOT NULL,
      `DATESTART` DATETIME NULL,
      `DATEEND` DATETIME NULL,
      `MAX_PARTICIPANTS` INT NULL,
      `PARTICIPANTS` INT NULL,
      `IS_OPEN_END` TINYINT NULL DEFAULT 0,
      `IS_CANCELLED` TINYINT NULL DEFAULT 0,
      `CALENDAR_EVENT_ID` TINYTEXT NULL,
      PRIMARY KEY (`ID`),
      INDEX `ID_idx` (`TEMPLATE` ASC),
      INDEX `ID_idx1` (`FERIEN` ASC),
      CONSTRAINT `ID`
        FOREIGN KEY (`TEMPLATE`)
        REFERENCES `" . db_ferientemplates . "` (`ID`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
      CONSTRAINT `FID`
        FOREIGN KEY (`FERIEN`)
        REFERENCES `" . db_ferien . "` (`FID`)
        ON DELETE CASCADE
        ON UPDATE CASCADE) $charset_collate";
  
    $initut = "CREATE TABLE $utname ( ID INT UNSIGNED NOT NULL AUTO_INCREMENT, TITEL VARCHAR(50), TYP TINYINT, TAG TINYINT, ZEITVON TIME, ZEITBIS TIME, STD_MAX_KINDER TINYINT, STD_KINDER TINYINT, OVR_DATUM DATE, OVR_KINDER TINYINT, PRIMARY KEY  (ID)) $charset_collate;";
    $initpf = "CREATE TABLE $pfname ( ID INT UNSIGNED NOT NULL AUTO_INCREMENT, NAME VARCHAR(50), LEVEL TINYINT, LINKURL VARCHAR(99), GEBURT DATE, PRIMARY KEY  (ID)) $charset_collate;";
  
  
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta( $sql_lessontemplates_init );
    dbDelta( $sql_lessons_init );
    dbDelta( $sql_ferientemplates_init );
    dbDelta( $sql_ferien_init );
    dbDelta( $sql_ferientermine_init );
  
    //TODO: Insert default Ferien
    if($wpdb->get_row("SELECT FID FROM " . db_ferien . " WHERE FID = 1") == null) {
      $dbData = array( 'FID' => 1, 'LABEL' => 'default', 'STARTDATE' => '1970-01-01', 'ENDDATE' => '2099-01-01');
      $dbType = array('%d', '%s', '%s', '%s');
      if($wpdb->insert(db_ferien, $dbData, $dbType) == FALSE) {
        error_log("[ENABLE] Failed to create default Ferien!");
      }
    } else {
      error_log("[ENABLE] default Ferien found!");
    }
    
    //dbDelta( $initut );
    //dbDelta( $initpf );
  
    add_option( 'mb_db_version', $mb_db_version );
  }
?>