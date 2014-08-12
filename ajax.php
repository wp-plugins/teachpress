<?php
/**
 * This file contains the server side part for the teachpress ajax interface
 * @package teachpress
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 5.0.0
 */

// include wp-load.php
require_once( '../../../wp-load.php' );
if ( is_user_logged_in() && current_user_can('use_teachpress') ) {
    $author_id = intval($_GET['author_id']);
    if ( $author_id !== 0 ) {
        // $pub = tp_publications::get_publication($author_id, ARRAY_A);
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
}
