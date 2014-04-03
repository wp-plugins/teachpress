<?php
/**
 * This file contains the widget class
 * @package teachpress/core
 * @since 5.0.0
 */


/** 
 * teachPress Books widget class 
 */
class tp_books_widget extends WP_Widget {
    /** 
     * constructor 
     */
    function tp_books_widget() {
        $widget_ops = array('classname' => 'widget_teachpress_books', 'description' => __('Shows a random book in the sidebar', 'teachpress') );
        $control_ops = array('width' => 400, 'height' => 300);
        parent::WP_Widget(false, $name = __('teachPress books','teachpress'), $widget_ops, $control_ops);
    }

    /** 
     * Widget content area
     * @see WP_Widget::widget 
     * @param array $args
     * @param array $instance
     */
    function widget($args, $instance) {
        global $wpdb;
        extract( $args );
        $title = apply_filters('widget_title', $instance['title']);
        $all_url = get_permalink($instance['url']);
        $books = $instance['books'];
        $zahl = count($books);
        $zufall = rand(0, $zahl - 1);
        $pub_id = $books[$zufall];
        $row = $wpdb->get_row("SELECT `name`, `image_url`, `rel_page` FROM " . TEACHPRESS_PUB . " WHERE `pub_id` = '$pub_id'" );
        echo $before_widget;
        if ( $title ) {
            echo $before_title . $title . $after_title;
        }
        echo '<p style="text-align:center"><a href="' . get_permalink($row->rel_page) . '" title="' . $row->name . '"><img class="tp_image" src="' . $row->image_url . '" alt="' . $row->name . '" title="' . $row->name . '" /></a></p>';
        echo '<p style="text-align:center"><a href="' . $all_url . '" title="' . __('All books','teachpress') . '">' . __('All books','teachpress') . '</a></p>';
        echo $after_widget;
    }

    /** 
     * Update values
     * @see WP_Widget::update 
     * @param array $new_instance
     * @param array $old_instance
     */
    function update($new_instance, $old_instance) {				
        return $new_instance;
    }

    /** 
     * Widget admin area
     * @see WP_Widget::form 
     * @param array $instance
     */
    function form($instance) {
        global $wpdb;		
        $title = isset ($instance['title']) ? esc_attr($instance['title']) : '';
        $url = isset ($instance['url']) ? esc_attr($instance['url']) : '';
        $books = isset ($instance['books']) ? $instance['books'] : '';
        echo '<p><label for="' . $this->get_field_id('title') . '">' . __('Title', 'teachpress') . ': <input class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . $title . '" /></label></p>';

        echo '<p><label for="' . $this->get_field_id('books') . '">' . __('Books', 'teachpress') . ': <select class="widefat" id="' . $this->get_field_id('books') . '" name="' . $this->get_field_name('books') . '[]" style="height:auto; max-height:25em" multiple="multiple" size="10">';
        $sql= "SELECT `pub_id`, `name` FROM " . TEACHPRESS_PUB . " WHERE `type` = 'Book' ORDER BY `date` DESC";
        $row= $wpdb->get_results($sql);
        foreach ($row as $row) {
            if ( in_array($row->pub_id, $books) ) {
                echo '<option value="' . $row->pub_id . '" selected="selected">' . $row->pub_id . ': ' . $row->name . '</option>';
            }
            else {
                echo '<option value="' . $row->pub_id . '">' . $row->pub_id . ': ' . $row->name . '</option>';
            }
        }
        echo '</select></label><small class="setting-description">' . __('use &lt;Ctrl&gt; key to select multiple books', 'teachpress') . '</small></p>';

        echo '<p><label for="' . $this->get_field_id('url') . '">' . __('Releated Page for &laquo;all books&raquo; link:', 'teachpress') . ' <select class="widefat" id="' . $this->get_field_id('url') . '" name="' . $this->get_field_name('url') . '>';
        echo '<option value="">' . __('none','teachpress') . '</option>';

        $post_type = get_tp_option('rel_page_publications');
        get_tp_wp_pages("menu_order","ASC",$url,$post_type,0,0);
            echo '</select></label></p>';
    }
}
?>
