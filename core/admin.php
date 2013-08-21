<?php
/****************************************/
/* teachPress Admin Interface functions */
/****************************************/ 

/** 
 * teachPress Admin Page Menu
 * @param int $number_entries       --> Number of all available entries
 * @param int $entries_per_page     --> Number of entries per page
 * @param int $current_page         --> current displayed page
 * @param int $entry_limit          --> SQL entry limit
 * @param string $page_link         --> the name of the page you will insert the menu
 * @param string $link_atrributes   --> the url attributes for get parameters
 * @param string $type              --> top or bottom, default: top
 * @return string
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
            $back_links = '<a href="' . $page_link . 'limit=1&amp;' . $link_attributes . '" title="' . __('first page','teachpress') . '" class="page-numbers">&laquo;</a> <a href="' . $page_link . 'limit=' . ($current_page - 1) . '&amp;' . $link_attributes . '" title="' . __('previous page','teachpress') . '" class="page-numbers">&lsaquo;</a> ';
        }
        else {
            $back_links = '<a class="first-page disabled">&laquo;</a> <a class="prev-page disabled">&lsaquo;</a> ';
        }
        $page_input = ' <input name="limit" type="text" size="2" value="' .  $current_page . '" style="text-align:center;" /> ' . __('of','teachpress') . ' ' . $num_pages . ' ';

        // next page/ last page
        if ( ( $entry_limit + $entries_per_page ) <= ($number_entries)) { 
            $next_links = '<a href="' . $page_link . 'limit=' . ($current_page + 1) . '&amp;' . $link_attributes . '" title="' . __('next page','teachpress') . '" class="page-numbers">&rsaquo;</a> <a href="' . $page_link . 'limit=' . $num_pages . '&amp;' . $link_attributes . '" title="' . __('last page','teachpress') . '" class="page-numbers">&raquo;</a> ';
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
                else {
                    $current = '';
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

/**
 * Returns a form field for the add_publication_page()
 * @param string $name          --> field name
 * @param string $title         --> field title
 * @param string label          --> field label
 * @param string $field_type    --> field type (textarea|input)
 * @param string $pub_type      --> publication type of the current/visible entry
 * @param string $pub_value     --> field value of the current/visible entry
 * @param array $availabe_for   --> array of publication types
 * @param int $tabindex         --> the tab index
 * @param string $style         --> css style attributes
 * @return string
 * @since 4.1.0
 */
function get_tp_admin_form_field ($name, $title, $label, $field_type, $pub_type, $pub_value, $availabe_for, $tabindex, $style = '') {
    $display = ( in_array($pub_type, $availabe_for) ) ? 'style="display:block;"' : 'style="display:none;"';
    if ( $field_type === 'textarea' ) {
        $field = '<textarea name="' . $name . '" id="' . $name . '" wrap="virtual" style="' . $style . '" tabindex="' . $tabindex . '" title="' . $title . '">' . stripslashes($pub_value) . '</textarea>';
    }
    else {
        $field = '<input name="' . $name . '" id="' . $name . '" type="text" title="' . $title . '" style="' . $style . '" value="' . stripslashes($pub_value) . '" tabindex="8" />';
    }
    $a = '<div id="div_' . $name . '" ' . $display . '>
          <p><label for="' . $name . '" title="' . $title . '"><strong>' . $label . '</strong></label></p>
          ' . $field . '</div>';
    return $a;
}

/**
 * Returns a checkbox for admin/settings screens
 * @param string $name
 * @param string $title
 * @param string $value
 * @return string
 * @since 4.2.0
 */
function get_tp_admin_checkbox($name, $title, $value, $disabled = false) {
    $checked = ( $value == '1' ) ? 'checked="checked"' : '';
    $disabled = ( $disabled === true ) ? ' disabled="disabled"' : '';
    return '<input name="' . $name . '" id="' . $name . '" type="checkbox" value="1" ' . $checked . $disabled .'/> <label for="' . $name . '">' . $title . '</label>';
}

/**
 * Gets a box for editing some options (terms|type|studies) for courses
 * @global class $wpdb
 * @global string $teachpress_settings
 * @global string $teachpress_courses
 * @global string $teachpress_stud
 * @param string $title
 * @param string $type
 * @param array $options
 * @since 4.2.0
 */
function get_tp_admin_course_option_box ( $title, $type, $options = array() ) {
    global $wpdb;
    global $teachpress_settings;
    global $teachpress_courses;
    global $teachpress_stud;
    echo '<h4><strong>' . $title . '</strong></h4>';
    echo '<table border="0" cellspacing="0" cellpadding="0" class="widefat">';
    echo '<thead>';
    echo '<tr>';
    echo '<th width="10">&nbsp;</th>';
    echo '<th>' . $options['element_title'] . '</th>';
    echo '<th width="150">' . $options['count_title'] . '</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    
    if ( $type === 'term' ) {
        $sql = "SELECT number, value, setting_id FROM ( SELECT COUNT(v.semester) as number, e.variable AS value,  e.setting_id as setting_id, e.category as category FROM $teachpress_settings e LEFT JOIN $teachpress_courses v ON e.variable = v.semester GROUP BY e.variable ORDER BY number DESC ) AS temp WHERE category = 'semester' ORDER BY setting_id";
    }
    elseif ( $type === 'type' ) {
        $sql = "SELECT number, value, setting_id FROM ( SELECT COUNT(v.type) as number, e.value AS value,  e.setting_id as setting_id, e.category as category FROM $teachpress_settings e LEFT JOIN $teachpress_courses v ON e.value = v.type GROUP BY e.value ORDER BY number DESC ) AS temp WHERE category = 'course_type' ORDER BY value";
    }
    elseif ( $type === 'studies' ) {
        $sql = "SELECT number, value, setting_id FROM ( SELECT COUNT(s.course_of_studies) as number, e.value AS value,  e.setting_id as setting_id, e.category as category FROM $teachpress_settings e LEFT JOIN $teachpress_stud s ON e.value = s.course_of_studies GROUP BY e.value ORDER BY number DESC ) AS temp WHERE category = 'course_of_studies' ORDER BY value";
    }
    else {
        exit('Wrong Type selected');
    }
               
    $row = $wpdb->get_results($sql);
    
    foreach ($row as $row) { 
        echo '<tr>';
        echo '<td><a title="' . $options['delete_title'] . '" href="options-general.php?page=teachpress/settings.php&amp;delete=' . $row->setting_id . '&amp;tab=courses" class="teachpress_delete">X</a></td>';
        echo '<td>' . stripslashes($row->value) . '</td>';
        echo '<td>' . $row->number . '</td>';    
        echo '</tr>';              
    }
    
    echo '<tr>';
    echo '<td></td>';
    echo '<td colspan="2"><input name="newsem" type="text" id="newsem" size="30" value="' . $options['add_title'] . '" onblur="if(this.value==' . "''" .') this.value='. "'" . $options['add_title'] . "'" . ';" onfocus="if(this.value=='. "'" . $options['add_title'] . "'" . ') this.value=' . "''" . ';"/> <input name="addsem" type="submit" class="button-secondary" value="' . __('Create','teachpress') . '"/></td>';
    echo '</tr>'; 
    
    echo '</tbody>';
    echo '</table>';     
}

/**
 * 
 * @since 4.2.0
 */
function tp_add_publication_as_post ($title, $bibtex_key, $date, $post_type = 'post', $tags = '', $category = '') {
    $content = str_replace('[key]', 'key="' . $bibtex_key . '"', get_tp_option('auto_post_template') );
     
    $post_id = wp_insert_post(array(
      'post_title' => $title,
      'post_content' => $content,
      'tags_input' => $tags,
      'post_date' => $date . " 12:00:00",
      'post_date_gmt' => $date . " 12:00:00",
      'post_type' => $post_type,
      'post_status' => 'publish',
      'post_category' => $category,
      ));
    return $post_id;
}

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