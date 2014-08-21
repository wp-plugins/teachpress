<?php
/**
 * This file contains all functions which are used in ajax calls
 * @package teachpress\core\ajax
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 5.0.0
 */

/**
 * This class contains all functions which are used in ajax calls
 * @package teachpress\core\ajax
 * @since 5.0.0
 */
class tp_ajax {
    /**
     * Adds a document headline
     * @param string $doc_name
     * @param int $course_id
     * @since 5.0.0
     * @access public
     */
    public static function add_document_headline( $doc_name, $course_id ) {
        $file_id = tp_documents::add_document($doc_name, '', $course_id);
        echo $file_id;
    }
    
    /**
     * Changes the name of a document
     * @param type $doc_id
     * @param type $doc_name
     * @since 5.0.0
     * @access public
     */
    public static function change_document_name( $doc_id, $doc_name ) {
        tp_documents::change_document_name($doc_id, $doc_name);
        echo $doc_name;
    }
    
    /**
     * Deletes a document
     * @param int $doc_id
     * @return boolean
     * @since 5.0.0
     * @access public
     */
    public static function delete_document( $doc_id ) {
        $doc_id = intval($doc_id);
        $data = tp_documents::get_document($doc_id);
        if ( $data['path'] !== '' ) {
            $uploads = wp_upload_dir();
            $test = @ unlink( $uploads['basedir'] . $data['path'] );
            //echo $uploads['basedir'] . $data['path'];
            if ( $test === false ) {
                echo 'false';
                return false;
            }
        }
        tp_documents::delete_document($doc_id);
        echo 'true';
        return true;
    }
    
    /**
     * Gets a list of publications of a single author. This function is used for teachpress/admin/show_authors.php
     * @param int $author_id
     * @since 5.0.0
     * @access public
     */
    public static function get_author_publications( $author_id ) {
        $author_id = intval($author_id);
        $pubs = tp_authors::get_related_authors($author_id, ARRAY_A);
        echo '<ol>';
        foreach ( $pubs as $pub) {
            echo '<li style="padding-left:10px;">';
            echo '<a target="_blank" title="' . __('Edit publication','teachpress') .'" href="admin.php?page=teachpress/addpublications.php&pub_id=' . $pub['pub_id'] . '">' . $pub['title'] . '</a>, ' . $pub['type'] . ', ' . $pub['year'];
            if ( $pub['is_author'] == 1 ) {
                echo ' (' . __('as author','teachpress') . ')';
            }
            if ( $pub['is_editor'] == 1 ) {
                echo ' (' . __('as editor','teachpress') . ')';
            }
            echo '</li>';
        }
        echo '</ol>';
    }
    
    /**
     * Gets the name of a document
     * @param int $doc_id
     * @since 5.0.0
     * @access public
     */
    public static function get_document_name( $doc_id ) {
        $doc_id = intval($doc_id);
        $data = tp_documents::get_document($doc_id);
        echo stripslashes($data['name']);
    }


    /**
     * Saves the order of a document list
     * @param array $array
     * @since 5.0.0
     * @access public
     */
    public static function set_sort_order( $array ) {
        $i = 0;
        foreach ($array as $value) {
            tp_documents::set_sort($value, $i);
            $i++;
        }
    }
}
