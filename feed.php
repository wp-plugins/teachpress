﻿<?php

/********************************/
/* Build feeds for publications */
/********************************/

// include wp-load.php
require_once( '../../../wp-load.php' );

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$tag = isset($_GET['tag']) ? intval($_GET['tag']) : 0;
$feedtype = isset($_GET['feedtype']) ? htmlspecialchars($_GET['feedtype']) : '';

/*
 * Bibtex 
 */
if ($feedtype == 'bibtex') {
    header('Content-Type: text/plain; charset=utf-8;');
    $row = get_tp_publications(array('user' => $id, 'tag' => $tag, 'output_type' => ARRAY_A));
    foreach ($row as $row) {
        $tags = get_tp_tags(array('pub_id' => $row['pub_id'], 'output_type' => ARRAY_A));
        echo tp_bibtex::get_single_publication_bibtex($row, $tags);
    }
}

/*
 * RSS 2.0
 */ else {
    $url = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Content-Type: application/xml;");
    echo '<?xml version="1.0" encoding="UTF-8"?>'. chr(13) . chr(10);
    echo '<rss version="2.0" 
            xmlns:content="http://purl.org/rss/1.0/modules/content/"
            xmlns:wfw="http://wellformedweb.org/CommentAPI/"
            xmlns:dc="http://purl.org/dc/elements/1.1/"
            xmlns:atom="http://www.w3.org/2005/Atom"
            xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
            xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
            >' . chr(13) . chr(10);
    echo '<channel>
            <title>' . get_bloginfo('name') . '</title>
            <atom:link href="' . $url . '" rel="self" type="application/rss+xml" />
            <link>' . get_bloginfo('url') . '</link>
            <description>' . get_bloginfo('description') . '</description>
            <language>' . get_bloginfo('language') . '</language>
            <sy:updatePeriod>daily</sy:updatePeriod>
            <sy:updateFrequency>1</sy:updateFrequency>
            <copyright>' . get_bloginfo('name') . '</copyright>
            <pubDate>' . date('r') . '</pubDate>
            <dc:creator>' . get_bloginfo('name') . '</dc:creator>' . chr(13) . chr(10);

    $row = get_tp_publications(array('user' => $id, 'tag' => $tag, 'output_type' => ARRAY_A));
    foreach ($row as $row) {
        if ($row['url'] != '') {
            $new = explode(', ', $row['url']);
            $item_link = $new[0];
        } elseif ($row['rel_page'] != '') {
            $item_link = get_bloginfo('url') . '/?page=' . $row['rel_page'];
        } else {
            $item_link = get_bloginfo('url');
        }
        $row['author'] = tp_bibtex::replace_html_chars($row['author']);
        $row['author'] = str_replace(' and ', ', ', $row['author']);
        $row['title'] = tp_bibtex::replace_html_chars($row['title']);
        $item_link = tp_bibtex::replace_html_chars($item_link);
        $settings['editor_name'] = 'simple';
        $settings['style'] = 'simple';
        echo '
             <item>
                <title><![CDATA[' . stripslashes($row['title']) . ']]></title>
                <description>' . tp_bibtex::single_publication_meta_row($row, $settings) . '</description>
                <link><![CDATA[' . $item_link . ']]></link>
                <dc:creator>' . stripslashes($row['author']) . '</dc:creator>
                <guid isPermaLink="false">' . get_bloginfo('url') . '?publication=' . $row['pub_id'] . '</guid>
                <pubDate>' . date('r', strtotime($row['date'])) . '</pubDate>
             </item>' . chr(13) . chr(10);
    }
    echo '</channel>' . chr(13) . chr(10);
    echo '</rss>' . chr(13) . chr(10);
}
?>
