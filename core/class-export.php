<?php
/**
 * teachPress export class
 *
 * @since 3.0.0
 */
class tp_export {

    /**
     * Print html table with registrations
     * @param int $course_ID
     * @param array $option
     * @param int $waitinglist 
     */
    static function get_course_registration_table($course_ID, $option, $waitinglist = '') {
        $row = get_tp_course_signups( array('course' => $course_ID, 'waitinglist' => $waitinglist, 'output_type' => ARRAY_A, 'order' => 'st.lastname ASC') );
        echo '<table border="1" cellpadding="5" cellspacing="0">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>' . __('Last name','teachpress') . '</th>';
        echo '<th>' . __('First name','teachpress') . '</th>';
        if ($option['regnum'] == '1') {
            echo '<th>' . __('Matr. number','teachpress') . '</th>';
        }
        if ($option['studies'] == '1') {
            echo '<th>' . __('Course of studies','teachpress') . '</th>';
        }
        echo '<th>' . __('User account','teachpress') . '</th>';
        echo '<th>' . __('E-Mail') . '</th>';
        echo '<th>' . __('Registered at','teachpress') . '</th>';
        echo '</tr>';
        echo '</thead>';  
        echo '<tbody>';
        foreach($row as $row) {
            $row['firstname'] = tp_export::decode($row['firstname']);
            $row['lastname'] = tp_export::decode($row['lastname']);
            $row['course_of_studies'] = tp_export::decode($row['course_of_studies']);
            echo '<tr>';
            echo '<td>' . stripslashes(utf8_decode($row['lastname'])) . '</td>';
            echo '<td>' . stripslashes(utf8_decode($row['firstname'])) . '</td>';
            if ($option['regnum'] == '1') {
                echo '<td>' . $row['matriculation_number'] . '</td>';
            }
            if ($option['studies'] == '1') {
                echo '<td>' . stripslashes(utf8_decode($row['course_of_studies'])) . '</td>';
            }
            echo '<td>' . $row['userlogin'] . '</td>';
            echo '<td>' . $row['email'] . '</td>';
            echo '<td>' . $row['date'] . '</td>';
            echo '</tr>';

        }
        echo '</tbody>';
        echo '</table>';
    }

    /**
     * Export course data in xls format
     * @param int $course_ID 
     */
    static function get_course_xls($course_ID) {
        global $wpdb;
        global $teachpress_courses;
		global $teachpress_signup;

        // load course data
        $daten = $wpdb->get_row("SELECT * FROM " . $teachpress_courses . " WHERE `course_id` = '$course_ID'", ARRAY_A);
        if ($daten['parent'] != '0') {
            $id = $daten['parent'];
            $parent = $wpdb->get_var("SELECT name FROM " . $teachpress_courses . " WHERE `course_id` = '$id'");
        }
        if ($parent != '') {
            $course_name = $parent . ' ' . $daten['name'];
        }
        else {
            $course_name = $daten['name'];
        }

        // load settings
        $option['regnum'] = get_tp_option('regnum');
        $option['studies'] = get_tp_option('studies');

        echo '<h2>' . stripslashes(utf8_decode($course_name)) . ' ' . stripslashes(utf8_decode($daten['semester'])) . '</h2>';
        echo '<table border="1" cellspacing="0" cellpadding="5">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>' . __('Lecturer','teachpress') . '</th>';
        echo '<td>' . stripslashes(utf8_decode($daten['lecturer'])) . '</td>';
        echo '<th>' . __('Date','teachpress') . '</th>';
        echo '<td>' . $daten['date'] . '</td>';
        echo '<th>' . __('Room','teachpress') . '</th>';
        echo '<td>' . stripslashes(utf8_decode($daten['room'])) . '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th>' . __('Places','teachpress') . '</th>';
        echo '<td>' . $daten['places'] . '</td>';
        echo '<th>' . __('free places','teachpress') . '</th>';
		$used_places = $wpdb->get_var("SELECT COUNT(`course_id`) FROM $teachpress_signup WHERE `course_id` = '" . $daten["course_id"] . "' AND `waitinglist` = 0");
        echo '<td>' . ($daten["places"] - $used_places) . '</td>';
        echo '<td>&nbsp;</td>';
        echo '<td>&nbsp;</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th>' . __('Comment','teachpress') . '</th>';
        echo '<td colspan="5">' . stripslashes(utf8_decode($daten['comment'])) . '</td>';
        echo '</tr>';
        echo '</thead>';
        echo '</table>';

        echo '<h3>' . __('Registered participants','teachpress') . '</h3>'; 
        tp_export::get_course_registration_table($course_ID, $option, 0);
        echo '<h3>' . __('Waiting list','teachpress') . '</h3>'; 
        tp_export::get_course_registration_table($course_ID, $option, 1);

        global $tp_version;
        echo '<p style="font-size:11px; font-style:italic;">' . __('Created on','teachpress') . ': ' . date("d.m.Y") . ' | teachPress ' . $tp_version . '</p>';
    }

    /**
     * Export course data in csv format
     * @param int $course_ID
     * @param array $options 
     */
    static function get_course_csv($course_ID) {
        // load settings
        $option['regnum'] = get_tp_option('regnum');
        $option['studies'] = get_tp_option('studies');
        $row = get_tp_course_signups( array('course' => $course_ID, 'waitinglist' => 0, 'output_type' => ARRAY_A, 'order' => 'st.lastname ASC') );

        if ($option['regnum'] == '1') { $matr = "" . __('Matr. number','teachpress') . ";"; } else { $matr = ""; }
        if ($option['studies'] == '1') { $cos = "" . __('Course of studies','teachpress') . ";"; } else { $cos = ""; }

        $headline = "" . __('Last name','teachpress') . ";" . __('First name','teachpress') . ";" . $matr . "" . $cos . "" . __('User account','teachpress') . ";" . __('E-Mail') . ";" . __('Registered at','teachpress') . ";" . __('Record-ID','teachpress') . ";" . __('Waiting list','teachpress') . "\r\n";
        $headline = tp_export::decode($headline);
        echo $headline;
        foreach($row as $row) {
            $row['firstname'] = tp_export::decode($row['firstname']);
            $row['lastname'] = tp_export::decode($row['lastname']);
            $row['course_of_studies'] = tp_export::decode($row['course_of_studies']);

            if ($option['regnum'] == '1') { $matr = "" . $row['matriculation_number'] . ";"; } else { $matr = ""; }
            if ($option['studies'] == '1') { $cos = "" . stripslashes(utf8_decode($row['course_of_studies'])) . ";"; } else { $cos = ""; }

            echo "" . stripslashes(utf8_decode($row['lastname'])) . ";" . stripslashes(utf8_decode($row['firstname'])) . ";" . $matr . "" . $cos . "" . $row['userlogin'] . ";" . $row['email'] . ";" . $row['date'] . ";" . $row['con_id'] . ";" . $row['waitinglist']. "\r\n";
        }
    }

    /**
     * Export publications
     * @param int $user_ID 
     * @param string $format - bibtex or rtf
     * @sinsce 4.2.0 
     */
    public static function get_publications($user_ID, $format = 'bibtex') {
        global $wpdb;
        global $teachpress_tags;
        global $teachpress_relation;
        
        $user_ID = intval($user_ID);
        $row = get_tp_publications( array('user' => $user_ID, 'output_type' => ARRAY_A) );
        if ( $format === 'bibtex' ) {
            foreach ($row as $row) {
                $tags = $wpdb->get_results("SELECT DISTINCT t.name FROM $teachpress_tags t INNER JOIN  $teachpress_relation r ON r.`tag_id` = t.`tag_id` WHERE r.pub_id = '" . $row['pub_id'] . "' ", ARRAY_A);
                echo tp_bibtex::get_single_publication_bibtex($row, $tags);
            }
        }     
        if ( $format === 'rtf' ) {
            echo tp_export::rtf($row);
        }
    }
    
    /**
     * Export a single publication
     * @param string $bibtex_key
     * @since 4.2.0
     */
    public static function get_publication_by_key($bibtex_key) {
        $row = get_tp_publication_by_key($bibtex_key, ARRAY_A);
        $tags = get_tp_tags( array( 'pub_id' => $row['pub_id'], 'output_type' => ARRAY_A ) );
        echo tp_bibtex::get_single_publication_bibtex($row, $tags);
    }

    /**
     * Generate rtf document format
     * @param array $row
     * @return string
     */
    static function rtf ($row) {
        $head = '{\rtf1';
        $line = '';
        foreach ($row as $row) {
            $line = $line . tp_export::rtf_row($row) . '\par'. '\par';
        }
        $foot = '}';
        return $head . $line . $foot;
    }

    /**
    * Get single line for rtf file
    * @param array $row
    * @return string 
    */
    static function rtf_row ($row) {
        $settings['editor_name'] = 'initials';
        $settings['style'] = 'simple';
        if ( $row['type'] == 'collection' ) {
            $all_authors = tp_bibtex::parse_author($row['editor'], $settings['editor_name'] );
        }
        else {
            $all_authors = tp_bibtex::parse_author($row['author'], $settings['editor_name'] );
        }
        $meta = tp_bibtex::single_publication_meta_row($row, $settings);
        $line = $all_authors . ' (' . $row['year'] . ')' . ': ' . stripslashes($row['title']) . '. ' . $meta;
        $line = str_replace('  ', ' ', $line);
        $line = utf8_decode($line);
        return $line;
    }

    /**
    * Decode chars
    * @param string $char
    * @return string 
    */
    static function decode ($char) {
        $array_1 = array('Ã¼','Ã¶', 'Ã¤', 'Ã¤', 'Ã?','Â§','Ãœ','Ã','Ã–','&Uuml;','&uuml;', '&Ouml;', '&ouml;', '&Auml;','&auml;', '&nbsp;', '&szlig;', '&sect;', '&ndash;', '&rdquo;', '&ldquo;', '&eacute;', '&egrave;', '&aacute;', '&agrave;', '&ograve;','&oacute;', '&copy;', '&reg;', '&micro;', '&pound;', '&raquo;', '&laquo;', '&yen;', '&Agrave;', '&Aacute;', '&Egrave;', '&Eacute;', '&Ograve;', '&Oacute;', '&shy;', '&amp;');
        $array_2 = array('ü','ö', 'ä', 'ä', 'ß', '§','Ü','Ä','Ö','Ü','ü', 'Ö', 'ö', 'Ä', 'ä', ' ', 'ß', '§', '-', '”', '“', 'é', 'è', 'á', 'à', 'ò', 'ó', '©', '®', 'µ', '£', '»', '«', '¥', 'À', 'Á', 'È', 'É', 'Ò', 'Ó', '­', '&');
        $char = str_replace($array_1, $array_2, $char);
        return $char;
    }
}
?>
