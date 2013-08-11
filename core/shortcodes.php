<?php
/**********************************/
/* teachPress Shortcode functions */
/*    (without tp_enrollments)    */
/**********************************/

/** 
 * Shows an overview of courses
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
 * @since 2.0.0
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
          "post_id" => get_the_ID()
    );

    if ( !get_option('permalink_structure') ) {
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
    $rowsem = get_tp_options('semester');
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
 *  id (INT)                --> id of a publication
 *  key (STRING)            --> bibtex key of a publication 
 *  author_name (STRING)    --> last, initials or old, default: simple
 *  author_name (STRING)    --> last, initials or old, default: last
 *  date_format (STRING)    --> the format for date; needed for the types: presentations, online; default: d.m.Y
 * 
 * @param array $atts
 * @return string
 * @since 2.0.0
*/ 
function tp_single_shortcode ($atts) {
    extract(shortcode_atts(array(
       'id' => 0,
       'key' => '',
       'author_name' => 'simple',
       'editor_name' => 'last',
       'date_format' => 'd.m.Y'
    ), $atts));

    $settings = array(
       'author_name' => htmlspecialchars($author_name),
       'editor_name' => htmlspecialchars($editor_name),
       'date_format' => htmlspecialchars($date_format),
       'style' => 'simple', 
    );
    
    if ( $key != '' ) {
        $publication = get_tp_publication_by_key($key, ARRAY_A);
    }
    else {
        $publication = get_tp_publication($id, ARRAY_A);
    }
    
    $author = tp_bibtex::parse_author($publication['author'], $settings['author_name']);

    $asg = '<div class="tp_single_publication"><span class="tp_single_author">' . stripslashes($author) . '</span><span class="tp_single_year"> (' . $publication['year'] . ')</span>: <span class="tp_single_title">' . stripslashes($publication['title']) . '</span>. <span class="tp_single_additional">' . tp_bibtex::single_publication_meta_row($publication, $settings) . '</span></div>';
    return $asg;
}

/**
 * Sort the table lines of a publication table
 * @param array $tparray        --> array of publications
 * @param array $headlines      --> array of headlines
 * @param array $args
 * @return string 
 * @since 4.0.1
 * @version 3
 */
function tp_sort_pub_table($tparray, $headlines, $args) {
    $publications = '';
    $field = $args['headline'] == 2 ? 2 : 0;
    $tpz = $args['number_publications'];
    
    // with headlines
    if ( $args['headline'] == 1 || $args['headline'] == 2 ) {
        for ($i = 0; $i < $tpz; $i++) {
            $key = $tparray[$i][$field];
            $headlines[$key] .= $tparray[$i][1];
        }
        foreach ( $headlines as $key => $value ) {
            if ( $value != '' ) {
                $line_title = $args['headline'] == 1 ? $key : tp_translate_pub_type($key, 'pl');
                $publications .=  '<tr><td' . $args['colspan'] . '><h3 class="tp_h3">' . $line_title . '</h3></td></tr>';
                $publications .=  $value;
            }
        }
    }
    // with healines grouped by year then by type
    else if ($args['headline'] == 3) {
        $yearHeadlines = array();
        for ($i = 0; $i < $tpz; $i++) {
            $keyYear = $tparray[$i][0];
            $keyType = $tparray[$i][2];
            if(!array_key_exists($keyYear, $yearHeadlines)) {
                $yearHeadlines[$keyYear] = array($keyType => '');
            }
            if(!array_key_exists($keyType, $yearHeadlines[$keyYear])) {
                $yearHeadlines[$keyYear][$keyType] = '';
            }
            $yearHeadlines[$keyYear][$keyType] .= $tparray[$i][1];
        }
        
        foreach ( $yearHeadlines as $year => $typeHeadlines ) {
            $publications .=  '<tr><td' . $args['colspan'] . '><h3 class="tp_h3">' . $year . '</h3></td></tr>';
            foreach($typeHeadlines as $type => $value) {
                if ($value != '' ) {
                    $type_title = tp_translate_pub_type($type, 'pl');
                    $publications .=  '<tr><td' . $args['colspan'] . '><h4 class="tp_h3">' . $type_title . '</h4></td></tr>';
                    $publications .=  $value;
                }
            }
        }
    }
    // without headlines
    else {
        for ($i = 0; $i < $tpz; $i++) {
            $publications = $publications . $tparray[$i][1];
        }
    }
  
    return $publications;
}

/**
 * Generate list of publications for [tplist], [tpcloud], [tpsearch]
 * @param array $tparray    --> the array of publications
 * @param array $args       --> an array with all options
 * @return string
 * @since 4.0.0
 * @version 2
 */
function tp_generate_pub_table($tparray, $args ) {
    $headlines = array();
    if ( $args['headline'] == 1 ) {
        foreach( $args['years'] as $row ) {
            $headlines[$row['year']] = '';
        }
        $pubs = tp_sort_pub_table($tparray, $headlines , $args);
    }
    elseif ( $args['headline'] == 2 ) {
        $pub_types = get_tp_publication_used_types( array('user' => $args['user'], 'output_type' => ARRAY_A) );
        foreach( $pub_types as $row ) {
            $headlines[$row['type']] = '';
        }
        $pubs = tp_sort_pub_table($tparray, $headlines, $args);
    }
    else {
        $pubs = tp_sort_pub_table($tparray,'',$args);
    }
    return '<table class="teachpress_publication_list">' . $pubs . '</table>';
}

/** 
 * Publication list with tag cloud
 * 
 * Parameters for the array $atts:
 *   user (INT)             --> the id of on or more users (separated by comma)
 *   tag (INT)              --> NOT IMPLEMENTED
 *   year (INT)             --> NOT IMPLEMENTED
 *   type (STRING)          --> the publication types you want to show (separated by comma)
 *   exclude (INT)          --> one or more IDs of publications you don't want to show (separated by comma)
 *   order (STRING)         --> name, year, bibtex or type, default: date DESC
 *   headline (INT)         --> show headlines with years(1) with publication types(2) or not(0), default: 1
 *   maxsize (INT)          --> maximal font size for the tag cloud, default: 35
 *   minsize (INT)          --> minimal font size for the tag cloud, default: 11
 *   limit (INT)            --> Number of tags, default: 30
 *   image (STRING)         --> none, left, right or bottom, default: none 
 *   image_size (INT)       --> max. Image size, default: 0
 *   anchor (INT)           --> 0 (false) or 1 (true), default: 1
 *   author_name (STRING)   --> simple, last, initials or old, default: last
 *   editor_name (STRING)   --> simple, last, initials or old, default: last
 *   style (STRING)         --> simple or std, default: std
 *   link_style (STRING)    --> inline or images, default: inline
 *   date_format (STRING)   --> the format for date; needed for the types: presentations, online; default: d.m.Y
 * 
 *   WARNING: id has been removed with teachPress 4.0.0, please use user instead!
 * 
 * GET-Parameter: $yr (Year, int), $type (Type, string), $auth (Author, int), $tg (tag id, int)
 * @param array atts
 * @return string
*/
function tp_cloud_shortcode($atts) {
   extract(shortcode_atts(array(
      'user' => '',
      'tag' => '',
      'year' => '',
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
      'link_style' => 'inline',
      'date_format' => 'd.m.Y',
   ), $atts));
   $user = intval($user);
   $sort_type = htmlspecialchars($type);
   $tag = htmlspecialchars($tag);
   $year = htmlspecialchars($year);
   
   $tgid = isset ($_GET['tgid']) ? intval($_GET['tgid']) : '';
   $yr = isset ($_GET['yr']) ? intval($_GET['yr']) : '';
   $type = isset ($_GET['type']) ? htmlspecialchars( $_GET['type'] ) : '';
   $author = isset ($_GET['auth']) ? intval($_GET['auth']) : '';
   
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
       'html_anchor' => $anchor == '1' ? '#tppubs' : '',
       'date_format' => htmlspecialchars($date_format)
   );
   
   // Permalinks
   // Link structure
   if ( get_option('permalink_structure') ) {
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
      $link_url .= "yr=$yr&amp;type=$type&amp;auth=$author" . $settings['html_anchor'];
      
      $asg .= '<span style="font-size:' . $size . 'px;"><a href="' . $link_url . '" title="' . $link_title . '" class="' . $link_class . '">' . stripslashes($tagcloud['name']) . '</a></span> ';
   }

   /**********/ 
   /* Filter */
   /**********/

   // for javascripts
   $str ="'";
   
   // Filter year
   $options = '';
   $row_year = get_tp_publication_years( array( 'user' => $user, 'type' => $sort_type, 'order' => 'DESC', 'output_type' => ARRAY_A ) );
   foreach ($row_year as $row) {
      if ($row['year'] != '0000') {
         $current = $row['year'] == $yr ? 'selected="selected"' : '' ;
         $options = $options . '<option value = "' . $permalink . 'tgid=' . $tgid . '&amp;yr=' . $row['year'] . '&amp;type=' . $type . '&amp;auth=' . $author . $settings['html_anchor'] . '" ' . $current . '>' . $row['year'] . '</option>';
      }
   }
   $filter1 ='<select name="yr" id="yr" onchange="teachpress_jumpMenu(' . $str . 'parent' . $str . ',this,0)">
          <option value="' . $permalink . 'tgid=' . $tgid . '&amp;type=' . $type . '&amp;auth=' . $author . '' . $settings['html_anchor'] . '">' . __('All years','teachpress') . '</option>' . $options . '</select>';
   // END filter year

   // Filter type
   $filter2 = "";
   if ($sort_type == '') {
      $row = get_tp_publication_used_types( array('user' => $user) );
      $current = '';	
      $options = '';
      foreach ($row as $row) {
          $current = ($row->type == $type && $type != '0') ? 'selected="selected"' : '';
          $options = $options . '<option value = "' . $permalink . 'tgid=' . $tgid . '&amp;yr=' . $yr . '&amp;type=' . $row->type . '&amp;auth=' . $author . $settings['html_anchor'] . '" ' . $current . '>' . tp_translate_pub_type($row->type, 'pl') . '</option>';
      }
      $filter2 ='<span style="padding-left:10px; padding-right:10px;"><select name="type" id="type" onchange="teachpress_jumpMenu(' . $str . 'parent' . $str . ',this,0)">
                   <option value="' . $permalink . 'tgid=' . $tgid . '&amp;yr=' . $yr . '&amp;auth=' . $author . $settings['html_anchor'] . '">' . __('All types','teachpress') . '</option>
                         ' . $options . '
                 </select></span>';
   }		   
   // End filter type

   // Filter author
   $current = '';	
   $options = '';  
   $filter3 = '';
   
   if ($user == '') {
      $row = get_tp_publication_user( array('output_type' => ARRAY_A) );	 
      foreach ($row as $row) {
         if ($row['user'] == $author) {
            $current = 'selected="selected"';
         }
         else {
            $current = '';
         }
         $user_info = get_userdata( $row['user'] );
         if ( $user_info != false ) {
               $options = $options . '<option value = "' . $permalink . 'tgid=' . $tgid . '&amp;yr=' . $yr . '&amp;type=' . $type . '&amp;auth=' . $row['user'] . $settings['html_anchor'] . '" ' . $current . '>' . $user_info->display_name . '</option>';
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
   
   if ( $headline === 3  ) {
        $order = "year DESC , type ASC , date DESC";
    }
   
   $row = get_tp_publications( array('tag' => $tgid, 'year' => $yr, 'type' => $type, 'user' => $user, 'order' => $order, 'output_type' => ARRAY_A) );
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
      $tparray[$tpz][1] = tp_bibtex::get_single_publication_html($row, $all_tags, $permalink, $settings, $tpz);
      if ( $headline === 2 || $headline === 3 ) {
          $tparray[$tpz][2] = $row['type'] ;
      }
      $tpz++;
   }
   // Sort the array
   // If there are publications
   if ( $tpz != 0 ) {  
      $asg2 = tp_generate_pub_table($tparray, array('number_publications' => $tpz, 
                                                   'headline' => $headline,
                                                   'years' => $row_year,
                                                   'colspan' => $colspan,
                                                   'user' => $user));  
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
 * 
 * possible values for $atts:
 *   user (INT)             --> 0 for all publications of all users, default: 0
 *   tag (INT)              --> tag-ID, default: 0
 *   type (STRING)          --> publication types (separated by comma)
 *   exclude (STRING)       --> a string with one or more IDs of publication you don't want to display
 *   include (STRING)       --> a string with one or more IDs of publication you want to display
 *   year (INT)             --> default: 0 (=show all years)
 *   order (STRING)         --> name, year, bibtex or type, default: date DESC
 *   headline (INT)         --> show headlines with years(1), with publication types(2), with both(3) or not(0), default: 1
 *   image (STRING)         --> none, left, right or bottom, default: none 
 *   image_size (INT)       --> max. Image size, default: 0
 *   author_name (STRING)   --> last, initials or old, default: last
 *   editor_name (STRING)   --> last, initials or old, default: last
 *   style (STRING)         --> simple, numbered or std, default: std
 *   link_style (STRING)    --> inline or images, default: inline
 *   date_format (STRING)   --> the format for date; needed for the types: presentations, online; default: d.m.Y
 *   pagination (INT)       --> activate pagination (1) or not (0), default: 0
 *   entries_per_page (INT) --> number of publications per page (pagination must be set to 1), default: 30
 * 
 * @param array $atts
 * @return string
*/
function tp_list_shortcode($atts){
    extract(shortcode_atts(array(
       'user' => 0,
       'tag' => 0,
       'type' => '',
       'exclude' => '',
       'include' => '',
       'year' => 0,
       'order' => 'date DESC',
       'headline' => 1,
       'image' => 'none',
       'image_size' => 0,
       'author_name' => 'last',
       'editor_name' => 'last',
       'style' => 'std',
       'link_style' => 'inline',
       'date_format' => 'd.m.Y',
       'pagination' => 0,
       'entries_per_page' => 30,
    ), $atts));

    $tparray = '';
    $tpz = 0;
    $colspan = '';
    $headline = intval($headline);
    $image_size = intval($image_size);
    $pagination = intval($pagination);
    $entries_per_page = intval($entries_per_page);

    $settings = array(
        'author_name' => htmlspecialchars($author_name),
        'editor_name' => htmlspecialchars($editor_name),
        'style' => htmlspecialchars($style),
        'image' => htmlspecialchars($image),
        'with_tags' => 0,
        'link_style' => htmlspecialchars($link_style),
        'date_format' => htmlspecialchars($date_format)
    );
    
    // Handle limits for pagination
    if ( isset( $_GET['limit'] ) ) {
        $current_page = intval( $_GET['limit'] );
        if ( $current_page <= 0 ) {
            $current_page = 1;
        }
        $entry_limit = ( $current_page - 1 ) * $entries_per_page;
    }
    else {
        $entry_limit = 0;
        $current_page = 1;
    }
    $limit = ( $pagination === 1 ) ? $entry_limit . ',' .  $entries_per_page : '';
    $page_link = ( get_option('permalink_structure') ) ? get_permalink() . "?" : get_permalink() . "&amp;";

    // Handle headline/order settings
    if ( $headline === 1 && strpos($order, 'year') === false && strpos($order, 'date') === false ) {
         $order = 'date DESC, ' . $order;
    }
    if ( $headline === 2 ) {
        $order = "type ASC, date DESC"; 
    }
    if ( $headline === 3  ) {
        $order = "year DESC , type ASC , date DESC ";
    }

    // Image settings
    if ($settings['image']== 'left' || $settings['image']== 'right') {
       $settings['pad_size'] = $image_size + 5;
       $colspan = ' colspan="2"';
    }
    
    // get publications
    $args = array('tag' => $tag, 'year' => $year, 'type' => $type, 'user' => $user, 'order' => $order, 'exclude' => $exclude, 'include' => $include, 'output_type' => ARRAY_A, 'limit' => $limit);
    $row = get_tp_publications( $args );
    $number_entries = ( $pagination === 1 ) ? get_tp_publications($args, true) : 0;
    foreach ($row as $row) {
       $tparray[$tpz][0] = $row['year'];
       $tparray[$tpz][1] = tp_bibtex::get_single_publication_html($row,'', '', $settings, $tpz + 1);
       if ( $headline === 2 || $headline === 3 ) {
           $tparray[$tpz][2] = $row['type'];
       }
       $tpz++;			
    }
    
    // menu
    $r = '';
    $menu = ( $pagination === 1 ) ? tp_admin_page_menu($number_entries, $entries_per_page, $current_page, $entry_limit, $page_link, '', 'bottom') : '';
    $r .= $menu;

    $row_year = ( $headline === 1 ) ? get_tp_publication_years( array('output_type' => ARRAY_A, 'order' => 'DESC') ) : '';
    $r .= tp_generate_pub_table($tparray, array('number_publications' => $tpz, 
                                                'headline' => $headline,
                                                'years' => $row_year,
                                                'colspan' => $colspan,
                                                'user' => $user));
    $r .= $menu;
    return $r;
}

/**
 * tpsearch: Frontend search function for publications
 *
 * possible values for $atts:
 *   entries_per_page (INT) --> number of entries per page (default: 20)
 *   image (STRING)         --> none, left, right or bottom, default: none 
 *   image_size (INT)       --> max. Image size, default: 0
 *   author_name (STRING)   --> last, initials or old, default: last
 *   editor_name (STRING)   --> last, initials or old, default: last
 *   style (STRING)         --> simple, numbered or std, default: numbered
 *   link_style (STRING)    --> inline or images, default: inline
 *   as_filter (STRING)     --> set it to "true" if you want to display publications by default
 *   date_format (STRING)   --> the format for date; needed for presentations, default: d.m.Y
 * 
 * @param array $atts
 * @return string
 * @since 4.0.0
 */
function tp_search_shortcode ($atts) {
    extract(shortcode_atts(array(
       'entries_per_page' => 20,
       'image' => 'none',
       'image_size' => 0,
       'author_name' => 'last',
       'editor_name' => 'last',
       'style' => 'numbered',
       'link_style' => 'inline',
       'as_filter' => 'false',
       'date_format' => 'd.m.Y'
    ), $atts)); 
    
    $tparray = '';
    $tpz = 0;
    $colspan = '';
    $image_size = intval($image_size);
    $entries_per_page = intval($entries_per_page);
    $settings = array(
        'author_name' => htmlspecialchars($author_name),
        'editor_name' => htmlspecialchars($editor_name),
        'style' => htmlspecialchars($style),
        'image' => htmlspecialchars($image),
        'with_tags' => 0,
        'link_style' => htmlspecialchars($link_style),
        'date_format' => htmlspecialchars($date_format)
    );
    if ($settings['image']== 'left' || $settings['image']== 'right') {
       $settings['pad_size'] = $image_size + 5;
       $colspan = ' colspan="2"';
    }
    
    $search = isset( $_GET['tps'] ) ? htmlspecialchars( esc_sql( $_GET['tps'] ) ) : "";
    $link_attributes = "tps=$search";
    
    // Handle limits
    if ( isset( $_GET['limit'] ) ) {
        $current_page = intval( $_GET['limit'] );
        if ( $current_page <= 0 ) {
            $current_page = 1;
        }
        $entry_limit = ( $current_page - 1 ) * $entries_per_page;
    }
    else {
        $entry_limit = 0;
        $current_page = 1;
    }
    
    // Define pagelink
    $page_link = ( get_option('permalink_structure') ) ? get_permalink() . "?" : get_permalink() . "&amp;";
    
    $r = '';
    $r .= '<form method="get">';
    if ( !get_option('permalink_structure') ) {
        $r .= '<input type="hidden" name="p" id="page_id" value="' . get_the_ID() . '"/>';
    }
    $r .= '<div class="tp_search_input">';
    $r .= '<input name="tps" id="tp_search" title="" type="text" value="' . $search . '" tabindex="1" size="40"/>';
    $r .= '<input name="tps_button" type="submit" value="' . __('Search', 'teachpress') . '"/>';
    $r .= '</div>';
    if ( $search != "" || $as_filter != 'false' ) {
        // get results
        $tpz = 0;
        $args = array ('search' => $search, 
                       'limit' => $entry_limit . ',' .  $entries_per_page,
                       'output_type' => ARRAY_A);
        $results = get_tp_publications( $args );
        $number_entries = get_tp_publications($args, true);
        
        // menu
        $menu = tp_admin_page_menu($number_entries, $entries_per_page, $current_page, $entry_limit, $page_link, $link_attributes, 'bottom');
        if ( $search != "" ) {
            $r .= '<h3>' . __('Results for','teachpress') . ' "' . $search . '":</h3>';
        }
        $r .= $menu;
        foreach ($results as $row) {
            $count = ( $entry_limit == 0 ) ? ( $tpz + 1 ) : ( $entry_limit + $tpz + 1 );
            $tparray[$tpz][0] = $row['year'];
            $tparray[$tpz][1] = tp_bibtex::get_single_publication_html($row,'', '', $settings, $count);
            $tpz++;
        }
        $r .= tp_generate_pub_table($tparray, array('number_publications' => $tpz, 
                                                    'colspan' => $colspan,
                                                    'headline' => 0,
                                                    'user' => ''));
        $r .= $menu;
    }
    else {
        $r . '<div class="teachpress_message_error">' . __('Sorry, no entries matched your criteria.','teachpress') . '</div>';
    }
    $r .= '</form>';
    return $r;
}

/** 
 * Private Post shortcode
 * @param ARRAY $atts
 *   $atts['id'] INT
 * @param STRING $content
 * @return STRING
 * @since 2.0.0
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