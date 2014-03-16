<?php
/*****************************************************/
/* teachPress enrollment system (frontend functions) */
/*****************************************************/

/**
 * Get registration message
 * @param int $code
 * @return boolean 
 */
function get_tp_signup_message($code) {
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
 * Send email notification
 * @global string $teachpress_stud
 * @param int $code
 * @param int $wp_id
 * @param string $name
 */
function tp_send_notification($code, $wp_id, $name) {
    global $wpdb;
    global $teachpress_stud;
    if ( $code == 201 || $code == 202 ) {
        // Send user an E-Mail and return a message
        $to = $wpdb->get_var("SELECT `email` FROM $teachpress_stud WHERE `wp_id` = '$wp_id'");
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

/** 
 * Add signup (= subscribe student in a course)
 * @param int $checkbox     --> course_ID
 * @param int $wp_id        --> user_ID
 * @return int (teachPress status code)
 *   code 0    --> course_ID was 0,
 *   code 101  --> user is already registered,
 *   code 102  --> user is already registered in waitinglist,
 *   code 103  --> user is already registered for an other course of the course group,
 *   code 104  --> no free places availablea,
 *   code 201  --> registration was successful,
 *   code 202  --> registration was successful for waitinglist,
*/
function tp_add_signup($checkbox, $wp_id){
   global $wpdb;
   global $teachpress_courses;
   global $teachpress_signup;
   $checkbox = intval($checkbox);
   $wp_id = intval($wp_id);
   if ( $checkbox == 0 ) {
        return 0;
   }
   // Start transaction
   $wpdb->query("SET AUTOCOMMIT=0");
   $wpdb->query("START TRANSACTION");
   // Check if the user is already registered
   $check = $wpdb->get_var("SELECT `waitinglist` FROM $teachpress_signup WHERE `course_id` = '$checkbox' and `wp_id` = '$wp_id'");
   if ( $check != NULL && $check == '0' ) {
        $wpdb->query("ROLLBACK");
        return 101;
   } 
   if ( $check != NULL && $check == '1' ) {
        $wpdb->query("ROLLBACK");
        return 102;
   }
   // Check if there is a strict signup
   $row1 = $wpdb->get_row("SELECT `places`, `waitinglist`, `parent` FROM $teachpress_courses WHERE `course_id` = '$checkbox'");
   if ( $row1->parent != 0 ) {
        $check = tp_courses::get_course_data($row1->parent, 'strict_signup');
        if ( $check != 0 ) {
             $check2 = $wpdb->query("SELECT c.course_id FROM $teachpress_courses c INNER JOIN $teachpress_signup s ON s.course_id = c.course_id WHERE c.parent = '$row1->parent' AND s.wp_id = '$wp_id' AND s.waitinglist = '0'");
             if ( $check2 != NULL ) {
                 $wpdb->query("ROLLBACK");
                 return 103;
             }
        }
   }
   // Check if there are free places available
   $used_places = $wpdb->query("SELECT `course_id` FROM $teachpress_signup WHERE `course_id` = '$checkbox' AND `waitinglist` = 0");
   if ($used_places < $row1->places ) {
        // Subscribe
        $wpdb->query("INSERT INTO $teachpress_signup (`course_id`, `wp_id`, `waitinglist`, `date`) VALUES ('$checkbox', '$wp_id', '0', NOW() )");
        $wpdb->query("COMMIT");
        return 201;
   }
   else {
        // if there is a waiting list available
        if ($row1->waitinglist == '1') {
              $wpdb->query( "INSERT INTO $teachpress_signup (course_id, wp_id, waitinglist, date) VALUES ('$checkbox', '$wp_id', '1', NOW() )" );
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
 * Unsubscribe a student (frontend function)
 * @param array $checkbox   --> An array with the registration IDs
 * @return string
*/
function tp_delete_signup_student($checkbox) {
    global $wpdb;
    global $teachpress_signup;
    for( $i = 0; $i < count( $checkbox ); $i++ ) {
        $checkbox[$i] = intval($checkbox[$i]);
        // Select course ID
        $sql = "SELECT `course_id`, `waitinglist` FROM $teachpress_signup WHERE `con_id` = '$checkbox[$i]'";
        $course = $wpdb->get_row($sql);
        // Start transaction
        $wpdb->query("SET AUTOCOMMIT=0");
        $wpdb->query("START TRANSACTION");
        // check if there are users in the waiting list
        if ( $course->waitinglist == 0 ) {
            $sql = "SELECT `con_id` FROM $teachpress_signup WHERE `course_id` = '$course->course_id' AND `waitinglist` = '1' ORDER BY `con_id` ASC LIMIT 0, 1";
            $con_id = $wpdb->get_var($sql);
            // if is true subscribe the first one in the waiting list for the course
            if ($con_id != 0 && $con_id != "") {
                $wpdb->query( "UPDATE $teachpress_signup SET `waitinglist` = '0' WHERE `con_id` = '$con_id'" );
            }
        }
        $wpdb->query("DELETE FROM $teachpress_signup WHERE `con_id` = '$checkbox[$i]'");
        // End transaction
        $wpdb->query("COMMIT");
    }	
    return '<div class="teachpress_message_success">' . __('You are signed out successful','teachpress') . '</div>';
}

/**
 * The form for user registrations
 * @param object $user        --> an object of user data
 * @param string $mode        --> register, edit or admin
 * @return string
 * @since 4.0.0
 */
function tp_registration_form ($user = '', $mode = 'register') {
    $rtn = "";
    $str = "'";
    $rtn .= '<form id="tp_registration_form" method="post">';
    $rtn .= '<div id="teachpress_registration">';
    if ( $mode == 'register' ) {
        $rtn .= '<p style="text-align:left; color:#FF0000;">' . __('Please fill in the following registration form and sign up in the system. You can edit your data later.','teachpress') . '</p>';
    }
    if ( $mode != 'admin' ) {
        $rtn .= '<fieldset style="border:1px solid silver; padding:5px;">
                    <legend>' . __('Your data','teachpress') . '</legend>';
    }
    $rtn .= '<table border="0" cellpadding="0" cellspacing="5" style="text-align:left; padding:5px;">';
    if ( $mode == 'admin' ) {
        $rtn .= '<tr>
                    <td><label for="wp_id">' . __('WordPress User-ID','teachpress') . '</label></td>
                    <td><input type="text" name="wp_id" id="wp_id" readonly="true" value="' . $user->wp_id . '" size="50"/></td>
                </tr>';
    }
    if (get_tp_option('regnum') == '1') {
        $value = $mode == 'register' ? '' : $user->matriculation_number;
        $rtn .= '<tr>
                    <td><label for="matriculation_number">' . __('Matr. number','teachpress') . '</label></td>
                    <td><input type="text" name="matriculation_number" id="matriculation_number" value="' . $value . '" size="50" /></td>
                 </tr>';
    }
    $value = $mode == 'register' ? '' : stripslashes($user->firstname);
    $rtn .= '<tr>
                <td><label for="firstname">' . __('First name','teachpress') . '</label></td>
                <td><input name="firstname" type="text" id="firstname" value="' . $value . '" size="50" /></td>
             </tr>';
    $value = $mode == 'register' ? '' : stripslashes($user->lastname);
    $rtn .= '<tr>
                <td><label for="lastname">' . __('Last name','teachpress') . '</label></td>
                <td><input name="lastname" type="text" id="lastname" value="' . $value . '" size="50" /></td>
             </tr>';
    if (get_tp_option('studies') == '1') {
	$value = $mode == 'register' ? '' : stripslashes($user->course_of_studies);
        $rtn = $rtn . '<tr>
                        <td><label for="course_of_studies">' . __('Course of studies','teachpress') . '</label></td>
                        <td>
                         <select name="course_of_studies" id="course_of_studies">';
        $rowstud = get_tp_options('course_of_studies', "setting_id ASC");
        foreach ($rowstud as $rowstud) {
			$selected = $value == $rowstud->value ? 'selected="selected"' : '';
            $rtn = $rtn . '<option value="' . $rowstud->value . '" ' . $selected . '>' . $rowstud->value . '</option>';
        } 
        $rtn = $rtn . '</select>
                     </td>
                     </tr>';
    }
    if (get_tp_option('termnumber') == '1') {
	$value = $mode == 'register' ? '' : stripslashes($user->semesternumber);
        $rtn = $rtn . '<tr>
                        <td><label for="semesternumber">' . __('Number of terms','teachpress') . '</label></td>
                        <td style="text-align:left;">
                        <select name="semesternumber" id="semesternumber">';
        for ($i = 1; $i < 20; $i++) {
            $selected = $value == $i ? 'selected="selected"' : '';
            $rtn = $rtn . '<option value="' . $i . '" ' . $selected . '>' . $i . '</option>';
        }
        $rtn = $rtn . '</select>
                      </td>
                      </tr>';
    }
    $readonly = !isset($user->userlogin) ? '' : 'readonly="true" ';
    $value = isset($user->userlogin) ? stripslashes($user->userlogin) : '';
    $rtn = $rtn . '<tr>
                    <td><label for="userlogin">' . __('User account','teachpress') . '</label></td>
                    <td><input type="text" name="userlogin" id="userlogin" value="' . $value . '" ' . $readonly . 'size="50"/></td>
                   </tr>';
    if (get_tp_option('birthday') == '1') {
        if ( $mode == 'edit' || $mode == 'admin' ) {
            $b = tp_datesplit($user->birthday);
        }
        
        $rtn .= '<tr>';
        $rtn .= '<td><label for="birth_day">' . __('Date of birth','teachpress') . '</label></td>';
        $value = ($mode == 'edit' || $mode == 'admin') ? $b[0][2] : '01';
        $rtn .= '<td><input name="birth_day" id="birth_day" type="text" title="Day" size="2" value="' . $value . '"/>';
        $value = ($mode == 'edit' || $mode == 'admin') ? $b[0][1] : '01';
        $rtn .= '<select name="birth_month" title="Month">';
        $months = array ( __('Jan','teachpress'),
                          __('Feb','teachpress'),
                          __('Mar','teachpress'),
                          __('Apr','teachpress'),
                          __('May','teachpress'),
                          __('Jun','teachpress'),
                          __('Jul','teachpress'),
                          __('Aug','teachpress'),
                          __('Sep','teachpress'),
                          __('Oct','teachpress'),
                          __('Nov','teachpress'),
                          __('Dec','teachpress')
            );
        for ( $i = 1; $i <= 12; $i++ ) {
            $m = $i < 10 ? '0' . $i : $i;
            $selected = $value == $m ? 'selected="selected"' : '';
            $rtn .= '<option value="' . $m . '" ' . $selected . '>' . $months[$i-1] . '</option>';
        }
        $rtn .= '</select>';
        $value = ($mode == 'edit' || $mode == 'admin') ? $b[0][0] : '19xx';
        $rtn .= '<input name="birth_year" type="text" title="' . __('Year','teachpress') . '" size="4" value="' . $value . '"/>
                    </td>';
        $rtn .= '<tr>';
    }
    $readonly = !isset($user->email) ? '' : 'readonly="true" ';
    $value = isset($user->email) ? stripslashes($user->email) : '';
    $rtn .= '<tr>
            <td><label for="tp_email">' . __('E-Mail') . '</label></td>
            <td><input type="text" id="tp_email" name="email" value="' . $value . '" ' . $readonly . 'size="50"/></td>
            </tr>
           </table>';
    if ( $mode != 'admin' ) {
        $rtn .= '</fieldset>';
    }
    
        $name = $mode == 'register' ? 'tp_add_user' : 'tp_change_user';
    $rtn .= '<input name="' . $name . '" type="submit" class="button-primary" id="' . $name . '" onclick="teachpress_validateForm(' . $str . 'firstname' . $str .',' . $str . $str . ',' . $str . 'R' . $str . ',' . $str . 'lastname' . $str . ',' . $str . $str . ',' . $str . 'R' . $str . ');return document.teachpress_returnValue" value="' . __('Send','teachpress') . '" />
             </div>
         </form>';
    return $rtn;
}

/** Show the enrollment system
 * @param ARRAY $atts
 *    term (STRING) - the term you want to show
 * @return: String
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
   global $teachpress_courses; 
   global $teachpress_stud;
   $is_sign_out = get_tp_option('sign_out');
   // term
   if ( $term != '' ) {
       $sem = $term;
   }
   else {
       $sem = get_tp_option('sem');
   }

   // Form
   global $pagenow;
   $wp_id = $user_ID;
   
   if ( isset($_POST['checkbox']) ) { $checkbox = $_POST['checkbox']; }
   else { $checkbox = ''; }
   
   if ( isset($_POST['checkbox2']) ) { $checkbox2 = $_POST['checkbox2']; }
   else { $checkbox2 = ''; }
   
   if ( isset($_GET['tab']) ) { $tab = htmlspecialchars($_GET['tab']); }
   else { $tab = ''; }
   
   $rtn = '<div id="enrollments">
           <h2 class="tp_enrollments">' . __('Enrollments for the','teachpress') . ' ' . $sem . '</h2>
           <form name="anzeige" method="post" id="anzeige" action="' . $_SERVER['REQUEST_URI'] . '">';
    /*
     * actions
    */ 
   // change user
   if ( isset( $_POST['tp_change_user'] ) ) {
      $data2 = array( 
        'matriculation_number' => isset($_POST['matriculation_number']) ? intval($_POST['matriculation_number']) : '',
        'firstname' => isset($_POST['firstname']) ? htmlspecialchars($_POST['firstname']) : '',
        'lastname' => isset($_POST['lastname']) ? htmlspecialchars($_POST['lastname']) : '',
        'userlogin' => htmlspecialchars($_POST['userlogin']),
        'course_of_studies' => isset($_POST['course_of_studies']) ? htmlspecialchars($_POST['course_of_studies']) : '',
        'semester_number' => isset($_POST['semesternumber']) ? intval($_POST['semesternumber']) : '',
        'birth_day' => isset($_POST['birth_day']) ? htmlspecialchars($_POST['birth_day']) : '',
        'birth_month' => isset($_POST['birth_month']) ? htmlspecialchars($_POST['birth_month']) : '',
        'birth_year' => isset($_POST['birth_year']) ? intval($_POST['birth_year']) : '',
        'email' => htmlspecialchars($_POST['email'])
      );    
      $rtn = $rtn . tp_students::change_student($wp_id, $data2, 0);
   }
   // delete signup
   if ( isset( $_POST['austragen'] ) && $checkbox2 != '' ) {
      $rtn = $rtn . tp_delete_signup_student($checkbox2);
   }
   // add signups
   if ( isset( $_POST['einschreiben'] ) && $checkbox != '' ) {
      $max = count( $checkbox );
      for ($n = 0; $n < $max; $n++) {
         $rowr = $wpdb->get_row("SELECT `name`, `parent` FROM $teachpress_courses WHERE `course_id` = '$checkbox[$n]'");
         if ($rowr->parent != '0') {
            $parent = tp_courses::get_course_data($rowr->parent, 'name');
            if ($rowr->name != $parent) {
                $rowr->name = $parent . ' ' . $rowr->name; 
            }
         }
         $code = tp_add_signup($checkbox[$n], $wp_id);
         tp_send_notification($code, $wp_id, $rowr->name);
         $message = get_tp_signup_message($code);
         if ($code == 201) { $class = 'teachpress_message_success'; }
         elseif ($code == 202) { $class = 'teachpress_message_info'; }
         else { $class = 'teachpress_message_error'; }
         $rtn = $rtn . '<div class="' . $class . '">&quot;' . stripslashes($rowr->name) . '&quot;: ' . $message . '</div>';
      }
   }
   // add new user
   if ( isset( $_POST['tp_add_user'] ) ) {
      // Registration
      $data = array(
          'firstname' => isset($_POST['firstname']) ? htmlspecialchars($_POST['firstname']) : '',
          'lastname' => isset($_POST['lastname']) ? htmlspecialchars($_POST['lastname']) : '',
          'course_of_studies' => isset($_POST['course_of_studies']) ? htmlspecialchars($_POST['course_of_studies']) : '',
          'semester_number' => isset($_POST['semesternumber']) ? intval($_POST['semesternumber']) : '',
          'userlogin' => $user_login,
          'birth_day' => isset($_POST['birth_day']) ? htmlspecialchars($_POST['birth_day']) : '',
          'birth_month' => isset($_POST['birth_month']) ? htmlspecialchars($_POST['birth_month']) : '',
          'birth_year' => isset($_POST['birth_year']) ? intval($_POST['birth_year']) : '',
          'email' => $user_email,
          'matriculation_number' => isset($_POST['matriculation_number']) ? intval($_POST['matriculation_number']) : '',
      );
      $ret = tp_students::add_student($wp_id, $data);
      if ($ret != false) {
         $rtn = $rtn . '<div class="teachpress_message_success"><strong>' . __('Registration successful','teachpress') . '</strong></div>';
      }
      else {
         $rtn = $rtn . '<div class="teachpress_message_error"><strong>' . __('Error: User already exist','teachpress') . '</strong></div>';
      }
   } 

   /*
    * User status
   */ 
   if ( is_user_logged_in() ) {
      $user_exists = $wpdb->get_var("Select `wp_id` FROM $teachpress_stud WHERE `wp_id` = '$user_ID'");
      // if user is not registered
      if ( $user_exists == '' ) {
         // Registration
         $user = (object)array('userlogin' => $user_login, 'email'=> $user_email);
         $rtn .= tp_registration_form($user);
      }
      else {
        // Select all user information
        $row = tp_students::get_student($user_ID);
        /*
         * Menu
        */
        $rtn = $rtn . '<div class="tp_user_menu" style="padding:5px;">
                        <h4>' . __('Hello','teachpress') . ', ' . stripslashes($row->firstname) . ' ' . stripslashes($row->lastname) . '</h4>';
        // handle permalink usage
        // No Permalinks: Page or Post?
        if (is_page()) {
            $page = "page_id";
        }
        else {
            $page = "p";
        }
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
        if ($tab == '' || $tab == 'current') {
           $tab1 = '<span class="teachpress_active_tab">' . __('Current enrollments','teachpress') . '</span>';
        }
        else {
           $tab1 = '<a href="' . $url["link"] . 'current">' . __('Current enrollments','teachpress') . '</a>';
        }
        if ($tab == 'old') {
           $tab2 = '<span class="teachpress_active_tab">' . __('Your enrollments','teachpress') . '</span>';
        }
        else {
           $tab2 = '<a href="' . $url["link"] . 'old">' . __('Your enrollments','teachpress') . '</a>';
        }
        if ($tab == 'data') {
           $tab3 = '<span class="teachpress_active_tab">' . __('Your data','teachpress') . '</span>';
        }
        else {
           $tab3 = '<a href="' . $url["link"] . 'data">' . __('Your data','teachpress') . '</a>';
        }
        $rtn = $rtn . '<p>' . $tab1 . ' | ' . $tab2 . ' | ' . $tab3 . '</p>
                      </div>';

        /*
         * Old Enrollments / Sign out
        */
        if ($tab == 'old') {
           $rtn = $rtn . '<p><strong>' . __('Signed up for','teachpress') . '</strong></p>   
                         <table class="teachpress_enr_old" border="1" cellpadding="5" cellspacing="0">
                         <tr>';
           if ($is_sign_out == '0') {
                   $rtn = $rtn . '<th width="15">&nbsp;</th>';
           }
           $rtn = $rtn . '<th>' . __('Name','teachpress') . '</th>
                          <th>' . __('Type') . '</th>
                          <th>' . __('Date','teachpress') . '</th>
                          <th>' . __('Room','teachpress') . '</th>
                          <th>' . __('Term','teachpress') . '</th>
                         </tr>';
             // Select all courses where user is registered
             $row1 = tp_students::get_signups( array('wp_id' => $student, 'mode' => 'reg') );
             if ( $wpdb->num_rows != 0 ) {
               foreach($row1 as $row1) {
                   $row1->parent_name = stripslashes($row1->parent_name);
                   $row1->name = stripslashes($row1->name);
                   if ($row1->parent_name != "") {
                       $row1->parent_name = $row1->parent_name . ' -';
                   }
                   $rtn = $rtn . '<tr>';
                   if ($is_sign_out == '0') {
                       $rtn = $rtn . '<td><input name="checkbox2[]" type="checkbox" value="' . $row1->con_id . '" title="' . $row1->name . '" id="ver_' . $row1->con_id . '"/></td>';
                   }		
                   $rtn = $rtn . '<td><label for="ver_' . $row1->con_id . '" style="line-height:normal;" title="' . $row1->parent_name . ' ' .  $row1->name . '">' . $row1->parent_name . ' ' .  $row1->name . '</label></td>
                                   <td>' . stripslashes($row1->type) . '</td>
                                   <td>' . stripslashes($row1->date) . '</td>
                                   <td>' . stripslashes($row1->room) . '</td> 
                                   <td>' . stripslashes($row1->semester) . '</td>
                                   </tr>';
               }
             }
             else {
                 $rtn = $rtn . '<tr><td colspan="6">' . __('No enrollments','teachpress') . '</td></tr>';
             }
             $rtn = $rtn . '</table>';
             // all courses where user is registered in a waiting list
             $row1 = tp_students::get_signups( array('wp_id' => $student, 'mode' => 'wtl') );
             if ( count($row1) != 0 ) {
                $rtn = $rtn . '<p><strong>' . __('Waiting list','teachpress') . '</strong></p>
                              <table class="teachpress_enr_old" border="1" cellpadding="5" cellspacing="0">
                              <tr>';
                if ($is_sign_out == '0') {
                        $rtn = $rtn . '<th width="15">&nbsp;</th>';
                }
                $rtn = $rtn . '<th>' . __('Name','teachpress') . '</th>
                               <th>' . __('Type') . '</th>
                               <th>' . __('Date','teachpress') . '</th>
                               <th>' . __('Room','teachpress') . '</th>
                               <th>' . __('Term','teachpress') . '</th>
                              </tr>';
                foreach($row1 as $row1) {
                    if ($row1->parent_name != "") {
                        $row1->parent_name = '' . $row1->parent_name . ' -';
                    }
                    $row1->parent_name = stripslashes($row1->parent_name);
                    $row1->name = stripslashes($row1->name);
                    $rtn = $rtn . '<tr>';
                    if ($is_sign_out == '0') {
                        $rtn = $rtn . '<td><input name="checkbox2[]" type="checkbox" value="' . $row1->con_id . '" title="' . $row1->name . '" id="ver_' . $row1->con_id . '"/></td>';
                    }		
                    $rtn = $rtn . '<td><label for="ver_' . $row1->con_id . '" style="line-height:normal;" title="' . $row1->parent_name . ' ' .  $row1->name . '">' . $row1->parent_name . ' ' .  $row1->name . '</label></td>
                                   <td>' . stripslashes($row1->type) . '</td>
                                   <td>' . stripslashes($row1->date) . '</td>
                                   <td>' . stripslashes($row1->room) . '</td> 
                                   <td>' . stripslashes($row1->semester) . '</td>
                                  </tr>';
                 }
                 $rtn = $rtn . '</table>';
             }
             if ($is_sign_out == '0') {
                $rtn = $rtn . '<p><input name="austragen" type="submit" value="' . __('unsubscribe','teachpress') . '" id="austragen" /></p>';
             }
        }	
        /*
         * Edit userdata
        */
        if ($tab == 'data') {
           $rtn = $rtn . tp_registration_form($row, 'edit'); 
           $field4 = get_tp_option('birthday');
           if ($field4 != '1') {
              $rtn = $rtn . '<input type="hidden" name="matriculation_number2" value="' . $row->matriculation_number . '" />';
           }
           if ($field4 != '1') {
              $rtn = $rtn . '<input type="hidden" name="course_of_studies2" value="' . $row->course_of_studies . '" />';
           }
           if ($field4 != '1') {
              $rtn = $rtn . '<input type="hidden" name=semesternumber2"" value="' . $row->semesternumber . '" />';
           }
           if ($field4 != '1') {
              $rtn = $rtn . '<input type="hidden" name="birthday2" value="' . $row->birthday . '" />';
           }
        }
       }
   }
   /*
    * Enrollments
   */
   if ($tab == '' || $tab == 'current') {
      // Select all courses where enrollments in the current term are available
      $row = $wpdb->get_results("SELECT * FROM $teachpress_courses WHERE `semester` = '$sem' AND `parent` = '0' AND (`visible` = '1' OR `visible` = '2') ORDER BY `type` DESC, `name`");
      foreach($row as $row) {
         // load all childs
         $row2 = $wpdb->get_results("Select * FROM $teachpress_courses WHERE `parent` = '$row->course_id' AND (`visible` = '1' OR `visible` = '2') AND (`start` != '0000-00-00 00:00:00') ORDER BY `name`");
         // test if a child has an enrollment
         $test = false;
         $free_places = 0;
         foreach ( $row2 as $childs ) {
            if ( $childs->start != '0000-00-00 00:00:00' ) {
               $test = true;
            }	
         }
         if ( $row->start != '0000-00-00 00:00:00' || $test == true ) {
            // define some course variables
            $date1 = $row->start;
            $date2 = $row->end;
            $free_places = tp_courses::get_free_places($row->course_id, $row->places);
            if ( $free_places < 0 ) {
                $free_places = 0;
            }
            if ($row->rel_page != 0) {
               $course_name = '<a href="' . get_permalink($row->rel_page) . '">' . stripslashes($row->name) . '</a>';
            }
            else {
               $course_name = '' . stripslashes($row->name) . '';
            }
            // build course string
            $rtn = $rtn . '<div class="teachpress_course_group">
                           <div class="teachpress_course_name">' . $course_name . '</div>
                           <table class="teachpress_enr" width="100%" border="0" cellpadding="1" cellspacing="0">
                           <tr>
                           <td rowspan="3" width="25" style="border-bottom:1px solid silver; border-collapse: collapse;">';
            if (is_user_logged_in() && $user_exists != '') {
               if ($date1 != '0000-00-00 00:00:00' && current_time('mysql') >= $date1 && current_time('mysql') <= $date2) {
                  $rtn = $rtn . '<input type="checkbox" name="checkbox[]" value="' . $row->course_id . '" title="' . stripslashes($row->name) . ' ' . __('Select','teachpress') . '" id="checkbox_' . $row->course_id . '"/>';
               } 
            }
            else {
               $rtn = $rtn . '&nbsp;';
            }	
            $rtn = $rtn . '</td>
                           <td colspan="2">&nbsp;</td>
                           <td align="center"><strong>' . __('Date(s)','teachpress') . '</strong></td>
                           <td align="center">';
            if ($date1 != '0000-00-00 00:00:00') {
               $rtn = $rtn . '<strong>' . __('free places','teachpress') . '</strong>';
            }
            $rtn = $rtn . '</td>
                        </tr>
                        <tr>
                         <td width="20%" style="font-weight:bold;">';
            if ($date1 != '0000-00-00 00:00:00' && current_time('mysql') >= $date1 && current_time('mysql') <= $date2) {
               $rtn = $rtn . '<label for="checkbox_' . $row->course_id . '" style="line-height:normal;">';
            }
            $rtn = $rtn . stripslashes($row->type);
            if ($date1 != '0000-00-00 00:00:00' && current_time('mysql') >= $date1 && current_time('mysql') <= $date2) {
               $rtn = $rtn . '</label>';
            }
            $rtn = $rtn . '</td>
                           <td width="20%">' . stripslashes($row->lecturer) . '</td>
                           <td align="center">' . stripslashes($row->date) . ' ' . stripslashes($row->room) . '</td>
                           <td align="center">';
            if ($date1 != '0000-00-00 00:00:00') { 
               $rtn = $rtn . $free_places . ' ' . __('of','teachpress') . ' ' .  $row->places;
            }
            $rtn = $rtn . '</td>
                         </tr>
                         <tr>
                         <td colspan="3" style="border-bottom:1px solid silver; border-collapse: collapse;" class="waitinglist">';
            if ($row->waitinglist == 1 && $free_places == 0) {
               $rtn = $rtn . __('Possible to subscribe in the waiting list','teachpress'); 
            }
            else {
               $rtn = $rtn . '&nbsp;';
            }
            $rtn = $rtn . '</td>
                         <td style="border-bottom:1px solid silver; border-collapse: collapse;" align="center" class="einschreibefrist">';
            if ($date1 != '0000-00-00 00:00:00') {
               $rtn = $rtn . __('Registration period','teachpress') . ': ' . substr($row->start,0,strlen($row->start)-3) . ' ' . __('to','teachpress') . ' ' . substr($row->end,0,strlen($row->end)-3);
            }
            $rtn = $rtn . '</td>
                          </tr>';
            // search childs
            foreach ($row2 as $row2) {
               $date3 = $row2->start;
               $date4 = $row2->end;
               $free_places = tp_courses::get_free_places($row2->course_id, $row2->places);
               if ( $free_places < 0 ) {
                   $free_places = 0;
               }
               if ($row->name == $row2->name) {
                   $row2->name = $row2->type;
               }
               $rtn = $rtn . '<tr>
                              <td rowspan="3" width="25" style="border-bottom:1px solid silver; border-collapse: collapse; vertical-align:middle">';
               if (is_user_logged_in() && $user_exists != '') {
                  if ($date3 != '0000-00-00 00:00:00' && current_time('mysql') >= $date3 && current_time('mysql') <= $date4) {
                     $rtn = $rtn . '<input type="checkbox" name="checkbox[]" value="' . $row2->course_id . '" title="' . stripslashes($row2->name) . ' ausw&auml;hlen" id="checkbox_' . $row2->course_id . '"/>';
                  }
               }
               $rtn = $rtn . '</td>
                              <td colspan="2">&nbsp;</td>
                              <td align="center"><strong>' . __('Date(s)','teachpress') . '</strong></td>
                              <td align="center"><strong>' . __('free places','teachpress') . '</strong></td>
                             </tr>
                             <tr>
                              <td width="20%" style="font-weight:bold;">';
               if ($date3 != '0000-00-00 00:00:00' && current_time('mysql') >= $date3 && current_time('mysql') <= $date4) {
                  $rtn = $rtn . '<label for="checkbox_' . $row2->course_id . '" style="line-height:normal;">';
               }
               $rtn = $rtn . $row2->name;
               if ($date3 != '0000-00-00 00:00:00' && current_time('mysql') >= $date3 && current_time('mysql') <= $date4) {
                  $rtn = $rtn . '</label>';
               }
               $rtn = $rtn . '</td>
                              <td width="20%">' . stripslashes($row2->lecturer) . '</td>
                              <td align="center">' . stripslashes($row2->date) . ' ' . stripslashes($row2->room) . '</td>
                              <td align="center">' . $free_places . ' ' . __('of','teachpress') . ' ' . $row2->places . '</td>
                             </tr>
                             <tr>
                              <td colspan="3" style="border-bottom:1px solid silver; border-collapse: collapse;" class="waitinglist">';
               $rtn = $rtn . stripslashes(nl2br($row2->comment)) . ' ';
               if ($row2->waitinglist == 1 && $free_places == 0) {
                  $rtn = $rtn . __('Possible to subscribe in the waiting list','teachpress');
               } 
               else {
                  $rtn = $rtn . '&nbsp;';
               }
               $rtn = $rtn . '</td>
                              <td align="center" class="einschreibefrist" style="border-bottom:1px solid silver; border-collapse: collapse;">';
               if ($date3 != '0000-00-00 00:00:00') {
                  $rtn = $rtn . __('Registration period','teachpress') . ': ' . substr($row2->start,0,strlen($row2->start)-3) . ' ' . __('to','teachpress') . ' ' . substr($row2->end,0,strlen($row2->end)-3);
               }
               $rtn = $rtn . '</td>
                        </tr>'; 
            } 
            // End (search for childs)
            $rtn = $rtn . '</table>
                     </div>';
         }				
      }	
      if (is_user_logged_in() && $user_exists != '') {
         $rtn = $rtn . '<input name="einschreiben" type="submit" value="' . __('Sign up','teachpress') . '" />';
      }
   }
   $rtn = $rtn . '</form>
            </div>';
   return $rtn;
}
?>
