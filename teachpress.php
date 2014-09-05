<?php
/*
Plugin Name: teachPress
Plugin URI: http://mtrv.wordpress.com/teachpress/
Description: With teachPress you can easy manage courses, enrollments and publications.
Version: 5.0.0alpha
Author: Michael Winkler
Author URI: http://mtrv.wordpress.com/
Min WP Version: 3.9
Max WP Version: 4.0
*/

/*
   LICENCE
 
    Copyright 2008-2014 Michael Winkler

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/*************/
/* Constants */
/*************/

/*
 * If you want, you can owerwrite this parameters in your wpconfig.php.
 */

global $wpdb;

if ( !defined('TEACHPRESS_ARTEFACTS') ) {
    /**
     * This constants defines the table name for teachpress_artefacts.
     * @since 5.0.0
    */
    define('TEACHPRESS_ARTEFACTS', $wpdb->prefix . 'teachpress_artefacts');}

if ( !defined('TEACHPRESS_ASSESSMENTS') ) {
    /**
     * This constants defines the table name for teachpress_assessments.
     * @since 5.0.0
    */
    define('TEACHPRESS_ASSESSMENTS', $wpdb->prefix . 'teachpress_assessments');}

if ( !defined('TEACHPRESS_STUD') ) {
    /**
     * This constants defines the table name for teachpress_stud.
     * @since 5.0.0
    */
    define('TEACHPRESS_STUD', $wpdb->prefix . 'teachpress_stud');}

if ( !defined('TEACHPRESS_STUD_META') ) {
    /**
     * This constants defines the table name for teachpress_stud_meta.
     * @since 5.0.0
    */
    define('TEACHPRESS_STUD_META', $wpdb->prefix . 'teachpress_stud_meta');}

if ( !defined('TEACHPRESS_COURSES') ) {
    /**
     * This constants defines the table name for teachpress_courses.
     * @since 5.0.0
    */
    define('TEACHPRESS_COURSES', $wpdb->prefix . 'teachpress_courses');}

if ( !defined('TEACHPRESS_COURSE_META') ) {
    /**
     * This constants defines the table name for teachpress_course_meta.
     * @since 5.0.0
    */
    define('TEACHPRESS_COURSE_META', $wpdb->prefix . 'teachpress_course_meta');}

if ( !defined('TEACHPRESS_COURSE_CAPABILITES') ) {
    /**
     * This constants defines the table name for teachpress_course_cababilites.
     * @since 5.0.0
    */
    define('TEACHPRESS_COURSE_CAPABILITES', $wpdb->prefix . 'teachpress_course_capabilites');}

if ( !defined('TEACHPRESS_COURSE_DOCUMENTS') ) {
    /**
     * This constants defines the table name for teachpress_course_documents.
     * @since 5.0.0
    */
    define('TEACHPRESS_COURSE_DOCUMENTS', $wpdb->prefix . 'teachpress_course_documents');}

if ( !defined('TEACHPRESS_SIGNUP') ) {
    /**
     * This constants defines the table name for teachpress_signups.
     * @since 5.0.0
    */
    define('TEACHPRESS_SIGNUP', $wpdb->prefix . 'teachpress_signup');}
    
if ( !defined('TEACHPRESS_SETTINGS') ) {
    /**
     * This constants defines the table name for teachpress_settings.
     * @since 5.0.0
    */
    define('TEACHPRESS_SETTINGS', $wpdb->prefix . 'teachpress_settings');}

if ( !defined('TEACHPRESS_PUB') ) {
    /**
     * This constants defines the table name for teachpress_pub.
     * @since 5.0.0
    */
    define('TEACHPRESS_PUB', $wpdb->prefix . 'teachpress_pub');}

if ( !defined('TEACHPRESS_PUB_META') ) {
    /**
     * This constants defines the table name for teachpress_pub_meta.
     * @since 5.0.0
    */
    define('TEACHPRESS_PUB_META', $wpdb->prefix . 'teachpress_pub_meta');}

if ( !defined('TEACHPRESS_TAGS') ) {
    /**
     * This constants defines the table name for teachpress_tags.
     * @since 5.0.0
    */
    define('TEACHPRESS_TAGS', $wpdb->prefix . 'teachpress_tags');}

if ( !defined('TEACHPRESS_RELATION') ) {
    /**
     * This constants defines the table name for teachpress_relation. This is the relationship tags to publications.
     * @since 5.0.0
    */
    define('TEACHPRESS_RELATION', $wpdb->prefix . 'teachpress_relation');}

if ( !defined('TEACHPRESS_USER') ) {
    /**
     * This constants defines the table name for teachpress_user. This is the relationship publications to users.
     * @since 5.0.0
    */
    define('TEACHPRESS_USER', $wpdb->prefix . 'teachpress_user');}

if ( !defined('TEACHPRESS_AUTHORS') ) {
    /**
     * This constants defines the table name for teachpress_authors.
     * @since 5.0.0
    */
    define('TEACHPRESS_AUTHORS', $wpdb->prefix . 'teachpress_authors');}

if ( !defined('TEACHPRESS_REL_PUB_AUTH') ) {
    /**
     * This constants defines the table name for teachpress_rel_pub_auth. This is the relationship publications to authors.
     * @since 5.0.0
    */
    define('TEACHPRESS_REL_PUB_AUTH', $wpdb->prefix . 'teachpress_rel_pub_auth');}

if ( !defined('TEACHPRESS_TIME_LIMIT') ) {
    /**
     * This value is used for PHP's set_time_limit(). The plugin set this value before an import or export of publications
     * @since 5.0.0
    */
    define('TEACHPRESS_TIME_LIMIT', 240);}

/*********/
/* Menus */
/*********/

/**
 * Add menu for courses and students
 * @since 0.1.0
 * @todo Remove support for WordPress <3.9 with teachPress 5.1 or later
 */
function tp_add_menu() {
    global $wp_version;
    global $tp_admin_show_courses_page;
    global $tp_admin_add_course_page;
    
    $logo = (version_compare($wp_version, '3.8', '>=')) ? plugins_url() . '/teachpress/images/logo_small.png' : plugins_url() . '/teachpress/images/logo_small_black.png';
    
    $tp_admin_show_courses_page = add_menu_page(__('Course','teachpress'), __('Course','teachpress'),'use_teachpress_courses', __FILE__, 'tp_show_courses_page', $logo);
    $tp_admin_add_course_page = add_submenu_page('teachpress/teachpress.php',__('Add new','teachpress'), __('Add new', 'teachpress'),'use_teachpress_courses','teachpress/add_course.php','tp_add_course_page');
    add_submenu_page('teachpress/teachpress.php',__('Students','teachpress'), __('Students','teachpress'),'use_teachpress_courses', 'teachpress/students.php', 'tp_students_page');
    add_action("load-$tp_admin_add_course_page", 'tp_add_course_page_help');
    add_action("load-$tp_admin_show_courses_page", 'tp_show_course_page_help');
    add_action("load-$tp_admin_show_courses_page", 'tp_show_course_page_screen_options');
}

/**
 * Add menu for publications
 * @since 0.9.0
 * @todo Remove support for WordPress <3.9 with teachPress 5.1 or later
 */
function tp_add_menu2() {
    global $wp_version;
    global $tp_admin_all_pub_page;
    global $tp_admin_your_pub_page;
    global $tp_admin_add_pub_page;
    global $tp_admin_import_page;
    global $tp_admin_show_authors_page;
    global $tp_admin_edit_tags_page;
    
    $logo = (version_compare($wp_version, '3.8', '>=')) ? plugins_url() . '/teachpress/images/logo_small.png' : plugins_url() . '/teachpress/images/logo_small_black.png';
    
    $tp_admin_all_pub_page = add_menu_page (__('Publications','teachpress'), __('Publications','teachpress'), 'use_teachpress', 'publications.php', 'tp_show_publications_page', $logo);
    $tp_admin_your_pub_page = add_submenu_page('publications.php',__('Your publications','teachpress'), __('Your publications','teachpress'),'use_teachpress','teachpress/publications.php','tp_show_publications_page');
    $tp_admin_add_pub_page = add_submenu_page('publications.php',__('Add new', 'teachpress'), __('Add new','teachpress'),'use_teachpress','teachpress/addpublications.php','tp_add_publication_page');
    $tp_admin_import_page = add_submenu_page('publications.php',__('Import/Export'), __('Import/Export'), 'use_teachpress', 'teachpress/import.php','tp_import_page');
    $tp_admin_show_authors_page = add_submenu_page('publications.php',__('Authors'),__('Authors'),'use_teachpress','teachpress/authors.php','tp_show_authors_page');
    $tp_admin_edit_tags_page = add_submenu_page('publications.php',__('Tags'),__('Tags'),'use_teachpress','teachpress/tags.php','tp_tags_page');
    
    add_action("load-$tp_admin_all_pub_page", 'tp_show_publications_page_help');
    add_action("load-$tp_admin_all_pub_page", 'tp_show_publications_page_screen_options');
    add_action("load-$tp_admin_your_pub_page", 'tp_show_publications_page_help');
    add_action("load-$tp_admin_your_pub_page", 'tp_show_publications_page_screen_options');
    add_action("load-$tp_admin_add_pub_page", 'tp_add_publication_page_help');
    add_action("load-$tp_admin_import_page", 'tp_import_page_help_tab');
    add_action("load-$tp_admin_show_authors_page", 'tp_show_authors_page_screen_options');
    add_action("load-$tp_admin_edit_tags_page", 'tp_edit_tags_page_screen_options');
}

/**
 * Add option screen
 * @since 4.2.0
 */
function tp_add_menu_settings() {
    add_options_page(__('teachPress Settings','teachpress'),'teachPress','administrator','teachpress/settings.php', 'tp_show_admin_settings');
}

/************/
/* Includes */
/************/

// Admin menus
if ( is_admin() ) {
    include_once("admin/show_courses.php");
    include_once("admin/add_course.php");
    include_once("admin/show_single_course.php");
    include_once("admin/create_lists.php");
    include_once("admin/mail.php");
    include_once("admin/show_students.php");
    include_once("admin/add_students.php");
    include_once("admin/edit_student.php");
    include_once("admin/settings.php");
    include_once("admin/show_publications.php");
    include_once("admin/add_publication.php");
    include_once("admin/edit_tags.php");
    include_once("admin/show_authors.php");
    include_once("admin/import_publications.php");
}

// Core functions
include_once("core/general.php");
include_once("core/class-ajax.php");
include_once("core/class-bibtex.php");
include_once("core/class-export.php");
include_once("core/class-mail.php");
include_once("core/admin.php");
include_once("core/database.php");
include_once("core/deprecated.php");
include_once("core/shortcodes.php");
include_once("core/enrollments.php");
include_once("core/widgets.php");

// BibTeX Parse
if ( !class_exists( 'PARSEENTRIES' ) ) {
    include_once("includes/bibtexParse/PARSEENTRIES.php");
    include_once("includes/bibtexParse/PARSECREATORS.php");
}

/*****************/
/* Mainfunctions */
/*****************/

/** 
 * Returns the current teachPress version
 * @return string
*/
function get_tp_version() {
    return '5.0.0alpha10';
}

/** Function for the integrated registration mode */
function tp_advanced_registration() {
    $user = wp_get_current_user();
    global $wpdb;
    global $current_user;
    $test = $wpdb->query("SELECT `wp_id` FROM " . TEACHPRESS_STUD . " WHERE `wp_id` = '$current_user->ID'");
    if ($test == '0' && $user->ID != '0') {
        if ($user->user_firstname == '') {
            $user->user_firstname = $user->display_name;
        }
        $data = array (
            'firstname' => $user->user_firstname,
            'lastname' => $user->user_lastname,
            'userlogin' => $user->user_login,
            'email' => $user->user_email
        );
        tp_students::add_student($user->ID, $data );
    }
} 

/*************************/
/* Installer and Updater */
/*************************/

/**
 * Database update manager
 * @since 4.2.0
 */
function tp_db_update() {
   require_once("core/class-tables.php");
   require_once('core/class-update.php');
   tp_update_db::force_update();
}

/**
 * Database synchronisation manager
 * @param string $table     authors or stud_meta
 * @since 5.0.0
 */
function tp_db_sync($table) {
    require_once("core/class-tables.php");
    require_once('core/class-update.php');
    if ( $table === 'authors' ) {
        tp_update_db::fill_table_authors();
    }
    if ( $table === 'stud_meta' ) {
        tp_update_db::fill_table_stud_meta();
    }
}

/**
 * teachPress plugin activation
 * @param boolean $network_wide
 * @since 4.0.0
 */
function tp_activation ( $network_wide ) {
    global $wpdb;
    // it's a network activation
    if ( $network_wide ) {
        $old_blog = $wpdb->blogid;
        // Get all blog ids
        $blogids = $wpdb->get_col($wpdb->prepare("SELECT `blog_id` FROM $wpdb->blogs"));
        foreach ($blogids as $blog_id) {
            switch_to_blog($blog_id);
            tp_install();
        }
        switch_to_blog($old_blog);
        return;
    } 
    // it's a normal activation
    else {
        tp_install();
    }
}

/**
 * Installation manager
 */
function tp_install() {
    require_once 'core/class-tables.php';
    tp_tables::create();
}

/**
 * Uninstallation manager
 */
function tp_uninstall() {
    require_once 'core/class-tables.php';
    tp_tables::remove();
}

/*********************/
/* Loading functions */
/*********************/

/**
 * Admin interface script loader
 */
function tp_backend_scripts() {
    // Define $page
    $page = isset($_GET['page']) ? $_GET['page'] : '';
    wp_enqueue_style('teachpress-print-css', plugins_url() . '/teachpress/styles/print.css', false, false, 'print');
    // Load scripts only, if it's a teachpress page
    if ( strpos($page, 'teachpress') !== false || strpos($page, 'publications') !== false ) {
        wp_enqueue_script('teachpress-standard', plugins_url() . '/teachpress/js/backend.js');
        wp_enqueue_style('teachpress.css', plugins_url() . '/teachpress/styles/teachpress.css');
        wp_enqueue_script('media-upload');
        add_thickbox();
        // Load jQuery + ui plugins + plupload
        wp_enqueue_script(array('jquery-ui-core', 'jquery-ui-datepicker', 'jquery-ui-resizable', 'jquery-ui-autocomplete', 'jquery-ui-sortable', 'jquery-ui-dialog', 'plupload'));
        wp_enqueue_style('teachpress-jquery-ui.css', plugins_url() . '/teachpress/styles/jquery.ui.css');
        wp_enqueue_style('teachpress-jquery-ui-dialog.css', includes_url() . '/css/jquery-ui-dialog.min.css');
        $lang = array('de_DE','it_IT','es_ES', 'sk_SK');
        if ( in_array( WPLANG , $lang) ) {
            wp_enqueue_script('teachpress-datepicker-de', plugins_url() . '/teachpress/js/datepicker/jquery.ui.datepicker-' . WPLANG . '.js');
        }
    }
}

/**
 * Frontend script loader
 */
function tp_frontend_scripts() {
    $version = get_tp_version();
    echo chr(13) . chr(10) . '<!-- teachPress -->' . chr(13) . chr(10);
    echo '<script type="text/javascript" src="' . plugins_url() . '/teachpress/js/frontend.js?ver=' . $version . '"></script>' . chr(13) . chr(10);
    $value = get_tp_option('stylesheet');
    if ($value == '1') {
        echo '<link type="text/css" href="' . plugins_url() . '/teachpress/styles/teachpress_front.css?ver=' . $version . '" rel="stylesheet" />' . chr(13) . chr(10);
    }
    echo '<!-- END teachPress -->' . chr(13) . chr(10);
}

/**
 * Load language files
 */
function tp_language_support() {
    load_plugin_textdomain('teachpress', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');
}

/**
 * Adds a link to the WordPress plugin menu
 * @param array $links
 * @param string $file
 * @return array
 */
function tp_plugin_link($links, $file){
    if ($file == plugin_basename(__FILE__)) {
        return array_merge($links, array( sprintf('<a href="options-general.php?page=teachpress/settings.php">%s</a>', __('Settings') ) ));
    }
    return $links;
}

// Register WordPress-Hooks
register_activation_hook( __FILE__, 'tp_activation');
add_action('init', 'tp_language_support');
add_action('admin_menu', 'tp_add_menu_settings');
add_action('wp_head', 'tp_frontend_scripts');
add_action('admin_init','tp_backend_scripts');
add_filter('plugin_action_links','tp_plugin_link', 10, 2);
add_action('wp_ajax_tp_document_upload', 'tp_handle_document_uploads' );

// Register course system
if ( !defined('TP_COURSE_SYSTEM') ) {
    add_action('admin_menu', 'tp_add_menu');
    add_action('widgets_init', create_function('', 'return register_widget("tp_books_widget");'));
    add_shortcode('tpdate', 'tp_date_shortcode');  // Deprecated
    add_shortcode('tpcoursedate', 'tp_coursedate_shortcode');
    add_shortcode('tpcoursedocs', 'tp_coursedocs_shortcode');
    add_shortcode('tpcourselist', 'tp_courselist_shortcode');
    add_shortcode('tpenrollments', 'tp_enrollments_shortcode');
    add_shortcode('tppost','tp_post_shortcode');
    add_shortcode('tpsearch', 'tp_search_shortcode');
}

// register publication system
if ( !defined('TP_PUBLICATION_SYSTEM') ) {
    add_action('admin_menu', 'tp_add_menu2');
    add_shortcode('tpcloud', 'tp_cloud_shortcode');
    add_shortcode('tplist', 'tp_list_shortcode');
    add_shortcode('tpsingle', 'tp_single_shortcode');
    add_shortcode('tpbibtex', 'tp_bibtex_shortcode');
    add_shortcode('tpabstract', 'tp_abstract_shortcode');
    add_shortcode('tplinks', 'tp_links_shortcode');
}