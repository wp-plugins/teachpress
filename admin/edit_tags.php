<?php
/* Tag management
 * @param $search (String) - Suchergebnis
 * @param $tag_id (INT) - ID eines zu bearbeitenden Tags
*/ 
function teachpress_tags_page(){ 
    ?> 
    <div class="wrap" style="max-width:650px;">
    <form id="form1" name="form1" method="get" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
    <input name="page" type="hidden" value="teachpress/tags.php" />
    <?php
    global $wpdb;
    global $teachpress_relation;
    global $teachpress_tags;
    // form data
    $action = isset( $_GET['action'] ) ? $action = htmlspecialchars($_GET['action']) : '';
    $search = isset( $_GET['search'] ) ? $search = htmlspecialchars($_GET['search']) : '';
    $checkbox = isset( $_GET['checkbox'] ) ? $checkbox = $_GET['checkbox'] : '';
    $page = 'teachpress/tags.php';
    $number_messages = 50;
    // Handle limits
    if (isset($_GET['limit'])) {
        $curr_page = (int)$_GET['limit'] ;
        if ( $curr_page <= 0 ) {
            $curr_page = 1;
        }
        $entry_limit = ( $curr_page - 1 ) * $number_messages;
    }
    else {
        $entry_limit = 0;
        $curr_page = 1;
    }

    // actions
    // Delete publications - part 1
    if ( $action == "delete" ) {
        echo '<div class="teachpress_message">
            <p class="teachpress_message_headline">' . __('Are you sure to delete the selected elements?','teachpress') . '</p>
            <p><input name="delete_ok" type="submit" class="button-secondary" value="' . __('Delete','teachpress') . '"/>
            <a href="admin.php?page=' . $page . '&search=' . $search . '&amp;limit=' . $curr_page . '"> ' . __('Cancel','teachpress') . '</a></p>
            </div>';
    }
    // delete publications - part 2
    if ( isset($_GET['delete_ok']) ) {
        tp_delete_tags($checkbox);
        get_tp_message( __('Removing successful','teachpress') );
    }
    if ( isset( $_POST['tp_edit_tag_submit'] )) {
        $name = htmlspecialchars($_POST['tp_edit_tag_name']);
        $tag_id = intvar($_POST['tp_edit_tag_ID']);
        tp_edit_tag($tag_id, $name);
        get_tp_message( __('Tag saved','teachpress') );
    }
    
    // if the user use the search
    if ($search != "") {
        $sql = "SELECT * FROM " . $teachpress_tags . " WHERE `name` like '%$search%' OR `tag_id` = '$search'";	
    }
    // normal sql statement
    else {
        $sql = "SELECT * FROM " . $teachpress_tags . " ORDER BY `name`";
    }				
    $test = $wpdb->query($sql);
    $sql = $sql . " LIMIT $entry_limit, $number_messages";
    ?>
    <h2><?php _e('Tags'); ?></h2>
    <div id="searchbox" style="float:right; padding-bottom:10px;">
            <?php if ($search != "") { ?><a href="admin.php?page=teachpress/tags.php" style="font-size:14px; font-weight:bold; text-decoration:none; padding-right:3px;" title="<?php _e('Cancel the search','teachpress'); ?>">X</a><?php } ?>
        <input type="text" name="search" id="pub_search_field" value="<?php echo stripslashes($search); ?>"/>
        <input type="submit" name="button" id="button" value="<?php _e('Search'); ?>" class="button-secondary"/>
    </div>
    <div class="tablenav" style="padding-bottom:5px;"> 
        <select name="action">
            <option value="">- <?php _e('Bulk actions','teachpress'); ?> -</option>
            <option value="delete"><?php _e('Delete','teachpress'); ?></option>
        </select>
        <input name="OK" value="OK" type="submit" class="button-secondary"/>
        <?php
        // Page Menu
        echo tp_admin_page_menu ($test, $number_messages, $curr_page, $entry_limit, "admin.php?page=$page&amp;", "search=$search"); ?>
    </div>
    <div style="width:650px;">
    <table border="0" cellspacing="0" cellpadding="0" class="widefat">
        <thead>
        <tr>
            <th class="check-column"><input name="tp_check_all" id="tp_check_all" type="checkbox" value="" onclick="teachpress_checkboxes('checkbox[]','tp_check_all');" /></th>
            <th><?php _e('Name','teachpress'); ?></th>
            <th><?php _e('ID'); ?></th>
            <th><?php _e('Number','teachpress'); ?></th>
        </tr>
        </thead> 
        <?php
        if ($test == 0) {
            echo '<tr><td colspan="4"><strong>' . __('Sorry, no entries matched your criteria.','teachpress') . '</strong></td></tr>';
        }
        else {
            $sql2 = "SELECT * FROM $teachpress_relation";
            $row = $wpdb->get_results($sql2);
            $z=0;
            foreach ($row as $row) {
                $daten[$z][0] = $row->pub_id;
                $daten[$z][1] = $row->tag_id;
                $z++;
            }
            $row2 = $wpdb->get_results($sql);
            foreach ($row2 as $row2) {
                echo '<tr>';
                $checked = '';
                $str = "'";
                if ( $action == "delete") { 
                    for( $k = 0; $k < count( $checkbox ); $k++ ) { 
                        if ( $row2->tag_id == $checkbox[$k] ) { $checked = 'checked="checked" '; } 
                    } 
                }
                echo '<th class="check-column"><input name="checkbox[]" class="tp_checkbox" type="checkbox" ' . $checked . ' type="checkbox" value="' . $row2->tag_id . '"></th>';
                echo '<td id="tp_tag_row_' . $row2->tag_id . '">';
                echo '<a onclick="teachpress_editTags(' . $str . $row2->tag_id . $str . ')" class="teachpress_link" title="' . __('Click to edit','teachpress') . '" style="cursor:pointer;"><strong>' . stripslashes($row2->name) . '</strong></a><input type="hidden" id="tp_tag_row_name_' . $row2->tag_id . '" value="' . stripslashes($row2->name) . '"/>';
                echo '<div class="tp_row_actions">';
                echo '<a onclick="teachpress_editTags(' . $str . $row2->tag_id . $str . ')" class="teachpress_link" title="' . __('Click to edit','teachpress') . '" style="cursor:pointer;">' . __('Edit', 'teachpress') . '</a> | <a href="admin.php?page=' . $page . '&amp;checkbox%5B%5D=' . $row2->tag_id . '&amp;action=delete' . '" style="color:red;" title="' . __('Delete','teachpress') . '">' . __('Delete', 'teachpress') . '</a>';
                echo '</div>';
                echo '</td>';
                echo '<td>' . $row2->tag_id . '</td>';
                echo '<td>';
                $anzahl = 0;
                for ($i=0; $i < $z ; $i++) {
                    if ($daten[$i][1] == $row2->tag_id) {
                        $anzahl++;
                    }
                }
                echo $anzahl;
                echo '</td>';
                echo '</tr>';
            }
        } ?>
    </table>
    <div class="tablenav"><div class="tablenav-pages" style="float:right;">
      <?php 
      if ($test > $number_messages) {
         echo tp_admin_page_menu ($test, $number_messages, $curr_page, $entry_limit, "admin.php?page=$page&amp;", "search=$search", 'bottom');
      } 
      else {
         if ($test == 1) {
            echo '' . $test . ' ' . __('entry','teachpress') . '';
         }
         else {
            echo '' . $test . ' ' . __('entries','teachpress') . '';
         }
      }
      ?>
    </div></div>
    </div>
    </form>
    </div>
<?php } ?>