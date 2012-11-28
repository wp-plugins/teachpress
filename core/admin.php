<?php
/****************************************/
/* teachPress Admin Interface functions */
/****************************************/ 

/** 
 * teachPress Admin Page Menu
 * @access public
 * @param INT $number_entries    -> Number of all available entries
 * @param INT $entries_per_page  -> Number of entries per page
 * @param INT $current_page      -> current displayed page
 * @param INT $entry_limit       -> SQL entry limit
 * @param STRING $page_link
 * @param STRING $link_atrributes
 * @param STRING $type           -> top or bottom, default: top
 * @return STRING
*/
function tp_admin_page_menu ($number_entries, $entries_per_page, $current_page, $entry_limit, $page_link = '', $link_attributes = '', $type = 'top') {
    // if number of entries > number of entries per page
    if ($number_entries > $entries_per_page) {
        $num_pages = floor (($number_entries / $entries_per_page));
        $mod = $number_entries % $entries_per_page;
        if ($mod != 0) {
            $num_pages = $num_pages + 1;
        }

        // first page / previous page
        if ($entry_limit != 0) {
            $back_links = '<a href="' . $page_link . '&amp;limit=1&amp;' . $link_attributes . '" title="' . __('first page','teachpress') . '" class="page-numbers">&laquo;</a> <a href="' . $page_link . '&amp;limit=' . ($current_page - 1) . '&amp;' . $link_attributes . '" title="' . __('previous page','teachpress') . '" class="page-numbers">&lsaquo;</a> ';
        }
        else {
            $back_links = '<a class="first-page disabled">&laquo;</a> <a class="prev-page disabled">&lsaquo;</a> ';
        }
        $page_input = ' <input name="limit" type="text" size="2" value="' .  $current_page . '" style="text-align:center;" /> ' . __('of','teachpress') . ' ' . $num_pages . ' ';

        // next page/ last page
        if ( ( $entry_limit + $entries_per_page ) <= ($number_entries)) { 
            $next_links = '<a href="' . $page_link . '&amp;limit=' . ($current_page + 1) . '&amp;' . $link_attributes . '" title="' . __('next page','teachpress') . '" class="page-numbers">&rsaquo;</a> <a href="' . $page_link . '&amp;limit=' . $num_pages . '&amp;' . $link_attributes . '" title="' . __('last page','teachpress') . '" class="page-numbers">&raquo;</a> ';
        }
        else {
            $next_links = '<a class="next-page disabled">&rsaquo;</a> <a class="last-page disabled">&raquo;</a> ';
        }

        // for displaying number of entries
        if ($entry_limit + $entries_per_page > $number_entries) {
            $anz2 = $number_entries;
        }
        else {
            $anz2 = $entry_limit + $entries_per_page;
        }

        // return
        if ($type == 'top') {
            return '<div class="tablenav-pages"><span class="displaying-num">' . ($entry_limit + 1) . ' - ' . $anz2 . ' ' . __('of','teachpress') . ' ' . $number_entries . ' ' . __('entries','teachpress') . '</span> ' . $back_links . '' . $page_input . '' . $next_links . '</div>';
        }
        else {
            return '<div class="tablenav"><div class="tablenav-pages"><span class="displaying-num">' . ($entry_limit + 1) . ' - ' . $anz2 . ' ' . __('of','teachpress') . ' ' . $number_entries . ' ' . __('entries','teachpress') . '</span> ' . $back_links . ' ' . $current_page . ' ' . __('of','teachpress') . ' ' . $num_pages . ' ' . $next_links . '</div></div>';
        }	
    }
}	

/** 
 * Get WordPress pages
 * adapted from Flexi Pages Widget Plugin
 * @param STRING $sort_column
 * @param STRING sort_order
 * @param STRING $selected
 * @param STRING $post_type
 * @param INT $parent
 * @param INT $level
*/ 
function get_tp_wp_pages($sort_column = "menu_order", $sort_order = "ASC", $selected = '', $post_type = 'page', $parent = 0, $level = 0 ) {
    global $wpdb;
    if ( $level == 0 ) {
        $pad = isset ($pad) ? $pad : '';
        if ( $selected == '0' ) {
            $current = ' selected="selected"';
        }
        elseif (is_array($selected)) {
            if ( in_array(0, $selected) ) {
                $current = ' selected="selected"';
            }   
        }
        else {
            $current = '';
        }
        echo "\n\t<option value='0'$current>$pad " . __('none','teachpress') . "</option>";
    }
    $items = $wpdb->get_results( "SELECT `ID`, `post_parent`, `post_title` FROM $wpdb->posts WHERE `post_parent` = $parent AND `post_type` = '$post_type' AND `post_status` = 'publish' ORDER BY {$sort_column} {$sort_order}" );
    if ( $items ) {
        foreach ( $items as $item ) {
            $pad = str_repeat( '&nbsp;', $level * 3 );
            if ( $item->ID == $selected  ) {
                $current = ' selected="selected"';
            }
            elseif (is_array($selected)) {
                if ( in_array($item->ID, $selected) ) {
                $current = ' selected="selected"';
                }   
            }
            else {
                $current = '';
            }	
            echo "\n\t<option value='$item->ID'$current>$pad " . get_the_title($item->ID) . "</option>";
            get_tp_wp_pages( $sort_column, $sort_order, $selected, $post_type, $item->ID,  $level +1 );
        }
    } else {
        return false;
    }
}

/** 
 * Get a single table row for show_courses.php
 * @param ARRAY_A $couse      --> course data
 * @param ARRAY $checkbox
 * @param ARRAY_A $static
       $static['bulk']        --> copy or delete
       $static['sem']         --> semester
       $static['search']      --> input from search field
 * @param $parent_course_name --> the name of the parent course
 * @param $type (STRING)      --> parent or child
 * @return STRING
*/ 
function get_tp_single_table_row_course ($course, $checkbox, $static, $parent_course_name = '', $type = 'parent') {
    $check = '';
    $style = '';
    // Check if checkbox must be activated or not
    if ( ( $static['bulk'] == "copy" || $static['bulk'] == "delete") && $checkbox != "" ) {
        for( $k = 0; $k < count( $checkbox ); $k++ ) { 
            if ( $course['course_id'] == $checkbox[$k] ) { $check = 'checked="checked"';} 
        }
    }
    // Change the style for an important information
    if ( $course['places'] > 0 && $course['fplaces'] <= 0 ) {
        $style = ' style="color:#ff6600; font-weight:bold;"'; 
    }
    // Type specifics
    if ( $type == 'parent' || $type == 'search' ) {
        $class = ' class="tp_course_parent"';
    }
    else {
        $class = ' class="tp_course_child"';
    }

    if ( $type == 'child' || $type == 'search' ) {
        if ( $course['name'] != $parent_course_name ) {
            $course['name'] = $parent_course_name . ' ' . $course['name'];
        }
    }
    // complete the row
    $a1 = '<tr>
        <th class="check-column"><input name="checkbox[]" type="checkbox" value="' . $course['course_id'] . '"' . $check . '/></th>
        <td' . $class . '>
                <a href="admin.php?page=teachpress/teachpress.php&amp;course_ID=' . $course['course_id'] . '&amp;sem=' . $static['sem'] . '&amp;search=' . $static['search'] . '&amp;action=show" class="teachpress_link" title="' . __('Click to show','teachpress') . '"><strong>' . $course['name'] . '</strong></a>
                <div class="tp_row_actions">
                        <a href="admin.php?page=teachpress/teachpress.php&amp;course_ID=' . $course['course_id'] . '&amp;sem=' . $static['sem'] . '&amp;search=' . $static['search'] . '&amp;action=show" title="' . __('Show','teachpress') . '">' . __('Show','teachpress') . '</a> | <a href="admin.php?page=teachpress/teachpress.php&amp;course_ID=' . $course['course_id'] . '&amp;sem=' . $static['sem'] . '&amp;search=' . $static['search'] . '&amp;action=edit&amp;ref=overview" title="' . __('Edit','teachpress') . '">' . __('Edit','teachpress') . '</a> | <a href="admin.php?page=teachpress/teachpress.php&amp;sem=' . $static['sem'] . '&amp;search=' . $static['search'] . '&amp;checkbox%5B%5D=' . $course['course_id'] . '&amp;bulk=delete" style="color:red;" title="' . __('Delete','teachpress') . '">' . __('Delete','teachpress') . '</a>
                </div>
        </td>
        <td>' . $course['course_id'] . '</td>
        <td>' . $course['type'] . '</td>
        <td>' . $course['lecturer'] . '</td>
        <td>' . $course['date'] . '</td>
        <td>' . $course['places'] . '</td>
        <td' . $style . '>' . $course['fplaces'] . '</td>';
    if ( $course['start'] != '0000-00-00' && $course['end'] != '0000-00-00' ) {
        $a2 ='<td>' . $course['start'] . '</td>
                <td>' . $course['end'] . '</td>';
    } 
    else {
        $a2 = '<td colspan="2" style="text-align:center;">' . __('none','teachpress') . '</td>';
    }
    $a3 = '<td>' . $course['semester'] . '</td>';
    if ( $course['visible'] == 1 ) {
        $a4 = '<td>' . __('normal','teachpress') . '</td>';
    }
    elseif ( $course['visible'] == 2 ) {
        $a4 = '<td>' . __('extend','teachpress') . '</td>';
    }
    else {
        $a4 = '<td>' . __('invisible','teachpress') . '</td>';
    }
    $a5 = '</tr>';
    // Return
    $return = $a1 . $a2 . $a3 . $a4 . $a5;
    return $return;
}

/***********/
/* Courses */
/***********/

/** 
 * Copy courses
 * @param ARRAY $checkbox - ID of the course you want to copy
 * @param STRING $copysem - semester
*/
function tp_copy_course($checkbox, $copysem) {
     global $wpdb;
     global $teachpress_courses; 
     $counter = 0;
     $counter2 = 0;
     for( $i = 0; $i < count( $checkbox ); $i++ ) {
          $checkbox[$i] = intval($checkbox[$i]);
          $row = get_tp_course($checkbox[$i]);
          foreach ($row as $row) {
               $daten[$counter]['id'] = $row->course_id;
               $daten[$counter]['name'] = $row->name;
               $daten[$counter]['type'] = $row->type;
               $daten[$counter]['room'] = $row->room;
               $daten[$counter]['lecturer'] = $row->lecturer;
               $daten[$counter]['date'] = $row->date;
               $daten[$counter]['places'] = $row->places;
               $daten[$counter]['start'] = $row->start;
               $daten[$counter]['end'] = $row->end;
               $daten[$counter]['semester'] = $row->semester;
               $daten[$counter]['comment'] = $row->comment;
               $daten[$counter]['rel_page'] = $row->rel_page;
               $daten[$counter]['parent'] = $row->parent;
               $daten[$counter]['visible'] = $row->visible;
               $daten[$counter]['waitinglist'] = $row->waitinglist;
               $daten[$counter]['image_url'] = $row->image_url;
               $counter++;
          }
          // copy parents
          if ( $daten[$i]['parent'] == 0) {
               $merke[$counter2] = $daten[$i]['id'];
               $daten[$i]['semester'] = $copysem;
               tp_add_course($daten[$i]);
               $counter2++;
          }
     }	
     // copy childs
     for( $i = 0; $i < $counter ; $i++ ) {
          if ( $daten[$i]['parent'] != 0) {
               // check if where is a parent for the current course
               $test = 0;
               for( $j = 0; $j < $counter2 ; $j++ ) {
                    if ( $daten[$i]['parent'] == $merke[$j]) {
                         $test = $merke[$j];
                    }
               }
               // if is true
               if ($test != 0) {
                    // search the parent
                    for( $k = 0; $k < $counter ; $k++ ) {
                         if ( $daten[$k]['id'] == $test) {
                              $suche = "SELECT `course_id` FROM $teachpress_courses WHERE `name` = '" . $daten[$k]['name'] . "' AND `type` = '" . $daten[$k]['type'] . "' AND `room` = '" . $daten[$k]['room'] . "' AND `lecturer` = '" . $daten[$k]['lecturer'] . "' AND `date` = '" . $daten[$k]['date'] . "' AND `semester` = '$copysem' AND `parent` = 0";
                              $suche = $wpdb->get_var($suche);
                              $daten[$i]['parent'] = $suche;
                              $daten[$i]['semester'] = $copysem;
                              tp_add_course($daten[$i]);					
                         }
                    }
               }
               // if is false: create copy directly
               else {
                    $daten[$i]['semester'] = $copysem;
                    tp_add_course($daten[$i]);
               }
          }
     }
}

?>