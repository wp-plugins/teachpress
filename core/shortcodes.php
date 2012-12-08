<?php
/**********************************/
/* teachPress Shortcode functions */
/*    (without tp_enrollments)    */
/**********************************/

/** 
 * Show an overview of courses
 * 
 * possible values for $atts:
 *      image      - left, right, bottom or none, default: none
 *      image_size - default: 0
 *      headline   - 0 for hide headline, 1 for show headline (default:1)
 *      text       - a custom text under the headline
 *      term       - the term/semester you want to show
 * 
 * @param array $atts
 * @param string $semester (GET)
 * @return string
*/
function tp_courselist_shortcode($atts) {	
    extract(shortcode_atts(array(
       'image' => 'none',
       'image_size' => 0,
       'headline' => 1,
       'text' => '',
       'term' => ''
    ), $atts));
    $image = htmlspecialchars($image);
    $text = htmlspecialchars($text);
    $term = htmlspecialchars($term);
    $image_size = intval($image_size);
    $headline = intval($headline);

    $url = array(
         "permalink" => get_tp_option('permalink'),
         "post_id" => get_the_ID()
    );    

    if ( $url["permalink"] == 0 ) {
       if (is_page()) {
          $page = "page_id";
       }
       else {
          $page = "p";
       }
       $page = '<input type="hidden" name="' . $page . '" id="' . $page . '" value="' . $url["post_id"] . '"/>';
    }
    else {
       $page = "";
    }
    // define term
    if ( isset( $_GET['semester'] ) ) {
         $sem = htmlspecialchars($_GET['semester']);
    }
    elseif ( $term != '' ) {
         $sem = $term;
    }
    else {
         $sem = get_tp_option('sem');
    }
   
    $rtn = '<div id="tpcourselist">';
    if ($headline == 1) {
       $rtn = $rtn . '<h2>' . __('Courses for the','teachpress') . ' ' . stripslashes($sem) . '</h2>';
    }
    $rtn = $rtn . '' . $text . '
               <form name="lvs" method="get" action="' . $_SERVER['REQUEST_URI'] . '">
               ' . $page . '		
               <div class="tp_auswahl"><label for="semester">' . __('Select the term','teachpress') . '</label> <select name="semester" id="semester" title="' . __('Select the term','teachpress') . '">';
    $rowsem = get_tp_settings('semester');
    foreach($rowsem as $rowsem) { 
       if ($rowsem->value == $sem) {
          $current = 'selected="selected"' ;
       }
       else {
          $current = '';
       }
       $rtn = $rtn . '<option value="' . $rowsem->value . '" ' . $current . '>' . stripslashes($rowsem->value) . '</option>';
    }
    $rtn = $rtn . '</select>
           <input type="submit" name="start" value="' . __('Show','teachpress') . '" id="teachpress_submit" class="button-secondary"/>
    </div>';
    $rtn2 = '';
    $row = get_tp_courses( array('semester' => $sem, 'parent' => 0, 'visibility' => '1,2') );
    if ( count($row) != 0 ){
        foreach($row as $row) {
            $row->name = stripslashes($row->name);
            $row->comment = stripslashes($row->comment);
            $childs = "";
            $div_cl_com = "";
            // handle images	
            $td_left = '';
            $td_right = '';
            if ($image == 'left' || $image == 'right') {
               $settings['pad_size'] = $image_size + 5;
            }
            $image_marginally = '';
            $image_bottom = '';
            if ($image == 'left' || $image == 'right') {
               if ($row->image_url != '') {
                  $image_marginally = '<img name="' . $row->name . '" src="' . $row->image_url . '" width="' . $image_size .'" alt="' . $row->name . '" />';
               }
            }
            if ($image == 'left') {
               $td_left = '<td width="' . $settings['pad_size'] . '">' . $image_marginally . '</td>';
            }
            if ($image == 'right') {
               $td_right = '<td width="' . $settings['pad_size'] . '">' . $image_marginally . '</td>';
            }
            if ($image == 'bottom') {
               if ($row->image_url != '') {
                  $image_bottom = '<div class="tp_pub_image_bottom"><img name="' . $row->name . '" src="' . $row->image_url . '" style="max-width:' . $image_size .'px;" alt="' . $row->name . '" /></div>';
               }
            }

            // handle childs
            if ($row->visible == 2) {
               $div_cl_com = "_c";
               $row2 = get_tp_courses( array('semester' => $sem, 'parent' => $row->course_id, 'visibility' => '1,2') );
               foreach ($row2 as $row2) {
                  $childs .= '<p><a href="' . get_permalink($row2->rel_page) . '" title="' . $row2->name . '">' . $row2->name . '</a></p>'; 
               }
               if ( $childs != "") {
                  $childs = '<div class="tp_lvs_childs" style="padding-left:10px;">' . $childs . '</div>';
               }
            }

            // handle page link
            if ($row->rel_page == 0) {
               $direct_to = '<strong>' . $row->name . '</strong>';
            }
            else {
               $direct_to = '<a href="' . get_permalink($row->rel_page) . '" title ="' . $row->name . '"><strong>' . $row->name . '</strong></a>';
            }
            $rtn2 .= '<tr>
                       ' . $td_left . '
                       <td class="tp_lvs_container">
                           <div class="tp_lvs_name">' . $direct_to . '</div>
                           <div class="tp_lvs_comments' . $div_cl_com . '">' . nl2br($row->comment) . '</div>
                           ' . $childs . '
                           ' . $image_bottom . '
                       </td>
                       ' . $td_right . '  
                     </tr>';
        } 
    }
    else {
        $rtn2 = '<tr><td class="teachpress_message">' . __('Sorry, no entries matched your criteria.','teachpress') . '</td></tr>';
    }
    $rtn2 = '<table class="teachpress_course_list">' . $rtn2 . '</table>';
    $rtn3 = '</form></div>';
    return $rtn . $rtn2 . $rtn3;
}

/** 
 * Display information about a single course and his childs
 * 
 * possible values of $attr:
 *  id      --> id of the course 
 * 
 * @param array $attr
 * @return string
*/
function tp_date_shortcode($attr) {
    $a1 = '<div class="untertitel">' . __('Date(s)','teachpress') . '</div>
            <table class="tpdate">';
    $id = intval($attr["id"]);
    
    $course = get_tp_course($id);
    $v_test = $course->name;
    $a2 .= '<tr>
                <td class="tp_date_type"><strong>' . stripslashes($course->type) . '</strong></td>
                <td class="tp_date_info">
                <p>' . stripslashes($course->date) . ' ' . stripslashes($course->room) . '</p>
                <p>' . stripslashes(nl2br($course->comment)) . '</p>
                </td>
                <td clas="tp_date_lecturer">' . stripslashes($course->lecturer) . '</td>
            </tr>';
    
    // Search the child courses
    $row = get_tp_courses( array('parent' => $id, 'visible' => '1,2') );
    foreach($row as $row) {
        // if parent name = child name
        if ($v_test == $row->name) {
            $row->name = $row->type;
        }
    $a3 = $a3 . '
        <tr>
            <td class="tp_date_type"><strong>' . stripslashes($row->name) . '</strong></td>
            <td class="tp_date_info">
                    <p>' . stripslashes($row->date) . ' ' . stripslashes($row->room) . '</p>
                    <p>' . stripslashes($row->comment) . '</p>
            </td>
            <td class="tp_date_lecturer">' . stripslashes($row->lecturer) . '</td>
        </tr>';
    } 
    $a4 = '</table>';
    $asg = $a1 . $a2 . $a3 . $a4;
    return $asg;
}

/** 
 * Shorcode for a single publication
 * 
 * possible values of $atts:
 *  id              --> id of the publication
 *  author_name     --> last, initials or old, default: old
 * 
 * @param array $atts
 * @return string
*/ 
function tp_single_shortcode ($atts) {
    extract(shortcode_atts(array(
       'id' => 0,
       'author_name' => 'simple',
       'editor_name' => 'last'
    ), $atts));

    $settings['$author_name'] = htmlspecialchars($author_name);
    $settings['editor_name'] = htmlspecialchars($editor_name);

    $publication = get_tp_publication($id, ARRAY_A);
    $author = tp_bibtex::parse_author($publication['author'], $settings['$author_name']);

    $asg = '<div class="tp_single_publication"><span class="tp_single_author">' . stripslashes($author) . '</span> (<span class="tp_single_year">' . $publication['year'] . '</span>): <span class="tp_single_title">' . stripslashes($publication['title']) . '</span>, <span class="tp_single_additional">' . tp_bibtex::single_publication_meta_row($publication, $settings) . '</span></div>';
    return $asg;
}

/**
 * Sort the table lines of a publication table
 * @param ARRAY $tparray
 * @param INT $tpz
 * @param INT $headline
 * @param STRING $colspan
 * @param STRING $line_title
 * @param STRING $line_name
 * @return STRING 
 */
function tp_sort_pub_table($tparray, $tpz, $headline, $colspan, $line_title, $line_name = '') {
    $save = '';
    $publications = '';
    $field = $headline == 2 ? 2 : 0;
    $line_name = $line_name == '' ? $line_title : $line_name;
    for ($i=0; $i < $tpz; $i++) {
        // without headlines
        if ( $headline == 0 ) {
            $publications = $publications . $tparray[$i][1];
        }
        // with headlines
        if ( $headline == 1 || $headline == 2 ) {
            if ($tparray[$i][$field] == $line_name) {
                $save = $save . $tparray[$i][1];
            }
            if ( ( $tparray[$i][$field] != $line_name || $i == $tpz - 1 ) && $save != '' ) {
                $publications = $publications . '<tr><td' . $colspan . '><h3 class="tp_h3">' . $line_title . '</h3></td></tr>' . $save;
                $save = '';
            }
        }
    }
    return $publications;
}

/**
 * Generate list of publications for [tplist], [tpcloud]
 * @param ARRAY $tparray
 * @param INT $tpz
 * @param INT $headline
 * @param ARRAY $row_year
 * @param STRING $colspan
 * @return STRING
 */
function tp_generate_pub_table($tparray, $tpz, $headline, $row_year, $colspan) {
    $pubs = '';
    if ( $headline == 1 ) {
        foreach($row_year as $row) {
            $pubs = $pubs . tp_sort_pub_table($tparray, $tpz, $headline, $colspan, $row->year);
        }
    }
    if ( $headline == 2 ) {
        $pub_types = get_tp_publication_types();
        for ( $j=1; $j<count($pub_types); $j++ ) {
            $pubs = $pubs . tp_sort_pub_table($tparray, $tpz, $headline, $colspan, $pub_types[$j][2], $pub_types[$j][0]);
        }
    }
    else {
        $pubs = $pubs . tp_sort_pub_table($tparray, $tpz, $headline, $colspan, '', '');
    }
    return '<table class="teachpress_publication_list">' . $pubs . '</table>';
}

/** 
 * Publication list with tag cloud
 * 
 * Parameters for the array $atts:
 *   user (INT)             => the id of on or more users (separated by comma)
 *   type (STRING)          => the publication types you want to show (separated by comma)
 *   exclude (INT)          => one or more IDs of publications you don't want to show (separated by comma)
 *   order (STRING)         => name, year, bibtex or type, default: date DESC
 *   headline (INT)         => show headlines with years(1) with publication types(2) or not(0), default: 1
 *   maxsize (INT)          => maximal font size for the tag cloud, default: 35
 *   minsize (INT)          => minimal font size for the tag cloud, default: 11
 *   limit (INT)            => Number of tags, default: 30
 *   image (STRING)         => none, left, right or bottom, default: none 
 *   image_size (INT)       => max. Image size, default: 0
 *   anchor (INT)           => 0 (false) or 1 (true), default: 1
 *   author_name (STRING)   => simple, last, initials or old, default: last
 *   editor_name (STRING)   => simple, last, initials or old, default: last
 *   style (STRING)         => simple or std, default: std
 *   link_style (STRING)    => inline or images, default: inline
 * 
 * GET-Parameter: $yr (Year, INT), $type (Type, STRING), $author (Author, INT)
 * 
 * @param array atts
 * @return string
*/
function tp_cloud_shortcode($atts) {
   global $teachpress_pub;
   global $teachpress_user;
   global $wpdb;
   // Shortcode options
   // Note: "id" is deprecated, please use "user" instead
   extract(shortcode_atts(array(
      'user' => '',
      'type' => '',
      'exclude' => '', 
      'order' => 'date DESC',
      'headline' => '1', 
      'maxsize' => 35,
      'minsize' => 11,
      'limit' => 30,
      'image' => 'none',
      'image_size' => 0,
      'anchor' => 1,
      'author_name' => 'last',
      'editor_name' => 'last',
      'style' => 'std',
      'link_style' => 'inline'
   ), $atts));
   $user = intval($user);
   $sort_type = htmlspecialchars($type);
   
   $tgid = isset ($_GET['tgid']) ? intval($_GET['tgid']) : '';
   $yr = isset ($_GET['yr']) ? intval($_GET['yr']) : '';
   $type = isset ($_GET['type']) ? htmlspecialchars( $_GET['type'] ) : '';
   $author = isset ($_GET['author']) ? intval($_GET['author']) : '';
   
   // if author is set by shortcode parameter
   if ($user != 0) {
      $author = $user;
   }
   
   // secure parameters
   $exclude = htmlspecialchars($exclude);
   $image_size = intval($image_size);
   $anchor = intval($anchor);
   $headline = intval($headline);
   $order = htmlspecialchars($order);
   $limit = intval($limit);
   $maxsize = intval($maxsize);
   $minsize = intval($minsize);
   $settings = array(
       'author_name' => htmlspecialchars($author_name),
       'editor_name' => htmlspecialchars($editor_name),
       'style' => htmlspecialchars($style),
       'image' => htmlspecialchars($image),
       'with_tags' => 1,
       'link_style' => htmlspecialchars($link_style),
       'html_anchor' => $anchor == '1' ? '#tppubs' : ''
   );
   
   // Permalinks
   // Link structure

   echo get_permalink();
   if ( get_tp_option('permalink') == 1 ) {
      $permalink = get_permalink() . "?";
   }
   else {
      $permalink = get_permalink() . "&amp;";
   }

   /*************/
   /* Tag cloud */
   /*************/
   
   $temp = get_tp_tag_cloud( array('user' => $user, 
                                   'type' => $sort_type, 
                                   'number_tags' => $limit, 
                                   'output_type' => ARRAY_A) );
   $asg = '';
   $min = $temp["info"]->min;
   $max = $temp["info"]->max;
   // level out the min
    if ($min == 1) {
       $min = 0;
    }
   // Create the cloud
   foreach ($temp["tags"] as $tagcloud) {
      $link_url = $permalink;
      $link_title = "";
      $link_class = "";
      $pub = $tagcloud['tagPeak'] == 1 ? __('publication', 'teachpress') : __('publications', 'teachpress');
 
      // calculate the font size
      // max. font size * (current occorence - min occurence) / (max occurence - min occurence)
      $size = floor(( $maxsize *( $tagcloud['tagPeak'] - $min )/( $max - $min ) ));
      // level out the font size
      if ($size < $minsize) {
         $size = $minsize ;
      }
      
      // for current tags
      if ( $tgid == $tagcloud['tag_id'] ) {
          $link_class = "teachpress_cloud_active";
          $link_title = __('Delete tag as filter','teachpress');
      }
      else {
          $link_title = $tagcloud['tagPeak'] . " $pub";
          $link_url .= "tgid=" . $tagcloud['tag_id'] . "&amp;";
      }
      
      // define url
      $link_url .= "yr=$yr&amp;type=$type&amp;author=$author" . $settings['html_anchor'];
      
      $asg .= '<span style="font-size:' . $size . 'px;"><a href="' . $link_url . '" title="' . $link_title . '" class="' . $link_class . '">' . stripslashes($tagcloud['name']) . '</a></span> ';
   }

   /**********/ 
   /* Filter */
   /**********/

   // for javascripts
   $str ="'";
   
   // Filter year
   $options = '';
   $row_year = get_tp_publication_years( array( 'user' => $user, 'type' => $sort_type ) );
   foreach ($row_year as $row) {
      if ($row->year != '0000') {
         $current = $row->year == $yr ? 'selected="selected"' : '' ;
         $options = $options . '<option value = "' . $permalink . 'tgid=' . $tgid . '&amp;yr=' . $row->year . '&amp;type=' . $type . '&amp;author=' . $author . $settings['html_anchor'] . '" ' . $current . '>' . $row->year . '</option>';
      }
   }
   $filter1 ='<select name="yr" id="yr" onchange="teachpress_jumpMenu(' . $str . 'parent' . $str . ',this,0)">
          <option value="' . $permalink . 'tgid=' . $tgid . '&amp;type=' . $type . '&amp;author=' . $author . '' . $settings['html_anchor'] . '">' . __('All years','teachpress') . '</option>' . $options . '</select>';
   // END filter year

   // Filter type
   $filter2 = "";
   if ($sort_type == '') {
      if ($user == "") {
         $row = $wpdb->get_results("SELECT DISTINCT p.type FROM $teachpress_pub p ORDER BY p.type ASC");
      }
      else {
         $row = $wpdb->get_results("SELECT DISTINCT p.type from $teachpress_pub  p
                                        INNER JOIN $teachpress_user u ON u.pub_id=p.pub_id
                                        WHERE u.user = '$user' 
                                        ORDER BY p.type ASC");
      }
      $current = '';	
      $options = '';
      foreach ($row as $row) {
          $current = ($row->type == $type && $type != '0') ? 'selected="selected"' : '';
          $options = $options . '<option value = "' . $permalink . 'tgid=' . $tgid . '&amp;yr=' . $yr . '&amp;type=' . $row->type . '&amp;author=' . $author . $settings['html_anchor'] . '" ' . $current . '>' . tp_translate_pub_type($row->type, 'pl') . '</option>';
      }
      $filter2 ='<span style="padding-left:10px; padding-right:10px;"><select name="type" id="type" onchange="teachpress_jumpMenu(' . $str . 'parent' . $str . ',this,0)">
                   <option value="' . $permalink . 'tgid=' . $tgid . '&amp;yr=' . $yr . '&amp;author=' . $author . $settings['html_anchor'] . '">' . __('All types','teachpress') . '</option>
                         ' . $options . '
                 </select></span>';
   }		   
   // End filter type

   // Filter author
   $current = '';	
   $options = '';  
   $filter3 = '';
   
   if ($user == '') {	
      $row = $wpdb->get_results("SELECT DISTINCT user FROM $teachpress_user", ARRAY_A);	 
      foreach ($row as $row) {
         if ($row['user'] == $author) {
            $current = 'selected="selected"';
         }
         else {
            $current = '';
         }
         $user_info = get_userdata( $row['user'] );
         if ( $user_info != false ) {
               $options = $options . '<option value = "' . $permalink . 'tgid=' . $tgid . '&amp;yr=' . $yr . '&amp;type=' . $type . '&amp;author=' . $row['user'] . $settings['html_anchor'] . '" ' . $current . '>' . $user_info->display_name . '</option>';
         }
      }  
      $filter3 ='<select name="pub-author" id="pub-author" onchange="teachpress_jumpMenu(' . $str . 'parent' . $str . ',this,0)">
                  <option value="' . $permalink . 'tgid=' . $tgid . '&amp;yr=' . $yr . '&amp;type=' . $type . $settings['html_anchor'] . '">' . __('All authors','teachpress') . '</option>
                         ' . $options . '
                </select>';	
   }
   // end filter author

   // Endformat
   if ($yr == '' && $type == '' && ($author == '' || $author == $user ) && $tgid == '') {
    $showall = "";
   }
   else {
    $showall ='<a href="' . $permalink . $settings['html_anchor'] . '" title="' . __('Show all','teachpress') . '">' . __('Show all','teachpress') . '</a>';
   }
   // complete the header (tag cloud + filter)
   $asg1 = '<a name="tppubs" id="tppubs"></a><div class="teachpress_cloud">' . $asg . '</div><div class="teachpress_filter">' . $filter1 . '' .   $filter2 . '' . $filter3 . '</div><p align="center">' . $showall . '</p>';

   /************************/
   /* List of publications */
   /************************/
   
   // change the id
   if ($author != 0) {
      $user = $author;
   }
   
   $row = get_tp_publications( array('tag' => $tgid, 'year' => $yr, 'type' => $type, 'user' => $user, 'output_type' => ARRAY_A) );
   $all_tags = get_tp_tags( array('output_type' => ARRAY_A) );
   $tpz = 0;
   $colspan = '';
   $tparray = '';
   if ($settings['image']== 'left' || $settings['image']== 'right') {
      $settings['pad_size'] = $image_size + 5;
      $colspan = ' colspan="2"';
   }
   // Create array of publications
   foreach ($row as $row) {
      $tparray[$tpz][0] = $row['year'] ;
      $tparray[$tpz][1] = tp_bibtex::get_single_publication_html($row, $all_tags, $permalink, $settings);
      if ( $headline == 2 ) {
          $tparray[$tpz][2] = '' . $row['type'] . '' ;
      }
      $tpz++;
   }
   // Sort the array
   // If there are publications
   if ( $tpz != 0 ) {  
      $asg2 = tp_generate_pub_table($tparray, $tpz, $headline, $row_year, $colspan);  
   }
   // If there are no publications founded
   else {
      $asg2 = '<div class="teachpress_list"><p class="teachpress_mistake">' . __('Sorry, no publications matched your criteria.','teachpress') . '</p></div>';
   }
   // Return
   return $asg1 . $asg2;
}

/** 
 * Publication list without tag cloud
 * @param ARRAY $atts
 *   user (INT)             => 0 for all publications of all users, default: 0
 *   tag (INT)              => tag-ID, default: 0
 *   type (STRING)          => publication types (separated by comma)
 *   exclude (STRING)       => a string with one or more IDs of publication you don't want to display
 *   year (INT)             => default: 0 (=show all years)
 *   order (STRING)         => name, year, bibtex or type, default: date DESC
 *   headline (INT)         => show headlines with years(1) with publication types(2) or not(0), default: 1
 *   image (STRING)         => none, left, right or bottom, default: none 
 *   image_size (INT)       => max. Image size, default: 0
 *   author_name (STRING)   => last, initials or old, default: last
 *   editor_name (STRING)   => last, initials or old, default: last
 *   style (STRING)         => simple or std, default: std
 *   link_style (STRING)    => inline or images, default: inline
 * @return STRING
*/
function tp_list_shortcode($atts){
   extract(shortcode_atts(array(
      'user' => 0,
      'tag' => 0,
      'type' => '',
      'exclude' => '', 
      'year' => 0,
      'order' => 'date DESC',
      'headline' => 1,
      'image' => 'none',
      'image_size' => 0,
      'author_name' => 'last',
      'editor_name' => 'last',
      'style' => 'std',
      'link_style' => 'inline'
   ), $atts));

   $tparray = '';
   $tpz = 0;
   $colspan = '';
   $headline = intval($headline);
   $image_size = intval($image_size);
 
   $settings = array(
       'author_name' => htmlspecialchars($author_name),
       'editor_name' => htmlspecialchars($editor_name),
       'style' => htmlspecialchars($style),
       'image' => htmlspecialchars($image),
       'with_tags' => 0,
       'link_style' => htmlspecialchars($link_style)
   );
   
   if ( $headline == 1 && strpos($order, 'year') === false && strpos($order, 'date') === false ) {
        $order = 'date DESC, ' . $order;
   }
   if ( $headline == 2 ) {
        $order = "type ASC, date DESC";
   }
   
   if ($settings['image']== 'left' || $settings['image']== 'right') {
      $settings['pad_size'] = $image_size + 5;
      $colspan = ' colspan="2"';
   }
   
   $row = get_tp_publications( array('tag' => $tag, 'year' => $year, 'type' => $type, 'user' => $user, 'order' => $order, 'exclude' => $exclude, 'output_type' => ARRAY_A) );
   foreach ($row as $row) {
      $tparray[$tpz][0] = '' . $row['year'] . '' ;
      $tparray[$tpz][1] = tp_bibtex::get_single_publication_html($row,'', '', $settings);
      if ( $headline == 2 ) {
          $tparray[$tpz][2] = '' . $row['type'] . '' ;
      }
      $tpz++;			
   }
   
   $row_year = $headline == 1 ? get_tp_publication_years() : '';
   $result = tp_generate_pub_table($tparray, $tpz, $headline, $row_year, $colspan);
   return $result;
}

function tp_search_shortcode () {
    
}

/** 
 * Private Post shortcode
 * @param ARRAY $atts
 *   $atts['id'] INT
 * @param STRING $content
 * @return STRING
*/
function tp_post_shortcode ($atts, $content) {
    extract(shortcode_atts(array('id' => 0), $atts));
    $id = intval($id);
    $test = tp_is_user_subscribed($id, true);
    if ($test == true) {
        return $content;
    }
}
?>