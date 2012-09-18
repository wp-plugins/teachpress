<?php

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