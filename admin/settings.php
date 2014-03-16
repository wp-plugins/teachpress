<?php
/**
 * teachPress settings menu: controller
 * @since 1.0.0
 */
function teachpress_admin_settings() {
    
    echo '<div class="wrap">';
    
    $option_semester = isset( $_POST['semester'] ) ? htmlspecialchars($_POST['semester']) : '';
    $option_rel_page_courses = isset( $_POST['rel_page_courses'] ) ? htmlspecialchars($_POST['rel_page_courses']) : '';
    $option_rel_page_publications = isset( $_POST['rel_page_publications'] ) ? htmlspecialchars($_POST['rel_page_publications']) : '';
    $option_stylesheet = isset( $_POST['stylesheet'] ) ? intval($_POST['stylesheet']) : '';
    $option_sign_out = isset( $_POST['sign_out'] ) ? intval($_POST['sign_out']) : '';
    $option_login = isset( $_POST['login'] ) ? htmlspecialchars($_POST['login']) : '';
    $option_userrole_publications = isset( $_POST['userrole_publications'] ) ? $_POST['userrole_publications'] : '';
    $option_userrole_courses = isset( $_POST['userrole_courses'] ) ? $_POST['userrole_courses'] : '';

    $site = 'options-general.php?page=teachpress/settings.php';
    $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';
    
    // update dababase
    if ( isset($_GET['up']) ) {
       tp_db_update();
       // Force a fill of the table teachpress_authors if needed
       if ( tp_update_db::check_table_authors() === true ) {
           $message = __('teachPress needs a second database update. This can take some time.','teachpress') . ' <a href="' . $site . '&amp;sync=1">' . __('Update','teachpress') . '</a>';
           get_tp_message($message, 'orange');
       }
    }
    // sync database
    if ( isset($_GET['sync']) ) {
        tp_db_sync();
    }
    // install database
    if ( isset($_GET['ins']) ) {
       tp_install();
    }
    
    if (isset( $_POST['einstellungen'] )) {

       if ($_POST['drop_tp'] == '1') {
           tp_uninstall();
       }
       else {
           tp_options::change_option('sem', $option_semester);
           tp_options::change_option('rel_page_courses', $option_rel_page_courses);
           tp_options::change_option('rel_page_publications', $option_rel_page_publications);
           tp_options::change_option('stylesheet', $option_stylesheet);
           tp_options::change_option('sign_out', $option_sign_out);
           tp_options::change_option('login', $option_login);
           tp_update_userrole($option_userrole_courses, 'use_teachpress_courses');
           tp_update_userrole($option_userrole_publications, 'use_teachpress');
       }
       get_tp_message( __('Settings are changed. Please note that access changes are visible, until you have reloaded this page a secont time.','teachpress') );
    }
    
    if ( isset( $_GET['delete'] ) ) {
        tp_options::delete_option($_GET['delete']);
    }
    
    // test if database is installed
    $test = get_tp_option('db-version');
    if ($test !== '') {
       $version = get_tp_version();
       if ($test !== $version) { 
           get_tp_message( __('A database update is necessary','teachpress') . '. <a href="options-general.php?page=teachpress/settings.php&up=1">' . __('Update to','teachpress') . ' ' . $version . '</a>.' );
       }
    }
    else {
        get_tp_message( '<a href="options-general.php?page=teachpress/settings.php&ins=1">' . __('Install database','teachpress') . '</a>' );
    }
    
    
    echo '<h2 style="padding-bottom:0px;">' . __('teachPress settings','teachpress') . '</h2>';
    
    // Site menu
    $set_menu_1 = ( $tab === "general" || $tab === "" ) ? "nav-tab nav-tab-active" : "nav-tab";
    $set_menu_2 = ( $tab === "courses" ) ? "nav-tab nav-tab-active" : "nav-tab";
    $set_menu_4 = ( $tab === "course_data" ) ? "nav-tab nav-tab-active" : "nav-tab";
    $set_menu_3 = ( $tab === "publications" ) ? "nav-tab nav-tab-active" : "nav-tab";
    
    echo '<h3 class="nav-tab-wrapper">'; 
    echo '<a href="' . $site . '&amp;tab=general" class="' . $set_menu_1 . '">' . __('General','teachpress') . '</a>';
    if ( !defined('TP_COURSE_SYSTEM') ) {
       echo '<a href="' . $site . '&amp;tab=courses" class="' . $set_menu_2 . '">' . __('Courses','teachpress') . ' 1</a>';
       echo '<a href="' . $site . '&amp;tab=course_data" class="' . $set_menu_4 . '">' . __('Courses','teachpress') . ' 2</a>';
    }
    if ( !defined('TP_PUBLICATION_SYSTEM') ) {	
       echo '<a href="' . $site . '&amp;tab=publications" class="' . $set_menu_3 . '">' . __('Publications','teachpress') . '</a>'; 
    }
    echo '</h3>';
  
    echo '<form id="form1" name="form1" method="post" action="' . $_SERVER['REQUEST_URI'] . '">';
    echo '<input name="page" type="hidden" value="teachpress/settings.php" />';
    echo '<input name="tab" type="hidden" value="<?php echo $tab; ?>" />';
    
    /* General */
    if ($tab == '' || $tab == 'general') {
        tp_admin_settings_general();
    }
    /* Courses */
    if ( $tab === 'courses' ) { 
        tp_admin_settings_courses1();
    }
    /* Course data */
    if ( $tab === 'course_data' ) {
        tp_admin_settings_courses2();
    }
    /* Publications */
    if ( $tab === 'publications' ) {
        tp_admin_settings_publications();
    }
    
    echo '</form>';
    echo '</div>';
}

/**
 * 
 * @global type $wp_roles
 * @global type $wp_roles
 */
function tp_admin_settings_general() {
    ?>
     <table class="form-table">
        <thead>
            <tr>
                <th width="160"><?php _e('teachPress version','teachpress'); ?></th>
                <td width="210"><?php echo get_tp_option('db-version'); ?></td>
                <td></td>
            </tr>
            <tr>
                <th><?php _e('Components','teachpress'); ?></th>
                <td style="vertical-align: top;">
                <?php
                $course_system = ( defined('TP_COURSE_SYSTEM') ) ? '<span style="color:#FF0000;">' . __('inactive','teachpress') . '</span>' : '<span style="color:#01DF01;">' . __('active','teachpress') . '</span>';
                echo 'Course system: ' . $course_system;
                echo '<br/>';
                $pub_system = ( defined('TP_PUBLICATION_SYSTEM') ) ? '<span style="color:#FF0000;">' . __('inactive','teachpress') . '</span>' : '<span style="color:#01DF01;">' . __('active','teachpress') . '</span>';
                echo 'Publication system: ' . $pub_system;
                ?>
                </td>
                <td>
                    <?php _e('You can deactivate parts of the plugin, if you copy the following in your wp-config.php','teachpress'); ?>:<br/>
                    <i>
                    // For deactivating the course system:<br/>
                    define ('TP_COURSE_SYSTEM','disable');<br/>
                    // For deactivating the publication system:<br/>
                    define ('TP_PUBLICATION_SYSTEM','disable');<br/>
                    </i>
                </td>
            </tr> 
            <tr>
                <th><?php _e('Related content','teachpress'); ?></th>
                <td>
                   <?php $value = get_tp_option('rel_page_courses'); ?>
                   <p><select name="rel_page_courses" id="rel_page_courses" title="<?php _e('for courses','teachpress');?>">
                   <option value="page" <?php if ($value == 'page') { echo 'selected="selected"'; } ?>><?php _e('Pages');?></option>
                   <option value="post" <?php if ($value == 'post') { echo 'selected="selected"'; } ?>><?php _e('Posts'); ?></option>
                   <?php
                   $args = array(
                     'public'   => true,
                     '_builtin' => false
                   ); 
                   $post_types = get_post_types($args,'objects'); 
                   foreach ($post_types as $post_type ) {
                       $current = ($post_type->name == $value) ? 'selected="selected"' : '';
                       echo '<option value="'. $post_type->name . '" ' . $current . '>'. $post_type->label. '</option>';
                   }
                   ?>
                    </select>
                    <label for="rel_page_courses"><?php _e('for courses','teachpress');?></label></p>
                    <p><select name="rel_page_publications" id="rel_page_publications" title="<?php _e('for publications','teachpress');?>">
                        <?php $value = get_tp_option('rel_page_publications'); ?>
                        <option value="page" <?php if ($value == 'page') { echo 'selected="selected"'; } ?>><?php _e('Pages');?></option>
                        <option value="post" <?php if ($value == 'post') { echo 'selected="selected"'; } ?>><?php _e('Posts'); ?></option>
                        <?php
                        $args = array(
                          'public'   => true,
                          '_builtin' => false
                        ); 
                        $post_types = get_post_types($args,'objects'); 
                        foreach ($post_types as $post_type ) {
                            $current = ($post_type->name == $value) ? 'selected="selected"' : '';
                            echo '<option value="'. $post_type->name . '" ' . $current . '>'. $post_type->label. '</option>';
                        }
                        ?>
                    </select> <label for="rel_page_publications"><?php _e('for publications','teachpress');?></label></p>
                </td>
                <td  style="vertical-align: top;"><?php _e('If you create a course or a publication you can define a link to related content. It is kind of a "more information link", which helps you to connect a course/publication with a page. If you want to use custom post types instead of pages, so you can set it here.','teachpress'); ?></td>
              </tr>
              <tr>
              	<th><label for="stylesheet"><?php _e('Frontend styles','teachpress'); ?></label></th>
                <td style="vertical-align: top;">
                     <select name="stylesheet" id="stylesheet" title="<?php _e('Frontend styles','teachpress'); ?>">
                <?php
                 $value = get_tp_option('stylesheet');
                 if ($value == '1') {
                       echo '<option value="1" selected="selected">' . __('teachpress_front.css','teachpress') . '</option>';
                       echo '<option value="0">' . __('your theme.css','teachpress') . '</option>';
                 }
                 else {
echo '<option value="1">' . __('teachpress_front.css','teachpress') . '</option>';
                       echo '<option value="0" selected="selected">' . __('your theme.css','teachpress') . '</option>';
                 } 
                 ?>
                    </select>
                </td>
                <td><?php _e('Select which style sheet you will use. teachpress_front.css is the teachPress default style. If you have created your own style in the default style sheet of your theme, you can activate this here.','teachpress'); ?></td>
              </tr>
              <tr>
              	<th><label for="userrole_publications"><?php _e('Backend access for publication system','teachpress'); ?></label></th>
                <td style="vertical-align: top;">
                    <select name="userrole_publications[]" id="userrole" multiple="multiple" style="height:120px;" title="<?php _e('Backend access for publication system','teachpress'); ?>">
                    <?php
                    global $wp_roles;
                    foreach ($wp_roles->role_names as $roledex => $rolename){
                       $role = $wp_roles->get_role($roledex);
                       $select = $role->has_cap('use_teachpress') ? 'selected="selected"' : '';
                       echo '<option value="'.$roledex.'" '.$select.'>'.$rolename.'</option>';
                    }
                    ?>
                    </select>
					
                </td>
                <td style="vertical-align: top;"><?php _e('Select which userrole your users must have to use the teachPress backend.','teachpress'); ?><br /><?php _e('use &lt;Ctrl&gt; key to select multiple roles','teachpress'); ?></td>
              </tr>
              <tr>
              	<th><label for="userrole_courses"><?php _e('Backend access for course system','teachpress'); ?></label></th>
                <td style="vertical-align: top;">
                    <select name="userrole_courses[]" id="userrole" multiple="multiple" style="height:120px;" title="<?php _e('Backend access for course system','teachpress'); ?>">
                    <?php
                    global $wp_roles;
                    foreach ($wp_roles->role_names as $roledex => $rolename){
                       $role = $wp_roles->get_role($roledex);
                       $select = $role->has_cap('use_teachpress_courses') ? 'selected="selected"' : '';
                       echo '<option value="'.$roledex.'" '.$select.'>'.$rolename.'</option>';
                    }
                    ?>
                    </select>
					
                </td>
                <td style="vertical-align: top;"><?php _e('Select which userrole your users must have to use the teachPress backend.','teachpress'); ?><br /><?php _e('use &lt;Ctrl&gt; key to select multiple roles','teachpress'); ?></td>
              </tr>
             </thead>
             </table>
             <h3><?php _e('Enrollment system','teachpress'); ?></h3>
             <table class="form-table">
             <thead>
               <tr>
                <th><label for="semester"><?php _e('Current term','teachpress'); ?></label></th>
                <td><select name="semester" id="semester" title="<?php _e('Current term','teachpress'); ?>">
                <?php
                $value = get_tp_option('sem');
                $sem = get_tp_options('semester');
                foreach ($sem as $sem) { 
                    $current = ($sem->value == $value) ? 'selected="selected"' : '';
                    echo '<option value="' . $sem->value . '" ' . $current . '>' . stripslashes($sem->value) . '</option>';
                } ?>    
                </select></td>
                <td><?php _e('Here you can change the current term. This value is used for the default settings for all menus.','teachpress'); ?></td>
              </tr>
              <tr>
              	<th width="160"><label for="login_mode"><?php _e('Mode','teachpress'); ?></label></th>
                <td width="210" style="vertical-align: top;">
                <select name="login" id="login_mode" title="<?php _e('Mode','teachpress'); ?>">
                  <?php
                  $value = get_tp_option('login');
                  if ($value == 'int') {
                    echo '<option value="std">' . __('Standard','teachpress') . '</option>';
                    echo '<option value="int" selected="selected">' . __('Integrated','teachpress') . '</option>';
                  }
                  else {
                    echo '<option value="std" selected="selected">' . __('Standard','teachpress') . '</option>';
                    echo '<option value="int">' . __('Integrated','teachpress') . '</option>';
                  } 
                  ?>
                </select>
                </td>
                <td><?php _e('Standard - teachPress has a separate registration. This is usefull if you have an auto login for WordPress or most of your users are registered in your blog, for example in a network.','teachpress'); ?><br /><?php _e('Integrated - teachPress deactivates the own registration and uses all available data from WordPress. This is usefull, if most of your users has not an acount in your blog.','teachpress'); ?></td>
              </tr>
              <tr>
              <th><label for="sign_out"><?php _e('Prevent sign out','teachpress'); ?></label></th>
              <td><select name="sign_out" id="sign_out" title="<?php _e('Prevent sign out','teachpress'); ?>">
              <?php
                  $value = get_tp_option('sign_out');
                  if ($value == '1') {
                    echo '<option value="1" selected="selected">' . __('yes','teachpress') . '</option>';
                    echo '<option value="0">' . __('no','teachpress') . '</option>';
                  }
                  else {
                    echo '<option value="1">' . __('yes','teachpress') . '</option>';
                    echo '<option value="0" selected="selected">' . __('no','teachpress') . '</option>';
                  } 
                  ?>
              </select></td>
              <td><?php _e('Prevent sign out for your users','teachpress'); ?></td>
              </tr>
             </thead> 
			</table>
            <h3><?php _e('Uninstalling','teachpress'); ?></h3> 
            <span style="margin: 0 15px 0 10px;"><?php _e('Remove teachPress from the database:','teachpress'); ?></span>
            <input type="radio" name="drop_tp" value="1" id="drop_tp_0" />
            <label for="drop_tp_0"><?php _e('yes','teachpress'); ?></label>
            <input type="radio" name="drop_tp" value="0" id="drop_tp_1" checked="checked" />
            <label for="drop_tp_1"><?php _e('no','teachpress'); ?></label>
           
            <p><input name="einstellungen" type="submit" id="teachpress_settings" value="<?php _e('Save'); ?>" class="button-primary" /></p>
    <?php
}

/**
 * teachPress settings menu: tab for courses (1)
 * @since 4.3.0
 */
function tp_admin_settings_courses1() {
    $new_term = isset( $_POST['new_term'] ) ? htmlspecialchars($_POST['new_term']) : ''; 
    $new_type = isset( $_POST['new_type'] ) ? htmlspecialchars($_POST['new_type']) : '';
    
    if (isset( $_POST['add_type'] ) && $new_type != __('Add type','teachpress')) {
        tp_options::add_option($new_type, $new_type, 'course_type');
    }
    if (isset( $_POST['add_term'] ) && $new_term != __('Add term','teachpress')) {
       tp_options::add_option($new_term, $new_term, 'semester');
    }
    
    echo '<div style="min-width:780px; width:100%;">';
    echo '<div style="width:48%; float:left; padding-right:2%;">';
    
    $args2 = array ( 
        'element_title' => __('Term','teachpress'),
        'count_title' => __('Number of courses','teachpress'),
        'delete_title' => __('Delete term','teachpress'),
        'add_title' => __('Add term','teachpress'),
        'tab' => 'courses'
        );
    get_tp_admin_course_option_box(__('Term','teachpress'), 'term', $args2);
    
    echo '</div>';
    echo '<div style="width:48%; float:left; padding-left:2%;">';
          
    $args3 = array ( 
        'element_title' => __('Type'),
        'count_title' => __('Number of courses','teachpress'),
        'delete_title' => __('Delete type','teachpress'),
        'add_title' => __('Add type','teachpress'),
        'tab' => 'coures'
        );
    get_tp_admin_course_option_box(__('Types of courses','teachpress'), 'type', $args3);
    
    echo '</div>';
    echo '</div>';
}

/**
 * 
 */
function tp_admin_settings_courses2() {
    if ( isset($_POST['add_field']) ) {
        $forbidden_names = array('system', 'teachpress_stud', 'course_type', 'semester');
        $field_name = isset( $_POST['field_name'] ) ? htmlspecialchars($_POST['field_name']) : '';
        $data['title'] = isset( $_POST['field_label'] ) ? htmlspecialchars($_POST['field_label']) : '';
        $data['type'] = isset( $_POST['field_type'] ) ? htmlspecialchars($_POST['field_type']) : '';
        $data['admin_visibility'] = isset( $_POST['admin_visibility'] ) ? 'true' : 'false';
        $data['required'] = isset( $_POST['is_required'] ) ? 'true' : 'false';
        $data['unique'] = isset( $_POST['is_unique'] ) ? 'true' : 'false';
        if ( !in_array($field_name, $forbidden_names) ) {
            tp_db_helpers::register_column('teachpress_stud', $field_name, $data);
            if ( $data['type'] == 'TEXTAREA' || $data['type'] == 'SELECT' ) {
                $data['type'] = 'TEXT';
            }
            tp_db_helpers::add_column(TEACHPRESS_STUD, $field_name, $data['type']);
        }
        else {
            get_tp_message(  _('Warning: This field name is not possible.','teachpress') );
        }
    }
    $select_fields = array();
        ?>
        <div style="min-width:780px; width:100%;">
        <div style="width:48%; float:left; padding-right:2%;">
        <h3>Student data fields</h3>
        <table class="widefat">
            <thead>
                <tr>
                    <th></th>
                    <th>Field name</th>
                    <th>Properties</th>
                </tr>
            </thead>
            <?php
            // Default fields
            $default_fields = array('wp_id', 'firstname', 'lastname', 'userlogin', 'email');
            for ( $i = 0; $i < 5; $i++ ) {
                echo '<tr>';
                echo '<td></td>';
                echo '<td>' . $default_fields[$i]. '</td>';
                echo '<td>' . __('Default field. Not editable.','teachpress') . '</td>';
                echo '</tr>';
            }
            $fields = get_tp_options('teachpress_stud','`setting_id` ASC');
            foreach ($fields as $field) {
                $data = tp_db_helpers::extract_column_data($field->value);
                if ( $data['type'] === 'SELECT' ) {
                    array_push($select_fields, $field->variable);
                    // search for select options and add it
                    if ( isset( $_POST['add_' . $field->variable] ) && $_POST['new_' . $field->variable] != __('Add element','teachpress') ) {
                        tp_options::add_option($_POST['new_' . $field->variable], $_POST['new_' . $field->variable], $field->variable);
                    }
                }
                echo '<tr>
                    <td><a class="teachpress_delete" href="options-general.php?page=teachpress/settings.php&amp;delete=' . $field->setting_id . '&amp;tab=course_data">X</a></td>
                    <td>' . $field->variable . '</td>
                    <td>Label: <b>' . $data['title'] . '</b><br/> 
                        Type: <b>' . $data['type'] . '</b><br/>
                        Admin-visibility: <b>' . $data['admin_visibility'] . '</b><br/>
                        Unique: <b>' . $data['unique'] . '</b><br/>
                        Required: <b>' . $data['required'] . '</b></td>
                    </tr>';
            }
            ?>
            <tr>
                <td></td>
                <td colspan="2">
                    <h4>Add new field</h4>
                    <?php
                    echo '<p><input name="field_name" type="text" id="field_name" size="30" value="' . __('Name') . '" onblur="if(this.value==' . "''" .') this.value='. "'" . __('Name') . "'" . ';" onfocus="if(this.value=='. "'" . __('Name') . "'" . ') this.value=' . "''" . ';"/></p>';
                    echo '<p><input name="field_label" type="text" id="field_name" size="30" value="' . __('Label') . '" onblur="if(this.value==' . "''" .') this.value='. "'" . __('Label') . "'" . ';" onfocus="if(this.value=='. "'" . __('Label') . "'" . ') this.value=' . "''" . ';"/></p>';
                    ?>
                    <p><label for="field_type">Field type:</label> <select name="field_type" id="field_type">
                        <option value="TEXT">TEXT</option>
                        <option value="TEXTAREA">TEXTAREA</option>
                        <option value="INT">INT</option>
                        <option value="DATE">DATE</option>
                        <option value="SELECT">SELECT</option>
                    </select></p>
                    <p><input type="checkbox" name="admin_visibility" id="admin_visibility" value="true"/> <label for="admin_visibility"><?php _e('Show field in admin interfaces','teachpress'); ?></label></p>
                    <p><input type="checkbox" name="is_unique" id="is_unique" value="true"/> <label for="is_unique"><?php _e('Allow only unique values (only for the types INT and TEXT).','teachpress'); ?></label></p>
                    <p><input type="checkbox" name="is_required" id="is_required" value="true"/> <label for="is_required"><?php _e('Select, if you want a required field.','teachpress'); ?></label></p>
                    <p><input type="submit" name="add_field" class="button-secondary" value="<?php _e('Create','teachpress'); ?>"/></p>
                </td>
            </tr>
        </table>
        </div>
        <div style="width:48%; float:left; padding-left:2%;">
        <?php
        foreach ( $select_fields as $elem ) {
            $args1 = array ( 
                 'element_title' => __('Name','teachpress'),
                 'count_title' => __('Number of students','teachpress'),
                 'delete_title' => __('Delete elemtent','teachpress'),
                 'add_title' => __('Add element','teachpress'),
                 'tab' => 'course_data'
                 );
             get_tp_admin_course_option_box($elem, $elem, $args1);
        }
        ?>
        </div> <?php
}

/**
 * teachPress settings menu: tab for publications
 * @since 4.3.0
 */
function tp_admin_settings_publications() {
    if ( isset($_POST['save_pub']) ) {
        $checkbox_import_overwrite = isset( $_POST['import_overwrite'] ) ? 1 : '';
        $checkbox_rel_content_auto = isset( $_POST['rel_content_auto'] ) ? 1 : '';
        tp_options::change_option('import_overwrite', $checkbox_import_overwrite, 'checkbox');
        tp_options::change_option('rel_content_auto', $checkbox_rel_content_auto, 'checkbox');
        tp_options::change_option('rel_content_template', $_POST['rel_content_template']);
        tp_options::change_option('rel_content_category', $_POST['rel_content_category']);
        get_tp_message(__('Saved'));
    }
    ?>
    <table class="form-table">
    	<thead>
            <tr>
                <th width="160"><?php _e('Overwrite publications','teachpress'); ?></th>
                <td width="510"><?php echo get_tp_admin_checkbox('import_overwrite', __('Allow optional overwriting for publication import','teachpress'), get_tp_option('import_overwrite')); ?> <b>(EXPERIMENTAL)</b></td>
                <td></td>
            </tr>
            <tr>
                <th><?php _e('Automatic related content','teachpress'); ?></th>
                <td><?php echo get_tp_admin_checkbox('rel_content_auto', __('Create an automatic related content with every new publication','teachpress'), get_tp_option('rel_content_auto')); ?></td>
                <td></td>
            </tr>
            <tr>
                <th><?php _e('Template for related content','teachpress'); ?></th>
                <td><textarea name="rel_content_template" id="rel_content_template" style="width:100%;" rows="5"><?php echo get_tp_option('rel_content_template'); ?></textarea></td>
                <td></td>
            </tr>
            <tr>
                <th><?php _e('Default category for related content','teachpress'); ?></th>
                <td>
                    <?php 
                    wp_dropdown_categories(array('hide_empty' => 0, 'name' => 'rel_content_category', 'orderby' => 'name', 'selected' => get_tp_option('rel_content_category'), 'hierarchical' => true, 'show_option_none' => __('None'))); 
                    ?>
                    <em><?php _e('Used if the related content type for publicaitons is set on "Posts"','teachpress'); ?></em>
                </td>
            </tr>
    	<tr>
          <th><?php _e('RSS feed addresses','teachpress'); ?></th>
          <td><p><em><?php _e('For all publications:','teachpress'); ?></em><br />
            	<strong><?php echo plugins_url() . '/teachpress/feed.php'; ?></strong> &raquo; <a href="<?php echo plugins_url() . '/teachpress/feed.php'; ?>" target="_blank"><?php _e('Show','teachpress'); ?></a></p>
            	<p><em><?php _e('Example for publications of a single user (id = WordPress user-ID):','teachpress'); ?></em><br />
            	<strong><?php echo plugins_url() . '/teachpress/feed.php?id=1'; ?></strong> &raquo; <a href="<?php echo plugins_url() . '/teachpress/feed.php?id=1'; ?>" target="_blank"><?php _e('Show','teachpress'); ?></a></p>
                <p><em><?php _e('Example for publications of a single tag (tag = tag-id):','teachpress'); ?></em><br />
            	<strong><?php echo plugins_url() . '/teachpress/feed.php?tag=1'; ?></strong> &raquo; <a href="<?php echo plugins_url() . '/teachpress/feed.php?tag=1'; ?>" target="_blank"><?php _e('Show','teachpress'); ?></a></p>
            </td>
            <td></td>
        </tr>
        </thead>
    </table>
    <input type="submit" class="button-primary" name="save_pub" value="<?php _e('Save'); ?>"/>
    <?php
}

?>