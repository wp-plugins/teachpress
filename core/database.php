<?php

/****************/
/* Publications */
/****************/

/**
 * Get a single publication
 * @param int $id
 * @param string $output_type (OBJECT, ARRAY_A or ARRAY_N)
 * @return object
 * @since 3.2.0 
 */
function get_tp_publication($id, $output_type = OBJECT) {
    global $wpdb;
    global $teachpress_pub;
    $id = intval($id);
    $result = $wpdb->get_row("SELECT * FROM $teachpress_pub WHERE `pub_id` = '$id'", $output_type);
    return $result;
}

/**
 * Get an array or object of publications
 * @param array $args
 * @since 3.2.0
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
        'output_type' => 'ARRAY_A'
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

    // additional joins
    if ( $user != '' ) {
        $join = $join . "INNER JOIN " . $teachpress_user . " u ON u.pub_id= b.pub_id";
    }
    if ( $tag != '' ) {
        $join = $join . "INNER JOIN $teachpress_tags t ON t.tag_id = b.tag_id";
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
    $order = substr($order, 0, -2);

    // define global search
    if ( $search != "" ) {
        $search = esc_sql(htmlspecialchars($search));
        $search = "p.name LIKE '`%$search%' OR p.author LIKE '%$search%' OR p.editor LIKE '%$search%' OR p.isbn LIKE '%$search%' OR p.booktitle LIKE '%$search%' OR p.journal LIKE '%$search%'";
    }

    // define where clause
    $ex = tp_generate_where_clause($exclude, "p.pub_id", "AND", "!=");
    $types = tp_generate_where_clause($type, "p.type", "OR", "=");
    $users = tp_generate_where_clause($user, "u.user", "OR", "=");
    $tags = tp_generate_where_clause($tag, "b.tag_id", "OR", "=");
    $years = tp_generate_where_clause($year, "year", "OR", "=");
    $authors = tp_generate_where_clause($author, "p.author", "OR", "=");
    $editors = tp_generate_where_clause($editor, "p.editor", "OR", "=");

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
    echo $sql;
    $sql = $wpdb->get_results($sql, $output_type);
    return $sql;
}

/** 
 * Add a publication
 * @param array_a $data
 * @param string $tags
 * @param array $bookmark
 * @return INT                  --> id of the new publication
*/
function tp_add_publication($data, $tags, $bookmark) {
     global $wpdb;
     global $teachpress_pub;
     global $teachpress_tags; 
     global $teachpress_relation;
     $wpdb->insert( $teachpress_pub, array( 'name' => $data['name'], 'type' => $data['type'], 'bibtex' => $data['bibtex'], 'author' => $data['author'], 'editor' => $data['editor'], 'isbn' => $data['isbn'], 'url' => $data['url'], 'date' => $data['date'], 'booktitle' => $data['booktitle'], 'journal' => $data['journal'], 'volume' => $data['volume'], 'number' => $data['number'], 'pages' => $data['pages'] , 'publisher' => $data['publisher'], 'address' => $data['address'], 'edition' => $data['edition'], 'chapter' => $data['chapter'], 'institution' => $data['institution'], 'organization' => $data['organization'], 'school' => $data['school'], 'series' => $data['series'], 'crossref' => $data['crossref'], 'abstract' => $data['abstract'], 'howpublished' => $data['howpublished'], 'key' => $data['key'], 'techtype' => $data['techtype'], 'comment' => $data['comment'], 'note' => $data['note'], 'image_url' => $data['image_url'], 'is_isbn' => $data['is_isbn'], 'rel_page' => $data['rel_page'] ), array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d' ) );
     $pub_ID = $wpdb->insert_id;
     // Bookmarks
     for( $i = 0; $i < count( $bookmark ); $i++ ) {
        if ($bookmark[$i] != '' || $bookmark[$i] != 0) {
            tp_add_bookmark($pub_ID, $bookmark[$i]);
        }
     }
     $array = explode(",",$tags);
     foreach($array as $element) {
        $element = trim($element);
        if ($element != '') {
            $element = htmlspecialchars($element);
            $check = $wpdb->get_var("SELECT `tag_id` FROM $teachpress_tags WHERE `name` = '$element'");
            // if tag not exist
            if ($check == 0){
                $check = tp_add_tag($element);
            }
            // add releation between publication and tag
            $test = $wpdb->query("SELECT `pub_id` FROM $teachpress_relation WHERE `pub_id` = '$pub_ID' AND `tag_id` = '$check'");
            if ($test == 0) {
                tp_add_tag_relation($pub_ID, $check);
            }
        }	
     }
     return $pub_ID;
}

/** 
 * Delete publications
 * @param array $checkbox       --> An array with IDs of publication
*/
function tp_delete_publications($checkbox){	
    global $wpdb;
    global $teachpress_pub; 
    global $teachpress_relation;
    global $teachpress_user;
    for( $i = 0; $i < count( $checkbox ); $i++ ) {
        $checkbox[$i] = intval($checkbox[$i]);
        $wpdb->query( "DELETE FROM $teachpress_pub WHERE `pub_id` = $checkbox[$i]" );
        $wpdb->query( "DELETE FROM $teachpress_relation WHERE `pub_id` = $checkbox[$i]" );
        $wpdb->query( "DELETE FROM $teachpress_user WHERE `pub_id` = $checkbox[$i]" );
    }
}	

/** 
 * Edit a publication
 * @param int $pub_ID           --> ID of the publication
 * @param array_a $data         --> Publication data
 * @param array $bookmark       --> An array with WP_USER_IDs
 * @param array $delbox         --> An array with tag IDs you want to delete
 * @param string $tags          --> A string of Tags seperate by comma
*/
function tp_change_publication($pub_ID, $data, $bookmark, $delbox, $tags) {
    global $wpdb;
    global $teachpress_pub;
    global $teachpress_tags;
    global $teachpress_relation;
    $pub_ID = intval($pub_ID);
    // update row
    $wpdb->update( $teachpress_pub, array( 'name' => $data['name'], 'type' => $data['type'], 'bibtex' => $data['bibtex'], 'author' => $data['author'], 'editor' => $data['editor'], 'isbn' => $data['isbn'], 'url' => $data['url'], 'date' => $data['date'], 'booktitle' => $data['booktitle'], 'journal' => $data['journal'], 'volume' => $data['volume'], 'number' => $data['number'], 'pages' => $data['pages'] , 'publisher' => $data['publisher'], 'address' => $data['address'], 'edition' => $data['edition'], 'chapter' => $data['chapter'], 'institution' => $data['institution'], 'organization' => $data['organization'], 'school' => $data['school'], 'series' => $data['series'], 'crossref' => $data['crossref'], 'abstract' => $data['abstract'], 'howpublished' => $data['howpublished'], 'key' => $data['key'], 'techtype' => $data['techtype'], 'comment' => $data['comment'], 'note' => $data['note'], 'image_url' => $data['image_url'], 'is_isbn' => $data['is_isbn'], 'rel_page' => $data['rel_page'] ), array( 'pub_id' => $pub_ID ), array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d' ), array( '%d' ) );
    // Bookmarks
    if ($bookmark != '') {
        for( $i = 0; $i < count( $bookmark ); $i++ ) {
            if ($bookmark[$i] != '' || $bookmark[$i] != 0) {
                tp_add_bookmark($pub_ID, $bookmark[$i]);
            }
        }
    }
    // Delete tag relations
    if ($delbox != '') {
        tp_delete_tag_relation($delbox);
    }
    $array = explode(",",$tags);
    foreach($array as $element) {
        $element = trim($element);
        if ($element != '') {
            $element = htmlspecialchars($element);
            $check = $wpdb->get_var("SELECT `tag_id` FROM $teachpress_tags WHERE `name` = '$element'");
            // if tag not exist
            if ( $check === NULL ){
                $check = tp_add_tag($element);
            }
            // add releation between publication and tag
            $test = $wpdb->query("SELECT `pub_id` FROM $teachpress_relation WHERE `pub_id` = '$pub_ID' AND `tag_id` = '$check'");
            if ($test == 0) {
                tp_add_tag_relation($pub_ID, $check);
            }
        }	
    }
}

/*********/
/* Years */
/*********/

/**
 * Get an object or array with the years where publications are written
 * @global type $wpdb
 * @global type $teachpress_pub
 * @param string $output_type (OBJECT, ARRAY_A or ARRAY_N)
 */
function get_tp_publication_years($output_type = OBJECT) {
    global $wpdb;
    global $teachpress_pub;
    $result = $wpdb->get_results("SELECT DISTINCT DATE_FORMAT(p.date, '%Y') AS year FROM $teachpress_pub p ORDER BY year DESC", $output_type);
    return $result;
}

/********/
/* Tags */
/********/
    
/**
 * Get an array of all tags
 * @param integer $pub_id 
 */
function get_tp_tags($args) {
    $defaults = array(
        'user' => '',
        'type' => '',
        'pub_id' => '',
        'order' => 'ASC',
        'limit' => '',
        'output_type' => 'ARRAY_A'
    ); 
    $args = wp_parse_args( $args, $defaults );
    extract( $args, EXTR_SKIP );

    global $wpdb;
    global $teachpress_pub;
    global $teachpress_relation;
    global $teachpress_tags;
    global $teachpress_user;

    // Define basics
    $select = "SELECT t.name, t.tag_id FROM $teachpress_tags t ";

    if ( $limit != '' ) {
        $limit = "LIMIT $limit";
    }

    // End
    $sql = $select . $join . $where . " ORDER BY t.name $order $limit";
    $sql = $wpdb->get_results($sql, $output_type);
    return $sql;
}

/**
 * Add a tag
 * @param string $name          --> the new tag
 * @return int                  --> the id of the created element
 * @since 3.2.0
 */
function tp_add_tag($name) {
    global $wpdb;
    global $teachpress_tags;
    $wpdb->query("INSERT INTO $teachpress_tags (`name`) VALUES ('$name')");
    $wpdb->insert($teachpress_tags, array('name' => $name), array('%s'));
    return $wpdb->insert_id;
}

/** 
 * Delete tags
 * @param array $checkbox       --> An array with tag IDs
*/
function tp_delete_tags($checkbox) {
    global $wpdb;
    global $teachpress_relation;
    global $teachpress_tags;
    for( $i = 0; $i < count( $checkbox ); $i++ ) {
        $checkbox[$i] = intval($checkbox[$i]);
        $wpdb->query( "DELETE FROM $teachpress_relation WHERE `tag_id` = $checkbox[$i]" );
        $wpdb->query( "DELETE FROM $teachpress_tags WHERE `tag_id` = $checkbox[$i]" );
    }
}

/** 
 * Edit a tag
 * @param INT $tag_id 
 * @param string $name
*/
function tp_edit_tag($tag_id, $name) {
    global $wpdb;
    global $teachpress_tags;
    $wpdb->update( $teachpress_tags, array( 'name' => $name ), array( 'tag_id' => $tag_id ), array( '%s' ), array( '%d' ) );
}

/**
 * Delete relations between tags ans publications
 * @param array $delbox
 * @since 3.2.0
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
 * @since 3.2.0
 */
function tp_add_tag_relation($pub_id, $tag_id) {
    global $wpdb;
    global $teachpress_relation;
    $wpdb->insert($teachpress_relation, array('pub_id' => $pub_id, 'tag_id' => $tag_id), array('%d', '%d'));
    return $wpdb->insert_id;
}

/*************/
/* Bookmarks */
/*************/

/** 
 * Add a bookmark
 * @param int $pub_id   --> publication id
 * @param int $user     --> user_ID
 * @return int          --> the id of the created element
*/
function tp_add_bookmark($pub_id, $user) {
    global $wpdb;
    global $teachpress_user;
    $pub_id = intval($pub_id);
    $user = intval($user);
    $wpdb->insert($teachpress_user, array('pub_id' => $pub_id, 'user' => $user), array('%d', '%d'));
    return $wpdb->insert_id;
}

/** 
 * Delete a bookmark 
 * @param int $del_id   --> IDs of the publications
 * @param int $user     --> User_ID
*/
function tp_delete_bookmark($del_id) {
    global $wpdb;
    global $teachpress_user;
    $del_id = intval($del_id);
    $wpdb->query( "DELETE FROM $teachpress_user WHERE `bookmark_id` = '$del_id'" );
}

/***********/
/* Courses */
/***********/

/**
 * Get data of a single course
 * @param int $id
 * @param string $output_type
 * @return object
 * @since 3.2.0
 */
function get_tp_course($id, $output_type = OBJECT) {
    global $wpdb;
    global $teachpress_courses;
    $id = intval($id);
    $result = $wpdb->get_row("SELECT * FROM $teachpress_courses WHERE `course_id` = '$id'", $output_type);
    return $result;
}


function get_tp_courses ($args) {
    $defaults = array(
        'semester' => '',
        'visibility' => '',
        'parent' => 0,
        'where' => '',
        'search' => '',
        'exclude' => '',
        'order' => 'semester DESC, name',
        'limit' => '',
        'output_type' => 'ARRAY_A'
    ); 
    $args = wp_parse_args( $args, $defaults );
    extract( $args, EXTR_SKIP );
    
    global $wpdb;
    global $teachpress_courses;
    global $teachpress_signup;
    
    $order_all = $order;
    
    // Define basics
    $select = "SELECT DISTINCT c.course_id, c.name, c.type, c.lecturer, c.date, c.room, c.places, c.start, c.end, c.semester, c.parent, c.visible
               FROM $teachpress_courses c";
    $join = "INNER JOIN $teachpress_signup r ON r.course_id = c.course_id";
    $where = htmlspecialchars($where);
    $limit = '';
    $order = '';
}

/** 
 * Add a new course
 * @param array_a $data
 * @return int              --> ID of the new course
*/
function tp_add_course($data) {
    global $wpdb;
    global $teachpress_courses;
    $data['start'] = $data['start'] . ' ' . $data['start_hour'] . ':' . $data['start_minute'] . ':00';
    $data['end'] = $data['end'] . ' ' . $data['end_hour'] . ':' . $data['end_minute'] . ':00';
    $wpdb->insert( $teachpress_courses, array( 'name' => $data['name'], 'type' => $data['type'], 'room' => $data['room'], 'lecturer' => $data['lecturer'], 'date' => $data['date'], 'places' => $data['places'], 'start' => $data['start'], 'end' => $data['end'], 'semester' => $data['semester'], 'comment' => $data['comment'], 'rel_page' => $data['rel_page'], 'parent' => $data['parent'], 'visible' => $data['visible'], 'waitinglist' => $data['waitinglist'], 'image_url' => $data['image_url'], 'strict_signup' => $data['strict_signup'] ), array( '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%s', '%d' ) );
    return $wpdb->insert_id;
}
	
/**
 * Delete course
 * @param array $checkbox   --> IDs of the courses
 */
function tp_delete_course($checkbox){
    global $wpdb;
    global $teachpress_courses;
    global $teachpress_signup;
    for( $i = 0; $i < count( $checkbox ); $i++ ) { 
        $checkbox[$i] = intval($checkbox[$i]); 
        $wpdb->query( "DELETE FROM $teachpress_courses WHERE `course_id` = $checkbox[$i]" );
        $wpdb->query( "DELETE FROM $teachpress_signup WHERE `course_id` = $checkbox[$i]" );
        // Check if there are parent courses, which are not selected for erasing, and set there parent to default
        $sql = "SELECT `course_id` FROM $teachpress_courses WHERE `parent` = $checkbox[$i]";
        $test = $wpdb->query($sql);
        if ($test != '0') {
            $row = $wpdb->get_results($sql);
            foreach ($row as $row) {
                if ( !in_array($row->course_id, $checkbox) ) {
                $wpdb->update( $teachpress_courses, array( 'parent' => 0 ), array( 'course_id' => $row->course_id ), array('%d' ), array( '%d' ) );
                }
            }
        }
    }
}
	
/** 
 * Change a course
 * @param int $course_ID    --> course ID
 * @param array_a $data
*/ 
function tp_change_course($course_ID, $data){
    global $wpdb;
    global $teachpress_courses;
    global $teachpress_signup;
    $course_ID = intval($course_ID);
    $old_places = get_tp_course_data ($course_ID, 'places');

    // handle the number of free places
    if ( $data['places'] > $old_places ) {
        $new_free_places = $data['places'] - $old_places;
        // subscribe students from the waiting list automatically
        $sql = "SELECT s.con_id, s.waitinglist, s.date
                FROM " . $teachpress_signup . " s 
                INNER JOIN " . $teachpress_courses . " c ON c.course_id=s.course_id
                WHERE c.course_id = '$course_ID' AND s.waitinglist = '1' ORDER BY s.date ASC";
        $waitinglist = $wpdb->get_results($sql, ARRAY_A);
        $count_waitinglist = count($waitinglist);
        if ( $count_waitinglist > 0 ) {
            foreach ( $waitinglist as $waitinglist ) {
                if ( $new_free_places > 0 ) {
                    $wpdb->update( $teachpress_signup, array ( 'waitinglist' => 0 ), array ( 'con_id' => $waitinglist["con_id"] ), array ( '%d' ), array ( '%d' ) );
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
    $wpdb->update( $teachpress_courses, array( 'name' => $data['name'], 'type' => $data['type'], 'room' => $data['room'], 'lecturer' => $data['lecturer'], 'date' => $data['date'], 'places' => $data['places'], 'start' => $data['start'], 'end' => $data['end'], 'semester' => $data['semester'], 'comment' => $data['comment'], 'rel_page' => $data['rel_page'], 'parent' => $data['parent'], 'visible' => $data['visible'], 'waitinglist' => $data['waitinglist'], 'image_url' => $data['image_url'], 'strict_signup' => $data['strict_signup'] ), array( 'course_id' => $course_ID ), array( '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%s', '%d' ), array( '%d' ) );
}

/***************/
/* Enrollments */
/***************/

function get_tp_course_signups ($args) {
    $defaults = array(
        'course' => '',
        'waitinglist' => '',
        'order' => '',
        'output_type' => 'ARRAY_A'
    );
    $args = wp_parse_args( $args, $defaults );
    extract( $args, EXTR_SKIP );
    
    global $wpdb;
    global $teachpress_stud;
    global $teachpress_signup;
    
    $course = htmlspecialchars($course);
    
    $sql = "SELECT DISTINCT st.matriculation_number, st.firstname, st.lastname, st.course_of_studies, st.userlogin, st.email , s.date, s.con_id, s.waitinglist
           FROM $teachpress_signup s
           INNER JOIN $teachpress_stud st ON st.wp_id = s.wp_id
           WHERE s.course_id = '$course'";
    $where = '';
    
    if ( $waitinglist != '' ) {
        $where = "  AND s.waitinglist = '$waitinglist'";
    }		
    $result = $wpdb->get_results($sql . $where, $output_type);
    return $result;
}

/************/
/* Students */
/************/

/**
 * Get data of a registered student
 * @param string $id
 * @return object
 * @since 3.2.0
 */
function get_tp_student ($id) {
    global $wpdb;
    global $teachpress_stud;
    $id = intval($id);
    $result = $wpdb->get_row("Select * FROM $teachpress_stud WHERE `wp_id` = '$id'");
    return $result;
}

/** 
 * Delete student
 * @param array $checkbox       --> ID of the enrollment
 * @param int $user_ID          --> User ID
*/ 
function tp_delete_student($checkbox, $user_ID){
    global $wpdb;
    global $teachpress_stud; 
    global $teachpress_signup;
    $user_ID = intval($user_ID);
    for( $i = 0; $i < count( $checkbox ); $i++ ) {
        $checkbox[$i] = intval($checkbox[$i]);
        // search courses where the user was registered
        $row1 = $wpdb->get_results("SELECT `course_id` FROM $teachpress_signup WHERE `wp_id` = '$checkbox[$i]'");
        foreach ($row1 as $row1) {
            // check if there are users in the waiting list
            $sql = "SELECT `con_id` FROM $teachpress_signup WHERE `course_id` = '" . $row1->course_id . "' AND `waitinglist` = '1' ORDER BY `con_id` ASC LIMIT 0, 1";
            $con_id = $wpdb->get_var($sql);
            // if is true subscribe the first one in the waiting list for the course
            if ($con_id != 0 && $con_id != "") {
                $wpdb->query( "UPDATE $teachpress_signup SET `waitinglist` = '0' WHERE `con_id` = '$con_id'" );
            }
        }
        $wpdb->query( "DELETE FROM $teachpress_stud WHERE `wp_id` = $checkbox[$i]" );
        $wpdb->query( "DELETE FROM $teachpress_signup WHERE `wp_id` = $checkbox[$i]" );
    }
}

/************/
/* Settings */
/************/

/** 
 * Get a teachPress option
 * @param string $var           --> permalink, sem, db-version, sign_out or login
 * @Return string
*/
function get_tp_option($var) {
    global $wpdb;
    global $teachpress_settings;
    $var = htmlspecialchars($var);
    $result = $wpdb->get_var("SELECT `value` FROM $teachpress_settings WHERE `variable` = '$var'");
    return $result;
}

/**
 * Get all settings of a category
 * @param string $category
 * @return object
 * @since 3.2.0
 */
function get_tp_settings($category) {
    global $wpdb;
    global $teachpress_settings;
    $category = htmlspecialchars($category);
    $result = $wpdb->get_results("SELECT `value` FROM $teachpress_settings WHERE `category` = '$category' ORDER BY `setting_id` DESC");
    return $result;
}

/** 
 * Delete an option
 * @param int $delete
*/
function tp_delete_setting($delete) {
    global $wpdb;
    global $teachpress_settings;
    $delete = intval($delete);		
    $wpdb->query( "DELETE FROM $teachpress_settings WHERE `setting_id` = '$delete'" );
}

/** 
 * Add an option
 * @param string $name
 * @param string $typ
*/
function tp_add_setting($name, $typ) { 
    global $wpdb;
    global $teachpress_settings;
    $wpdb->insert( $teachpress_settings, array( 'variable' => $name, 'value' => $name, 'category' => $typ ), array( '%s', '%s', '%s' ) );
}

/** 
 * Change settings
 * @param ARRAY $options
*/
function tp_change_settings($options) {
    global $wpdb;
    global $teachpress_settings;
    $eintragen = "UPDATE $teachpress_settings SET `value` = '" . $options['semester'] . "' WHERE `variable` = 'sem'";
    $wpdb->query( $eintragen );
    $eintragen = "UPDATE $teachpress_settings SET `value` = '" . $options['permalink'] . "' WHERE `variable` = 'permalink'";
    $wpdb->query( $eintragen );
    $eintragen = "UPDATE $teachpress_settings SET `value` = '" . $options['rel_page_courses'] . "' WHERE `variable` = 'rel_page_courses'";
    $wpdb->query( $eintragen );
    $eintragen = "UPDATE $teachpress_settings SET `value` = '" . $options['rel_page_publications'] . "' WHERE `variable` = 'rel_page_publications'";
    $wpdb->query( $eintragen );
    $eintragen = "UPDATE $teachpress_settings SET `value` = '" . $options['stylesheet'] . "' WHERE `variable` = 'stylesheet'";
    $wpdb->query( $eintragen );
    $eintragen = "UPDATE $teachpress_settings SET `value` = '" . $options['sign_out'] . "' WHERE `variable` = 'sign_out'";
    $wpdb->query( $eintragen );
    $eintragen = "UPDATE $teachpress_settings SET `value` = '" . $options['matriculation_number'] . "' WHERE `variable` = 'regnum'";
    $wpdb->query( $eintragen );
    $eintragen = "UPDATE $teachpress_settings SET `value` = '" . $options['course_of_studies'] . "' WHERE `variable` = 'studies'";
    $wpdb->query( $eintragen );
    $eintragen = "UPDATE $teachpress_settings SET `value` = '" . $options['semesternumber'] . "' WHERE `variable` = 'termnumber'";
    $wpdb->query( $eintragen );
    $eintragen = "UPDATE $teachpress_settings SET `value` = '" . $options['birthday'] . "' WHERE `variable` = 'birthday'";
    $wpdb->query( $eintragen );
    $eintragen = "UPDATE $teachpress_settings SET `value` = '" . $options['login'] . "' WHERE `variable` = 'login'";
    $wpdb->query( $eintragen );
    tp_update_userrole($options['userrole']);
}

/********/
/* Misc */
/********/
    
/**
 * Generate a where clause
 * @param string $input
 * @param string $column
 * @param string $connector
 * @param string $operator
 * @return string
 * @since 3.2.0
 */
function tp_generate_where_clause($input, $column, $connector = "AND", $operator = "=") {
    $end = "";
    if ($input != "") {
        $array = explode(",", $input);
        foreach ( $array as $element ) {
            $element = trim ($element);
            $element = htmlspecialchars($element);
            if ( $element != "" ) {
                $end = $end == "" ? "$column $operator '$element'" : $end . " $connector $column $operator '$element'";
            }
        }
    }
    return $end;
}
?>
