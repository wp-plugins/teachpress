<?php
/**
 * teachPress E-Mail class
 */
class tp_mail {
     
     /**
      * Send E-Mail
      * @param STRING $from
      * @param STRING $to
      * @param STRING $subject
      * @param STRING $message
      * @param ARRAY $options
      * @param STRING $attachments
      * @return BOOLEAN
      */
     function sendMail($from, $to, $subject, $message, $options, $attachments = '') {
          global $current_user;
          get_currentuserinfo();
          $message = htmlspecialchars($message);
          
          if ( $from == '' || $message == '' ) {
               return false;
          }
          
          // Prepare header attribute: From
          if ( $from == 'currentuser' ) {
               $headers = 'From: ' . $current_user->display_name . ' <' . $current_user->user_email . '>' . "\r\n";
          }
          else {
               $headers = 'From: ' . get_bloginfo('name') . ' <' . get_bloginfo('admin_email') . '>' . "\r\n";
          }
          
          // Preprare header attribute: Bcc, Cc
          if ( $options['recipients'] == 'Bcc' ) {
               $headers = $headers . tp_mail::prepareBCC($to);
               $to = $current_user->user_email;
          }
          else {
               $headers = $headers . 'Cc: ' . $current_user->user_email . "\r\n";
          }
          
          // Send user a backup e-mail if he want
          if ( $options['backup_mail'] == 'backup' ) {
               wp_mail($current_user->user_email, $subject, $message, '', $attachments);
          }
          
          $ret = wp_mail($to, $subject, $message, $headers, $attachments);
          return $ret;
     }
     
     /**
      * Prepare BCC field for E-Mail header
      * @param STRING $recipients
      * @return STRING
      */
     function prepareBCC($recipients) {
          $array = explode(",",$recipients);
          $bcc = '';
          foreach ($array as $recipient) {
               $recipient = trim($recipient);
               if ( !is_email($recipient) ) { continue; }
               if ( !empty($recipient) ) {
                    if ($bcc == '') {
                         $bcc = 'Bcc: ' . $recipient;
                    }
                    else {
                         $bcc = $bcc . ', ' . $recipient;
                    }
               }
          }
          return $bcc . "\r\n";
     }
}
?>
