<?php 
/**
 * This file contains all functions for displaying the add_students page in admin menu
 * @package teachpress/admin
 */

/**
 * Form for manual edits in the enrollment system
 * @since 2.0.0
*/ 
function teachpress_students_new_page() { 

    global $wpdb;

    $fields = get_tp_options('teachpress_stud','`setting_id` ASC', ARRAY_A);

    $wp_id = isset($_POST['wp_id']) ? intval($_POST['wp_id']) : '';
    $data['userlogin'] = isset( $_POST['birthday'] ) ? htmlspecialchars($_POST['userlogin']) : '';
    $data['email'] = isset( $_POST['email'] ) ? htmlspecialchars($_POST['email']) : '';

    // actions
    if (isset( $_POST['insert'] ) && $wp_id != __('WordPress User-ID','teachpress') && $wp_id != '') {
        tp_enrollments::add_student($wp_id, $data['userlogin'], $data['email'], $fields, filter_input_array(INPUT_POST, $_POST));
    }
    ?>
    <div class="wrap" >
        <p><a href="admin.php?page=teachpress/students.php" class="button-secondary"><?php _e('Back','teachpress'); ?></a></p>
    <h2><?php _e('Add student','teachpress'); ?></h2>

    <p style="padding:0px; margin:0px;">&nbsp;</p>
    <form id="new_student" name="new_student" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
    <table class="form-table">
        <thead>
          <tr>
            <td><label for="wp_id"><b><?php _e('WordPress User-ID','teachpress'); ?></b></label></td>
            <td style="text-align:left;">
            <?php 
              echo '<select name="wp_id" id="wp_id">';
              echo '<option value="n">' . __('Select user','teachpress') . '</option>';
              $sql = "SELECT u.ID, s.wp_id, u.user_login FROM " . $wpdb->users . " u
                      LEFT JOIN " . TEACHPRESS_STUD . " s ON u.ID = s.wp_id";	
              $row = $wpdb->get_results($sql);
              foreach ($row as $row) {
                 if ($row->ID != $row->wp_id) {
                    echo '<option value="' . $row->ID . '">' . $row->user_login . '</option>';
                 }
              }
              echo '</select>';
              ?> <?php _e('The Menu shows all your blog users who has no teachPress account','teachpress'); ?>
            </td>
      	  </tr>
          <tr>
            <td><label for="firstname"><b><?php _e('First name','teachpress'); ?></b></label></td>
            <td><input name="firstname" type="text" id="firstname" size="40" /></td>
          </tr>
          <tr>
            <td><label for="lastname"><b><?php _e('Last name','teachpress'); ?></b></label></td>
            <td><input name="lastname" type="text" id="lastname" size="40" /></td>
          </tr>
          <tr>
            <td><label for="userlogin"><b><?php _e('User account','teachpress'); ?></b></label></td>
            <td style="text-align:left;"><input type="text" name="userlogin" id="userlogin" /></td>
          </tr>
          <tr>
            <td><label for="email"><b><?php _e('E-Mail'); ?></b></label></td>
            <td><input name="email" type="text" id="email" size="50" /></td>
          </tr>
          <?php
          // Show custom fields
            foreach ($fields as $row) {
                $data = tp_db_helpers::extract_column_data($row['value']);
                $value = '';
                if ( $data['type'] === 'SELECT' ) {
                    echo tp_enrollments::get_form_select_field($row['variable'], $data['title'], $value);
                }
                elseif ( $data['type'] === 'TEXTAREA' ) {
                    echo tp_enrollments::get_form_textarea_field($row['variable'], $data['title'], $value);
                }
                elseif ( $data['type'] === 'DATE' ) {
                    echo tp_enrollments::get_form_date_field($row['variable'], $data['title'], $value);
                }
                elseif ( $data['type'] === 'INT' ) {
                    echo tp_enrollments::get_form_int_field($row['variable'], $data['title'], $value);
                }
                else {
                    echo tp_enrollments::get_form_text_field($row['variable'], $data['title'], $value);
                }
            }
            ?>
         </thead>
        </table>
    <p>
      <input name="insert" type="submit" id="std_einschreiben" onclick="teachpress_validateForm('firstname','','R','lastname','','R','userlogin','','R','email','','RisEmail');return document.teachpress_returnValue" value="<?php _e('Create','teachpress'); ?>" class="button-primary"/>
      <input name="reset" type="reset" id="reset" value="Reset" class="button-secondary"/>
    </p>
</form>
</div>
<?php } ?>