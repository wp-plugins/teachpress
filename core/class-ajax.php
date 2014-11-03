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
        $file_id = tp_documents::add_document($doc_name, '', 0, $course_id);
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
    
    public static function get_artefact_screen($artefact_id) {
        $artefact = tp_artefacts::get_artefact($artefact_id);
        echo '<!doctype html>';
        echo '<html>';
        echo '<head>';
        echo '<meta charset="utf-8">';
	echo '<title>teachPress - Assessment details</title>';
        echo '</head>';
        echo '<body>';
        echo '<div id="content">';
        echo '<form method="post">';
        echo '<input name="tp_artefact_id" type="hidden" value="' . $artefact_id . '"/>';
        echo '<table class="form-table">';
        echo '<tr>';
        echo '<td>' . __('Title','teachpress') . '</td>';
        echo '<td><input name="tp_artefact_title" cols="50" value="' . stripslashes($artefact['title']) . '"/></td>';
        echo '</tr>';
        echo '</table>';
        echo '<p><input name="tp_save_artefact" type="submit" class="button-primary" value="' . __('Save') . '"/> <input name="tp_delete_artefact" type="submit" class="button-secondary" value="' . __('Delete','teachpress') . '"/></p>';
        echo '</form>';
        echo '</div>';
        echo '</body>';
        echo '</html>';
    }
    
    /**
     * Gets the info screen for a single assessment.
     * @param type $assessment_id
     * @since 5.0.0
     * @access public
     */
    public static function get_assessment_screen($assessment_id) {
        global $current_user;
        $assessment = tp_assessments::get_assessment($assessment_id);
        $artefact = tp_artefacts::get_artefact($assessment['artefact_id']);
        $course_id = ( $assessment['course_id'] !== '' ) ? $assessment['course_id'] : $artefact['course_id'];
        $capability = tp_courses::get_capability($course_id, $current_user->ID);
        $student = tp_students::get_student($assessment['wp_id']);
        $examiner = get_userdata($assessment['examiner_id']);

        // Check capability
        if ( $capability !== 'owner' && $capability !== 'approved' ) {
            return;
        }

        $artefact['title'] = ( $artefact['title'] == '' ) ? __('Complete Course','teachpress') : $artefact['title'];
        echo '<!doctype html>';
        echo '<html>';
        echo '<head>';
        echo '<meta charset="utf-8">';
	echo '<title>teachPress - Assessment details</title>';
        echo '</head>';
        echo '<body>';
        echo '<div id="content">';
        echo '<form method="post">';
        echo '<input name="tp_assessment_id" type="hidden" value="' . $assessment_id . '"/>';
        echo '<table class="form-table">';
        echo '<tr>';
        echo '<td>' . __('Name','teachpress') . '</td>';
        echo '<td>' . stripslashes($student['firstname']) . ' ' . stripslashes($student['lastname']) . '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td>' . __('Artefact','teachpress') . '</td>';
        echo '<td>' . stripslashes($artefact['title'])  . '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td>' . __('Type') . '</td>';
        echo '<td>' . tp_admin::get_assessment_type_field('tp_type', $assessment['type']) . '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td>' . __('Value/Grade','teachpress') . '</td>';
        echo '<td><input name="tp_value" type="text" size="50" value="' . $assessment['value'] . '" /></td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td>' . __('Comment','teachpress') . '</td>';
        echo '<td><textarea name="tp_comment" rows="4" cols="50">' . stripslashes($assessment['comment']) . '</textarea></td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td>' . __('Has passed','teachpress') . '</td>';
        echo '<td>' . tp_admin::get_assessment_passed_field('tp_passed', $assessment['passed']) . '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td>' . __('Date','teachpress') . '</td>';
        echo '<td>' . $assessment['exam_date'] . '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td>' . __('Examiner','teachpress') . '</td>';
        echo '<td>' . stripslashes($examiner->display_name) . '</td>';
        echo '</tr>';
        echo '</table>';
        echo '<p><input name="tp_save_assessment" type="submit" class="button-primary" value="' . __('Save') . '"/> <input name="tp_delete_assessment" type="submit" class="button-secondary" value="' . __('Delete','teachpress') . '"/></p>';
        echo '</form>';
        echo '</div>';
        echo '</body>';
        echo '</html>';
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
