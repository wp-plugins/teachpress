<?php
/*****************************************************/
/* teachPress enrollment system (frontend functions) */
/*****************************************************/

/**
 * Get registration message
 * @param int $code
 * @return boolean 
 */
function tp_get_registration_message($code) {
    switch ($code) {
    case 0:
        return __('Warning: Wrong course_ID','teachpress');
    case 101:
        return __('You are already registered for this course.','teachpress');
    case 102:
        return __('Registration is not possible, because you are already registered in the waitinglist.','teachpress');
    case 103:
        return __('Registration is not possible, because you are already registered for an other course of this course group.','teachpress');
    case 201:
        return __('Registration was successful.','teachpress');
    case 202:
        return __('For this course there are no more free places available. You are automatically signed up in a waiting list.','teachpress');
    default:
        return falsr;
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
        $to = $wpdb->get_var("SELECT `email` FROM " . $teachpress_stud . " WHERE `wp_id` = '$wp_id'");
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
 * Add registration (= subscribe student in a course)
 * @param int $checkbox     --> course_ID
 * @param int $wp_id        --> user_ID
 * @return int (teachPress status code)
 *   code 0    --> course_ID was 0,
 *   code 101  --> user is already registered,
 *   code 102  --> user is already registered in waitinglist,
 *   code 103  --> user is already registered for an other course of the course group,
 *   code 201  --> registration was successful,
 *   code 202  --> registration was successful for waitinglist,
*/
function tp_add_registration($checkbox, $wp_id){
   global $wpdb;
   global $teachpress_courses;
   global $teachpress_signup;
   settype($checkbox, 'integer');
   
   if ( $checkbox == 0 ) {
        return 0;
   }
   // Check if the user is already registered
   $check = $wpdb->get_var("SELECT `waitinglist` FROM " . $teachpress_signup . " WHERE `course_id` = '$checkbox' and `wp_id` = '$wp_id'");
   if ( $check != NULL && $check == '0' ) {
        return 101;
   } 
   if ( $check != NULL && $check == '1' ) {
        return 102;
   }
   // Start Transaction
   $wpdb->query("SET AUTOCOMMIT=0");
   $wpdb->query("START TRANSACTION");
   // Check if there is a strict signup
   $row1 = "SELECT `fplaces`, `name`, `start`, `end`, `waitinglist`, `parent` FROM " . $teachpress_courses . " WHERE `course_id` = '$checkbox' FOR UPDATE";
   $row1 = $wpdb->get_row($row1);
   if ( $row1->parent != 0 ) {
        $check = get_tp_course_data ($row1->parent, 'strict_signup');
        if ( $check != 0 ) {
             $check2 = $wpdb->query("SELECT c.course_id FROM " . $teachpress_courses . " c INNER JOIN " . $teachpress_signup . " s ON s.course_id = c.course_id WHERE c.parent = '$row1->parent' AND s.wp_id = '$wp_id' AND s.waitinglist = '0' FOR UPDATE");
             if ( $check2 != NULL ) {
                 $wpdb->query("ROLLBACK");
                 return 103;
             }
        }
   }
   // Check if there are free places available
   if ($row1->fplaces > 0 ) {
        // Subscribe
        $wpdb->query( "INSERT INTO " . $teachpress_signup . " (`course_id`, `wp_id`, `waitinglist`, `date`) VALUES ('$checkbox', '$wp_id', '0', NOW() )" );
        // reduce the number of free places in the course
        $neu = $row1->fplaces - 1;
        $wpdb->query( "UPDATE " . $teachpress_courses . " SET `fplaces` = '$neu' WHERE `course_id` = '$checkbox'" );
        $wpdb->query("COMMIT");
        return 201;
   }
   else {
        // if there is a waiting lis available
        if ($row1->waitinglist == '1') {
              $wpdb->query( "INSERT INTO " . $teachpress_signup . " (course_id, wp_id, waitinglist, date) VALUES ('$checkbox', '$wp_id', '1', NOW() )" );
              $wpdb->query("COMMIT");
              return 202;
        }
   }
}

/** 
 * Unsubscribe a student (frontend function)
 * @param ARRAY $checkbox2 - An array with the registration IDs
*/
function tp_delete_registration_student($checkbox2) {
     global $wpdb;
     global $teachpress_courses; 
     global $teachpress_signup;
     for( $i = 0; $i < count( $checkbox2 ); $i++ ) {
          settype($checkbox2[$i], 'integer');
          // Select course ID
          $row1 = "SELECT `course_id` FROM " . $teachpress_signup . " WHERE `con_id` = '$checkbox2[$i]'";
          $row1 = $wpdb->get_results($row1);
          foreach ($row1 as $row1) {
               // Start Transaction
               $wpdb->query("SET AUTOCOMMIT=0");
               $wpdb->query("START TRANSACTION");
               // check if there are users in the waiting list
               $abfrage = "SELECT `con_id` FROM " . $teachpress_signup . " WHERE `course_id` = '$row1->course_id' AND `waitinglist` = '1' FOR UPDATE";
               $test = $wpdb->query($abfrage);
               // if is true
               if ($test!= 0) {
                    $zahl = 0;
                    $row = $wpdb->get_results($abfrage);
                    foreach ($row as $row) {
                         if ($zahl < 1) {
                               $aendern = "UPDATE " . $teachpress_signup . " SET `waitinglist` = '0' WHERE `con_id` = '$row->con_id'";
                               $wpdb->query( $aendern );
                               $zahl++;
                         }
                    }
               }
               // if not enhance the number of free places
               else {
                    $fplaces = "SELECT `fplaces` FROM " . $teachpress_courses . " WHERE `course_id` = '$row1->course_id' FOR UPDATE";
                    $fplaces = $wpdb->get_var($fplaces);
                    $neu = $fplaces + 1;
                    $aendern = "UPDATE " . $teachpress_courses . " SET fplaces = '$neu' WHERE `course_id` = '$row1->course_id'";
                    $wpdb->query( $aendern );
               }
               $wpdb->query("COMMIT");
          }
          $wpdb->query( "DELETE FROM " . $teachpress_signup . " WHERE `con_id` = '$checkbox2[$i]'" );
     }	
     return '<div class="teachpress_message_success">' . __('You are signed out successful','teachpress') . '</div>';
}

/** 
 * Add student
 * @param INT $wp_id - The WordPress user ID
 * @param ARRAY $data
 * @return BOOLEAN
*/
function tp_add_student($wp_id, $data) {
     global $wpdb;
     global $teachpress_stud;
     $wp_id = tp_sec_var($wp_id, 'integer');
     $sql = "SELECT `wp_id` FROM " . $teachpress_stud . " WHERE `wp_id` = '$wp_id'";
     $test = $wpdb->query($sql);
     if ($test == '0') {
          $data['birthday'] = $data['birth_year'] . '-' . $data['birth_month'] . '-' . $data['birth_day'];
          $wpdb->insert( $teachpress_stud, array( 'wp_id' => $wp_id, 'firstname' => $data['firstname'], 'lastname' => $data['lastname'], 'course_of_studies' => $data['course_of_studies'], 'userlogin' => $data['userlogin'], 'birthday' => $data['birthday'], 'email' => $data['email'], 'semesternumber' => $data['semester_number'], 'matriculation_number' => $data['matriculation_number'] ), array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d' ) );
          return true;
     }
     else {
          return false;
     }
}

/** 
 * Edit userdata
 * @param INT $wp_id - user ID
 * @param ARRAY_A $data - user data
 * @param INT $user_ID - current user ID
 * @return STRING
*/
function tp_change_student($wp_id, $data, $user_ID = 0) {
    global $wpdb;
    global $teachpress_stud;
    $wp_id = tp_sec_var($wp_id, 'integer');
    $user_ID = tp_sec_var($user_ID, 'integer');
    $wpdb->update( $teachpress_stud, array( 'firstname' => $data['firstname'], 'lastname' => $data['lastname'], 'course_of_studies' => $data['course_of_studies'], 'userlogin' => $data['userlogin'], 'birthday' => $data['birthday'], 'email' => $data['email'], 'semesternumber' => $data['semester_number'], 'matriculation_number' => $data['matriculation_number'] ), array( 'wp_id' => $wp_id ), array( '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d' ), array( '%d' ) );
    if ($user_ID == 0) {
        $return = '<div class="teachpress_message_success">' . __('Changes in your profile successful.','teachpress') . '</div>';
        return $return;
    }
}

/* Show the enrollment system
 * @param ARRAY $atts
 * @return: String
*/
function tp_enrollments_shortcode($atts) {		
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
   global $teachpress_settings; 
   global $teachpress_signup;
   $sem = get_tp_option('sem');
   $is_sign_out = get_tp_option('sign_out');
   $url["permalink"] = get_tp_option('permalink');

   // Form
   global $pagenow;
   $wp_id = $user_ID;
   
   if ( isset($_POST['checkbox']) ) { $checkbox = $_POST['checkbox']; }
   else { $checkbox = ''; }
   
   if ( isset($_POST['checkbox2']) ) { $checkbox2 = $_POST['checkbox2']; }
   else { $checkbox2 = ''; }
   
   if ( isset($_GET['tab']) ) { $tab = tp_sec_var($_GET['tab']); }
   else { $tab = ''; }
   
   $str = "'";
   $rtn = '<div id="enrollments">
           <h2 class="tp_enrollments">' . __('Enrollments for the','teachpress') . ' ' . $sem . '</h2>
           <form name="anzeige" method="post" id="anzeige" action="' . $_SERVER['REQUEST_URI'] . '">';
    /*
     * actions
    */ 
   // change user
   if ( isset( $_POST['aendern'] ) ) {
      $data2 = array( 
        'matriculation_number' => tp_sec_var($_POST['matriculation_number2'], 'integer'),
        'firstname' => tp_sec_var($_POST['firstname2']),
        'lastname' => tp_sec_var($_POST['lastname2']),
        'course_of_studies' => tp_sec_var($_POST['course_of_studies2']),
        'semester_number' => tp_sec_var($_POST['semesternumber2'], 'integer'),
        'birthday' => tp_sec_var($_POST['birthday2']),
        'email' => tp_sec_var($_POST['email2'])
      );    
      $rtn = $rtn . tp_change_student($wp_id, $data2, 0);
   }
   // delete registration
   if ( isset( $_POST['austragen'] ) ) {
      $rtn = $rtn . tp_delete_registration_student($checkbox2);
   }
   // add registrations
   if ( isset( $_POST['einschreiben'] ) ) {
      for ($n = 0; $n < count( $checkbox ); $n++) {
         $rowr = $wpdb->get_row("SELECT `name`, `parent` FROM " . $teachpress_courses . " WHERE `course_id` = '$checkbox[$n]'");
         if ($rowr->parent != '0') {
            $parent = get_tp_course_data ($rowr->parent, 'name');
            if ($rowr->name != $parent) {
                $rowr->name = $parent . ' ' . $rowr->name; 
            }
         }
         $code = tp_add_registration($checkbox[$n], $wp_id);
         tp_send_notification($code, $wp_id, $rowr->name);
         $message = tp_get_registration_message($code);
         if ($code == 201) { $class = 'teachpress_message_success'; }
         elseif ($code == 202) { $class = 'teachpress_message_info'; }
         else { $class = 'teachpress_message_error'; }
         $rtn = $rtn . '<div class="' . $class . '">&quot;' . stripslashes($rowr->name) . '&quot;: ' . $message . '</div>';
      }
   }
   // add new user
   if ( isset( $_POST['eintragen'] ) ) {
      // Registration
      $data = array(
          'firstname' => tp_sec_var($_POST['firstname']),
          'lastname' => tp_sec_var($_POST['lastname']),
          'course_of_studies' => tp_sec_var($_POST['course_of_studies']),
          'semester_number' => tp_sec_var($_POST['semesternumber'], 'integer'),
          'userlogin' => $user_login,
          'birth_day' => tp_sec_var($_POST['birth_day']),
          'birth_month' => tp_sec_var($_POST['birth_month']),
          'birth_year' => tp_sec_var($_POST['birth_year'], 'integer'),
          'email' => $user_email,
          'matriculation_number' => tp_sec_var($_POST['matriculation_number'], 'integer')
      );    
      $ret = tp_add_student($wp_id, $data);
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
   if (is_user_logged_in()) {
      $auswahl = "Select `wp_id` FROM " . $teachpress_stud . " WHERE `wp_id` = '$user_ID'";
      $auswahl = $wpdb->get_var($auswahl);
      // if user is not registered
      if($auswahl == '' ) {
         /*
          * Registration
         */
         $rtn = $rtn . '<div id="eintragen">
                  <p style="text-align:left; color:#FF0000;">' . __('Please fill in the following registration form and sign up in the system. You can edit your data later.','teachpress') . '</p>
                  <fieldset style="border:1px solid silver; padding:5px;">
                   <legend>' . __('Your data','teachpress') . '</legend>
                   <table border="0" cellpadding="0" cellspacing="5" style="text-align:left; padding:5px;">';
         $field1 = get_tp_option('regnum');
         if ($field1 == '1') { 
                 $rtn = $rtn . '<tr>
                                <td><label for="matriculation_number">' . __('Matr. number','teachpress') . '</label></td>
                                <td><input type="text" name="matriculation_number" id="matriculation_number" /></td>
                                </tr>';
         } 
         $rtn = $rtn . '<tr>
                        <td><label for="firstname">' . __('First name','teachpress') . '</label></td>
                        <td><input name="firstname" type="text" id="firstname" /></td>
                        </tr>
                        <tr>
                        <td><label for="lastname">' . __('Last name','teachpress') . '</label></td>
                        <td><input name="lastname" type="text" id="lastname" /></td>
                        </tr>';
         $field2 = get_tp_option('studies');
         if ($field2 == '1') {
            $rtn = $rtn . '<tr>
                           <td><label for="course_of_studies">' . __('Course of studies','teachpress') . '</label></td>
                           <td>
                           <select name="course_of_studies" id="course_of_studies">';
            $rowstud = "SELECT value FROM " . $teachpress_settings . " WHERE category = 'course_of_studies'";
            $rowstud = $wpdb->get_results($rowstud);
            foreach ($rowstud as $rowstud) {
                    $rtn = $rtn . '<option value="' . $rowstud->value . '">' . $rowstud->value . '</option>';
            } 
            $rtn = $rtn . '</select>
                      </td>
                      </tr>';
         }
         $field2 = get_tp_option('termnumber');
         if ($field2 == '1') {
            $rtn = $rtn . '<tr>
                           <td><label for="semesternumber">' . __('Number of terms','teachpress') . '</label></td>
                           <td style="text-align:left;">
                           <select name="semesternumber" id="semesternumber">';
            for ($i=1; $i<20; $i++) {
               $rtn = $rtn . '<option value="' . $i . '">' . $i . '</option>';
            }
            $rtn = $rtn . '</select>
                      </td>
                      </tr>';
         }
         $rtn = $rtn . '<tr>
                        <td>' . __('User account','teachpress') . '</td>
                        <td style="text-align:left;"><?php echo"$user_login" ?></td>
                        </tr>';
         $field2 = get_tp_option('birthday');
         if ($field2 == '1') {
            $rtn = $rtn . '<tr>
                        <td><label for="birth_day">' . __('Date of birth','teachpress') . '</label></td>
                        <td><input name="birth_day" id="birth_day" type="text" title="Day" size="2" value="01"/>
                                <select name="birth_month" title="Month">
                                        <option value="01">' . __('Jan','teachpress') . '</option>
                                        <option value="02">' . __('Feb','teachpress') . '</option>
                                        <option value="03">' . __('Mar','teachpress') . '</option>
                                        <option value="04">' . __('Apr','teachpress') . '</option>
                                        <option value="05">' . __('May','teachpress') . '</option>
                                        <option value="06">' . __('Jun','teachpress') . '</option>
                                        <option value="07">' . __('Jul','teachpress') . '</option>
                                        <option value="08">' . __('Aug','teachpress') . '</option>
                                        <option value="09">' . __('Sep','teachpress') . '</option>
                                        <option value="10">' . __('Oct','teachpress') . '</option>
                                        <option value="11">' . __('Nov','teachpress') . '</option>
                                        <option value="12">' . __('Dec','teachpress') . '</option>
                                </select>
                                <input name="birth_year" type="text" title="' . __('Year','teachpress') . '" size="4" value="19xx"/>
                        </td>
                        </tr>';
         }
         $rtn = $rtn . '<tr>
                        <td>' . __('E-Mail') . '</td>
                        <td>' . $user_email . '</td>
                        </tr>
                       </table>
                       </fieldset>
               <input name="eintragen" type="submit" id="eintragen" onclick="teachpress_validateForm(' . $str . 'firstname' . $str .',' . $str . $str . ',' . $str . 'R' . $str . ',' . $str . 'lastname' . $str . ',' . $str . $str . ',' . $str . 'R' . $str . ');return document.teachpress_returnValue" value="' . __('Send','teachpress') . '" />
                       </div>
                       </form>';
           }
           else {
              // Select all user information
              $auswahl = "Select * FROM " . $teachpress_stud . " WHERE wp_id = '$user_ID'";
              $auswahl = $wpdb->get_results($auswahl);
              foreach ($auswahl as $row) {
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
                 if ($url["permalink"] == '1') {
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
                         $tab1 = '<strong>' . __('Current enrollments','teachpress') . '</strong>';
                 }
                 else {
                         $tab1 = '<a href="' . $url["link"] . 'current">' . __('Current enrollments','teachpress') . '</a>';
                 }
                 if ($tab == 'old') {
                         $tab2 = '<strong>' . __('Your enrollments','teachpress') . '</strong>';
                 }
                 else {
                         $tab2 = '<a href="' . $url["link"] . 'old">' . __('Your enrollments','teachpress') . '</a>';
                 }
                 if ($tab == 'data') {
                         $tab3 = '<strong>' . __('Your data','teachpress') . '</strong>';
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
                            $rtn = $rtn . '<th>&nbsp;</th>';
                    }
                    $rtn = $rtn . '<th>' . __('Name','teachpress') . '</th>
                                   <th>' . __('Type') . '</th>
                                   <th>' . __('Date','teachpress') . '</th>
                                   <th>' . __('Room','teachpress') . '</th>
                                   <th>' . __('Term','teachpress') . '</th>
                                  </tr>';
                      // Select all courses where user is registered
                      $row1 = "SELECT wp_id, v_id, b_id, waitinglist, name, type, room, date, semester, parent_name FROM (SELECT k.wp_id as wp_id, k.course_id as v_id, k.con_id as b_id, k.waitinglist as waitinglist, v.name as name, v.type as type, v.room as room, v.date as date, v.semester as semester, p.name as parent_name FROM " . $teachpress_signup . " k INNER JOIN " . $teachpress_courses . " v ON k.course_id = v.course_id LEFT JOIN " . $teachpress_courses . " p ON v.parent = p.course_id ) AS temp 
                      WHERE wp_id = '$row->wp_id' AND waitinglist = '0' 
                      ORDER BY b_id DESC";
                      $row1 = $wpdb->get_results($row1);
                      foreach($row1 as $row1) {
                              $row1->parent_name = stripslashes($row1->parent_name);
                              $row1->name = stripslashes($row1->name);
                              if ($row1->parent_name != "") {
                                      $row1->parent_name = '' . $row1->parent_name . ' -';
                              }
                              $rtn = $rtn . '<tr>';
                              if ($is_sign_out == '0') {
                                      $rtn = $rtn . '<td><input name="checkbox2[]" type="checkbox" value="' . $row1->b_id . '" title="' . $row1->name . '" id="ver_' . $row1->b_id . '"/></td>';
                              }		
                              $rtn = $rtn . '<td><label for="ver_' . $row1->b_id . '" style="line-height:normal;" title="' . $row1->parent_name . ' ' .  $row1->name . '">' . $row1->parent_name . ' ' .  $row1->name . '</label></td>
                                             <td>' . stripslashes($row1->type) . '</td>
                                             <td>' . stripslashes($row1->date) . '</td>
                                             <td>' . stripslashes($row1->room) . '</td> 
                                             <td>' . stripslashes($row1->semester) . '</td>
                                            </tr>';
                      }
                      $rtn = $rtn . '</table>';
                      // all courses where user is registered in a waiting list
                      $row1 = "SELECT wp_id, v_id, b_id, waitinglist, name, type, room, date, semester, parent_name FROM (SELECT k.wp_id as wp_id, k.course_id as v_id, k.con_id as b_id, k.waitinglist as waitinglist, v.name as name, v.type as type, v.room as room, v.date as date, v.semester as semester, p.name as parent_name FROM " . $teachpress_signup . " k INNER JOIN " . $teachpress_courses . " v ON k.course_id = v.course_id LEFT JOIN " . $teachpress_courses . " p ON v.parent = p.course_id ) AS temp 
                      WHERE wp_id = '$row->wp_id' AND waitinglist = '1' 
                      ORDER BY b_id DESC";
                      $test = $wpdb->query($row1);
                      if ($test != 0) {
                         $rtn = $rtn . '<p><strong>' . __('Waiting list','teachpress') . '</strong></p>
                                       <table class="teachpress_enr_old" border="1" cellpadding="5" cellspacing="0">
                                       <tr>';
                         if ($is_sign_out == '0') {
                                 $rtn = $rtn . '<th>&nbsp;</th>';
                         }
                         $rtn = $rtn . '<th>' . __('Name','teachpress') . '</th>
                                        <th>' . __('Type') . '</th>
                                        <th>' . __('Date','teachpress') . '</th>
                                        <th>' . __('Room','teachpress') . '</th>
                                        <th>' . __('Term','teachpress') . '</th>
                                       </tr>';
                         $row1 = $wpdb->get_results($row1);
                         foreach($row1 as $row1) {
                                 if ($row1->parent_name != "") {
                                         $row1->parent_name = '' . $row1->parent_name . ' -';
                                 }
                                 $row1->parent_name = stripslashes($row1->parent_name);
                                 $row1->name = stripslashes($row1->name);
                                 $rtn = $rtn . '<tr>';
                                 if ($is_sign_out == '0') {
                                         $rtn = $rtn . '<td><input name="checkbox2[]" type="checkbox" value="' . $row1->b_id . '" title="' . $row1->name . '" id="ver_' . $row1->b_id . '"/></td>';
                                 }		
                                 $rtn = $rtn . '<td><label for="ver_' . $row1->b_id . '" style="line-height:normal;" title="' . $row1->parent_name . ' ' .  $row1->name . '">' . $row1->parent_name . ' ' .  $row1->name . '</label></td>
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
                    $rtn = $rtn . '<table class="teachpress_enr_edit">';
                    $field1 = get_tp_option('regnum');
                    if ($field1 == '1') {
                            $rtn = $rtn . '<tr>
                                           <td><label for="matriculation_number2">' . __('Matr. number','teachpress') . '</label></td>
                                           <td><input type="text" name="matriculation_number2" id="matriculation_number2" value="' . $row->matriculation_number . '"/></td>
                                           </tr>';
                    }  
                    $rtn = $rtn . '<tr>
                                    <td><label for="firstname2">' . __('First name','teachpress') . '</label></td>
                                    <td><input name="firstname2" type="text" id="firstname2" value="' . stripslashes($row->firstname) . '" size="30"/></td>
                                  </tr>';
                    $rtn = $rtn . '<tr>
                                    <td><label for="lastname2">' . __('Last name','teachpress') . '</label></td>
                                    <td><input name="lastname2" type="text" id="lastname2" value="' . stripslashes($row->lastname) . '" size="30"/></td>
                                  </tr>';
                    $field2 = get_tp_option('studies');
                    if ($field2 == '1') { 
                       $rtn = $rtn . '<tr>
                                      <td><label for="course_of_studies2">' . __('Course of studies','teachpress') . '</label></td>
                                      <td><select name="course_of_studies2" id="course_of_studies2">';
                       $stud = "SELECT value FROM " . $teachpress_settings . " WHERE category = 'course_of_studies'";
                       $stud = $wpdb->get_results($stud);
                       foreach($stud as $stud) { 
                               if ($stud->value == $row->course_of_studies) {
                                       $current = 'selected="selected"' ;
                               }
                               else {
                                       $current = '' ;
                               }
                               $rtn = $rtn . '<option value="' . stripslashes($stud->value) . '" ' . $current . '>' . stripslashes($stud->value) . '</option>';
                       } 
                       $rtn = $rtn . '</select>
                                 </td>
                                 </tr>';
                    }
                    $field3 = get_tp_option('termnumber');
                    if ($field3 == '1') {
                       $rtn = $rtn . '<tr>
                                      <td><label for="semesternumber2">' . __('Number of terms','teachpress') . '</label></td>
                                      <td><select name="semesternumber2" id="semesternumber2">';
                       for ($i=1; $i<20; $i++) {
                          if ($i == $row->semesternumber) {
                             $current = 'selected="selected"' ;
                          }
                          else {
                             $current = '' ;
                          }
                             $rtn = $rtn . '<option value="' . $i . '" ' . $current . '>' . $i . '</option>';
                       }  
                       $rtn = $rtn . '</select>
                                 </td>
                                 </tr>';
                    }
                    $field4 = get_tp_option('birthday');
                    if ($field4 == '1') {
                            $rtn = $rtn . '<tr>
                                           <td><label for="birthday2">' . __('Date of birth','teachpress') . '</label></td>
                                           <td><input name="birthday2" type="text" value="' . $row->birthday . '" size="30"/>
                                               <em>' . __('Format: JJJJ-MM-TT','teachpress') . '</em></td>
                                         </tr>';
                    }
                    $rtn = $rtn . '<tr>
                                   <td><label for="email2">' . __('E-Mail') . '</label></td>
                                   <td><input name="email2" type="text" id="email2" value="' . $row->email . '" size="50" readonly="true"/></td>
                                   </tr>
                                   </table>';
                    if ($field1 != '1') {
                       $rtn = $rtn . '<input type="hidden" name="matriculation_number2" value="' . $row->matriculation_number . '" />';
                    }
                    if ($field2 != '1') {
                       $rtn = $rtn . '<input type="hidden" name="course_of_studies2" value="' . $row->course_of_studies . '" />';
                    }
                    if ($field3 != '1') {
                       $rtn = $rtn . '<input type="hidden" name=semesternumber2"" value="' . $row->semesternumber . '" />';
                    }
                    if ($field4 != '1') {
                       $rtn = $rtn . '<input type="hidden" name="birthday2" value="' . $row->birthday . '" />';
                    }
                    $rtn = $rtn . '<input name="aendern" type="submit" id="aendern" onclick="teachpress_validateForm(' . $str . 'matriculation_number2' . $str . ',' . $str . $str . ',' . $str . 'RisNum' . $str . ',' . $str . 'firstname2' . $str . ',' . $str . $str . ',' . $str . 'R' . $str . ',' . $str . 'lastname2' . $str . ',' . $str . $str . ',' . $str . 'R' . $str . ',' . $str . 'email2' . $str . ',' . $str . $str . ',' . $str . 'RisEmail' . $str . ');return document.teachpress_returnValue" value="senden" />';
           	 }
              }
           }
   }
   /*
    * Enrollments
   */
   if ($tab == '' || $tab == 'current') {
      // Select all courses where enrollments in the current term are available
      $row = "SELECT * FROM " . $teachpress_courses . " WHERE `semester` = '$sem' AND `parent` = '0' AND (`visible` = '1' OR `visible` = '2') ORDER BY `type` DESC, `name`";
      $row = $wpdb->get_results($row);
      foreach($row as $row) {
         // load all childs
         $row2 = "Select * FROM " . $teachpress_courses . " WHERE `parent` = '$row->course_id' AND (`visible` = '1' OR `visible` = '2') AND (`start` != '0000-00-00 00:00:00') ORDER BY `name`";
         $row2 = $wpdb->get_results($row2);
         // test if a child has an enrollment
         $test = false;
         foreach ( $row2 as $childs ) {
            if ( $childs->start != '0000-00-00 00:00:00' ) {
               $test = true;
            }	
         }
         if ( $row->start != '0000-00-00 00:00:00' || $test == true ) {
            // define some course variables
            $date1 = $row->start;
            $date2 = $row->end;
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
            if (is_user_logged_in() && $auswahl != '') {
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
               $rtn = $rtn . $row->fplaces . ' ' . __('of','teachpress') . ' ' .  $row->places;
            }
            $rtn = $rtn . '</td>
                         </tr>
                         <tr>
                         <td colspan="3" style="border-bottom:1px solid silver; border-collapse: collapse;" class="waitinglist">';
            if ($row->waitinglist == 1 && $row->fplaces == 0) {
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
               if ($row->name == $row2->name) {
                       $row2->name = $row2->type;
               }
               $rtn = $rtn . '<tr>
                              <td rowspan="3" width="25" style="border-bottom:1px solid silver; border-collapse: collapse; vertical-align:middle">';
               if (is_user_logged_in() && $auswahl != '') {
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
                              <td align="center">' . $row2->fplaces . ' ' . __('of','teachpress') . ' ' . $row2->places . '</td>
                             </tr>
                             <tr>
                              <td colspan="3" style="border-bottom:1px solid silver; border-collapse: collapse;" class="waitinglist">';
               $rtn = $rtn . stripslashes(nl2br($row2->comment)) . ' ';
               if ($row2->waitinglist == 1 && $row2->fplaces == 0) {
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
      if (is_user_logged_in() && $auswahl != '') {
         $rtn = $rtn . '<input name="einschreiben" type="submit" value="' . __('Sign up','teachpress') . '" />';
      }
   }
   $rtn = $rtn . '</form>
            </div>';
   return $rtn;
}
?>
