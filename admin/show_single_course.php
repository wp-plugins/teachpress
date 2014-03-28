<?php 
/**
 * This file contains all functions for displaying the show_single_course page in admin menu
 * @package teachpress/admin
 */

/** 
 * Single course overview
 * 
 * $_GET parameters:
 * @param int $course_ID    course ID
 * @param string $sem       semester, from show_courses.php
 * @param string $search    search string, from show_courses.php
*/
function tp_show_single_course_page() {
    
    global $current_user;
    get_currentuserinfo();

    // form
    $checkbox = ( isset( $_POST['checkbox'] ) ) ?  $_POST['checkbox'] : '';
    $waiting = isset( $_POST['waiting'] ) ?  $_POST['waiting'] : '';
    $reg_action = isset( $_POST['reg_action'] ) ?  $_POST['reg_action'] : '';
    $course_ID = intval($_GET['course_ID']);
    
    $link_parameter['sem'] = htmlspecialchars($_GET['sem']);
    $link_parameter['redirect'] = isset( $_GET['redirect'] ) ?  intval($_GET['redirect']) : 0;
    $link_parameter['sort'] = isset ( $_GET['sort'] ) ? $_GET['sort'] : 'asc';
    $link_parameter['search'] = htmlspecialchars($_GET['search']);
    $link_parameter['order'] = isset ( $_GET['order'] ) ? $_GET['order'] : 'name';
    $action = isset( $_GET['action'] ) ?  $_GET['action'] : 'show';
    
    $capability = tp_courses::get_capability($course_ID, $current_user->ID);

    echo '<div class="wrap">';
    echo '<form id="einzel" name="einzel" action="' . $_SERVER['REQUEST_URI'] . '" method="post">';
    echo '<input name="page" type="hidden" value="teachpress/teachpress.php">';
    echo '<input name="action" type="hidden" value="' . $action . '" />';
    echo '<input name="course_ID" type="hidden" value="' . $course_ID . '" />';
    echo '<input name="sem" type="hidden" value="' . $link_parameter['sem'] . '" />';
    echo '<input name="search" type="hidden" value="' . $link_parameter['search'] . '" />';
    echo '<input name="redirect" type="hidden" value="' . $link_parameter['redirect'] . '" />';
    echo '<input name="sort" type="hidden" value="' . $link_parameter['sort'] . '" />';
    echo '<input name="order" type="hidden" value="' . $link_parameter['order'] . '" />';
   
    // change signup
    if ( $reg_action == 'signup' && ( $capability === 'owner' || $capability === 'approved' ) ) {
        tp_courses::change_signup_status($waiting, 'course');
        get_tp_message( __('Participant added','teachpress') );	
    }
    if ( $reg_action == 'signout' && ( $capability === 'owner' || $capability === 'approved' ) ) {
        tp_courses::change_signup_status($checkbox, 'waitinglist');
        get_tp_message( __('Participant moved','teachpress') );
    }
    // add signup
    if ( isset( $_POST['add_signup'] ) && ( $capability === 'owner' || $capability === 'approved' ) ) {
        tp_courses::add_signup($_POST['tp_add_reg_student'], $course_ID);
        get_tp_message( __('Participant added','teachpress') );
    }
    // move signup
    if ( isset( $_POST['move_ok'] ) && ( $capability === 'owner' || $capability === 'approved' ) ) {
        tp_courses::move_signup($checkbox, intval($_POST['tp_rel_course']) );
        tp_courses::move_signup($waiting, intval($_POST['tp_rel_course']) );
        get_tp_message( __('Participant moved','teachpress') );	
    }
    // Delete functions
    if ( isset( $_POST['delete_ok'] ) && ( $capability === 'owner' || $capability === 'approved' ) ) {
        $move_up = isset( $_POST['move_up'] ) ? true : false;
        tp_courses::delete_signup($checkbox, $move_up);
        tp_courses::delete_signup($waiting, $move_up);
        get_tp_message( __('Removing successful','teachpress') );	
    }
    // Add artefact
    if ( isset( $_POST['add_artefact'] ) && ( $capability === 'owner' || $capability === 'approved' ) ) {
        $data = array('parent_id' => intval($_POST['artefact_parent']), 
                      'course_id' => $course_ID, 
                      'title' => htmlspecialchars($_POST['artefact_name']), 
                      'scale' => '', 
                      'passed' => '', 
                      'max_value' => '');
        tp_artefacts::add_artefact($data);
        get_tp_message( __('Artefact added','teachpress') );
    }
    // Add assessment
    if ( isset( $_POST['add_assessment'] ) && ( $capability === 'owner' || $capability === 'approved' ) ) {
        $assessment_target = intval($_POST['assessment_target']);
        $assessment_passed = ( isset($_POST['assessment_passed']) ) ? 1 : 0;
        $artefact_id = ( $assessment_target !== 0 ) ? intval($_POST['assessment_target']) : NULL;
        $course = ( $assessment_target === 0 ) ? $course_ID : NULL;
        $data = array('artefact_id' => $artefact_id, 
                      'course_id' => $course, 
                      'wp_id' => intval($_POST['assessment_participant']), 
                      'value' => htmlspecialchars($_POST['assessment_value']), 
                      'max_value' => '',
                      'type' => htmlspecialchars($_POST['assessment_value_type']),
                      'examiner_id' => get_current_user_id(), 
                      'exam_date' => date('Y-m-d H:i:s'), 
                      'comment' => htmlspecialchars($_POST['assessment_comment']), 
                      'passed' =>  $assessment_passed );
        tp_assessments::add_assessments($data);
        get_tp_message( __('Assessment added','teachpress') );
    }
    // Add capability
    if ( isset( $_POST['cap_submit'] ) ) {
        $cap_user = $_POST['cap_user'];
        if ( tp_courses::has_capability($course_ID, $current_user->ID, 'owner') ) {
            $ret = tp_courses::add_capability($course_ID, $cap_user, 'approved');
            if ( $ret !== false ) {
                get_tp_message( __('Capability added','teachpress') );
            }
        }
        else {
            get_tp_message( __('You have not enough rights for this action','teachpress'), 'red' );
        }
    }
    
    // course data
    $daten = tp_courses::get_course($course_ID, ARRAY_A);
    $parent = tp_courses::get_course($daten["parent"], ARRAY_A);
   
    echo tp_single_course_page::get_back_button($link_parameter);
    echo tp_single_course_page::get_course_headline($course_ID, $daten, $parent, $link_parameter, true);
    echo tp_single_course_page::get_menu($course_ID, $link_parameter, $action, $capability);
    
    echo '<div style="min-width:780px; width:100%; float:left; margin-top: 12px;">';
    
    // Show tab content
    if ( $action === 'assessments' && ( $capability === 'owner' || $capability === 'approved' ) ) {
        tp_single_course_page::get_assessments_tab($course_ID);
    }
    else if ( $action === 'enrollments' && ( $capability === 'owner' || $capability === 'approved' ) ) {
        tp_single_course_page::get_enrollments_tab($course_ID, $daten, $link_parameter, $reg_action, $checkbox, $waiting);
    }
    else if ( $action === 'capabilites' && $capability === 'owner' ) {
        tp_single_course_page::get_capability_tab($daten);
    }
    else {
        tp_single_course_page::get_course_info_tab($daten);
    }
    
    echo '</form>';
    echo '</div>';
    echo '</div>';
    
}

/**
 * This class contains function for generating the single_course admin pages
 * @since 5.0.0
 */
class tp_single_course_page {
    
    /**
     * Shows the add_artefact_form for show_single_course page
     * @param int $course_id
     * @since 5.0.0
     */
    public static function get_artefact_form($course_id) {
        echo '<div id="tp_add_artefact_form" class="teachpress_message" style="display:none;">';
        echo '<p class="teachpress_message_headline">' . __('Add artefact','teachpress') . '</p>';

        echo '<p><label for="artefact_name">' . __('Title','teachpress') . '</label></p>';
        echo '<input name="artefact_name" id="artefact_name" type="text" style="width:50%;"/>';

        echo '<p><label for="artefact_parent">' . __('Parent','teachpress') . '</label></p>';
        echo '<select name="artefact_parent" id="artefact_parent">';
            echo '<option value="0">' . __('None') . '</option>';
            $artefacts = tp_artefacts::get_artefacts($course_id, 0);
            foreach ( $artefacts as $row ) {
                echo '<option value="' . $row['artefact_id'] . '">' . $row['title'] . '</option>';
            }
        echo '</select>';

        echo '<p><input name="add_artefact" type="submit" class="button-primary" value="' . __('Add','teachpress') . '"/> <a onclick="teachpress_showhide(' . "'tp_add_artefact_form'" . ');" class="button-secondary" style="cursor:pointer;">' . __('Cancel', 'teachpress') . '</a></p>';
        echo '</div>';
    }
    
    /**
     * Shows the add_assessment form for show_single_course page
     * @param int $course_id
     * @since 5.0.0
     */
    public static function get_assessment_form($course_id) {
        echo '<div id="tp_add_assessment_form" class="teachpress_message" style="display:none;">';
        echo '<p class="teachpress_message_headline">' . __('Add assessment','teachpress') . '</p>';

        echo '<p><label for="assessment_participant">' . __('Participant','teachpress') . '</label></p>';
        echo '<select name="assessment_participant" id="assessment_participant">';
        $students = tp_courses::get_signups( array('output_type' => ARRAY_A, 'course' => $course_id, 'waitinglist' => 0) );
        foreach ( $students as $stud ) {
            echo '<option value="' . $stud['wp_id'] . '">' . $stud['lastname'] . ', ' . $stud['firstname'] . '</option>';
        }
        echo '</select>';

        echo '<p><label for="assessment_value">' . __('Value/Grade','teachpress') . '</label></p>';
        echo '<input name="assessment_value" id="assessment_value" type="text" style="width:100px;"/>';
        echo '<select name="assessment_value_type" id="assessment_value_type">';
            echo '<option value="percentage">Percentage</option>';
            echo '<option value="grade">Grade</option>';
            echo '<option value="points">Points</option>';
        echo '</select> ';
        echo '<input type="checkbox" name="assessment_passed" id="assessment_passed" value="1"/> <label for="assessment_passed">' . __('Participant has passed','teachpress') . '</label>';

        echo '<p><label for="assessment_target">' . __('Assessment for','teachpress') . '</label></p>';
        echo '<select name="assessment_target" id="assessment_target">';
            echo '<option value="0">' . __('Complete Course','teachpress') . '</option>';
            $artefacts = tp_artefacts::get_artefacts($course_id, 0);
            foreach ( $artefacts as $row ) {
                echo '<option value="' . $row['artefact_id'] . '">' . $row['title'] . '</option>';
            }
        echo '</select>';

        echo '<p><label for="assessment_comment">' . __('Comment','teachpress') . '</label></p>';
        echo '<textarea name="assessment_comment" id="assessment_comment" style="width:50%; height:50px;"></textarea>';

        echo '<p><input name="add_assessment" type="submit" class="button-primary" value="' . __('Add','teachpress') . '"/> <a onclick="teachpress_showhide(' . "'tp_add_assessment_form'" . ');" class="button-secondary" style="cursor:pointer;">' . __('Cancel', 'teachpress') . '</a></p>';
        echo '</div>';
    }
    
    /**
     * Return the back_to button
     * @param array $link_parameter
     * @return string
     * @since 5.0.0
     */
    public static function get_back_button ($link_parameter){
        $save = isset( $_POST['save'] ) ?  $_POST['save'] : '';
        if ( $save == __('Save') ) {
            return;
        }
        if ( $link_parameter['redirect'] != 0 ) {
            return '<p><a href="admin.php?page=teachpress/teachpress.php&amp;course_ID=' . $link_parameter['redirect'] . '&amp;sem=' . $link_parameter['sem'] . '&amp;search=' . $link_parameter['search'] . '&amp;action=show" class="button-secondary" title="' . __('Back','teachpress') . '">&larr; ' . __('Back','teachpress') . '</a></p>';
        }
        else {
             return '<p><a href="admin.php?page=teachpress/teachpress.php&amp;sem=' . $link_parameter['sem'] . '&amp;search=' . $link_parameter['search'] . '" class="button-secondary" title="' . __('Back','teachpress') . '">&larr; ' . __('Back','teachpress') . '</a></p>';
        }
        
    }

    /**
     * Returns the page headline
     * @param int $course_ID
     * @param array $course_data
     * @param array $parent_data
     * @param array $link_parameter
     * @param string $edit_link
     * @return string
     * @since 5.0.0
     */
    public static function get_course_headline($course_ID, $course_data, $parent_data, $link_parameter, $edit_link = true) {
        $link = '';
        $parent_name = '';
        
        if ($course_data["parent"] != 0) {
            if ($parent_data["course_id"] == $course_data["parent"]) {
                $parent_name = '<a href="admin.php?page=teachpress/teachpress.php&amp;course_ID=' . $parent_data["course_id"] . '&amp;sem=' . $link_parameter['sem'] . '&amp;search=' . $link_parameter['search'] . '&amp;action=show&amp;redirect=' . $course_ID . '" title="' . stripslashes($parent_data["name"]) . '" style="color:#464646">' . stripslashes($parent_data["name"]) . '</a> &rarr; ';
            }
        }
        
        if ( $edit_link === true ) {
            $link = '<small><a href="admin.php?page=teachpress/teachpress.php&amp;course_ID=' . $course_ID . '&amp;sem=' . $link_parameter['sem'] . '&amp;search=' . $link_parameter['search'] . '&amp;action=edit" class="teachpress_link" style="cursor:pointer;">' . __('Edit','teachpress') . '</a></small>';
        }

        return '<h2 style="padding-top:5px;">' . $parent_name . stripslashes($course_data["name"]) . ' ' . $course_data["semester"] . ' <span class="tp_break">|</span> ' . $link . '</h2>';
    }
    
    /**
     * Returns the page menu
     * @param int $course_ID
     * @param array $link_parameter
     * @param string $action
     * @param strin $capability
     * @return string
     * @since 5.0.0
     */
    public static function get_menu($course_ID, $link_parameter, $action, $capability){
        $enrollments_tab = '';
        $assessment_tab = '';
        $capability_tab = '';
        
        $set_info_tab = ( $action === 'show' ) ? 'nav-tab nav-tab-active' : 'nav-tab';
        $info_tab = '<a href="admin.php?page=teachpress/teachpress.php&amp;course_ID=' . $course_ID . '&amp;sem=' . $link_parameter['sem'] . '&amp;search=' . $link_parameter['search'] . '&amp;action=show" class="' . $set_info_tab . '">' . __('Info','teachpress') . '</a> ';
        
        if ( $capability === 'owner' || $capability === 'approved' ) {
            $set_enrollments_tab = ( $action === 'enrollments' ) ? 'nav-tab nav-tab-active' : 'nav-tab';
            $enrollments_tab = '<a href="admin.php?page=teachpress/teachpress.php&amp;course_ID=' . $course_ID . '&amp;sem=' . $link_parameter['sem'] . '&amp;search=' . $link_parameter['search'] . '&amp;action=enrollments" class="' . $set_enrollments_tab . '">' . __('Enrollments','teachpress') . '</a> ';
        }
        if ( $capability === 'owner' || $capability === 'approved' ) {
            $set_assessment_tab = ( $action === 'assessments' ) ? 'nav-tab nav-tab-active' : 'nav-tab';
            $assessment_tab = '<a href="admin.php?page=teachpress/teachpress.php&amp;course_ID=' . $course_ID . '&amp;sem=' . $link_parameter['sem'] . '&amp;search=' . $link_parameter['search'] . '&amp;action=assessments" class="' . $set_assessment_tab . '">' . __('Assessments','teachpress') . '</a> ';
        }
        
        if ( $capability === 'owner' ) {
            $set_capability_tab = ( $action === 'capabilites' ) ? 'nav-tab nav-tab-active' : 'nav-tab';
            $capability_tab = '<a href="admin.php?page=teachpress/teachpress.php&amp;course_ID=' . $course_ID . '&amp;sem=' . $link_parameter['sem'] . '&amp;search=' . $link_parameter['search'] . '&amp;action=capabilites" class="' . $set_capability_tab . '">' . __('Capabilites','teachpress') . '</a> ';
        }
        
        return '<h3 class="nav-tab-wrapper">' . $info_tab . $enrollments_tab. $assessment_tab . $capability_tab . '</h3>';
    }
    
    /**
     * Shows the course assessment tab
     * @param int $course_ID
     * @since 5.0.0
     */
    public static function get_assessments_tab($course_ID) {
        echo '<div class="tp_actions">';
        echo '<span style="margin-right:15px;"><a onclick="teachpress_showhide(' . "'tp_add_artefact_form'" . ');" id="teachpress_add_artefact" class="button-secondary" style="cursor:pointer;">' . __('Add artefact','teachpress') . '</a></span> ';
        echo '<span style="margin-right:15px;"><a onclick="teachpress_showhide(' . "'tp_add_assessment_form'" . ');" style="cursor:pointer;" id="teachpress_add_assessment" class="button-secondary">' . __('Add assessment','teachpress') . '</a></span> ';
        echo '</div>';
        tp_single_course_page::get_artefact_form($course_ID);
        tp_single_course_page::get_assessment_form($course_ID);
        echo '<table id="tp_assessment_overview" class="widefat">';
        echo '<thead>';
        echo '<tr>';
        echo '<th></th>';
        echo '<th>' . __('Last name','teachpress') . '</th>';
        echo '<th>' . __('First name','teachpress') . '</th>';
        $artefacts = tp_artefacts::get_artefacts($course_ID, 0);
        foreach ( $artefacts as $row ) {
            echo '<th>' . $row['title'] . '</th>';
        }
        echo '<th>' . __('Course','teachpress') . '</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        $pos = 1;
        $students = tp_courses::get_signups( array('output_type' => ARRAY_A, 'course' => $course_ID, 'waitinglist' => 0) );
        foreach ( $students as $stud ) {
            echo '<tr>';
            echo '<td></td>';
            echo '<td>' . $stud['lastname'] . '</td>';
            echo '<td>' . $stud['firstname'] . '</td>';
            foreach ( $artefacts as $row ) {
                $class = '';
                echo '<td>';
                $assessments = tp_assessments::get_assessments($stud['wp_id'], $row['artefact_id']);
                foreach ( $assessments as $single_assessment ) {
                    $class = ( $single_assessment['comment'] != '' ) ? 'tp_assessment_comment' : '';
                    echo '<a href="javascript:teachpress_open_description(' . $single_assessment['assessment_id'] . ',' . $pos . ');" class="tp_assessment ' . $class . '" id="tp_assessment_' . $single_assessment['assessment_id'] . '">' . $single_assessment['value'] . '</a>';
                    echo '<div id="tp_description_' .  $single_assessment['assessment_id'] . '" class="tp_description">
                         <input name="tp_desc_comment_' .  $single_assessment['assessment_id'] . '" id="tp_desc_comment_' .  $single_assessment['assessment_id'] . '" type="hidden" value="' . $single_assessment['comment'] . '"/>
                         <input name="tp_desc_date_' .  $single_assessment['assessment_id'] . '" id="tp_desc_date_' .  $single_assessment['assessment_id'] . '" type="hidden" value="' . $single_assessment['exam_date'] . '"/>
                         </div> ';
                }
                echo '</td>';
            }
            $course_result = tp_assessments::get_assessments($stud['wp_id'], '', $course_ID);
            echo '<td>';
            foreach ( $course_result as $end ) {
                $class = '';
                if ( $end['passed'] == 1 ) {
                    $class .= 'tp_assessment_passed';
                }
                if ( $end['comment'] != '' ) {
                    $class .= ' tp_assessment_comment';
                }
                echo '<a href="javascript:teachpress_open_description(' . $end['assessment_id'] . ',' . $pos . ');" class="tp_assessment ' . $class . '" id="tp_assessment_' . $end['assessment_id'] . '">' . $end['value'] . '</a>';
                    echo '<div id="tp_description_' .  $end['assessment_id'] . '" class="tp_description">
                         <input name="tp_desc_comment_' .  $end['assessment_id'] . '" id="tp_desc_comment_' .  $end['assessment_id'] . '" type="hidden" value="' . $end['comment'] . '"/>
                         </div> ';
            }
            echo '</td>';
            echo '</tr>';
            $pos++;
        }
        echo '</tbody>';
        echo '</table>';
    }
    
    /**
     * Shows the capabilites tab for show_single_course page
     * @param array $course_data
     * @since 5.0.0
     */
    public static function get_capability_tab ($course_data) {
        if ( $course_data['use_capabilites'] != 1 ) {
            get_tp_message( __("You can't set user capabilites here, because you are using global capabilites for this course.",'teachpress'), 'orange' );
            return;
        }
        echo '<div class="tp_actions"><a id="teachpress_add_capability" class="button-secondary" onclick="javascript:teachpress_showhide(' . "'add_capability'" .');">' . __('Add new','teachpress') . '</a></div>';
        echo '<div id="add_capability" class="teachpress_message" style="display:none;">';
        echo '<form name="add_cap" method=""post>';
        echo '<p class="teachpress_message_headline">' . __('Add capability for user','teachpress') . '</p>';
        echo '<label for="cap_user">' . __('Username', 'teachpress') . '</label> ';
        echo '<select id="cap_user" name="cap_user">';
        echo '<option>- ' . __('Select user','teachpress') . ' -</option>';
        $capabilites = tp_courses::get_capabilities($course_data['course_id']);
        $users = get_users();
        $array_caps = array();
        foreach ($capabilites as $row) {
            array_push($array_caps, $row['wp_id']);
        }
        foreach ($users as $user) {
            if (!in_array($user->ID, $array_caps) && user_can( $user->ID, 'use_teachpress_courses' )  ) {
                echo '<option>' . $user->display_name . '</option>';
            }
        }
        echo '</select>';
        echo '<p><input name="cap_submit" type="submit" class="button-primary" value="' . _('Add','teachpress') . '" /> <a class="button-secondary" onclick="javascript:teachpress_showhide(' . "'add_capability'" .');">' . __('Cancel','teachpress') . '</a></p>';
        echo '</form>';
        echo '</div>';
        echo '<table class="widefat">';
        echo '<thead>';
        echo '<tr>';
        echo '<th class="check-column"></th>';
        echo '<th>' . __('User','teachpress') . '</th>';
        echo '<th>' . __('Username','teachpress') . '</th>';
        echo '<th>' . __('Capability','teachpress') . '</th>';
        echo '</thead>';
        echo '</tr>';
        echo '<tbody>';
        $class_alternate = true;
        foreach ( $capabilites as $row ) {
            if ( $class_alternate === true ) {
                $tr_class = 'class="alternate"';
                $class_alternate = false;
            }
            else {
                $tr_class = '';
                $class_alternate = true;
            }
            $user = get_userdata( $row['wp_id'] );
            echo '<tr ' . $tr_class . '>';
            echo '<th class="check-column"></th>';
            echo '<td>';
            echo '<span style="float:left; margin-right:10px;">' . get_avatar($row['wp_id'], 35) . '</span> <strong>' . $user->user_login . '</strong>';
            if ( $row['capability'] !== 'owner' ) {
                echo '<div class="tp_row_actions"><a href="admin.php?page=teachpress/teachpress.php&course_ID=6&sem=Example%20term&search=&action=capabilites" style="color:red;" title="' . __('Delete','teachpress') . '">' . __('Delete','teachpress') . '</a></div>';
            }
            echo '</td>';
            echo '<td>' . $user->display_name . '</td>';
            echo '<td>' . $row['capability'] . '</td>';
            echo '<tr>';
        }
        echo '</tbody>';
        echo '</table>';
    }
    
    /**
     * Shows the info tab for show_single_course page
     * @param array $cours_data
     * @since 5.0.0
     */
    public static function get_course_info_tab ($cours_data) {
        ?>
     <div style="width:24%; float:right; padding-left:1%; padding-bottom:1%;">
         <div class="postbox">
             <h3 style="padding: 7px 10px; cursor:default;"><span><?php _e('Enrollments','teachpress'); ?></span></h3>
             <div class="inside">
                  <table cellpadding="8">
                    <?php if ($cours_data["start"] != '0000-00-00 00:00:00' && $cours_data["end"] != '0000-00-00 00:00:00') {?>
                    <tr>
                      <td colspan="2"><strong><?php _e('Start','teachpress'); ?></strong></td>
                      <td colspan="2"><?php echo substr($cours_data["start"],0,strlen($cours_data["start"])-3); ?></td>
                    </tr>  
                    <tr>  
                      <td colspan="2"><strong><?php _e('End','teachpress'); ?></strong></td>
                      <td colspan="2"><?php echo substr($cours_data["end"],0,strlen($cours_data["end"])-3); ?></td>
                    </tr>
                    <tr>
                      <td><strong><?php _e('Places','teachpress'); ?></strong></th>
                      <td><?php echo $cours_data["places"]; ?></td>  
                      <td><strong><?php _e('free places','teachpress'); ?></strong></td>
                      <?php $free_places = tp_courses::get_free_places($cours_data["course_id"], $cours_data["places"]); ?>
                      <td <?php if ( $free_places < 0 ) { echo ' style="color:#ff6600; font-weight:bold;"';} ?>><?php echo $free_places ?></td>
                    </tr>  
                    <?php } else {?>
                    <tr>
                      <td colspan="4"><?php _e('none','teachpress'); ?></td>
                    </tr>  
                    <?php } ?>  
                  </table>
             </div>
         </div>
       </div>
       <div style="width:75%; float:left; padding-bottom:10px;">
           <div class="postbox">
               <h3 style="padding: 7px 10px; cursor:default;"><span><?php _e('General','teachpress'); ?></span></h3>
               <div class="inside">
                    <table cellpadding="8">
                        <tr>
                         <td width="230"><strong><?php _e('ID'); ?></strong></td>
                         <td><?php echo $cours_data["course_id"]; ?></td>
                        </tr>
                      <tr>
                        <td><strong><?php _e('Type'); ?></strong></td>
                        <td><?php echo stripslashes($cours_data["type"]); ?></td>
                      </tr>
                      <tr>
                        <td><strong><?php _e('Visibility','teachpress'); ?></strong></td>
                        <td>
                         <?php 
                            if ( $cours_data["visible"] == 1 ) {
                                 _e('normal','teachpress');
                            }
                            elseif ( $cours_data["visible"] == 2 ) {
                                 _e('extend','teachpress');
                            }
                            else {
                                 _e('invisible','teachpress');
                            } 
                         ?></td> 
                      </tr>
                      <tr>
                        <td><strong><?php _e('Date','teachpress'); ?></strong></td>
                        <td><?php echo stripslashes($cours_data["date"]); ?></td>
                      </tr>
                      <tr>
                          <td><strong><?php _e('Room','teachpress'); ?></strong></td>
                        <td><?php echo stripslashes($cours_data["room"]); ?></td>
                      </tr>
                      <tr>
                        <td><strong><?php _e('Lecturer','teachpress'); ?></strong></td>
                        <td><?php echo stripslashes($cours_data["lecturer"]); ?></td>
                      </tr>
                      <tr>
                        <td><strong><?php _e('Comment','teachpress'); ?></strong></td>
                        <td><?php echo stripslashes($cours_data["comment"]); ?></td>
                      </tr>
                      <tr>
                        <td><strong><?php _e('Related content','teachpress'); ?></strong></td>
                        <td><?php if ( $cours_data["rel_page"] != 0) {echo '<a href="' . get_permalink( $cours_data["rel_page"] ) . '" target="_blank" class="teachpress_link">' . get_permalink( $cours_data["rel_page"] ) . '</a>'; } else { _e('none','teachpress'); } ?></td>
                      </tr>
                </table>
               </div>
           </div>

       </div>
    <?php
    }
    
    /**
     * Shows the enrollments tab for show_single_course page
     * @param int $course_ID
     * @param array_a $cours_data
     * @param array_a $link_parameter
     * @param string $reg_action
     * @param array $checkbox
     * @param array $waiting
     * @since 5.0.0
     */
    public static function get_enrollments_tab ($course_ID, $cours_data, $link_parameter, $reg_action, $checkbox, $waiting) {
        // field options
        $fields = get_tp_options('teachpress_stud','`setting_id` ASC');
        $visible_fields = array();
        foreach ($fields as $row) {
            $data = tp_db_helpers::extract_column_data($row->value);
            if ( $data['admin_visibility'] === 'true') {
                array_push($visible_fields, $row->variable);
            }
        }

        // sort and order of signups
        $order_s = ( $link_parameter['order'] === 'name' ) ? 'st.lastname' : 's.date';
        $sort_s = ( $link_parameter['sort'] === 'asc' ) ? ' ASC' : ' DESC';

        // enrollments / signups
        $enrollments = tp_courses::get_signups( array('output_type' => ARRAY_A, 'course' => $course_ID, 'order' => $order_s . $sort_s, 'waitinglist' => 0) );
        $count_enrollments = count($enrollments);

        // waitinglist
        $waitinglist = tp_courses::get_signups( array('output_type' => ARRAY_A, 'course' => $course_ID, 'order' => $order_s . $sort_s, 'waitinglist' => 1) );
        $count_waitinglist = count($waitinglist);

        ?>
       <div class="tp_actions">
            <span style="margin-right:15px;">
            <select name="reg_action">
                <option value="0">- <?php _e('Bulk actions','teachpress'); ?> -</option>
                <option value="signout"><?php _e('Move to waitinglist','teachpress'); ?></option>
                <option value="signup"><?php _e('Move to course','teachpress'); ?></option>
                <option value="move"><?php _e('Move to a related course','teachpress'); ?></option>
                <option value="delete"><?php _e('Delete','teachpress'); ?></option>
            </select>
            <input name="tp_submit" type="submit" class="button-secondary" value="<?php _e('OK', 'teachpress'); ?>"/>
            </span>
           <span style="margin-right:15px;">
            <a id="teachpress_add_signup" style="cursor:pointer;" class="button-secondary" onclick="teachpress_showhide('tp_add_signup_form');" title="<?php _e('Add signup','teachpress'); ?>"><?php _e('Add signup','teachpress'); ?></a>
            <a id="teachpress_create_list" href="admin.php?page=teachpress/teachpress.php&amp;course_ID=<?php echo $course_ID; ?>&amp;sem=<?php echo $link_parameter['sem']; ?>&amp;search=<?php echo $link_parameter['search']; ?>&amp;redirect=<?php echo $link_parameter['redirect']; ?>&amp;action=list" class="button-secondary" title="<?php _e('Attendance list','teachpress'); ?>"><?php _e('Attendance list','teachpress'); ?></a>
           </span>
           <span style="margin-right:15px;">
            <a id="teachpress_create_csv" class="button-secondary" href="<?php echo plugins_url(); ?>/teachpress/export.php?course_ID=<?php echo $course_ID; ?>&amp;type=csv" title="<?php _e('CSV export','teachpress'); ?>">CSV</a>
            <a id="teachpress_create_xls" class="button-secondary" href="<?php echo plugins_url(); ?>/teachpress/export.php?course_ID=<?php echo $course_ID; ?>&amp;type=xls" title="<?php _e('XLS export','teachpress'); ?>">XLS</a>
           </span>
           <a id="teachpress_send_mail" class="button-secondary" href="admin.php?page=teachpress/teachpress.php&amp;course_ID=<?php echo $course_ID; ?>&amp;sem=<?php echo $link_parameter['sem']; ?>&amp;search=<?php echo $link_parameter['search']; ?>&amp;redirect=<?php echo $link_parameter['redirect']; ?>&amp;action=mail&amp;type=course" title="<?php _e('Send E-Mail','teachpress'); ?>"><?php _e('Send E-Mail','teachpress'); ?></a>
       </div>
       <!-- Add students -->
       <div class="teachpress_message" id="tp_add_signup_form" style="display: none;">
           <p class="teachpress_message_headline"><?php _e('Add students manually','teachpress'); ?></p>
           <select name="tp_add_reg_student" id="tp_add_reg_student">
               <option value="0">- <?php _e('Select student','teachpress'); ?>- </option>
               <?php
                $row1 = tp_students::get_students();
                $zahl = 0;
                foreach($row1 as $row1) {
                   if ($zahl != 0 && $merke[0] != $row1->lastname[0]) {
                      echo '<option>----------</option>';
                   }
                   echo '<option value="' . $row1->wp_id . '">' . stripslashes($row1->lastname) . ', ' . stripslashes($row1->firstname) . ' (' . $row1->matriculation_number . ')</option>';
                   $merke = $row1->lastname;
                   $zahl++;
                } ?>
           </select>
           <p>
               <input type="submit" name="add_signup" class="button-primary" value="<?php _e('Add', 'teachpress'); ?>" />
               <a onclick="teachpress_showhide('tp_add_signup_form');" class="button-secondary" style="cursor:pointer;"><?php _e('Cancel', 'teachpress'); ?></a>
           </p>
       </div>
       <!-- Move to a course -->
       <?php if ( $reg_action === 'move' ) { 
           $p = $cours_data['parent'] != 0 ? $cours_data['parent'] : $cours_data['course_id'];
           $related_courses = tp_courses::get_courses( array('parent' => $p ) );
           if ( count($related_courses) != 0 ) {
            ?>
            <div class="teachpress_message" id="tp_move_to_course">
                <p class="teachpress_message_headline"><?php _e('Move to a related course','teachpress'); ?></p>
                <p><?php _e('If you move a signup to an other course the signup status will be not changed. So a waitinglist will be a waitinglist entry.','teachpress'); ?></p>
                <select name="tp_rel_course" id="tp_rel_course">
                    <?php
                    foreach ( $related_courses as $rel ) {
                        $selected = $rel->course_id == $cours_data['course_id'] ? ' selected="selected"' : '';
                        echo '<option value="' . $rel->course_id . '"' . $selected . '>' . $rel->course_id . ' - ' . $rel->name . '</option>';
                    }
                    ?>
                </select>
                <p><input name="move_ok" type="submit" class="button-primary" value="<?php _e('Move','teachpress'); ?>"/>
                    <a href="admin.php?page=teachpress/teachpress.php&course_ID=<?php echo $course_ID; ?>&amp;sem=<?php echo $link_parameter['sem']; ?>&amp;search=<?php echo $link_parameter['search']; ?>&amp;order=<?php echo $link_parameter['order']; ?>&amp;sort=<?php echo $link_parameter['sort']; ?>&amp;action=show" class="button-secondary"><?php _e('Cancel','teachpress'); ?></a></p>
            </div>
       <?php } 
             else {
                get_tp_message(__('Error: There are no related courses.','teachpress'));
             }
       } ?>
       <!-- Delete entries -->
       <?php if ( $reg_action == 'delete' ) { ?>
       <div class="teachpress_message" id="tp_delete entries" style="">
           <p class="teachpress_message_headline"><?php _e('Are you sure to delete the selected elements?','teachpress'); ?></p>
           <p><input type="checkbox" name="move_up" id="move_up" checked="checked" /> <label for="move_up"><?php _e('Move up entries from the waitinglist as replacement for deleted signups.','teachpress'); ?></label></p>
           <p><input name="delete_ok" type="submit" class="button-primary" value="<?php _e('Delete','teachpress'); ?>"/>
               <a href="admin.php?page=teachpress/teachpress.php&course_ID=<?php echo $course_ID; ?>&amp;sem=<?php echo $link_parameter['sem']; ?>&amp;search=<?php echo $link_parameter['search']; ?>&amp;order=<?php echo $link_parameter['order']; ?>&amp;sort=<?php echo $link_parameter['sort']; ?>&amp;action=show" class="button-secondary"><?php _e('Cancel','teachpress'); ?></a></p>
       <!-- END Menu -->    
       </div>
       <?php } ?>
       <h3><?php _e('Signups','teachpress'); ?></h3>
       <table class="widefat">
        <thead>
         <tr>
           <th class="check-column">
            <input name="tp_check_all" id="tp_check_all" type="checkbox" value="" onclick="teachpress_checkboxes('checkbox[]','tp_check_all');" />
           </th>
           <?php
           // Order option parameter
           if ( $link_parameter['order'] == 'name' ) {
               $display_date = 'none';
               $display_name = 'inline';
               $sort_date = ( $link_parameter['sort'] === 'asc' ) ? 'asc' : 'desc';
               $sort_name = ( $link_parameter['sort'] === 'desc' ) ? 'asc' : 'desc';
               $sort_sign_name = $sort_name == 'asc' ? '&Downarrow;' : '&Uparrow;';
               $sort_sign_date = $sort_name == 'asc' ? '&Downarrow;' : '&Uparrow;';
           }
           else {
               $display_date = 'inline';
               $display_name = 'none';
               $sort_date = ( $link_parameter['sort'] === 'asc' ) ? 'desc' : 'asc';
               $sort_name = ( $link_parameter['sort'] === 'desc' ) ? 'asc' : 'desc';
               $sort_sign_name = $sort_name == 'asc' ? '&Downarrow;' : '&Uparrow;';
               $sort_sign_date = $sort_name == 'asc' ? '&Downarrow;' : '&Uparrow;';
           }
           ?>
           <th><a href="admin.php?page=teachpress/teachpress.php&course_ID=<?php echo $course_ID; ?>&amp;sem=<?php echo $link_parameter['sem']; ?>&amp;search=<?php echo $link_parameter['search']; ?>&amp;order=name&amp;sort=<?php echo $sort_name; ?>&amp;action=show"><?php _e('Last name','teachpress'); ?></a> <span style="display: <?php echo $display_name; ?>"><?php echo $sort_sign_name; ?></span></th>
           <th><?php _e('First name','teachpress'); ?></th>
           <th><?php _e('User account','teachpress'); ?></th>
           <th><?php _e('E-Mail'); ?></th>
           <?php
           foreach ($fields as $row) {
                $data = tp_db_helpers::extract_column_data($row->value);
                if ( $data['admin_visibility'] === 'true' ) {
                    echo '<th>' . $data['title'] . '</th>';
                }
            }
           ?>
           <th><a href="admin.php?page=teachpress/teachpress.php&course_ID=<?php echo $course_ID; ?>&amp;sem=<?php echo $link_parameter['sem']; ?>&amp;search=<?php echo $link_parameter['search']; ?>&amp;order=date&amp;sort=<?php echo $sort_date; ?>&amp;action=show"><?php _e('Registered at','teachpress'); ?></a> <span style="display: <?php echo $display_date; ?>"><?php echo $sort_sign_date; ?></span></th>
         </tr>
        </thead>  
        <tbody>
       <?php
       if ($count_enrollments == 0) {
           echo '<tr><td colspan="8"><strong>' . __('No entries','teachpress') . '</strong></td></tr>';
       }
       else {
           // all registered students for the course
           foreach ($enrollments as $enrollments) {
                echo '<tr>';
                $checked = '';
                if ( ( $reg_action == "delete" || $reg_action == 'move' ) && $checkbox != '' ) { 
                   for( $k = 0; $k < count( $checkbox ); $k++ ) { 
                      if ( $enrollments["con_id"] == $checkbox[$k] ) { $checked = 'checked="checked" '; } 
                   } 
                }
                echo '<th class="check-column"><input name="checkbox[]" type="checkbox" value="' . $enrollments["con_id"] . '" ' . $checked . '/></th>';
                echo '<td>' . stripslashes($enrollments["lastname"]) . '</td>';
                echo '<td>' . stripslashes($enrollments["firstname"]) . '</td>';
                echo '<td>' . stripslashes($enrollments["userlogin"]) . '</td>';
                echo '<td><a href="admin.php?page=teachpress/teachpress.php&amp;course_ID=' . $course_ID . '&amp;sem=' . $link_parameter['sem'] . '&amp;search=' . $link_parameter['search'] . '&amp;action=mail&amp;single=' . stripslashes($enrollments["email"]) . '" title="' . __('send E-Mail','teachpress') . '">' . stripslashes($enrollments["email"]) . '</a></td>';
                for ($i = 0; $i< count($visible_fields); $i++) {
                    echo '<td>' . $enrollments[$visible_fields[$i]] . '</td>';
                }
                echo '<td>' . $enrollments["date"] . '</td>';
                echo '</tr>';

           } 
       }?>
       </tbody>
               </table>
               <?php
       // waitinglist
       if ($count_waitinglist != 0) { ?>
           <h3><?php _e('Waitinglist','teachpress'); ?></h3>
           <table class="widefat">
            <thead>
             <tr>
               <th class="check-column">
                <input name="tp_check_all" id="tp_check_all" type="checkbox" value="" onclick="teachpress_checkboxes('waiting[]','tp_check_all');" />
               </th>
               <th><?php _e('Last name','teachpress'); ?></th>
               <th><?php _e('First name','teachpress'); ?></th>
               <th><?php _e('User account','teachpress'); ?></th>
               <th><?php _e('E-Mail'); ?></th>
               <?php
                foreach ($fields as $row) {
                     $data = tp_db_helpers::extract_column_data($row->value);
                     if ( $data['admin_visibility'] === 'true' ) {
                         echo '<th>' . $data['title'] . '</th>';
                     }
                 }
                ?>
               <th><?php _e('Registered at','teachpress'); ?></th>
             </tr>
            </thead>  
            <tbody> 
            <?php
            foreach ( $waitinglist as $waitinglist ) {
               echo '<tr>';
               $checked = '';
               if ( ($reg_action == "delete" || $reg_action == 'move') && $waiting != '' ) { 
                    for( $k = 0; $k < count( $waiting ); $k++ ) { 
                        if ( $waitinglist["con_id"] == $waiting[$k] ) { $checked = 'checked="checked" '; } 
                    } 
               }
               echo '<th class="check-column"><input name="waiting[]" type="checkbox" value="' . $waitinglist["con_id"] . '" ' . $checked . '/></th>';
               echo '<td>' . stripslashes($waitinglist["lastname"]) . '</td>';
               echo '<td>' . stripslashes($waitinglist["firstname"]) . '</td>';
               echo '<td>' . stripslashes($waitinglist["userlogin"]) . '</td>';
               echo '<td><a href="admin.php?page=teachpress/teachpress.php&amp;course_ID=' . $course_ID . '&amp;sem=' . $link_parameter['sem'] . '&amp;search=' . $link_parameter['search'] . '&amp;action=mail&amp;single=' . stripslashes($waitinglist["email"]) . '" title="' . __('send E-Mail','teachpress') . '">' . stripslashes($waitinglist["email"]) . '</a></td>';
               for ($i = 0; $i< count($visible_fields); $i++) {
                    echo '<td>' . $waitinglist[$visible_fields[$i]] . '</td>';
               }
               echo '<td>' . stripslashes($waitinglist["date"]) . '</td>';
               echo '<tr>';
            }?>
            </tbody>
            </table>
       <?php  }
    }
    
}
?>