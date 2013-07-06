<?php 
/* Single course overview
 * $_GET parameters:
 * @param $course_ID (INT) - course ID
 * @param $sem (String) - semester, from show_courses.php
 * @param $search (String) - search string, from show_courses.php
*/
function tp_show_single_course_page() {

   // form
   $checkbox = isset( $_GET['checkbox'] ) ?  $_GET['checkbox'] : '';
   $waiting = isset( $_GET['waiting'] ) ?  $_GET['waiting'] : '';
   $reg_action = isset( $_GET['reg_action'] ) ?  $_GET['reg_action'] : '';
   $save = isset( $_GET['save'] ) ?  $_GET['save'] : '';
   $course_ID = intval($_GET['course_ID']);
   $search = htmlspecialchars($_GET['search']);
   $sem = htmlspecialchars($_GET['sem']);
   $redirect = isset( $_GET['redirect'] ) ?  intval($_GET['redirect']) : 0;
   $sort = isset ( $_GET['sort'] ) ? $_GET['sort'] : 'asc';
   $order = isset ( $_GET['order'] ) ? $_GET['order'] : 'name';
   
   // teachPress settings
   $field2 = get_tp_option('studies');
   ?>
   <div class="wrap">
   <form id="einzel" name="einzel" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="get">
   <input name="page" type="hidden" value="teachpress/teachpress.php">
   <input name="action" type="hidden" value="show" />
   <input name="course_ID" type="hidden" value="<?php echo $course_ID; ?>" />
   <input name="sem" type="hidden" value="<?php echo $sem; ?>" />
   <input name="search" type="hidden" value="<?php echo $search; ?>" />
   <input name="redirect" type="hidden" value="<?php echo $redirect; ?>" />
   <input name="sort" type="hidden" value="<?php echo $sort; ?>" />
   <input name="order" type="hidden" value="<?php echo $order; ?>" />
   <?php
   // Event handler
   if ( $reg_action == 'signup' ) {
       tp_change_signup_status($waiting, 'course');
       get_tp_message( __('Participant added','teachpress') );	
   }
   if ( $reg_action == 'signout' ) {
       tp_change_signup_status($checkbox, 'waitinglist');
       get_tp_message( __('Participant moved','teachpress') );
   }
   if ( isset( $_GET['add_signup'] ) ) {
       tp_add_direct_signup($_GET['tp_add_reg_student'], $course_ID);
       get_tp_message( __('Participant added','teachpress') );
   }
   if ( isset($_GET['move_ok']) ) {
       tp_move_signup($checkbox, intval($_GET['tp_rel_course']) );
       tp_move_signup($waiting, intval($_GET['tp_rel_course']) );
       get_tp_message( __('Participant moved','teachpress') );	
   }
   if ( isset( $_GET['delete_ok'] ) ) {
       $move_up = isset( $_GET['move_up'] ) ? true : false;
       tp_delete_signup($checkbox, $move_up);
       tp_delete_signup($waiting, $move_up);
       get_tp_message( __('Removing successful','teachpress') );	
   }
   
   // sort and order of signups
   $order_s = $order == 'name' ? 'st.lastname' : 's.date';
   $sort_s = $sort == 'asc' ? ' ASC' : ' DESC';

   // course data
   $daten = get_tp_course($course_ID, ARRAY_A);
   $parent = get_tp_course($daten["parent"], ARRAY_A);

   // enrollments / signups
   $enrollments = get_tp_course_signups( array('output_type' => ARRAY_A, 'course' => $course_ID, 'order' => $order_s . $sort_s, 'waitinglist' => 0) );
   $count_enrollments = count($enrollments);

   // waitinglist
   $waitinglist = get_tp_course_signups( array('output_type' => ARRAY_A, 'course' => $course_ID, 'order' => $order_s . $sort_s, 'waitinglist' => 1) );
   $count_waitinglist = count($waitinglist);
   
   // the back button
   if ($save != __('Save')) {
       if ( $redirect != 0 ) {
           echo '<p><a href="admin.php?page=teachpress/teachpress.php&amp;course_ID=' . $redirect . '&amp;sem=' . $sem . '&amp;search=' . $search . '&amp;action=show" class="button-secondary" title="' . __('Back','teachpress') . '">&larr; ' . __('Back','teachpress') . '</a></p>';
       }
       else {
            echo '<p><a href="admin.php?page=teachpress/teachpress.php&amp;sem=' . $sem . '&amp;search=' . $search . '" class="button-secondary" title="' . __('Back','teachpress') . '">&larr; ' . __('Back','teachpress') . '</a></p>';
       }
   } 
   // define course name
   if ($daten["parent"] != 0) {
     if ($parent["course_id"] == $daten["parent"]) {
         $parent_name = '<a href="admin.php?page=teachpress/teachpress.php&amp;course_ID=' . $parent["course_id"] . '&amp;sem=' . $sem . '&amp;search=' . $search . '&amp;action=show&amp;redirect=' . $daten["course_id"] . '" title="' . stripslashes($parent["name"]) . '" style="color:#464646">' . stripslashes($parent["name"]) . '</a> &rarr; ';
     }
   }
   else {
      $parent_name = "";
   }
   ?>
   <h2 style="padding-top:5px;"><?php echo $parent_name . stripslashes($daten["name"]) . ' ' . $daten["semester"]; ?> <span class="tp_break">|</span> <small><a href="admin.php?page=teachpress/teachpress.php&amp;course_ID=<?php echo $course_ID; ?>&amp;sem=<?php echo $sem; ?>&amp;search=<?php echo $search; ?>&amp;action=edit" class="teachpress_link" style="cursor:pointer;"><?php _e('Edit','teachpress'); ?></a></small></h2>
   <div style="min-width:780px; width:100%; max-width:1100px;">
   <div style="width:24%; float:right; padding-left:1%; padding-bottom:1%;">
   <table border="1" cellspacing="0" cellpadding="0" class="widefat" id="teachpress_edit">
       <thead>
         <tr>
           <th colspan="4"><?php _e('Enrollments','teachpress'); ?></th>
         </tr>
         <?php if ($daten["start"] != '0000-00-00 00:00:00' && $daten["end"] != '0000-00-00 00:00:00') {?>
         <tr>
           <td colspan="2"><strong><?php _e('Start','teachpress'); ?></strong></td>
           <td colspan="2"><?php echo substr($daten["start"],0,strlen($daten["start"])-3); ?></td>
         </tr>  
         <tr>  
           <td colspan="2"><strong><?php _e('End','teachpress'); ?></strong></td>
           <td colspan="2"><?php echo substr($daten["end"],0,strlen($daten["end"])-3); ?></td>
         </tr>
         <tr>
           <td><strong><?php _e('Places','teachpress'); ?></strong></th>
           <td><?php echo $daten["places"]; ?></td>  
           <td><strong><?php _e('free places','teachpress'); ?></strong></td>
           <?php $free_places = get_tp_course_free_places($daten["course_id"], $daten["places"]); ?>
           <td <?php if ( $free_places < 0 ) { echo ' style="color:#ff6600; font-weight:bold;"';} ?>><?php echo $free_places ?></td>
         </tr>  
         <?php } else {?>
         <tr>
           <td colspan="4"><?php _e('none','teachpress'); ?></td>
         </tr>  
         <?php } ?>  
         </thead>
   </table>
   </div>
   <div style="width:75%; float:left; padding-bottom:10px;">
   <table class="widefat">
       <thead>
         <tr>
           <th colspan="4"><?php _e('Meta Information','teachpress'); ?></th>
         </tr>
         <tr>
           <td width="170"><strong><?php _e('Type'); ?></strong></td>
           <td><?php echo stripslashes($daten["type"]); ?></td>
           <td width="100"><strong><?php _e('ID'); ?></strong></td>
           <td width="140"><?php echo $daten["course_id"]; ?></td>
         </tr>
         <tr>
           <td><strong><?php _e('Visibility','teachpress'); ?></strong></td>
           <td colspan="3">
            <?php 
               if ( $daten["visible"] == 1 ) {
                    _e('normal','teachpress');
               }
               elseif ( $daten["visible"] == 2 ) {
                    _e('extend','teachpress');
               }
               else {
                    _e('invisible','teachpress');
               } 
            ?></td> 
         </tr>
         <tr>
           <td><strong><?php _e('Date','teachpress'); ?></strong></td>
           <td><?php echo stripslashes($daten["date"]); ?></td>
           <td><strong><?php _e('Room','teachpress'); ?></strong></td>
           <td><?php echo stripslashes($daten["room"]); ?></td>
         </tr>
         <tr>
           <td><strong><?php _e('Lecturer','teachpress'); ?></strong></td>
           <td colspan="3"><?php echo stripslashes($daten["lecturer"]); ?></td>
         </tr>
         <tr>
           <td><strong><?php _e('Comment','teachpress'); ?></strong></td>
           <td colspan="3"><?php echo stripslashes($daten["comment"]); ?></td>
         </tr>
         <tr>
           <td><strong><?php _e('Related page','teachpress'); ?></strong></td>
           <td colspan="3"><?php if ( $daten["rel_page"] != 0) {echo '<a href="' . get_permalink( $daten["rel_page"] ) . '" target="_blank" class="teachpress_link">' . get_permalink( $daten["rel_page"] ) . '</a>'; } else { _e('none','teachpress'); } ?></td>
         </tr>
         </thead>
   </table>
   </div>
   <div style="min-width:780px; width:100%; max-width:1100px; float: left; margin-top: 12px;">
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
        <a id="teachpress_add_signup" style="cursor:pointer;" class="button-secondary" onclick="teachpress_showhide('tp_add_signup_form')" title="<?php _e('Add signup','teachpress'); ?>"><?php _e('Add signup','teachpress'); ?></a>
        <a id="teachpress_create_list" href="admin.php?page=teachpress/teachpress.php&amp;course_ID=<?php echo $course_ID; ?>&amp;sem=<?php echo $sem; ?>&amp;search=<?php echo $search; ?>&amp;redirect=<?php echo $redirect; ?>&amp;action=list" class="button-secondary" title="<?php _e('Attendance list','teachpress'); ?>"><?php _e('Attendance list','teachpress'); ?></a>
       </span>
       <span style="margin-right:15px;">
        <a id="teachpress_create_csv" class="button-secondary" href="<?php echo plugins_url(); ?>/teachpress/export.php?course_ID=<?php echo $course_ID; ?>&amp;type=csv" title="<?php _e('CSV export','teachpress'); ?>">CSV</a>
        <a id="teachpress_create_xls" class="button-secondary" href="<?php echo plugins_url(); ?>/teachpress/export.php?course_ID=<?php echo $course_ID; ?>&amp;type=csv" title="<?php _e('XLS export','teachpress'); ?>">XLS</a>
       </span>
       <a id="teachpress_send_mail" class="button-secondary" href="admin.php?page=teachpress/teachpress.php&amp;course_ID=<?php echo $course_ID; ?>&amp;sem=<?php echo $sem; ?>&amp;search=<?php echo $search; ?>&amp;redirect=<?php echo $redirect; ?>&amp;action=mail&amp;type=course" title="<?php _e('Send E-Mail','teachpress'); ?>"><?php _e('Send E-Mail','teachpress'); ?></a>
   </div>
   <!-- Add students -->
   <div class="teachpress_message" id="tp_add_signup_form" style="display: none;">
       <p class="teachpress_message_headline"><?php _e('Add students manually','teachpress'); ?></p>
       <select name="tp_add_reg_student" id="tp_add_reg_student">
           <option value="0">- <?php _e('Select student','teachpress'); ?>- </option>
           <?php
            $row1 = get_tp_students();
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
           <a onclick="teachpress_showhide('tp_add_signup_form')" class="button-secondary" style="cursor:pointer;"><?php _e('Cancel', 'teachpress'); ?></a>
       </p>
   </div>
   <!-- Move to a course -->
   <?php if ( $reg_action == 'move' ) { 
       $p = $daten['parent'] != 0 ? $daten['parent'] : $daten['course_id'];
       $related_courses = get_tp_courses( array('parent' => $p ) );
       if ( count($related_courses) != 0 ) {
        ?>
        <div class="teachpress_message" id="tp_move_to_course">
            <p class="teachpress_message_headline"><?php _e('Move to a related course','teachpress'); ?></p>
            <p><?php _e('If you move a signup to an other course the signup status will be not changed. So a waitinglist will be a waitinglist entry.','teachpress'); ?></p>
            <select name="tp_rel_course" id="tp_rel_course">
                <?php
                foreach ( $related_courses as $rel ) {
                    $selected = $rel->course_id == $daten['course_id'] ? ' selected="selected"' : '';
                    echo '<option value="' . $rel->course_id . '"' . $selected . '>' . $rel->course_id . ' - ' . $rel->name . '</option>';
                }
                ?>
            </select>
            <p><input name="move_ok" type="submit" class="button-primary" value="<?php _e('Move','teachpress'); ?>"/>
                <a href="admin.php?page=teachpress/teachpress.php&course_ID=<?php echo $course_ID; ?>&amp;sem=<?php echo $sem; ?>&amp;search=<?php echo $search; ?>&amp;order=<?php echo $order; ?>&amp;sort=<?php echo $sort; ?>&amp;action=show" class="button-secondary"><?php _e('Cancel','teachpress'); ?></a></p>
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
           <a href="admin.php?page=teachpress/teachpress.php&course_ID=<?php echo $course_ID; ?>&amp;sem=<?php echo $sem; ?>&amp;search=<?php echo $search; ?>&amp;order=<?php echo $order; ?>&amp;sort=<?php echo $sort; ?>&amp;action=show" class="button-secondary"><?php _e('Cancel','teachpress'); ?></a></p>
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
       if ( $order == 'name' ) {
           $display_date = 'none';
           $display_name = 'inline';
           $sort_date = $sort == 'asc' ? 'asc' : 'desc';
           $sort_name = $sort == 'desc' ? 'asc' : 'desc';
           $sort_sign_name = $sort_name == 'asc' ? '&Downarrow;' : '&Uparrow;';
           $sort_sign_date = $sort_name == 'asc' ? '&Downarrow;' : '&Uparrow;';
       }
       else {
           $display_date = 'inline';
           $display_name = 'none';
           $sort_date = $sort == 'asc' ? 'desc' : 'asc';
           $sort_name = $sort == 'desc' ? 'asc' : 'desc';
           $sort_sign_name = $sort_name == 'asc' ? '&Downarrow;' : '&Uparrow;';
           $sort_sign_date = $sort_name == 'asc' ? '&Downarrow;' : '&Uparrow;';
       }
       ?>
       <th><a href="admin.php?page=teachpress/teachpress.php&course_ID=<?php echo $course_ID; ?>&amp;sem=<?php echo $sem; ?>&amp;search=<?php echo $search; ?>&amp;order=name&amp;sort=<?php echo $sort_name; ?>&amp;action=show"><?php _e('Last name','teachpress'); ?></a> <span style="display: <?php echo $display_name; ?>"><?php echo $sort_sign_name; ?></span></th>
       <th><?php _e('First name','teachpress'); ?></th>
       <?php
       if ($field2 == '1') {
           echo '<th>' .  __('Course of studies','teachpress') . '</th>';
       }	
       ?>
       <th><?php _e('User account','teachpress'); ?></th>
       <th><?php _e('E-Mail'); ?></th>
       <th><a href="admin.php?page=teachpress/teachpress.php&course_ID=<?php echo $course_ID; ?>&amp;sem=<?php echo $sem; ?>&amp;search=<?php echo $search; ?>&amp;order=date&amp;sort=<?php echo $sort_date; ?>&amp;action=show"><?php _e('Registered at','teachpress'); ?></a> <span style="display: <?php echo $display_date; ?>"><?php echo $sort_sign_date; ?></span></th>
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
            if ($field2 == '1') {
               echo '<td>' . stripslashes($enrollments["course_of_studies"]) . '</td>';
            }
            echo '<td>' . stripslashes($enrollments["userlogin"]) . '</td>';
            echo '<td><a href="admin.php?page=teachpress/teachpress.php&amp;course_ID=' . $course_ID . '&amp;sem=' . $sem . '&amp;search=' . $search . '&amp;action=mail&amp;single=' . stripslashes($enrollments["email"]) . '" title="' . __('send E-Mail','teachpress') . '">' . stripslashes($enrollments["email"]) . '</a></td>';
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
           <?php if ($field2 == '1') {?>
           <th><?php _e('Course of studies','teachpress'); ?></th>
           <?php } ?>
           <th><?php _e('User account','teachpress'); ?></th>
           <th><?php _e('E-Mail'); ?></th>
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
           if ($field2 == '1') {
               echo '<td>' . stripslashes($waitinglist["course_of_studies"]) . '</td>';
           }
           echo '<td>' . stripslashes($waitinglist["userlogin"]) . '</td>';
           echo '<td><a href="admin.php?page=teachpress/teachpress.php&amp;course_ID=' . $course_ID . '&amp;sem=' . $sem . '&amp;search=' . $search . '&amp;action=mail&amp;single=' . stripslashes($waitinglist["email"]) . '" title="' . __('send E-Mail','teachpress') . '">' . stripslashes($waitinglist["email"]) . '</a></td>';
           echo '<td>' . stripslashes($waitinglist["date"]) . '</td>';
           echo '<tr>';
        }?>
        </tbody>
        </table>
   <?php  } ?>
   </div>
   </form>
   </div>
</div>
<?php } ?>