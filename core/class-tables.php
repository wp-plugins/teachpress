<?php
/**
 * This file contains all functions for creating a database for teachpress
 * @package teachpress/core
 * @since 5.0.0
 */

/**
 * This class contains all functions for creating a database for teachpress
 */
class tp_tables {
    
    /**
     * Install teachPress database tables
     * @since 5.0.0
     */
    public static function create() {
        tp_tables::add_capabilites();
        
        $charset_collate = tp_tables::get_charset();
        tp_tables::add_table_courses($charset_collate);
        tp_tables::add_table_course_capabilites($charset_collate);
        tp_tables::add_table_stud($charset_collate);
        tp_tables::add_table_signup($charset_collate);
        tp_tables::add_table_artefacts($charset_collate);
        tp_tables::add_table_assessments($charset_collate);
        tp_tables::add_table_settings($charset_collate);
        tp_tables::add_default_settings();
        tp_tables::add_table_pub($charset_collate);
        tp_tables::add_table_tags($charset_collate);
        tp_tables::add_table_relation($charset_collate);
        tp_tables::add_table_user($charset_collate);
        tp_tables::add_table_authors($charset_collate);
        tp_tables::add_table_rel_pub_auth($charset_collate);
    }
    
    /**
     * Remove teachPress database tables
     * @since 5.0.0
     */
    public static function remove() {
        global $wpdb;
        $wpdb->query("SET FOREIGN_KEY_CHECKS=0");
        $wpdb->query("DROP TABLE `" . TEACHPRESS_COURSES . "`, `" . TEACHPRESS_STUD . "`, `" . TEACHPRESS_SETTINGS ."`, `" . TEACHPRESS_SIGNUP ."`, `" . TEACHPRESS_PUB . "`, `" . TEACHPRESS_TAGS . "`, `" . TEACHPRESS_USER . "`, `" . TEACHPRESS_RELATION ."`, `" . TEACHPRESS_ARTEFACTS . "`, `" . TEACHPRESS_ASSESSMENTS . "`, `" . TEACHPRESS_COURSE_CAPABILITES . "`, `" . TEACHPRESS_AUTHORS . "`, `" . TEACHPRESS_REL_PUB_AUTH . "`");
        $wpdb->query("SET FOREIGN_KEY_CHECKS=1");
    }

    /**
     * Create table teachpress_courses
     * @param string $charset_collate
     * @since 5.0.0
     */
    public static function add_table_courses($charset_collate) {
        global $wpdb;
        
        if($wpdb->get_var("SHOW TABLES LIKE '" . TEACHPRESS_COURSES . "'") == TEACHPRESS_COURSES) {
            return;
        }
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        dbDelta( "CREATE TABLE " . TEACHPRESS_COURSES . " (
                    `course_id` INT UNSIGNED AUTO_INCREMENT,
                    `name` VARCHAR(100),
                    `type` VARCHAR (100),
                    `room` VARCHAR(100),
                    `lecturer` VARCHAR (100),
                    `date` VARCHAR(60),
                    `places` INT(4),
                    `start` DATETIME,
                    `end` DATETIME,
                    `semester` VARCHAR(100),
                    `comment` VARCHAR(500),
                    `rel_page` INT,
                    `parent` INT,
                    `visible` INT(1),
                    `waitinglist` INT(1),
                    `image_url` VARCHAR(400),
                    `strict_signup` INT(1),
                    `use_capabilites` INT(1),
                    PRIMARY KEY (course_id)
                ) $charset_collate;");
    }
    
    /**
     * Create table table_course_capabilites
     * @param string $charset_collate
     * @since 5.0.0
     */
    public static function add_table_course_capabilites($charset_collate) {
        global $wpdb;
        
        if($wpdb->get_var("SHOW TABLES LIKE '" . TEACHPRESS_COURSE_CAPABILITES . "'") == TEACHPRESS_COURSE_CAPABILITES) {
            return;
        }
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        dbDelta( "CREATE TABLE " . TEACHPRESS_COURSE_CAPABILITES . " (
                    `cap_id` INT UNSIGNED AUTO_INCREMENT,
                    `wp_id` INT UNSIGNED,
                    `course_id` INT UNSIGNED,
                    `capability` VARCHAR(100),
                    FOREIGN KEY (course_id) REFERENCES " . TEACHPRESS_COURSES . " (course_id),
                    PRIMARY KEY (cap_id)
                ) $charset_collate;");
    }
    
    /**
     * Create table teachpress_stud
     * @param string $charset_collate
     * @since 5.0.0
     */
    public static function add_table_stud($charset_collate) {
        global $wpdb;
        
        if($wpdb->get_var("SHOW TABLES LIKE '" . TEACHPRESS_STUD . "'") == TEACHPRESS_STUD) {
            return;
        }
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
        dbDelta("CREATE TABLE " . TEACHPRESS_STUD . " (
                    `wp_id` INT UNSIGNED,
                    `firstname` VARCHAR(100 ,
                    `lastname` VARCHAR(100),
                    `course_of_studies` VARCHAR(100),
                    `userlogin` VARCHAR (100),
                    `birthday` DATE,
                    `email` VARCHAR(50),
                    `semesternumber` INT(2),
                    `matriculation_number` INT,
                    PRIMARY KEY (wp_id)
                ) $charset_collate;");
    }
    
    /**
     * Create table teachpress_signup
     * @param string $charset_collate
     * @since 5.0.0
     */
    public static function add_table_signup($charset_collate) {
        global $wpdb;
        
        if($wpdb->get_var("SHOW TABLES LIKE '" . TEACHPRESS_SIGNUP ."'") == TEACHPRESS_SIGNUP) {
            return;
        }
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
        dbDelta("CREATE TABLE " . TEACHPRESS_SIGNUP ." (
                    `con_id` INT UNSIGNED AUTO_INCREMENT,
                    `course_id` INT UNSIGNED,
                    `wp_id` INT UNSIGNED,
                    `waitinglist` INT(1) UNSIGNED,
                    `date` DATETIME,
                    FOREIGN KEY (course_id) REFERENCES " . TEACHPRESS_COURSES . " (course_id),
                    FOREIGN KEY (wp_id) REFERENCES " . TEACHPRESS_STUD . " (wp_id),
                    PRIMARY KEY (con_id)
                ) $charset_collate;");
    }
    
    /**
     * Create table teachpress_artefacts
     * @param string $charset_collate
     * @since 5.0.0
     */
    public static function add_table_artefacts($charset_collate) {
        global $wpdb;
        
        if($wpdb->get_var("SHOW TABLES LIKE '" . TEACHPRESS_ARTEFACTS . "'") == TEACHPRESS_ARTEFACTS) {
            return;
        }
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
        dbDelta("CREATE TABLE " . TEACHPRESS_ARTEFACTS . " (
                    `artefact_id` INT UNSIGNED AUTO_INCREMENT,
                    `parent_id` INT UNSIGNED,
                    `course_id` INT UNSIGNED,
                    `title` VARCHAR(500),
                    `scale` TEXT,
                    `passed` INT(1),
                    `max_value` VARCHAR(50),
                    FOREIGN KEY (course_id) REFERENCES " . TEACHPRESS_COURSES . " (course_id),
                    PRIMARY KEY (artefact_id)
                ) $charset_collate;");
        echo "CREATE TABLE " . TEACHPRESS_ARTEFACTS . " (
                    `artefact_id` INT UNSIGNED AUTO_INCREMENT,
                    `parent_id` INT UNSIGNED,
                    `course_id` INT UNSIGNED,
                    `title` VARCHAR(500),
                    `scale` TEXT,
                    `passed` INT(1),
                    `max_value` VARCHAR(50),
                    FOREIGN KEY (course_id) REFERENCES " . TEACHPRESS_COURSES . " (course_id),
                    PRIMARY KEY (artefact_id)
                ) $charset_collate;";
    }
    
    /**
     * Create table teachpress_assessments
     * @param string $charset_collate
     * @since 5.0.0
     */
    public static function add_table_assessments($charset_collate) {
        global $wpdb;
        
        if($wpdb->get_var("SHOW TABLES LIKE '" . TEACHPRESS_ASSESSMENTS . "'") == TEACHPRESS_ASSESSMENTS) {
            return;
        }
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
        dbDelta("CREATE TABLE " . TEACHPRESS_ASSESSMENTS . " (
                    `assessment_id` INT UNSIGNED AUTO_INCREMENT,
                    `artefact_id` INT UNSIGNED,
                    `course_id` INT UNSIGNED,
                    `wp_id` INT UNSIGNED,
                    `value` VARCHAR(50),
                    `max_value` VARCHAR(50),
                    `type` VARCHAR(50),
                    `examiner_id` INT,
                    `exam_date` DATETIME,
                    `comment` TEXT,
                    `passed` INT(1),
                    FOREIGN KEY (artefact_id) REFERENCES " . TEACHPRESS_ARTEFACTS . " (artefact_id),
                    FOREIGN KEY (course_id) REFERENCES " . TEACHPRESS_COURSES . " (course_id),
                    FOREIGN KEY (wp_id) REFERENCES " . TEACHPRESS_STUD . "(wp_id),
                    PRIMARY KEY (assessment_id)
                ) $charset_collate;");
    }
    
    /**
     * Create table teachpress_settings
     * @param string $charset_collate
     * @since 5.0.0
     */
    public static function add_table_settings($charset_collate) {
        global $wpdb;
        
        if($wpdb->get_var("SHOW TABLES LIKE '" . TEACHPRESS_SETTINGS . "'") == TEACHPRESS_SETTINGS) {
            return;
        }
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
        dbDelta("CREATE TABLE " . TEACHPRESS_SETTINGS . " (
                    `setting_id` INT UNSIGNED AUTO_INCREMENT,
                    `variable` VARCHAR (100),
                    `value` TEXT,
                    `category` VARCHAR (100),
                    PRIMARY KEY (setting_id)
                    ) $charset_collate;");
    }
    
    /**
     * Add default system settings
     * @since 5.0.0
     */
    public static function add_default_settings(){
        global $wpdb;
        $value = '[tpsingle [key]]<!--more-->' . "\n\n[tpabstract]\n\n[tplinks]\n\n[tpbibtex]";
        $version = get_tp_version();        
        
        $wpdb->query("INSERT INTO " . TEACHPRESS_SETTINGS . " (`variable`, `value`, `category`) VALUES ('sem', 'Example term', 'system')");
        $wpdb->query("INSERT INTO " . TEACHPRESS_SETTINGS . " (`variable`, `value`, `category`) VALUES ('db-version', '$version', 'system')");
        $wpdb->query("INSERT INTO " . TEACHPRESS_SETTINGS . " (`variable`, `value`, `category`) VALUES ('sign_out', '0', 'system')");
        $wpdb->query("INSERT INTO " . TEACHPRESS_SETTINGS . " (`variable`, `value`, `category`) VALUES ('login', 'std', 'system')");
        $wpdb->query("INSERT INTO " . TEACHPRESS_SETTINGS . " (`variable`, `value`, `category`) VALUES ('stylesheet', '1', 'system')");
        $wpdb->query("INSERT INTO " . TEACHPRESS_SETTINGS . " (`variable`, `value`, `category`) VALUES ('rel_page_courses', 'page', 'system')");
        $wpdb->query("INSERT INTO " . TEACHPRESS_SETTINGS . " (`variable`, `value`, `category`) VALUES ('rel_page_publications', 'page', 'system')");
        $wpdb->query("INSERT INTO " . TEACHPRESS_SETTINGS . " (`variable`, `value`, `category`) VALUES ('rel_content_auto', '0', 'system')");
        $wpdb->query("INSERT INTO " . TEACHPRESS_SETTINGS . " (`variable`, `value`, `category`) VALUES ('rel_content_template', 'page', 'system')");
        $wpdb->query("INSERT INTO " . TEACHPRESS_SETTINGS . " (`variable`, `value`, `category`) VALUES ('rel_content_category', '$value', 'system')");
        $wpdb->query("INSERT INTO " . TEACHPRESS_SETTINGS . " (`variable`, `value`, `category`) VALUES ('import_overwrite', '0', 'system')");
        // Example values
        $wpdb->query("INSERT INTO " . TEACHPRESS_SETTINGS . " (`variable`, `value`, `category`) VALUES ('Example term', 'Example term', 'semester')");
        $wpdb->query("INSERT INTO " . TEACHPRESS_SETTINGS . " (`variable`, `value`, `category`) VALUES ('Example', 'Example', 'course_of_studies')");	
        $wpdb->query("INSERT INTO " . TEACHPRESS_SETTINGS . "(`variable`, `value`, `category`) VALUES ('Lecture', 'Lecture', 'course_type')");
    }
    
    /**
     * Create table teachpress_pub
     * @param string $charset_collate
     * @since 5.0.0
     */
    public static function add_table_pub($charset_collate) {
        global $wpdb;
        
        if($wpdb->get_var("SHOW TABLES LIKE '" . TEACHPRESS_PUB . "'") == TEACHPRESS_PUB) {
            return;
        }
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
        dbDelta("CREATE TABLE " . TEACHPRESS_PUB . " (
                    `pub_id` INT UNSIGNED AUTO_INCREMENT,
                    `title` VARCHAR(500),
                    `type` VARCHAR (50),
                    `bibtex` VARCHAR (50),
                    `author` VARCHAR (500),
                    `editor` VARCHAR (500),
                    `isbn` VARCHAR (50),
                    `url` TEXT,
                    `date` DATE,
                    `urldate` DATE,
                    `booktitle` VARCHAR (200),
                    `issuetitle` VARCHAR (200),
                    `journal` VARCHAR(200),
                    `volume` VARCHAR(40),
                    `number` VARCHAR(40),
                    `pages` VARCHAR(40),
                    `publisher` VARCHAR (500),
                    `address` VARCHAR (300),
                    `edition` VARCHAR (100),
                    `chapter` VARCHAR (40),
                    `institution` VARCHAR (200),
                    `organization` VARCHAR (200),
                    `school` VARCHAR (200),
                    `series` VARCHAR (200),
                    `crossref` VARCHAR (100),
                    `abstract` TEXT,
                    `howpublished` VARCHAR (200),
                    `key` VARCHAR (100),
                    `techtype` VARCHAR (200),
                    `comment` TEXT,
                    `note` TEXT,
                    `image_url` VARCHAR(400),
                    `rel_page` INT,
                    `is_isbn` INT(1),
                    PRIMARY KEY (pub_id)
                ) $charset_collate;");
    }
    
    /**
     * Create table teachpress_tags
     * @param string $charset_collate
     * @since 5.0.0
     */
    public static function add_table_tags($charset_collate) {
        global $wpdb;
        
        if($wpdb->get_var("SHOW TABLES LIKE '" . TEACHPRESS_TAGS . "'") == TEACHPRESS_TAGS) {
            return;
        }
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
        dbDelta("CREATE TABLE " . TEACHPRESS_TAGS . " (
                    `tag_id` INT UNSIGNED AUTO_INCREMENT,
                    `name` VARCHAR(300),
                    PRIMARY KEY (tag_id)
                ) $charset_collate;");
    }
    
    /**
     * Create table teachpress_relation
     * @param string $charset_collate
     * @since 5.0.0
     */
    public static function add_table_relation($charset_collate) {
        global $wpdb;
        
        if( $wpdb->get_var("SHOW TABLES LIKE '" . TEACHPRESS_RELATION . "'") == TEACHPRESS_RELATION ) {
            return;
        }
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
        dbDelta("CREATE TABLE " . TEACHPRESS_RELATION . " (
                    `con_id` INT UNSIGNED AUTO_INCREMENT,
                    `pub_id` INT UNSIGNED,
                    `tag_id` INT UNSIGNED,
                    FOREIGN KEY (pub_id) REFERENCES " . TEACHPRESS_PUB . " (pub_id),
                    FOREIGN KEY (tag_id) REFERENCES " . TEACHPRESS_TAGS . " (tag_id ,
                    PRIMARY KEY (con_id)
                ) $charset_collate;");
    }
    
    /**
     * Create table teachpress_user
     * @param string $charset_collate
     * @since 5.0.0
     */
    public static function add_table_user($charset_collate) {
        global $wpdb;
        
        if( $wpdb->get_var("SHOW TABLES LIKE '" . TEACHPRESS_USER . "'") == TEACHPRESS_USER ) {
            return;
        }
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
        dbDelta("CREATE TABLE " . TEACHPRESS_USER . " (
                    `bookmark_id` INT UNSIGNED AUTO_INCREMENT,
                    `pub_id` INT UNSIGNED,
                    `user` INT UNSIGNED,
                    FOREIGN KEY (pub_id) REFERENCES " . TEACHPRESS_PUB . " (pub_id),
                    PRIMARY KEY (bookmark_id)
                    ) $charset_collate;");
    }
    
    /**
     * Create table teachpress_authors
     * @param string $charset_collate
     * @since 5.0.0
     */
    public static function add_table_authors($charset_collate) {
        global $wpdb;
        
        if($wpdb->get_var("SHOW TABLES LIKE '" . TEACHPRESS_AUTHORS . "'") == TEACHPRESS_AUTHORS) {
            return;
        }
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
        dbDelta("CREATE TABLE " . TEACHPRESS_AUTHORS . " (
                    `author_id` INT UNSIGNED AUTO_INCREMENT,
                    `name` VARCHAR(500),
                    PRIMARY KEY (author_id)
                ) $charset_collate;");
    }
    
    /**
     * Create table teachpress_rel_pub_auth
     * @param string $charset_collate
     * @since 5.0.0
     */
    public static function add_table_rel_pub_auth($charset_collate) {
        global $wpdb;
        
        if( $wpdb->get_var("SHOW TABLES LIKE '" . TEACHPRESS_REL_PUB_AUTH . "'") == TEACHPRESS_REL_PUB_AUTH ) {
            return;
        }
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
        dbDelta("CREATE TABLE " . TEACHPRESS_REL_PUB_AUTH . " (
                    `con_id` INT UNSIGNED AUTO_INCREMENT,
                    `pub_id` INT UNSIGNED,
                    `author_id` INT UNSIGNED,
                    `is_author` INT(1),
                    `is_editor` INT(1),
                    FOREIGN KEY (pub_id) REFERENCES " . TEACHPRESS_PUB . " (pub_id),
                    FOREIGN KEY (author_id) REFERENCES " . TEACHPRESS_AUTHORS . " (author_id),
                    PRIMARY KEY (con_id)
                ) $charset_collate;");
    }
    
    /**
     * Add capabilities
     * @since 5.0.0
     */
    private static function add_capabilites() {
        // 
        global $wp_roles;
        $role = $wp_roles->get_role('administrator');
        if ( !$role->has_cap('use_teachpress') ) {
            $wp_roles->add_cap('administrator', 'use_teachpress');
        }
        if ( !$role->has_cap('use_teachpress_courses') ) {
            $wp_roles->add_cap('administrator', 'use_teachpress_courses');
        }
    }
    
    /**
     * charset & collate like WordPress
     * @since 5.0.0
     */
    public static function get_charset() {
        global $wpdb; 
        $charset_collate = '';
        if ( version_compare(mysql_get_server_info(), '4.1.0', '>=') ) {
            if ( ! empty($wpdb->charset) ) {
                $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
            }	
            if ( ! empty($wpdb->collate) ) {
                $charset_collate .= " COLLATE $wpdb->collate";
            }
            $charset_collate .= " ENGINE = INNODB";
        }
        return $charset_collate;
    }
    
}
?>
