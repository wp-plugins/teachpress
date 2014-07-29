<?php
/**
 * This file contains all functions for displaying the show_courses page in admin menu
 * @package teachpress/admin
 */

/**
 * Add help tab for show courses page
 */
function tp_show_course_page_help () {
    $screen = get_current_screen();  
    $screen->add_help_tab( array(
        'id'        => 'tp_show_course_help',
        'title'     => __('Display courses','teachpress'),
        'content'   => '<p><strong>' . __('Shortcodes') . '</strong></p>
                        <p>' . __('You can use courses in a page or article with the following shortcodes:','teachpress') . '</p>
                        <p>' . __('For course informations','teachpress') . ': <strong>[tpdate id="x"]</strong> ' . __('x = Course-ID','teachpress') . '</p>
                        <p>' . __('For the course list','teachpress') . ': <strong>[tpcourselist]</strong></p>
                        <p>' . __('For the enrollment system','teachpress') . ': <strong>[tpenrollments]</strong></p>
                        <p><strong>' . __('More information','teachpress') . '</strong></p>
                        <p><a href="http://mtrv.wordpress.com/teachpress/shortcode-reference/" target="_blank" title="teachPress Shortcode Reference (engl.)">teachPress Shortcode Reference (engl.)</a></p>',
    ) );
} 

/**
 * Show courses page
 */
function teachpress_show_courses_page() {
     // test if teachpress database is up to date
     $test = get_tp_option('db-version');
     $version = get_tp_version();
     // if is the current one
     if ($test != $version) {
           $message = __('An database update is necessary.','teachpress') . ' <a href="options-general.php?page=teachpress/settings.php&amp;up=1">' . __('Update','teachpress') . '</a>';
           echo '<div class="wrap">';
           get_tp_message($message);
           echo '</div>';
     }
     
     // Send mail (received from mail.php)
     if( isset( $_POST['send_mail'] ) ) {
          $from = isset ( $_POST['from'] ) ? htmlspecialchars($_POST['from']) : '';
          $to = isset ( $_POST['recipients'] ) ? htmlspecialchars($_POST['recipients']) : '';
          $subject = isset ( $_POST['subject'] ) ? htmlspecialchars($_POST['subject']) : '';
          $text = isset ( $_POST['text'] ) ? htmlspecialchars($_POST['text']) : '';
          $options['backup_mail'] = isset ( $_POST['backup_mail'] ) ? htmlspecialchars($_POST['backup_mail']) : '';
          $options['recipients'] = isset ( $_POST['recipients_option'] ) ? htmlspecialchars($_POST['recipients_option']) : '';
          $attachments = isset ( $_POST['attachments'] ) ? $_POST['attachments'] : '';
          $ret = tp_mail::sendMail($from, $to, $subject, $text, $options, $attachments);
          $message = $ret == true ? __('E-Mail sent','teachpress') : __('Error: E-Mail could not sent','teachpress');
          get_tp_message($message);
     }

     // Event Handler
     $action = isset( $_GET['action'] ) ? htmlspecialchars($_GET['action']) : '';
     
     if ( $action === 'edit' ) {
          tp_add_course_page();
     }
     elseif ( $action === 'show' || $action === 'assessments' || $action === 'enrollments' || $action === 'capabilites' ) {
          tp_show_single_course_page();
     }
     elseif ( $action === 'list' ) {
          tp_lists_page();
     }
     elseif ( $action === 'mail' ) {
          tp_show_mail_page();
     }
     else {
     /*
      * Show courses
     */

     $search = isset( $_GET['search'] ) ? htmlspecialchars($_GET['search']) : '';
     $checkbox = isset( $_GET['checkbox'] ) ? $_GET['checkbox'] : '';
     $bulk = isset( $_GET['bulk'] ) ? $_GET['bulk'] : '';
     $copysem = isset( $_GET['copysem'] ) ? $_GET['copysem'] : '';
     
     // if the semester is selected by user
     if (isset($_GET['sem'])) {
       $sem = htmlspecialchars($_GET['sem']);
     }
     else {
       $sem = get_tp_option('sem');
     }
     ?> 

     <div class="wrap">
         <h2><?php _e('Courses','teachpress'); ?> <a href="admin.php?page=teachpress/add_course.php" class="add-new-h2"><?php _e('Create','teachpress'); ?></a></h2>
     <form id="showcourse" name="showcourse" method="get" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
     <input name="page" type="hidden" value="teachpress/teachpress.php" />
           <?php 	
           // delete a course, part 1
           if ( $bulk === "delete" ) {
                echo '<div class="teachpress_message">
                <p class="teachpress_message_headline">' . __('Are you sure to delete the selected elements?','teachpress') . '</p>
                <p><input name="delete_ok" type="submit" class="button-primary" value="' . __('Delete','teachpress') . '"/>
                <a href="admin.php?page=teachpress/teachpress.php&sem=' . $sem . '&search=' . $search . '" class="button-secondary"> ' . __('Cancel','teachpress') . '</a></p>
                </div>';
           }
           // delete a course, part 2
           if ( isset($_GET['delete_ok']) ) {
               tp_courses::delete_courses($checkbox);
                $message = __('Removing successful','teachpress');
                get_tp_message($message);
           }
           // copy a course, part 1
           if ( $bulk === "copy" ) { ?>
                <div class="teachpress_message">
                <p class="teachpress_message_headline"><?php _e('Copy courses','teachpress'); ?></p>
                <p class="teachpress_message_text"><?php _e('Select the term, in which you will copy the selected courses.','teachpress'); ?></p>
                <p class="teachpress_message_text">
                <select name="copysem" id="copysem">
                    <?php
                    $term = get_tp_options('semester');
                    foreach ($term as $term) { 
                        $current = ( $term->value == $sem ) ? 'selected="selected"' : '';
                        echo '<option value="' . $term->value . '" ' . $current . '>' . stripslashes($term->value) . '</option>';
                    } ?> 
                </select>
                <input name="copy_ok" type="submit" class="button-primary" value="<?php _e('copy','teachpress'); ?>"/>
                <a href="<?php echo 'admin.php?page=teachpress/teachpress.php&sem=' . $sem . '&search=' . $search . ''; ?>" class="button-secondary"> <?php _e('Cancel','teachpress'); ?></a>
                </p>
                </div>
           <?php
           }
           // copy a course, part 2
           if ( isset($_GET['copy_ok']) ) {
                tp_copy_course($checkbox, $copysem);
                $message = __('Copying successful','teachpress');
                get_tp_message($message);
           }
           ?>
     <div id="searchbox" style="float:right; padding-bottom:10px;"> 
         <?php if ($search != "") { ?>
         <a href="admin.php?page=teachpress/teachpress.php" style="font-size:14px; font-weight:bold; text-decoration:none; padding-right:3px;" title="<?php _e('Cancel the search','teachpress'); ?>">X</a>
         <?php } ?>
         <input type="text" name="search" id="pub_search_field" value="<?php echo stripslashes($search); ?>"/></td>
         <input type="submit" name="pub_search_button" id="pub_search_button" value="<?php _e('Search','teachpress'); ?>" class="button-secondary"/>
     </div>
     <div id="filterbox" style="padding-bottom:10px;">    
          <select name="bulk" id="bulk">
               <option>- <?php _e('Bulk actions','teachpress'); ?> -</option>
               <option value="copy"><?php _e('copy','teachpress'); ?></option>
               <option value="delete"><?php _e('Delete','teachpress'); ?></option>
          </select>
          <input type="submit" name="teachpress_submit" id="doaction" value="<?php _e('OK','teachpress'); ?>" class="button-secondary"/>
          <select name="sem" id="sem">
               <option value=""><?php _e('All terms','teachpress'); ?></option>
               <?php    
               $row = get_tp_options('semester');
               foreach ($row as $row) { 
                    $current = ( $row->value == $sem ) ? 'selected="selected"' : '';
                    echo '<option value="' . $row->value . '" ' . $current . '>' . stripslashes($row->value) . '</option>';
               } ?> 
          </select>
         <input type="submit" name="start" value="<?php _e('Show','teachpress'); ?>" id="teachpress_submit" class="button-secondary"/>
      </div>
     <table class="widefat">
        <thead>
        <tr>
            <th class="check-column"><input name="tp_check_all" id="tp_check_all" type="checkbox" value="" onclick="teachpress_checkboxes('checkbox[]','tp_check_all');" /></th>
            <th><?php _e('Name','teachpress'); ?></th>
            <th><?php _e('ID'); ?></th>
            <th><?php _e('Type'); ?></th>
            <th><?php _e('Lecturer','teachpress'); ?></th>
            <th><?php _e('Date','teachpress'); ?></th>
            <th colspan="2" align="center" style="text-align:center;"><?php _e('Places','teachpress'); ?></th>
            <th colspan="2" align="center" style="text-align:center;"><?php _e('Enrollments','teachpress'); ?></th>
            <th><?php _e('Term','teachpress'); ?></th>
            <th><?php _e('Visibility','teachpress'); ?></th>
        </tr>
        </thead>
        <tbody>
     <?php
        $order = 'name, course_id';
        if ($search != '') {
            $order = 'semester DESC, name';	
        }
           
        $row = tp_courses::get_courses( array('search' => $search, 'semester' => $sem, 'order' => 'name, course_id') );
        // is the query is empty
        if ( count($row) === 0 ) { 
            echo '<tr><td colspan="13"><strong>' . __('Sorry, no entries matched your criteria.','teachpress') . '</strong></td></tr>';
        }
        else {
             // used places
             $used_places = tp_courses::get_used_places();
             $static['bulk'] = $bulk;
             $static['sem'] = $sem;
             $static['search'] = $search;
             $z = 0;
             foreach ($row as $row){
                 $date1 = tp_datesplit($row->start);
                 $date2 = tp_datesplit($row->end);
                 $courses[$z]['course_id'] = $row->course_id;
                 $courses[$z]['name'] = stripslashes($row->name);
                 $courses[$z]['type'] = stripslashes($row->type);
                 $courses[$z]['room'] = stripslashes($row->room);
                 $courses[$z]['lecturer'] = stripslashes($row->lecturer);
                 $courses[$z]['date'] = stripslashes($row->date);
                 $courses[$z]['places'] = $row->places;
                 // number of free places
                 if ( array_key_exists($row->course_id, $used_places) ) {
                     $courses[$z]['fplaces'] = $courses[$z]['places'] - $used_places[$row->course_id];
                 }
                 else {
                     $courses[$z]['fplaces'] = $courses[$z]['places'];
                 }
                 $courses[$z]['start'] = '' . $date1[0][0] . '-' . $date1[0][1] . '-' . $date1[0][2] . '';
                 $courses[$z]['end'] = '' . $date2[0][0] . '-' . $date2[0][1] . '-' . $date2[0][2] . '';
                 $courses[$z]['semester'] = stripslashes($row->semester);
                 $courses[$z]['parent'] = $row->parent;
                 $courses[$z]['visible'] = $row->visible;
                 $z++;
             }
             // display courses
             $class_alternate = true;
             for ($i=0; $i<$z; $i++) {
                 
                 // normal table design
                 if ($search == '') {
                     if ($courses[$i]['parent'] == 0) {
                         // alternate table rows
                         $static['tr_class'] = ( $class_alternate === true ) ? ' class="alternate"' : '';
                         $class_alternate = ( $class_alternate === true ) ? false : true;
                         echo get_tp_single_table_row_course ($courses[$i], $checkbox, $static);
                         // Search childs
                         for ($j=0; $j<$z; $j++) {
                             if ($courses[$i]['course_id'] == $courses[$j]['parent']) {
                                 echo get_tp_single_table_row_course ($courses[$j], $checkbox, $static, $courses[$i]['name'],'child');
                             }
                         }
                         // END search childs
                     }	
                 }
                 // table design for searches
                 else {
                     if ($courses[$i]['parent'] != 0) {
                         $parent_name = tp_courses::get_course_data($courses[$i]['parent'], 'name'); 
                     }
                     else {
                         $parent_name = "";
                     }
                     echo get_tp_single_table_row_course ($courses[$i], $checkbox, $static, $parent_name, 'search');
                 }
             }	
        }   
     ?>
     </tbody>
     </table>
     </form>
     </div>
     <?php 
     }
} ?>