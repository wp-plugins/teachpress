<?php
/**
 * Defines the screen options for show_authors_page
 * @global type $tp_admin_show_authors_page
 * @return type
 * @since 5.0.0
 */
function tp_show_authors_page_screen_options(){
    global $tp_admin_show_authors_page;
    $screen = get_current_screen();
 
    if(!is_object($screen) || $screen->id != $tp_admin_show_authors_page) {
        return;
    }

    $args = array(
        'label' => __('Items per page', 'teachpress'),
        'default' => 50,
        'option' => 'tp_authors_per_page'
    );
    add_screen_option( 'per_page', $args );
}

/**
 * teachpress show_authors_page
 * @since 5.0.0
 */
function teachpress_authors_page () {
    $search = isset( $_GET['search'] ) ? htmlspecialchars($_GET['search']) : '';
    $checkbox = isset( $_GET['checkbox'] ) ? $_GET['checkbox'] : array();
    $page = 'teachpress/tags.php';
    // Get screen options
    $user = get_current_user_id();
    $screen = get_current_screen();
    $screen_option = $screen->get_option('per_page', 'option');
    $per_page = get_user_meta($user, $screen_option, true);
    if ( empty ( $per_page) || $per_page < 1 ) {
        $per_page = $screen->get_option( 'per_page', 'default' );
    }
    echo '<div class="wrap" style="max-width:700px;">';
    echo '<h2>' . __('Authors','teachpress') . '</h2>';
    
    echo '<div id="searchbox" style="float:right; padding-bottom:10px;">';
    if ($search != "") {
        echo '<a href="admin.php?page=teachpress/tags.php" style="font-size:14px; font-weight:bold; text-decoration:none; padding-right:3px;" title="' . __('Cancel the search','teachpress') . '">X</a>';
    }
    echo '<input type="text" name="search" id="pub_search_field" value="' . stripslashes($search) . '"/>';
    echo '<input type="submit" name="button" id="button" value="' . __('Search','teachpress') . '" class="button-secondary"/>';
    echo '</div>';
    
    echo '<div class="tablenav" style="padding-bottom:5px;">';
    echo '<select name="action1">';
    echo '<option value="">- ' . __('Bulk actions','teachpress') . ' -</option>';
    echo '<option value="delete">' . __('Delete','teachpress') . '</option>';
    echo '</select>';
    echo '<input name="OK" value="OK" type="submit" class="button-secondary"/>';
    echo '</div>';
    
    echo '<table class="widefat" style="width:700px;">';
    echo '<thead>';
    echo '<th class="check-column"><input name="tp_check_all" id="tp_check_all" type="checkbox" value="" /></th>';
    echo '<th>' . __('Name','teachpress') . '</th>';
    echo '<th>' . __('ID','teachpress') . '</th>';
    echo '<th>' . __('Number publications','teachpress') . '</th>';
    echo '</thead>';
    
    $class_alternate = true;
    $row = tp_authors::count_authors($search);
    foreach ( $row as $row ) {
        $checked = '';
        if ( $class_alternate === true ) {
            $tr_class = 'class="alternate"';
            $class_alternate = false;
        }
        else {
            $tr_class = '';
            $class_alternate = true;
        }
        echo '<tr ' . $tr_class . '>';
        echo '<th class="check-column"><input name="checkbox[]" class="tp_checkbox" type="checkbox" ' . $checked . ' type="checkbox" value="' . $row['author_id'] . '"></th>';
        echo '<td>' . stripslashes($row['name']) . '</td>';
        echo '<td>' . $row['author_id'] . '</td>';
        echo '<td>' . $row['count'] . '</td>';
        echo '<tr>';
    }
    
    echo '</table>';
    echo '</div>';
}
?>
