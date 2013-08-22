<?php
/**
 * This class contains all functions for updating a teachpress database
 */
class tp_update_db {
    
    public static function force_update () {
        global $wpdb;
        $db_version = get_tp_option('db-version');
        $software_version = get_tp_version();
        $update_level = '0';
        
        // if is the current one
        if ($db_version === $software_version) {
            get_tp_message( __('An update is not necessary.','teachpress') );
            return;
        }
        
        // charset & collate like WordPress
        $charset_collate = '';
        if ( version_compare(mysql_get_server_info(), '4.1.0', '>=') ) {
            $charset_collate = ( !empty($wpdb->charset) ) ? "CHARACTER SET $wpdb->charset" : "CHARACTER SET utf8";
            if ( ! empty($wpdb->collate) ) {
                $charset_collate .= " COLLATE $wpdb->collate";
            }
            else {
                $charset_collate .= " COLLATE utf8_general_ci";
            }
        }
        
        // set capabilities
        global $wp_roles;
        $role = $wp_roles->get_role('administrator');
        if ( !$role->has_cap('use_teachpress') ) {
            $wp_roles->add_cap('administrator', 'use_teachpress');
        }
        
        // force updates to reach structure of teachPress 2.0.0
        if ( $db_version[0] === '0' || $db_version[0] === '1' ) {
            tp_update_db::upgrade_table_teachpress_ver($charset_collate);
            tp_update_db::upgrade_table_teachpress_beziehung($charset_collate);
            tp_update_db::upgrade_table_teachpress_kursbelegung($charset_collate);
            tp_update_db::upgrade_table_teachpress_einstellungen($charset_collate);
            tp_update_db::upgrade_table_teachpress_stud_to_20($charset_collate);
            tp_update_db::upgrade_table_teachpress_pub_to_04($charset_collate);
            tp_update_db::upgrade_table_teachpress_pub_to_20($charset_collate);
            $update_level = '2';
        }
        
        // force updates to reach structure of teachPress 3.0.0
        if ( $db_version[0] === '2' || $update_level === '2' ) {
            tp_update_db::upgrade_to_30();
            $update_level = '3';
        }
        // force updates to reach structure of teachPress 3.1.0
        if ( $db_version[0] === '3' || $update_level === '3' ) {
            tp_update_db::upgrade_to_31($charset_collate);
            $update_level = '4';
        }
        // force updates to reach structure of teachPress 4.2.0
        if ( $db_version[0] === '4' || $update_level === '4' ) {
            tp_update_db::upgrade_to_40($charset_collate);
            tp_update_db::upgrade_to_41();
            tp_update_db::upgrade_to_42($charset_collate);
        }
        tp_update_db::add_options();
        tp_update_db::finalize_update($software_version);
   }

   /**
    * Replace the old table "teachpress_ver" with "teachpress_courses" and copy all data
    * @global class $wpdb
    * @global string $teachpress_courses
    * @param string $charset_collate
    * @since 4.2.0
    */
   private static function upgrade_table_teachpress_ver ($charset_collate) {
        global $wpdb;
        global $teachpress_courses;
        $teachpress_ver = $wpdb->prefix . 'teachpress_ver';

        if ($wpdb->query("SHOW COLUMNS FROM $teachpress_ver LIKE 'veranstaltungs_id'") == '1') {
             // create new table teachpress_courses
             if($wpdb->get_var("SHOW TABLES LIKE '$teachpress_courses'") != $teachpress_courses) {
                 $sql = "CREATE TABLE $teachpress_courses ( `course_id` INT UNSIGNED AUTO_INCREMENT, `name` VARCHAR(100), `type` VARCHAR(100), `room` VARCHAR(100), `lecturer` VARCHAR (100), `date` VARCHAR(60), `places` INT(4), `start` DATETIME, `end` DATETIME, `semester` VARCHAR(100), `comment` VARCHAR(500), `rel_page` INT, `parent` INT, `visible` INT(1), `waitinglist` INT(1), `image_url` VARCHAR(400), `strict_signup` INT(1), PRIMARY KEY (course_id)
                 ) $charset_collate;";			
                 $wpdb->query($sql);
             }
             // copy all data
             $row = $wpdb->get_results("SELECT * FROM $teachpress_ver");
             foreach ($row as $row) {
                 $sql = "INSERT INTO $teachpress_courses (`course_id`, `name`, `type`, `room`, `lecturer`, `date`, `places`, `start`, `end`, `semester`, `comment`, `rel_page`, `parent`, `visible`, `waitinglist`) VALUES('$row->veranstaltungs_id', '$row->name', '$row->vtyp', '$row->raum', '$row->dozent', '$row->termin', '$row->plaetze', '$row->startein', '$row->endein', '$row->semester', '$row->bemerkungen', '$row->rel_page', '$row->parent', '$row->sichtbar', '$row->warteliste')";
                 $wpdb->query($sql);
             }
             // delete old table
             $wpdb->query("DROP TABLE $teachpress_ver");
        }
    }
    
    /**
     * Replace the old table "teachpress_beziehung" with "teachpress_relation" and copy all data
     * @global class $wpdb
     * @global string $teachpress_pub
     * @global string $teachpress_tags
     * @global string $teachpress_relation
     * @param string $charset_collate
     * @since 4.2.0
     */
    private static function upgrade_table_teachpress_beziehung ($charset_collate) {
        global $wpdb;
        global $teachpress_pub;
        global $teachpress_tags;
        global $teachpress_relation;
        $teachpress_beziehung = $wpdb->prefix . 'teachpress_beziehung';
        if ($wpdb->query("SHOW COLUMNS FROM $teachpress_beziehung LIKE 'belegungs_id'") == '1') {
            // create new table teachpress_relation
            if($wpdb->get_var("SHOW TABLES LIKE '$teachpress_relation'") != $teachpress_relation) {
                $sql = "CREATE TABLE $teachpress_relation ( `con_id` INT UNSIGNED AUTO_INCREMENT, `pub_id` INT UNSIGNED, `tag_id` INT UNSIGNED, FOREIGN KEY (pub_id) REFERENCES $teachpress_pub (pub_id), FOREIGN KEY (tag_id) REFERENCES $teachpress_tags (tag_id), PRIMARY KEY (con_id) ) $charset_collate;";
                $wpdb->query($sql);
            }
            // copy all data
            $row = $wpdb->get_results("SELECT * FROM $teachpress_beziehung");
            foreach ($row as $row) {
                $sql = "INSERT INTO $teachpress_relation (`con_id`, `pub_id`, `tag_id`) VALUES('$row->belegungs_id', '$row->pub_id', '$row->tag_id')";
                $wpdb->query($sql);
            }
            // delete old table
            $wpdb->query("DROP TABLE $teachpress_beziehung");
        }
    }
    
    /**
     * Replace the old table "teachpress_kursbelegung" with "teachpress_signup" and copy all data
     * @global class $wpdb
     * @global string $teachpress_signup
     * @global string $teachpress_courses
     * @param string $charset_collate
     * @since 4.2.0
     */
    private static function upgrade_table_teachpress_kursbelegung ($charset_collate) {
        global $wpdb;
        global $teachpress_signup;
        global $teachpress_courses;
        global $teachpress_stud;
        $teachpress_kursbelegung = $wpdb->prefix . 'teachpress_kursbelegung';
        if ($wpdb->query("SHOW COLUMNS FROM $teachpress_kursbelegung LIKE 'belegungs_id'") == '1') {
            // create new table teachpress_signup
            if($wpdb->get_var("SHOW TABLES LIKE '$teachpress_signup'") != $teachpress_signup) {
                $sql = "CREATE TABLE $teachpress_signup (`con_id` INT UNSIGNED AUTO_INCREMENT, `course_id` INT UNSIGNED, `wp_id` INT UNSIGNED, `waitinglist` INT(1) UNSIGNED, `date` DATETIME, FOREIGN KEY (course_id) REFERENCES $teachpress_courses (course_id), FOREIGN KEY (wp_id) REFERENCES $teachpress_stud (wp_id), PRIMARY KEY (con_id) ) $charset_collate;";
                $wpdb->query($sql);
            }
            // copy all data
            $row = $wpdb->get_results("SELECT * FROM $teachpress_kursbelegung");
            foreach ($row as $row) {
                $sql = "INSERT INTO $teachpress_signup (`con_id`, `course_id`, `wp_id`, `waitinglist`, `date`) VALUES('$row->belegungs_id', '$row->veranstaltungs_id', '$row->wp_id', '$row->warteliste', '$row->datum')";
                $wpdb->query($sql);
            }
            // delete old table
            $wpdb->query("DROP TABLE $teachpress_kursbelegung");
        }
    }
    
    /**
     * Replace the old table "teachpress_einstellungen" with "teachpress_settings" and copy all data
     * @global class $wpdb
     * @global string $teachpress_settings
     * @param string $charset_collate
     * @since 4.2.0
     */
    private static function upgrade_table_teachpress_einstellungen ($charset_collate) {
        global $wpdb;
        global $teachpress_settings;
        $teachpress_einstellungen = $wpdb->prefix . 'teachpress_einstellungen';
        if ($wpdb->query("SHOW COLUMNS FROM $teachpress_einstellungen LIKE 'einstellungs_id'") == '1') {
            // create new table teachpress_settings
            if($wpdb->get_var("SHOW TABLES LIKE '$teachpress_settings'") != $teachpress_settings) {
                $sql = "CREATE TABLE $teachpress_settings ( `setting_id` INT UNSIGNED AUTO_INCREMENT, `variable` VARCHAR (100), `value` VARCHAR (400), `category` VARCHAR (100), PRIMARY KEY (setting_id) ) $charset_collate;";				
                $wpdb->query($sql);
            }
            // copy all data
            $row = $wpdb->get_results("SELECT * FROM $teachpress_einstellungen");
            foreach ($row as $row) {
                if ($row->category == 'studiengang') {
                    $row->category = 'course_of_studies';
                }
                if ($row->category == 'veranstaltungstyp') {
                    $row->category = 'course_type';
                }
                $sql = "INSERT INTO $teachpress_settings (`setting_id`, `variable`, `value`, `category`) VALUES('$row->einstellungs_id', '$row->variable', '$row->wert', '$row->category')";
                $wpdb->query($sql);
            }
            // delete old table
            $wpdb->query("DROP TABLE $teachpress_einstellungen");
        }
    }
    
    /**
     * Upgrade table "teachPress_stud" to teachPress 2.x structure
     * @global class $wpdb
     * @global string $teachpress_stud
     * @param string $charset_collate
     */
    private static function upgrade_table_teachpress_stud_to_20 ($charset_collate) {
        global $wpdb;
        global $teachpress_stud;
        // rename column vorname to firstname
        if ($wpdb->query("SHOW COLUMNS FROM $teachpress_stud LIKE 'vorname'") == '1') {
            $wpdb->query("ALTER TABLE $teachpress_stud CHANGE `vorname` `firstname` VARCHAR( 100 ) $charset_collate NULL DEFAULT NULL");
        }
        // rename column nachname to lastname
        if ($wpdb->query("SHOW COLUMNS FROM $teachpress_stud LIKE 'nachname'") == '1') {
            $wpdb->query("ALTER TABLE $teachpress_stud CHANGE `nachname` `lastname` VARCHAR( 100 ) $charset_collate NULL DEFAULT NULL");
        }
        // rename column studiengang to course_of_studies
        if ($wpdb->query("SHOW COLUMNS FROM $teachpress_stud LIKE 'studiengang'") == '1') {
            $wpdb->query("ALTER TABLE $teachpress_stud CHANGE `studiengang` `course_of_studies` VARCHAR( 100 ) $charset_collate NULL DEFAULT NULL");
        }
        // rename column urzkurz to userlogin
        if ($wpdb->query("SHOW COLUMNS FROM $teachpress_stud LIKE 'urzkurz'") == '1') {
            $wpdb->query("ALTER TABLE $teachpress_stud CHANGE `urzkurz` `userlogin` VARCHAR( 100 ) $charset_collate NULL DEFAULT NULL");
        }
        // rename column gebdat to birthday
        if ($wpdb->query("SHOW COLUMNS FROM $teachpress_stud LIKE 'gebdat'") == '1') {
            $wpdb->query("ALTER TABLE $teachpress_stud CHANGE `gebdat` `birthday` DATE $charset_collate NULL DEFAULT NULL");
        }
        // rename column fachsemester to semesternumber
        if ($wpdb->query("SHOW COLUMNS FROM $teachpress_stud LIKE 'fachsemester'") == '1') {
            $wpdb->query("ALTER TABLE $teachpress_stud CHANGE `fachsemester` `semesternumber` INT(2) NULL DEFAULT NULL");
        }
        // rename column matrikel to matriculation_number
        if ($wpdb->query("SHOW COLUMNS FROM $teachpress_stud LIKE 'matrikel'") == '1') {
            $wpdb->query("ALTER TABLE $teachpress_stud CHANGE `matrikel` `matriculation_number` INT NULL DEFAULT NULL");
        }
    }
    
    /**
     * Upgrade table "teachPress_pub" to teachPress 0.40 structure
     * @global class $wpdb
     * @global string $teachpress_pub
     * @since 4.2.0
     */
    private static function upgrade_table_teachpress_pub_to_04 ($charset_collate) {
        global $wpdb;
        global $teachpress_pub;
        // add column image_url
        if ( $wpdb->query("SHOW COLUMNS FROM $teachpress_pub LIKE 'image_url'") == '0' ) { 
            $wpdb->query("ALTER TABLE $teachpress_pub ADD `image_url` VARCHAR(200) $charset_collate NULL DEFAULT NULL AFTER `comment`");
        }
        // add colum rel_page
        if ( $wpdb->query("SHOW COLUMNS FROM $teachpress_pub LIKE 'rel_page'") == '0' ) {
            $wpdb->query("ALTER TABLE $teachpress_pub ADD `rel_page` INT NULL AFTER `image_url`");
        }
        // add column is_isbn
        if ( $wpdb->query("SHOW COLUMNS FROM $teachpress_pub LIKE 'is_isbn'") == '0' ) {
            $wpdb->query("ALTER TABLE $teachpress_pub ADD `is_isbn` INT(1) NULL DEFAULT NULL AFTER `rel_page`");
        }
    }
    
    /**
     * Upgrade table "teachPress_pub" to teachPress 2.x structure
     * @global class $wpdb
     * @global string $teachpress_pub
     * @param type $charset_collate
     * @since 4.2.0
     */
    private static function upgrade_table_teachpress_pub_to_20 ($charset_collate) {
        global $wpdb;
        global $teachpress_pub;
        // Rename sort to date
        if ( $wpdb->query("SHOW COLUMNS FROM $teachpress_pub LIKE 'sort'") == '1' ) {
            $wpdb->query("ALTER TABLE $teachpress_pub CHANGE  `sort`  `date` DATE NULL DEFAULT NULL");
        }
        // Rename typ to type
        if ( $wpdb->query("SHOW COLUMNS FROM $teachpress_pub LIKE 'typ'") == '1' ) {
            $wpdb->query("ALTER TABLE $teachpress_pub CHANGE `typ`  `type` VARCHAR( 50 ) $charset_collate NULL DEFAULT NULL");
            // remane publication types
            $row = $wpdb->get_results("SELECT pub_id, type  FROM $teachpress_pub");
            foreach ($row as $row) {
                if ($row->type === 'Buch') {
                    $wpdb->query("UPDATE $teachpress_pub SET type = 'book' WHERE pub_id = '$row->pub_id'");
                }
                if ($row->type === 'Chapter in book') {
                    $wpdb->query("UPDATE $teachpress_pub SET type = 'inbook' WHERE pub_id = '$row->pub_id'");
                }
                if ($row->type === 'Conference paper') {
                    $wpdb->query("UPDATE $teachpress_pub SET type = 'proceedings' WHERE pub_id = '$row->pub_id'");
                }
                if ($row->type === 'Journal article') {
                    $wpdb->query("UPDATE $teachpress_pub SET type = 'article' WHERE pub_id = '$row->pub_id'");
                }
                if ($row->type === 'Vortrag') {
                    $wpdb->query("UPDATE $teachpress_pub SET type = 'presentation' WHERE pub_id = '$row->pub_id'");
                }
                if ($row->type === 'Bericht') {
                    $wpdb->query("UPDATE $teachpress_pub SET type = 'techreport' WHERE pub_id = '$row->pub_id'");
                }
                if ($row->type === 'Sonstiges') {
                    $wpdb->query("UPDATE $teachpress_pub SET type = 'misc' WHERE pub_id = '$row->pub_id'");
                }
            }
        }
        // Rename autor to author
        if ( $wpdb->query("SHOW COLUMNS FROM $teachpress_pub LIKE 'autor'") == '1' ) {
            $wpdb->query("ALTER TABLE $teachpress_pub CHANGE `autor` `author` VARCHAR( 500 ) $charset_collate NULL DEFAULT NULL");
        }
        // Drop column jahr
        if ( $wpdb->query("SHOW COLUMNS FROM $teachpress_pub LIKE 'jahr'") == '1' ) {
            $wpdb->query("ALTER TABLE $teachpress_pub DROP `jahr`");
        }
        // insert column bibtex
        if ( $wpdb->query("SHOW COLUMNS FROM $teachpress_pub LIKE 'bibtex'") == '0' ) {
            $wpdb->query("ALTER TABLE $teachpress_pub ADD `bibtex` VARCHAR(50) $charset_collate NULL DEFAULT NULL AFTER `type`");
        }
        // insert column editor
        if ( $wpdb->query("SHOW COLUMNS FROM $teachpress_pub LIKE 'editor'") == '0' ) {
            $wpdb->query("ALTER TABLE $teachpress_pub ADD `editor` VARCHAR(500) $charset_collate NULL DEFAULT NULL AFTER `author`");
        }
        // insert column booktitle
        if ( $wpdb->query("SHOW COLUMNS FROM $teachpress_pub LIKE 'booktitle'") == '0' ) {
            $wpdb->query("ALTER TABLE $teachpress_pub ADD `booktitle` VARCHAR(200) $charset_collate NULL DEFAULT NULL AFTER `date`");
        }
        // insert column journal
        if ( $wpdb->query("SHOW COLUMNS FROM $teachpress_pub LIKE 'journal'") == '0' ) {
            $wpdb->query("ALTER TABLE $teachpress_pub ADD `journal` VARCHAR(200) $charset_collate NULL DEFAULT NULL AFTER `booktitle`");
        }
        // insert column volume
        if ( $wpdb->query("SHOW COLUMNS FROM $teachpress_pub LIKE 'volume'") == '0' ) {
            $wpdb->query("ALTER TABLE $teachpress_pub ADD `volume` VARCHAR(20) $charset_collate NULL DEFAULT NULL AFTER `journal`");
        }
        // insert column number
        if ( $wpdb->query("SHOW COLUMNS FROM $teachpress_pub LIKE 'number'") == '0' ) {
            $wpdb->query("ALTER TABLE $teachpress_pub ADD `number` VARCHAR(20) $charset_collate NULL DEFAULT NULL AFTER `volume`");
        }
        // insert column pages
        if ( $wpdb->query("SHOW COLUMNS FROM $teachpress_pub LIKE 'pages'") == '0' ) {
            $wpdb->query("ALTER TABLE $teachpress_pub ADD `pages` VARCHAR(20) $charset_collate NULL DEFAULT NULL AFTER `number`");
        }
        // insert column publisher
        if ( $wpdb->query("SHOW COLUMNS FROM $teachpress_pub LIKE 'publisher'") == '0' ) {
            $wpdb->query("ALTER TABLE $teachpress_pub ADD `publisher` VARCHAR(200) $charset_collate NULL DEFAULT NULL AFTER `pages`");
        }
        // insert column address
        if ( $wpdb->query("SHOW COLUMNS FROM $teachpress_pub LIKE 'address'") == '0' ) {
            $wpdb->query("ALTER TABLE $teachpress_pub ADD `address` VARCHAR(300) $charset_collate NULL DEFAULT NULL AFTER `publisher`");
        }
        // insert column edition
        if ( $wpdb->query("SHOW COLUMNS FROM $teachpress_pub LIKE 'edition'") == '0' ) {
            $wpdb->query("ALTER TABLE $teachpress_pub ADD `edition` VARCHAR(100) $charset_collate NULL DEFAULT NULL AFTER `address`");
        }
        // insert column chapter
        if ( $wpdb->query("SHOW COLUMNS FROM $teachpress_pub LIKE 'chapter'") == '0' ) {
            $wpdb->query("ALTER TABLE $teachpress_pub ADD `chapter` VARCHAR(20) $charset_collate NULL DEFAULT NULL AFTER `edition`");
        }
        // insert column institution
        if ( $wpdb->query("SHOW COLUMNS FROM $teachpress_pub LIKE 'institution'") == '0' ) {
            $wpdb->query("ALTER TABLE $teachpress_pub ADD `institution` VARCHAR(200) $charset_collate NULL DEFAULT NULL AFTER `chapter`");
        }
        // insert column organization
        if ( $wpdb->query("SHOW COLUMNS FROM $teachpress_pub LIKE 'organization'") == '0' ) {
            $wpdb->query("ALTER TABLE $teachpress_pub ADD `organization` VARCHAR(200) $charset_collate NULL DEFAULT NULL AFTER `institution`");
        }
        // insert column school
        if ( $wpdb->query("SHOW COLUMNS FROM $teachpress_pub LIKE 'school'") == '0' ) {
            $wpdb->query("ALTER TABLE $teachpress_pub ADD `school` VARCHAR(200) $charset_collate NULL DEFAULT NULL AFTER `organization`");
        }
        // insert column series
        if ( $wpdb->query("SHOW COLUMNS FROM $teachpress_pub LIKE 'series'") == '0' ) {
            $wpdb->query("ALTER TABLE $teachpress_pub ADD `series` VARCHAR(200) $charset_collate NULL DEFAULT NULL AFTER `school`");
        }
        // insert column crossref
        if ( $wpdb->query("SHOW COLUMNS FROM $teachpress_pub LIKE 'crossref'") == '0' ) {
            $wpdb->query("ALTER TABLE $teachpress_pub ADD `crossref` VARCHAR(100) $charset_collate NULL DEFAULT NULL AFTER `series`");
        }
        // insert column abstract
        if ( $wpdb->query("SHOW COLUMNS FROM $teachpress_pub LIKE 'abstract'") == '0' ) {
            $wpdb->query("ALTER TABLE $teachpress_pub ADD `abstract` TEXT $charset_collate NULL DEFAULT NULL AFTER `crossref`");
        }
        // insert column howpublished
        if ( $wpdb->query("SHOW COLUMNS FROM $teachpress_pub LIKE 'howpublished'") == '0' ) {
            $wpdb->query("ALTER TABLE $teachpress_pub ADD `howpublished` VARCHAR(200) $charset_collate NULL DEFAULT NULL AFTER `abstract`");
        }
        // insert column key
        if ( $wpdb->query("SHOW COLUMNS FROM $teachpress_pub LIKE 'key'") == '0' ) {
            $wpdb->query("ALTER TABLE $teachpress_pub ADD `key` VARCHAR(100) $charset_collate NULL DEFAULT NULL AFTER `howpublished`");
        }
        // insert column techtype
        if ( $wpdb->query("SHOW COLUMNS FROM $teachpress_pub LIKE 'techtype'") == '0' ) {
            $wpdb->query("ALTER TABLE $teachpress_pub ADD `techtype` VARCHAR(200) $charset_collate NULL DEFAULT NULL AFTER `key`");
        }
        // insert column note
        if ( $wpdb->query("SHOW COLUMNS FROM $teachpress_pub LIKE 'note'") == '0' ) {
            $wpdb->query("ALTER TABLE $teachpress_pub ADD `note` TEXT $charset_collate NULL DEFAULT NULL AFTER `comment`");
        }
        // drop column verlag
        if ( $wpdb->query("SHOW COLUMNS FROM $teachpress_pub LIKE 'verlag'") == '1') {
            $row = $wpdb->get_results("SELECT pub_id, verlag  FROM $teachpress_pub");
            foreach ($row as $row) {
                $wpdb->query("UPDATE $teachpress_pub SET `publisher` = '$row->verlag' WHERE `pub_id` = '$row->pub_id'");
            }
            $wpdb->query("ALTER TABLE $teachpress_pub DROP `verlag`");
        }
    }
    
    /**
     * Upgrade table "teachpress_courses" to teachPress 3.0 structure
     * @global class $wpdb
     * @global string $teachpress_courses
     * @since 4.2.0
     */
    private static function upgrade_to_30 () {
        global $wpdb;
        global $teachpress_courses;
        global $teachpress_signup;
        
        // teachpress_courses
        // change type in column start
        $wpdb->get_results("SELECT `start` FROM $teachpress_courses");
        if ($wpdb->get_col_info('type', 0) == 'date') {
            $wpdb->query("ALTER TABLE `$teachpress_courses` CHANGE  `start`  `start` DATETIME NULL DEFAULT NULL");
        }
        // change type in column end
        $wpdb->get_results("SELECT `end` FROM $teachpress_courses");
        if ($wpdb->get_col_info('type', 0) == 'date') {
            $wpdb->query("ALTER TABLE `$teachpress_courses` CHANGE  `end`  `end` DATETIME NULL DEFAULT NULL");
        }
        // insert column strict_signup
        if ($wpdb->query("SHOW COLUMNS FROM $teachpress_courses LIKE 'strict_signup'") == '0') {
            $wpdb->query("ALTER TABLE $teachpress_courses ADD `strict_signup` INT( 1 ) NULL DEFAULT NULL");
        }
        
        // teachpress_signup
        // Change type in column date
        $wpdb->get_results("SELECT `date` FROM $teachpress_signup");
        if ($wpdb->get_col_info('type', 0) == 'date') {
            $wpdb->query("ALTER TABLE `$teachpress_signup` CHANGE `date` `date` DATETIME NULL DEFAULT NULL");
        }
    }
    
    /**
     * Database upgrade to teachPress 3.1.3 structure
     * @global class $wpdb
     * @global string $teachpress_pub
     * @param string $charset_collate
     * @since 4.2.0
     */
    private static function upgrade_to_31 ($charset_collate) {
        global $wpdb;
        global $teachpress_settings;
        global $teachpress_pub;
        global $teachpress_courses;
        global $teachpress_signup;
        global $teachpress_relation;
        global $teachpress_stud;
        global $teachpress_tags;
        global $teachpress_user;
        // change type in column url
        $wpdb->get_results("SELECT `url` FROM $teachpress_pub");
        if ($wpdb->get_col_info('type', 0) == 'string') {
            $wpdb->query("ALTER TABLE `$teachpress_pub` CHANGE `url` `url` TEXT $charset_collate NULL DEFAULT NULL");
        }
        // drop table teachpress_log
        $table_name = $wpdb->prefix . 'teachpress_log';
        if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
            $wpdb->query("DROP TABLE " . $table_name . "");
        }
        // Drop column fplaces
        if ($wpdb->query("SHOW COLUMNS FROM $teachpress_courses LIKE 'fplaces'") == '1') {
            $wpdb->query("ALTER TABLE $teachpress_courses DROP `fplaces`");
        }
        // Change type in column birthday
        // Fixed a bug with the installer in teachpress versions 2.0.0 to 2.1.0
        $wpdb->get_results("SELECT `birthday` FROM $teachpress_stud");
        if ($wpdb->get_col_info('type', 0) != 'date') {
            $wpdb->query("ALTER TABLE `$teachpress_stud` CHANGE `birthday` `birthday` DATE NULL DEFAULT NULL");
        }
        // Change database engine
        $wpdb->query("ALTER TABLE $teachpress_stud ENGINE = INNODB");
        $wpdb->query("ALTER TABLE $teachpress_pub ENGINE = INNODB");
        $wpdb->query("ALTER TABLE $teachpress_settings ENGINE = INNODB");
        $wpdb->query("ALTER TABLE $teachpress_tags ENGINE = INNODB");
        $wpdb->query("ALTER TABLE $teachpress_courses ENGINE = INNODB");
        $wpdb->query("ALTER TABLE $teachpress_signup ENGINE = INNODB");
        $wpdb->query("ALTER TABLE $teachpress_relation ENGINE = INNODB");
        $wpdb->query("ALTER TABLE $teachpress_user ENGINE = INNODB");
    }
    
    /**
     * Database upgrade to teachPress 4.0.0 structure
     * @global class $wpdb
     * @global string $teachpress_pub
     * @param string $charset_collate
     * @since 4.2.0
     */
    private static function upgrade_to_40 ($charset_collate) {
        global $wpdb;
        global $teachpress_pub;
        // rename column name to title
        if ($wpdb->query("SHOW COLUMNS FROM $teachpress_pub LIKE 'name'") == '1') {
            $wpdb->query("ALTER TABLE $teachpress_pub CHANGE `name` `title` VARCHAR( 500 ) $charset_collate NULL DEFAULT NULL");
        }
        // add column urldate
        if ($wpdb->query("SHOW COLUMNS FROM $teachpress_pub LIKE 'urldate'") == '0') { 
            $wpdb->query("ALTER TABLE $teachpress_pub ADD `urldate` DATE NULL DEFAULT NULL AFTER `date`");
        }
    }
    
    /**
     * Database upgrade to teachPress 4.1.0 structure
     * @global class $wpdb
     * @global string $teachpress_pub
     * @param string $charset_collate
     * @since 4.2.0
     */
    private static function upgrade_to_41 () {
        global $wpdb;
        global $teachpress_pub;
        // add column urldate
        if ($wpdb->query("SHOW COLUMNS FROM $teachpress_pub LIKE 'issuetitle'") == '0') { 
            $wpdb->query("ALTER TABLE $teachpress_pub ADD `issuetitle` VARCHAR( 200 ) NULL DEFAULT NULL AFTER `booktitle`");
        }
    }
    
    /**
     * Database upgrade to teachPress 4.2.0 structure
     * @global class $wpdb
     * @global string $teachpress_settings
     * @param string $charset_collate
     * @since 4.2.0
     */
    private static function upgrade_to_42 ($charset_collate) {
        global $wpdb;
        global $teachpress_settings;
        // expand char limit for tp_settings::value
        if ($wpdb->query("SHOW COLUMNS FROM $teachpress_settings LIKE 'value'") == '1') {
            $wpdb->query("ALTER TABLE $teachpress_settings CHANGE `value` `value` TEXT $charset_collate NULL DEFAULT NULL");
        }
    }


    /**
     * Add possible missing options
     * @global class $wpdb
     * @global string $teachpress_settings
     * @since 4.2.0
     */
    private static function add_options () {
        global $wpdb;
        global $teachpress_settings;
        // Stylesheet
        if ($wpdb->query("SELECT value FROM $teachpress_settings WHERE variable = 'stylesheet'") == '0') {
            $wpdb->query("INSERT INTO $teachpress_settings (variable, value, category) VALUES ('stylesheet', '1', 'system')"); 
        }
        // Sign out
        if ($wpdb->query("SELECT value FROM $teachpress_settings WHERE variable = 'sign_out'") == '0') {
            $wpdb->query("INSERT INTO $teachpress_settings (variable, value, category) VALUES ('sign_out', '0', 'system')"); 
        }
        // Login
        if ($wpdb->query("SELECT value FROM $teachpress_settings WHERE variable = 'login'") == '0') {
            $wpdb->query("INSERT INTO $teachpress_settings (variable, value, category) VALUES ('login', 'std', 'system')"); 
        }
        // Registration number
        if ($wpdb->query("SELECT value FROM $teachpress_settings WHERE variable = 'regnum'") == '0') {
            $wpdb->query("INSERT INTO $teachpress_settings (variable, value, category) VALUES ('regnum', '1', 'system')"); 
        }
        // Studies
        if ($wpdb->query("SELECT value FROM $teachpress_settings WHERE variable = 'studies'") == '0') {
            $wpdb->query("INSERT INTO $teachpress_settings (variable, value, category) VALUES ('studies', '1', 'system')"); 
        }
        // Termnumber
        if ($wpdb->query("SELECT value FROM $teachpress_settings WHERE variable = 'termnumber'") == '0') {
            $wpdb->query("INSERT INTO $teachpress_settings (variable, value, category) VALUES ('termnumber', '1', 'system')");
        }
        // Birthday
        if ($wpdb->query("SELECT value FROM $teachpress_settings WHERE variable = 'birthday'") == '0') {
            $wpdb->query("INSERT INTO $teachpress_settings (variable, value, category) VALUES ('birthday', '1', 'system')"); 
        }
        // rel_page_courses
        if ($wpdb->query("SELECT value FROM $teachpress_settings WHERE variable = 'rel_page_courses'") == '0') {
            $wpdb->query("INSERT INTO $teachpress_settings (variable, value, category) VALUES ('rel_page_courses', 'page', 'system')"); 
        }
        // rel_page_publications
        if ($wpdb->query("SELECT value FROM $teachpress_settings WHERE variable = 'rel_page_publications'") == '0') {
            $wpdb->query("INSERT INTO $teachpress_settings (variable, value, category) VALUES ('rel_page_publications', 'page', 'system')"); 
        }
        
        /**** since version 4.2.0 ****/
        
        // add option value auto_post_template
        
        if ($wpdb->query("SELECT value FROM $teachpress_settings WHERE `variable` = 'auto_post_template'") == '0') {
            $value = '[tpsingle [key]]<!--more-->' . "\n\n[tpabstract]\n\n[tpbibtex]";
            $wpdb->query("INSERT INTO $teachpress_settings (`variable`, `value`, `category`) VALUES ('auto_post_template', '$value', 'system')"); 
        }
        // add option value auto_post
        if ($wpdb->query("SELECT value FROM $teachpress_settings WHERE `variable` = 'auto_post'") == '0') {
            $wpdb->query("INSERT INTO $teachpress_settings (`variable`, `value`, `category`) VALUES ('auto_post', '0', 'system')"); 
        }
        // add option value auto_post
        if ($wpdb->query("SELECT value FROM $teachpress_settings WHERE `variable` = 'auto_post_category'") == '0') {
            $wpdb->query("INSERT INTO $teachpress_settings (`variable`, `value`, `category`) VALUES ('auto_post_category', '', 'system')"); 
        }
        // add option value import_overwrite
        if ($wpdb->query("SELECT value FROM $teachpress_settings WHERE `variable` = 'import_overwrite'") == '0') {
            $wpdb->query("INSERT INTO $teachpress_settings (`variable`, `value`, `category`) VALUES ('import_overwrite', '0', 'system')"); 
        }
    }
    
    /**
     * Update version information in the database
     * @global class $wpdb
     * @global string $teachpress_settings
     * @since 4.2.0
     */
    private static function finalize_update ($version) {
        global $wpdb;
        global $teachpress_settings;
        $wpdb->query("UPDATE $teachpress_settings SET  value = '$version' WHERE variable = 'db-version'");
        get_tp_message( __('Update successful','teachpress') );
    }
}
?>    