<?php
/**
 * Add help tab for show publications page
 */
function tp_show_publications_page_help () {
    $screen = get_current_screen();  
    $screen->add_help_tab( array(
        'id'        => 'tp_show_publications_help',
        'title'     => __('Display publications','teachpress'),
        'content'   => '<p><strong>' . __('Shortcodes') . '</strong></p>
                        <p>' . __('You can use publications in a page or article with the following shortcodes:','teachpress') . '</p>
                        <p>' . __('For a single publication:','teachpress') .  '<strong>[tpsingle]</strong></p>
                        <p>' . __('For a publication list with tag cloud:','teachpress') . ' <strong>[tpcloud]</strong></p>
                        <p>' . __('For normal publication lists:','teachpress') . ' <strong>[tplist]</strong></p>
                        <p><strong>' . __('More information','teachpress') . '</strong></p>
                        <p><a href="http://mtrv.wordpress.com/teachpress/shortcode-reference/" target="_blank" title="teachPress Shortcode Reference (engl.)">teachPress Shortcode Reference (engl.)</a></p>',
    ) );
} 

/* Show all publications / Show user's publications
 * from addpublications.php (GET):
 * @param $search (String)
*/  
function teachpress_publications_page() {
    // WordPress User informations
    global $current_user;
    get_currentuserinfo();
    // parameters from form
    global $pagenow;

    $checkbox = isset( $_GET['checkbox'] ) ? $_GET['checkbox'] : '';
    $action = isset( $_GET['action'] ) ? $_GET['action'] : '';
    $page = isset( $_GET['page'] ) ? htmlspecialchars($_GET['page']) : '';
    $filter = ( isset( $_GET['filter'] ) && $_GET['filter'] != '0' ) ? htmlspecialchars($_GET['filter']) : '';
    $user = isset( $_GET['user'] ) ? intval($_GET['user']) : '';
    $search = isset( $_GET['search'] ) ? htmlspecialchars($_GET['search']) : '';
    $tag_id = isset( $_GET['tag'] ) ? intval($_GET['tag']) : '';

    // Page menu
    $number_messages = 50;
    // Handle limits
    if ( isset($_GET['limit']) ) {
        $curr_page = intval($_GET['limit']);
        if ( $curr_page <= 0 ) {
            $curr_page = 1;
        }
        $entry_limit = ( $curr_page - 1 ) * $number_messages;
    }
     else {
        $entry_limit = 0;
        $curr_page = 1;
    }
    // test if teachpress database is up to date
    $test = get_tp_option('db-version');
    $version = get_tp_version();

    // if is the actual one
    if ($test != $version) {
        $message = __('An database update is necessary.','teachpress') . ' <a href="options-general.php?page=teachpress/settings.php&amp;up=1">' . __('Update','teachpress') . '</a>';
        get_tp_message($message, '');
    }
    // Add a bookmark for the publication
    if ( isset( $_GET['add_id'] ) ) {
        tp_add_bookmark($_GET['add_id'], $user);
    }
    // Delete bookmark for the publication
    if ( isset( $_GET['del_id'] ) ) {
        tp_delete_bookmark($_GET['del_id']);
    }
    ?>
    <div class="wrap">
    <form id="showlvs" name="form1" method="get" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
    <input type="hidden" name="page" id="page" value="<?php echo $page; ?>" />
    <input type="hidden" name="tag" id="tag" value="<?php echo $tag_id; ?>" />
    <?php

    // Delete publications - part 1
    if ( $action == "delete" ) {
        echo '<div class="teachpress_message">
              <p class="teachpress_message_headline">' . __('Are you sure to delete the selected elements?','teachpress') . '</p>
              <p><input name="delete_ok" type="submit" class="button-primary" value="' . __('Delete','teachpress') . '"/>
              <a href="admin.php?page=publications.php&search=' . $search . '&amp;limit=' . $curr_page . '" class="button-secondary"> ' . __('Cancel','teachpress') . '</a></p>
              </div>';
    }
    // delete publications - part 2
    if ( isset($_GET['delete_ok']) ) {
        tp_delete_publications($checkbox);
        get_tp_message( __('Removing successful','teachpress') );
    }

    if ($page == 'publications.php' && $search == '') {
        $title = __('All publications','teachpress');
    }
    else {
        $title = __('Your publications','teachpress');
    }
    // For displaying bibtex entries
    if ($action == 'bibtex') {
        echo '<p><a href="admin.php?page=' . $page . '&amp;search=' . $search . '&amp;limit=' . $curr_page . '" class="button-secondary">&larr; ' . __('Back','teachpress') . '</a></p>';
        echo '<h2>' . __('BibTeX','teachpress') . '</h2>';
        echo '<textarea name="bibtex_area" rows="20" style="width:90%;" >';
        if ( $checkbox != '' ) {
            for ($i=0; $i < count ($checkbox); $i++) {
                $checkbox[$i] = intval($checkbox[$i]);
                $row = get_tp_publication( $checkbox[$i], ARRAY_A );
                $tags = get_tp_tags( array('output_type' => ARRAY_A, 'pub_id' => $checkbox[$i]) );
                echo tp_bibtex::get_single_publication_bibtex($row, $tags);	
            }
        }
        else {
            $row = get_tp_publications( array('output_type' => ARRAY_A) );
            foreach ( $row as $row ) {
                $tags = get_tp_tags( array('output_type' => ARRAY_A, 'pub_id' => $row['pub_id']) );
                echo tp_bibtex::get_single_publication_bibtex($row, $tags);
            }
        }
        echo '</textarea>';
    }
    else {
        $user_ID = $page == 'publications.php' ? '' : $current_user->ID;
        $args = array('search' => $search,
                      'user' => $user_ID,
                      'tag' => $tag_id,
                      'limit' => $entry_limit . ',' .  $number_messages,
                      'type' => $filter,
                      'order' => 'date DESC, title ASC'
                     );
        $test = get_tp_publications($args, true);

        // Load tags
        $tags = get_tp_tags( array('output_type' => ARRAY_A) );
        
        // Load bookmarks
        $bookmarks = get_tp_bookmarks( array('user'=> $current_user->ID, 'output_type' => ARRAY_A) );
        
      ?>
      <h2><?php echo $title; ?></h2>
      <div id="searchbox" style="float:right; padding-bottom:5px;">
         <?php if ($search != "") { 
                 echo '<a href="admin.php?page=' . $page . '&amp;filter=' . $filter . '&amp;tag=' . $tag_id . '" style="font-size:14px; font-weight:bold; text-decoration:none; padding-right:3px;" title="' . __('Cancel the search','teachpress') . '">X</a>';
         } ?>
         <input type="text" name="search" id="pub_search_field" value="<?php echo $search; ?>"/>
         <input type="submit" name="pub_search_button" id="pub_search_button" value="<?php _e('Search','teachpress'); ?>" class="button-secondary"/>
      </div>
      <div class="tablenav" style="padding-bottom:5px;">
      <select name="action">
         <option value="0">- <?php _e('Bulk actions','teachpress'); ?> -</option>
         <option value="bibtex"><?php _e('Show as BibTeX entry','teachpress'); ?></option>
         <?php if ($page == 'publications.php') {?>
         <option value="delete"><?php _e('Delete','teachpress'); ?></option>
         <?php } ?>
      </select>
      <input name="ok" value="<?php _e('OK','teachpress'); ?>" type="submit" class="button-secondary"/>
      <select name="filter">
         <option value="0">- <?php _e('All types','teachpress'); ?> -</option>
         <?php echo get_tp_publication_type_options ($filter, $mode = 'pl'); ?>
      </select>
      <input name="filter-ok" value="<?php _e('Limit selection','teachpress'); ?>" type="submit" class="button-secondary"/>
      <?php
      // Page Menu
      echo tp_admin_page_menu ($test, $number_messages, $curr_page, $entry_limit, "admin.php?page=$page&amp;", "search=$search&amp;filter=$filter&amp;tag=$tag_id"); ?>
      </div>
      <table class="widefat">
         <thead>
            <tr>
               <th>&nbsp;</th>
               <th class="check-column"><input name="tp_check_all" id="tp_check_all" type="checkbox" value="" onclick="teachpress_checkboxes('checkbox','tp_check_all');" /></th>
               <th><?php _e('Title','teachpress'); ?></th>
               <th><?php _e('ID'); ?></th>
               <th><?php _e('Type'); ?></th> 
               <th><?php _e('Author(s)','teachpress'); ?></th>
               <th><?php _e('Tags'); ?></th>
               <th><?php _e('Year','teachpress'); ?></th>
            </tr>
         </thead>
         <tbody>
         <?php
         if ($test == 0) {
             echo '<tr><td colspan="7"><strong>' . __('Sorry, no entries matched your criteria.','teachpress') . '</strong></td></tr>';
         }
         else {
             //$row = $wpdb->get_results($abfrage);
             $row = get_tp_publications($args);
             foreach ($row as $row) { 
                 $get_string = '&amp;search=' . $search . '&amp;filter=' . $filter . '&amp;limit=' . $curr_page . '&amp;site=' . $page . '&amp;tag=' . $tag_id . '';
                 ?>
               <tr>
                  <td style="font-size:20px; padding-top:8px; padding-bottom:0px; padding-right:0px;">
                  <?php
                  // check if the publication is already in users publication list
                  $test2 = false;
                  foreach ( $bookmarks as $bookmark ) {
                      if ( $bookmark['pub_id'] == $row->pub_id ) {
                          $test2 = $bookmark['bookmark_id'];
                          break;
                      }
                  }
                  if ($page == 'publications.php') {
                     // Add to your own list icon
                     if ($test2 == false) {
                        echo '<a href="' . $pagenow . '?page=' . $page . '&amp;add_id='. $row->pub_id . '&amp;user=' . $current_user->ID . $get_string . '" title="' . __('Add to your own list','teachpress') . '">+</a>';
                     }
                  }
                  else {
                     // Delete from your own list icon
                     echo '<a href="' . $pagenow . '?page=' . $page .'&amp;del_id='. $test2 . $get_string . '" title="' . __('Delete from you own publication list','teachpress') . '">&laquo;</a>';
                  } ?>
                  </td>
                  <?php
                  $checked = '';
                  if ( $action == "delete") { 
                     for( $k = 0; $k < count( $checkbox ); $k++ ) { 
                        if ( $row->pub_id == $checkbox[$k] ) { $checked = 'checked="checked" '; } 
                     } 
                  }
                  echo '<th class="check-column"><input name="checkbox[]" class="tp_checkbox" type="checkbox" ' . $checked . ' value="' . $row->pub_id . '" /></th>';
                  echo '<td>';
                  echo '<a href="admin.php?page=teachpress/addpublications.php&amp;pub_ID=' . $row->pub_id . $get_string . '" class="teachpress_link" title="' . __('Click to edit','teachpress') . '"><strong>' . stripslashes($row->title) . '</strong></a>';
                  echo '<div class="tp_row_actions"><a href="admin.php?page=teachpress/addpublications.php&amp;pub_ID=' . $row->pub_id . $get_string . '" class="teachpress_link" title="' . __('Click to edit','teachpress') . '">' . __('Edit','teachpress') . '</a> | <a href="' . $pagenow . '?page=' . $page .'&amp;checkbox%5B%5D=' . $row->pub_id . '&amp;action=delete' . $get_string . '" style="color:red;" title="' . __('Delete','teachpress') . '">' . __('Delete','teachpress') . '</a></div>';
                  echo '</td>';
                  echo '<td>' . $row->pub_id . '</td>';
                  echo '<td>' . tp_translate_pub_type($row->type) . '</td>';
                  if ( $row->type == 'collection' || ( $row->author == '' && $row->editor != '' ) ) {
                     echo '<td>' . stripslashes( str_replace(' and ', ', ', $row->editor) ) . ' (' . __('Ed.','teachpress') . ')</td>';
                  }
                  else {
                     echo '<td>' . stripslashes( str_replace(' and ', ', ', $row->author) ) . '</td>';
                  }
                  echo '<td>';
                  // Tags
                  $tag_string = '';
                  foreach ($tags as $temp) {
                     if ($temp["pub_id"] == $row->pub_id) {
                        if ($temp["tag_id"] == $tag_id) {
                           $tag_string = $tag_string . '<a href="admin.php?page=' . $page . '&amp;search=' . $search . '&amp;filter=' . $filter . '&amp;limit=' . $curr_page . '" title="' . __('Delete tag as filter','teachpress') . '"><strong>' . stripslashes($temp["name"]) . '</strong></a>, ';
                        }
                        else {
                           $tag_string = $tag_string . '<a href="admin.php?page=' . $page . '&amp;search=' . $search . '&amp;filter=' . $filter . '&amp;tag=' . $temp["tag_id"] . '" title="' . __('Show all publications which have a relationship to this tag','teachpress') . '">' . stripslashes($temp["name"]) . '</a>, ';
                        }
                     }
                  }
                  echo substr($tag_string, 0, -2);
                  echo '</td>';
                  echo '<td>' . $row->year . '</td>'; ?>
               </tr>
                 <?php       
                 }
              }
              ?>
          </tbody>
      </table>
      <div class="tablenav"><div class="tablenav-pages" style="float:right;">
      <?php 
      if ($test > $number_messages) {
         echo tp_admin_page_menu ($test, $number_messages, $curr_page, $entry_limit, "admin.php?page=$page&amp;", "search=$search&amp;filter=$filter&amp;tag=$tag_id", 'bottom');
      } 
      else {
         if ($test == 1) {
            echo "$test " . __('entry','teachpress');
         }
         else {
            echo "$test " . __('entries','teachpress');
         }
      }
      ?>
      </div></div>
     <?php } ?>
     </form>
     <?php
     if ( $action == 'bibtex' ) {
        echo '<script type="text/javascript">
               document.form1.bibtex_area.focus();
               document.form1.bibtex_area.select();
               </script>';
     }
     ?>
     </div>
<?php } ?>