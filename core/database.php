<?php

/****************/
/* Publications */
/****************/

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
    $result = $wpdb->get_row("SELECT *, DATE_FORMAT(date, '%Y') AS year FROM $teachpress_pub WHERE `pub_id` = '$id'", $output_type);
    return $result;
}

/**
 * Get an array or object of publications
 * @param array $args :
 *      user            --> user IDs (separated by comma)
 *      type            --> type name (separated by comma)
 *      tag             --> tag IDs (separated by comma)
 *      year            --> years (separated by comma)
 *      author          --> author name (separated by comma)
 *      editor          --> editor name (separated by comma)
 *      exclude         --> the id of the publication you want to exclude
 *      order           --> the order of the list
 *      limit           --> the sql search limit, ie: 0,30
 *      search          --> the search string
 *      output type     --> OBJECT, ARRAY_A, ARRAY_N	
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
        'include' => '',
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
    $select = "SELECT DISTINCT p.pub_id, p.title, p.type, p.bibtex, p.author, p.editor, p.date, DATE_FORMAT(p.date, '%Y') AS year, p.urldate, p.isbn , p.url, p.booktitle, p.journal, p.volume, p.number, p.pages, p.publisher, p.address, p.edition, p.chapter, p.institution, p.organization, p.school, p.series, p.crossref, p.abstract, p.howpublished, p.key, p.techtype, p.note, p.is_isbn, p.image_url, p.rel_page 
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
        $search = "p.title LIKE '%$search%' OR p.author LIKE '%$search%' OR p.editor LIKE '%$search%' OR p.isbn LIKE '%$search%' OR p.booktitle LIKE '%$search%' OR p.journal LIKE '%$search%'";
    }

    // define where clause
    $ex = tp_generate_where_clause($exclude, "p.pub_id", "AND", "!=");
    $includes = tp_generate_where_clause($include, "p.pub_id", "AND", "=");
    $types = tp_generate_where_clause($type, "p.type", "OR", "=");
    $users = tp_generate_where_clause($user, "u.user", "OR", "=");
    $tags = tp_generate_where_clause($tag, "b.tag_id", "OR", "=");
    $years = tp_generate_where_clause($year, "year", "OR", "=");
    $authors = tp_generate_where_clause($author, "p.author", "OR", "LIKE", '%');
    $editors = tp_generate_where_clause($editor, "p.editor", "OR", "LIKE", '%');

    if ( $ex != '' ) {
        $where = $where != "" ? $where . " AND $ex " : $ex;
    }
    if ( $includes != '' ) {
        $where = $where != "" ? $where . " AND $includes " : $includes;
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
     $wpdb->insert( $teachpress_pub, array( 'title' => $title, 'type' => $type, 'bibtex' => $bibtex, 'author' => $author, 'editor' => $editor, 'isbn' => $isbn, 'url' => $url, 'date' => $date, 'urldate' => $urldate, 'booktitle' => $booktitle, 'journal' => $journal, 'volume' => $volume, 'number' => $number, 'pages' => $pages , 'publisher' => $publisher, 'address' => $address, 'edition' => $edition, 'chapter' => $chapter, 'institution' => $institution, 'organization' => $organization, 'school' => $school, 'series' => $series, 'crossref' => $crossref, 'abstract' => $abstract, 'howpublished' => $howpublished, 'key' => $key, 'techtype' => $techtype, 'comment' => $comment, 'note' => $note, 'image_url' => $image_url, 'is_isbn' => $is_isbn, 'rel_page' => $rel_page ), array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d' ) );
     $pub_ID = $wpdb->insert_id;
     // Bookmarks
     if ( $bookmark != '' ) {
        for( $i = 0; $i < count( $bookmark ); $i++ ) {
           if ($bookmark[$i] != '' || $bookmark[$i] != 0) {
               tp_add_bookmark($pub_ID, $bookmark[$i]);
           }
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
    $wpdb->update( $teachpress_pub, array( 'title' => $data['title'], 'type' => $data['type'], 'bibtex' => $data['bibtex'], 'author' => $data['author'], 'editor' => $data['editor'], 'isbn' => $data['isbn'], 'url' => $data['url'], 'date' => $data['date'], 'urldate' => $data['urldate'], 'booktitle' => $data['booktitle'], 'journal' => $data['journal'], 'volume' => $data['volume'], 'number' => $data['number'], 'pages' => $data['pages'] , 'publisher' => $data['publisher'], 'address' => $data['address'], 'edition' => $data['edition'], 'chapter' => $data['chapter'], 'institution' => $data['institution'], 'organization' => $data['organization'], 'school' => $data['school'], 'series' => $data['series'], 'crossref' => $data['crossref'], 'abstract' => $data['abstract'], 'howpublished' => $data['howpublished'], 'key' => $data['key'], 'techtype' => $data['techtype'], 'comment' => $data['comment'], 'note' => $data['note'], 'image_url' => $data['image_url'], 'is_isbn' => $data['is_isbn'], 'rel_page' => $data['rel_page'] ), array( 'pub_id' => $pub_ID ), array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d' ), array( '%d' ) );
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
 * @param string $output_type (OBJECT, ARRAY_A or ARRAY_N)
 * @since 3.1.7
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
        'output_type' => OBJECT
    ); 
    $args = wp_parse_args( $args, $defaults );
    extract( $args, EXTR_SKIP );

    global $wpdb;
    global $teachpress_tags;
    $limit = htmlspecialchars($limit);
    $order = htmlspecialchars($order);

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
 * @since 3.1.7
 */
function tp_add_tag($name) {
    global $wpdb;
    global $teachpress_tags;
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
 * @param int $course_id--> ID of the course
 * @param int $places   --> Number of places
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
 * Get the number of free places in more than one course
 * @return array
 * @since 4.0.0
 */
function get_tp_courses_used_places() {
    global $wpdb;
    global $teachpress_signup;
    $free_places = array();
    $sql = "SELECT `course_id`, COUNT(`course_id`) AS used_places FROM $teachpress_signup WHERE `waitinglist` = '0' GROUP BY `course_id`";
    $r = $wpdb->get_results($sql);
    foreach ($r as $r) {
        $free_places[$r->course_id] = $r->used_places;
    }
    return $free_places;
}

/**
 * Get data of courses
 * @param type $args
 * @since 4.0.0
 */
function get_tp_courses ($args) {
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
    global $teachpress_courses;
    
    // Define basics
    $sql = "SELECT `course_id`, `name`, `type`, `lecturer`, `date`, `room`, `places`, `start`, `end`, `semester`, `parent`, `visible`, `parent_name` 
            FROM ( SELECT t.course_id AS course_id, t.name AS name, t.type AS type, t.lecturer AS lecturer, t.date AS date, t.room As room, t.places AS places, t.start AS start, t.end As end, t.semester AS semester, t.parent As parent, t.visible AS visible, p.name AS parent_name 
                   FROM $teachpress_courses t 
                   LEFT JOIN " . $teachpress_courses . " p ON t.parent = p.course_id ) AS temp";
    $where = "";
    $order = esc_sql(htmlspecialchars($order));
    $limit = esc_sql(htmlspecialchars($limit));
    $output_type = htmlspecialchars($output_type);
    $search = esc_sql(htmlspecialchars($search));
    $parent = intval($parent);
    $exclude = tp_generate_where_clause($exclude, "p.pub_id", "AND", "!=");
    $semester = tp_generate_where_clause($semester, "semester", "OR", "=");
    $visibility = tp_generate_where_clause($visibility, "semester", "OR", "=");

    if ( $exclude != '' ) {
        $where = $where != "" ? $where . " AND $exclude " : $exclude;
    }
    if ( $semester != '') {
        $where = $where != "" ? $where . " AND ( $semester )" : $semester;
    }
    if ( $visibility != '') {
        $where = $where != "" ? $where . " AND ( $visibility )" : $visibility;
    }
    if ( $search != '') {
        $where = $where != "" ? $where . " AND ( $search )" : $search ;
    }
    if ( $parent != 0 ) {
        $where = $where != "" ? $where . " AND ( `parent` = '$parent' )" : "`parent` = '$parent'" ;
    }
    if ( $where != '' ) {
        $where = " WHERE $where";
    }
    if ( $limit != '' ) {
        $limit = "LIMIT $limit";
    }
    
    // define global search
    if ( $search != "" ) {
        $search = "`name` like '%$search%' OR `parent_name` like '%$search%' OR `lecturer` like '%$search%' OR `date` like '%$search%' OR `room` like '%$search%' OR `course_id` = '$search'";
    }
    
    // define order
    if ($order != '') {
        $order = " ORDER BY $order";
    }
    
    $result = $wpdb->get_results($sql . $where . $order, $output_type);
    return $result;
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
 * Subscribe a student manually
 * @param int $student      --> ID of the student
 * @param int $course       --> ID of the course
 * @since 4.0.0
*/	
function tp_add_direct_registration($student, $course) {
    global $wpdb;
    global $teachpress_signup;
    $student = intval($student);
    $course = intval($course);
    $wpdb->query( "INSERT INTO $teachpress_signup (`course_id`, `wp_id`, `waitinglist`, `date`) VALUES ('$course', '$student', '0', NOW() )" );
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

/**
 * Get course signups or waitinglist entries
 * @param array $args
 * @return object or array
 * @since 4.0.0
 */
function get_tp_course_signups ($args) {
    $defaults = array(
        'course' => '',
        'waitinglist' => '',
        'order' => '',
        'output_type' => OBJECT
    );
    $args = wp_parse_args( $args, $defaults );
    extract( $args, EXTR_SKIP );
    
    global $wpdb;
    global $teachpress_stud;
    global $teachpress_signup;
    
    $course = htmlspecialchars($course);
    $order = htmlspecialchars($order);
    $output_type = htmlspecialchars($output_type);
    $waitinglist = htmlspecialchars($waitinglist);
    
    if ($order != '') {
        $order = " ORDER BY $order";
    }
    
    $sql = "SELECT DISTINCT st.matriculation_number, st.firstname, st.lastname, st.course_of_studies, st.userlogin, st.email , s.date, s.con_id, s.waitinglist
           FROM $teachpress_signup s
           INNER JOIN $teachpress_stud st ON st.wp_id = s.wp_id
           WHERE s.course_id = '$course'";
    $where = '';
    
    if ( $waitinglist !== '' ) {
        $where = "  AND s.waitinglist = '$waitinglist'";
    }
    $result = $wpdb->get_results($sql . $where . $order, $output_type);
    return $result;
}

/** 
 * Delete registration
 * @param ARRAY $checkbox   --> An array with course IDs
*/
function tp_delete_registration($checkbox) {
    global $wpdb;
    global $teachpress_signup;
    if ( $checkbox == '' ) {
        return false;
    }
    for( $i = 0; $i < count( $checkbox ); $i++ ) {
        $checkbox[$i] = intval($checkbox[$i]);
        // select the course_ID
        $row1 = "SELECT `course_id` FROM " . $teachpress_signup . " WHERE `con_id` = '$checkbox[$i]'";
        $row1 = $wpdb->get_results($row1);
        foreach ($row1 as $row1) {
            // check if there are users in the waiting list
            $sql = "SELECT `con_id` FROM $teachpress_signup WHERE `course_id` = '" . $row1->course_id . "' AND `waitinglist` = '1' ORDER BY `con_id` ASC LIMIT 0, 1";
            $con_id = $wpdb->get_var($sql);
            // if is true subscribe the first one in the waiting list for the course
            if ($con_id != 0 && $con_id != "") {
                $wpdb->query( "UPDATE $teachpress_signup SET `waitinglist` = '0' WHERE `con_id` = '$con_id'" );
            }	
        }
        $wpdb->query( "DELETE FROM $teachpress_signup WHERE `con_id` = '$checkbox[$i]'" );
    }
}

/** 
 * Change the status of one or more course signups
 * @param array $checkbox   --> IDs of the signups
 * @param string $status    --> the new status for the signups (course or waitinglist)
 * @since 4.0.0
*/
function tp_change_signup_status($checkbox, $status = 'course') {
    global $wpdb;
    global $teachpress_signup;
    $status = $status == 'course' ? 0 : 1;
    for( $i = 0; $i < count( $checkbox ); $i++ ) {
        $checkbox[$i] = intval($checkbox[$i]);
        $wpdb->update( $teachpress_signup, array ( 'waitinglist' => $status ), array ( 'con_id' => $checkbox[$i] ), array ( '%d'), array ( '%d' ) );
    }
}

/************/
/* Students */
/************/

/**
 * Get data of a student
 * @param string $id            ID of the student/user
 * @param string $output_type   OBJECT, ARRAY_A or ARRAY_N
 * @return object
 * @since 3.1.7
 */
function get_tp_student ($id, $output_type = OBJECT) {
    global $wpdb;
    global $teachpress_stud;
    $id = intval($id);
    $result = $wpdb->get_row("Select * FROM $teachpress_stud WHERE `wp_id` = '$id'", $output_type);
    return $result;
}

/**
 * Get data of all students
 * @param array $args
 * @return object or array
 * @since 4.0.0
 */
function get_tp_students ($args) {
    $defaults = array(
        'coure_of_studies' => '',
        'search' => '',
        'order' => '`lastname` ASC, `firstname` ASC',
        'limit' => '',
        'output_type' => OBJECT,
        'count' => false
    );
    $args = wp_parse_args( $args, $defaults );
    extract( $args, EXTR_SKIP );
    
    global $wpdb;
    global $teachpress_stud;
    
    $select = "SELECT * FROM $teachpress_stud";
    $where = "";
    $order = htmlspecialchars($order);
    $limit = htmlspecialchars($limit);
    $output_type = htmlspecialchars($output_type);
    $search = esc_sql(htmlspecialchars($search));
    
    // define global search
    if ( $search != "" ) {
        $search = "`matriculation_number` like '%$search%' OR `wp_id` like '%$search%' OR `firstname` LIKE '%$search%' OR `lastname` LIKE '%$search%' OR `userlogin` LIKE '%$search%'";
    }
    
    // if the user needs only the number of rows
    if ( $count == true ) {
        $select = "SELECT COUNT(`wp_id`) AS `count` FROM $teachpress_stud";
    }

    // define where clause
    $course_of_studies = tp_generate_where_clause($course_of_studies, "course_of_studies", "OR", "=");

    if ( $course_of_studies != '') {
        $where = $where != "" ? $where . " AND ( $course_of_studies )" : $course_of_studies;
    }
    if ( $search != '') {
        $where = $where != "" ? $where . " AND ( $search )" : $search ;
    }
    if ( $where != '' ) {
        $where = " WHERE $where";
    }
    if ( $limit != '' ) {
        $limit = "LIMIT $limit";
    }

    // End
    $sql = $select . $where . " ORDER BY $order $limit";
    $sql = $count == false ? $wpdb->get_results($sql, $output_type): $wpdb->get_var($sql);
    return $sql;
}

/**
 * Get all signups of a student
 * @param int $user_id      --> the user id
 * @param string $mode      --> all, reg or wtl
 * @return array or object 
 * @since 4.0.0
 */
function get_tp_student_signups ($user_id, $mode = 'all', $output_type = OBJECT) {
    global $wpdb;
    global $teachpress_signup;
    global $teachpress_courses;
    $user_id = intval($user_id);
    $sql = "SELECT wp_id, course_id, signup_id, waitinglist, name, type, room, date, semester, parent_name FROM (SELECT k.wp_id as wp_id, k.course_id as course_id, k.con_id as signup_id, k.waitinglist as waitinglist, v.name as name, v.type as type, v.room as room, v.date as date, v.semester as semester, p.name as parent_name FROM $teachpress_signup k INNER JOIN $teachpress_courses v ON k.course_id = v.course_id LEFT JOIN $teachpress_courses p ON v.parent = p.course_id ) AS temp WHERE `wp_id` = '$user_id'";
    if ( $mode == 'reg' ) {
        $sql = $sql . " AND `waitinglist` = '0'";
    }
    if ( $mode == 'wtl' ) {
        $sql = $sql . " AND `waitinglist` = '1'";
    }
    $sql = $sql . " ORDER BY signup_id DESC";
    $result = $wpdb->get_results($sql, $output_type);
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

/**
 * Return true if the user is subscribed in the course or false of not
 * @param integer course_id
 * @param boolean consider_childcourses   --> 
 * @return boolean
 * @since 3.1.7
 */
function tp_is_user_subscribed ($course_id, $consider_childcourses = false) {
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
    if ( $consider_childcourses == false ) {
        $test = $wpdb->query("SELECT `con_id` FROM $teachpress_signup WHERE `course_id` = '$course_id' AND `wp_id` = '$user_ID' AND `waitinglist` = '0'");
    }
    // consider child courses
    if ( $consider_childcourses == true ) {
        $where = "";
        $courses = $wpdb->get_results("SELECT `course_id` FROM $teachpress_courses WHERE `parent` = '$course_id'");
        foreach ( $courses as $row ) {
            $where = $where == "" ? "`course_id` = '$row->course_id'" : $where . " OR `course_id` = '$row->course_id'";
        }
        if ( $where != "" ) {
            $where = " WHERE `wp_id` = '$user_ID' AND `waitinglist` = '0' AND ( $where OR `course_id` = '$course_id' )";
            $test = $wpdb->query("SELECT `con_id` FROM $teachpress_signup $where");
        }
        // Fallback if there are no child courses
        else {
            $test = $wpdb->query("SELECT `con_id` FROM $teachpress_signup WHERE `course_id` = '$course_id' AND `wp_id` = '$user_ID' AND `waitinglist` = '0'");
        }
    }

    if ( $test >= 1 ) {
        return true;
    }
    return false;
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
 * @param string $order
 * @param string $output_type
 * @return object
 * @since 4.0.0
 */
function get_tp_settings($category, $order = "`setting_id` DESC", $output_type = OBJECT) {
    global $wpdb;
    global $teachpress_settings;
    $category = htmlspecialchars($category);
    $order = htmlspecialchars($order);
    $output_type = htmlspecialchars($output_type);
    $result = $wpdb->get_results("SELECT `value` FROM $teachpress_settings WHERE `category` = '$category' ORDER BY $order", $output_type);
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
?>