<?php

/**
 * Get an array or object of publications
 * @param array $args
 *		user 		--> user IDs (separated by comma)
 *		type 		-->type name (separated by comma)
 *		tag 		--> tag IDs (separated by comma)
 *		year 		--> years (separated by comma)
 * 		author 		--> author name (separated by comma)
 *		editor 		--> editor name (separated by comma)
 *		exclude 	--> the id of the publication you want to exclude
 *		order
 *		limit 		--> the sql search limit, ie: 0,30
 *		search 		--> the search string
 * 		output type --> OBJECT, ARRAY_A, ARRAY_N		
 * @return object or array
 * @since 3.1.8
*/
function get_tp_publications($args) {
    $defaults = array(
        'user' => '',
        'type' => '',
        'tag' => '',
        'year' => '',
        'author' => '',
        'editor' => '',
        'exclude' => '',
        'order' => 'date DESC',
        'limit' => '',
        'search' => '',
        'output_type' => OBJECT
    ); 
    $args = wp_parse_args( $args, $defaults );
    extract( $args, EXTR_SKIP );

    $order_all = $order;

    global $wpdb;
    global $teachpress_pub;
    global $teachpress_relation;
    global $teachpress_tags;
    global $teachpress_user;

    // define basics
    $select = "SELECT DISTINCT p.pub_id, p.name, p.type, p.bibtex, p.author, p.editor, p.date, DATE_FORMAT(p.date, '%Y') AS year, p.isbn , p.url, p.booktitle, p.journal, p.volume, p.number, p.pages, p.publisher, p.address, p.edition, p.chapter, p.institution, p.organization, p.school, p.series, p.crossref, p.abstract, p.howpublished, p.key, p.techtype, p.note, p.is_isbn, p.image_url, p.rel_page 
               FROM $teachpress_relation b ";
    $join = "INNER JOIN $teachpress_pub p ON p.pub_id = b.pub_id ";
    $where = "";
    $order = "";
    $having ="";
    $search = esc_sql(htmlspecialchars($search));
    $limit = esc_sql(htmlspecialchars($limit));
    $output_type = htmlspecialchars($output_type);

    // additional joins
    if ( $user != '' ) {
        $join = $join . "INNER JOIN $teachpress_user u ON u.pub_id= b.pub_id ";
    }
    if ( $tag != '' ) {
        $join = $join . "INNER JOIN $teachpress_tags t ON t.tag_id = b.tag_id ";
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
    if ( $search != "" ) {
        $search = "p.name LIKE '%$search%' OR p.author LIKE '%$search%' OR p.editor LIKE '%$search%' OR p.isbn LIKE '%$search%' OR p.booktitle LIKE '%$search%' OR p.journal LIKE '%$search%'";
    }

    // define where clause
    $ex = tp_generate_where_clause($exclude, "p.pub_id", "AND", "!=");
    $types = tp_generate_where_clause($type, "p.type", "OR", "=");
    $users = tp_generate_where_clause($user, "u.user", "OR", "=");
    $tags = tp_generate_where_clause($tag, "b.tag_id", "OR", "=");
    $years = tp_generate_where_clause($year, "year", "OR", "=");
    $authors = tp_generate_where_clause($author, "p.author", "OR", "LIKE", '%');
    $editors = tp_generate_where_clause($editor, "p.editor", "OR", "LIKE", '%');

    if ( $ex != '' ) {
        $where = $where != "" ? $where . " AND $ex " : $ex;
    }
    if ( $types != '') {
        $where = $where != "" ? $where . " AND ( $types )" : $types;
    }
    if ( $users != '') {
        $where = $where != "" ? $where . " AND ( $users )" : $users;
    }
    if ( $tags != '') {
        $where = $where != "" ? $where . " AND ( $tags )" : $tags;
    }
    if ( $authors != '') {
        $where = $where != "" ? $where . " AND ( $authors )" : $authors;
    }
    if ( $editors != '') {
        $where = $where != "" ? $where . " AND ( $editors )" : $editors;
    }
    if ( $search != '') {
        $where = $where != "" ? $where . " AND ( $search )" : $search ;
    }
    if ( $where != '' ) {
        $where = " WHERE $where";
    }
    if ( $years != '') {
        $having = " HAVING $years";
    }
    if ( $limit != '' ) {
        $limit = "LIMIT $limit";
    }

    // End
    $sql = $select . $join . $where . $having . " ORDER BY $order $limit";
    $sql = $wpdb->get_results($sql, $output_type);
    return $sql;
}

/**
 * Generate a where clause
 * @param string $input
 * @param string $column
 * @param string $connector
 * @param string $operator
 * @param string $pattern
 * @return string
 * @since 3.1.8
 */
function tp_generate_where_clause($input, $column, $connector = "AND", $operator = "=", $pattern = "") {
    $end = "";
    if ($input != "") {
        $array = explode(",", $input);
        foreach ( $array as $element ) {
            $element = esc_sql( htmlspecialchars( trim($element) ) );
            if ( $element != "" ) {
                if ( $pattern != "" ) { $element = $pattern . $element . $pattern; }
                $end = $end == "" ? "$column $operator '$element'" : $end . " $connector $column $operator '$element'";
            }
        }
    }
    return $end;
}

/**
 * Get a single publication
 * @param int $id
 * @param string $output_type (OBJECT, ARRAY_A or ARRAY_N)
 * @return object or array
 * @since 3.1.7 
 */
function get_tp_publication($id, $output_type = OBJECT) {
    global $wpdb;
    global $teachpress_pub;
    $id = intval($id);
    $result = $wpdb->get_row("SELECT * FROM $teachpress_pub WHERE `pub_id` = '$id'", $output_type);
    return $result;
}

/**
 * Get an object or array with the years where publications are written
 * @param string $output_type (OBJECT, ARRAY_A or ARRAY_N)
 * @since 3.1.7
 */
function get_tp_publication_years($output_type = OBJECT) {
    global $wpdb;
    global $teachpress_pub;
    $result = $wpdb->get_results("SELECT DISTINCT DATE_FORMAT(p.date, '%Y') AS year FROM $teachpress_pub p ORDER BY year DESC", $output_type);
    return $result;
}

/**
 * Add a tag
 * @param string $name          --> the new tag
 * @return int                  --> the id of the created element
 * @since 3.1.7
 */
function tp_add_tag($name) {
    global $wpdb;
    global $teachpress_tags;
    $wpdb->insert($teachpress_tags, array('name' => $name), array('%s'));
    return $wpdb->insert_id;
}

/**
 * Delete relations between tags ans publications
 * @param array $delbox
 * @since 3.1.7
 */
function tp_delete_tag_relation($delbox) {
    global $wpdb;
    global $teachpress_relation;
    for ( $i = 0; $i < count($delbox); $i++ ) {
        $delbox[$i] = intval($delbox[$i]);
        $wpdb->query( "DELETE FROM $teachpress_relation WHERE `con_id` = $delbox[$i]" );
    }
}

/**
 * Add a relation between a tag and a publication
 * @param int $pub_id
 * @param int $tag_id
 * @return int
 * @since 3.1.7
 */
function tp_add_tag_relation($pub_id, $tag_id) {
    global $wpdb;
    global $teachpress_relation;
    $wpdb->insert($teachpress_relation, array('pub_id' => $pub_id, 'tag_id' => $tag_id), array('%d', '%d'));
    return $wpdb->insert_id;
}

/**
 * Get data of a single course
 * @param int $id
 * @param string $output_type
 * @return object
 * @since 3.1.7
 */
function get_tp_course($id, $output_type = OBJECT) {
    global $wpdb;
    global $teachpress_courses;
    $id = intval($id);
    $result = $wpdb->get_row("SELECT * FROM $teachpress_courses WHERE `course_id` = '$id'", $output_type);
    return $result;
}

/**
 * Get the number of free places in a course
 * @param int $course_id
 * @param int $places
 * @return int
 * @since 3.1.7
 */
function get_tp_course_free_places($course_id, $places) {
    global $wpdb;
    global $teachpress_signup;
    $course_id = intval($course_id);
    $used_places = $wpdb->get_var("SELECT COUNT(`course_id`) FROM $teachpress_signup WHERE `course_id` = '$course_id' AND `waitinglist` = 0");
    return ($places - $used_places);
}

/**
 * Get data of a student
 * @param string $id
 * @return object
 * @since 3.1.7
 */
function get_tp_student($id) {
    global $wpdb;
    global $teachpress_stud;
    $id = intval($id);
    $result = $wpdb->get_row("Select * FROM $teachpress_stud WHERE `wp_id` = '$id'");
    return $result;
}

/**
 * Return true if the user is subscribed in the course or false of not
 * @param integer course_id
 * @param boolean consider_subcourses   --> 
 * @return boolean
 * @since 3.1.7
 */
function tp_is_user_subscribed ($course_id, $consider_subcourses = false) {
    global $wpdb;
    global $teachpress_signup;
    global $teachpress_courses;
    global $user_ID;
    get_currentuserinfo();
    $course_id = intval($course_id);
    if ( $course_id == 0 ) {
        return false;
    }
    // simple case
    if ( $consider_subcourses == false ) {
        $test = $wpdb->query("SELECT `con_id` FROM $teachpress_signup WHERE `course_id` = '$course_id' AND `wp_id` = '$user_ID' AND `waitinglist` = '0'");
    }
    // consider subcourses
    if ( $consider_subcourses == true ) {
        $where = "";
        $courses = $wpdb->get_results("SELECT `course_id` FROM $teachpress_courses WHERE `parent` = '$course_id'");
        foreach ( $courses as $row ) {
            $where = $where == "" ? "`course_id` = '$row->course_id'" : $where . " OR `course_id` = '$row->course_id'";
        }
        if ( $where != "" ) {
            $where = " WHERE `wp_id` = '$user_ID' AND `waitinglist` = '0' AND ( $where OR `course_id` = '$course_id' )";
            $test = $wpdb->query("SELECT `con_id` FROM $teachpress_signup $where");
        }
        // Fallback if there are no subcourses
        else {
            $test = $wpdb->query("SELECT `con_id` FROM $teachpress_signup WHERE `course_id` = '$course_id' AND `wp_id` = '$user_ID' AND `waitinglist` = '0'");
        }
    }

    if ( $test >= 1 ) {
        return true;
    }
    return false;
}
?>