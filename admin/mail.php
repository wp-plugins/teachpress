<?php
/**
 * Mail form
 * 
 * @global class $wpdb
 * @global var $teachpress_stud
 * @global var $teachpress_signup
 * @global $current_user;
 * 
 * @since 3.0.0
 */
function tp_show_mail_page() {
    global $wpdb;
    global $teachpress_stud; 
    global $teachpress_signup;
    global $current_user;
    get_currentuserinfo();

    $course_ID = isset( $_GET['course_ID'] ) ? intval($_GET['course_ID']) : 0;
    $redirect = isset( $_GET['redirect'] ) ?  intval($_GET['redirect']) : 0;
    $student_ID = isset( $_GET['student_ID'] ) ? intval($_GET['student_ID']) : 0;
    $search = isset( $_GET['search'] ) ? htmlspecialchars($_GET['search']) : '';
    $sem = isset( $_GET['sem'] ) ? htmlspecialchars($_GET['sem']) : '';
    $single = isset( $_GET['single'] ) ? htmlspecialchars($_GET['single']) : '';
    $students_group = isset( $_GET['students_group'] ) ? htmlspecialchars($_GET['students_group']) : '';
    $limit = isset( $_GET['limit'] ) ? intval($_GET['limit']) : 0;
    $group = isset( $_GET['group'] ) ? htmlspecialchars($_GET['group']) : '';

    if( !isset( $_GET['single'] ) ) {
        $sql = "SELECT DISTINCT st.email 
                FROM $teachpress_signup s 
                INNER JOIN $teachpress_stud st ON st.wp_id=s.wp_id
                WHERE s.course_id = '$course_ID'";	
        // E-Mails of registered participants
        if ( $group == 'reg' ) {
            $sql = $sql . " AND s.waitinglist = '0'";	
        }
        // E-Mails of participants in waitinglist
        if ( $group == 'wtl' ) {
            $sql = $sql . " AND s.waitinglist = '1'";		
        }
        $sql = $sql . " ORDER BY st.lastname ASC";	
        $mails = $wpdb->get_results($sql, ARRAY_A);
    }
    ?>
    <div class="wrap">
        <?php
        if ( isset( $_GET['course_ID'] ) ) {
            $return_url = "admin.php?page=teachpress/teachpress.php&amp;course_ID=$course_ID&amp;sem=$sem&amp;search=$search&amp;redirect=$redirect&amp;action=enrollments";
        }
        if ( isset( $_GET['student_ID'] ) ) {
            $return_url = "admin.php?page=teachpress/students.php&amp;student_ID=$student_ID&amp;search=$search&amp;students_group=$students_group&amp;limit=$limit";
        }
        ?>
        <p><a href="<?php echo $return_url; ?>" class="button-secondary">&larr; <?php _e('Back','teachpress'); ?></a></p>
        <h2><?php _e('Writing an E-Mail','teachpress'); ?></h2>
        <form name="form_mail" method="post" action="<?php echo $return_url; ?>">
        <table class="form-table">
            <tr>
            <th scope="row" style="width: 65px;"><label for="mail_from"><?php _e('From','teachpress'); ?></label</th>
            <td>
                <select name="from" id="mail_from">
                    <option value="currentuser"><?php echo $current_user->display_name . ' (' . $current_user->user_email . ')'; ?></option>
                    <option value="wordpress"><?php echo get_bloginfo('name') . ' (' . get_bloginfo('admin_email') . ')'; ?></option>
                </select>
            </td>
            </tr>
            <tr>
                <th scope="row" style="width: 65px;">
                    <select name="recipients_option" id="mail_recipients_option">
                        <option value="To"><?php _e('To','teachpress'); ?></option>
                        <option value="Bcc"><?php _e('Bcc','teachpress'); ?></option>
                    </select>
                </th>
                <td>
                    <?php
                    if( !isset( $_GET['single'] ) ) {
                        $link = "admin.php?page=teachpress/teachpress.php&amp;course_ID=$course_ID&amp;sem=$sem&amp;search=$search&amp;action=mail&amp;type=course";
                        if ($group == "wtl") {
                            echo '<p><strong><a href="' . $link . '">' . __('All', 'teachpress') . '</a> | <a href="' . $link . '&amp;group=reg">' . __('Only participants', 'teachpress') . '</a> | ' . __('Only waitinglist','teachpress') . '</strong><p>';
                        }
                        elseif ( $group == "reg" ) {
                            echo '<p><strong><a href="' . $link . '">' . __('All', 'teachpress') . '</a> | ' . __('Only participants', 'teachpress') . ' | <a href="' . $link . '&amp;group=wtl">' . __('Only waitinglist','teachpress') . '</a></strong><p>';
                        }
                        else {
                            echo '<p><strong>' . __('All', 'teachpress') . ' | <a href="' . $link . '&amp;group=reg">' . __('Only participants', 'teachpress') . '</a> | <a href="' . $link . '&amp;group=wtl">' . __('Only waitinglist','teachpress') . '</a></strong><p>';
                        }
                    }
                    
                    if( !isset( $_GET['single'] ) ) {
                        $to = '';
                        foreach($mails as $mail) { 
                            $to = $to . $mail["email"] . ', '; 
                        } 
                        $to = substr($to, 0, -2);
                    }
                    else {
                        $to = $single;
                    }
                    ?> 
                    <textarea name="recipients" id="mail_recipients" rows="3" style="width: 590px;"><?php echo $to; ?></textarea>
                </td>
            </tr>
            <tr>
                <th scope="row" style="width: 65px;"><label for="mail_subject"><?php _e('Subject','teachpress'); ?></label></th>
                <td><input name="subject" id="mail_subject" type="text" style="width: 580px;"/></td>
            </tr>
        </table>
        <br />
        <textarea name="text" id="mail_text" style="width: 685px;" rows="15"></textarea>
        <table>
            <tr>
                <td><input type="checkbox" name="backup_mail" id="backup_mail" title="<?php _e('Send me the e-mail as separate copy','teachpress'); ?>" value="backup" checked="checked" /></td>
                <td><label for="backup_mail"><?php _e('Send me the e-mail as separate copy','teachpress'); ?></label></td>
            </tr>
        </table>
        <br />
        <input type="submit" class="button-primary" name="send_mail" value="<?php _e('Send','teachpress'); ?>"/>
        <script type="text/javascript" charset="utf-8">
        jQuery(document).ready(function($) {
            $('#mail_text').resizable({handles: "se", minHeight: 55, minWidth: 400});
	});
        jQuery(document).ready(function($) {
            $('#mail_recipients').resizable({handles: "se", minHeight: 55, minWidth: 400});
	});
        </script>
        </form>
    </div>
    <?php
}
?>