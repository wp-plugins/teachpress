<?php
/**
 * This file contains frontend functions for the enrollment forms of teachpress
 * @package teachpress/core
 */

/**
 * This class contains all special functions for the shortcode [tpenrollments]
 * @since 5.0.0
 */
class tp_enrollments {
    
    /**
     * Add signups for a student
     * @param int $user_id
     * @param array $checkbox
     * @return string
     * @since 5.0.0
     */
    public static function add_signups ($user_id, $checkbox) {
        global $wpdb;
        $max = count( $checkbox );
        for ($n = 0; $n < $max; $n++) {
            $row = $wpdb->get_row("SELECT `name`, `parent` FROM " . TEACHPRESS_COURSES . " WHERE `course_id` = '$checkbox[$n]'");
            if ($row->parent != '0') {
                $parent = tp_courses::get_course_data($row->parent, 'name');
                $row->name = ( $row->name != $parent ) ? $parent . ' ' . $row->name : $row->name;
            }
            $code = tp_enrollments::add_signup($checkbox[$n], $user_id);
            tp_enrollments::send_notification($code, $user_id, $row->name);
            $message = tp_enrollments::get_signup_message($code);
            if ($code === 201) { $class = 'teachpress_message_success'; }
            elseif ($code === 202) { $class = 'teachpress_message_info'; }
            else { $class = 'teachpress_message_error'; }
            return '<div class="' . $class . '">&quot;' . stripslashes($row->name) . '&quot;: ' . $message . '</div>';
        }
        
    }
    
    /** 
     * Add signup (= subscribe student in a course)
     * @param int $checkbox     course_ID
     * @param int $wp_id        user_ID
     * @return int      This function returns a status code. This means:
     *                  code 0    --> ERROR: course_ID was 0
     *                  code 101  --> user is already registered
     *                  code 102  --> user is already registered in waitinglist
     *                  code 103  --> user is already registered for an other course of the course group
     *                  code 104  --> no free places availablea
     *                  code 201  --> registration was successful
     *                  code 202  --> registration was successful for waitinglist
     * @since 5.0.0
     * @access private
    */
    private static function add_signup($checkbox, $wp_id){
       global $wpdb;
       $checkbox = intval($checkbox);
       $wp_id = intval($wp_id);
       if ( $checkbox == 0 ) {
            return 0;
       }
       // Start transaction
       $wpdb->query("SET AUTOCOMMIT=0");
       $wpdb->query("START TRANSACTION");
       // Check if the user is already registered
       $check = $wpdb->get_var("SELECT `waitinglist` FROM " . TEACHPRESS_SIGNUP . " WHERE `course_id` = '$checkbox' and `wp_id` = '$wp_id'");
       if ( $check != NULL && $check == '0' ) {
            $wpdb->query("ROLLBACK");
            return 101;
       } 
       if ( $check != NULL && $check == '1' ) {
            $wpdb->query("ROLLBACK");
            return 102;
       }
       // Check if there is a strict signup
       $row1 = $wpdb->get_row("SELECT `places`, `waitinglist`, `parent` FROM " . TEACHPRESS_COURSES . " WHERE `course_id` = '$checkbox'");
       if ( $row1->parent != 0 ) {
            $check = tp_courses::get_course_data($row1->parent, 'strict_signup');
            if ( $check != 0 ) {
                 $check2 = $wpdb->query("SELECT c.course_id FROM " . TEACHPRESS_COURSES . " c INNER JOIN " . TEACHPRESS_SIGNUP . " s ON s.course_id = c.course_id WHERE c.parent = '$row1->parent' AND s.wp_id = '$wp_id' AND s.waitinglist = '0'");
                 if ( $check2 != NULL ) {
                     $wpdb->query("ROLLBACK");
                     return 103;
                 }
            }
       }
       // Check if there are free places available
       $used_places = $wpdb->query("SELECT `course_id` FROM " . TEACHPRESS_SIGNUP . " WHERE `course_id` = '$checkbox' AND `waitinglist` = 0");
       if ($used_places < $row1->places ) {
            // Subscribe
            $wpdb->query("INSERT INTO " . TEACHPRESS_SIGNUP . " (`course_id`, `wp_id`, `waitinglist`, `date`) VALUES ('$checkbox', '$wp_id', '0', NOW() )");
            $wpdb->query("COMMIT");
            return 201;
       }
       else {
            // if there is a waiting list available
            if ($row1->waitinglist == '1') {
                  $wpdb->query( "INSERT INTO " . TEACHPRESS_SIGNUP . " (course_id, wp_id, waitinglist, date) VALUES ('$checkbox', '$wp_id', '1', NOW() )" );
                  $wpdb->query("COMMIT");
                  return 202;
            }
            else {
                $wpdb->query("ROLLBACK");
                return 104;
            }
       }
    }
    
    /**
     * Add student
     * @param int $user_ID
     * @param string $user_login
     * @param string $user_email
     * @return string
     * @since 5.0.0
     */
    public static function add_student ($user_ID, $user_login, $user_email, $fields, $post) {
        $data = array(
         'firstname' => isset($post['firstname']) ? htmlspecialchars($post['firstname']) : '',
         'lastname' => isset($post['lastname']) ? htmlspecialchars($post['lastname']) : '',
         'userlogin' => $user_login,
         'email' => $user_email
        );
        $ret = tp_students::add_student($user_ID, $data);
        if ($ret !== false) {
            tp_enrollments::add_student_meta( $user_ID, $fields, $post );
            return '<div class="teachpress_message_success"><strong>' . __('Registration successful','teachpress') . '</strong></div>';
        }
        else {
            return '<div class="teachpress_message_error"><strong>' . __('Error: User already exist','teachpress') . '</strong></div>';
        }
        
    }
    
    /**
     * Add student meta
     * @param int $user_id
     * @param array $fields
     * @param array $post
     * @since 5.0.0
     */
    public static function add_student_meta ($user_id, $fields, $post) {
        foreach ($fields as $row) {
            if ( !isset( $post[$row['variable']] ) && !isset( $post[$row['variable'] . '_day'] ) ) {
                continue;
            }
            
            $column_info = tp_db_helpers::extract_column_data($row['value']);
            if ( $column_info['type'] === 'DATE' ) {
                $day = intval( $post[$row['variable'] . '_day'] );
                $day2 = ( $day < 10 ) ? '0' . $day : $day;
                $value = $post[$row['variable'] . '_year'] . '-' . $post[$row['variable'] . '_month'] . '-' . $day2;
            }
            else {
                $value = $post[$row['variable']];
            }
            tp_students::add_student_meta( $user_id, $row['variable'], htmlspecialchars($value) );
        }
    }

    /**
     * Get registration message
     * @param int $code
     * @return boolean 
     * @since 5.0.0
     */
    public static function get_signup_message($code) {
        switch ($code) {
        case 0:
            return __('Warning: Wrong course_ID','teachpress');
        case 101:
            return __('You are already registered for this course.','teachpress');
        case 102:
            return __('Registration is not possible, because you are already registered in the waitinglist.','teachpress');
        case 103:
            return __('Registration is not possible, because you are already registered for an other course of this course group.','teachpress');
        case 104:
            return __('No free places available.','teachpress');
        case 201:
            return __('Registration was successful.','teachpress');
        case 202:
            return __('For this course there are no more free places available. You are automatically signed up in a waiting list.','teachpress');
        default:
            return false;
        }
    }
    
    /** 
     * Unsubscribe a student from a course
     * @param array $checkbox   An array with the registration IDs
     * @return string
     * @since 5.0.0
    */
    public static function delete_signup($checkbox) {
        global $wpdb;
        for( $i = 0; $i < count( $checkbox ); $i++ ) {
            $checkbox[$i] = intval($checkbox[$i]);
            // Select course ID
            $sql = "SELECT `course_id`, `waitinglist` FROM " . TEACHPRESS_SIGNUP . " WHERE `con_id` = '$checkbox[$i]'";
            $course = $wpdb->get_row($sql);
            // Start transaction
            $wpdb->query("SET AUTOCOMMIT=0");
            $wpdb->query("START TRANSACTION");
            // check if there are users in the waiting list
            if ( $course->waitinglist == 0 ) {
                $sql = "SELECT `con_id` FROM " . TEACHPRESS_SIGNUP . " WHERE `course_id` = '$course->course_id' AND `waitinglist` = '1' ORDER BY `con_id` ASC LIMIT 0, 1";
                $con_id = $wpdb->get_var($sql);
                // if is true subscribe the first one in the waiting list for the course
                if ($con_id != 0 && $con_id != '') {
                    $wpdb->query( "UPDATE " . TEACHPRESS_SIGNUP . " SET `waitinglist` = '0' WHERE `con_id` = '$con_id'" );
                }
            }
            $wpdb->query("DELETE FROM " . TEACHPRESS_SIGNUP . " WHERE `con_id` = '$checkbox[$i]'");
            // End transaction
            $wpdb->query("COMMIT");
        }	
        return '<div class="teachpress_message_success">' . __('You are signed out successful','teachpress') . '</div>';
    }
    
    /**
     * Returns a text field for user form
     * @param string $field_name    name/id of the field
     * @param string $label         label for the field
     * @param string $value         value for the field
     * @param boolean $readonly     true or false
     * @return string
     * @since 5.0.0
     */
    public static function get_form_text_field($field_name, $label, $value, $readonly = false) {
        $readonly = ( $readonly === false ) ? '' : 'readonly="true" ';
        return '<tr>
                    <td><label for="' . $field_name . '"><b>' . $label . '</b></label></td>
                    <td><input name="' . $field_name . '" type="text" id="' . $field_name . '" value="' . $value . '" size="50" ' . $readonly . '/></td>
                 </tr>';
    }
    
    /**
     * Returns a texteara field for user form
     * @param string $field_name    name/id of the field
     * @param string $label         label for the field
     * @param string $value         value for the field
     * @return string
     * @since 5.0.0
     */
    public static function get_form_textarea_field ($field_name, $label, $value) {
        return '<tr>
                    <td><label for="' . $field_name . '"><b>' . $label . '</b></label></td>
                    <td><textarea name="' . $field_name . '" id="' . $field_name . '" style="width:100%; height:80px;">' . $value . '</textarea></td>
                 </tr>';
    }
    
    /**
     * Returns a select box for user form
     * @global type $wpdb
     * @param string $field_name    name/id of the field
     * @param string $label         label for the field
     * @param string $value         value for the field
     * @return string
     * @since 5.0.0
     */
    public static function get_form_select_field ($field_name, $label, $value) {
        global $wpdb;
        $return = '';
        $return .= '<tr>';
        $return .= '<td><label for="' . $field_name . '"><b>' . $label . '</b></label></td>';
        $return .= '<td><select name="' . $field_name . '" id="' . $field_name . '">';
        $options = $wpdb->get_results("SELECT * FROM " . TEACHPRESS_SETTINGS . " WHERE `category` = '" . $field_name . "' ORDER BY value ASC");
        if ( $value == '' ) {
            $return .= '<option value=""></option>';
        }
        foreach ($options as $opt) {
            $selected = ( $value == $opt->value ) ? 'selected="selected"' : '';
            $return .= '<option value="' . $opt->value . '" ' . $selected . '>' . $opt->value . '</option>';
        }
        $return .= '</select></td>';
        $return .= '</tr>';
        return $return;
    }
    
    /**
     * Returns date select fields for user form
     * @param string $field_name    name/id of the field
     * @param string $label         label for the field
     * @param string $value         value for the field
     * @return string
     * @since 5.0.0
     */
    public static function get_form_date_field ($field_name, $label, $value) {
        if ( $value != '' ) {
            $b = tp_datesplit($value);
        }
        $day = ( $value != '' ) ? $b[0][2] : '01';
        $month = ( $value != '' ) ? $b[0][1] : '01';
        $year = ( $value != '' ) ? $b[0][0] : '19xx';
        $months = array ( __('Jan','teachpress'), __('Feb','teachpress'), __('Mar','teachpress'), __('Apr','teachpress'), __('May','teachpress'), __('Jun','teachpress'), __('Jul','teachpress'), __('Aug','teachpress'), __('Sep','teachpress'), __('Oct','teachpress'), __('Nov','teachpress'), __('Dec','teachpress') );
        $return = '';
        $return .= '<tr>';
        $return .= '<td><b>' . $label . '</b></td>';
        $return .= '<td>';
        $return .= '<input name="' . $field_name . '_day" id="' . $field_name . '_day" type="text" title="Day" size="2" value="' . $day . '"/>';
        $return .= '<select name="' . $field_name . '_month" id="' . $field_name . '_month" title="' . __('Month','teachpress') . '">';
        for ( $i = 1; $i <= 12; $i++ ) {
            $m = ( $i < 10 ) ? '0' . $i : $i;
            $selected = ($month == $m) ? 'selected="selected"' : '';
            $return .= '<option value="' . $m . '" ' . $selected . '>' . $months[$i-1] . '</option>';
        }
        $return .= '</select>';
        $return .= '<input name="' . $field_name . '_year" id="' . $field_name . '_year" type="text" title="' . __('Year','teachpress') . '" size="4" value="' . $year . '"/>';
        $return .= '</td>';
        $return .= '</tr>';
        return $return;
    }
    
    /**
     * Returns the main menu for enrollments
     * @param string $tab
     * @param object $user
     * @return string
     * @since 5.0.0
     */
    public static function get_menu ($tab, $user) {
        global $pagenow;
        
        // No Permalinks: Page or Post?
        $page = (is_page()) ? 'page_id' : 'p';
       
        // Define permalinks
        if ( get_option('permalink_structure') ) {
           $url["link"] = $pagenow;
           $url["link"] = str_replace("index.php", "", $url["link"]);
           $url["link"] = $url["link"] . '?tab=';
        }
        else {
           $url["post_id"] = get_the_ID();
           $url["link"] = $pagenow;
           $url["link"] = str_replace("index.php", "", $url["link"]);
           $url["link"] = $url["link"] . '?' . $page . '=' . $url["post_id"] . '&amp;tab=';
        }
        // Create Tabs
        $tab1 = ( $tab === '' || $tab === 'current' ) ? '<span class="teachpress_active_tab">' . __('Current enrollments','teachpress') . '</span>' : '<a href="' . $url["link"] . 'current">' . __('Current enrollments','teachpress') . '</a>';
        
        $tab2 = ( $tab === 'old' ) ? '<span class="teachpress_active_tab">' . __('Your enrollments','teachpress') . '</span>' : '<a href="' . $url["link"] . 'old">' . __('Your enrollments','teachpress') . '</a>';
        
        $tab3 = ( $tab === 'data' ) ? '<span class="teachpress_active_tab">' . __('Your data','teachpress') . '</span>' : '<a href="' . $url["link"] . 'data">' . __('Your data','teachpress') . '</a>';
        
        $rtn = '<div class="tp_user_menu">
                   <h4>' . __('Hello','teachpress') . ', ' . stripslashes($user['firstname']) . ' ' . stripslashes($user['lastname']) . '</h4>'
                . '<p>' . $tab1 . ' | ' . $tab2 . ' | ' . $tab3 . '</p></div>'; 
        return $rtn;
    }
    
    /**
     * Creates the table for signups/waitinglist entries for old tab
     * @param object $row
     * @param int $is_sign_out
     * @return string
     * @since 5.0.0
     */
    private static function create_signups_table ($row, $is_sign_out) {
        $rtn = '<table class="teachpress_enr_old" border="1" cellpadding="5" cellspacing="0">';
        $rtn .= '<tr>';
        if ($is_sign_out == '0') {
            $rtn .= '<th width="15">&nbsp;</th>';
        }
        $rtn .= '<th>' . __('Name','teachpress') . '</th>
                <th>' . __('Type') . '</th>
                <th>' . __('Date','teachpress') . '</th>
                <th>' . __('Room','teachpress') . '</th>
                <th>' . __('Term','teachpress') . '</th>
               </tr>';
        // Select all courses where user is registered
        if ( count($row) != 0 ) {
            foreach($row as $row1) {
                $row1->parent_name = stripslashes($row1->parent_name);
                $row1->name = stripslashes($row1->name);
                if ($row1->parent_name != '') {
                    $row1->parent_name = $row1->parent_name . ' -';
                }
                $rtn .= '<tr>';
                if ($is_sign_out == '0') {
                    $rtn .= '<td><input name="checkbox2[]" type="checkbox" value="' . $row1->con_id . '" title="' . $row1->name . '" id="ver_' . $row1->con_id . '"/></td>';
                }		
                $rtn .= '<td><label for="ver_' . $row1->con_id . '" style="line-height:normal;" title="' . $row1->parent_name . ' ' .  $row1->name . '">' . $row1->parent_name . ' ' .  $row1->name . '</label></td>
                        <td>' . stripslashes($row1->type) . '</td>
                        <td>' . stripslashes($row1->date) . '</td>
                        <td>' . stripslashes($row1->room) . '</td> 
                        <td>' . stripslashes($row1->semester) . '</td>
                        </tr>';
            }
        }
        else {
            $rtn .= '<tr><td colspan="6">' . __('No enrollments','teachpress') . '</td></tr>';
        }
        $rtn .= '</table>';
        return $rtn;
    }
    
    /**
     * Returns the tab for former signups/waitinglist places
     * @param int $user_ID
     * @param int $is_sign_out
     * @return string
     * @since 5.0.0
     */
    public static function get_old_tab ($user_ID, $is_sign_out) { 
        $rtn = '<p><strong>' . __('Signed up for','teachpress') . '</strong></p>';
        
        // signups
        $row1 = tp_students::get_signups( array('wp_id' => $user_ID, 'mode' => 'reg') );
        $rtn .= tp_enrollments::create_signups_table($row1, $is_sign_out);
        
        // waitinglist entries
        $row2 = tp_students::get_signups( array('wp_id' => $user_ID, 'mode' => 'wtl') );
        if ( count($row2) !== 0 ) {
            $rtn .= '<p><strong>' . __('Waiting list','teachpress') . '</strong></p>';
            $rtn .= tp_enrollments::create_signups_table($row2, $is_sign_out);
        }
        if ($is_sign_out == '0') {
            $rtn .= '<p><input name="austragen" type="submit" value="' . __('unsubscribe','teachpress') . '" id="austragen" /></p>';
        }
        return $rtn;
    }
    
    /**
     * Returns the enrollment tab
     * @param string $sem
     * @param string $user_exists
     * @return string
     * @since 5.0.0
     */
    public static function get_enrollments_tab($sem, $user_exists) {
        global $wpdb;
        $rtn = '';
        // Select all courses where enrollments in the current term are available
        $row = $wpdb->get_results("SELECT * FROM " . TEACHPRESS_COURSES . " WHERE `semester` = '$sem' AND `parent` = '0' AND (`visible` = '1' OR `visible` = '2') ORDER BY `type` DESC, `name`");
        foreach( $row as $row ) {
            $rtn .= tp_enrollments::load_course_entry($row, $user_exists);	
        }	
        if (is_user_logged_in() && $user_exists != '') {
            $rtn .= '<input name="einschreiben" type="submit" value="' . __('Sign up','teachpress') . '" />';
        }
        return $rtn;
    }
    
    /**
     * Returns a table with a course and his sub courses for enrollments tab
     * @param object $row
     * @param string $user_exists
     * @return string
     * @since 5.0.0
     * @access private
     */
    private static function load_course_entry ($row, $user_exists) {
        global $wpdb;
        
        $course_name = ( $row->rel_page != 0 ) ? '<a href="' . get_permalink($row->rel_page) . '">' . stripslashes($row->name) . '</a>' : stripslashes($row->name);

        // load all childs
        $childs = $wpdb->get_results("Select * FROM " . TEACHPRESS_COURSES . " WHERE `parent` = '$row->course_id' AND (`visible` = '1' OR `visible` = '2') AND (`start` != '0000-00-00 00:00:00') ORDER BY `name`");
        
        // leave the function if there is nothing to show
        if ( $row->start == '0000-00-00 00:00:00' && count($childs) === 0 ) {
            return;
        }
        
        // build course string
        $rtn = '<div class="teachpress_course_group">';
        $rtn .= '<div class="teachpress_course_name">' . $course_name . '</div>';
        $rtn .= '<table class="teachpress_enr" width="100%" border="0">';
        $rtn .= tp_enrollments::create_course_entry($row, $user_exists);
        foreach ( $childs as $child ) {
            $rtn .= tp_enrollments::create_course_entry($child, $user_exists, $row->name);
        }
        $rtn .= '</table>';
        $rtn .= '</div>';
        return $rtn;
    }

    /**
     * Returns a single course entry for the function load_course_entry()
     * @param object $row
     * @param string $user_exists
     * @param string $parent_name
     * @return string
     * @since 5.0.0
     * @access private
     */
    private static function create_course_entry ($row, $user_exists, $parent_name = '') {
        
        // define some course variables
        $date1 = $row->start;
        $date2 = $row->end;
        $free_places = tp_courses::get_free_places($row->course_id, $row->places);
        if ( $free_places < 0 ) {
            $free_places = 0;
        }
        
        // Overwrite child name
        if ($parent_name == $row->name) {
            $row->name = $row->type;
        }
        
        // checkbox
        $checkbox = '&nbsp;';
        $checkbox_label = stripslashes($row->type);
        if (is_user_logged_in() && $user_exists != '') {
            if ($date1 != '0000-00-00 00:00:00' && current_time('mysql') >= $date1 && current_time('mysql') <= $date2) {
               $checkbox = '<input type="checkbox" name="checkbox[]" value="' . $row->course_id . '" title="' . stripslashes($row->name) . ' ' . __('Select','teachpress') . '" id="checkbox_' . $row->course_id . '"/>';
               $checkbox_label = '<label for="checkbox_' . $row->course_id . '" style="line-height:normal;">' . stripslashes($row->type) . '</label>';
            } 
        }
        
        // display configs
        $display_free_places = ( $date1 != '0000-00-00 00:00:00' ) ? $free_places . ' ' . __('of','teachpress') . ' ' .  $row->places : '&nbsp;';
        $waitinglist_info = ( $row->waitinglist == 1 && $free_places == 0 ) ? __('Possible to subscribe in the waiting list','teachpress') : '&nbsp;';
        $registration_period = ($date1 != '0000-00-00 00:00:00') ? __('Registration period','teachpress') . ': ' . substr($row->start,0,strlen($row->start)-3) . ' ' . __('to','teachpress') . ' ' . substr($row->end,0,strlen($row->end)-3) : '&nbsp;';
        $additional_info = ( $parent_name != '' ) ? stripslashes(nl2br($row2->comment)) . ' ' : '';
        
        // Row 1
        $rtn = '<tr>';
        $rtn .= '<td rowspan="3" width="25" style="border-bottom:1px solid silver; border-collapse: collapse;">' . $checkbox . '</td>';
        $rtn .= '<td colspan="2">&nbsp;</td>';
        $rtn .= '<td align="center"><strong>' . __('Date(s)','teachpress') . '</strong></td>';
        $rtn .= '<td align="center">';
        if ($date1 != '0000-00-00 00:00:00') {
            $rtn .= '<strong>' . __('free places','teachpress') . '</strong>';
        }
        $rtn .= '</td>';
        $rtn .= '</tr>';
        
        // Row 2
        $rtn .= '<tr>';
        $rtn .= '<td width="20%" style="font-weight:bold;">' . $checkbox_label . '</td>';
        $rtn .= '<td width="20%">' . stripslashes($row->lecturer) . '</td>';
        $rtn .= '<td align="center">' . stripslashes($row->date) . ' ' . stripslashes($row->room) . '</td>';
        $rtn .= '<td align="center">' . $display_free_places . '</td>';
        $rtn .= '</tr>';
        
        // Row 3
        $rtn .= '<tr>';
        $rtn .= '<td colspan="3" style="border-bottom:1px solid silver; border-collapse: collapse;" class="waitinglist">' . $additional_info . $waitinglist_info . '</td>';
        $rtn .= '<td style="border-bottom:1px solid silver; border-collapse: collapse;" align="center" class="einschreibefrist">' . $registration_period . '</td>';
        $rtn .= '</tr>';
        
        return $rtn;
    }
    
    /**
     * Send email notification
     * @param int $code
     * @param int $wp_id
     * @param string $name
     * @since 5.0.0
     */
    public static function send_notification($code, $wp_id, $name) {
        global $wpdb;
        if ( $code == 201 || $code == 202 ) {
            // Send user an E-Mail and return a message
            $to = $wpdb->get_var("SELECT `email` FROM " . TEACHPRESS_STUD . " WHERE `wp_id` = '$wp_id'");
            if ( $code == 201 ) {
                $subject = '[' . get_bloginfo('name') . '] ' . __('Registration','teachpress');
                $message = __('Your Registration for the following course was successful:','teachpress') . chr(13) . chr(10);
            }
            else {
                $subject = '[' . get_bloginfo('name') . '] ' . __('Waitinglist','teachpress');
                $message = __('You are signed up in the waitinglist for the following course:','teachpress') . chr(13) . chr(10);
            }
            $message = $message . stripslashes($name);
            $headers = 'From: ' . get_bloginfo('name') . ' ' . utf8_decode(chr(60)) .  get_bloginfo('admin_email') . utf8_decode(chr(62)) . "\r\n";
            wp_mail($to, $subject, $message, $headers);
        }
    }
    
}

/**
 * The form for user registrations
 * @param int $user_ID
 * @param string $mode        --> register, edit or admin
 * @return string
 * @since 4.0.0
 */
function tp_registration_form ($user_ID, $mode = 'register') {
    $user = ( $mode !== 'register' ) ? tp_students::get_student($user_ID) : '';
    $user_meta = ( $mode !== 'register' ) ? tp_students::get_student_meta($user_ID) : '';
    $fields = get_tp_options('teachpress_stud','`setting_id` ASC', ARRAY_A);
    
    $rtn = '';
    $rtn .= '<form id="tp_registration_form" method="post">';
    $rtn .= '<div id="teachpress_registration">';
    if ( $mode === 'register' ) {
        $rtn .= '<p style="text-align:left; color:#FF0000;">' . __('Please fill in the following registration form and sign up in the system. You can edit your data later.','teachpress') . '</p>';
    }
    
    $rtn .= '<table border="0" cellpadding="0" cellspacing="5" style="text-align:left; padding:5px;">';
    
    // Show default fields
    if ( $mode === 'admin' ) {
        $rtn .= tp_enrollments::get_form_text_field('wp_id', __('WordPress User-ID','teachpress'), $user['wp_id'], true);
    }
    
    $value = ( $mode === 'register' ) ? '' : stripslashes($user['firstname']);
    $rtn .= tp_enrollments::get_form_text_field('firstname', __('First name','teachpress'), $value);
 
    $value = ( $mode === 'register' ) ? '' : stripslashes($user['lastname']);
    $rtn .= tp_enrollments::get_form_text_field('lastname', __('Last name','teachpress'), $value);
    
    $value = isset($user['userlogin']) ? stripslashes($user['userlogin']) : '';
    $rtn .= tp_enrollments::get_form_text_field('userlogin', __('User account','teachpress'), $value, true);
    
    $readonly = !isset($user['email']) ? false : true;
    $value = isset($user['email']) ? stripslashes($user['email']) : '';
    $rtn .= tp_enrollments::get_form_text_field('email', __('E-Mail'), $value, $readonly);
    
    // Show custom fields
    foreach ($fields as $row) {
        $data = tp_db_helpers::extract_column_data($row['value']);
        $value = '';
        foreach ( $user_meta as $row_meta ) {
            if ( $row['variable'] === $row_meta['meta_key'] ) {
                $value = $row_meta['meta_value'];
                break;
            }
        }
        if ( $data['type'] === 'SELECT' ) {
            $rtn .= tp_enrollments::get_form_select_field($row['variable'], $data['title'], $value);
        }
        elseif ( $data['type'] === 'TEXTAREA' ) {
            $rtn .= tp_enrollments::get_form_textarea_field($row['variable'], $data['title'], $value);
        }
        elseif ( $data['type'] === 'DATE' ) {
            $rtn .= tp_enrollments::get_form_date_field($row['variable'], $data['title'], $value);
        }
        else {
            $rtn .= tp_enrollments::get_form_text_field($row['variable'], $data['title'], $value);
        }
    }
    $rtn .= '</table>';
    
    $name = ( $mode === 'register' ) ? 'tp_add_user' : 'tp_change_user';
    $rtn .= '<input name="' . $name . '" type="submit" class="button-primary" id="' . $name . '" onclick="teachpress_validateForm(' . "'firstname','','R','lastname','','R'" . ');return document.teachpress_returnValue" value="' . __('Send','teachpress') . '" />
             </div>
         </form>';
    return $rtn;
}

/** 
 * Show the enrollment system
 * @param array $atts
 *    term (STRING) - the term you want to show
 * @return string
*/
function tp_enrollments_shortcode($atts) {
    // Shortcode options
    extract(shortcode_atts(array(
       'term' => ''
    ), $atts));
    $term = htmlspecialchars($term);
    // Advanced Login
    $tp_login = get_tp_option('login');
    if ( $tp_login == 'int' ) {
         tp_advanced_registration();
    }
    // WordPress
    global $wpdb;
    global $user_ID;
    global $user_email;
    global $user_login;
    get_currentuserinfo();

    // teachPress
    $is_sign_out = get_tp_option('sign_out');
    $sem = ( $term != '' ) ? $term : get_tp_option('sem');
    $fields = get_tp_options('teachpress_stud','`setting_id` ASC', ARRAY_A);

    // Form   
    $checkbox = ( isset($_POST['checkbox']) ) ? $_POST['checkbox'] : '';
    $checkbox2 = ( isset($_POST['checkbox2']) ) ? $_POST['checkbox2'] : '';
    $tab = ( isset($_GET['tab']) ) ? $_GET['tab'] : '';
   
    $rtn = '<div id="enrollments">
            <h2 class="tp_enrollments">' . __('Enrollments for the','teachpress') . ' ' . $sem . '</h2>
            <form name="anzeige" method="post" id="anzeige" action="' . $_SERVER['REQUEST_URI'] . '">';
    /*
     * actions
    */ 
    // change user
    if ( isset( $_POST['tp_change_user'] ) ) {
        $data2 = array( 
          'firstname' => isset($_POST['firstname']) ? htmlspecialchars($_POST['firstname']) : '',
          'lastname' => isset($_POST['lastname']) ? htmlspecialchars($_POST['lastname']) : '',
          'userlogin' => htmlspecialchars($_POST['userlogin']),
          'email' => htmlspecialchars($_POST['email'])
        );
        tp_students::delete_student_meta($user_ID);
        $rtn .= tp_students::change_student($user_ID, $data2, true);
        tp_enrollments::add_student_meta( $user_ID, $fields, filter_input_array(INPUT_POST, $_POST) );
    }
    // delete signup
    if ( isset( $_POST['austragen'] ) && $checkbox2 != '' ) {
        $rtn .= tp_enrollments::delete_signup($checkbox2);
    }
    // add signups
    if ( isset( $_POST['einschreiben'] ) && $checkbox != '' ) {
        $rtn .= tp_enrollments::add_signups($user_ID, $checkbox);
    }
    // add new user
    if ( isset( $_POST['tp_add_user'] ) ) {
        $rtn .= tp_enrollments::add_student( $user_ID, $user_login, $user_email, $fields, filter_input_array(INPUT_POST, $_POST) );
    } 

    /*
     * User status
    */ 
    if ( is_user_logged_in() ) {
        $user_exists = $wpdb->get_var("SELECT `wp_id` FROM " . TEACHPRESS_STUD . " WHERE `wp_id` = '$user_ID'");
        // if user is not registered: Registration
        if ( $user_exists == '' ) {
           $user = array('userlogin' => $user_login, 'email'=> $user_email);
           $rtn .= tp_registration_form($user);
        }
        else {
            // Select all user information
            $row = tp_students::get_student($user_ID);
            // Menu
            $rtn .= tp_enrollments::get_menu($tab, $row);

            // Old Enrollments / Sign out
            if ($tab === 'old') {
                $rtn .= tp_enrollments::get_old_tab($user_ID, $is_sign_out); 
            }	
            // Edit userdata
            if ($tab === 'data') {
                $rtn .= tp_registration_form($row, 'edit'); 
            }
        }
    }
    
   /*
    * Enrollments
   */
   if ($tab === '' || $tab === 'current') {
       $rtn .= tp_enrollments::get_enrollments_tab($sem, $user_exists);
   }
   $rtn .= '</form>';
   $rtn .= '</div>';
   
   return $rtn;
}
?>
