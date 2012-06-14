<?php
/**********************************/
/* teachPress Shortcode functions */
/*    (without tp_enrollments)    */
/**********************************/

/** 
 * Show the course overview
 * @param ARRAY $atts
 *    image (STRING) - left, right, bottom or none, default: none
 *    image_size (INT) - default: 0
 *    headline (INT) - 0 for hide headline, 1 for show headline (default:1)
 *    text (STRING) - shows a custom text under the headline
 *    term (STRING) - the term you want to show
 * @param STRING $semester (GET)
 * @return STRING
*/
function tp_courselist_shortcode($atts) {	
   global $wpdb;
   global $teachpress_courses; 
   global $teachpress_settings; 
   // Shortcode options
   extract(shortcode_atts(array(
      'image' => 'none',
      'image_size' => 0,
      'headline' => 1,
      'text' => '',
      'term' => ''
   ), $atts));
   $image = tp_sec_var($image);
   $text = tp_sec_var($text);
   $term = tp_sec_var($term);
   settype($image_size, 'integer');
   settype($headline, 'integer');

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
        $sem = tp_sec_var($_GET['semester']);
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
   $rowsem = "SELECT value FROM " . $teachpress_settings . " WHERE category = 'semester' ORDER BY setting_id DESC";
   $rowsem = $wpdb->get_results($rowsem);
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
   $row = "Select `course_id`, `name`, `comment`, `rel_page`, `image_url`, `visible` FROM " . $teachpress_courses . " WHERE `semester` = '$sem' AND `parent` = '0' AND (`visible` = '1' OR `visible` = '2') ORDER BY `name`";
   $test = $wpdb->query($row);
   if ($test != 0){
      $row = $wpdb->get_results($row);
      foreach($row as $row) {
         $row->name = stripslashes($row->name);
         $row->comment = stripslashes($row->comment);
         $childs = "";
         $div_cl_com = "";
         // handle images	
         $colspan = '';
         $td_left = '';
         $td_right = '';
         if ($image == 'left' || $image == 'right') {
            $settings['pad_size'] = $image_size + 5;
            $rowspan = ' colspan="2"';
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
            $sql = "Select `name`, `comment`, `rel_page`, `image_url` FROM " . $teachpress_courses . " WHERE `semester` = '$sem' AND `parent` = '$row->course_id' AND (`visible` = '1' OR `visible` = '2') ORDER BY `name`";
            $row2 = $wpdb->get_results($sql);
            foreach ($row2 as $row2) {
               $childs = $childs . '<div>
                                      <p><a href="' . get_permalink($row2->rel_page) . '" title="' . $row2->name . '">' . $row2->name . '</a></p>
                                   </div>'; 
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
         $rtn2 = $rtn2 . '<tr>
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
 * Date-Shortcode
 * @param ARRAY 
 *   id (integer)
 * Return STRING
*/
function tp_date_shortcode($attr) {
   $a1 = '<div class="untertitel">' . __('Date(s)','teachpress') . '</div>
            <table class="tpdate">';
   global $wpdb;	
   global $teachpress_courses;
   $id = $attr["id"];
   settype($id, 'integer');
   $row = "SELECT name, type, room, lecturer, date, comment FROM " . $teachpress_courses . " WHERE course_id= ". $id . "";
   $row = $wpdb->get_results($row);
   foreach($row as $row) {
      $v_test = $row->name;
      $a2 = $a2 . ' 
        <tr>
         <td class="tp_date_type"><strong>' . stripslashes($row->type) . '</strong></td>
         <td class="tp_date_info">
            <p>' . stripslashes($row->date) . ' ' . stripslashes($row->room) . '</p>
            <p>' . stripslashes(nl2br($row->comment)) . '</p>
         </td>
         <td clas="tp_date_lecturer">' . stripslashes($row->lecturer) . '</td>
        </tr>';
   } 
   // Search the child courses
   $row = "SELECT name, type, room, lecturer, date, comment FROM " . $teachpress_courses . " WHERE parent= ". $attr["id"] . " AND (`visible` = '1' OR `visible` = '2') ORDER BY name";
   $row = $wpdb->get_results($row);
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
   $asg = '' . $a1 . '' . $a2 . '' . $a3 . '' . $a4 . '';
   return $asg;
}

/** 
 * Shorcode for a single publication
 * @param ARRAY $atts 
 	id (INT)
  	author_name (STRING) => last, initials or old, default: old
 * Return STRING
*/ 
function tp_single_shortcode ($atts) {
   global $teachpress_pub;
   global $wpdb;
   // Shortcode options
   extract(shortcode_atts(array(
      'id' => 0,
      'author_name' => 'simple',
      'editor_name' => 'last'
   ), $atts));
   // secure parameters
   settype($id, 'integer');
   $settings['author_name'] = tp_sec_var($author_name);
   $settings['editor_name'] = tp_sec_var($editor_name);
   // Select from database
   $id = tp_sec_var($id, 'integer');
   $row = "SELECT * FROM " . $teachpress_pub . " WHERE `pub_id` = '$id'";
   $daten = $wpdb->get_row($row, ARRAY_A);
   $author = tp_bibtex::parse_author($daten['author'], $settings['author_name']);
   // Return
   $asg = '<div class="tp_single_publication"><span class="tp_single_author">' . stripslashes($author) . '</span>: "<span class="tp_single_title">' . stripslashes($daten['name']) . '</span>", <span class="tp_single_additional">' . tp_bibtex::single_publication_meta_row($daten, $settings['editor_name']) . '</span></div>';
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
            $pubs = $pubs . tp_sort_pub_table($tparray, $tpz, $headline, $colspan, $row->jahr);
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
 * @param $atts (ARRAY) with: 
 *   user (INT)             => 0 for all publications of all users, default: 0
 *   type (STRING)          => a publication type
 *   exclude (INT)          => one or more IDs of publications (separate by comma) you don't want to show
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
 * $_GET: $yr (Year, INT), $type (Type, STRING), $autor (Author, INT)
 * @return STRING
*/
function tp_cloud_shortcode($atts) {
   global $teachpress_pub;
   global $teachpress_tags;
   global $teachpress_relation;
   global $teachpress_user;
   global $pagenow;
   global $wpdb;
   // Shortcode options
   // Note: "id" is deprecated, please use "user" instead
   extract(shortcode_atts(array(
      'id' => 0,
      'user' => 0,
      'type' => 'all',
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
   $user = $id; // switch to the new parameter
   settype($user, 'integer');
   $sort_type = tp_sec_var($type);
   // tgid - shows the current tag
   if ( isset ($_GET['tgid']) ) {
        $tgid = tp_sec_var( $_GET['tgid'], 'integer' );
   }
   else {
        $tgid = 0;
   }
   // year
   if ( isset ($_GET['yr']) ) {
        $yr = tp_sec_var( $_GET['yr'], 'integer' );
   }
   else {
        $yr = 0;
   }
   // publication type
   if ( isset ($_GET['type']) ) {
        $type = tp_sec_var( $_GET['type'] );
   }
   else {
        $type = 0;
   }
   // author
   if ( isset ($_GET['autor']) ) {
        $author = tp_sec_var( $_GET['autor'], 'integer' );
   }
   else {
        $author = 0;
   }
   // if author is set by shortcode parameter
   if ($user != 0) {
      $author = $user;
   }
   
   // secure parameters
   settype($image_size, 'integer');
   settype($anchor, 'integer');
   settype($headline, 'integer');
   $order_all = tp_sec_var($order);
   $settings = array(
       'author_name' => tp_sec_var($author_name),
       'editor_name' => tp_sec_var($editor_name),
       'style' => tp_sec_var($style),
       'image' => tp_sec_var($image),
       'with_tags' => 1,
       'link_style' => tp_sec_var($link_style),
       'html_anchor' => ''
       );
   // define order_by clause
   $order = '';
   $array = explode(",",$order_all);
   foreach($array as $element) {
      $element = trim($element);
      // rename year to real sql_name
      if ( strpos($element, 'year') !== false ) {
         $element = 'jahr';
      }
      // normal case
      if ( $element != '' && $element != 'jahr' ) {
         $order = $order . 'p.' . $element . ', ';
      }
      // case if headline is off and the user want to order by year
      if ( $element == 'jahr' ) {
         $order = $order . $element . ', ';
      }
   }
   if ( strpos($order, 'jahr') === false && $order != 'p.date DESC, ' ) {
      $order = 'jahr DESC, ' . $order;
   }
   if ( $headline == 2 ) {
      $order = "p.type ASC, p.date DESC  ";
   }
   $order = substr($order, 0, -2);
   // END define order_by clause
   // excludes
   $exclude = tp_sec_var($exclude);
   $ex = '';
   if ( $exclude != '' ) {
        $array = explode(',', $exclude);
        foreach ( $array as $element ) {
            $element = trim ( $element );
            settype($element, 'integer');
            if ( $element != 0 ) {
                $ex = $ex == '' ? "p.pub_id != '$element'" : $ex . " AND p.pub_id != '$element'";
            }
        }
   }
   // if permalinks are off
   if (is_page()) {
      $page = "page_id";
   }
   else {
      $page = "p";
   }
   // With html anchors?
   if ($anchor == '1') {
      $settings['html_anchor'] = '#tppubs';
   }
   $url["permalink"] = get_tp_option('permalink');

   /*************/
   /* Tag cloud */
   /*************/

   // define where clause
   if ( $sort_type == 'all' ) {
      $where = "";
   }
   else {
      if ( $user == 0 ) {
         $where = " WHERE p.type = '" . $sort_type . "' ";
      }
      else {
         $where = "AND p.type = '" . $sort_type . "'";
      }
   }
   // END define where clause

   // List of tags DESC
   if ($user == '0') {
        if ( $sort_type == "all" ) {
            $sql = "SELECT anzahlTags FROM ( 
                        SELECT COUNT(*) AS anzahlTags 
                        FROM " . $teachpress_relation . " 
                        GROUP BY " . $teachpress_relation . ".`tag_id` 
                        ORDER BY anzahlTags DESC ) as temp1 
                    GROUP BY anzahlTags 
                    ORDER BY anzahlTags DESC";
        }
        else {
            $sql = "SELECT anzahlTags FROM ( 
                        SELECT COUNT(*) AS anzahlTags 
                        FROM " . $teachpress_relation . " b  
                        LEFT JOIN " . $teachpress_tags . " t ON b.tag_id = t.tag_id 
                        INNER JOIN " . $teachpress_pub . " p ON p.pub_id = b.pub_id " . $where . " 
                        GROUP BY b.tag_id ORDER BY anzahlTags DESC ) as temp1 
                    GROUP BY anzahlTags 
                    ORDER BY anzahlTags DESC";
        }
   }
   else {
        $sql = "SELECT anzahlTags FROM ( 
                    SELECT COUNT(*) AS anzahlTags FROM " . $teachpress_relation . " b  
                    LEFT JOIN " . $teachpress_tags . " t ON b.tag_id = t.tag_id 
                    INNER JOIN " . $teachpress_pub . " p ON p.pub_id = b.pub_id 
                    INNER JOIN " . $teachpress_user . " u ON u.pub_id = p.pub_id  
                    WHERE u.user = '$user' " . $where . " GROUP BY b.tag_id 
                    ORDER BY anzahlTags DESC ) as temp1 
                GROUP BY anzahlTags 
                ORDER BY anzahlTags DESC";
   }
   // Read Min occurrence and Max occurrence
   $sql = "SELECT MAX(anzahlTags) AS max, min(anzahlTags) AS min FROM (".$sql.") AS temp";
   $tagcloud_temp = $wpdb->get_row($sql, ARRAY_A);
   $max = $tagcloud_temp['max'];
   $min = $tagcloud_temp['min'];

   // Create a list with the tags and their occurcence
   // 0 for all publications
   if ($user == '0') {
      $sql = "SELECT tagPeak, name, tag_id FROM ( 
                SELECT COUNT(b.tag_id) as tagPeak, t.name AS name,  t.tag_id as tag_id 
                FROM " . $teachpress_relation . " b 
                LEFT JOIN " . $teachpress_tags . " t ON b.tag_id = t.tag_id 
                INNER JOIN " . $teachpress_pub . " p ON p.pub_id = b.pub_id " . $where . " 
                GROUP BY b.tag_id 
                ORDER BY tagPeak DESC 
                LIMIT " . $limit . " ) AS temp 
              WHERE tagPeak>=".$min." 
              ORDER BY name";
   }
   else {
      $sql = "SELECT tagPeak, name, tag_id FROM ( 
                SELECT COUNT(b.tag_id) as tagPeak, t.name AS name, t.tag_id as tag_id 
                FROM " . $teachpress_relation . " b 
                LEFT JOIN " . $teachpress_tags . " t ON b.tag_id = t.tag_id 
                INNER JOIN " . $teachpress_pub . " p ON p.pub_id = b.pub_id 
                INNER JOIN " . $teachpress_user . " u ON u.pub_id = p.pub_id  
                WHERE u.user = '$user' " . $where . " 
                GROUP BY b.tag_id ORDER BY tagPeak DESC 
                LIMIT " . $limit . " ) AS temp 
              WHERE tagPeak>=".$min." 
              ORDER BY name";
   }
   $temp = $wpdb->get_results($sql, ARRAY_A);
   $asg = '';
   // Create the cloud
   foreach ($temp as $tagcloud) {
      // calculate the font size
      // level out the min
      if ($min == 1) {
         $min = 0;
      }
      // max. font size * (current occorence - min occurence)/ (max occurence - min occurence)
      $size = floor(($maxsize*($tagcloud['tagPeak']-$min)/($max-$min)));
      // level out the font size
      if ($size < $minsize) {
         $size = $minsize ;
      }
      if ($tagcloud['tagPeak'] == 1) {
         $pub = __('publication', 'teachpress');
      }
      else {
         $pub = __('publications', 'teachpress');
      }
      // if permalinks are on
      if ( $url["permalink"] == 1 ) {
         $url["link"] = $pagenow;
         $url["link"] = str_replace("index.php", "", $url["link"]);
         // define the string
         // selected tag
         if ( $tgid == $tagcloud['tag_id'] ) {
            $asg = $asg . '<span style="font-size:' . $size . 'px;"><a href="' . $url["link"] . '?tgid=0&amp;yr=' . $yr . '&amp;type=' . $type . '&amp;autor=' . $author . $settings['html_anchor'] . '" class = "teachpress_cloud_active" title="' . __('Delete tag as filter','teachpress') . '">' . stripslashes($tagcloud['name']) . ' </a></span> ';
         }
         // normal tag
         else {
            $asg = $asg . '<span style="font-size:' . $size . 'px;"><a href="' . $url["link"] . '?tgid=' . $tagcloud['tag_id'] . '&amp;yr=' . $yr . '&amp;type=' . $type . '&amp;autor=' . $author . $settings['html_anchor'] . '" title="' . $tagcloud['tagPeak'] . ' ' . $pub . '">' . stripslashes($tagcloud['name']) . ' </a></span> ';
         }
      }
      // if permalinks are off
      else {
         $url["post_id"] = get_the_ID();
         $url["link"] = $pagenow;
         $url["link"] = str_replace("index.php", "", $url["link"]);
         // define the string
         // current tag
         if ( $tgid == $tagcloud['tag_id'] ) {
            $asg = $asg . '<span style="font-size:' . $size . 'px;"><a href="' . $url["link"] . '?' . $page . '=' . $url["post_id"] . '&amp;tgid=0&amp;yr=' . $yr . '&amp;type=' . $type . '&amp;autor=' . $author . $settings['html_anchor'] . '" class = "teachpress_cloud_active" title="' . __('Delete tag as filter','teachpress') . '">' . stripslashes($tagcloud['name']) . ' </a></span> ';
         }
         else {
            $asg = $asg . '<span style="font-size:' . $size . 'px;"><a href="' . $url["link"] . '?' . $page . '=' . $url["post_id"] . '&amp;tgid=' . $tagcloud['tag_id'] . '&amp;yr=' . $yr . '&amp;type=' . $type . '&amp;autor=' . $author . $settings['html_anchor'] . '" title="' . $tagcloud['tagPeak'] . ' ' . $pub . '"> ' . stripslashes($tagcloud['name']) . '</a></span> ';
         }
      }
   }

   /**********/ 
   /* Filter */
   /**********/

   // for javascripts
   $str ="'";
   // Link structure
   if ( $url["permalink"] == 1 ) {
      $tpurl = '' . $url["link"] . '?';
   }
   else {
      $tpurl = '' . $url["link"] . '?' . $page . '=' . $url["post_id"] . '&amp;';
   }

   // Filter year
   if ($user == 0) {
      $where = $ex != '' ? "WHERE " . $ex . "" : ''; 
      $row_year = $wpdb->get_results("SELECT DISTINCT DATE_FORMAT(p.date, '%Y') AS jahr FROM " . $teachpress_pub . " p $where ORDER BY jahr DESC");
   }
   else {
      $where = $ex != '' ? "AND " . $ex . "" : '';  
      $row_year = $wpdb->get_results("SELECT DISTINCT DATE_FORMAT(p.date, '%Y') AS jahr FROM " . $teachpress_pub . "  p
                                     INNER JOIN " . $teachpress_user . " u ON u.pub_id=p.pub_id
                                     WHERE u.user = '$user' $where
                                     ORDER BY jahr DESC");
   }
   $options = '';
   foreach ($row_year as $row) {
      if ($row->jahr != '0000') {
         if ($row->jahr == $yr) {
            $current = 'selected="selected"';
         }
         else {
            $current = '';
         }
         $options = $options . '<option value = "' . $tpurl . 'tgid=' . $tgid . '&amp;yr=' . $row->jahr . '&amp;type=' . $type . '&amp;autor=' . $author . $settings['html_anchor'] . '" ' . $current . '>' . $row->jahr . '</option>';
      }
   }
   $filter1 ='<select name="yr" id="yr" onchange="teachpress_jumpMenu(' . $str . 'parent' . $str . ',this,0)">
          <option value="' . $tpurl . 'tgid=' . $tgid . '&amp;yr=0&amp;type=' . $type . '&amp;autor=' . $author . '' . $settings['html_anchor'] . '">' . __('All years','teachpress') . '</option>
                      ' . $options . '
          </select>';
   // END filter year

   // Filter type
   if ($sort_type == 'all') {
      if ($user == 0) {
         $where = $ex != '' ? "WHERE " . $ex . "" : ''; 
         $row = $wpdb->get_results("SELECT DISTINCT p.type FROM " . $teachpress_pub . " p $where ORDER BY p.type ASC");
      }
      else {
         $where = $ex != '' ? "AND " . $ex . "" : ''; 
         $row = $wpdb->get_results("SELECT DISTINCT p.type from " . $teachpress_pub . "  p
                                        INNER JOIN " . $teachpress_user . " u ON u.pub_id=p.pub_id
                                        WHERE u.user = '$user' $where
                                        ORDER BY p.type ASC");
      }
      $current = '';	
      $options = '';
      foreach ($row as $row) {
         if ($row->type == $type && $type != '0') {
                 $current = 'selected="selected"';
         }
         else {
                 $current = '';
         }
         $options = $options . '<option value = "' . $tpurl . 'tgid=' . $tgid . '&amp;yr=' . $yr . '&amp;type=' . $row->type . '&amp;autor=' . $author . $settings['html_anchor'] . '" ' . $current . '>' . tp_translate_pub_type($row->type, 'pl') . '</option>';
      }
      $filter2 ='<span style="padding-left:10px; padding-right:10px;"><select name="type" id="type" onchange="teachpress_jumpMenu(' . $str . 'parent' . $str . ',this,0)">
                   <option value="' . $tpurl . 'tgid=' . $tgid . '&amp;yr=' . $yr . '&amp;type=0&amp;autor=' . $author . '' . $settings['html_anchor'] . '">' . __('All types','teachpress') . '</option>
                         ' . $options . '
                 </select></span>';
   }
   else {
      $filter2 = "";
   }		   
   // End filter type

   // Filter author
   $current = '';	
   $options = '';  
   // for all publications	   
   if ($user == '0') {	
      $row = $wpdb->get_results("SELECT DISTINCT user FROM " . $teachpress_user . "", ARRAY_A);	 
      foreach ($row as $row) {
         if ($row['user'] == $author) {
            $current = 'selected="selected"';
         }
         else {
            $current = '';
         }
         $user_info = get_userdata( $row['user'] );
         if ( $user_info != false ) {
               $options = $options . '<option value = "' . $tpurl . 'tgid=' . $tgid . '&amp;yr=' . $yr . '&amp;type=' . $type . '&amp;autor=' . $row['user'] . $settings['html_anchor'] . '" ' . $current . '>' . $user_info->display_name . '</option>';
         }
      }  
      $filter3 ='<select name="pub-author" id="pub-author" onchange="teachpress_jumpMenu(' . $str . 'parent' . $str . ',this,0)">
                  <option value="' . $tpurl . 'tgid=' . $tgid . '&amp;yr=' . $yr . '&amp;type=' . $type . '&amp;autor=0' . $settings['html_anchor'] . '">' . __('All authors','teachpress') . '</option>
                         ' . $options . '
                </select>';	
   }
   // for publications of one author, where is no third filter	   	
   else {
      $filter3 = "";
   }
   // end filter author

   // Endformat
   if ($yr == '' && $type == '' && ($author == '' || $author == $user ) && $tgid == '') {
      $showall = "";
   }
   else {
      $url["link"] = $pagenow;
      $url["link"] = str_replace("index.php", "", $url["link"]);
      if ($url["permalink"] == 1) {
         $showall ='<a href="' . $url["link"] . '?tgid=0' . $settings['html_anchor'] . '" title="' . __('Show all','teachpress') . '">' . __('Show all','teachpress') . '</a>';
      }
      else {
         $showall ='<a href="' . $url["link"] . '?' . $page . '=' . $url["post_id"] . '&amp;tgid=0' . $settings['html_anchor'] . '" title="' . __('Show all','teachpress') . '">' . __('Show all','teachpress') . '</a>';
      }
   }
   // complete the header (tag cloud + filter)
   $asg1 = '<a name="tppubs" id="tppubs"></a><div class="teachpress_cloud">' . $asg . '</div><div class="teachpress_filter">' . $filter1 . '' .   $filter2 . '' . $filter3 . '</div><p align="center">' . $showall . '</p>';

   /************************/
   /* List of publications */
   /************************/
   
   $select = "SELECT DISTINCT p.pub_id, p.name, p.type, p.bibtex, p.author, p.editor, p.date, DATE_FORMAT(p.date, '%Y') AS jahr, p.isbn , p.url, p.booktitle, p.journal, p.volume, p.number, p.pages, p.publisher, p.address, p.edition, p.chapter, p.institution, p.organization, p.school, p.series, p.crossref, p.abstract, p.howpublished, p.key, p.techtype, p.note, p.is_isbn, p.image_url, p.rel_page 
              FROM " . $teachpress_relation . " b ";
   $join = "INNER JOIN " . $teachpress_tags . " t ON t.tag_id = b.tag_id
            INNER JOIN " . $teachpress_pub . " p ON p.pub_id = b.pub_id ";
   $where = "";
   // define where clause
   if ( $yr != 0 && $yr != '' ) {
       $where = "(p.date BETWEEN '$yr-01-01' AND '$yr-12-31')";
   }
   if ( $type != '0' && $sort_type == 'all' ) {
       $and = $where == '' ? '' : ' AND '; 
       $where = $where . $and . 'p.type = ' . $str . '' . $type . '' . $str . '';
   }
   if ( $sort_type != 'all' ) {
       $and = $where == '' ? '' : ' AND '; 
       $where = $where . $and . "p.type = '$sort_type'";
   }
   if ( $ex != '' ) {
       $where = $where != '' ? $where . ' AND ' . $ex : $ex;
   }
   // END define where clause
   // change the id
   if ($author != 0) {
      $user = $author;
   }
   // If a tag is not selected
   if ($tgid == "" || $tgid == 0) {
      // all publications
      if ($user == 0) {
         $where = $where != '' ? " WHERE " . $where : "";
         $row =  "" . $select . "" . $join . "" . $where . " ORDER BY " . $order . "";
      }
      // publications of one author
      else {
         $where = $where != '' ? " AND " . $where : ""; 
         $row = "" . $select . "" . $join . "
              INNER JOIN " . $teachpress_user . " u ON u.pub_id= b.pub_id
              WHERE u.user = '$user' $where
              ORDER BY " . $order . "";
      }	
   }
   // If a tag is selected
   else {
      if ($user == 0) {
         // all publications
         $where = $where != '' ? " AND " . $where : ""; 
         $row = "" . $select . "" . $join . "
              WHERE t.tag_id = '$tgid' $where
              ORDER BY " . $order . "";
      }
      // publications of one auhtors
      else {
         $where = $where != '' ? " AND " . $where : "";  
         $row = "" . $select . "" . $join . "
              INNER JOIN " . $teachpress_user . " u ON u.pub_id = p.pub_id
              WHERE u.user = '$user' AND t.tag_id = '$tgid' $where
              ORDER BY " . $order . "";
      }	
   }
   $row = $wpdb->get_results($row, ARRAY_A);
   $sql = "SELECT name, tag_id, pub_id FROM (
            SELECT t.name AS name, t.tag_id AS tag_id, b.pub_id AS pub_id 
            FROM " . $teachpress_tags . " t 
            LEFT JOIN " . $teachpress_relation . " b ON t.tag_id = b.tag_id 
           ) as temp";
   $all_tags = $wpdb->get_results($sql, ARRAY_A);
   $tpz = 0;
   $colspan = '';
   if ($settings['image']== 'left' || $settings['image']== 'right') {
      $settings['pad_size'] = $image_size + 5;
      $colspan = ' colspan="2"';
   }
   // Create array of publications
   foreach ($row as $row) {
      $tparray[$tpz][0] = '' . $row['jahr'] . '' ;
      $tparray[$tpz][1] = tp_bibtex::get_single_publication_html($row, $all_tags, $url, $settings);
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
   $asg = $asg1 . $asg2;
   // Return
   return $asg;
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
   global $wpdb;
   global $teachpress_pub;
   global $teachpress_tags;
   global $teachpress_relation;
   global $teachpress_user;
   // extract attributes
   extract(shortcode_atts(array(
      'user' => 0,
      'tag' => 0,
      'type' => 'all',
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
   $userid = $user;
   $tag_id = $tag;
   $yr = $year;

   // Secure parameters
   settype($userid, 'integer');
   settype($tag_id, 'integer');
   settype($yr, 'integer');
   settype($headline, 'integer');
   settype($image_size, 'integer');
   $sort_type = tp_sec_var($type);
   $exclude = tp_sec_var($exclude);
   $order_all = tp_sec_var($order);
   $settings = array(
       'author_name' => tp_sec_var($author_name),
       'editor_name' => tp_sec_var($editor_name),
       'style' => tp_sec_var($style),
       'image' => tp_sec_var($image),
       'with_tags' => 0,
       'link_style' => tp_sec_var($link_style)
   );
   // define order_by clause
   $order = '';
   $array = explode(",",$order_all);
   foreach($array as $element) {
        $element = trim($element);
        // rename year to real sql_name
        if ( strpos($element, 'year') !== false ) {
            $element = 'jahr';
        }
        // normal case
        if ( $element != '' && $element != 'jahr' ) {
            $order = $order . 'p.' . $element . ', ';
        }
        // case if headline is off and the user want to order by year
        if ( $element == 'jahr' && $headline == 0 ) {
            $order = $order . $element . ', ';
        }
   }
   if ( $headline == 1 && strpos($order, 'jahr') === false && $order != 'p.date DESC, ' ) {
        $order = 'jahr DESC, ' . $order;
   }
   if ( $headline == 2 ) {
        $order = "p.type ASC, p.date DESC  ";
   }
   $order = substr($order, 0, -2);
   // END define order_by clause
   
   // define where clause
   $where = "";
   if ( $exclude != '' ) {
        $array = explode(',', $exclude);
        foreach ( $array as $element ) {
            $element = trim ( $element );
            settype($element, 'integer');
            if ( $element != 0 ) {
                $where = $where == '' ? "p.pub_id != '$element'" : $where . " AND p.pub_id != '$element'";
            }
        }
   }
   if ( $sort_type != 'all' ) {
        $tp = "";
        $sort_type = explode(',', $sort_type);
        foreach ( $sort_type as $element ) {
            $element = trim($element);
            $tp = $tp == "" ? "p.type = '" . $element . "'" : $tp . " OR p.type = '" . $element . "'";
        }
        $where = $where == "" ? $tp : $where . " AND ( $tp )";
   }
   if ( $where != "" ) {
        if ( $userid == 0 || $tag_id != 0 ) {
            $where = "WHERE " . $where . "";
        }
        else {
            $where = "AND " . $where. "";
        }
   }
   // END define where clause

   $select = "p.pub_id, p.name, p.type, p.bibtex, p.author, p.editor, p.date, DATE_FORMAT(p.date, '%Y') AS jahr, p.isbn , p.url, p.booktitle, p.journal, p.volume, p.number, p.pages, p.publisher, p.address, p.edition, p.chapter, p.institution, p.organization, p.school, p.series, p.crossref, p.abstract, p.howpublished, p.key, p.techtype, p.note, p.is_isbn, p.image_url, p.rel_page"; 
   // publications of all authors
   if ( $userid == 0 ) {
      // publications of all authors of a specific tags
      if ( $tag_id != 0 ) {
         $row = "SELECT DISTINCT " . $select . " FROM " . $teachpress_relation ." b
                   INNER JOIN " . $teachpress_tags . " t ON t.tag_id = b.tag_id
                   INNER JOIN " . $teachpress_pub . " p ON p.pub_id = b.pub_id
                   WHERE t.tag_id = '$tag_id' " . $where . "
                   ORDER BY " . $order . "";
      }
      // publications of all authors
      else {
         $row = "SELECT " . $select . " FROM " . $teachpress_pub. " p " . $where . " ORDER BY " . $order . "";
      }
   }
   else {
      if ( $tag_id != 0 ) {
         $row = "SELECT DISTINCT " . $select . " FROM " . $teachpress_relation ." b
              INNER JOIN " . $teachpress_tags . " t ON t.tag_id = b.tag_id
              INNER JOIN " . $teachpress_pub . " p ON p.pub_id = b.pub_id
              INNER JOIN " . $teachpress_user . " u ON u.pub_id = p.pub_id
              WHERE u.user = '$id' AND t.tag_id = '$tag_id' " . $where . "
              ORDER BY " . $order . "";
      }
      else {
         $row = "SELECT DISTINCT " . $select . " FROM " . $teachpress_relation ." b 
              INNER JOIN " . $teachpress_tags . " t ON t.tag_id = b.tag_id
              INNER JOIN " . $teachpress_pub . " p ON p.pub_id = b.pub_id
              INNER JOIN " . $teachpress_user . " u ON u.pub_id = b.pub_id
              WHERE u.user = '$userid' " . $where . "
              ORDER BY " . $order . "";
      }	
   }
   
   $tpz = 0;
   $colspan = '';
   if ($settings['image']== 'left' || $settings['image']== 'right') {
      $settings['pad_size'] = $image_size + 5;
      $colspan = ' colspan="2"';
   }
   $row = $wpdb->get_results($row, ARRAY_A);
   foreach ($row as $row) {
      $tparray[$tpz][0] = '' . $row['jahr'] . '' ;
      $tparray[$tpz][1] = tp_bibtex::get_single_publication_html($row,'', '', $settings);
      if ( $headline == 2 ) {
          $tparray[$tpz][2] = '' . $row['type'] . '' ;
      }
      $tpz++;			
   }
   
   if ( $headline == 1 ) {
        $sql = "SELECT DISTINCT DATE_FORMAT(p.date, '%Y') AS jahr FROM " . $teachpress_pub . " p ORDER BY jahr DESC";
        $row_year = $wpdb->get_results($sql);
   }
   else {
        $row_year = '';
   }
   
   return tp_generate_pub_table($tparray, $tpz, $headline, $row_year, $colspan);
}

/** 
 * Private Post shortcode
 * @param ARRAY $atts
 *   $atts['id'] INT
 * @param STRING $content
 * @return STRING
*/
function tp_post_shortcode ($atts, $content) {
   global $wpdb;
   global $teachpress_signup;
   global $user_ID;
   get_currentuserinfo();
   extract(shortcode_atts(array('id' => 0), $atts));
   settype($id, 'integer');
   $sql = "SELECT con_id FROM " . $teachpress_signup . " WHERE course_id = '$id' AND wp_id = '$user_ID'";
   $test = $wpdb->query($sql);
   if ($test == 1) {
     return $content;
   }
}
?>