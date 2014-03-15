<?php
/** 
 * teachPress Page Menu
 * @param int $number_entries       --> Number of all available entries
 * @param int $entries_per_page     --> Number of entries per page
 * @param int $current_page         --> current displayed page
 * @param int $entry_limit          --> SQL entry limit
 * @param string $page_link         --> the name of the page you will insert the menu
 * @param string $link_atrributes   --> the url attributes for get parameters
 * @param string $type              --> top or bottom, default: top
 * @return string
*/
function tp_page_menu ($atts) {
    extract(shortcode_atts(array(
       'number_entries' => '',
       'entries_per_page' => 50,
       'current_page' => '',
       'entry_limit' => '',
       'page_link' => '',
       'link_attributes' => '',
       'mode' => 'top',
       'class' => 'tablenav-pages',
    ), $atts));
    
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

        // return
        if ($mode === 'top') {
            return '<div class="' . $class . '"><span class="displaying-num">' . $number_entries . ' ' . __('entries','teachpress') . '</span> ' . $back_links . '' . $page_input . '' . $next_links . '</div>';
        }
        else {
            return '<div class="tablenav"><div class="' . $class . '"><span class="displaying-num">' . $number_entries . ' ' . __('entries','teachpress') . '</span> ' . $back_links . ' ' . $current_page . ' ' . __('of','teachpress') . ' ' . $num_pages . ' ' . $next_links . '</div></div>';
        }	
    }
}	

/** 
 * Print message
 * @param string $message   -> Content
 * @param string $color     -> green (default), orange, red
 * @version 2
 * @since 5.0.0
*/ 
function get_tp_message($message, $color = 'green') {
    $color = htmlspecialchars($color);
    echo '<div class="teachpress_message teachpress_message_' . $color . '">';
    echo '<strong>' . $message . '</strong>';
    echo '</div>';
}

/** 
 * Split a timestamp
 * @param TIMESTAMP $datum
 * @return ARRAY
 *
 * $split[0][0] => Year
 * $split[0][1] => Month 
 * $split[0][2] => Day
 * $split[0][3] => Hour 
 * $split[0][4] => Minute 
 * $split[0][5] => Second
*/ 
function tp_datesplit($datum) {
    $preg = '/[\d]{2,4}/'; 
    $split = array(); 
    preg_match_all($preg, $datum, $split); 
    return $split; 
}

/** 
 * Gives an array with all publication types
 * 
 * Definition of array[] $pub_types:
 *      $pub_types[x][0] ==> BibTeX key
 *      $pub_types[x][1] ==> i18n string (singular)
 *      $pub_types[x][2] ==> i18n string (plural)
 * 
 * @return array
*/ 
function get_tp_publication_types() {
    $pub_types[0] = array (0 => '0', 1 => __('All types','teachpress'), 2 => __('All types','teachpress'));
    $pub_types[1] = array (0 => 'article', 1 => __('Article','teachpress'), 2 => __('Articles','teachpress'));
    $pub_types[2] = array (0 => 'book', 1 => __('Book','teachpress'), 2 => __('Books','teachpress'));
    $pub_types[3] = array (0 => 'booklet', 1 => __('Booklet','teachpress'), 2 => __('Booklets','teachpress'));
    $pub_types[4] = array (0 => 'collection', 1 => __('Collection','teachpress'), 2 => __('Collections','teachpress'));
    $pub_types[5] = array (0 => 'conference', 1 => __('Conference','teachpress'), 2 => __('Conferences','teachpress'));
    $pub_types[6] = array (0 => 'inbook', 1 => __('Inbook','teachpress'), 2 => __('Inbooks','teachpress'));
    $pub_types[7] = array (0 => 'incollection', 1 => __('Incollection','teachpress'), 2 => __('Incollections','teachpress'));
    $pub_types[8] = array (0 => 'inproceedings', 1 => __('Inproceeding','teachpress'), 2 => __('Inproceedings','teachpress'));
    $pub_types[9] = array (0 => 'manual', 1 => __('Manual','teachpress'), 2 => __('Manuals','teachpress'));
    $pub_types[10] = array (0 => 'mastersthesis', 1 => __('Mastersthesis','teachpress'), 2 => __('Masterstheses','teachpress'));
    $pub_types[11] = array (0 => 'misc', 1 => __('Misc','teachpress'), 2 => __('Misc','teachpress'));
    $pub_types[12] = array (0 => 'online', 1 => __('Online','teachpress'), 2 => __('Online','teachpress'));
    $pub_types[13] = array (0 => 'periodical', 1 => __('Periodical','teachpress'), 2 => __('Periodicals','teachpress'));
    $pub_types[14] = array (0 => 'phdthesis', 1 => __('PhD Thesis','teachpress'), 2 => __('PhD Theses','teachpress'));
    $pub_types[15] = array (0 => 'presentation', 1 => __('Presentation','teachpress'), 2 => __('Presentations','teachpress'));
    $pub_types[16] = array (0 => 'proceedings', 1 => __('Proceeding','teachpress'), 2 => __('Proceedings','teachpress'));
    $pub_types[17] = array (0 => 'techreport', 1 => __('Techreport','teachpress'), 2 => __('Techreports','teachpress'));
    $pub_types[18] = array (0 => 'unpublished', 1 => __('Unpublished','teachpress'), 2 => __('Unpublished','teachpress'));
    return $pub_types;
}

/**
 * get the path to a mimetype image
 * @param string $url   --> the URL of a file
 * @return string 
 * @since 3.1.0
 */
function get_tp_mimetype_images($url) {
    $mimetype = substr($url,-4,4);
    $url = plugins_url();
    $mimetypes = array(
        '.pdf' => $url . '/teachpress/images/mimetypes/application-pdf.png',
        '.doc' => $url . '/teachpress/images/mimetypes/application-msword.png',
        'docx' => $url . '/teachpress/images/mimetypes/application-msword.png',
        '.ppt' => $url . '/teachpress/images/mimetypes/application-mspowerpoint.png',
        'pptx' => $url . '/teachpress/images/mimetypes/application-mspowerpoint.png',
        '.xls' => $url . '/teachpress/images/mimetypes/application-msexcel.png',
        'xlsx' => $url . '/teachpress/images/mimetypes/application-msexcel.png',
        '.odt' => $url . '/teachpress/images/mimetypes/application-opendocument.text.png',
        '.ods' => $url . '/teachpress/images/mimetypes/application-opendocument.spreadsheet.png',
        '.odp' => $url . '/teachpress/images/mimetypes/application-opendocument.presentation.png',
        '.odf' => $url . '/teachpress/images/mimetypes/application-opendocument.formula.png',
        '.odg' => $url . '/teachpress/images/mimetypes/application-opendocument.graphics.png',
        '.odc' => $url . '/teachpress/images/mimetypes/application-opendocument.chart.png',
        '.odi' => $url . '/teachpress/images/mimetypes/application-opendocument.image.png',
        '.rtf' => $url . '/teachpress/images/mimetypes/application-rtf.png',
        '.rdf' => $url . '/teachpress/images/mimetypes/text-rdf.png',
        '.txt' => $url . '/teachpress/images/mimetypes/text-plain.png',
        '.tex' => $url . '/teachpress/images/mimetypes/text-x-bibtex.png',
        'html' => $url . '/teachpress/images/mimetypes/text-html.png',
        '.php' => $url . '/teachpress/images/mimetypes/text-html.png',
        '.xml' => $url . '/teachpress/images/mimetypes/text-xml.png',
        '.csv' => $url . '/teachpress/images/mimetypes/text-csv.png',
        '.mp3' => $url . '/teachpress/images/mimetypes/audio-x-generic.png',
        '.wma' => $url . '/teachpress/images/mimetypes/audio-x-generic.png',
        '.wav' => $url . '/teachpress/images/mimetypes/audio-x-generic.png',
        '.gif' => $url . '/teachpress/images/mimetypes/image-x-generic.png',
        '.jpg' => $url . '/teachpress/images/mimetypes/image-x-generic.png',
        '.png' => $url . '/teachpress/images/mimetypes/image-x-generic.png',
        '.svg' => $url . '/teachpress/images/mimetypes/image-x-generic.png',
        '.dvi' => $url . '/teachpress/images/mimetypes/video-x-generic.png',
        '.flv' => $url . '/teachpress/images/mimetypes/video-x-generic.png',
        '.mov' => $url . '/teachpress/images/mimetypes/video-x-generic.png',
        '.mp4' => $url . '/teachpress/images/mimetypes/video-x-generic.png',
        '.wmv' => $url . '/teachpress/images/mimetypes/video-x-generic.png',
        );
    if ( isset ($mimetypes[$mimetype]) ) {
        return $mimetypes[$mimetype];
    }
    else {
        return $mimetypes['html'];
    }
}

/**
 * Translate a publication type
 * @param STRING $string
 * @param STRING $num - sin (singular) or pl (plural)
 * @return STRING
 */
function tp_translate_pub_type($string, $num = 'sin') {
    $t = get_tp_publication_types();
    $tr = '';
    $num = ( $num === 'sin' ) ? 1 : 2;
    for ( $i=1; $i <= count($t) - 1; $i++ ) {
        if ( $string == $t[$i][0] ) {
            $tr = $t[$i][$num];
        }
    }
    return $tr;
}

/** 
 * Get publication types
 * @param string $selected  --> 
 * @param string $mode      --> sng (singular titles) or pl (plural titles)
 * 
 * @version 2
 * @since 4.1.0
 * 
 * @return string
*/
function get_tp_publication_type_options ($selected, $mode = 'sng') {
     $selected = htmlspecialchars($selected);
     $types = '';
     $pub_types = get_tp_publication_types();
     $m = ($mode === 'sng') ? 1 : 2;
     $max = count($pub_types);
     for ($i = 1; $i < $max; $i++) {
         $current = ($pub_types[$i][0] == $selected && $selected != '') ? 'selected="selected"' : '';
         $types = $types . '<option value="' . $pub_types[$i][0] . '" ' . $current . '>' . __('' . $pub_types[$i][$m] . '','teachpress') . '</option>';  
     }
   return $types;
}

/**
 * Get the array structure for a parameter
 * @param string $type  --> values: course_array, publication_array
 * @return array 
 */
function get_tp_var_types($type) {
     if ($type == 'course_array') {
          $ret = array( 'course_id' => '',
                        'name' => '',
                        'type' => '',
                        'room' => '',
                        'lecturer' => '',
                        'date' => '',
                        'places' => '',
                        'start' => '',
                        'end' => '',
                        'semester' => '',
                        'comment' => '',
                        'rel_page' => '',
                        'parent' => '',
                        'visible' => '',
                        'waitinglist' => '',
                        'image_url' => '',
                        'strict_signup' => '',
                        'use_capabilites' => '');
     }
     if ($type == 'publication_array') {
          $ret = array( 'pub_id' => '',
                        'title' => '',
                        'type' => '',
                        'bibtex' => '',
                        'author' => '',
                        'editor' => '',
                        'isbn' => '',
                        'url' => '',
                        'date' => '',
                        'urldate' => '',
                        'booktitle' => '',
                        'issuetitle' => '',
                        'journal' => '',
                        'volume' => '',
                        'number' => '',
                        'pages' => '',
                        'publisher' => '',
                        'address' => '',
                        'edition' => '',
                        'chapter' => '',
                        'institution' => '',
                        'organization' => '',
                        'school' => '',
                        'series' => '',
                        'crossref' => '',
                        'abstract' => '',
                        'howpublished' => '',
                        'key' => '',
                        'techtype' => '',
                        'comment' => '',
                        'note' => '',
                        'image_url' => '',
                        'is_isbn' => '',
                        'rel_page' => '');
     }
     return $ret;
}

/** 
 * Define who can use teachPress
 * @param array $roles
 * @param string $capability
 * @since 1.0
 * @version 2
 */
function tp_update_userrole($roles, $capability) {
    global $wp_roles;

    if ( empty($roles) || ! is_array($roles) ) { 
        $roles = array(); 
    }
    $who_can = $roles;
    $who_cannot = array_diff( array_keys($wp_roles->role_names), $roles);
    foreach ($who_can as $role) {
        $wp_roles->add_cap($role, $capability);
    }
    foreach ($who_cannot as $role) {
        $wp_roles->remove_cap($role, $capability);
    }
}
?>
