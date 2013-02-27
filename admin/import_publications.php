<?php 
/*
 * Import BibTeX
*/ 
function teachpress_import_page() {
    $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : '';
    if ( ( isset($_POST['tp_submit']) && isset ($_POST['bibtex_area']) ) || isset($_POST['tp_bookmark']) ) {
        // WordPress User informations
        global $current_user;
        get_currentuserinfo();
        if ( isset($_POST['tp_submit']) && isset ($_POST['bibtex_area']) ) {
            // import
            $bibtex = $_POST['bibtex_area']; // The input will be checked by the function tp_bibtex::import_bibtex
            $settings = array(
                'keyword_separator' => htmlspecialchars($_POST['keyword_option']),
                'author_format' => htmlspecialchars($_POST['author_format'])
            );
            $entries = tp_bibtex::import_bibtex($bibtex, $settings);
        }
        else {
            $entries = get_tp_publications( array( 'include' => htmlspecialchars($_POST['tp_entries'] ), 'output_type' => ARRAY_A ) );
        }
        // add bookmarks
        if ( isset( $_POST['tp_bookmark'] ) && isset($_POST['checkbox']) ) {
            $max = count($_POST['checkbox']);
            $add = $_POST['checkbox'];
            for ( $i = 0; $i < $max; $i++ ) {
                tp_add_bookmark( intval($add[$i]), $current_user->ID );
            }
            get_tp_message( __('Publications added to your list.','teachpress') );
        }
        echo '<div class="wrap">';
        echo '<p><a href="admin.php?page=teachpress/import.php" class="button-secondary">&larr; ' . __('Back','teachpress') . '</a></p>';
        echo '<h2>' . __('Imported Publications','teachpress') . '</h2>';
        echo '<form id="import_publications" name="import_publications" method="post" action="' . $_SERVER['REQUEST_URI'] . '">';
        echo '<p><input type="submit" class="button-primary" name="tp_bookmark" value="' . __('Add to your own list','teachpress') . '"/></p>';
        echo '<table class="widefat">';
        echo '<thead>';
        echo '<tr>';
        echo '<th class="check-column"><input name="tp_check_all" id="tp_check_all" type="checkbox" value="" onclick="' . "teachpress_checkboxes('checkbox','tp_check_all');" . '" /></th>';
        echo '<th>' . __('Title','teachpress') . '</th>';
        echo '<th>' . __('ID') . '</th>';
        echo '<th>' . __('Type') . '</th>';
        echo '<th>' . __('Author(s)','teachpress') . '</th>';
        echo '<th>' . __('Year','teachpress') . '</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        $array_id = '';
        foreach ( $entries as $entry ) {
            $value = ( isset($_POST['tp_submit']) && isset ($_POST['bibtex_area']) ) ? intval($entry['entry_id']) : intval($entry['pub_id']);
            echo '<tr>';
            if ( isset( $_POST['tp_bookmark'] ) && tp_check_bookmark($value, $current_user->ID) === true ) {
                echo '<th></th>';
            }
            else {
                echo '<th class="check-column"><input name="checkbox[]" class="tp_checkbox" type="checkbox" value="' . $value . '"/></th>';
            }
            echo '<td><a href="admin.php?page=teachpress/addpublications.php&amp;pub_ID=' . $value . '" class="teachpress_link" title="' . __('Click to edit','teachpress') . '" target="_blank">' . $entry['title'] . '</a></td>';
            echo '<td>' . $value . '</td>';
            echo '<td>' . tp_translate_pub_type( $entry['type'] ) . '</td>';
            echo '<td>' . $entry['author'] . '</td>';
            echo '<td>' . $entry['year'] . '</td>';
            echo '</tr>';
            $array_id .= $value . ',';
        }
        echo '</tbody>';
        echo '</table>';
        echo '<input type="hidden" name="tp_entries" value="' . substr($array_id, 0, -1) . '"/>';
        echo '</form>';
        echo '</div>';
    }
    else {
        $set_menu_1 = ( $tab == "import" || $tab == "" ) ? "nav-tab nav-tab-active" : "nav-tab";
        $set_menu_2 = $tab == "export" ? "nav-tab nav-tab-active" : "nav-tab";
	?>
	<div class="wrap">
	<h2><?php _e('Publications','teachpress'); ?></h2>
        <h3 class="nav-tab-wrapper"><?php echo '<a href="admin.php?page=teachpress/import.php&amp;tab=import" class="' . $set_menu_1 .'" title="' . __('Import') . '" >' . __('Import') . '</a> <a href="admin.php?page=teachpress/import.php&amp;tab=export" class="' . $set_menu_2 . '" title="' . __('Export') . '">' . __('Export') . '</a>'; ?></h3>
        <?php if ($tab == '' || $tab == 'import') { ?>
        <p><?php _e("Copy your BibTeX entries in the textarea. Restrictions: teachPress can't convert not numeric month and day attributes.","teachpress"); ?></p>
	<form id="tp_import" name="tp_import" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
	<input type="hidden" name="page" value="teachpress/import.php"/>
        <table class="form-table">
             <tr>
                  <th style="width: 100px;"><label for="bibtex_area"><?php _e('Import Area','teachpress'); ?></label></th>
                  <td><textarea name="bibtex_area" id="bibtex_area" rows="20" style="width:90%;" title="<?php _e('Insert your BibTeX entries here','teachpress'); ?>"></textarea></td>
             </tr>
        </table>
        <p><a onclick="teachpress_showhide('import_options')" style="cursor: pointer;"><strong>+ <?php _e('Options','teachpress'); ?></strong></a></p>
        <div id="import_options" style="display:none;">
        <table class="form-table">
            <tr>
                <th style="width:150px;"><label for="author_format_0"><?php _e('Author/Editor Format','teachpress'); ?></label></th>
                <td>
                      <label>
                        <input type="radio" name="author_format" value="1" id="author_format_0" checked="checked" />
                        Firstname1 Lastname1 and Firstname2 Lastname2 and ...</label>
                      <br />
                      <label>
                        <input type="radio" name="author_format" value="2" id="author_format_1" />
                        Lastname1, Firstname1 and Lastname2, Firstname2 and ...</label>
                      <br />
                  </td>
          </tr>
            <tr>
                <th style="width:150px;"><label for="keyword_option"><?php _e('Keyword Separator','teachpress'); ?></label></th>
                <td><input type="input" name="keyword_option" id="keyword_option" title="<?php _e('Keyword Separator','teachpress'); ?>" value="," size="3"/></td>
            </tr>
        </table>
        </div>     
	<p><input name="tp_submit" type="submit" class="button-primary" value="<?php _e('Import'); ?>"/></p>
	</form>
	
	<?php
        }
        
        if ($tab == 'export') {
        ?>
        <form id="tp_export" name="tp_export" action="<?php echo WP_PLUGIN_URL; ?>/teachpress/export.php?type=pub" method="post">
        <table class="form-table">
             <tr>
                  <th style="width: 150px;"><label for="tp_user"><?php _e('Publications by user','teachpress'); ?></label></th>
                  <td>
                       <select name="tp_user" id="tp_user">
                            <option value="all"><?php _e('All','teachpress'); ?></option>
                            <?php
                            $row = get_tp_publication_user();
                            foreach($row as $row) {
                                 $user_info = get_userdata($row->user);
                                 if ( $user_info != false ) { 
                                      echo '<option value="' . $user_info->ID . '">' . $user_info->display_name . '</option>';
                                 }
                            }
                            ?>
                       </select>
                  </td>
             </tr>
             <tr>
                  <th style="width: 150px;"><label for="tp_format"><?php _e('Format'); ?></label></th>
                  <td>
                       <select name="tp_format" id="tp_format">
                            <option value="bibtex">BibTeX</option>
                            <option value="rss">RSS (2.0)</option>
                            <option value="rtf">RTF</option>
                       </select>
                  </td>
             </tr>
        </table>
        <p><input name="tp_submit_2" type="submit" class="button-primary" value="<?php _e('Export'); ?>"/></p>
        </form>
        <?php
        }
        ?>
        </div>
        <?php
        
     }
} ?>