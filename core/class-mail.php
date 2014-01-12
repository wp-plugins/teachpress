<?php
/**
 * teachPress E-Mail class
 */
class tp_mail {
     
    /**
     * Send E-Mail
     * @global string $current_user
     * @param string $from
     * @param string $to
     * @param string $subject
     * @param string $message
     * @param string $options
     * @param string $attachments
     * @return boolean
     */
    static function sendMail($from, $to, $subject, $message, $options, $attachments = '') {
        global $current_user;
        get_currentuserinfo();
        $message = htmlspecialchars($message);

        if ( $from == '' || $message == '' ) {
            return false;
        }

        // Send mail
        // Use the normal wp_mail()
        // The Return-Path seems to be useless, I'm no sure why
            // Prepare header attributes
            if ( $from === 'currentuser' ) {
                $headers[] = 'From: ' . $current_user->display_name . ' <' . $current_user->user_email . '>';
                $headers[] = 'Return-Path: ' . $current_user->user_email;
            }
            else {
                $headers[] = 'From: ' . get_bloginfo('name') . ' <' . get_bloginfo('admin_email') . '>';
                $headers[] = 'Return-Path: ' . get_bloginfo('admin_email');
            }
            
            // Preprare header attribute: Bcc
            if ( $options['recipients'] === 'Bcc' ) {
                $headers[] = tp_mail::prepareBCC($to);
                $to = $current_user->user_email;
            }
            
            // Send backup mail
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
