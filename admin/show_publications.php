<?php
/**
 * This file contains all functions for displaying the show_publications page in admin menu
 * @package teachpress/admin
 */

/**
 * Add screen options for show publications page
 */
function tp_show_publications_page_screen_options() {
    global $tp_admin_all_pub_page;
    global $tp_admin_your_pub_page;
    $screen = get_current_screen();
 
    if(!is_object($screen) || ( $screen->id != $tp_admin_all_pub_page && $screen->id != $tp_admin_your_pub_page ) ) {
        return;
    }

    $args = array(
        'label' => __('Items per page', 'teachpress'),
        'default' => 50,
        'option' => 'tp_pubs_per_page'
    );
    add_screen_option( 'per_page', $args );
}

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

/**
 * Controller for show publications page
 * @global object $current_user
 */
function teachpress_publications_page() {
    // WordPress User informations
    global $current_user;
    get_currentuserinfo();
    
     // Get screen options
    $screen = get_current_screen();
    $screen_option = $screen->get_option('per_page', 'option');
    $per_page = get_user_meta($current_user->ID, $screen_option, true);
    if ( empty ( $per_page) || $per_page < 1 ) {
        $per_page = $screen->get_option( 'per_page', 'default' );
    }

    $array_variables['checkbox'] = isset( $_GET['checkbox'] ) ? $_GET['checkbox'] : '';
    $array_variables['action'] = isset( $_GET['action'] ) ? $_GET['action'] : '';
    $array_variables['page'] = isset( $_GET['page'] ) ? htmlspecialchars($_GET['page']) : '';
    $array_variables['type'] = ( isset( $_GET['filter'] ) && $_GET['filter'] != '0' ) ? htmlspecialchars($_GET['filter']) : '';
    $array_variables['year'] = isset( $_GET['year'] ) ? intval($_GET['year']) : '';
    $array_variables['search'] = isset( $_GET['search'] ) ? htmlspecialchars($_GET['search']) : '';
    $array_variables['tag_id'] = isset( $_GET['tag'] ) ? intval($_GET['tag']) : '';
    $user = $current_user->ID;

    // Page menu
    $array_variables['per_page'] = $per_page;
    // Handle limits
    if ( isset($_GET['limit']) ) {
        $array_variables['curr_page'] = intval($_GET['limit']);
        if ( $array_variables['curr_page'] <= 0 ) {
            $array_variables['curr_page'] = 1;
        }
        $array_variables['entry_limit'] = ( $array_variables['curr_page'] - 1 ) * $per_page;
    }
     else {
        $array_variables['entry_limit'] = 0;
        $array_variables['curr_page'] = 1;
    }
    
    echo '<div class="wrap">';
    
    // test if teachpress database is up to date
    $test = get_tp_option('db-version');
    $version = get_tp_version();

    if ($test != $version) {
        $message = __('An database update is necessary.','teachpress') . ' <a href="options-general.php?page=teachpress/settings.php&amp;up=1">' . __('Update','teachpress') . '</a>';
        get_tp_message($message);
    }
    
    // Add a bookmark for the publication
    if ( isset( $_GET['add_id'] ) ) {
        tp_bookmarks::add_bookmark( $_GET['add_id'], $current_user->ID );
    }
    
    // Delete bookmark for the publication
    if ( isset( $_GET['del_id'] ) ) {
        tp_bookmarks::delete_bookmark( $_GET['del_id'] );
    }
    
    // Add a bookmark for the publication (bulk version)
    if ( $array_variables['action'] === 'add_list' ) {
        $max = count( $array_variables['checkbox'] );
        for( $i = 0; $i < $max; $i++ ) {
            $array_variables['checkbox'][$i] = intval($array_variables['checkbox'][$i]);
            $test = tp_bookmarks::bookmark_exists($array_variables['checkbox'][$i], $current_user->ID);
            if ( $test === false ) {
                tp_bookmarks::add_bookmark( $array_variables['checkbox'][$i], $current_user->ID );
            }
        }
        get_tp_message( __('Publications added','teachpress') );
    }
    
    // delete publications - part 2
    if ( isset($_GET['delete_ok']) ) {
        tp_publications::delete_publications($array_variables['checkbox']);
        get_tp_message( __('Removing successful','teachpress') );
    }
    
    // Bulk edit of publications
    if ( isset($_GET['bulk_edit']) ) {
        $mass_edit = ( isset($_GET['mass_edit']) ) ? $_GET['mass_edit'] : '';
        $tags = ( isset($_GET['add_tags']) ) ? $_GET['add_tags'] : '';
        $delbox = ( isset($_GET['delbox']) ) ? $_GET['delbox'] : '';
        tp_tags::change_tag_relations($mass_edit, $tags, $delbox);
        get_tp_message( __('Bulk edit executed','teachpress') );
    }
    
    // Show page
    if ( $array_variables['action'] === 'bibtex' ) {
        tp_show_publications_page_bibtex_screen($array_variables);
    }
    else {
        tp_show_publications_page_main_screen($user, $array_variables);
    }
    
    echo '</div>';
}

/**
 * bibtex mode for show publications page
 * @param array $array_variables
 * @since 4.3.0
 */
function tp_show_publications_page_bibtex_screen($array_variables) {
    $sel = '';
    echo '<form name="form1">';
    echo '<p><a href="admin.php?page=' . $array_variables['page'] . '&amp;search=' . $array_variables['search'] . '&amp;limit=' . $array_variables['curr_page'] . '" class="button-secondary">&larr; ' . __('Back','teachpress') . '</a></p>';
    echo '<h2>' . __('BibTeX','teachpress') . '</h2>';
    echo '<textarea name="bibtex_area" rows="20" style="width:90%;" >';

    if ( $array_variables['checkbox'] != '' ) {
        $max = count ($array_variables['checkbox']);
        for ($i=0; $i < $max; $i++) {
            $pub = intval($array_variables['checkbox'][$i]);
            $row = tp_publications::get_publication( $pub, ARRAY_A );
            $tags = tp_tags::get_tags( array('output_type' => ARRAY_A, 'pub_id' => $pub) );
            echo tp_bibtex::get_single_publication_bibtex($row, $tags);
            $sel = ( $sel !== '' ) ? $sel . ',' . $pub : $pub;
        }
    }
    else {
        $row = tp_publications::get_publications( array('output_type' => ARRAY_A) );
        foreach ( $row as $row ) {
            $tags = tp_tags::get_tags( array('output_type' => ARRAY_A, 'pub_id' => $row['pub_id']) );
            echo tp_bibtex::get_single_publication_bibtex($row, $tags);
        }
    }

    echo '</textarea>';
    echo '</form>';
    echo '<script type="text/javascript">
           document.form1.bibtex_area.focus();
           document.form1.bibtex_area.select();
           </script>';
    if ( $sel != '' ) {
        echo '<form id="tp_export" method="post" action="' . plugins_url() . '/teachpress/export.php?type=pub">';
        echo '<input type="hidden" name="tp_sel" value="' . $sel . '"/>';
        echo '<input type="hidden" name="tp_format" value="bib"/>';
        echo '<input type="submit" name="tp_submit" class="button-primary" value="' . __('Export','teachpress') . ' (.bibtex)"/>';
        echo '</form>';
    }
}

/**
 * Bulk edit screen for show publications page
 * @param array $array_variables
 * @since 4.3.0
 */
function tp_show_publications_page_bulk_edit_screen($array_variables) {
    $selected_publications = '';
    $max = count($array_variables['checkbox']);
    for ( $i = 0; $i < $max; $i++ ) {
        $selected_publications = ( $selected_publications === '' ) ? $array_variables['checkbox'][$i] : $selected_publications . ',' . $array_variables['checkbox'][$i];
    }
    echo '<tr class="inline-edit-row" id="tp-inline-edit-row" style="display:table-row;">';
    echo '<td colspan="8" class="colspanchange" style="padding-bottom:7px;">';
    echo '<h4>' . __('Bulk editing','teachpress') . '</h4>';
    echo '<div id="bulk-titles" style="width:30%; float:left;">';
    echo '<ul>';
    $list = tp_publications::get_publications( array('include' => $selected_publications, 'output_type' => ARRAY_A) );
    foreach ( $list as $row ) {
        echo '<li><input type="checkbox" name="mass_edit[]" id="mass_edit_'. $row['pub_id'] . '" value="'. $row['pub_id'] . '" checked="checked"/> <label for="mass_edit_'. $row['pub_id'] . '">'. $row['title'] . '</label></li>';
    }
    echo '</ul>';
    echo '</div>';
    echo '<div class="tp_mass_edit_right">';
    echo '<p><b>' . __('Delete current tags','teachpress') . '</b></p>';
    $used_tags = tp_tags::get_tags( array('pub_id' => $selected_publications, 'output_type' => ARRAY_A, 'group_by' => true) );
    $s = "'";
    echo '<p>';
    foreach ( $used_tags as $row ) {
        echo'<input name="delbox[]" type="checkbox" value="' . $row['tag_id'] . '" id="checkbox_' . $row['tag_id']. '" onclick="teachpress_change_label_color(' . $s . $row['tag_id'] . $s . ')"/> <label for="checkbox_' . $row['tag_id'] . '" title="Tag &laquo;' . $row['name'] . '&raquo; ' . __('Delete','teachpress') . '" id="tag_label_' . $row['tag_id'] . '">' . $row['name'] . '</label> | ';
    }
    echo '</p>';
    echo '<p><label for="add_tags"><b>' . __('New (separate by comma)','teachpress') . '</b></label></p> <p><input name="add_tags" id="add_tags" type="text" style="width:70%;"/></p>';
    echo '</div>';
    echo '<p class="submit inline-edit-save"><a accesskey="c" onclick="teachpress_showhide(' . $s . 'tp-inline-edit-row' . $s . ')" class="button-secondary cancel alignleft">' . __('Cancel') . '</a> <input type="submit" name="bulk_edit" id="bulk_edit" class="button button-primary alignright" value="' . __('Save') . '" accesskey="s"></p>';
    echo '</td>';
    echo '</tr>';
       ?>
       <script type="text/javascript" charset="utf-8">
       jQuery(document).ready(function($) {
           var availableTags = [
               <?php
               $sql = tp_tags::get_tags( array('group_by' => true) );
               foreach ($sql as $row) {
                   echo '"' . $row->name . '",';        
               } ?>
           ];
           function split( val ) {
               return val.split( /,\s*/ );
           }
           function extractLast( term ) {
               return split( term ).pop();
           }

           $( "#add_tags" )
               // don't navigate away from the field on tab when selecting an item
               .bind( "keydown", function( event ) {
                   if ( event.keyCode === $.ui.keyCode.TAB && $( this ).data( "autocomplete" ).menu.active ) {
                       event.preventDefault();
                   }
               })
               .autocomplete({
                   minLength: 0,
                   source: function( request, response ) {
                       // delegate back to autocomplete, but extract the last term
                       response( $.ui.autocomplete.filter(
                           availableTags, extractLast( request.term ) ) );
                   },
                   focus: function() {
                       // prevent value inserted on focus
                       return false;
                   },
                   select: function( event, ui ) {
                       var terms = split( this.value );
                       // remove the current input
                       terms.pop();
                       // add the selected item
                       terms.push( ui.item.value );
                       // add placeholder to get the comma-and-space at the end
                       terms.push( "" );
                       this.value = terms.join( ", " );
                       return false;
                   }
               });
       });
       </script>
       <?php
}

/**
 * Show publications main screen
 * @param int $user
 * @param array $array_variables
 * @since 4.3.0
 */
function tp_show_publications_page_main_screen($user, $array_variables) {
    ?>
    <form id="showlvs" name="form1" method="get" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
    <input type="hidden" name="page" id="page" value="<?php echo $array_variables['page']; ?>" />
    <input type="hidden" name="tag" id="tag" value="<?php echo $array_variables['tag_id']; ?>" />
    <?php

    // Delete publications - part 1
    if ( $array_variables['action'] == "delete" ) {
        echo '<div class="teachpress_message">
              <p class="teachpress_message_headline">' . __('Are you sure to delete the selected elements?','teachpress') . '</p>
              <p><input name="delete_ok" type="submit" class="button-primary" value="' . __('Delete','teachpress') . '"/>
              <a href="admin.php?page=publications.php&search=' . $array_variables['search'] . '&amp;limit=' . $array_variables['curr_page'] . '" class="button-secondary"> ' . __('Cancel','teachpress') . '</a></p>
              </div>';
    }
    
    $title = ($array_variables['page'] == 'publications.php' && $array_variables['search'] == '') ? __('All publications','teachpress') : __('Your publications','teachpress');
    
    $args = array('search' => $array_variables['search'],
                  'user' => ($array_variables['page'] == 'publications.php') ? '' : $user,
                  'tag' => $array_variables['tag_id'],
                  'year' => $array_variables['year'],
                  'limit' => $array_variables['entry_limit'] . ',' .  $array_variables['per_page'],
                  'type' => $array_variables['type'],
                  'order' => 'date DESC, title ASC'
                 );
    $test = tp_publications::get_publications($args, true);

    // Load tags
    $tags = tp_tags::get_tags( array('output_type' => ARRAY_A) );

    // Load bookmarks
    $bookmarks = tp_bookmarks::get_bookmarks( array('user'=> $user, 'output_type' => ARRAY_A) );
        
      ?>
      <h2><?php echo $title; ?> <a href="admin.php?page=teachpress/addpublications.php" class="add-new-h2"><?php _e('Create','teachpress'); ?></a></h2>
      <div id="searchbox" style="float:right; padding-bottom:5px;">
         <?php if ($array_variables['search'] != "") { 
            echo '<a href="admin.php?page=' . $array_variables['page'] . '&amp;filter=' . $array_variables['type'] . '&amp;tag=' . $array_variables['tag_id'] . '&amp;year=' . $array_variables['year'] . '" style="font-size:14px; font-weight:bold; text-decoration:none; padding-right:3px;" title="' . __('Cancel the search','teachpress') . '">X</a>';
         } ?>
         <input type="text" name="search" id="pub_search_field" value="<?php echo $array_variables['search']; ?>"/>
         <input type="submit" name="pub_search_button" id="pub_search_button" value="<?php _e('Search','teachpress'); ?>" class="button-secondary"/>
      </div>
      <div class="tablenav" style="padding-bottom:5px;">
          <div class="alignleft actions">
            <select name="action">
               <option value="0">- <?php _e('Bulk actions','teachpress'); ?> -</option>
               <option value="edit"><?php _e('Edit','teachpress'); ?></option>
               <option value="bibtex"><?php _e('Show as BibTeX entry','teachpress'); ?></option>
               <?php if ($array_variables['page'] === 'publications.php') {?>
               <option value="add_list"><?php _e('Add to your own list','teachpress'); ?></option>
               <option value="delete"><?php _e('Delete','teachpress'); ?></option>
               <?php } ?>
            </select>
            <input name="ok" id="doaction" value="<?php _e('OK','teachpress'); ?>" type="submit" class="button-secondary"/>
          </div>
          <div class="alignleft actions">
            <select name="filter">
               <option value="0">- <?php _e('All types','teachpress'); ?> -</option>
               <?php 
               $array_types = tp_publications::get_used_pubtypes( array(
                    'user' => ($array_variables['page'] == 'publications.php') ? '' : $user ) );
               foreach ( $array_types as $row ) {
                   $selected = ( $array_variables['type'] === $row['type'] ) ? 'selected="selected"' : '';
                   echo '<option value="' . $row['type'] . '" ' . $selected . '>' . tp_translate_pub_type($row['type'],'pl') . '</option>';
               }
               ?>
            </select>
            <select name="year">
                <option value="0">- <?php _e('All years','teachpress'); ?> -</option>
                <?php
                $array_years = tp_publications::get_years( array(
                    'order' => 'DESC', 
                    'user' => ($array_variables['page'] == 'publications.php') ? '' : $user) );
                foreach ( $array_years as $row ) {
                    $selected = ( $array_variables['year'] == $row->year ) ? 'selected="selected"' : '';
                    echo '<option value="' . $row->year . '" ' . $selected . '>' . $row->year . '</option>';
                }
                ?>
            </select>
            <select name="tag">
                <option value="0">- <?php _e('All tags','teachpress'); ?> -</option>
                <?php
                $array_tags = tp_tags::get_tags( array(
                    'user' => ($array_variables['page'] == 'publications.php') ? '' : $user, 
                    'group_by' => true, 
                    'order' => 'ASC') );
                foreach ( $array_tags as $row ) {
                    $selected = ( $array_variables['tag_id'] == $row->tag_id ) ? 'selected="selected"' : '';
                    echo '<option value="' . $row->tag_id . '" ' . $selected . '>' . $row->name . '</option>';
                }
                ?>
            </select>
            <input name="filter-ok" value="<?php _e('Limit selection','teachpress'); ?>" type="submit" class="button-secondary"/>
          </div>
      <?php
      // Page Menu
      $link = 'search=' . $array_variables['search'] . '&amp;filter=' . $array_variables['type'] . '&amp;tag=' . $array_variables['tag_id'];
      echo tp_admin_page_menu ($test, $array_variables['per_page'], $array_variables['curr_page'], $array_variables['entry_limit'], 'admin.php?page=' . $array_variables['page'] . '&amp;', $link); ?>
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
         // Bulk edit
         if ( $array_variables['action'] === 'edit' && $array_variables['checkbox'] !== '' ) {
             tp_show_publications_page_bulk_edit_screen($array_variables);
         }
         
         if ($test === 0) {
             echo '<tr><td colspan="7"><strong>' . __('Sorry, no entries matched your criteria.','teachpress') . '</strong></td></tr>';
         }
         
         else {
             $row = tp_publications::get_publications($args);
             $class_alternate = true;
             foreach ($row as $row) { 
                 $get_string = '&amp;search=' . $array_variables['search'] . '&amp;filter=' . $array_variables['type'] . '&amp;limit=' . $array_variables['curr_page'] . '&amp;site=' . $array_variables['page'] . '&amp;tag=' . $array_variables['tag_id'] . '&amp;year=' . $array_variables['year'];
                 if ( $class_alternate === true ) {
                     $tr_class = 'class="alternate"';
                     $class_alternate = false;
                 }
                 else {
                     $tr_class = '';
                     $class_alternate = true;
                 }
                 ?>
               <tr <?php echo $tr_class; ?>>
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
                  if ( $array_variables['page'] === 'publications.php' ) {
                     // Add to your own list icon
                     if ($test2 === false) {
                        echo '<a href="admin.php?page=' . $array_variables['page'] . '&amp;add_id='. $row->pub_id . $get_string . '" title="' . __('Add to your own list','teachpress') . '">+</a>';
                     }
                  }
                  else {
                     // Delete from your own list icon
                     echo '<a href="admin.php?page=' . $array_variables['page'] .'&amp;del_id='. $test2 . $get_string . '" title="' . __('Delete from your own list','teachpress') . '">&laquo;</a>';
                  } ?>
                  </td>
                  <?php
                  $checked = '';
                  if ( ( $array_variables['action'] === "delete" || $array_variables['action'] === "edit" ) && is_array($array_variables['checkbox']) ) { 
                     for( $k = 0; $k < count( $array_variables['checkbox'] ); $k++ ) { 
                        if ( $row->pub_id == $array_variables['checkbox'][$k] ) { $checked = 'checked="checked" '; } 
                     } 
                  }
                  echo '<th class="check-column"><input name="checkbox[]" class="tp_checkbox" type="checkbox" ' . $checked . ' value="' . $row->pub_id . '" /></th>';
                  echo '<td>';
                  echo '<a href="admin.php?page=teachpress/addpublications.php&amp;pub_ID=' . $row->pub_id . $get_string . '" class="teachpress_link" title="' . __('Click to edit','teachpress') . '"><strong>' . stripslashes($row->title) . '</strong></a>';
                  echo '<div class="tp_row_actions"><a href="admin.php?page=teachpress/addpublications.php&amp;pub_ID=' . $row->pub_id . $get_string . '" class="teachpress_link" title="' . __('Click to edit','teachpress') . '">' . __('Edit','teachpress') . '</a> | <a href="admin.php?page=' . $array_variables['page']  .'&amp;checkbox%5B%5D=' . $row->pub_id . '&amp;action=delete' . $get_string . '" style="color:red;" title="' . __('Delete','teachpress') . '">' . __('Delete','teachpress') . '</a></div>';
                  echo '</td>';
                  echo '<td>' . $row->pub_id . '</td>';
                  echo '<td>' . tp_translate_pub_type($row->type) . '</td>';
                  if ( $row->type === 'collection' || ( $row->author === '' && $row->editor !== '' ) ) {
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
                        if ($temp["tag_id"] == $array_variables['tag_id']) {
                           $tag_string .= '<a href="admin.php?page=' . $array_variables['page']  . '&amp;search=' . $array_variables['search'] . '&amp;filter=' . $array_variables['type'] . '&amp;limit=' . $array_variables['curr_page'] . '&amp;year=' . $array_variables['year'] . '" title="' . __('Delete tag as filter','teachpress') . '"><strong>' . stripslashes($temp["name"]) . '</strong></a>, ';
                        }
                        else {
                           $tag_string .= '<a href="admin.php?page=' . $array_variables['page']  . '&amp;search=' . $array_variables['search'] . '&amp;filter=' . $array_variables['type'] . '&amp;tag=' . $temp["tag_id"] . '&amp;year=' . $array_variables['year'] . '" title="' . __('Show all publications which have a relationship to this tag','teachpress') . '">' . stripslashes($temp["name"]) . '</a>, ';
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
      if ($test > $array_variables['per_page']) {
         echo tp_admin_page_menu ($test, $array_variables['per_page'], $array_variables['curr_page'], $array_variables['entry_limit'], 'admin.php?page=' . $array_variables['page'] . '&amp;', $link, 'bottom');
      } 
      else {
         if ($test === 1) {
            echo "$test " . __('entry','teachpress');
         }
         else {
            echo "$test " . __('entries','teachpress');
         }
      }
      ?>
      </div></div>
  
     </form>
    
<?php } 

?>