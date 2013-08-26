<?php
/*
 * Setting page
*/ 
function teachpress_admin_settings() {

    global $wpdb;
    global $teachpress_stud; 
     
    $checkbox_rel_content_auto = isset( $_POST['rel_content_auto'] ) ? 1 : '';
    $checkbox_import_overwrite = isset( $_POST['import_overwrite'] ) ? 1 : '';
    $checkbox_matriculation_number = isset( $_POST['matriculation_number_field'] ) ? 1 : '';
    $checkbox_course_of_studies = isset( $_POST['course_of_studies_field'] ) ? 1 : '';
    $checkbox_semesternumber = isset( $_POST['semesternumber_field'] ) ? 1 : '';
    $checkbox_birthday = isset( $_POST['birthday_field'] ) ? 1 : '';
     
    $option_semester = isset( $_POST['semester'] ) ? htmlspecialchars($_POST['semester']) : '';
    $option_rel_page_courses = isset( $_POST['rel_page_courses'] ) ? htmlspecialchars($_POST['rel_page_courses']) : '';
    $option_rel_page_publications = isset( $_POST['rel_page_publications'] ) ? htmlspecialchars($_POST['rel_page_publications']) : '';
    $option_stylesheet = isset( $_POST['stylesheet'] ) ? intval($_POST['stylesheet']) : '';
    $option_sign_out = isset( $_POST['sign_out'] ) ? intval($_POST['sign_out']) : '';
    $option_login = isset( $_POST['login'] ) ? htmlspecialchars($_POST['login']) : '';
    $option_userrole = isset( $_POST['userrole'] ) ? $_POST['userrole'] : '';

    $name = isset( $_POST['name'] ) ? htmlspecialchars($_POST['name']) : '';
    $typ = isset( $_POST['typ'] ) ? htmlspecialchars($_POST['typ']) : '';
    $newsem = isset( $_POST['newsem'] ) ? htmlspecialchars($_POST['newsem']) : '';
    $site = 'options-general.php?page=teachpress/settings.php';
    $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';
    
    // event handler
    if ( isset($_GET['up']) ) {
       tp_db_update();
    }
    if ( isset($_GET['ins']) ) {
       tp_install();
    }
    if (isset( $_POST['einstellungen'] )) {

       if ($_POST['drop_tp'] == '1') {
           tp_uninstall();
       }
       else {
           tp_change_option('sem', $option_semester);
           tp_change_option('rel_page_courses', $option_rel_page_courses);
           tp_change_option('rel_page_publications', $option_rel_page_publications);
           tp_change_option('stylesheet', $option_stylesheet);
           tp_change_option('sign_out', $option_sign_out);
           tp_change_option('regnum', $checkbox_matriculation_number, 'checkbox');
           tp_change_option('studies', $checkbox_course_of_studies, 'checkbox');
           tp_change_option('termnumber', $checkbox_semesternumber, 'checkbox');
           tp_change_option('birthday', $checkbox_birthday, 'checkbox');
           tp_change_option('login', $option_login);
           tp_update_userrole($option_userrole);
       }
       get_tp_message( __('Settings are changed. Please note that access changes are visible, until you have reloaded this page a secont time.','teachpress') );
    }
    if ( isset($_POST['save_pub']) ) {
        tp_change_option('import_overwrite', $checkbox_import_overwrite, 'checkbox');
        tp_change_option('rel_content_auto', $checkbox_rel_content_auto, 'checkbox');
        tp_change_option('rel_content_template', $_POST['rel_content_template']);
        tp_change_option('rel_content_category', $_POST['rel_content_category']);
        get_tp_message(__('Saved'));
    }
    if (isset( $_POST['addstud'] ) && $name != __('Add course of studies','teachpress')) {
       tp_add_option($name, 'course_of_studies');
    }
    if (isset( $_POST['addtyp'] ) && $typ != __('Add type','teachpress')) {
       tp_add_option($typ, 'course_type');
    }
    if (isset( $_POST['addsem'] ) && $newsem != __('Add term','teachpress')) {
       tp_add_option($newsem, 'semester');
    }
    if ( isset( $_GET['delete'] ) ) {
       tp_delete_option($_GET['delete']);
    }?>
<div class="wrap">
    <h2 style="padding-bottom:0px;"><?php _e('teachPress settings','teachpress'); ?></h2>
    <?php
     // Site menu
     $set_menu_1 = ( $tab === "general" || $tab === "" ) ? "nav-tab nav-tab-active" : "nav-tab";
     $set_menu_2 = ( $tab === "courses" ) ? "nav-tab nav-tab-active" : "nav-tab";
     $set_menu_3 = ( $tab === "publications" ) ? "nav-tab nav-tab-active" : "nav-tab";
    ?>
    <h3 class="nav-tab-wrapper"><?php 
    echo '<a href="' . $site . '&amp;tab=general" class="' . $set_menu_1 . '" title="' . __('General','teachpress') . '" >' . __('General','teachpress') . '</a>';
    if ( !defined('TP_COURSE_SYSTEM') ) {
       echo '<a href="' . $site . '&amp;tab=courses" class="' . $set_menu_2 . '" title="' . __('Courses','teachpress') . '">' . __('Courses','teachpress') . '</a>';
    }
    if ( !defined('TP_PUBLICATION_SYSTEM') ) {	
       echo '<a href="' . $site . '&amp;tab=publications" class="' . $set_menu_3 . '" title="' . __('Publications','teachpress') . '">' . __('Publications','teachpress') . '</a>'; 
    }
    ?></h3>
  
    <form id="form1" name="form1" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
    <input name="page" type="hidden" value="teachpress/settings.php" />
    <input name="tab" type="hidden" value="<?php echo $tab; ?>" />
    <?php
     /***********/
     /* General */
     /***********/
     if ($tab == '' || $tab == 'general') {?>
     <table class="form-table">
        <thead>
            <tr>
                <th width="160"><?php _e('teachPress version','teachpress'); ?></th>
                <td width="210"><?php 
                // test if database is installed
                $test = get_tp_option('db-version');
                if ($test != '') {
                   // test if the database needs an update
                   $version = get_tp_version();
                   if ($test == $version) { 
                      echo $version . ' <span style="color:#01DF01; font-weight:bold;">&radic;</span>';
                   } 
                   else {
                      echo $test . ' <span style="color:#FF0000; font-weight:bold;">X</span> <a href="options-general.php?page=teachpress/settings.php&up=1"><strong>' . __('Update to','teachpress') . ' ' . $version . '</strong></a>';
                   }
                }
                else {
                   if ( $wpdb->query("SHOW COLUMNS FROM $teachpress_stud LIKE 'wp_id'") != 0 ) {
                      echo '<a href="options-general.php?page=teachpress/settings.php&up=1"><strong>' . __('Update','teachpress') . '</strong></a>';
                   }
                   else {
                      echo '<a href="options-general.php?page=teachpress/settings.php&ins=1"><strong>' . __('install','teachpress') . '</strong></a>';
                   }
                } ?>
                </td>
                <td><?php _e('Shows the teachPress database version and available database updates','teachpress'); ?></td>
            </tr>
            <tr>
                <th><?php _e('Components','teachpress'); ?></th>
                <td style="vertical-align: top;">
                <?php
                $course_system = ( defined('TP_COURSE_SYSTEM') ) ? '<span style="color:#FF0000;">' . __('inactive','teachpress') . '</span>' : '<span style="color:#01DF01;">' . __('active','teachpress') . '</span>';
                echo 'Course system: ' . $course_system;
                echo '<br/>';
                $pub_system = ( defined('TP_PUBLICATION_SYSTEM') ) ? '<span style="color:#FF0000;">' . __('inactive','teachPress') . '</span>' : '<span style="color:#01DF01;">' . __('active','teachpress') . '</span>';
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
              	<th><label for="userrole"><?php _e('Backend access for','teachpress'); ?></label></th>
                <td style="vertical-align: top;">
                    <select name="userrole[]" id="userrole" multiple="multiple" style="height:80px;" title="<?php _e('Backend access for','teachpress'); ?>">
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
              <tr>
              	<th><?php _e('User data fields','teachpress'); ?></th>
                <td>
                 <?php
                 echo get_tp_admin_checkbox('matriculation_number_field', __('Matr. number','teachpress'), get_tp_option('regnum'));
                 echo '<br />';
                 echo get_tp_admin_checkbox('firstname_field', __('First name','teachpress'), '1', true);
                 echo '<br />';
                 echo get_tp_admin_checkbox('lastname_field', __('Last name','teachpress'), '1', true);
                 echo '<br />';
                 echo get_tp_admin_checkbox('course_of_studies_field', __('Course of studies','teachpress'), get_tp_option('studies'));
                 echo '<br />';
                 echo get_tp_admin_checkbox('semesternumber_field', __('Number of terms','teachpress'), get_tp_option('termnumber'));
                 echo '<br />';
                 echo get_tp_admin_checkbox('userid_field', __('User account','teachpress'), '1', true);
                 echo '<br />';
                 echo get_tp_admin_checkbox('birthday_field', __('Date of birth','teachpress'), get_tp_option('birthday'));
                 echo '<br />';
                 echo get_tp_admin_checkbox('email_field', __('E-Mail'), '1', true);
                 echo '<br />';
                 ?>
                </td>
                <td style="vertical-align: top;"><?php _e('Define which fields for the registration form you will use. Some are required.','teachpress'); ?></td>
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
	
    /***********/
    /* Courses */
    /***********/
        
    if ( $tab === 'courses' ) { ?>
        <div style="min-width:780px; width:100%;">
        <div style="width:48%; float:left; padding-right:2%;">
        <?php
          $args1 = array ( 
              'element_title' => __('Name','teachpress'),
              'count_title' => __('Number of students','teachpress'),
              'delete_title' => __('Delete course of studies','teachpress'),
              'add_title' => __('Add course of studies','teachpress')
              );
          get_tp_admin_course_option_box(__('Courses of studies','teachpress'), 'studies', $args1);
          ?>
        </div>
        <div style="width:48%; float:left; padding-left:2%;">
          <?php
          $args2 = array ( 
              'element_title' => __('Term','teachpress'),
              'count_title' => __('Number of courses','teachpress'),
              'delete_title' => __('Delete term','teachpress'),
              'add_title' => __('Add term','teachpress')
              );
          get_tp_admin_course_option_box(__('Term','teachpress'), 'term', $args2);
          ?>

          <?php
          $args3 = array ( 
              'element_title' => __('Type'),
              'count_title' => __('Number of courses','teachpress'),
              'delete_title' => __('Delete type','teachpress'),
              'add_title' => __('Add type','teachpress')
              );
          get_tp_admin_course_option_box(__('Types of courses','teachpress'), 'type', $args3);
          ?>
       </div>    
       </div>       
    <?php
    }

     /****************/
     /* Publications */
     /****************/
     if ( $tab === 'publications' ) {?>
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
    </form>
</div>
<?php } ?>