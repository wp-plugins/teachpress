<?php

/**
 * teachPress BibTeX | HTML class
 *
 * @since 3.0.0
 */
class tp_bibtex {
     
    var $row;
    var $settings;
    var $all_tags;
    var $url;
    var $input;

    /**
     * Get a single publication in bibtex format
     * @param array $row
     * @param array $all_tags (optional)
     * @return string
     * @since 3.0.0 
    */
    public static function get_single_publication_bibtex ($row, $all_tags = '') {
        $string = '';
        $pub_fields = array('type','bibtex','title','author','editor','url','isbn','date','urldate','booktitle','issuetitle','journal','volume','number','pages','publisher','address','edition','chapter','institution','organization','school','series','crossref','abstract','howpublished','key','techtype','note');
        // initial string
        if ( $row['type'] == 'presentation' ) {
            $string = '@misc{' . stripslashes($row['bibtex']) . ',' . chr(13) . chr(10);
        }
        else {
            $string = '@' . stripslashes($row['type']) . '{' . stripslashes($row['bibtex']) . ',' . chr(13) . chr(10);
        }
        // loop for all BibTeX fields
        for ( $i = 2; $i < count($pub_fields); $i++ ) {
            // replace html chars
            if ( $pub_fields[$i] == 'author' || $pub_fields[$i] == 'title' ) {
                $row[$pub_fields[$i]] = tp_bibtex::replace_html_chars($row[$pub_fields[$i]]);
            }
            // prepare single lines
            if ( isset( $row[$pub_fields[$i]] ) ) {  
                // ISBN | ISSN
                if ( $pub_fields[$i] == 'isbn' ) {
                    if ( $row['is_isbn'] == 1 && $row[$pub_fields[$i]] != '' ) {
                        $string = $string . 'isbn = {' . $row[$pub_fields[$i]] . '},' . chr(13) . chr(10);
                    }
                    if ( $row['is_isbn'] == 0 && $row[$pub_fields[$i]] != '' ) {
                        $string = $string . 'issn = {' . $row[$pub_fields[$i]] . '},' . chr(13) . chr(10);
                    }
                }
                // year
                elseif ( $pub_fields[$i] == 'date' ) {
                    if ( $row[$pub_fields[$i]] != '0000-00-00' ) {
                        $string = $string . 'year  = {' . $row['year'] . '},' . chr(13) . chr(10);
                        $string = $string . tp_bibtex::prepare_bibtex_line($row[$pub_fields[$i]],$pub_fields[$i]);
                    }
                }
                // urldate
                elseif ( $pub_fields[$i] == 'urldate' ) {
                    if ( $row[$pub_fields[$i]] != '0000-00-00' ) {
                        $string = $string . tp_bibtex::prepare_bibtex_line($row[$pub_fields[$i]],$pub_fields[$i]);
                    }
                }
                // techtype
                elseif ( $pub_fields[$i] == 'techtype' ) {
                    if ( $row[$pub_fields[$i]] != '' ) {
                        $string = $string . 'type = {' . $row[$pub_fields[$i]] . '},' . chr(13) . chr(10);
                    }
                }
                // abstract
                elseif ( $pub_fields[$i] == 'abstract' ) {
                    $string = $string . tp_bibtex::prepare_text($row[$pub_fields[$i]], $pub_fields[$i]);
                }
                // normal case
                else {
                    $string = $string . tp_bibtex::prepare_bibtex_line($row[$pub_fields[$i]],$pub_fields[$i]);
                }
            }
        }
        // Add month
        if ( $row['type'] == 'booklet' ) {
            $date = tp_datesplit( $row['date'] );
            $string = $string . 'month = {' . $date[0][1] . '},' . chr(13) . chr(10);
        }
        // Add keywords
        if ( $all_tags != '' ) {
            $keywords = '';
            foreach ( $all_tags as $all_tags ) {
                $keywords = $keywords . $all_tags['name'] . ', ';
            }
            $string = $string . 'keywords = {' . substr($keywords, 0, -2) . '}';
        }
        else {
            $string = $string . 'keywords = {}';
        }
        // Add teachPress export data
        if ( $row['type'] == 'presentation' ) {
            $string = $string . ",". chr(13) . chr(10) . 'tppubtype = {' . $row['type'] . '}' . chr(13) . chr(10);
        }
        else {
            $string = $string . chr(13) . chr(10);
        }
        $string = $string . '}' . chr(13) . chr(10);
        return $string;
    }

    /**
     * Get a single publication in html format
     * @param array $row        --> the publication array
     * @param array $all_tags   --> array of tags
     * @param array $url        --> the URL of the page on which the publication will be displayed
     * @param array $settings   --> array of settings
     * @param int $tpz          --> the counter for numbered publications (default: 0)
     * @return string
     * @since 3.0.0
    */
    public static function get_single_publication_html ($row, $all_tags, $permalink, $settings, $tpz = 0) {
        $tag_string = '';
        $str = "'";
        $keywords = '';
        // show tags
        if ( $settings['with_tags'] == 1 ) {
            foreach ($all_tags as $tag) {
                if ($tag["pub_id"] == $row['pub_id']) {
                    $keywords[] = array('name' => stripslashes($tag["name"]));
                    $tag_string = $tag_string . '<a href="' . $permalink . 'tgid=' . $tag["tag_id"] . $settings['html_anchor'] . '" title="' . __('Show all publications which have a relationship to this tag','teachpress') . '">' . stripslashes($tag["name"]) . '</a>, ';
                }
            }
            $tag_string = substr($tag_string, 0, -2);
        }
        // handle images
        $image_marginally = '';
        $image_bottom = '';
        $td_left = '';
        $td_right = '';
        if ( $settings['image'] === 'left' || $settings['image'] === 'right' ) {
            if ( $row['image_url'] != '' ) {
                $image_marginally = '<img name="' . $row['title'] . '" src="' . $row['image_url'] . '" width="' . ($settings['pad_size'] - 5) .'" alt="' . $row['title'] . '" />';
            }
        }
        if ( $settings['image'] === 'left' ) {
            $td_left = '<td class="tp_pub_image_left" width="' . $settings['pad_size'] . '">' . $image_marginally . '</td>';
        }
        if ( $settings['image'] === 'right' ) {
            $td_right = '<td class="tp_pub_image_right" width="' . $settings['pad_size']  . '">' . $image_marginally . '</td>';
        }
        if ( $settings['image'] === 'bottom' ) {
            if ( $row['image_url'] != '' ) {
                $image_bottom = '<div class="tp_pub_image_bottom"><img name="' . stripslashes($row['title']) . '" src="' . $row['image_url'] . '" style="max-width:' . ($settings['pad_size']  - 5) .'px;" alt="' . stripslashes($row['title']) . '" /></div>';
            }
        }
        // transform URL into full HTML link
        if ( $row['rel_page'] != 0 ) {
            $name = '<a href="' . get_permalink($row['rel_page']) . '">' . stripslashes($row['title']) . '</a>';
        }
        // for inline style
        elseif ( $row['url'] != '' && $settings['link_style'] === 'inline' ) {
            $name = '<a class="tp_title_link" onclick="teachpress_pub_showhide(' . $str . $row['pub_id'] . $str . ',' . $str . 'tp_links' . $str . ')" style="cursor:pointer;">' . $row['title'] . '</a>';
        }
        else {
            $name = $row['title'];
        }

        // parse author names 
        if ( $row['type'] === 'collection' || $row['type'] === 'periodical' || ( $row['author'] === '' && $row['editor'] !== '' ) ) {
            $all_authors = tp_bibtex::parse_author($row['editor'], $settings['author_name'] ) . ' (' . __('Ed.','teachpress') . ')';
        }
        else {
            $all_authors = tp_bibtex::parse_author($row['author'], $settings['author_name'] );
        }

        // language sensitive publication type
        $type = tp_translate_pub_type($row['type']);

        $a2 = '';
        $a3 = '';
        $abstract = '';
        $url = '';

        // if is an abstract
        if ( $row['abstract'] != '' ) {
            $abstract = '<a id="tp_abstract_sh_' . $row['pub_id'] . '" class="tp_show" onclick="teachpress_pub_showhide(' . $str . $row['pub_id'] . $str . ',' . $str . 'tp_abstract' . $str . ')" title="' . __('Show abstract','teachpress') . '" style="cursor:pointer;">' . __('Abstract','teachpress') . '</a> | ';
        }
        // if are links
        if ( $row['url'] != '' ) {
            if ( $settings['link_style'] == 'inline' ) {
                $url = '<a id="tp_links_sh_' . $row['pub_id'] . '" class="tp_show" onclick="teachpress_pub_showhide(' . $str . $row['pub_id'] . $str . ',' . $str . 'tp_links' . $str . ')" title="' . __('Show links and resources','teachpress') . '" style="cursor:pointer;">' . __('Links','teachpress') . '</a> | ';
            }
            else {
                $url = ' | ' . __('Links','teachpress') . ': ' . tp_bibtex::prepare_url($row['url'], 'enumeration') . '';
            }
        }
        // if with tags
        if ($settings['with_tags'] == '1') {
            $tag_string = ' | ' . __('Tags') . ': ' . $tag_string;
        }
        // link style
        if ( $settings['link_style'] == 'inline' ) {
            $a2 = $abstract . $url . '<a id="tp_bibtex_sh_' . $row['pub_id'] . '" class="tp_show" onclick="teachpress_pub_showhide(' . $str . $row['pub_id'] . $str . ',' . $str . 'tp_bibtex' . $str . ')" style="cursor:pointer;" title="' . __('Show BibTeX entry','teachpress') . '">' . __('BibTeX','teachpress') . '</a>' . $tag_string;
        }
        else {
            $a2 = $abstract . '<a onclick="teachpress_pub_showhide(' . $str . $row['pub_id'] . $str . ',' . $str . 'tp_bibtex' . $str . ')" style="cursor:pointer;" title="' . __('Show BibTeX entry','teachpress') . '">' . __('BibTeX','teachpress') . '</a>' . $tag_string . $url;
        }
        // different styles: simple and normal
        if ($settings['style'] === 'simple' || $settings['style'] === 'numbered' || $settings['style'] === 'numbered_desc' ) {
            $a1 = '<tr class="tp_publication_simple">';
            if ( $settings['style'] === 'numbered' || $settings['style'] === 'numbered_desc' ) {
                $a1 = $a1 . '<td class="tp_pub_number">' . $tpz . '.</td>';
            }
            $a1 = $a1 . $td_left;
            $a1 = $a1 . '<td class="tp_pub_info_simple">';
            $a1 = $a1 . '<span class="tp_pub_author_simple">' . stripslashes($all_authors) . '</span>';
            $a1 = $a1 . '<span class="tp_pub_year_simple"> (' . $row['year'] . ')</span>: ';
            $a1 = $a1 . '<span class="tp_pub_title_simple">' . stripslashes($name) . '</span>. ';
            $a1 = $a1 . '<span class="tp_pub_additional_simple">' . tp_bibtex::single_publication_meta_row($row, $settings) . '</span>';
            $a2 = ' <span class="tp_pub_tags_simple">(' . __('Type') . ': <span class="tp_pub_typ_simple">' . stripslashes($type) . '</span> | ' . $a2 . ')</span>';
        }
        else {
            $a1 = '<tr class="tp_publication">';
            $a1 = $a1 . $td_left;
            $a1 = $a1 . '<td class="tp_pub_info">';
            $a1 = $a1 . '<p class="tp_pub_author">' . stripslashes($all_authors) . '</p>';
            $a1 = $a1 . '<p class="tp_pub_title">' . stripslashes($name) . ' <span class="tp_pub_typ">(' . stripslashes($type) . ')</span></p>';
            $meta_row = tp_bibtex::single_publication_meta_row($row, $settings);
            if ($meta_row != '.') {
                $a1 = $a1 . '<p class="tp_pub_additional">' . $meta_row . '</p>';
            }
            $a2 = '<p class="tp_pub_tags">(' . $a2 . ')</p>';
        }
        // end styles

        // div bibtex
        $a3 = '<div class="tp_bibtex" id="tp_bibtex_' . $row['pub_id'] . '" style="display:none;">';
        $a3 = $a3 . '<div class="tp_bibtex_entry">' . nl2br(tp_bibtex::get_single_publication_bibtex($row, $keywords)) . '</div>';
        $a3 = $a3 . '<p class="tp_close_menu"><a class="tp_close" onclick="teachpress_pub_showhide(' . $str . $row['pub_id'] . $str . ',' . $str . 'tp_bibtex' . $str . ')">' . __('Close','teachpress') . '</a></p>';
        $a3 = $a3 . '</div>';
        // div abstract
        if ( $row['abstract'] != '' ) {
            $a3 = $a3 . '<div class="tp_abstract" id="tp_abstract_' . $row['pub_id'] . '" style="display:none;">';
            $a3 = $a3 . '<div class="tp_abstract_entry">' . tp_bibtex::prepare_text_for_html($row['abstract']) . '</div>';
            $a3 = $a3 . '<p class="tp_close_menu"><a class="tp_close" onclick="teachpress_pub_showhide(' . $str . $row['pub_id'] . $str . ',' . $str . 'tp_abstract' . $str . ')">' . __('Close','teachpress') . '</a></p>';
            $a3 = $a3 . '</div>';
        }
        // div links
        if ( $row['url'] != '' && $settings['link_style'] == 'inline' ) {
            $a3 = $a3 . '<div class="tp_links" id="tp_links_' . $row['pub_id'] . '" style="display:none;">';
            $a3 = $a3 . '<div class="tp_links_entry">' . tp_bibtex::prepare_url($row['url'], 'list') . '</div>';
            $a3 = $a3 . '<p class="tp_close_menu"><a class="tp_close" onclick="teachpress_pub_showhide(' . $str . $row['pub_id'] . $str . ',' . $str . 'tp_links' . $str . ')">' . __('Close','teachpress') . '</a></p>';
            $a3 = $a3 . '</div>';
        }
        $a4 = $image_bottom . '
                </td>
                ' . $td_right . '
                </tr>';			
        $a = $a1 . $a2 . $a3 . $a4;			
        return $a;
    }

    /**
     * Get the second line of the publications with editor, year, volume, address, edition, etc.
     * @param array $row
     * @param array $settings
     * @return string
     * @since 3.0.0
    */
    public static function single_publication_meta_row($row, $settings) {
        // For ISBN or ISSN number
        if ( $row['isbn'] != '' ) {
            // test if ISBN or ISSN
            if ($row['is_isbn'] == '0') { 
                $isbn = ', ISSN: ' . $row['isbn'] . '';
            }
            else {
                $isbn = ', ISBN: ' . $row['isbn'] . '';
            }
        }
        else {
            $isbn = '';
        }
        
        // for urldate
        if ( isset( $row['urldate'] ) && $row['urldate'] !== '0000-00-00'  ) {
             $row['urldate'] = ( array_key_exists('date_format', $settings) === true ) ? date( $settings['date_format'], strtotime($row['urldate']) ) : $row['urldate'];
            $urldate = tp_bibtex::prepare_html_line($row['urldate'],', ' . __('visited','teachpress') . ': ','');
        }
        else {
            $urldate = '';
        }
        
        // isset() doesn't work for $editor
        $editor = $row['editor'] != '' ? tp_bibtex::parse_author($row['editor'], $settings['editor_name']) . ' (' . __('Ed.','teachpress') . '): ' : '';
        $pages = isset( $row['pages'] ) ? tp_bibtex::prepare_html_line( tp_bibtex::prepare_page_number($row['pages']) , __('pp.','teachpress') . ' ',', ') : '';
        $year = isset( $row['year'] ) ? tp_bibtex::prepare_html_line($row['year']) : '';
        $booktitle = isset( $row['booktitle'] ) ? tp_bibtex::prepare_html_line($row['booktitle'],'',', ') : '';
        $issuetitle = isset( $row['issuetitle'] ) ? tp_bibtex::prepare_html_line($row['issuetitle'],'',', ') : '';
        $journal = isset( $row['journal'] ) ? tp_bibtex::prepare_html_line($row['journal'],'',', ') : '';
        $volume = isset( $row['volume'] ) ? tp_bibtex::prepare_html_line($row['volume'],'',' ') : '';
        $number = isset( $row['number'] ) ? tp_bibtex::prepare_html_line($row['number'],'(','), ') : '';
        $publisher = isset( $row['publisher'] ) ? tp_bibtex::prepare_html_line($row['publisher'],'',', ') : '';
        $address = isset( $row['address'] ) ? tp_bibtex::prepare_html_line($row['address'],'',', ') : '';
        $edition = isset( $row['edition'] ) ? tp_bibtex::prepare_html_line($row['edition'],'',', ') : '';
        $chapter = isset( $row['chapter'] ) ? tp_bibtex::prepare_html_line($row['chapter'],' ' . __('Chapter','teachpress') . ' ',', ') : '';
        $institution = isset( $row['institution'] ) ? tp_bibtex::prepare_html_line($row['institution'],'',' ') : '';
        $organization = isset( $row['organization'] ) ? tp_bibtex::prepare_html_line($row['organization'],'',' ') : '';
        $school = isset( $row['school'] ) ? tp_bibtex::prepare_html_line($row['school'],'',', ') : '';
        $series = isset( $row['series'] ) ? tp_bibtex::prepare_html_line($row['series'],'',' ') : '';
        $howpublished = isset( $row['howpublished'] ) ? tp_bibtex::prepare_html_line($row['howpublished'],'',', ') : '';
        $techtype = isset( $row['techtype'] ) ? tp_bibtex::prepare_html_line($row['techtype'],'',', ') : '';
        $note = isset( $row['techtype'] ) ? tp_bibtex::prepare_html_line($row['note'],', (',')') : '';
        
        // special cases for volume/number
        if ( $number == '' && $volume != '' ) {
            $volume = substr($volume, 0, -1);
            $number = ', ';
        }
        
        // special cases for article/incollection/inbook/inproceedings
        $in = '';
        if ($settings['style'] === 'simple' || $settings['style'] === 'numbered' ) {
            if ( $row['type'] === 'article' || $row['type'] === 'inbook' || $row['type'] === 'incollection' || $row['type'] === 'inproceedings') {
                $in = __('In','teachpress') . ': ';
            }
        }

        // end format after type
        if ($row['type'] === 'article') {
            $end = $in . $journal . $volume . $number . $pages . $year . $isbn . $note . '.';
        }
        elseif ($row['type'] === 'book') {
            $end = $edition . $publisher . $address . $year . $isbn . $note .'.';
        }
        elseif ($row['type'] === 'booklet') {
            $end = $howpublished . $address . $edition . $year . $isbn . $note . '.';
        }
        elseif ($row['type'] === 'collection') {
            $end = $edition . $publisher . $address . $year . $isbn . $note . '.';
        }
        elseif ($row['type'] === 'conference') {
            $end = $booktitle . $volume . $number . $series . $organization . $publisher . $address . $year . $isbn . $note . '.';
        }
        elseif ($row['type'] === 'inbook') {
            $end = $in . $editor . $booktitle . $volume . $number . $chapter . $pages . $publisher . $address . $edition. $year . $isbn . $note . '.';
        }
        elseif ($row['type'] === 'incollection') {
            $end = $in . $editor . $booktitle . $volume . $number . $pages . $publisher . $address . $year . $isbn . $note . '.';
        }
        elseif ($row['type'] === 'inproceedings') {
            $end = $in . $editor . $booktitle . $pages . $organization . $publisher . $address. $year . $isbn . $note . '.';
        }
        elseif ($row['type'] === 'manual') {
            $end = $editor . $organization . $address. $edition . $year . $isbn . $note . '.';
        }
        elseif ($row['type'] == 'mastersthesis') {
            $end = $school . $year . $isbn . $note . '.';
        }
        elseif ($row['type'] === 'misc') {
            $end = $howpublished . $year . $isbn . $note . '.';
        }
        elseif ($row['type'] === 'online') {
            $end = $editor . $organization . $year . $urldate . $note . '.';
        }
        elseif ($row['type'] === 'periodical') {
            $end = $issuetitle . $series . $volume . $number . $year . $urldate . $isbn . $note . '.';
        }
        elseif ($row['type'] === 'phdthesis') {
            $end = $school . $year . $isbn . $note . '.';
        }
        elseif ($row['type'] === 'presentation') {
            $date = ( array_key_exists('date_format', $settings) === true ) ? ', ' . date( $settings['date_format'], strtotime($row['date']) ) . '' : '';
            $end = ( $howpublished === '' && $row['address'] === '' ) ? substr($date,2) . $note . '.' : $howpublished . $row['address'] . $date . $note . '.';
        }
        elseif ($row['type'] === 'proceedings') {
            $end = $howpublished . $organization. $publisher. $address . $edition . $year . $isbn . $note . '.';
        }
        elseif ($row['type'] === 'techreport') {
            $end = $institution . $address . $techtype . $number. $year . $isbn . $note . '.';
        }
        elseif ($row['type'] === 'unpublished') {
            $end = $year . $isbn . $note . '.';
        }
        else {
            $end = $year . $note . '.';
        }
        $end = stripslashes($end);
        return $end;
    }
    
    /**
    * Import a BibTeX String
    * @global class $PARSEENTRIES
    * @param string $input      --> the input string with bibtex entries
    * @param array $settings    --> with index names: keyword_separator, author_format
    * @param string $test       --> set it to true for test mode ( = disable the inserting of publications into database )
    */
    static function import_bibtex ($input, $settings, $test = false) {
        global $PARSEENTRIES;
        $input = tp_bibtex::replace_bibtex_chars($input);
        $parse = NEW PARSEENTRIES();
        $parse->expandMacro = TRUE;
        $array = array('RMP' => 'Rev., Mod. Phys.');
        $parse->loadStringMacro($array);
        $parse->loadBibtexString($input);
        $parse->extractEntries();
        
        list($preamble, $strings, $entries, $undefinedStrings) = $parse->returnArrays();
        $max = count( $entries );
        for ( $i = 0; $i < $max; $i++ ) {
            $entries[$i]['name'] = array_key_exists('name', $entries[$i]) === true ? $entries[$i]['name'] : '';
            $entries[$i]['date'] = array_key_exists('date', $entries[$i]) === true ? $entries[$i]['date'] : '';
            $entries[$i]['location'] = array_key_exists('location', $entries[$i]) === true ? $entries[$i]['location'] : '';
            $entries[$i]['keywords'] = array_key_exists('keywords', $entries[$i]) === true ? $entries[$i]['keywords'] : '';
            $entries[$i]['tags'] = array_key_exists('tags', $entries[$i]) === true ? $entries[$i]['tags'] : '';
            $entries[$i]['isbn'] = array_key_exists('isbn', $entries[$i]) === true ? $entries[$i]['isbn'] : '';
            $entries[$i]['issn'] = array_key_exists('issn', $entries[$i]) === true ? $entries[$i]['issn'] : '';
            $entries[$i]['tppubtype'] = array_key_exists('tppubtype', $entries[$i]) === true ? $entries[$i]['tppubtype'] : '';
            // for the date of publishing
            if ( $entries[$i]['date'] != '' ) {
                $entries[$i]['date'] = $entries[$i]['date'];
            }
            elseif ($entries[$i]['month'] != '' && $entries[$i]['day'] != '' && $entries[$i]['year'] != '') {
                $entries[$i]['date'] = $entries[$i]['year'] . '-' . $entries[$i]['month'] . '-' . $entries[$i]['day'];
            }
            else {
                $entries[$i]['date'] = $entries[$i]['year'] . '-01-01';
            }
            // for tags
            if ($entries[$i]['keywords'] != '') {
                $tags = str_replace($settings['keyword_separator'],",",$entries[$i]['keywords']);
            }
            elseif ($entries[$i]['tags'] != '') {
                $tags = str_replace($settings['keyword_separator'],",",$entries[$i]['tags']);
            }
            else {
                $tags = '';
            }
            // correct name | title bug of old teachPress versions
            if ($entries[$i]['name'] != '') {
                $entries[$i]['title'] = $entries[$i]['name'];
            }
            
            // consider old location fields
            if ( $entries[$i]['location'] != '' ) {
                $entries[$i]['address'] = $entries[$i]['location'];
            }
            // for author / editor
            // for format lastname1, firstname1 and lastname2, firstname2
            if ($settings['author_format'] == 2) {
                $end = '';
                $new = explode(' and ', $entries[$i]['author'] );
                foreach ( $new as $new ) {
                    $parts = explode(',', $new); 
                    $num = count($parts); 
                    $one = ''; 
                    for ($j = 1; $j < $num; $j++) {
                        $parts[$j] = trim($parts[$j]);
                        $one = $one . ' '. $parts[$j];
                    }
                    $one = $one . ' ' . trim($parts[0]);
                    $end = $end != '' ? $end . ' and ' . $one : $one;
                }
                $entries[$i]['author'] = $end;
            }
            // for isbn/issn detection
            if ( $entries[$i]['issn'] != '' ) {
                $entries[$i]['is_isbn'] = 0;
                $entries[$i]['isbn'] = $entries[$i]['issn'];
            }
            else {
                $entries[$i]['is_isbn'] = 1;
            }
            // rename to teachPress keys
            $entries[$i]['type'] = $entries[$i]['bibtexEntryType'];
            $entries[$i]['bibtex'] = $entries[$i]['bibtexCitation'];
            // handle export data from teachPress
            if ( $entries[$i]['tppubtype'] != '' ) {
                $entries[$i]['type'] = $entries[$i]['tppubtype'];
            }
            // replace bibtex chars
            foreach ($entries[$i] as $key => $value) {
                $entries[$i][$key] = str_replace(array('{','}'), array('',''), $value);
            }
            if ( $test === false ) {
                // Try to fix problems with line breaks
                if ( $tags != '' ) {
                    $tags = str_replace (array("\r\n", "\n", "\r"), ' ', $tags);
                }
                // update/overwrite existing publication
                $check = true;
                if ( $settings['overwrite'] === true ) {
                    $entries[$i]['entry_id'] = tp_change_publication_by_key($entries[$i]['bibtex'], $entries[$i], $tags);
                    $check = ( $entries[$i]['entry_id'] === false ) ? false : true;
                }
                if ( $settings['overwrite'] === false || $check === false ) {
                    $entries[$i]['entry_id'] = tp_add_publication($entries[$i], $tags, '');
                }    
            }
        }
        return $entries;

    }

    /**
     * Replace some HTML special chars with the UTF-8 versions
     * @param string $input
     * @return string
     * @since 3.0.0
    */
    public static function replace_html_chars ($input) {
        $array_1 = array('&Uuml;','&uuml;',
                         '&Ouml;','&ouml;','&ograve;','&oacute;','&Ograve;','&Oacute;',
                         '&Auml;','&auml;','&aacute;','&agrave;','&Agrave;','&Aacute;',
                         '&eacute;','&egrave;','&Egrave;','&Eacute;',
                         '&sect;','&copy;','&reg;','&pound;','&yen;',
                         '&szlig;','&micro;','&amp;',
                         '&nbsp;','&ndash;','&rdquo;','&ldquo;','&raquo;','&laquo;','&shy;');
        $array_2 = array('Ü','ü',
                         'Ö','ö','ò','ó','Ò','Ó',
                         'Ä','ä','á','à','À','Á',
                         'é','è','È','É',
                         '§','©','®','£','¥',
                         'ß','µ','&',
                         ' ','-','”','“','»','«','­');
        $input = str_replace($array_1, $array_2, $input);
        return $input;
    }

    /**
     * Replace some BibTeX special chars with the UTF-8 versions and secure the parameter
     * @param string $input
     * @return string
     * @since 3.0.0
     * @access public
     */
    public static function replace_bibtex_chars ($input) {
        // return the input if there are no bibtex chars
        if ( strpos( $input,'\\' ) === false && strpos($input,'{') === false ) { return $input; }
        // Step 1: Chars which based on a combination of two chars, delete escapes
        $array_a = array('\ss','\O','\o','\AE','\ae','\OE','\oe','\textendash','\textemdash',chr(92));
        $array_b = array('ß','Ø','ø','Æ','æ','Œ','œ','–','—','');
        $input = str_replace( $array_a , $array_b ,$input);
        // Step 2: All over special chars 
        $array_1 = array('"{a}','"{A}','`{a}','`{A}',"'{a}","'{A}",'~{a}','~{A}','={a}','={A}','^{a}','^{A}','.{a}','.{A}','u{a}','u{A}','k{a}','k{A}','r{a}','r{A}',
                         '.{b}','.{B}',
                         "'{c}","'{C}",'v{c}','v{C}','c{c}','c{C}','.{c}','.{C}','^{c}','^{C}',
                         'v{d}','v{D}','.{d}','.{D}','d{d}','d{D}','{d}','{D}',
                         '"{e}','"{E}',"'{e}","'{E}",'`{e}','`{E}','^{e}','^{E}','u{e}','u{E}','v{e}','v{E}','={e}','={E}','k{e}','k{E}','.{e}','.{E}',
                         '.{f}','.{F}',
                         'u{g}','u{G}','c{g}','c{G}','.{g}','.{G}','^{g}','^{G}',
                         '.{h}','.{H}','d{h}','d{H}','^{h}','^{H}','{h}','{H}',
                         '"{i}','"{I}','~{i}','~{I}','`{i}','`{I}',"'{i}","'{I}",'^{i}','^{I}','u{i}','u{I}','={i}','={I}','k{i}','k{I}','.{i}','.{I}',
                         '^{j}','^{J}',
                         'c{k}','c{K}','d{k}','d{K}',
                         "'{l}","'{L}",'v{l}','v{L}','c{l}','c{L}','d{l}','d{L}',
                         '.{m}','.{M}','d{m}','d{M}',
                         "'{n}","'{N}",'~{n}','~{N}','v{n}','v{N}','c{n}','c{N}','.{n}','.{N}',
                         '"{o}','"{O}','`{o}','`{O}',"'{o}","'{O}",'~{o}','~{O}','^{o}','^{O}','u{o}','u{O}','.{o}','.{O}','={o}','={O}','H{o}','H{O}',
                         '.{p}','.{P}',
                         "'{r}","'{R}",'v{r}','v{R}','c{r}','c{R}','.{r}','.{R}','d{r}','d{R}',
                         "'{s}","'{S}",'v{s}','v{S}','c{s}','c{S}','.{s}','.{S}','d{s}','d{S}','^{s}','^{S}',
                         'v{t}','v{T}','c{t}','c{T}','.{t}','.{T}','d{t}','d{T}','{t}','{T}',
                         '"{u}','"{U}','`{u}','`{U}',"'{u}","'{U}",'^{u}','^{U}','d{u}','d{U}','~{u}','~{U}','u{u}','u{U}','={u}','={U}','k{u}','k{U}','r{u}','r{U}','H{u}','H{U}',
                         'd{v}','d{V}',
                         '^{w}','^{W}',
                         '"{y}','"{Y}',"'{y}","'{Y}",'^{y}','^{Y}',
                         "'{z}","'{Z}",'v{z}','v{Z}','.{z}','.{Z}');
        $array_2 = array('ä','Ä','à','À','á','Á','ã','Ã','ā','Ā','â','Â','å','Å','ă','Ă','ą','Ą','å','Å',
                         'ḃ','Ḃ',
                         'ć','Ć','č','Č','ç','Ç','ċ','Ċ','ĉ','Ĉ',
                         'ď','Ď','ḋ','Ḋ','ḍ','Ḍ','đ','Đ',
                         'ë','Ë','é','É','è','È','ê','Ê','ĕ','Ĕ','ě','Ě','ē','Ē','ę','Ę','ė','Ė',
                         'ḟ','Ḟ',
                         'ğ','Ğ','ģ','Ģ','ġ','Ġ','ĝ','Ĝ',
                         'ḣ','Ḣ','ḥ','Ḥ','ĥ','Ĥ','ħ','Ħ',
                         'ï','Ï','ĩ','Ĩ','ì','Ì','í','Í','î','Î','ĭ','Ĭ','ī','Ī','į','Į','i','İ',
                         'ĵ','Ĵ',
                         'ķ','Ķ','ḳ','Ḳ',
                         'ĺ','Ĺ','ľ','Ľ','ļ','Ļ','ḷ','Ḷ',
                         'ṁ','Ṁ','ṃ','Ṃ',
                         'ń','Ń','ñ','Ñ','ň','Ň','ņ','Ņ','ṅ','Ṅ',
                         'ö','Ö','ò','Ò','ó','Ó','õ','Õ','ô','Ô','ŏ','Ŏ','ȯ','Ȯ','ō','Ō','ő','Ő',
                         'ṗ','Ṗ',
                         'ŕ','Ŕ','ř','Ř','ŗ','Ŗ','ṙ','Ṙ','ṛ','Ṛ',
                         'ś','Ś','š','Š','ş','Ş','ṡ','Ṡ','ṣ','Ṣ','ŝ','Ŝ',
                         'ť','Ť','ţ','Ţ','ṫ','Ṫ','ṭ','Ṭ','ŧ','Ŧ',
                         'ü','Ü','ù','Ù','ú','Ú','û','Û','ụ','Ụ','ũ','Ũ','ŭ','Ŭ','ū','Ū','ų','Ų','ů','Ů','ű','Ű',
                         'ṿ','Ṿ',
                         'ŵ','Ŵ',
                         'ÿ','Ÿ','ý','Ý','ŷ','Ŷ',
                         'ź','Ź','ž','Ž','ż','Ż');
        $return = str_replace($array_1, $array_2, $input);
        return htmlspecialchars($return);
    }
    
    /**
     * Prepare an (html) input for bibtex and replace expressions for bold, italic, lists, etc. with their latex equivalents
     * @param string $text      The (html) input
     * @param string $fieldname The bibtex field name
     * @return string
     * @since 4.2.0
     */
    public static function prepare_text($text, $fieldname = 'abstract') {
        if ($text != '') {
	    $text = htmlspecialchars_decode($text);
            // Replace expressions
            $search = array ('/<sub>/i', '/<sup>/i',
                             '/<i>/i', '/<b>/i', '/<em>/i', 
                             '/<\/(sub|sup|i|b|em)>/i',
                             '/<ul>/i', '/<\/ul>/i',
                             '/<ol>/i', '/<\/ol>/i',
                             '/<li>/i', '/<\/li>/i');
            $replace = array ('_{', '^{',
                              '\textit{', '\textbf{', '\emph{',
                              '}',
                              '\begin{itemize}', '\end{itemize}' . "\n",
                              '\begin{enumerate}', '\end{enumerate} . "\n"',
                              '\item ', '');
	    $text = preg_replace($search, $replace, $text);
            // Add wordwrap if necessary
            if (strpos($text, "\n") === false ) {
                $text = wordwrap($text, 80, "\r\n");
            }
            return tp_bibtex::prepare_bibtex_line($text, $fieldname, false);
        }
        else {
            return '';
        }
    }
    
    /**
     * Prepare a text for normal html output. Works like htmlspecialchars_decode, but with a white list
     * @param string $input
     * @return string
     * @since 4.2.0
     * @access public
     */
    public static function prepare_text_for_html($input) {
        $search = array('&lt;sub&gt;', '&lt;/sub&gt;',
                        '&lt;sup&gt;', '&lt;/sup&gt;',
                        '&lt;i&gt;', '&lt;/i&gt;',
                        '&lt;b&gt;', '&lt;/b&gt;',
                        '&lt;em&gt;', '&lt;/em&gt;',
                        '&lt;u&gt;', '&lt;/u&gt;',
                        '&lt;ul&gt;', '&lt;/ul&gt;', 
                        '&lt;li&gt;', '&lt;/li&gt;', 
                        '&lt;ol&gt;', '&lt;/ol&gt;' );
        $replace = array('<sub>', '</sub>', 
                         '<sup>', '</sup>',
                         '<i>', '</i>',
                         '<b>', '</b>', 
                         '<em>', '</em>', 
                         '<u>', '</u>', 
                         '<ul>', '</ul>', 
                         '<li>', '</li>', 
                         '<ol>', '</ol>' );
        $input = str_replace($search, $replace, $input);
        return nl2br(stripslashes($input));
    }

        /**
     * Prepare a page number
     * @access public
     * @param string $input
     * @return string
     * @since 4.0.0
     */
    public static function prepare_page_number ($input) {
        if ( isset($input) ) {
            return str_replace("--", "–", $input);
        }
        else {
            return '';
        }
    }

    /**
     * Prepare a single BibTeX line with the input from onde publication field
     * @param string    $input          The value of the publication field
     * @param string    $fieldname      The name of the publication field
     * @param boolean   $stripslashes   Strip slashes (true) or not (false); default is true; since 4.2.0
     * @return string
     * @since 3.0.0
     */
    public static function prepare_bibtex_line($input, $fieldname, $stripslashes = true) {
        if ($input != '') {
            $input = ( $stripslashes === true ) ? stripslashes($input) : $input;
            return '' . $fieldname . ' = {' . $input . '},' . chr(13) . chr(10);
        }
        else {
            return '';
        }
    }

    /**
     * Prepare a single HTML line with the input from one publication field
     * @param string $input
     * @param string $before
     * @param string $after
     * @return string
     * @since 3.0.0 
     */
    public static function prepare_html_line($input, $before = '', $after = '') {
        if ($input != '') {
            return $before . $input . $after;
        }
        else {
            return '';
        }
    }

    /**
     * Prepare a url link for publication resources 
     * @param string $url
     * @param string $mode     -> list or enumeration
     * @return string
     * @since 3.0.0
     */
    public static function prepare_url($url, $mode = 'list') {
        $end = '';
        $url = explode(chr(13) . chr(10), $url);
        foreach ($url as $url) {
            $parts = explode(', ',$url);
            $parts[0] = trim( $parts[0] );
            $parts[1] = isset( $parts[1] ) ? $parts[1] : $parts[0];
            // list mode 
            if ( $mode == 'list' ) {
                $length = strlen($parts[1]);
                $parts[1] = substr($parts[1], 0 , 80);
                if ($length > 80) {
                    $parts[1] = $parts[1] . '[...]';
                }
                $end = $end . '<li><a class="tp_pub_list" style="background-image: url(' . get_tp_mimetype_images( $parts[0] ) . ')" href="' . $parts[0] . '" title="' . $parts[1] . '" target="_blank">' . $parts[1] . '</a></li>';
            }
            // enumeration mode
            else {
                $end = $end . '<a class="tp_pub_link" href="' . $parts[0] . '" title="' . $parts[1] . '" target="_blank"><img class="tp_pub_link_image" alt="" src="' . get_tp_mimetype_images( $parts[0] ) . '"/></a>';
            }
        }
        if ( $mode == 'list' ) {
            $end = '<ul class="tp_pub_list">' . $end . '</ul>';
        }
        return $end;
    }

    /**
     * Parse author names
     * @global $PARSECREATORS
     * @param string $input
     * @param string $mode       --> values: last, initials, old
     * @return string
     * @since 3.0.0
    */
    public static function parse_author ($input, $mode = '') {
        global $PARSECREATORS;
        /* the new teachpress parsing
         * last: 	Adolf F. Weinhold and Ludwig van Beethoven --> Weinhold, Adolf; van Beethoven, Ludwig
         * initials: 	Adolf F. Weinhold and Ludwig van Beethoven --> Weinhold, Adolf F; van Beethoven, Ludwig
        */
        if ($mode == 'last' || $mode == 'initials') {
            $creator = new PARSECREATORS();
            $creatorArray = $creator->parse($input);
            $all_authors = '';
            for ($i = 0; $i < count($creatorArray); $i++) {
                $one_author = '';
                if ($mode == 'last' || $mode == 'initials') {
                    if ($creatorArray[$i][3] != '') { $one_author = trim($creatorArray[$i][3]);}
                    if ($creatorArray[$i][2] != '') { $one_author .= ' ' .trim($creatorArray[$i][2]) . ',';}
                    if ($creatorArray[$i][0] != '') { $one_author .= ' ' .trim($creatorArray[$i][0]);}
                    if ($mode == 'initials') { 
                        if ($creatorArray[$i][1] != '') { $one_author = $one_author . ' ' .trim($creatorArray[$i][1]);}
                    }
                    $all_authors = $all_authors . stripslashes($one_author);
                    if ($i < count($creatorArray) -1) {$all_authors = $all_authors . '; ';}
                }
            }
        }
        /* the original (old) teachpress parsing
         * example: Adolf F. Weinhold and Ludwig van Beethoven --> Weinhold, Adolf F.; van Beethoven, Ludwig
        */
        elseif ($mode == 'old') {
            $all_authors = '';
            $one_author = '';
            $array = explode(" and ",$input);
            $lenth = count ($array);
            for ($i=0; $i < $lenth; $i++) {
                $array[$i] = trim($array[$i]);
                $names = explode(" ",$array[$i]);
                $lenth2 = count($names);
                for ($j=0; $j < $lenth2-1; $j++) {
                    $one_author = $one_author . ' ' . trim( $names[$j] );
                }
                $one_author = trim( $names[$lenth2 - 1] ). ', ' . $one_author;
                $all_authors = $all_authors . $one_author;
                if ($i < $lenth - 1) {
                    $all_authors = $all_authors . '; ';
                }
                $one_author = '';
            }
        }
        /* the simple teachpress parsing
         * example: Adolf F. Weinhold and Albert Einstein --> Adolf F. Weinhold, Albert Einstein
        */
        else {
            $all_authors = str_replace(' and ', ', ', $input);
        }
        return $all_authors;
    }
}
?>