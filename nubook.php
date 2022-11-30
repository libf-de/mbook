<?php
/***
* Plugin Name: nuBook
* Plugin URI: https://xorg.ga/
* Description: Reitbuch für Wordpress
* Version: 2.1
* Author: Fabian Schillig
* License: GNU GPL
*/

require_once 'strutils.php';
require_once 'timeutil.php';

global $wpdb;
define('db_lessontemplates', $wpdb->prefix . "nubook_lessontemplates");
define('db_lessons', $wpdb->prefix . "nubook_lessons");
define('db_ferientemplates', $wpdb->prefix . "nubook_ferientemplates");
define('db_ferientermine', $wpdb->prefix . "nubook_ferientermine");
define('db_ferien', $wpdb->prefix . "nubook_ferien");

global $nb_db_version;
$nb_db_version = '21';

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$plugin_root = trailingslashit( untrailingslashit( __DIR__ ));

foreach(glob($plugin_root . "inc/common/*.php") as $commonscript) {
  require_once($commonscript);
}

foreach(glob($plugin_root . "inc/admin/*.php") as $adminscript) {
  require_once($adminscript);
}

foreach(glob($plugin_root . "inc/frontend/*.php") as $frontendscript) {
  require_once($frontendscript);
}

foreach(glob($plugin_root . "inc/rest/*.php") as $restscript) {
  require_once($restscript);
}


/**
 * Initalizes wordpress menus
 * @return void
 */
function nb_menu() {
  add_menu_page( 'Reitbuch-Einstellungen', 'Reitbuch', 'manage_options', 'nb-options-menu', 'nb_options_ferien' );
  add_submenu_page( 'nb-options-menu', 'Ferienprogramm verwalten — nuBook', 'Ferienprogramm', 'manage_options', 'nb-options-menu', 'nb_options_ferien');
  add_submenu_page( 'nb-options-menu', 'Unterricht verwalten — nuBook', 'Unterricht', 'manage_options', 'nb-options-lessons', 'nb_options_lessons');
}

/**
 * Registers admin styles and scripts
 * @return void
 */
function nb_styles_init() {
  wp_register_style( 'fa', plugins_url('/assets/css/fontawesome.min.css',__FILE__ ) );
  wp_register_style( 'fa-solid', plugins_url('/assets/css/solid.min.css',__FILE__ ) );
  wp_register_style( 'jqueryui', plugins_url('/assets/css/jquery-ui.min.css',__FILE__ ) );
  wp_register_style( 'jqueryui-theme', plugins_url('/assets/css/jquery-ui.theme.min.css',__FILE__ ) );
  wp_register_script( 'nbadminjs', plugins_url('/assets/js/nubook.admin.js', __FILE__) );
  wp_register_script( 'jquery-ui-multidate', plugins_url('/assets/js/jquery-ui.multidatespicker.js', __FILE__), array( 'jquery', 'jquery-ui-datepicker' ) );
  
  //neo-imports
  //Common
  wp_register_style( 'nb-common-css', plugins_url('/assets/css/common.css',__FILE__ ) );


  //Unterricht
  wp_register_style( 'nb-lessons-css', plugins_url('/assets/css/nubook.lessons.css',__FILE__ ) );
  wp_register_script( 'nb-lsadd-js', plugins_url('/assets/js/nubook.lessons.add.js', __FILE__) , array( 'jquery' ) );
  wp_register_script( 'nb-lslist-js', plugins_url('/assets/js/nubook.lessons.list.js', __FILE__) , array( 'jquery', 'wp-api' ) );

  //Ferien
  wp_register_script( 'nb-ltlist-js', plugins_url('/assets/js/nubook.lessontemplate.list.js', __FILE__) , array( 'jquery', 'wp-api' ) );

  wp_register_style( 'nb-flist-css', plugins_url('/assets/css/nubook.ferien.list.css',__FILE__ ) );

  wp_register_script( 'nb-fkadd-js', plugins_url('/assets/js/nubook.ferienkurs.add.js', __FILE__) , array( 'jquery', 'wp-api' ) );
  wp_register_script( 'nb-fklist-js', plugins_url('/assets/js/nubook.ferienkurs.list.js', __FILE__) , array( 'jquery', 'wp-api' ) );
  wp_register_style( 'nb-fklist-css', plugins_url('/assets/css/nubook.ferienkurs.list.css',__FILE__ ) );

  wp_register_style( 'nb-fkadd-css', plugins_url('/assets/css/nubook.ferienkurs.add.css',__FILE__ ) );

  wp_register_style( 'nb-fkcopy-css', plugins_url('/assets/css/nubook.ferienkurs.copy.css',__FILE__ ) );

  wp_register_style( 'nb-config-css', plugins_url('/assets/css/nubook.settings.css',__FILE__ ) );

  wp_register_script( 'nb-ferien-js', plugins_url('/assets/js/nubook.ferien.js', __FILE__) , array( 'wp-api' ) );

  wp_register_script( 'nbfkjs', plugins_url('/assets/js/nubook.ferienadmin.js', __FILE__), array( 'wp-api' ) );
}

function nb_init_frontend() {
  wp_register_style( 'user', plugins_url('/assets/css/user.css',__FILE__ ) );
  wp_enqueue_style('user');


  wp_register_style( 'sc-ddtemplates', plugins_url('/assets/css/nubook.user.ddtemplates.css',__FILE__ ) );
  wp_register_script('sc-ddtemplates', plugins_url('/assets/js/nubook.user.ddtemplates.js', __FILE__) );
  wp_register_style( 'fa', plugins_url('/assets/css/fontawesome.min.css',__FILE__ ) );
  wp_register_style( 'fa-solid', plugins_url('/assets/css/solid.min.css',__FILE__ ) );
  

  wp_register_script( 'nbuserjs', plugins_url('/assets/js/nubook.user.js', __FILE__) );
  wp_register_style( 'nb-user-lessontable-css', plugins_url('/assets/css/nubook.user.lessontable.css',__FILE__ ) );
  wp_register_style( 'nb-user-lesson-css', plugins_url('/assets/css/nubook.user.lesson.css',__FILE__ ) );
  //wp_enqueue_script("jquery");
  //wp_enqueue_script('nbuserjs');
}

function nb_load_fa() {
  wp_enqueue_style('fa');
  wp_enqueue_style('fa-solid');
}



function nb_get_pfooter() {
  global $nb_db_version;
  return "<span class=\"nb-footer-text\">powered by nuBook " . $nb_db_version . " &copy; Fabian Schillig 2022</span>";
}

register_activation_hook( __FILE__, 'nb_init' );
add_action( 'admin_menu', 'nb_menu' );
add_action('admin_enqueue_scripts', 'nb_styles_init');

add_action('admin_post_nb_lt_modify', 'handle_admin_lessontemplate_modify_post');
add_action('admin_post_nb_lt_delete', 'handle_admin_lessontemplate_delete_post');

add_action('admin_post_nb_ls_add', 'handle_admin_lessons_add_post');
add_action('admin_post_nb_ls_edit', 'handle_admin_lessons_edit_post');
add_action('admin_post_nb_ls_delete', 'handle_admin_lessons_delete_post');

add_action('admin_post_print', 'handle_admin_ferien_print' );

add_action('admin_post_nb_ft_delete', 'handle_admin_ferientemplate_delete_post');
add_action('admin_post_nb_ft_modify', 'handle_admin_ferientemplate_modify_post');

add_action('admin_post_nb_fe_modify', 'handle_admin_ferien_modify_post');
add_action('admin_post_nb_fe_standard', 'handle_admin_ferien_standard_post');
add_action('admin_post_nb_fe_active', 'handle_admin_ferien_active_post');
add_action('admin_post_nb_fe_delete', 'handle_admin_ferien_delete_post');
add_action('admin_post_nb_fe_import', 'handle_admin_ferien_import_post');

add_action('admin_post_nb_fk_add', 'handle_admin_ferienkurs_add_post');
add_action('admin_post_nb_fk_edit', 'handle_admin_ferienkurs_edit_post');
add_action('admin_post_nb_fk_delete', 'handle_admin_ferienkurs_delete_post');
add_action('admin_post_nb_fk_query', 'handle_admin_get_occupation_for_month');
add_action('admin_post_nb_fk_clean', 'handle_admin_ferienkurs_clean_post');
add_action('admin_post_nb_fk_copy', 'handle_admin_ferienkurs_copy_post');

add_action('admin_post_nb_cf_modify', 'handle_admin_settings_post');


add_action( 'wp_ajax_nb_get_kurse', 'handle_ajax_ferienkurs' );

add_action('wp_enqueue_scripts', 'nb_init_frontend');
add_action( 'rest_api_init', 'nb_api_init' );

add_shortcode('lesson-table', 'handle_user_lessontable');

add_shortcode('ftemplates', 'handle_user_templates');
add_shortcode('kategorietabelle', 'handle_user_categorytable');
add_shortcode('ferientabelle', 'handle_user_ferientable');
add_shortcode('stunden', 'handle_user_lesson');