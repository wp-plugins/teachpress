<?php
/**
 * This file contains all functions for sql calls
 * @package teachpress\core\database
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 4.0.0
 */

/********************/
/* Settings/Options */
/********************/

/** 
 * Returns a teachPress option
 * @param string $var           --> sem, db-version, sign_out, login, regnum, studies, termnumber, birthday
 * @param string $category      --> system,... default: system
 * @return string
 * @since 1.0.0
*/
function get_tp_option($var, $category = 'system') {
    global $wpdb;
    $var = esc_sql($var);
    $category = esc_sql($category);
    $result = $wpdb->get_var("SELECT `value` FROM " . TEACHPRESS_SETTINGS . " WHERE `variable` = '$var' AND `category` = '$category'");
    return $result;
}

/**
 * Returns all settings of a category
 * @param string $category      --> category name (system, course_of_studies, course_type, semester)
 * @param string $order         --> default: setting_id DESC
 * @param string $output_type   --> default: OBJECT
 * @return object|array
 * @since 4.0.0
 */
function get_tp_options($category, $order = "`setting_id` DESC", $output_type = OBJECT) {
    global $wpdb;
    $category = esc_sql($category);
    $order = esc_sql($order);
    $result = $wpdb->get_results("SELECT * FROM " . TEACHPRESS_SETTINGS . " WHERE `category` = '$category' ORDER BY $order", $output_type);
    return $result;
}

/*******************************************************************************
 * NEW (teachPress 5.0)
 *******************************************************************************/

/**
 * Database access class for course artefacts
 * @package teachpress\core\database
 * @since 5.0.0
 */
class tp_artefacts {
    
    /**
     * Returns an artefact by ID
     * @param int $artefact_id
     * @param string $output_type
     * @return array|object
     * @since 5.0.0
     */
    public static function get_artefact ($artefact_id, $output_type = ARRAY_A) {
        global $wpdb;
        $artefact_id = intval($artefact_id);
        return $wpdb->get_results("SELECT * FROM " . TEACHPRESS_ARTEFACTS . " WHERE `artefact_id` = '$artefact_id'", $output_type);
    }
    
    /**
     * Returns artefacts
     * @param int $course_id
     * @param int $parent_id
     * @param string $output_type
     * @return array|object
     * @since 5.0.0
     */
    public static function get_artefacts ($course_id, $parent_id , $output_type = ARRAY_A) {
        global $wpdb;
        $course_id = intval($course_id);
        $parent_id = intval($parent_id);
        return $wpdb->get_results("SELECT * FROM " . TEACHPRESS_ARTEFACTS . " WHERE `course_id` = '$course_id' AND `parent_id` = '$parent_id'", $output_type);
    }
    
    /**
     * Adds a new artefact
     * @param array_a $data
     * @return int
     * @since 5.0.0
     */
    public static function add_artefact ($data) {
        global $wpdb;
        $wpdb->insert(TEACHPRESS_ARTEFACTS, array('parent_id' => $data['parent_id'], 'course_id' => $data['course_id'], 'title' => $data['title'], 'scale' => $data['scale'], 'passed' => $data['passed'], 'max_value' => $data['max_value']), array('%d', '%d', '%s', '%s', '%d', '%s'));
        return $wpdb->insert_id;
    }
    
    /**
     * Deletes an artefact
     * @param int $artefact_id
     * @since 5.0.0
     */
    public static function delete_artefact ($artefact_id) {
        global $wpdb;
        $artefact_id = intval($artefact_id);
        $wpdb->query("DELETE FROM " . TEACHPRESS_ARTEFACTS . " WHERE `artefact_id` = '$artefact_id'");
    }
    
    /**
     * Changes an artefact
     * @todo This function is not complete
     * @since x.x.x
     */
    public static function change_artefact () {

    }
}

/**
 * Database access class for course assessments
 * @package teachpress\core\database
 * @since 5.0.0
 */
class tp_assessments {
    
    /**
     * Returns an assessment by id
     * @param int $assessment_id
     * @param string $output_type
     * @return array|object
     * @since 5.0.0
     */
    public static function get_assessment ($assessment_id, $output_type = ARRAY_A) {
        global $wpdb;
        $assessment_id = intval($assessment_id);
        return $wpdb->get_row("SELECT * FROM " . TEACHPRESS_ASSESSMENTS . " WHERE `assessment_id` = '$assessment_id'", $output_type);
    }
    
    /**
     * Get assessments
     * @param int $wp_id
     * @param int $artefact_id
     * @param int $course_id
     * @param string $output_type
     * @return array|object
     * @since 5.0.0
     */
    public static function get_assessments ($wp_id, $artefact_id = '', $course_id = '', $output_type = ARRAY_A) {
        global $wpdb;
        $wp_id = intval($wp_id);
        if ( $artefact_id === '' ) {
            $artefacts = "course_id = '" . intval($course_id) . "'"; 
        }
        else {
            $artefacts = tp_db_helpers::generate_where_clause($artefact_id, "artefact_id", "OR", "=");
        }
        return $wpdb->get_results("SELECT * FROM " . TEACHPRESS_ASSESSMENTS . " WHERE `wp_id` = '$wp_id' AND $artefacts", $output_type);
    }
    
    /**
     * Add a new assessment
     * @param array_a $data
     * @return int
     * @since 5.0.0
     */
    public static function add_assessments ($data) {
        global $wpdb;
        $wpdb->insert(TEACHPRESS_ASSESSMENTS, array('wp_id' => $data['wp_id'], 'value' => $data['value'], 'max_value' => $data['max_value'], 'type' => $data['type'], 'examiner_id' => $data['examiner_id'], 'exam_date' => $data['exam_date'], 'comment' => $data['comment'], 'passed' => $data['passed']), array('%d', '%s', '%s', '%s', '%d', '%s', '%s', '%d'));
        $insert_id = $wpdb->insert_id;

        $data['artefact_id'] = $data['artefact_id'] === NULL ? "NULL" : intval($data['artefact_id']);
        $data['course_id'] = $data['course_id'] === NULL ? "NULL" : intval($data['course_id']);
        $wpdb->query("UPDATE " . TEACHPRESS_ASSESSMENTS . " SET `artefact_id` = " . $data['artefact_id'] . ", `course_id` = " . $data['course_id'] . " WHERE `assessment_id` = $insert_id");
        return $insert_id;
    }
    
   /**
    * Delete assessment
    * @param int $assessment_id
    * @since 5.0.0
    */
   public static function delete_assessment ($assessment_id) {
       global $wpdb;
       $assessment_id = intval($assessment_id);
       $wpdb->query("DELETE FROM " . TEACHPRESS_ASSESSMENTS . " WHERE `assessment_id` = '$assessment_id'");
   }
    
}

/**
 * Database access class for publication authors
 * @package teachpress\core\database
 * @since 5.0.0
 */
class tp_authors  {
   /**
    * Returns an array/object of authors/editors of publications
    * 
    * Note: If you only need a list of used tags, set group_by to true.
    * In this case you should ignore the columns con_id and pub_id from return
    * 
    * Possible values for $args:
    *  pub_id          publication IDs (separated by comma)
    *  user            user IDs (separated by comma)
    *  exclude         authors IDs you want to exclude from result (separated by comma)
    *  order           ASC or DESC; default is ASC
    *  limit           the sql search limit, example: 0,30
    *  search          a normal search string
    *  inclue_editors  boolean flag, set it to true if you want to include editors (default: false)
    *  group by        boolean flag for the group by clause (default: false)
    *  count           set it to true if you only need an number of authors which will be returned by your selection (default: false)
    *  output type     OBJECT, ARRAY_A, ARRAY_N 
    * 
    * @param array $args
    * @return array|object
    * @since 5.0.0
    */
   public static function get_authors ( $args = array() ) {
       $defaults = array(
           'pub_id' => '',
           'user' => '',
           'exclude' => '',
           'order' => 'ASC',
           'limit' => '',
           'search' => '',
           'include_editors' => false,
           'count' => false,
           'group_by' => false, 
           'output_type' => OBJECT
       ); 
       $args = wp_parse_args( $args, $defaults );
       extract( $args, EXTR_SKIP );

       global $wpdb;
       $limit = esc_sql($limit);
       $order = esc_sql($order);
       $pub_id = tp_db_helpers::generate_where_clause($pub_id, "r.pub_id", "OR", "=");
       $user = tp_db_helpers::generate_where_clause($user, "u.user", "OR", "=");
       $exclude = tp_db_helpers::generate_where_clause($exclude, "r.author_id", "AND", "!=");
       $output_type = esc_sql($output_type);
       $search = esc_sql(htmlspecialchars($search));

       // Define basics
       $select = "SELECT DISTINCT a.name, r.author_id, r.pub_id, r.con_id, r.is_author, r.is_editor FROM " . TEACHPRESS_REL_PUB_AUTH . " r INNER JOIN " . TEACHPRESS_AUTHORS . " a ON a.author_id = r.author_id";
       $join = '';
       $where = '';
       
       // define global search
       if ( $search != '' ) {
           $search = "a.name like '%$search%'";
       }
       
       // if the user needs only the number of rows
       if ( $count === true ) {
           $select = "SELECT COUNT(a.`author_id`) AS `count` FROM " . TEACHPRESS_AUTHORS . " a";
       }

       // Additional tables
       if ( $user != '' ) {
           $join .= " INNER JOIN " . TEACHPRESS_USER . " u ON u.pub_id = r.pub_id ";
       }

       // WHERE clause
       if ( $pub_id != '') {
           $where = ( $where != '' ) ? $where . " AND ( $pub_id )" : $pub_id;
       }
       if ( $user != '' ) {
           $where = ( $where != '' ) ? $where . " AND ( $user )" : $user;
       }
       if ( $search != '') {
           $where = ( $where != '' ) ? $where . " AND ( $search )" : $search ;
       }
       if ( $exclude != '' ) {
           $where = ( $where != '' ) ? $where . " AND ( $exclude )" : $exclude;
       }
       if ( $include_editors === false ) {
           $where = ( $where != '' ) ? $where . " AND ( r.is_editor = '0' )" : "r.is_editor = '0'";
       }
       if ( $where != '' ) {
           $where = " WHERE $where";
       }

       // LIMIT clause
       if ( $limit != '' ) {
           $limit = "LIMIT $limit";
       }

       // GROUP BY clause
       $group_by = $group_by === true ? " GROUP BY a.name" : '';

       // End
       $sql = $select . $join . $where . $group_by . " ORDER BY a.name $order $limit";
       // echo $sql . '<br/><br/>';
       $sql = $count == false ? $wpdb->get_results($sql, $output_type): $wpdb->get_var($sql);
       return $sql;
    }
    
    /**
     * Adds a new author
     * @param string $name
     * @return int
     * @since 5.0.0
     */
    public static function add_author ($name) {
        global $wpdb;
        $wpdb->insert(TEACHPRESS_AUTHORS, array('name' => $name), array('%s'));
        return $wpdb->insert_id;
    }
    
    /**
     * Adds a new author - publication relation
     * @param int $pub_id
     * @param int $author_id
     * @param int $is_author    --> 1 (true) or 0 (false)
     * @param int $is_editor    --> 1 (true) or 0 (false)
     * @return int
     * @since 5.0.0
     */
    public static function add_author_relation ($pub_id, $author_id, $is_author, $is_editor){
        global $wpdb;
        $wpdb->insert(TEACHPRESS_REL_PUB_AUTH, array('pub_id' => $pub_id, 'author_id' => $author_id, 'is_author' => $is_author, 'is_editor' => $is_editor), array('%d', '%d', '%d', '%d'));
        return $wpdb->insert_id;
    }
    
     /**
     * Returns an array|object with the name, author_id and occurence of all authors
     * @param string $search            --> normal search string
     * @param string $limit             --> SQL limit like 0,50
     * @param string $output_type       --> ARRAY_A, ARRAY_N, OBJECT
     * @return array|object
     * @since 5.0.0
     */
    public static function count_authors ( $search = '', $limit = '', $output_type = ARRAY_A ) {
        global $wpdb;
        $search = esc_sql($search);
        $limit = esc_sql($limit);
        
        // define global search
        if ( $search != '' ) {
            $search = "WHERE a.`name` like '%$search%'";
        }
        
        // LIMIT clause
        if ( $limit != '' ) {
            $limit = "LIMIT $limit";
        }
        
        return $wpdb->get_results("SELECT DISTINCT a.name, a.author_id, count(r.author_id) AS count FROM " . TEACHPRESS_AUTHORS . " a LEFT JOIN " . TEACHPRESS_REL_PUB_AUTH . " r ON a.author_id = r.author_id $search GROUP BY a.name ORDER BY a.name ASC $limit", $output_type);
    }
    
    /**
     * Delete auhtor - publication relations
     * @param int $pub_id
     * @since 5.0.0
     */
    public static function delete_author_relations ($pub_id) {
        global $wpdb;
        $pub_id = intval($pub_id);
        $wpdb->query("DELETE FROM " . TEACHPRESS_REL_PUB_AUTH . " WHERE `pub_id` = '$pub_id'");
    }
    
    /**
     * Delete authors
     * @param array $checkbox
     * @since 5.0.0
     */
    public static function delete_authors($checkbox) {
       global $wpdb;
       for( $i = 0; $i < count( $checkbox ); $i++ ) {
           $checkbox[$i] = intval($checkbox[$i]);
           $wpdb->query( "DELETE FROM " . TEACHPRESS_REL_PUB_AUTH . " WHERE `author_id` = $checkbox[$i]" );
           $wpdb->query( "DELETE FROM " . TEACHPRESS_AUTHORS . " WHERE `author_id` = $checkbox[$i]" );
       }
   }
   
   /**
    * Returns an array or object of related publications of an author/editor
    * @param int $author_id
    * @param string $output_type
    * @since 5.0.0
    */
   public static function get_related_authors($author_id, $output_type = ARRAY_A){
       global $wpdb;
       $author_id = intval($author_id);
       return $wpdb->get_results("SELECT DISTINCT p.pub_id, p.title, p.type, p.bibtex, p.author, p.editor, p.date, DATE_FORMAT(p.date, '%Y') AS year, p.urldate, p.isbn , p.url, p.booktitle, p.issuetitle, p.journal, p.volume, p.number, p.pages, p.publisher, p.address, p.edition, p.chapter, p.institution, p.organization, p.school, p.series, p.crossref, p.abstract, p.howpublished, p.key, p.techtype, p.note, p.is_isbn, p.image_url, p.rel_page, r.is_author, r.is_editor FROM " . TEACHPRESS_PUB .  " p INNER JOIN " . TEACHPRESS_REL_PUB_AUTH . " r ON p.pub_id = r.pub_id WHERE r.author_id = '$author_id' ORDER BY year DESC", $output_type);
       
   }
    
}

/**
 * Contains functions for getting, adding and deleting of bookmarks
 * @package teachpress\core\database
 * @since 5.0.0
 */
class tp_bookmarks {
    
    /**
     * Returns an arrayor object of bookmarks of an user
     * 
     * Possible values for $args:
     *      user            --> user_id
     *      output_type     --> ARRAY_A, ARRAY_N or OBJECT
     *
     * @since 5.0.0
     * @param array $args
     * @return mixed
     */
    public static function get_bookmarks( $args = array() ) {
        $defaults = array(
            'user' => '',
            'output_type' => OBJECT
        ); 
        $args = wp_parse_args( $args, $defaults );
        extract( $args, EXTR_SKIP );

        global $wpdb;

        $user = intval($user);
        $output_type = esc_sql($output_type);

        $sql = "SELECT `bookmark_id`, `pub_id` FROM " . TEACHPRESS_USER . " WHERE `user` = '$user'";
        return $wpdb->get_results($sql, $output_type);
    }
    
    /** 
     * Adds a new bookmark for a user
     * @param int $pub_id   --> publication id
     * @param int $user     --> user_id
     * @return int          --> the id of the created element
     * @since 5.0.0
    */
   public static function add_bookmark($pub_id, $user) {
        global $wpdb;
        $wpdb->insert(TEACHPRESS_USER, array('pub_id' => $pub_id, 'user' => $user), array('%d', '%d'));
        return $wpdb->insert_id;
    }
    
    /** 
     * Delete a bookmark 
     * @param int $del_id   --> IDs of the publications
     * @param int $user     --> user_id
     * @since 5.0.0
    */
    public static function delete_bookmark($del_id) {
        global $wpdb;
        $del_id = intval($del_id);
        $wpdb->query( "DELETE FROM " . TEACHPRESS_USER . " WHERE `bookmark_id` = '$del_id'" );
    }
    
    /**
     * Checks if an user has bookmarked a publication. Returns true the bookmark exists.
     * @param int $pub_id
     * @param int $user_id
     * @return boolean
     * @since 5.0.0
     */
    public static function bookmark_exists($pub_id, $user_id) {
        global $wpdb;
        $pub_id = intval($pub_id);
        $user_id = intval($user_id);
        $test = $wpdb->query("SELECT `pub_id` FROM " . TEACHPRESS_USER . " WHERE `pub_id`='$pub_id' AND `user` = '$user_id'");
        if ($test != 0) {
            return true;
        }
        return false;
    }
    
}

/**
 * Contains functions for getting, adding and deleting of courses
 * @package teachpress\core\database
 * @since 5.0.0
 */
class tp_courses {
    
    /**
     * Returns the capability ("owner" or "approved") of an user for a course
     * @param string $course_id
     * @param string $user_id
     * @return string
     * @since 5.0.0
     */
    public static function get_capability ($course_id, $user_id){
        global $wpdb;
        $course_id = intval($course_id);
        $user_id = intval($user_id);
        $test = $wpdb->get_var("SELECT `use_capabilites` FROM " . TEACHPRESS_COURSES . " WHERE `course_id` = '$course_id'");
        if ( intval($test) === 1 ){
            return $wpdb->get_var("SELECT `capability` FROM " . TEACHPRESS_COURSE_CAPABILITES . " WHERE `course_id` = '$course_id' AND `wp_id` = '$user_id'");
        }
        return 'owner';
    }

    /**
    * Get course capabilites
    * @param int $course_id
    * @param string $output_type
    * @return array|object
    * @since 5.0.0
    */
   public static function get_capabilities ($course_id, $output_type = 'ARRAY_A') {
       global $wpdb;
       $course_id = intval($course_id);
       return $wpdb->get_results("SELECT * FROM " . TEACHPRESS_COURSE_CAPABILITES . " WHERE `course_id` = '$course_id'",$output_type);
   }
   
   /**
    * Add course capability
    * @param int $course_id
    * @param int $user_id
    * @param string $capability
    * @return int|false
    * @since 5.0.0
    */
   public static function add_capability ($course_id, $user_id, $capability) {
       global $wpdb;
       if ( $course_id === 0 || $user_id === 0 || $capability === '' ) {
           return false;
       }
       if ( !tp_courses::has_capability($course_id, $user_id, $capability) ) {
           $wpdb->insert(TEACHPRESS_COURSE_CAPABILITES, array('course_id' => $course_id, 'wp_id' => $user_id, 'capability' => $capability), array('%d', '%d', '%s'));
       }
       return $wpdb->insert_id;
   }
   
   /**
    * Delete course capability
    * @param int $cap_id
    * @since 5.0.0
    * @todo unused
    */
   public static function delete_capability ($cap_id) {
       global $wpdb;
       $cap_id = intval($cap_id);
       $wpdb->query("DELETE FROM " . TEACHPRESS_COURSE_CAPABILITES . " WHERE `cap_id` = '$cap_id'");
   }
   
   /**
    * Checks if a user has a cap in the selected course
    * @param int $course_id         ID of a course
    * @param int $user_id           WordPress user ID
    * @param string $capability     "owner" or "approved"
    * @return boolean
    * @since 5.0.0
    */
   public static function has_capability ($course_id, $user_id, $capability) {
       global $wpdb;
       $user_id = intval($user_id);
       $course_id = intval($course_id);
       $capability = esc_sql($capability);
       if ( $capability === '' ) {
           $where = '';
       }
       else {
           $where = "AND `capability` = '$capability'";
       }
       $test = $wpdb->query("SELECT `wp_id` FROM " . TEACHPRESS_COURSE_CAPABILITES . " WHERE `course_id` = '$course_id' AND `wp_id` = '$user_id' $where");
       if ( $test === 1 ) {
           return true;
       }
       return false;
   }

    /**
     * Returns all data of a single course
     * @param int $id
     * @param string $output_type
     * @return mixed
     * @since 5.0.0
     */
    public static function get_course($id, $output_type = OBJECT) {
        global $wpdb;
        $id = intval($id);
        $result = $wpdb->get_row("SELECT * FROM `" . TEACHPRESS_COURSES . "` WHERE `course_id` = '$id'", $output_type);
        return $result;
    }
    
    /**
     * Returns all data of one or more courses
     * 
     * possible values for $args:
     *      semester    --> the semester/term of the courses
     *      visibility  --> the visibility of the coures (1,2,3) separated by comma
     *      parent      --> the course_id of the parent
     *      search      --> a general search string
     *      exclude     --> the course_ids you want to exclude
     *      order       --> default: semester DESC, name
     *      limit       --> the sql search limit, ie: 0,30
     *      output_type --> ARRAY_A, ARRAY_N or OBJECT
     * 
     * @param array $args
     * @return object|array
     * @since 5.0.0
     */
    public static function get_courses ( $args = array() ) {
        $defaults = array(
            'semester' => '',
            'visibility' => '',
            'parent' => '',
            'search' => '',
            'exclude' => '',
            'order' => 'semester DESC, name',
            'limit' => '',
            'output_type' => OBJECT
        ); 
        $args = wp_parse_args( $args, $defaults );
        extract( $args, EXTR_SKIP );

        global $wpdb;

        // Define basics
        $sql = "SELECT course_id, name, type, lecturer, date, room, places, start, end, semester, parent, visible, rel_page, comment, image_url, strict_signup, use_capabilites, parent_name
                FROM ( SELECT t.course_id AS course_id, t.name AS name, t.type AS type, t.lecturer AS lecturer, t.date AS date, t.room As room, t.places AS places, t.start AS start, t.end As end, t.semester AS semester, t.parent As parent, t.visible AS visible, t.rel_page AS rel_page, t.comment AS comment, t.image_url AS image_url, t.strict_signup AS strict_signup, t.use_capabilites AS use_capabilites, p.name AS parent_name 
                    FROM " . TEACHPRESS_COURSES . " t 
                    LEFT JOIN " . TEACHPRESS_COURSES . " p ON t.parent = p.course_id ) AS temp";
        $where = '';
        $order = esc_sql($order);
        $limit = esc_sql($limit);
        $output_type = esc_sql($output_type);
        $search = esc_sql(htmlspecialchars($search));
        $exclude = tp_db_helpers::generate_where_clause($exclude, "p.pub_id", "AND", "!=");
        $semester = tp_db_helpers::generate_where_clause($semester, "semester", "OR", "=");
        $visibility = tp_db_helpers::generate_where_clause($visibility, "visible", "OR", "=");

        // define global search
        if ( $search != '' ) {
            $search = "`name` like '%$search%' OR `parent_name` like '%$search%' OR `lecturer` like '%$search%' OR `date` like '%$search%' OR `room` like '%$search%' OR `course_id` = '$search'";
        }

        if ( $exclude != '' ) {
            $where = $where != '' ? $where . " AND $exclude " : $exclude;
        }
        if ( $semester != '') {
            $where = $where != '' ? $where . " AND ( $semester )" : $semester;
        }
        if ( $visibility != '') {
            $where = $where != '' ? $where . " AND ( $visibility )" : $visibility;
        }
        if ( $search != '') {
            $where = $where != '' ? $where . " AND ( $search )" : $search ;
        }
        if ( $parent !== '' ) {
            $parent = intval($parent);
            $where = $where != '' ? $where . " AND ( `parent` = '$parent' )" : "`parent` = '$parent'" ;
        }
        if ( $where != '' ) {
            $where = " WHERE $where";
        }
        if ( $limit != '' ) {
            $limit = " LIMIT $limit";
        }

        // define order
        if ($order != '') {
            $order = " ORDER BY $order";
        }
        $result = $wpdb->get_results($sql . $where . $order . $limit, $output_type);
        return $result;
    }
    
    /** 
     * Returns a single value of a course 
     * @param int $course_id    id of the course
     * @param string $col       column name
     * @return string
     * @since 5.0.0
    */  
    public static function get_course_data ($course_id, $col) {
        global $wpdb;
        $course_id = intval($course_id);
        $col = esc_sql($col);
        $result = $wpdb->get_var("SELECT `$col` FROM `" . TEACHPRESS_COURSES . "` WHERE `course_id` = '$course_id'");
        return $result;
    }
    
    /**
     * Returns course meta data
     * @param int $course_id
     * @param string $meta_key
     * @since 5.0.0
     */
    public static function get_course_meta($course_id, $meta_key = ''){
        global $wpdb;
        $course_id = intval($course_id);
        $meta_key = esc_sql($meta_key);
        $where = '';
        if ( $meta_key !== '' ) {
            $where = "AND `meta_key` = '$meta_key'";
        }
        $sql = "SELECT * FROM " . TEACHPRESS_COURSE_META . " WHERE `course_id` = '$course_id' $where";
        return $wpdb->get_results($sql, ARRAY_A);
    }
    
    /**
     * Add course meta
     * @param int $course_id
     * @param string $meta_key
     * @param string $meta_value
     * @since 5.0.0
     */
    public static function add_course_meta ($course_id, $meta_key, $meta_value) {
        global $wpdb;
        $wpdb->insert( TEACHPRESS_COURSE_META, array( 'course_id' => $course_id, 'meta_key' => $meta_key, 'meta_value' => $meta_value ), array( '%d', '%s', '%s' ) );
    }
    
    /**
     * Deletes curse meta
     * @param int $course_id
     * @param string $meta_key
     * @since 5.0.0
     */
    public static function delete_course_meta ($course_id, $meta_key = '') {
        global $wpdb;
        $course_id = intval($course_id);
        $meta_key = esc_sql($meta_key);
        $where = '';
        if ( $meta_key !== '' ) {
            $where = "AND `meta_key` = '$meta_key'";
        }
        $wpdb->query("DELETE FROM " . TEACHPRESS_COURSE_META . " WHERE `course_id` = '$course_id' $where");
    }
    
    /**
     * Returns the number of free places in a course
     * @param int $course_id    --> ID of the course
     * @param int $places       --> Number of places
     * @return int
     * @since 5.0.0
     */
    public static function get_free_places($course_id, $places) {
        global $wpdb;
        $course_id = intval($course_id);
        $places = intval($places);
        $used_places = $wpdb->get_var("SELECT COUNT(`course_id`) FROM " . TEACHPRESS_SIGNUP . " WHERE `course_id` = '$course_id' AND `waitinglist` = 0");
        return ($places - $used_places);
    }
    
    /**
    * Returns the number of used places in all couses
    * @return array
    * @since 5.0.0
    */
   public static function get_used_places() {
       global $wpdb;
       $used_places = array();
       $sql = "SELECT `course_id`, COUNT(`course_id`) AS used_places FROM " . TEACHPRESS_SIGNUP . " WHERE `waitinglist` = '0' GROUP BY `course_id`";
       $r = $wpdb->get_results($sql);
       foreach ($r as $r) {
           $used_places[$r->course_id] = $r->used_places;
       }
       return $used_places;
    }
    
    /** 
     * Add a new course
     * @param array $data       An associative array with data of the course
     * @param array $sub        An associative array with data for the sub courses (type, places, number)
     * @return int              ID of the new course
     * @since 5.0.0
    */
   public static function add_course($data, $sub) {
        global $wpdb;
        $data['start'] = $data['start'] . ' ' . $data['start_hour'] . ':' . $data['start_minute'] . ':00';
        $data['end'] = $data['end'] . ' ' . $data['end_hour'] . ':' . $data['end_minute'] . ':00';
        $wpdb->insert( TEACHPRESS_COURSES, array( 'name' => $data['name'], 'type' => $data['type'], 'room' => $data['room'], 'lecturer' => $data['lecturer'], 'date' => $data['date'], 'places' => $data['places'], 'start' => $data['start'], 'end' => $data['end'], 'semester' => $data['semester'], 'comment' => $data['comment'], 'rel_page' => $data['rel_page'], 'parent' => $data['parent'], 'visible' => $data['visible'], 'waitinglist' => $data['waitinglist'], 'image_url' => $data['image_url'], 'strict_signup' => $data['strict_signup'], 'use_capabilites' => $data['use_capabilites'] ), array( '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%s', '%d', '%d' ) );
        $course_id = $wpdb->insert_id;
        // add capability
        global $current_user;
        tp_courses::add_capability($course_id, $current_user->ID, 'owner');
        // create rel_page
        if ($data['rel_page_alter'] !== 0 ) {
            $data['rel_page'] = tp_courses::add_rel_page($course_id, $data);
            // Update rel_page
            $wpdb->update( TEACHPRESS_COURSES, array( 'rel_page' => $data['rel_page'] ), array( 'course_id' => $course_id ), array( '%d', ), array( '%d' ) );
        }
        // test if creation was successful
        if ( $data['rel_page'] === false ) {
            get_tp_message(__('Error while adding new related content.','teachpress'), 'red');
        }
        // create sub courses
        if ( $sub['number'] !== 0 ) {
            tp_courses::add_sub_courses($course_id, $data, $sub);
        }
        return $course_id;
    }
    
    /**
     * Adds a new related content to WordPress
     * @param int $course_id    The ID of the course
     * @param array $data       An associative array of the course data
     * @return int or false
     * @since 5.0.0
     * @access private
     */
    private static function add_rel_page($course_id, $data) {
        $post = get_post($data['rel_page_alter']);
        $content = str_replace('[course_id]', 'id="' . $course_id . '"', $post->post_content );
        $postarr = array ( 
            'post_title'   => $data['name'],
            'post_content' => $content,
            'post_type'    => $post->post_type,
            'post_author'  => $post->post_author,
            'post_status'   => 'publish'
        );
        return wp_insert_post($postarr);
    }
    
    /**
     * Adds sub courses to a course
     * @param int $course_id    The ID of the parent course
     * @param array $data       An associative array with data of the parent course
     * @param array $sub        An associative array with data for the sub courses (type, places, number)
     * @since 5.0.0
     * @access private
     */
    private static function add_sub_courses($course_id, $data, $sub) {
        $sub_data = $data;
        $sub_data['parent'] = $course_id;
        $sub_data['places'] = $sub['places'];
        $sub_data['type'] = $sub['type'];
        $options = array('number' => 0);
        for ( $i = 1; $i <= $sub['number']; $i++ ) {
            $sub_data['name'] = $sub['type'] . ' ' . $i;
            tp_courses::add_course($sub_data, $options);
        }
    }
    
    /** 
     * Change a course
     * @param int $course_id    --> course ID
     * @param array $data
     * @since 5.0.0
    */ 
   public static function change_course($course_id, $data){
        global $wpdb;
        $course_id = intval($course_id);
        $old_places = tp_courses::get_course_data ($course_id, 'places');

        // handle the number of free places
        if ( $data['places'] > $old_places ) {
            $new_free_places = $data['places'] - $old_places;
            // subscribe students from the waiting list automatically
            $sql = "SELECT s.con_id, s.waitinglist, s.date
                    FROM " . TEACHPRESS_SIGNUP . " s 
                    INNER JOIN " . TEACHPRESS_COURSES . " c ON c.course_id=s.course_id
                    WHERE c.course_id = '$course_id' AND s.waitinglist = '1' ORDER BY s.date ASC";
            $waitinglist = $wpdb->get_results($sql, ARRAY_A);
            $count_waitinglist = count($waitinglist);
            if ( $count_waitinglist > 0 ) {
                foreach ( $waitinglist as $waitinglist ) {
                    if ( $new_free_places > 0 ) {
                        $wpdb->update( TEACHPRESS_SIGNUP, array ( 'waitinglist' => 0 ), array ( 'con_id' => $waitinglist["con_id"] ), array ( '%d' ), array ( '%d' ) );
                    }
                    else {
                        break;
                    }
                    $new_free_places--;
                }
            }
            // END subscribe students from the waiting list automatically
        }
        // END handle the number of free places

        $data['start'] = $data['start'] . ' ' . $data['start_hour'] . ':' . $data['start_minute'] . ':00';
        $data['end'] = $data['end'] . ' ' . $data['end_hour'] . ':' . $data['end_minute'] . ':00';
        $wpdb->update( TEACHPRESS_COURSES, array( 'name' => $data['name'], 'type' => $data['type'], 'room' => $data['room'], 'lecturer' => $data['lecturer'], 'date' => $data['date'], 'places' => $data['places'], 'start' => $data['start'], 'end' => $data['end'], 'semester' => $data['semester'], 'comment' => $data['comment'], 'rel_page' => $data['rel_page'], 'parent' => $data['parent'], 'visible' => $data['visible'], 'waitinglist' => $data['waitinglist'], 'image_url' => $data['image_url'], 'strict_signup' => $data['strict_signup'], 'use_capabilites' => $data['use_capabilites'] ), array( 'course_id' => $course_id ), array( '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%s', '%d', '%d' ), array( '%d' ) );
    }
    
    /**
     * Delete courses
     * @param array $checkbox   --> IDs of the courses
     * @since 5.0.0
     */
    public static function delete_courses($checkbox){
        global $wpdb;
        $wpdb->query("SET FOREIGN_KEY_CHECKS=0");
        for( $i = 0; $i < count( $checkbox ); $i++ ) { 
            $checkbox[$i] = intval($checkbox[$i]); 
            $wpdb->query( "DELETE FROM " . TEACHPRESS_COURSES . " WHERE `course_id` = $checkbox[$i]" );
            $wpdb->query( "DELETE FROM " . TEACHPRESS_COURSE_META . " WHERE `course_id` = $checkbox[$i]" );
            $wpdb->query( "DELETE FROM " . TEACHPRESS_COURSE_CAPABILITES . " WHERE `course_id` = $checkbox[$i]" );
            $wpdb->query( "DELETE FROM " . TEACHPRESS_COURSE_DOCUMENTS . " WHERE `course_id` = $checkbox[$i]" );
            $wpdb->query( "DELETE FROM " . TEACHPRESS_ASSESSMENTS . " WHERE `course_id` = $checkbox[$i]" );
            $wpdb->query( "DELETE FROM " . TEACHPRESS_ARTEFACTS . " WHERE `course_id` = $checkbox[$i]" );
            $wpdb->query( "DELETE FROM " . TEACHPRESS_SIGNUP . " WHERE `course_id` = $checkbox[$i]" );
            // Check if there are parent courses, which are not selected for erasing, and set there parent to default
            $sql = "SELECT `course_id` FROM " . TEACHPRESS_COURSES . " WHERE `parent` = $checkbox[$i]";
            $test = $wpdb->query($sql);
            if ($test == '0') {
                continue;
            }
            $row = $wpdb->get_results($sql);
            foreach ($row as $row) {
                if ( !in_array($row->course_id, $checkbox) ) {
                    $wpdb->update( TEACHPRESS_COURSES, array( 'parent' => 0 ), array( 'course_id' => $row->course_id ), array('%d' ), array( '%d' ) );
                }
            }
        }
        $wpdb->query("SET FOREIGN_KEY_CHECKS=1");
    }
    
    /**
     * Returns course signups or waitinglist entries
     * @param array $args
     * @return object|array
     * @since 5.0.0
     */
    public static function get_signups ( $args = array() ) {
        $defaults = array(
            'course' => '',
            'waitinglist' => '',
            'order' => '',
            'limit' => '',
            'output_type' => OBJECT
        );
        $args = wp_parse_args( $args, $defaults );
        extract( $args, EXTR_SKIP );

        global $wpdb;

        $course = esc_sql($course);
        $order = esc_sql($order);
        $output_type = esc_sql($output_type);
        $waitinglist = esc_sql($waitinglist);
        $limit = esc_sql($limit);

        if ($order != '') {
            $order = " ORDER BY $order";
        }
        if ( $limit != '' ) {
            $limit = " LIMIT $limit";
        }

        $fields = get_tp_options('teachpress_stud','`setting_id` ASC');
        $selects = '';
        $joins = '';
        $count = 1;
        foreach ($fields as $row) {
            $table_id = 'm' . $count; 
            $selects = $selects . ', ' . $table_id .'.meta_value AS ' . $row->variable;
            $joins = $joins . ' INNER JOIN ' . TEACHPRESS_STUD_META . ' ' . $table_id . " ON ( " . $table_id . ".wp_id = s.wp_id AND " . $table_id . ".meta_key = '" . $row->variable . "' ) ";
            $count++;
        }

        $where = '';
        $sql = "SELECT DISTINCT st.wp_id, st.firstname, st.lastname, st.userlogin, st.email, s.date, s.con_id, s.waitinglist $selects "
                . "FROM " . TEACHPRESS_SIGNUP . " s "
                . "INNER JOIN " . TEACHPRESS_STUD . " st ON st.wp_id = s.wp_id $joins"
                . "WHERE s.course_id = '$course' ";

        if ( $waitinglist !== '' ) {
            $where = " AND s.waitinglist = '$waitinglist'";
        }
        
        // get_tp_message($sql . $where . $order . $limit, 'orange');
        $result = $wpdb->get_results($sql . $where . $order . $limit, $output_type);
        return $result;
    }
    
    /** 
     * Subscribe a student manually
     * @param int $student      --> ID of the student
     * @param int $course       --> ID of the course
     * @return boolean
     * @since 5.0.0
    */	
    public static function add_signup($student, $course) {
        global $wpdb;
        $student = intval($student);
        $course = intval($course);
        if ( $student != 0 && $course != 0 ) {
            $wpdb->query( "INSERT INTO " . TEACHPRESS_SIGNUP . " (`course_id`, `wp_id`, `waitinglist`, `date`) VALUES ('$course', '$student', '0', NOW() )" );
            return true;
        }
        return false;
    }
    
    /**
     * Moves a signup to an other course
     * @param array $checkbox     --> ID's of registrations
     * @param int $course         --> course_id
     * @since 5.0.0
     */
    public static function move_signup($checkbox, $course) {
        global $wpdb;
        if ( $checkbox == '' ) { return false; }
        $course = intval($course);
        $max = count($checkbox);
        for ( $i = 0; $i < $max; $i++ ) {
            $checkbox[$i] = intval($checkbox[$i]);
            if ( $checkbox[$i] != 0 && $course != 0) {
                $wpdb->update( TEACHPRESS_SIGNUP, array ('course_id' => $course), array( 'con_id' => $checkbox[$i] ), array('%d'), array('%d') );
            }
        }
    }
    
    /** 
     * Change the status of one or more course signups
     * @param array $checkbox   --> IDs of the signups
     * @param string $status    --> the new status for the signups (course or waitinglist)
     * @since 5.0.0
    */
    public static function change_signup_status($checkbox, $status = 'course') {
        global $wpdb;
        if ( $checkbox == '' ) { return false; }
        $status = $status == 'course' ? 0 : 1;
        $max = count( $checkbox );
        for( $i = 0; $i < $max; $i++ ) {
            $checkbox[$i] = intval($checkbox[$i]);
            $wpdb->update( TEACHPRESS_SIGNUP, array ( 'waitinglist' => $status ), array ( 'con_id' => $checkbox[$i] ), array ( '%d'), array ( '%d' ) );
        }
    }
    
    /** 
     * Delete signup and add an entry from the waitinglist to the course (if possible). Please note that this function doesn't use transactions like tp_delete_signup_student().
     * @param array $checkbox   --> An array with course IDs
     * @param boolean $move_up  --> A flag for the automatic move up from waitinglist entries
     * @since 5.0.0
    */
    public static function delete_signup($checkbox, $move_up = true) {
        global $wpdb;
        if ( $checkbox == '' ) {
            return false;
        }
        $max = count( $checkbox );
        for( $i = 0; $i < $max; $i++ ) {
            $checkbox[$i] = intval($checkbox[$i]);
            if ( $move_up !== true ) {
                continue;
            }
            $row1 = $wpdb->get_results("SELECT `course_id` FROM " . TEACHPRESS_SIGNUP . " WHERE `con_id` = '$checkbox[$i]'");
            foreach ($row1 as $row1) {
                // check if there are users in the waiting list
                $sql = "SELECT `con_id` FROM " . TEACHPRESS_SIGNUP . " WHERE `course_id` = '" . $row1->course_id . "' AND `waitinglist` = '1' ORDER BY `con_id` ASC LIMIT 0, 1";
                $con_id = $wpdb->get_var($sql);
                // if is true subscribe the first one in the waiting list for the course
                if ($con_id != 0 && $con_id != '') {
                    $wpdb->query( "UPDATE " . TEACHPRESS_SIGNUP . " SET `waitinglist` = '0' WHERE `con_id` = '$con_id'" );
                }	
            }
            $wpdb->query( "DELETE FROM " . TEACHPRESS_SIGNUP . " WHERE `con_id` = '$checkbox[$i]'" );
        }
    }
    
    /**
     * Returns true if the current user is subscribed in the tested course or false if not.
     * @param integer $course_id
     * @param boolean $consider_childcourses
     * @return boolean
     * @since 5.0.0
     */
    public static function is_student_subscribed ($course_id, $consider_childcourses = false) {
        global $wpdb;
        global $user_ID;
        get_currentuserinfo();
        $course_id = intval($course_id);
        if ( $course_id == 0 ) {
            return false;
        }
        // simple case
        if ( $consider_childcourses == false ) {
            $test = $wpdb->query("SELECT `con_id` FROM " . TEACHPRESS_SIGNUP . " WHERE `course_id` = '$course_id' AND `wp_id` = '$user_ID' AND `waitinglist` = '0'");
        }
        // consider child courses
        if ( $consider_childcourses == true ) {
            $where = '';
            $courses = $wpdb->get_results("SELECT `course_id` FROM " . TEACHPRESS_COURSES . " WHERE `parent` = '$course_id'");
            foreach ( $courses as $row ) {
                $where = $where == '' ? "`course_id` = '$row->course_id'" : $where . " OR `course_id` = '$row->course_id'";
            }
            if ( $where != '' ) {
                $where = " WHERE `wp_id` = '$user_ID' AND `waitinglist` = '0' AND ( $where OR `course_id` = '$course_id' )";
                $test = $wpdb->query("SELECT `con_id` FROM " . TEACHPRESS_SIGNUP . " $where");
            }
            // Fallback if there are no child courses
            else {
                $test = $wpdb->query("SELECT `con_id` FROM " . TEACHPRESS_SIGNUP . " WHERE `course_id` = '$course_id' AND `wp_id` = '$user_ID' AND `waitinglist` = '0'");
            }
        }

        if ( $test >= 1 ) {
            return true;
        }
        return false;
    }
    
}

/**
 * Contains all functions for the access to course documents
 * @package teachpress\core\database
 * @since 5.0.0
 */
class tp_documents {
    
    public static function get_document($doc_id, $output_type = ARRAY_A) {
        global $wpdb;
        $doc_id = intval($doc_id);
        return $wpdb->get_row("SELECT * FROM " . TEACHPRESS_COURSE_DOCUMENTS . " WHERE `doc_id` = '$doc_id'",$output_type);
    }
    
    /**
     * Returns the documents of a course
     * @param int $course_id
     * @param string $output_type (OBJECT, ARRAY_A or ARRAY_N)
     * @return array|object
     * @since 5.0.0
     */
    public static function get_documents($course_id, $output_type = ARRAY_A) {
        global $wpdb;
        $course_id = intval($course_id);
        return $wpdb->get_results("SELECT * FROM " . TEACHPRESS_COURSE_DOCUMENTS . " WHERE `course_id` = '$course_id' ORDER BY `sort` ASC, `added` ASC",$output_type);
    }
    
    /**
     * Adds a connection between a course and a document file
     * @param string $name
     * @param string $path
     * @param int $course_id
     * @return int The id of the added document entry
     * @since 5.0.0
     */
    public static function add_document($name, $path, $course_id) {
        global $wpdb;
        $time = current_time('mysql',0);
        $wpdb->insert( TEACHPRESS_COURSE_DOCUMENTS, array( 'name' => $name, 
                                                           'path' => $path, 
                                                           'added' => $time, 
                                                           'sort' => 0, 
                                                           'course_id' => intval($course_id) ), 
                                                           array( '%s', '%s', '%s', '%d','%d') );
        return $wpdb->insert_id;
    }
    
    /**
     * Sets the value of the name field for a document entry
     * @param int $doc_id
     * @param string $doc_name
     * @since 5.0.0
     */
    public static function change_document_name($doc_id, $doc_name) {
        global $wpdb;
        $wpdb->update( TEACHPRESS_COURSE_DOCUMENTS, array( 'name' => $doc_name ), array( 'doc_id' => $doc_id ), array( '%s', ), array( '%d' ) );
    }


    /**
     * Sets the value of the sort field for a document entry
     * @param int $doc_id
     * @param int $sort
     * @since 5.0.0
     */
    public static function set_sort($doc_id, $sort) {
        global $wpdb;
        $wpdb->update( TEACHPRESS_COURSE_DOCUMENTS, array( 'sort' => $sort ), array( 'doc_id' => $doc_id ), array( '%d', ), array( '%d' ) );
    }

        /**
     * Deletes a document entry in the database
     * @param int $doc_id
     * @since 5.0.0
     */
    public static function delete_document($doc_id) {
        global $wpdb;
        $doc_id = intval($doc_id);
        $wpdb->query("DELETE FROM " . TEACHPRESS_COURSE_DOCUMENTS . " WHERE `doc_id` = '$doc_id'");
    }
}

/**
 * Contains all functions for getting, adding and deleting of plugin options
 * @package teachpress\core\database
 * @since 5.0.0
 */
class tp_options {
    
    /**
     * Returns an option by ID
     * @param int $id
     * @return array
     * @since 5.0.0
     */
    public static function get_option_by_id ($id){
        global $wpdb;
        $id = intval($id);
        return $wpdb->get_row("SELECT * FROM " . TEACHPRESS_SETTINGS . " WHERE `setting_id` = '$id'", ARRAY_A);
    }

    /** 
     * Adds an option
     * @param string $name      Name of the option
     * @param string $value     Nalue of the option
     * @param string $category  Category name (system, course_of_studies, course_type, semester)
     * @return int The ID if the added option
     * @since 5.0.0
    */
    public static function add_option($name, $value, $category) { 
        global $wpdb;
        $name = htmlspecialchars($name);
        $value = htmlspecialchars($value);
        $category = htmlspecialchars($category);
        $wpdb->insert( TEACHPRESS_SETTINGS, array( 'variable' => $name, 'value' => $value, 'category' => $category ), array( '%s', '%s', '%s' ) );
        return $wpdb->insert_id;
    }
    
    /**
     * Updates an option
     * @param string $variable
     * @param string $value
     * @param string $type      --> normal or checkbox
     * @since 5.0.0
     */
    public static function change_option ($variable, $value, $type = 'normal') {
        global $wpdb;
        $variable = esc_sql($variable);
        $value = esc_sql($value);
        if ( $type === 'checkbox' ) {
            $value = ( $value !== '' ) ? 1 : 0;
        }
        $wpdb->query( "UPDATE " . TEACHPRESS_SETTINGS . " SET `value` = '$value' WHERE `variable` = '$variable'" );
    }
    
    /** 
     * Deletes an option
     * @param int $delete 
     * @since 5.0.0
    */
    public static function delete_option($delete) {
        global $wpdb;
        $delete = intval($delete);		
        $wpdb->query( "DELETE FROM " . TEACHPRESS_SETTINGS . " WHERE `setting_id` = '$delete'" );
    }
    
}

/**
 * Contains functions for getting, adding and deleting of publications
 * @package teachpress\core\database
 * @since 5.0.0
 */
class tp_publications {
    
    /**
     * Returns a single publication
     * @param int $id
     * @param string $output_type (OBJECT, ARRAY_A or ARRAY_N)
     * @return mixed
     * @since 5.0.0
     */
    public static function get_publication($id, $output_type = OBJECT) {
        global $wpdb;
        $id = intval($id);
        $result = $wpdb->get_row("SELECT *, DATE_FORMAT(date, '%Y') AS year FROM " . TEACHPRESS_PUB . " WHERE `pub_id` = '$id'", $output_type);
        return $result;
    }
    
    /**
     * Returns a single publication selected by bibtex key
     * @param int $key
     * @param string $output_type (OBJECT, ARRAY_A or ARRAY_N)
     * @return mixed
     * @since 5.0.0
     */
    public static function get_publication_by_key($key, $output_type = OBJECT) {
        global $wpdb;
        $key = esc_sql(htmlspecialchars($key));
        $result = $wpdb->get_row("SELECT *, DATE_FORMAT(date, '%Y') AS year FROM " . TEACHPRESS_PUB . " WHERE `bibtex` = '$key'", $output_type);
        return $result;
    }
    
    /**
     * Returns an array or object of publications
     * 
     * Possible values for $args:
     *  user            --> user IDs (separated by comma)
     *  type            --> type name (separated by comma)
     *  tag             --> tag IDs (separated by comma)
     *  author_id       --> author IDs (separated by comma)
     *  year            --> years (separated by comma)
     *  author          --> author name (separated by comma)
     *  editor          --> editor name (separated by comma)
     *  exclude         --> the ids of the publications you want to exclude (separated by comma)
     *  include         --> the ids of the publications you want to include (separated by comma)
     *  exclude_tags    --> use it to exclude publications via tag IDs (separated by comma)
     *  order           --> the order of the list
     *  limit           --> the sql search limit, ie: 0,30
     *  search          --> the search string
     *  output type     --> OBJECT, ARRAY_A, ARRAY_N	
     *
     * @since 5.0.0
     * @param array $args
     * @param boolean $count    set to true of you only need the number of rows
     * @return mixed            array, object or int
    */
    public static function get_publications($args = array(), $count = false) {
        $defaults = array(
            'user' => '',
            'type' => '',
            'tag' => '',
            'author_id' => '',
            'year' => '',
            'author' => '',
            'editor' => '',
            'include' => '',
            'exclude' => '',
            'exclude_tags' => '',
            'order' => 'date DESC',
            'limit' => '',
            'search' => '',
            'output_type' => OBJECT
        );
        $args = wp_parse_args( $args, $defaults );
        extract( $args, EXTR_SKIP );

        $order_all = $order;

        global $wpdb;

        // define basics
        $select = "SELECT DISTINCT p.pub_id, p.title, p.type, p.bibtex, p.author, p.editor, p.date, DATE_FORMAT(p.date, '%Y') AS year, p.urldate, p.isbn , p.url, p.booktitle, p.issuetitle, p.journal, p.volume, p.number, p.pages, p.publisher, p.address, p.edition, p.chapter, p.institution, p.organization, p.school, p.series, p.crossref, p.abstract, p.howpublished, p.key, p.techtype, p.note, p.is_isbn, p.image_url, p.rel_page FROM " . TEACHPRESS_PUB .  " p ";
        $join = '';
        $where = '';
        $order = '';
        $having ='';
        $output_type = esc_sql($output_type);
        $search = esc_sql($search);
        $limit = esc_sql($limit);

        // exclude publications via tag_id
        if ( $exclude_tags != '' ) {
            $extend = '';
            $exclude_tags = tp_db_helpers::generate_where_clause($exclude_tags , "tag_id", "OR", "=");
            $exclude_publications = $wpdb->get_results("SELECT DISTINCT pub_id FROM " . TEACHPRESS_RELATION . " WHERE $exclude_tags ORDER BY pub_id ASC", ARRAY_A);
            foreach ($exclude_publications as $row) {
                $extend = $extend . $row['pub_id'] . ',';
            }
            $exclude = $extend . $exclude;
        }

        // define where, having and limit clause
        $exclude = tp_db_helpers::generate_where_clause($exclude, "p.pub_id", "AND", "!=");
        $include = tp_db_helpers::generate_where_clause($include, "p.pub_id", "OR", "=");
        $type = tp_db_helpers::generate_where_clause($type, "p.type", "OR", "=");
        $user = tp_db_helpers::generate_where_clause($user, "u.user", "OR", "=");
        $tag = tp_db_helpers::generate_where_clause($tag, "b.tag_id", "OR", "=");
        $author_id = tp_db_helpers::generate_where_clause($author_id, "r.author_id", "OR", "=");
        $year = tp_db_helpers::generate_where_clause($year, "year", "OR", "=");
        $author = tp_db_helpers::generate_where_clause($author, "p.author", "OR", "LIKE", '%');
        $editor = tp_db_helpers::generate_where_clause($editor, "p.editor", "OR", "LIKE", '%');

        // additional joins
        if ( $user != '' ) {
            $join .= "INNER JOIN " . TEACHPRESS_USER . " u ON u.pub_id = p.pub_id ";
        }
        if ( $tag != '' ) {
            $join .= "INNER JOIN " . TEACHPRESS_RELATION . " b ON p.pub_id = b.pub_id INNER JOIN " . TEACHPRESS_TAGS . " t ON t.tag_id = b.tag_id ";
        }
        if ( $author_id != '' ) {
            $join .= "INNER JOIN " . TEACHPRESS_REL_PUB_AUTH . " r ON p.pub_id = r.pub_id ";
        }

        // define order_by clause
        $array = explode(",",$order_all);
        foreach($array as $element) {
            $element = trim($element);
            // order by year
            if ( strpos($element, 'year') !== false ) {
                $order = $order . $element . ', ';
            }
            // normal case
            if ( $element != '' && strpos($element, 'year') === false ) {
                $order = $order . 'p.' . $element . ', ';
            }

        }
        if ( $order != '' ) {
            $order = substr($order, 0, -2);
        }

        // define global search
        if ( $search != '' ) {
            $search = "p.title LIKE '%$search%' OR p.author LIKE '%$search%' OR p.editor LIKE '%$search%' OR p.isbn LIKE '%$search%' OR p.booktitle LIKE '%$search%' OR p.issuetitle LIKE '%$search%' OR p.journal LIKE '%$search%' OR p.date LIKE '%$search%'";
        }

        if ( $exclude != '' ) {
            $where = $where != '' ? $where . " AND $exclude " : $exclude;
        }
        if ( $include != '' ) {
            $where = $where != '' ? $where . " AND $include " : $include;
        }
        if ( $type != '') {
            $where = $where != '' ? $where . " AND ( $type )" : $type;
        }
        if ( $user != '') {
            $where = $where != '' ? $where . " AND ( $user )" : $user;
        }
        if ( $tag != '' ) {
            $where = $where != '' ? $where . " AND ( $tag )" : $tag;
        }
        if ( $author_id != '') {
            $where = $where != '' ? $where . " AND ( $author_id )" : $author_id;
        }
        if ( $author != '') {
            $where = $where != '' ? $where . " AND ( $author )" : $author;
        }
        if ( $editor != '') {
            $where = $where != '' ? $where . " AND ( $editor )" : $editor;
        }
        if ( $search != '') {
            $where = $where != '' ? $where . " AND ( $search )" : $search ;
        }
        if ( $where != '' ) {
            $where = " WHERE $where";
        }
        if ( $year != '' && $year !== '0' ) {
            $having = " HAVING $year";
        }
        if ( $limit != '' ) {
            $limit = "LIMIT $limit";
        }
        else {
            $limit = '';
        }

        // End
        if ( $count !== true ) {
            $sql = $select . $join . $where . $having . " ORDER BY $order $limit";
        }
        else {
            $sql = "SELECT COUNT( DISTINCT pub_id ) AS `count` FROM ( $select $join $where $having) p ";
        }
        // print_r($args);
        // get_tp_message($sql,'red');
        $sql = $count != true ? $wpdb->get_results($sql, $output_type): $wpdb->get_var($sql);
        return $sql;
    }
    
    /**
     * Returns course meta data
     * @param int $pub_id
     * @param string $meta_key
     * @since 5.0.0
     */
    public static function get_pub_meta($pub_id, $meta_key = ''){
        global $wpdb;
        $pub_id = intval($pub_id);
        $meta_key = esc_sql($meta_key);
        $where = '';
        if ( $meta_key !== '' ) {
            $where = "AND `meta_key` = '$meta_key'";
        }
        $sql = "SELECT * FROM " . TEACHPRESS_PUB_META . " WHERE `pub_id` = '$pub_id' $where";
        return $wpdb->get_results($sql, ARRAY_A);
    }
    
    /**
     * Returns an array or object of users who has a publication list
     * @param array $args
     * @return object|array
     * @since 5.0.0
     */
    public static function get_pub_users( $args = array() ) {
        $defaults = array(
            'output_type' => OBJECT
        ); 
        $args = wp_parse_args( $args, $defaults );
        extract( $args, EXTR_SKIP );

        global $wpdb;
        $output_type = esc_sql($output_type);

        $result = $wpdb->get_results("SELECT DISTINCT user FROM " . TEACHPRESS_USER, $output_type);

        return $result;
    }
    
    /**
     * Returns an array or object of publication types which are used for existing publication entries
     * @param array $args
     * @return object|array
     * @since 5.0.0
     */
    public static function get_used_pubtypes( $args = array() ) {
        $defaults = array(
            'user' => '',
            'output_type' => ARRAY_A
        ); 
        $args = wp_parse_args( $args, $defaults );
        extract( $args, EXTR_SKIP );

        global $wpdb;
        $output_type = esc_sql($output_type);
        $user = tp_db_helpers::generate_where_clause($user, "u.user", "OR", "=");
        if ( $user == '' ) {
            $result = $wpdb->get_results("SELECT DISTINCT p.type FROM " .TEACHPRESS_PUB . " p ORDER BY p.type ASC", $output_type);
        }    
        else {
            $result = $wpdb->get_results("SELECT DISTINCT p.type from " .TEACHPRESS_PUB . " p 
                                          INNER JOIN " .TEACHPRESS_USER . " u ON u.pub_id=p.pub_id 
                                          WHERE $user 
                                          ORDER BY p.type ASC", $output_type);
        }
        return $result;
    }
    
    /**
     * Returns an object or array with the years where publications are written
     * @param array $args
     * @return object|array
     * @since 5.0.0
     */
    public static function get_years( $args = array() ) {
        $defaults = array(
            'type' => '',
            'user' => '',
            'order' => 'ASC',
            'output_type' => OBJECT
        ); 
        $args = wp_parse_args( $args, $defaults );
        extract( $args, EXTR_SKIP );

        global $wpdb;

        $join = '';
        $where = '';
        $order = esc_sql($order);
        $output_type = esc_sql($output_type);
        $type = tp_db_helpers::generate_where_clause($type, "p.type", "OR", "=");
        $user = tp_db_helpers::generate_where_clause($user, "u.user", "OR", "=");

        if ( $type != '') {
            $where = $where != '' ? $where . " AND ( $type )" : $type;
        }
        if ( $user != '') {
            $where = $where != '' ? $where . " AND ( $user )" : $user;
            $join = "INNER JOIN " . TEACHPRESS_USER . " u ON u.pub_id=p.pub_id";
        }
        if ( $where != '' ) {
            $where = " WHERE $where";
        }

        $result = $wpdb->get_results("SELECT DISTINCT DATE_FORMAT(p.date, '%Y') AS year FROM " . TEACHPRESS_PUB . " p $join $where ORDER BY year $order", $output_type);
        return $result;
    }
    
    /** 
     * Adds a publication
     * @param array $data
     * @param string $tags
     * @param array $bookmark
     * @return int      --> id of the new publication
     * @since 5.0.0
    */
    public static function add_publication($data, $tags, $bookmark) {
         global $wpdb;
         $defaults = array(
            'title' => '',
            'type' => '',
            'bibtex' => '',
            'author' => '',
            'editor' => '',
            'isbn' => '',
            'url' => '',
            'date' => '',
            'urldate' => '', 
            'booktitle' => '',
            'issuetitle' => '',
            'journal' => '',
            'volume' => '',
            'number' => '',
            'pages' => '',
            'publisher' => '',
            'address' => '',
            'edition' => '',
            'chapter' => '',
            'institution' => '',
            'organization' => '',
            'school' => '',
            'series' => '',
            'crossref' => '',
            'abstract' => '',
            'howpublished' => '',
            'key' => '',
            'techtype' => '',
            'comment' => '',
            'note' => '',
            'image_url' => '',
            'is_isbn' => '',
            'rel_page' => ''
        ); 
        $data = wp_parse_args( $data, $defaults );
        extract( $data, EXTR_SKIP );

        // intercept wrong values for dates
        $urldate = ( $urldate == 'JJJJ-MM-TT' ) ? '0000-00-00' : $urldate;
        $date = ( $date == 'JJJJ-MM-TT' ) ? '0000-00-00' : $date;

        // check if bibtex_key is unique; if not make him unique
        $check = $wpdb->get_var("SELECT COUNT('pub_id') FROM " . TEACHPRESS_PUB . " WHERE `bibtex` = '" . esc_sql($bibtex) . "'");
        if ( intval($check) > 0 ) {
            $alphabet = range('a', 'z');
            if ( $check <= 25 ) {
                $bibtex .= $alphabet[$check];
            }
            else {
                $bibtex .= '_' . $check;
            }
        }
        
        // check if bibtex key has no spaces
        if ( strpos($bibtex, ' ') !== false ) {
            $bibtex = str_replace(' ', '', $bibtex);
        }
        
        // check last chars of author/editor fields
        if ( substr($author, -5) === ' and ' ) {
            $author = substr($author ,0 , strlen($author) - 5);
        }
        if ( substr($editor, -5) === ' and ' ) {
            $editor = substr($editor ,0 , strlen($editor) - 5);
        }

        $wpdb->insert( TEACHPRESS_PUB, array( 'title' => $title, 'type' => $type, 'bibtex' => $bibtex, 'author' => $author, 'editor' => $editor, 'isbn' => $isbn, 'url' => $url, 'date' => $date, 'urldate' => $urldate, 'booktitle' => $booktitle, 'issuetitle' => $issuetitle, 'journal' => $journal, 'volume' => $volume, 'number' => $number, 'pages' => $pages , 'publisher' => $publisher, 'address' => $address, 'edition' => $edition, 'chapter' => $chapter, 'institution' => $institution, 'organization' => $organization, 'school' => $school, 'series' => $series, 'crossref' => $crossref, 'abstract' => $abstract, 'howpublished' => $howpublished, 'key' => $key, 'techtype' => $techtype, 'comment' => $comment, 'note' => $note, 'image_url' => $image_url, 'is_isbn' => $is_isbn, 'rel_page' => $rel_page ), array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d' ) );
         $pub_id = $wpdb->insert_id;

        // Bookmarks
        if ( $bookmark != '' ) {
            $max = count( $bookmark );
            for( $i = 0; $i < $max; $i++ ) {
               if ($bookmark[$i] != '' || $bookmark[$i] != 0) {
                   tp_bookmarks::add_bookmark($pub_id, $bookmark[$i]);
               }
            }
        }
        
        // Tags
        tp_publications::add_relation($pub_id, $tags);
        
        // Authors
        tp_publications::add_relation($pub_id, $author, ' and ', 'authors');
        
        // Editors
        tp_publications::add_relation($pub_id, $editor, ' and ', 'editors');
        
        return $pub_id;
    }
    
    /**
     * Add publication meta data
     * @param int $pub_id
     * @param string $meta_key
     * @param string $meta_value
     * @since 5.0.0
     */
    public static function add_pub_meta ($pub_id, $meta_key, $meta_value) {
        global $wpdb;
        $wpdb->insert( TEACHPRESS_PUB_META, array( 'pub_id' => $pub_id, 'meta_key' => $meta_key, 'meta_value' => $meta_value ), array( '%d', '%s', '%s' ) );
    }
    
    /** 
     * Edit a publication
     * @param int $pub_id           --> ID of the publication
     * @param array_a $data         --> Publication data
     * @param array $bookmark       --> An array with WP_USER_ids
     * @param array $delbox         --> An array with tag IDs you want to delete
     * @param string $tags          --> A string of Tags seperate by comma
     * @since 5.0.0
    */
   public static function change_publication($pub_id, $data, $bookmark, $delbox, $tags) {
        global $wpdb;
        $pub_id = intval($pub_id);
        // check if bibtex key has no spaces
        if ( strpos($data['bibtex'], ' ') !== false ) {
            $data['bibtex'] = str_replace(' ', '', $data['bibtex']);
        }
        // check last chars of author/editor fields
        if ( substr($data['author'], -5) === ' and ' ) {
            $data['author'] = substr($data['author'] ,0 , strlen($data['author']) - 5);
        }
        if ( substr($data['editor'], -5) === ' and ' ) {
            $data['editor'] = substr($data['editor'] ,0 , strlen($data['editor']) - 5);
        }
        // update row
        $wpdb->update( TEACHPRESS_PUB, array( 'title' => $data['title'], 'type' => $data['type'], 'bibtex' => $data['bibtex'], 'author' => $data['author'], 'editor' => $data['editor'], 'isbn' => $data['isbn'], 'url' => $data['url'], 'date' => $data['date'], 'urldate' => $data['urldate'], 'booktitle' => $data['booktitle'], 'issuetitle' => $data['issuetitle'], 'journal' => $data['journal'], 'volume' => $data['volume'], 'number' => $data['number'], 'pages' => $data['pages'] , 'publisher' => $data['publisher'], 'address' => $data['address'], 'edition' => $data['edition'], 'chapter' => $data['chapter'], 'institution' => $data['institution'], 'organization' => $data['organization'], 'school' => $data['school'], 'series' => $data['series'], 'crossref' => $data['crossref'], 'abstract' => $data['abstract'], 'howpublished' => $data['howpublished'], 'key' => $data['key'], 'techtype' => $data['techtype'], 'comment' => $data['comment'], 'note' => $data['note'], 'image_url' => $data['image_url'], 'is_isbn' => $data['is_isbn'], 'rel_page' => $data['rel_page'] ), array( 'pub_id' => $pub_id ), array( '%s', '%s', '%s', '%s', '%s', '%s', '%s' ,'%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ,'%d', '%d' ), array( '%d' ) );
        // Bookmarks
        if ($bookmark != '') {
            $max = count( $bookmark );
            for( $i = 0; $i < $max; $i++ ) {
                if ($bookmark[$i] != '' || $bookmark[$i] != 0) {
                    tp_bookmarks::add_bookmark($pub_id, $bookmark[$i]);
                }
            }
        }
        
        // Handle tag relations
        if ( $delbox != '' ) {
            tp_tags::delete_tag_relation($delbox);
        }
        if ( $tags != '' ) {
            tp_publications::add_relation($pub_id, $tags);
        }
        
        // Handle author/editor relations
        tp_authors::delete_author_relations($pub_id);
        if ( $data['author'] != '' ) {
            tp_publications::add_relation($pub_id, $data['author'], ' and ', 'authors');
        }
        if ( $data['editor'] != '' ) {
            tp_publications::add_relation($pub_id, $data['editor'], ' and ', 'editors');
        }
    }
    
    /**
     * Update a publication by key (import option); Returns FALSE if no publication with the given key was found
     * @param string $key
     * @param array $data
     * @param string $tags
     * @return boolean|int
     * @since 5.0.0
     */
    public static function change_publication_by_key($key, $data, $tags) {
        global $wpdb;
        $key = esc_sql($key);
        $pub_id = $wpdb->get_var("SELECT `pub_id` FROM " . TEACHPRESS_PUB . " WHERE `bibtex` = '$key'");
        if ( $pub_id === NULL ) {
            return false;
        }
        
        // check if bibtex key has no spaces
        if ( strpos($data['bibtex'], ' ') !== false ) {
            $data['bibtex'] = str_replace(' ', '', $data['bibtex']);
        }
        
        // update row
        $wpdb->update( TEACHPRESS_PUB, array( 'title' => $data['title'], 'type' => $data['type'], 'bibtex' => $data['bibtex'], 'author' => $data['author'], 'editor' => $data['editor'], 'isbn' => $data['isbn'], 'url' => $data['url'], 'date' => $data['date'], 'urldate' => $data['urldate'], 'booktitle' => $data['booktitle'], 'issuetitle' => $data['issuetitle'], 'journal' => $data['journal'], 'volume' => $data['volume'], 'number' => $data['number'], 'pages' => $data['pages'] , 'publisher' => $data['publisher'], 'address' => $data['address'], 'edition' => $data['edition'], 'chapter' => $data['chapter'], 'institution' => $data['institution'], 'organization' => $data['organization'], 'school' => $data['school'], 'series' => $data['series'], 'crossref' => $data['crossref'], 'abstract' => $data['abstract'], 'howpublished' => $data['howpublished'], 'key' => $data['key'], 'techtype' => $data['techtype'], 'comment' => $data['comment'], 'note' => $data['note'], 'is_isbn' => $data['is_isbn'] ), array( 'pub_id' => $pub_id ), array( '%s', '%s', '%s', '%s', '%s', '%s', '%s' ,'%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s','%d' ), array( '%d' ) );
        
        // Delete existing tags
        $wpdb->query( "DELETE FROM " . TEACHPRESS_RELATION . " WHERE `pub_id` = $pub_id" );
        
        // Add new tags
        if ( $tags != '' ) {
            tp_publications::add_relation($pub_id, $tags);
        }
        
        // Handle author/editor relations
        tp_authors::delete_author_relations($pub_id);
        if ( $data['author'] != '' ) {
            tp_publications::add_relation($pub_id, $data['author'], ' and ', 'authors');
        }
        if ( $data['editor'] != '' ) {
            tp_publications::add_relation($pub_id, $data['editor'], ' and ', 'editors');
        }
        
        return $pub_id;
    }
    
    /** 
     * Delete publications
     * @param array $checkbox       --> An array with IDs of publication
     * @since 5.0.0
    */
   public static function delete_publications($checkbox){	
        global $wpdb;
        $max = count( $checkbox );
        for( $i = 0; $i < $max; $i++ ) {
            $checkbox[$i] = intval($checkbox[$i]);
            $wpdb->query( "DELETE FROM " . TEACHPRESS_RELATION . " WHERE `pub_id` = '$checkbox[$i]'" );
            $wpdb->query( "DELETE FROM " . TEACHPRESS_REL_PUB_AUTH . " WHERE `pub_id` = $checkbox[$i]" );
            $wpdb->query( "DELETE FROM " . TEACHPRESS_USER . " WHERE `pub_id` = '$checkbox[$i]'" );
            $wpdb->query( "DELETE FROM " . TEACHPRESS_PUB . " WHERE `pub_id` = '$checkbox[$i]'" );
        }
    }
    
    /**
     * Deletes curse meta
     * @param int $pub_id
     * @param string $meta_key
     * @since 5.0.0
     */
    public static function delete_pub_meta ($pub_id, $meta_key = '') {
        global $wpdb;
        $pub_id = intval($pub_id);
        $meta_key = esc_sql($meta_key);
        $where = '';
        if ( $meta_key !== '' ) {
            $where = "AND `meta_key` = '$meta_key'";
        }
        $wpdb->query("DELETE FROM " . TEACHPRESS_PUB_META . " WHERE `pub_id` = '$pub_id' $where");
    }
    
    /**
     * Add new relations (for tags,authors,etc)
     * @param int $pub_id
     * @param string $input_string
     * @param string $delimiter         --> example: ','
     * @param string $rel_type          --> tags, authors, editors
     * @since 5.0.0
     */
    public static function add_relation ($pub_id, $input_string, $delimiter = ',', $rel_type = 'tags') {
        global $wpdb;
        $pub_id = intval($pub_id);
        $array = explode($delimiter, $input_string);
        foreach($array as $element) {
            $element = trim($element);
            
            if ( $element === '' ) {
                continue;
            }
            
            $element = esc_sql( htmlspecialchars($element) );
            // check if element exists
            if ( $rel_type === 'tags' ) {
                $check = $wpdb->get_var("SELECT `tag_id` FROM " . TEACHPRESS_TAGS . " WHERE `name` = '$element'");
            }
            else {
                $check = $wpdb->get_var("SELECT `author_id` FROM " . TEACHPRESS_AUTHORS . " WHERE `name` = '$element'");
            }
            // if element not exists
            if ( $check === NULL ){
                $check = ( $rel_type === 'tags' ) ? tp_tags::add_tag($element) : tp_authors::add_author($element);
            }
            // check if relation exists, if not add relation
            if ( $rel_type === 'tags' ) {
                $test = $wpdb->query("SELECT `pub_id` FROM " . TEACHPRESS_RELATION . " WHERE `pub_id` = '$pub_id' AND `tag_id` = '$check'");
                if ($test == 0) {
                    tp_tags::add_tag_relation($pub_id, $check);
                }
            }
            else {
                $test = $wpdb->query("SELECT `pub_id` FROM " . TEACHPRESS_REL_PUB_AUTH . " WHERE `pub_id` = '$pub_id' AND `author_id` = '$check'");
                if ($test == 0) {
                    $is_author = ( $rel_type === 'authors' ) ? 1 : 0;
                    $is_editor = ( $rel_type === 'editors' ) ? 1 : 0;
                    tp_authors::add_author_relation($pub_id, $check, $is_author, $is_editor);
                }
            }
        }
    }
}

/**
 * Contains functions for getting, adding and deleting of students
 * @package teachpress\core\database
 * @since 5.0.0
 */
class tp_students {
    
    /**
     * Returns data of a student
     * @param string $id            ID of the student/user
     * @param string $output_type   OBJECT, ARRAY_A or ARRAY_N; Default is ARRAY_A
     * @return object
     * @since 5.0.0
     */
    public static function get_student ($id, $output_type = ARRAY_A) {
        global $wpdb;
        $id = intval($id);
        $result = $wpdb->get_row("Select * FROM " . TEACHPRESS_STUD . " WHERE `wp_id` = '$id'", $output_type);
        return $result;
    }
    
    /**
     * Returns the data of all students
     * @param array $args
     * @return object or array
     * @since 5.0.0
     */
    public static function get_students ( $args = array() ) {
        $defaults = array(
            'course_of_studies' => '',
            'search' => '',
            'order' => '`lastname` ASC, `firstname` ASC',
            'limit' => '',
            'output_type' => OBJECT,
            'count' => false
        );
        $args = wp_parse_args( $args, $defaults );
        extract( $args, EXTR_SKIP );

        global $wpdb;

        $select = "SELECT * FROM " . TEACHPRESS_STUD;
        $where = '';
        $order = esc_sql($order);
        $limit = esc_sql($limit);
        $output_type = esc_sql($output_type);
        $search = esc_sql(htmlspecialchars($search));

        // define global search
        if ( $search != '' ) {
            $search = "`wp_id` like '%$search%' OR `firstname` LIKE '%$search%' OR `lastname` LIKE '%$search%' OR `email` LIKE '%$search%'";
        }

        // if the user needs only the number of rows
        if ( $count === true ) {
            $select = "SELECT COUNT(`wp_id`) AS `count` FROM " . TEACHPRESS_STUD;
        }

        // define where clause
        $course_of_studies = tp_db_helpers::generate_where_clause($course_of_studies, "course_of_studies", "OR", "=");

        if ( $course_of_studies != '') {
            $where = $where != '' ? $where . " AND ( $course_of_studies )" : $course_of_studies;
        }
        if ( $search != '') {
            $where = $where != '' ? $where . " AND ( $search )" : $search ;
        }
        if ( $where != '' ) {
            $where = " WHERE $where";
        }
        if ( $limit != '' ) {
            $limit = "LIMIT $limit";
        }

        // End
        $sql = $select . $where . " ORDER BY $order $limit";
        $sql = $count === false ? $wpdb->get_results($sql, $output_type): $wpdb->get_var($sql);
        return $sql;
    }
    
    /**
     * Returns user meta data
     * @param int $wp_id
     * @param string $meta_key
     * @since 5.0.0
     */
    public static function get_student_meta($wp_id, $meta_key = ''){
        global $wpdb;
        $wp_id = intval($wp_id);
        $meta_key = esc_sql($meta_key);
        $where = '';
        if ( $meta_key !== '' ) {
            $where = "AND `meta_key` = '$meta_key'";
        }
        $sql = "SELECT * FROM " . TEACHPRESS_STUD_META . " WHERE `wp_id` = '$wp_id' $where";
        return $wpdb->get_results($sql, ARRAY_A);
    }
    
    /** 
     * Add student
     * @param int $wp_id    --> WordPress user ID
     * @param array $data   --> an array with the user data
     * @return boolean
     * @since 5.0.0
    */
   public static function add_student($wp_id, $data) {
        global $wpdb;
        $wp_id = intval($wp_id);

        $test = $wpdb->query("SELECT `wp_id` FROM " . TEACHPRESS_STUD . " WHERE `wp_id` = '$wp_id'");
        if ($test == '0') {
            $data['birthday'] = $data['birth_year'] . '-' . $data['birth_month'] . '-' . $data['birth_day'];
            $wpdb->insert( TEACHPRESS_STUD, array( 'wp_id' => $wp_id, 'firstname' => $data['firstname'], 'lastname' => $data['lastname'], 'userlogin' => $data['userlogin'], 'email' => $data['email'] ), array( '%d', '%s', '%s', '%s', '%s' ) );
            return true;
        }
        else {
            return false;
        }
    }
    
    /**
     * Add student meta
     * @param int $wp_id
     * @param string $meta_key
     * @param string $meta_value
     * @since 5.0.0
     */
    public static function add_student_meta ($wp_id, $meta_key, $meta_value) {
        global $wpdb;
        $wpdb->insert( TEACHPRESS_STUD_META, array( 'wp_id' => $wp_id, 'meta_key' => $meta_key, 'meta_value' => $meta_value ), array( '%d', '%s', '%s' ) );
    }
    
    /** 
     * Edit userdata
     * @param int $wp_id
     * @param array_a $data
     * @param boolean $show_message
     * @return string
     * @since 5.0.0
    */
   public static function change_student($wp_id, $data, $show_message = true) {
        global $wpdb;
        $wpdb->update( TEACHPRESS_STUD, array( 'firstname' => $data['firstname'], 'lastname' => $data['lastname'], 'userlogin' => $data['userlogin'], 'email' => $data['email'] ), array( 'wp_id' => $wp_id ), array( '%s', '%s', '%s', '%s' ), array( '%d' ) );
        if ($show_message === true) {
            return '<div class="teachpress_message_success">' . __('Changes in your profile successful.','teachpress') . '</div>';
        }
    }
    
    /** 
     * Delete student
     * @param array $checkbox       --> ID of the enrollment
     * @since 5.0.0
    */ 
   public static function delete_student($checkbox){
        global $wpdb;
        for( $i = 0; $i < count( $checkbox ); $i++ ) {
            $checkbox[$i] = intval($checkbox[$i]);
            // search courses where the user was registered
            $row1 = $wpdb->get_results("SELECT `course_id` FROM " . TEACHPRESS_SIGNUP . " WHERE `wp_id` = '$checkbox[$i]'");
            foreach ($row1 as $row1) {
                // check if there are users in the waiting list
                $sql = "SELECT `con_id` FROM " . TEACHPRESS_SIGNUP . " WHERE `course_id` = '" . $row1->course_id . "' AND `waitinglist` = '1' ORDER BY `con_id` ASC LIMIT 0, 1";
                $con_id = $wpdb->get_var($sql);
                // if is true subscribe the first one in the waiting list for the course
                if ($con_id != 0 && $con_id != '') {
                    $wpdb->query( "UPDATE " . TEACHPRESS_SIGNUP . " SET `waitinglist` = '0' WHERE `con_id` = '$con_id'" );
                }
            }
            $wpdb->query( "DELETE FROM " . TEACHPRESS_SIGNUP . " WHERE `wp_id` = '$checkbox[$i]'" );
            $wpdb->query("DELETE FROM " . TEACHPRESS_STUD_META . " WHERE `wp_id` = '$checkbox[$i]'");
            $wpdb->query( "DELETE FROM " . TEACHPRESS_STUD . " WHERE `wp_id` = '$checkbox[$i]'" );
        }
    }
    
    /**
     * Deletes student meta
     * @param int $wp_id
     * @param string $meta_key
     * @since 5.0.0
     */
    public static function delete_student_meta ($wp_id, $meta_key = '') {
        global $wpdb;
        $wp_id = intval($wp_id);
        $meta_key = esc_sql($meta_key);
        $where = '';
        if ( $meta_key !== '' ) {
            $where = "AND `meta_key` = '$meta_key'";
        }
        $wpdb->query("DELETE FROM " . TEACHPRESS_STUD_META . " WHERE `wp_id` = '$wp_id' $where");
    }
    
    /**
     * Returns an array or object of all signups of a student
     * @param array $args
     * @return array|object 
     * @since 5.0.0
     */
    public static function get_signups ( $args = array()) {
        $defaults = array(
            'wp_id' => '',
            'mode' => 'all',
            'course_id' => '',
            'output_type' => OBJECT,
        );
        $args = wp_parse_args( $args, $defaults );
        extract( $args, EXTR_SKIP );

        global $wpdb;
        $wp_id = intval($wp_id);
        $course_id = intval($course_id);
        $mode = htmlspecialchars($mode);
        $output_type = htmlspecialchars($output_type);

        // search only in sub courses
        $where = '';
        if ( $course_id !== 0 ) {
            $where = "WHERE c.parent = '$course_id' ";
        }

        $sql = "SELECT con_id, wp_id, course_id, waitinglist, name, type, room, date, semester, parent_name, timestamp FROM (SELECT s.con_id as con_id, s.wp_id as wp_id, s.course_id as course_id, s.waitinglist as waitinglist, c.name as name, c.type as type, c.room as room, c.date as date, c.semester as semester, c2.name as parent_name, s.date as timestamp FROM " . TEACHPRESS_SIGNUP . " s INNER JOIN " . TEACHPRESS_COURSES . " c ON s.course_id = c.course_id LEFT JOIN " . TEACHPRESS_COURSES . " c2 ON c.parent = c2.course_id $where) AS temp WHERE `wp_id` = '$wp_id'";
        if ( $mode == 'reg' ) {
            $sql = $sql . " AND `waitinglist` = '0'";
        }
        if ( $mode == 'wtl' ) {
            $sql = $sql . " AND `waitinglist` = '1'";
        }
        $sql = $sql . " ORDER BY con_id DESC";
        $result = $wpdb->get_results($sql, $output_type);
        return $result;
    }
    
}

/**
 * Database access class for tags
 * @package teachpress\core\database
 * @since 5.0.0
 */
class tp_tags {
    
   /**
    * Returns an array of all used tags based on the publication tag relation
    * 
    * Note: If you only need a list of used tags, set group_by to true.
    * In this case you should ignore the columns con_id and pub_id from return
    * 
    * Possible values for $args:
    *  pub_id          --> publication IDs (separated by comma)
    *  user            --> user IDs (separated by comma)
    *  exclude         --> tag IDs you want to exclude from result (separated by comma)
    *  order           --> ASC or DESC; default is ASC
    *  limit           --> the sql limit, example: 0,30
    *  search          --> a normal search string
    *  group by        --> boolean flag for the group by clause
    *  count           --> set it to true if you only need an number of tags which will be returned by your selection
    *  output type     --> OBJECT, ARRAY_A, ARRAY_N 
    * 
    * @param array $args
    * @return array|object
    * @since 5.0.0
    */
   public static function get_tags( $args = array() ) {
       $defaults = array(
           'pub_id' => '',
           'user' => '',
           'exclude' => '',
           'order' => 'ASC',
           'limit' => '',
           'search' => '',
           'count' => false,
           'group_by' => false, 
           'output_type' => OBJECT
       ); 
       $args = wp_parse_args( $args, $defaults );
       extract( $args, EXTR_SKIP );

       global $wpdb;
       $limit = esc_sql($limit);
       $order = esc_sql($order);
       $user = tp_db_helpers::generate_where_clause($user, "u.user", "OR", "=");
       $pub_id = tp_db_helpers::generate_where_clause($pub_id, "r.pub_id", "OR", "=");
       $exclude = tp_db_helpers::generate_where_clause($exclude, "r.tag_id", "AND", "!=");
       $output_type = esc_sql($output_type);
       $search = esc_sql(htmlspecialchars($search));

       // Define basics
       $select = "SELECT DISTINCT t.name, r.tag_id, r.pub_id, r.con_id FROM " . TEACHPRESS_RELATION . " r INNER JOIN " . TEACHPRESS_TAGS . " t ON t.tag_id = r.tag_id";
       $join = '';
       $where = '';

       // define global search
       if ( $search != '' ) {
           $search = "t.name like '%$search%'";
       }

       // if the user needs only the number of rows
       if ( $count === true ) {
           $select = "SELECT COUNT(t.`tag_id`) AS `count` FROM " . TEACHPRESS_TAGS . " t";
       }

       // Additional tables
       if ( $user != '' ) {
           $join .= " INNER JOIN " . TEACHPRESS_USER . " u ON u.pub_id = r.pub_id ";
       }

       // WHERE clause
       if ( $pub_id != '') {
           $where = ( $where != '' ) ? $where . " AND ( $pub_id )" : $pub_id;
       }
       if ( $user != '' ) {
           $where = ( $where != '' ) ? $where . " AND ( $user )" : $user;
       }
       if ( $search != '') {
           $where = $where != '' ? $where . " AND ( $search )" : $search ;
       }
       if ( $exclude != '' ) {
           $where = ( $where != '' ) ? $where . " AND ( $exclude )" : $exclude;
       }
       if ( $where != '' ) {
           $where = " WHERE $where";
       }

       // LIMIT clause
       if ( $limit != '' ) {
           $limit = "LIMIT $limit";
       }

       // GROUP BY clause
       $group_by = $group_by === true ? " GROUP BY t.name" : '';

       // End
       $sql = $select . $join . $where . $group_by . " ORDER BY t.name $order $limit";
       // echo $sql . '<br/><br/>';
       $sql = $count == false ? $wpdb->get_results($sql, $output_type): $wpdb->get_var($sql);
       return $sql;
   }
   
   /**
    * Adds a new tag
    * @param string $name          --> the new tag
    * @return int                  --> the id of the created tag
    * @since 5.0.0
    */
   public static function add_tag($name) {
       global $wpdb;
       $wpdb->insert(TEACHPRESS_TAGS, array('name' => $name), array('%s'));
       return $wpdb->insert_id;
   }
    
   /** 
    * Edit a tag
    * @param int $tag_id 
    * @param string $name
    * @since 5.0.0
   */
   public static function edit_tag($tag_id, $name) {
       global $wpdb;
       $wpdb->update( TEACHPRESS_TAGS, array( 'name' => $name ), array( 'tag_id' => $tag_id ), array( '%s' ), array( '%d' ) );
   }
   
   /**
    * Adds a relation between a tag and a publication
    * @param int $pub_id
    * @param int $tag_id
    * @return int
    * @since 5.0.0
    */
   public static function add_tag_relation($pub_id, $tag_id) {
       global $wpdb;
       $wpdb->insert(TEACHPRESS_RELATION, array('pub_id' => $pub_id, 'tag_id' => $tag_id), array('%d', '%d'));
       return $wpdb->insert_id;
   }
   
   /**
    * Changes tag relations for more than one publication
    * @param array $publications       --> Array of publication IDs
    * @param string $new_tags          --> New tags separated by comma
    * @param array $delete             --> Array of tag IDs whose relations with publications (given in the first parameter) should be deleted
    * @since 5.0.0
    */
   public static function change_tag_relations ($publications, $new_tags, $delete) {
       global $wpdb;
       $array = explode(",",$new_tags);
       $max = count( $publications );
       $max_delete = count ( $delete );

       for( $i = 0; $i < $max; $i++ ) {
           $publication = intval($publications[$i]);
           // Delete tags
           for ( $j = 0; $j < $max_delete; $j++ ) {
               $delete[$j] = intval($delete[$j]);
               $wpdb->query( "DELETE FROM " . TEACHPRESS_RELATION . " WHERE `pub_id` = '$publication' AND `tag_id` = '$delete[$j]'" );
           }

           // Add tags
           foreach( $array as $element ) {
                $element = esc_sql( htmlspecialchars( trim($element) ) );
                if ($element === '') {
                   continue;
                }
                $check = $wpdb->get_var("SELECT `tag_id` FROM " . TEACHPRESS_TAGS . " WHERE `name` = '$element'");
                // if tag not exist
                if ( $check === NULL ){
                    $check = tp_tags::add_tag($element);
                }
                // add releation between publication and tag
                $test = $wpdb->query("SELECT `pub_id` FROM " . TEACHPRESS_RELATION . " WHERE `pub_id` = '$publication' AND `tag_id` = '$check'");
                if ($test === 0) {
                    tp_tags::add_tag_relation($publications[$i], $check);
                }
         	
           }  
       } 
   }
   
   /** 
    * Deletes tags
    * @param array $checkbox       --> an array with tag IDs
    * @since 5.0.0
   */
   public static function delete_tags($checkbox) {
       global $wpdb;
       for( $i = 0; $i < count( $checkbox ); $i++ ) {
           $checkbox[$i] = intval($checkbox[$i]);
           $wpdb->query( "DELETE FROM " . TEACHPRESS_RELATION . " WHERE `tag_id` = $checkbox[$i]" );
           $wpdb->query( "DELETE FROM " . TEACHPRESS_TAGS . " WHERE `tag_id` = $checkbox[$i]" );
       }
   }
   
   /**
    * Deletes relations between tags and publications
    * @param array $delbox
    * @since 5.0.0
    */
   public static function delete_tag_relation($delbox) {
       global $wpdb;
       for ( $i = 0; $i < count($delbox); $i++ ) {
           $delbox[$i] = intval($delbox[$i]);
           $wpdb->query( "DELETE FROM " . TEACHPRESS_RELATION .  " WHERE `con_id` = $delbox[$i]" );
       }
   }
    
    /**
     * Returns an array|object with the name, tag_id and occurence of all_tags
     * @param string $search            --> normal search string
     * @param string $limit             --> SQL limit like 0,50
     * @param string $output_type       --> ARRAY_A, ARRAY_N, OBJECT
     * @return array|object
     * @since 5.0.0
     */
    public static function count_tags ( $search = '', $limit = '', $output_type = ARRAY_A ) {
        global $wpdb;
        $search = esc_sql($search);
        $limit = esc_sql($limit);
        
        // define global search
        if ( $search != '' ) {
            $search = "WHERE t.`name` like '%$search%'";
        }
        
        // LIMIT clause
        if ( $limit != '' ) {
            $limit = "LIMIT $limit";
        }
        
        return $wpdb->get_results("SELECT DISTINCT t.name, t.tag_id, count(r.tag_id) AS count FROM " . TEACHPRESS_TAGS . " t LEFT JOIN " . TEACHPRESS_RELATION . " r ON t.tag_id = r.tag_id $search GROUP BY t.name ORDER BY t.name ASC $limit", $output_type);
    }
    
    /**
     * Returns a special array for creating tag clouds
     * 
     * The returned array $result has the following array_keys:
     *      'tags'  => it's an array or object with tags, including following keys: tagPeak, name, tag_id
     *      'info'  => it's an object which includes information about the frequency of tags, including following keys: max, min
     * 
     * @param array $args
     * @return array|object
     * @since 5.0.0
    */
    public static function get_tag_cloud ( $args = array() ) {
       $defaults = array(
           'user' => '',
           'type' => '',
           'number_tags' => '',
           'exclude' => '',
           'output_type' => OBJECT
       ); 
       $args = wp_parse_args( $args, $defaults );
       extract( $args, EXTR_SKIP );

       global $wpdb;

       $where = '';
       $number_tags = intval($number_tags);
       $output_type = esc_sql($output_type);
       $type = tp_db_helpers::generate_where_clause($type, "p.type", "OR", "=");
       $user = tp_db_helpers::generate_where_clause($user, "u.user", "OR", "=");
       $exclude = tp_db_helpers::generate_where_clause($exclude, "r.tag_id", "AND", "!=");
       $join1 = "LEFT JOIN " . TEACHPRESS_TAGS . " t ON r.tag_id = t.tag_id";
       $join2 = "INNER JOIN " . TEACHPRESS_PUB . " p ON p.pub_id = r.pub_id";
       $join3 = "INNER JOIN " . TEACHPRESS_USER . " u ON u.pub_id = p.pub_id";

       if ( $user == '' && $type == '' ) {
           $join1 = '';
           $join2 = '';
           $join3 = '';

       }
       if ( $user == '' && $type != '' ) {
           $join3 = '';
       }

       // WHERE clause
       if ( $type != '') {
           $where = $where != '' ? $where . " AND ( $type )" : $type;
       }
       if ( $user != '') {
           $where = $where != '' ? $where . " AND ( $user )" : $user;
       }
       if ( $exclude != '' ) {
           $where = $where != '' ? $where . " AND ( $exclude )" : $exclude;
       }
       if ( $where != '' ) {
           $where = " WHERE $where";
       }

       $sql = "SELECT anzahlTags FROM ( 
                   SELECT COUNT(*) AS anzahlTags 
                   FROM " . TEACHPRESS_RELATION . " r
                   $join1 $join2 $join3 $where
                   GROUP BY r.tag_id 
                   ORDER BY anzahlTags DESC ) as temp1 
               GROUP BY anzahlTags 
               ORDER BY anzahlTags DESC";
       $cloud_info = $wpdb->get_row("SELECT MAX(anzahlTags) AS max, min(anzahlTags) AS min FROM ( $sql ) AS temp", OBJECT);
       $cloud_info->min = $cloud_info->min == '' ? 0 : $cloud_info->min; // Fix if there are no tags
       $sql = "SELECT tagPeak, name, tag_id FROM ( 
                 SELECT COUNT(r.tag_id) as tagPeak, t.name AS name, t.tag_id as tag_id 
                 FROM " . TEACHPRESS_RELATION . " r 
                 LEFT JOIN " . TEACHPRESS_TAGS . " t ON r.tag_id = t.tag_id 
                 INNER JOIN " . TEACHPRESS_PUB . " p ON p.pub_id = r.pub_id 
                 $join3 $where
                 GROUP BY r.tag_id ORDER BY tagPeak DESC 
                 LIMIT $number_tags ) AS temp 
               WHERE tagPeak>=".$cloud_info->min." 
               ORDER BY name";
       $result["tags"] = $wpdb->get_results($sql, $output_type);
       $result["info"] = $cloud_info;
       return $result;
    }
}

/**
 * Contains database helper functions
 * @package teachpress\core\database
 * @since 5.0.0
 */
class tp_db_helpers {
    
    /**
     * Generate a where clause
     * @param string $input         --> an array with values
     * @param string $column        --> name of the table column
     * @param string $connector     --> the connector: AND, OR
     * @param string $operator      --> the operator: = !=
     * @param string $pattern       --> things like %
     * @return string
     * @since 5.0.0
     */
    public static function generate_where_clause($input, $column, $connector = 'AND', $operator = '=', $pattern = '') {
        $end = '';
        if ($input === '' || $input === 0) {
            return;
        }
        
        $array = explode(",", $input);
        foreach ( $array as $element ) {
            $element = esc_sql( htmlspecialchars( trim($element) ) );
            if ( $element === '' ) {
                continue;
            }
            if ( $pattern != '' ) { $element = $pattern . $element . $pattern; }
            $end = ( $end == '' ) ? "$column $operator '$element'" : $end . " $connector $column $operator '$element'";
        }
        
        return $end;
    }
    
    /**
     * Register a new table column in teachpress
     * @param string $table
     * @param string $column
     * @param array $data
     * @since 5.0.0
     */
    public static function register_column ($table, $column, $data) {
        $value = 'name = {' . $column. '}, title = {' . $data['title'] . '}, type = {' . $data['type'] . '}, required = {' . $data['required'] . '}, unique = {' . $data['unique'] . '}, visibility = {' . $data['visibility'] . '}';
        tp_options::add_option($column, $value, $table);
    }
    
    /**
     * Extract column settings from a string
     * @param string $data
     * @return array
     * @since 5.0.0
     */
    public static function extract_column_data ($data) {
        $return = array();
        $data = explode(',', $data);
        foreach ( $data as $row ) {
            $row = explode(' = ', $row);
            $name = trim($row[0]);
            $value = str_replace(array('{','}'), array('',''), trim($row[1]));
            $return[$name] = $value;
        }
        return $return;
    }
    
    
}
