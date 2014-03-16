<?php 
/* overview for students
 *
 * from editstudent.php (GET):
 * @param string $search
 * @param string $students_group
*/
function teachpress_students_page() { 

    global $user_ID;
    get_currentuserinfo();
    $checkbox = isset ( $_GET['checkbox'] ) ? $_GET['checkbox'] : '';
    $bulk = isset ( $_GET['bulk'] ) ? $_GET['bulk'] : '';
    $search = isset ( $_GET['search'] ) ? htmlspecialchars($_GET['search']) : ''; 
    $students_group = isset ( $_GET['students_group'] ) ? htmlspecialchars($_GET['students_group']) : '';
    $action = isset ($_GET['action']) ? $_GET['action'] : '';

    // Page menu
    $page = 'teachpress/students.php';
    $entries_per_page = 50;
    // Handle limits
    if (isset($_GET['limit'])) {
        $curr_page = (int)$_GET['limit'] ;
        if ( $curr_page <= 0 ) {
            $curr_page = 1;
        }
        $entry_limit = ( $curr_page - 1 ) * $entries_per_page;
    }
    else {
        $entry_limit = 0;
        $curr_page = 1;
    }

    // Send mail (received from mail.php)
    if( isset( $_POST['send_mail'] ) ) {
        $from = isset ( $_POST['from'] ) ? htmlspecialchars($_POST['from']) : '';
        $to = isset ( $_POST['recipients'] ) ? htmlspecialchars($_POST['recipients']) : '';
        $recipients_option = isset ( $_POST['recipients_option'] ) ? htmlspecialchars($_POST['recipients_option']) : '';
        $subject = isset ( $_POST['subject'] ) ? htmlspecialchars($_POST['subject']) : '';
        $text = isset ( $_POST['text'] ) ? htmlspecialchars($_POST['text']) : '';
        $attachments = isset ( $_POST['attachments'] ) ? $_POST['attachments'] : '';
        tp_mail::sendMail($from, $to, $subject, $text, $recipients_option, $attachments);
        $message = __('E-Mail sent','teachpress');
        get_tp_message($message);
    }

    // Event handler
    if ( $action == 'show' ) {
        teachpress_show_student_page();
    }
    elseif ( $action == 'edit' ) {
        teachpress_edit_student_page();
    }
    elseif ( $action == 'add' ) {
        teachpress_students_new_page();
    }
    else {
        ?>
        <div class="wrap">
        <form name="search" method="get" action="admin.php">
        <input name="page" type="hidden" value="<?php echo $page; ?>" />
        <?php
        // Delete students part 1
        if ( $bulk == "delete" ) {
            echo '<div class="teachpress_message">
            <p class="teachpress_message_headline">' . __('Are you sure to delete the selected elements?','teachpress') . '</p>
            <p><input name="delete_ok" type="submit" class="button-primary" value="' . __('Delete','teachpress') . '"/>
            <a href="admin.php?page=teachpress/students.php&amp;search=' . $search . '&amp;students_group=' . $students_group . '&amp;limit=' . $curr_page . '" class="button-secondary"> ' . __('Cancel','teachpress') . '</a></p>
            </div>';
        }
        // Delete students part 2
        if ( isset($_GET['delete_ok']) ) {
            tp_students::delete_student($checkbox, $user_ID);
            $message = __('Removing successful','teachpress');
            get_tp_message($message);
        }
        // Load data
        $number_entries = tp_students::get_students( array('course_of_studies' => $students_group, 'search' => $search, 'output_type' => OBJECT, 'count' => true ) );
        $students = tp_students::get_students( array('course_of_studies' => $students_group, 'search' => $search, 'limit' => $entry_limit . ',' . $entries_per_page, 'output_type' => ARRAY_A ) );
        
        // field options
        $fields = get_tp_options('teachpress_stud','`setting_id` ASC');
        $visible_fields = array();
        $select_fields = array();
        foreach ($fields as $row) {
            $data = tp_db_helpers::extract_column_data($row->value);
            if ( $data['admin_visibility'] === 'true') {
                array_push($visible_fields, $row->variable);
            }
            if ( $data['admin_visibility'] === 'true' && $data['type'] === 'SELECT' ) {
                array_push($select_fields, $row->variable);
            }
        }
        ?>
        <h2><?php _e('Students','teachpress'); ?> <a class="add-new-h2" href="admin.php?page=teachpress/students.php&amp;action=add"><?php _e('Add student','teachpress'); ?></a></h2>
        <div id="searchbox" style="float:right; padding-bottom:5px;">  
        <?php if ($search != "") { ?>
        <a href="admin.php?page=teachpress/students.php" style="font-size:14px; font-weight:bold; text-decoration:none; padding-right:3px;" title="<?php _e('Cancel the search','teachpress'); ?>">X</a>
        <?php } ?>
        <input name="search" type="text" value="<?php echo stripslashes($search); ?>"/></td>
        <input name="go" type="submit" value="<?php _e('Search','teachpress'); ?>" id="teachpress_search_senden" class="button-secondary"/>
    </div>
    <div class="tablenav" style="padding-bottom:5px;">
        <select name="bulk" id="bulk">
            <option>- <?php _e('Bulk actions','teachpress'); ?> -</option>
            <option value="delete"><?php _e('Delete','teachpress'); ?></option>
        </select>
        <input type="submit" name="teachpress_submit" value="<?php _e('OK','teachpress'); ?>" id="doaction" class="button-secondary"/>
        <?php 
        $selects = false;
        for ($i = 0; $i< count($select_fields); $i++) {
            echo '<select name="' . $select_fields[$i] . '">';
            echo '<option value="">- ' . __('All','teachpress') . ' -</option>';
            $options = get_tp_options($select_fields[$i]);
            foreach ( $options as $option ) {
                echo '<option>' . $option->value  . '</option>';
            }
            echo '</select>';
            $selects = true;
        }
        if ( $selects === true ) {
            echo ' <input name="anzeigen" type="submit" id="teachpress_search_senden" value="' . __('Show','teachpress') . '" class="button-secondary"/>';
        }
        // Page Menu
        echo tp_admin_page_menu ($number_entries, $entries_per_page, $curr_page, $entry_limit, "admin.php?page=$page&amp;", "search=$search&amp;students_group=$students_group"); ?>
    </div>
    <table class="widefat">
        <thead>
        <tr>
            <th class="check-column">
                <input name="tp_check_all" id="tp_check_all" type="checkbox" value="" onclick="teachpress_checkboxes('checkbox[]','tp_check_all');" />
            </th>
            <?php
            echo '<th>' . __('Last name','teachpress') . '</th>';
            echo '<th>' . __('First name','teachpress') . '</th>'; 
            echo '<th>' . __('User account','teachpress') . '</th>'; 
            echo '<th>' . __('E-Mail') . '</th>';
            foreach ($fields as $row) {
                $data = tp_db_helpers::extract_column_data($row->value);
                if ( $data['admin_visibility'] === 'true' ) {
                    echo '<th>' . $data['title'] . '</th>';
                }
            }
            ?>
        </tr>
        </thead>
        <tbody> 
    <?php
        // Show students
        if (count($students) == 0) { 
            echo '<tr><td colspan="9"><strong>' . __('Sorry, no entries matched your criteria.','teachpress') . '</strong></td></tr>';
        }
        else {
            $class_alternate = true;
            foreach($students as $row3) { 
                if ( $class_alternate === true ) {
                    $tr_class = 'class="alternate"';
                    $class_alternate = false;
                }
                else {
                    $tr_class = '';
                    $class_alternate = true;
                }
                echo '<tr ' . $tr_class . '>';
                echo '<th class="check-column"><input type="checkbox" name="checkbox[]" id="checkbox" value="' . $row3['wp_id'] . '"';
                if ( $bulk == "delete") { 
                    for( $i = 0; $i < count( $checkbox ); $i++ ) { 
                        if ( $row3['wp_id'] == $checkbox[$i] ) { echo 'checked="checked"';} 
                    } 
                }
                echo '/></th>';
                $link_name = ( $row3['lastname'] !== '' ) ? stripslashes($row3['lastname']) : '[' . __('empty','teachpress') . ']';
                echo '<td><a href="admin.php?page=teachpress/students.php&amp;student_ID=' . $row3['wp_id'] . '&amp;search=' . $search . '&amp;students_group=' . $students_group . '&amp;limit=' . $curr_page . '&amp;action=show" class="teachpress_link" title="' . __('Click to edit','teachpress') . '"><strong>' . $link_name . '</strong></a></td>';
                echo '<td>' . stripslashes($row3['firstname']) . '</td>';
                echo '<td>' . stripslashes($row3['userlogin']) . '</td>';
                echo '<td><a href="admin.php?page=teachpress/teachpress.php&amp;student_ID=' . $row3['wp_id'] . '&amp;search=' . $search . '&amp;students_group=' . $students_group . '&amp;limit=' . $curr_page . '&amp;action=mail&amp;single=' . $row3['email'] . '" title="' . __('send E-Mail','teachpress') . '">' . $row3['email'] . '</a></td>';
                for ($i = 0; $i< count($visible_fields); $i++) {
                    echo '<td>' . $row3[$visible_fields[$i]] . '</td>';
                }
                echo '</tr>';
            } 
        }
        ?> 
        </tbody>
        </table>
    <div class="tablenav"><div class="tablenav-pages" style="float:right;">
        <?php 
        if ($number_entries > $entries_per_page) {
            echo tp_admin_page_menu ($number_entries, $entries_per_page, $curr_page, $entry_limit, "admin.php?page=$page&amp;", "search=$search&amp;students_group=$students_group", 'bottom');
        } 
        else {
            if ($number_entries == 1) {
                echo $number_entries . ' ' . __('entry','teachpress');
            }
            else {
                echo $number_entries . ' ' . __('entries','teachpress');
            }
        }?>
        </div></div>
        </form>
        </div>
        <?php
    }
} ?>