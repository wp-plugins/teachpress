<?php 
/* 
 * Form for manual edits in the enrollment system
 * 1. Adding new students manually:
 * @param int wp_id                 --> [$_POST] WordPress user-ID
 * @param int matriculation_number  --> [$_POST] Registration number
 * @param string firstname          --> [$_POST] First name
 * @param string lastname           --> [$_POST] Last name
 * @param string course_of_studies  --> [$_POST] Course of studies
 * @param int semesternumber        --> [$_POST] Number of termns
 * @param string uzrkurz            --> [$_POST] User name 
 * @param string birthday           --> [$_POST] Date of birth
 * @param string email              --> [$_POST] E-mail adress
 * 2. Actions
 * @param string insert             --> [$_POST]
*/ 
function teachpress_students_new_page() { 

global $wpdb;
global $teachpress_stud;

$wp_id = isset($_POST['wp_id']) ? intval($_POST['wp_id']) : '';
$data['matriculation_number'] = isset( $_POST['matriculation_number'] ) ? intval($_POST['matriculation_number']) : '';
$data['firstname'] = isset( $_POST['firstname'] ) ? htmlspecialchars($_POST['firstname']) : '';
$data['lastname'] = isset( $_POST['lastname'] ) ? htmlspecialchars($_POST['lastname']) : '';
$data['course_of_studies'] = isset( $_POST['course_of_studies'] ) ? htmlspecialchars($_POST['course_of_studies']) : '';
$data['semesternumber'] = isset( $_POST['semesternumber'] ) ? intval($_POST['semesternumber']) : '';
$data['userlogin'] = isset( $_POST['userlogin'] ) ? htmlspecialchars($_POST['userlogin']) : '';
$data['birthday'] = isset( $_POST['birthday'] ) ? htmlspecialchars($_POST['birthday']) : '';
$data['email'] = isset( $_POST['email'] ) ? htmlspecialchars($_POST['email']) : '';

// actions
if (isset( $_POST['insert'] ) && $wp_id != __('WordPress User-ID','teachpress') && $wp_id != '') {
   $ret = tp_students::add_student($wp_id, $data);
   if ($ret != false) {
      $message = __('Registration successful','teachpress');
   }
   else {
      $message = __('Error: User already exist','teachpress');
   }
   get_tp_message($message);
}
?>
<div class="wrap" >
    <p><a href="admin.php?page=teachpress/students.php" class="button-secondary"><?php _e('Back','teachpress'); ?></a></p>
<h2><?php _e('Add student','teachpress'); ?></h2>

<p style="padding:0px; margin:0px;">&nbsp;</p>
<form id="neuer_student" name="neuer_student" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
<table class="form-table">
	<thead>
          <tr>
            <th><label for="wp_id"><strong><?php _e('WordPress User-ID','teachpress'); ?></strong></label></th>
            <td style="text-align:left;">
            <?php 
              echo '<select name="wp_id" id="wp_id">';
              echo '<option value="n">' . __('Select user','teachpress') . '</option>';
              $sql = "SELECT u.ID, s.wp_id, u.user_login FROM " . $wpdb->users . " u
                      LEFT JOIN $teachpress_stud s ON u.ID = s.wp_id";	
              $row = $wpdb->get_results($sql);
              foreach ($row as $row) {
                 if ($row->ID != $row->wp_id) {
                    echo '<option value="' . $row->ID . '">' . $row->user_login . '</option>';
                 }
              }
              echo '</select>';
              ?>
            </td>
            <td><?php _e('The Menu shows all your blog users who has no teachPress account','teachpress'); ?></td>  
      	  </tr>
          <?php $field1 = get_tp_option('regnum');
          if ($field1 == '1') { ?>
          <tr>
            <th><label for="matriculation_number"><strong><?php _e('Matr. number','teachpress'); ?></strong></label></th>
            <td style="text-align:left;"><input type="text" name="matriculation_number" id="matriculation_number" /></td>
            <td></td>
          </tr>
          <?php } ?>
          <tr>
            <th><label for="firstname"><strong><?php _e('First name','teachpress'); ?></strong></label></th>
            <td><input name="firstname" type="text" id="firstname" size="40" /></td>
            <td></td>
          </tr>
          <tr>
            <th><label for="lastname"><strong><?php _e('Last name','teachpress'); ?></strong></label></th>
            <td><input name="lastname" type="text" id="lastname" size="40" /></td>
            <td></td>
          </tr>
          <?php $field2 = get_tp_option('studies');
        	if ($field2 == '1') { ?>
          <tr>
            <th><label for="course_of_studies"><strong><?php _e('Course of studies','teachpress'); ?></strong></label></th>
            <td>
            <select name="course_of_studies" id="course_of_studies">
             <?php
              $stud = get_tp_options('course_of_studies', 'value ASC');
              foreach ($stud as $stud) {
                    echo '<option value="' . $stud->value . '">' . $stud->value . '</option>';
              } ?>
            </select>
            </td>
            <td></td>
          </tr>
          <?php } ?>
          <?php $field2 = get_tp_option('termnumber');
        	if ($field2 == '1') { ?>
          <tr>
            <th><label for="semesternumber"><strong><?php _e('Number of terms','teachpress'); ?></strong></label></th>
            <td style="text-align:left;">
            <select name="semesternumber" id="semesternumber">
            <?php
              for ($i=1; $i<20; $i++) {
                      echo '<option value="' . $i . '">' . $i . '</option>';
              } ?>
            </select>
            </td>
            <td></td>
          </tr>
          <?php } ?> 
          <tr>
            <th><label for="userlogin"><strong><?php _e('User account','teachpress'); ?></strong></label></th>
            <td style="text-align:left;"><input type="text" name="userlogin" id="userlogin" /></td>
            <td></td>
          </tr>
          <?php $field2 = get_tp_option('birthday');
        	if ($field2 == '1') { ?>
          <tr>
            <th><label for="birthday"><strong><?php _e('Date of birth','teachpress'); ?></strong></label></th>
            <td><input name="birthday" type="text" id="birthday" value="<?php _e('JJJJ-MM-TT','teachpress'); ?>" onblur="if(this.value=='') this.value='<?php _e('JJJJ-MM-TT','teachpress'); ?>';" onfocus="if(this.value=='<?php _e('JJJJ-MM-TT','teachpress'); ?>') this.value='';" size="15"/>
              </td>
            <td><?php _e('Format'); ?>: <?php _e('JJJJ-MM-TT','teachpress'); ?></td>  
          </tr>
          <?php } ?>
          <tr>
            <th><label for="email"><strong><?php _e('E-Mail'); ?></strong></label></th>
            <td><input name="email" type="text" id="email" size="50" /></td>
            <td></td>
          </tr>
         </thead> 
        </table>
    <p>
      <input name="insert" type="submit" id="std_einschreiben" onclick="teachpress_validateForm('firstname','','R','lastname','','R','userlogin','','R','email','','RisEmail');return document.teachpress_returnValue" value="<?php _e('Create','teachpress'); ?>" class="button-primary"/>
      <input name="reset" type="reset" id="reset" value="Reset" class="button-secondary"/>
    </p>
</form>
 <script type="text/javascript" charset="utf-8">
     jQuery(document).ready(function($) {
         $('#birthday').datepicker({showWeek: true, changeMonth: true, changeYear: true, showOtherMonths: true, firstDay: 1, renderer: $.extend({}, $.datepicker.weekOfYearRenderer), onShow: $.datepicker.showStatus, dateFormat: 'yy-mm-dd', yearRange: '1950:c+0'});
     });
 </script>
</div>
<?php } ?>