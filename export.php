<?php
/**
 * This file contains the XLS and CSV constructor for courses and publications export
 * @package teachpress
 */

// include wp-load.php
require_once( '../../../wp-load.php' );
$key = isset ( $_GET['key'] ) ? $_GET['key'] : '';

// Export single publication
if ($key != '') {
    header('Content-Type: text/plain; charset=utf-8' );
    $filename = preg_replace('/[^a-zA-Z0-9]/', '_', $key);
    header("Content-Disposition: attachment; filename=" . $filename . ".bib");
    tp_export::get_publication_by_key($key); 
} 
elseif ( is_user_logged_in() && current_user_can('use_teachpress') ) {
    $type = isset ( $_GET['type'] ) ? htmlspecialchars($_GET['type']) : '';
    $course_ID = isset ( $_GET['course_ID'] ) ? intval($_GET['course_ID']) : 0;
    $user_ID = isset ( $_POST['tp_user'] ) ? intval($_POST['tp_user']) : 0;
    $format = isset ( $_POST['tp_format'] ) ?  htmlspecialchars($_POST['tp_format']) : '';
    $filename = 'teachpress_course_' . $course_ID . '_' . date('dmY');

    // Export courses
    if ($type === "xls" && $course_ID != 0) {
        header("Content-type: application/vnd-ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=" . $filename . ".xls");
        tp_export::get_course_xls($course_ID);
    }

    if ($type === 'csv' && $course_ID != 0) {
        header('Content-Type: text/x-csv');
        header("Content-Disposition: attachment; filename=" . $filename . ".csv");
        tp_export::get_course_csv($course_ID);
    }
    // Export publication lists
    if ( $type === 'pub' ) {
        $filename = 'teachpress_pub_' . date('dmY');
        if ( $format === 'bib' ) {
            header('Content-Type: text/plain; charset=utf-8' );
            header("Content-Disposition: attachment; filename=" . $filename . ".bib");
            echo '% This file was created with teachPress ' . get_tp_version() . chr(13) . chr(10);
            echo '% Encoding: UTF-8' . chr(13) . chr(10) . chr(13) . chr(10);
            tp_export::get_publications($user_ID,'bibtex');
        }
        if ( $format === 'txt' ) {
            header('Content-Type: text/plain; charset=utf-8' );
            header("Content-Disposition: attachment; filename=" . $filename . ".txt");
            tp_export::get_publications($user_ID,'bibtex');
        }
        if ( $format === 'rtf' ) {
            header('Content-Type: text/plain; charset=utf-8' );
            header("Content-Disposition: attachment; filename=" . $filename . ".rtf");
            tp_export::get_publications($user_ID,'rtf');
        }
        if ( $format === 'rss' ) {
            if ( $user_ID == 0 ) {
                header("Location: " . plugins_url() . "/teachpress/feed.php");
                exit;
            }
            else {
                header("Location: " . plugins_url() . "/teachpress/feed.php?id=$user_ID");
                exit;
            }
        }
    }
} ?>   