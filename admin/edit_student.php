<?php
/**
 * This file contains all functions for displaying the edit_student page in admin menu
 * @package teachpress/admin
 */

/** 
 * Edit a student
 * @param int $student_ID (GET)
 * @param string $search (GET)
 * @param string $students_group (GET)
 * @since 4.0.0
*/ 
function teachpress_show_student_page() {
   $student = htmlspecialchars($_GET['student_ID']);
   $students_group = htmlspecialchars($_GET['students_group']);
   $search = htmlspecialchars($_GET['search']);
   $entry_limit = intval($_GET['limit']);
   
   $fields = get_tp_options('teachpress_stud','`setting_id` ASC');
   
   ?> 
   <div class="wrap">
   <?php
   // Event handler
   if ( isset( $_GET['delete'] )) {
        tp_delete_registration($_GET['checkbox']);
        $message = __('Enrollment deleted','teachpress');
        get_tp_message($message);
   }
   echo '<p><a href="admin.php?page=teachpress/students.php&amp;search=' . $search . '&amp;students_group=' . $students_group . '&amp;limit=' . $entry_limit . '" class="button-secondary" title="' . __('Back','teachpress') . '">&larr; ' . __('Back','teachpress') . ' </a></p>';
   ?>
   <form name="edit_student" method="get" action="admin.php">
   <input name="page" type="hidden" value="teachpress/students.php" />
   <input name="action" type="hidden" value="show" />
   <input name="student_ID" type="hidden" value="<?php echo $student; ?>" />
   <input name="students_group" type="hidden" value="<?php echo $students_group; ?>" />
   <input name="search" type="hidden" value="<?php echo $search; ?>" />
   <input name="limit" type="hidden" value="<?php echo $entry_limit; ?>" />
   <?php
      $row3 = tp_students::get_student($student);
      $row4 = tp_students::get_student_meta($student);
   ?>
    <h2 style="padding-top:0px;"><?php echo stripslashes($row3['firstname']); ?> <?php echo stripslashes($row3['lastname']); ?> <span class="tp_break">|</span> <small><a href="<?php echo 'admin.php?page=teachpress/students.php&amp;student_ID=' . $student . '&amp;search=' . $search . '&amp;students_group=' . $students_group . '&amp;limit=' . $entry_limit . '&amp;action=edit'; ?>" id="daten_aendern"><?php _e('Edit','teachpress'); ?> </a></small></h2>
     <div style="width:55%; padding-bottom:10px;">
     <table border="0" cellpadding="0" cellspacing="5" class="widefat">
        <thead>
        <?php
        echo '<tr>';
        echo '<td width="130"><strong>' . __('WordPress User-ID','teachpress') . '</strong></td>';
        echo '<td style="vertical-align:middle;">' . $row3['wp_id'] . '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<tr>';
        echo '<td width="130"><strong>' . __('User account','teachpress') . '</strong></td>';
        echo '<td style="vertical-align:middle;">' . $row3['userlogin'] . '</td>';
        echo '</tr>';
        echo '<tr>';
        echo'<td><strong>' . __('E-Mail') . '</strong></td>';
        echo '<td style="vertical-align:middle;"><a href="admin.php?page=teachpress/teachpress.php&amp;student_ID=' . $row3['wp_id'] . '&amp;search=' . $search . '&amp;students_group=' . $students_group . '&amp;limit=' . $entry_limit . '&amp;action=mail&amp;single=' . $row3['email'] . '" title="' . __('Send E-Mail to','teachpress') . ' ' . $row3['firstname'] . ' ' . $row3['lastname'] . '">' . $row3['email'] . '</a></td>';
        echo '</tr>';
        foreach ($fields as $row) {
            $data = tp_db_helpers::extract_column_data($row->value);
            echo '<tr>';
            echo '<td><strong>' . $data['title'] . '</strong></td>';
            foreach ($row4 as $meta) {
                if ( $meta['meta_key'] === $row->variable ) {
                    echo '<td style="vertical-align:middle;">' . $meta['meta_value'] . '</td>';
                    continue;
                }
                
            }
            echo '</tr>';
           
        }
        ?>
      </thead>   
     </table>
     </div>
   </form>
   <form method="get" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
   <input name="page" type="hidden" value="teachpress/editstudent.php">
   <input name="student_ID" type="hidden" value="<?php echo $student; ?>">
   <input name="search" type="hidden" value="<?php echo $search; ?>">
   <h3><?php _e('Signups','teachpress'); ?></h3>
   <table cellpadding="5" class="widefat">
    <thead>
        <tr>
        <th>&nbsp;</th>
        <th><?php _e('Enrollment-Nr.','teachpress'); ?></th>
        <th><?php _e('Registered at','teachpress'); ?></th>
        <th><?php _e('Course','teachpress'); ?></th>
        <th><?php _e('Type'); ?></th>
        <th><?php _e('Date','teachpress'); ?></th>
        </tr>
    </thead>    
    <tbody>
    <?php
        // get signups
        $row = tp_students::get_signups( array('wp_id' => $student, 'mode' => 'reg'));
        if ( count($row) != 0) {
            foreach($row as $row) {
                $parent_name = ( $row->parent_name != "" ) ? $row->parent_name . ' ' : '';
                echo '<tr>';
                echo '<th class="check-column"><input name="checkbox[]" type="checkbox" value="' . $row->con_id . '"/></th>';
                echo '<td>' . $row->con_id . '</td>';
                echo '<td>' . $row->timestamp . '</td>';
                echo '<td>' . stripslashes($row->parent_name) . stripslashes($row->name) . '</td>';
                echo '<td>' . stripslashes($row->type) . '</td>';
                echo '<td>' . stripslashes($row->date) . '</td>';
                echo '</tr>';
            } 
        }
        else {
            echo '<tr><td colspan="6"><strong>' . __('Sorry, no entries matched your criteria.','teachpress') . '</strong></td></tr>';
        }?>
    </tbody>
   </table>
   <?php
   $row = tp_students::get_signups( array('wp_id' => $student, 'mode' => 'wtl') );
   if ( count($row) != 0 ) {
        echo '<h3>' . __('Waitinglist','teachpress') . '</h3>';
        ?>
        <table cellpadding="5" class="widefat">
            <thead>
                <tr>
                    <th>&nbsp;</th>
                    <th><?php _e('Enrollment-Nr.','teachpress'); ?></th>
                    <th><?php _e('Registered at','teachpress'); ?></th>
                    <th><?php _e('Course','teachpress'); ?></th>
                    <th><?php _e('Type'); ?></th>
                    <th><?php _e('Date','teachpress'); ?></th>
                </tr>
            </thead>    
            <tbody>
            <?php     
            foreach($row as $row) {
                if ( $row->waitinglist == 1 ) {
                    $parent_name = ( $row->parent_name != "" ) ? $row->parent_name . ' ' : '';
                    echo '<tr>';
                    echo '<th class="check-column"><input name="checkbox[]" type="checkbox" value="' . $row->con_id . '"/></th>';
                    echo '<td>' . $row->con_id . '</td>';
                    echo '<td>' . $row->timestamp . '</td>';
                    echo '<td>' . stripslashes($parent_name) . stripslashes($row->name) . '</td>';
                    echo '<td>' . stripslashes($row->type) . '</td>';
                    echo '<td>' . stripslashes($row->date) . '</td>';
                    echo '</tr>';
                }
            }
                ?>
            </tbody>
        </table>
   <?php } ?>
   <table border="0" cellspacing="0" cellpadding="7" id="einzel_optionen">
     <tr>
        <td><?php _e('delete enrollment','teachpress'); ?></td>
        <td> <input name="delete" type="submit" value="<?php _e('Delete','teachpress'); ?>" id="teachpress_search_delete" class="button-secondary"/></td>
     </tr>
   </table>
   </form>
   </div>
<?php } 

/**
 * Edit student UI
 */
function teachpress_edit_student_page() {
    $user_ID = intval($_GET['student_ID']);
    $students_group = htmlspecialchars($_GET['students_group']);
    $search = htmlspecialchars($_GET['search']);
    $entry_limit = intval($_GET['limit']);
    $fields = get_tp_options('teachpress_stud','`setting_id` ASC', ARRAY_A);
    
    if ( isset($_POST['tp_change_user'] ) ) {
        // delete old meta data
        tp_students::delete_student_meta($user_ID);
        
        $data = array (
            'firstname' => htmlspecialchars($_POST['firstname']),
            'lastname' => htmlspecialchars($_POST['lastname']),
            'userlogin' => htmlspecialchars($_POST['userlogin']),
            'email' => htmlspecialchars($_POST['email'])
        );
        tp_enrollments::add_student_meta($user_ID, $fields, filter_input_array(INPUT_POST, $_POST));
        tp_students::change_student($user_ID, $data, false);
        get_tp_message( __('Saved') );
    }
    
    echo '<div class="wrap">';
    echo '<p><a href="admin.php?page=teachpress/students.php&amp;student_ID=' . $user_ID . '&amp;search=' . $search . '&amp;students_group=' . $students_group . '&amp;limit=' . $entry_limit . '&amp;action=show" class="button-secondary" title="' . __('Back','teachpress') . '">&larr; ' . __('Back','teachpress') . ' </a></p>';
    echo '<h2>' . __('Edit Student','teachpress') . '</h2>';
    echo tp_registration_form($user_ID, 'admin');
    echo '</div>';
}

?>