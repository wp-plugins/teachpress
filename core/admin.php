<?php
/**
 * This file contains all general functions for admin menu
 * 
 * @package teachpress\core\admin
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */

/**
 * This class contains general functions for teachpress admin menus
 * @since 5.0.0
 * @package teachpress\core\admin
 */
class tp_admin {
    
    /**
     * Tests if the database needs an update. If this is available a message will be displayed.
     * @param $before       This will be displayed before the message
     * @param $after        This will be displayed after the message
     * @since 5.0.0
     */
    public static function database_test($before = '', $after = '') {
        $test = get_tp_option('db-version');
        
        // Don't use !== operator here
        if ($test != '') {
           $version = get_tp_version();
           if ($test !== $version) {
               echo $before;
               get_tp_message( __('A database update is necessary','teachpress') . '. <a href="options-general.php?page=teachpress/settings.php&up=1">' . __('Update to','teachpress') . ' ' . $version . '</a>.', 'orange' );
               echo $after;
           }
        }
        else {
            echo $before;
            get_tp_message( '<a href="options-general.php?page=teachpress/settings.php&ins=1">' . __('Install database','teachpress') . '</a>', 'orange' );
            echo $after;
        }
    }
    
    /**
     * Returns a select field for assessment types
     * @param string $field_name    The name of the field
     * @param string $value         the value of the field
     * @param string $tabindex         The tabindex number
     * @return string
     * @since 5.0.0
     */
    public static function get_assessment_type_field($field_name, $value, $tabindex = ''){
        $return = '';
        $return .= '<select name="' . $field_name . '" id="' . $field_name . '" tabindex="' . $tabindex . '">';
        $options = array( 
                    array( 'value' => 'grade', 'title' => __('Grade','teachpress') ),
                    array( 'value' => 'percentage', 'title' => __('Percentage','teachpress') ),
                    array( 'value' => 'points', 'title' => __('Points','teachpress') ) 
                   );
        foreach ($options as $opt) {
            $selected = ( $value == $opt['value'] ) ? 'selected="selected"' : '';
            $return .= '<option value="' . stripslashes($opt['value']) . '" ' . $selected . '>' . stripslashes($opt['title']) . '</option>';
        }
        $return .= '</select>';
        return $return;
    }
    
    /**
     * Returns a select field for assessment_passed
     * @param string $field_name    The name of the field
     * @param string $value         the value of the field
     * @param string $tabindex         The tabindex number
     * @return string
     */
    public static function get_assessment_passed_field($field_name, $value, $tabindex = '') {
        $return = '';
        $return .= '<select name="' . $field_name . '" id="' . $field_name . '" tabindex="' . $tabindex . '">';
        $options = array( 
                    array( 'value' => '0', 'title' => __('not passed','teachpress') ),
                    array( 'value' => '1', 'title' => __('passed','teachpress') ) 
                   );
        foreach ($options as $opt) {
            $selected = ( $value == $opt['value'] ) ? 'selected="selected"' : '';
            $return .= '<option value="' . stripslashes($opt['value']) . '" ' . $selected . '>' . stripslashes($opt['title']) . '</option>';
        }
        $return .= '</select>';
        return $return;
    }
    
    /**
     * Returns checkbox fields for admin form
     * @param string $field_name    name/id of the field
     * @param string $label         label for the field
     * @param string $checked       value for the field
     * @param boolean $readonly     true or false, default is false
     * @param boolean $required     true or false, default is false
     * @return string
     * @since 5.0.0
     */
    public static function get_checkbox_field ($field_name, $label, $checked, $readonly = false, $required = false) {
        global $wpdb;
        $return = '';
        $options = $wpdb->get_results("SELECT * FROM " . TEACHPRESS_SETTINGS . " WHERE `category` = '" . esc_sql($field_name) . "' ORDER BY value ASC");
        $readonly = ( $readonly === true ) ? 'readonly="true" ' : '' ;
        $required = ( $required === true ) ? 'required="required"' : '';
        // extrakt checkbox_values
        $array_checked = tp_enrollments::extract_checkbox_data($checked);
        $return .= '<p><label for="' . $field_name . '"><b>' . stripslashes($label) . '</b></label></p>';
        $i = 1;
        $max = count($options);
        foreach ($options as $opt) {
            $checked = ( in_array($opt->value, $array_checked) ) ? 'checked="checked"' : '';
            $required = ( $max === 1 ) ? $required : '';  // The required optopns is only available for single checkboxes
            $return .= '<input name="' . $field_name . '[]" type="checkbox" id="' . $field_name . '_' . $i . '" value="' . stripslashes($opt->value) . '" ' . $checked . ' ' . $readonly . ' ' . $required . '/> <label for="' . $field_name . '_' . $i . '">' . stripslashes($opt->value) . '</label><br/>';
            $i++;
        }
        return $return;
    }
    
    /**
     * Returns date select fields for admin form
     * @param string $field_name    name/id of the field
     * @param string $label         label for the field
     * @param string $value         value for the field
     * @return string
     * @since 5.0.0
     */
    public static function get_date_field ($field_name, $label, $value) {
        if ( $value != '' ) {
            $b = tp_datesplit($value);
        }
        $day = ( $value != '' ) ? $b[0][2] : '01';
        $month = ( $value != '' ) ? $b[0][1] : '01';
        $year = ( $value != '' ) ? $b[0][0] : '19xx';
        $months = array ( __('Jan','teachpress'), __('Feb','teachpress'), __('Mar','teachpress'), __('Apr','teachpress'), __('May','teachpress'), __('Jun','teachpress'), __('Jul','teachpress'), __('Aug','teachpress'), __('Sep','teachpress'), __('Oct','teachpress'), __('Nov','teachpress'), __('Dec','teachpress') );
        $return = '';
        $return .= '<p><b>' . stripslashes($label) . '</b></p>';
        $return .= '<input name="' . $field_name . '_day" id="' . $field_name . '_day" type="text" title="Day" size="2" value="' . $day . '"/>';
        $return .= '<select name="' . $field_name . '_month" id="' . $field_name . '_month" title="' . __('Month','teachpress') . '">';
        for ( $i = 1; $i <= 12; $i++ ) {
            $m = ( $i < 10 ) ? '0' . $i : $i;
            $selected = ($month == $m) ? 'selected="selected"' : '';
            $return .= '<option value="' . $m . '" ' . $selected . '>' . $months[$i-1] . '</option>';
        }
        $return .= '</select>';
        $return .= '<input name="' . $field_name . '_year" id="' . $field_name . '_year" type="text" title="' . __('Year','teachpress') . '" size="4" value="' . $year . '"/>';
        return $return;
    }
    
    /**
     * Returns a number field for admin form
     * @param string $field_name    name/id of the field
     * @param string $label         label for the field
     * @param int $value            value for the field
     * @param int $min              default is 0
     * @param int $max              default is 999
     * @param int step              default is 1
     * @param boolean $readonly     true or false, default is false
     * @param boolean $required     true or false, default is false
     * @return string
     * @since 5.0.0
     */
    public static function get_int_field($field_name, $label, $value, $min = 0, $max = 999, $step = 1, $readonly = false, $required = false){
        $readonly = ( $readonly === true ) ? 'readonly="true" ' : '' ;
        $required = ( $required === true ) ? 'required="required"' : '';
        return '<p><label for="' . $field_name . '"><b>' . stripslashes($label) . '</b></label></p>
                <input name="' . $field_name . '" type="number" id="' . $field_name . '" value="' . $value . '" size="50" ' . $readonly . ' ' . $required . ' min="' . $min . '" max="' . $max . '" step="' . $step . '"/>';
    }
    
    /**
     * Returns radio fields for admin form
     * @param string $field_name    name/id of the field
     * @param string $label         label for the field
     * @param string $value         current value for the field
     * @param boolean $readonly     true or false, default is false
     * @param boolean $required     true or false, default is false
     * @return string
     * @since 5.0.0
     */
    public static function get_radio_field ($field_name, $label, $value, $readonly = false, $required = false) {
        global $wpdb;
        $return = '';
        $options = $wpdb->get_results("SELECT * FROM " . TEACHPRESS_SETTINGS . " WHERE `category` = '" . esc_sql($field_name) . "' ORDER BY value ASC");
        $readonly = ( $readonly === true ) ? 'readonly="true" ' : '' ;
        $required = ( $required === true ) ? 'required="required"' : '';
        $return .= '<p><label for="' . $field_name . '"><b>' . stripslashes($label) . '</b></label></p>';
        $i = 1;
        foreach ($options as $opt) {
            $checked = ( $value == $opt->value ) ? 'checked="checked"' : '';
            $return .= '<input name="' . $field_name . '" type="radio" id="' . $field_name . '_' . $i . '" value="' . stripslashes($opt->value) . '" ' . $checked . ' ' . $readonly . ' ' . $required . '/> <label for="' . $field_name . '_' . $i . '">' . stripslashes($opt->value) . '</label><br/>';
            $i++;
        }
        return $return;
    }
    
    /**
     * Returns a select field for admin/settings screens
     * @param string $field_name    name/id of the field
     * @param string $label         label for the field
     * @param string $value         value for the field
     * @return string
     * @since 5.0.0
     */
    public static function get_select_field ($field_name, $label, $value) {
        global $wpdb;
        $return = '';
        $return .= '<p><label for="' . $field_name . '"><b>' . stripslashes($label) . '</b></label></p>';
        $return .= '<select name="' . $field_name . '" id="' . $field_name . '">';
        $options = $wpdb->get_results("SELECT * FROM " . TEACHPRESS_SETTINGS . " WHERE `category` = '" . esc_sql($field_name) . "' ORDER BY value ASC");
        if ( $value == '' ) {
            $return .= '<option value="">- ' . __('none','teachpress') . ' -</option>';
        }
        foreach ($options as $opt) {
            $selected = ( $value == $opt->value ) ? 'selected="selected"' : '';
            $return .= '<option value="' . stripslashes($opt->value) . '" ' . $selected . '>' . stripslashes($opt->value) . '</option>';
        }
        $return .= '</select>';
        return $return;
    }
    
    /**
     * Returns a text field for admin/settings screens
     * @param string $field_name    name/id of the field
     * @param string $label         label for the field
     * @param string $value         value for the field
     * @param boolean $readonly
     * @return string
     * @since 5.0.0
     */
    public static function get_text_field($field_name, $label, $value, $readonly = false) {
        $readonly = ( $readonly === false ) ? '' : 'readonly="true" ';
        return '<p><label for="' . $field_name . '"><b>' . stripslashes($label) . '</b></label></p>
                <input name="' . $field_name . '" type="text" id="' . $field_name . '" value="' . stripslashes($value) . '" size="50" ' . $readonly . '/>';
    }
    
    /**
     * Returns a textarea field for admin/settings screens
     * @param string $field_name    name/id of the field
     * @param string $label         label for the field
     * @param string $value         value for the field
     * @return string
     * @since 5.0.0
     */
    public static function get_textarea_field ($field_name, $label, $value) {
        return '<p><label for="' . $field_name . '"><b>' . stripslashes($label) . '</b></label><p>
                <textarea name="' . $field_name . '" id="' . $field_name . '" style="width:100%; height:80px;">' . stripslashes($value) . '</textarea>';
    }
    
    /**
     * Returns a form field for the add_publication_page()
     * @param string $name          field name
     * @param string $title         field title
     * @param string $label         field label
     * @param string $field_type    field type (textarea|input)
     * @param string $pub_type      publication type of the current/visible entry
     * @param string $pub_value     field value of the current/visible entry
     * @param array $availabe_for   array of publication types
     * @param int $tabindex         the tab index
     * @param string $style         css style attributes
     * @return string
     * @since 5.0.0
     */
    public static function get_form_field ($name, $title, $label, $field_type, $pub_type, $pub_value, $availabe_for, $tabindex, $style = '') {
        $display = ( in_array($pub_type, $availabe_for) ) ? 'style="display:block;"' : 'style="display:none;"';
        if ( $field_type === 'textarea' ) {
            $field = '<textarea name="' . $name . '" id="' . $name . '" wrap="virtual" style="' . $style . '" tabindex="' . $tabindex . '" title="' . stripslashes($title) . '">' . stripslashes($pub_value) . '</textarea>';
        }
        else {
            $field = '<input name="' . $name . '" id="' . $name . '" type="text" title="' . stripslashes($title) . '" style="' . $style . '" value="' . stripslashes($pub_value) . '" tabindex="' . $tabindex . '" />';
        }
        $a = '<div id="div_' . $name . '" ' . $display . '>
              <p><label for="' . $name . '" title="' . stripslashes($title) . '"><strong>' . stripslashes($label) . '</strong></label></p>
              ' . $field . '</div>';
        return $a;
    }
    
    /**
     * Returns a checkbox for admin/settings screens
     * @param string $name
     * @param string $title
     * @param string $value
     * @param boolean $disabled
     * @return string
     * @since 5.0.0
     */
    public static function get_checkbox($name, $title, $value, $disabled = false) {
        $checked = ( $value == '1' ) ? 'checked="checked"' : '';
        $disabled = ( $disabled === true ) ? ' disabled="disabled"' : '';
        return '<input name="' . $name . '" id="' . $name . '" type="checkbox" value="1" ' . $checked . $disabled .'/> <label for="' . $name . '">' . stripslashes($title) . '</label>';
    }
    
    /**
     * Displays a box for editing some options (terms|type|studies) for courses
     * @param string $title
     * @param string $type
     * @param array $options (element_title|add_title|delete_title|count_title|tab)
     * @since 5.0.0
     */
    public static function get_course_option_box ( $title, $type, $options = array() ) {
        global $wpdb;
        echo '<h3>' . $title . '</h3>';
        echo '<table border="0" cellspacing="0" cellpadding="0" class="widefat">';
        echo '<thead>';
        echo '<tr>';
        echo '<th width="10">&nbsp;</th>';
        echo '<th>' . $options['element_title'] . '</th>';
        if ( $type === 'term' || $type === 'course_of_studies' || $type === 'type' ) {
        echo '<th width="150">' . $options['count_title'] . '</th>';
        }
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        if ( $type === 'term' ) {
            $sql = "SELECT number, value, setting_id FROM ( SELECT COUNT(v.semester) as number, e.variable AS value,  e.setting_id as setting_id, e.category as category FROM " . TEACHPRESS_SETTINGS . " e LEFT JOIN " . TEACHPRESS_COURSES . " v ON e.variable = v.semester GROUP BY e.variable ORDER BY number DESC ) AS temp WHERE category = 'semester' ORDER BY setting_id";
        }
        elseif ( $type === 'type' ) {
            $sql = "SELECT number, value, setting_id FROM ( SELECT COUNT(v.type) as number, e.value AS value,  e.setting_id as setting_id, e.category as category FROM " . TEACHPRESS_SETTINGS . " e LEFT JOIN " . TEACHPRESS_COURSES . " v ON e.value = v.type GROUP BY e.value ORDER BY number DESC ) AS temp WHERE category = 'course_type' ORDER BY value";
        }
        elseif ( $type === 'course_of_studies' ) {
            $sql = "SELECT number, value, setting_id FROM ( SELECT COUNT(s.course_of_studies) as number, e.value AS value,  e.setting_id as setting_id, e.category as category FROM " . TEACHPRESS_SETTINGS . " e LEFT JOIN " . TEACHPRESS_STUD . " s ON e.value = s.course_of_studies GROUP BY e.value ORDER BY number DESC ) AS temp WHERE category = 'course_of_studies' ORDER BY value";
        }
        else {
            $sql = "SELECT * FROM " . TEACHPRESS_SETTINGS . " WHERE `category` = '" . esc_sql($type) . "' ORDER BY value ASC";
        }

        $row = $wpdb->get_results($sql);
        $class_alternate = true;
        foreach ($row as $row) {
            if ( $class_alternate === true ) {
                $tr_class = 'class="alternate"';
                $class_alternate = false;
            }
            else {
                $tr_class = '';
                $class_alternate = true;
            }
            echo '<tr ' . $tr_class . '>';
            echo '<td><a title="' . $options['delete_title'] . '" href="options-general.php?page=teachpress/settings.php&amp;delete=' . $row->setting_id . '&amp;tab=' . $options['tab'] . '" class="teachpress_delete">X</a></td>';
            echo '<td>' . stripslashes($row->value) . '</td>';
            if ( $type === 'term' || $type === 'course_of_studies' || $type === 'type' ) {
                echo '<td>' . $row->number . '</td>';
            }
            echo '</tr>';              
        }

        echo '<tr>';
        echo '<td></td>';
        echo '<td colspan="2"><input name="new_' . $type . '" type="text" id="new_' . $type . '" size="30" value="' . $options['add_title'] . '" onblur="if(this.value==' . "''" .') this.value='. "'" . $options['add_title'] . "'" . ';" onfocus="if(this.value=='. "'" . $options['add_title'] . "'" . ') this.value=' . "''" . ';"/> <input name="add_' . $type . '" type="submit" class="button-secondary" value="' . __('Create','teachpress') . '"/></td>'; 
        echo '</tr>'; 

        echo '</tbody>';
        echo '</table>';     
    }
    
    /**
     * Displays the meta data section of publications / courses in admin menus
     * @param array $fields
     * @param array $meta_input
     * @since 5.0.0
     */
    public static function display_meta_data($fields, $meta_input) {
    
        echo '<div class="postbox">';
        echo '<h3 class="tp_postbox"><span>' . __('Custom meta data','teachpress') . '</span></h3>';
    
        echo '<div class="inside">';   
        foreach ($fields as $row) {
            $col_data = tp_db_helpers::extract_column_data($row['value']);
            $required = ( $col_data['required'] === 'true' ) ? true : false;
            $value = '';
            foreach ( $meta_input as $row_meta ) {
                if ( $row['variable'] === $row_meta['meta_key'] ) {
                    $value = $row_meta['meta_value'];
                    break;
                }
            }
            if ( $col_data['type'] === 'SELECT' ) {
                echo tp_admin::get_select_field($row['variable'], $col_data['title'], $value);
            }
            elseif ( $col_data['type'] === 'DATE' ) {
                echo tp_admin::get_date_field($row['variable'], $col_data['title'], $value);
            }
            elseif ( $col_data['type'] === 'RADIO' ) {
                echo tp_admin::get_radio_field($row['variable'], $col_data['title'], $value, false, $required);
            }
            elseif ( $col_data['type'] === 'CHECKBOX' ) {
                echo tp_admin::get_checkbox_field($row['variable'], $col_data['title'], $value, false, $required);
            }
            elseif ( $col_data['type'] === 'TEXTAREA' ) {
                echo tp_admin::get_textarea_field($row['variable'], $col_data['title'], $value);
            }
            elseif ( $col_data['type'] === 'INT' ) {
                $col_data['min'] = ( $col_data['min'] !== 'false' ) ? intval($col_data['min']) : 0;
                $col_data['max'] = ( $col_data['max'] !== 'false' ) ? intval($col_data['max']) : 999;
                $col_data['step'] = ( $col_data['step'] !== 'false' ) ? intval($col_data['step']) : 1;
                echo tp_admin::get_int_field($row['variable'], $col_data['title'], $value, $col_data['min'], $col_data['max'], $col_data['step'], false, $required);
            }
            else {
                echo tp_admin::get_text_field($row['variable'], $col_data['title'], $value);
            }
        }
        echo '</div>';
        echo '</div>'; 
    }
}

/**
 * Gets all drafts of a post type as options for select menus
 * @param string $post_type
 * @param string $post_status       Default is "publish"
 * @param string $sort_column       Default is "menu_order"
 * @param string $sort_order        Defalut is "ASC"
 * @since 5.0.0
 */
function get_tp_wp_drafts($post_type, $post_status = 'publish', $sort_column = 'menu_order', $sort_order = 'ASC') {
    global $wpdb;
    echo "\n\t<option value='0'>" . __('none','teachpress') . "</option>";
    $items = $wpdb->get_results( "SELECT `ID`, `post_title` FROM $wpdb->posts WHERE `post_type` = '" . esc_sql($post_type) . "' AND `post_status` = '" . esc_sql($post_status) . "' ORDER BY " . esc_sql($sort_column) . " " . esc_sql($sort_order) );
    foreach ( $items as $item ) {
        echo "\n\t<option value='$item->ID'>" . get_the_title($item->ID) . "</option>";
    }
}

/**
 * This function handles document uploads in teachPress
 * @since 5.0.0
 */
function tp_handle_document_uploads(){
    check_ajax_referer('document-upload');
    $course_id = ( isset ($_POST['course_id']) ) ? intval($_POST['course_id']) : 0;
    $status = tp_handle_upload($_FILES['async-upload'], array('action' => 'tp_document_upload'), $course_id);
    // print_r($status);
    if ( isset($status['error']) ) {
        echo htmlspecialchars($status['error']);
        exit;
    }
    $doc_id = tp_documents::add_document($status['filename'], $status['path'], $status['size'], $course_id);
    $upload_dir = wp_upload_dir();
    echo $doc_id . ' | ' . $course_id . ' | ' . esc_url($upload_dir['baseurl'] . $status['path']);
    exit;
}

/**
 * Handle PHP uploads in teachPress, sanitizing file names, checking extensions for mime type,
 * and moving the file to the appropriate directory within the uploads directory. The function is a modified copy
 * of wp_handle_upload(), but uses the teachpress upload directory
 *
 * @since 5.0.0
 *
 * @param array $file       Reference to a single element of $_FILES. Call the function once for each uploaded file.
 * @param array $overrides  Optional. An associative array of names=>values to override default variables with extract( $overrides, EXTR_OVERWRITE ).
 * @param int $course_id    ID of a teachPress course.
 * @return array On success, returns an associative array of file attributes. On failure, returns $overrides['upload_error_handler'](&$file, $message ) or array( 'error'=>$message ).
 */
function tp_handle_upload( &$file, $overrides = false, $course_id = 0 ) {
	// The default error handler.
	if ( ! function_exists( 'wp_handle_upload_error' ) ) {
            function wp_handle_upload_error( &$file, $message ) {
                return array( 'error'=>$message );
            }
	}

	$file = apply_filters( 'wp_handle_upload_prefilter', $file );

	// You may define your own function and pass the name in $overrides['upload_error_handler']
	$upload_error_handler = 'wp_handle_upload_error';

	// You may have had one or more 'wp_handle_upload_prefilter' functions error out the file. Handle that gracefully.
	if ( isset( $file['error'] ) && !is_numeric( $file['error'] ) && $file['error'] ) {
            return $upload_error_handler( $file, $file['error'] );
        }

	// Courtesy of php.net, the strings that describe the error indicated in $_FILES[{form field}]['error'].
	$upload_error_strings = array( false,
		__( "The uploaded file exceeds the upload_max_filesize directive in php.ini." ),
		__( "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form." ),
		__( "The uploaded file was only partially uploaded." ),
		__( "No file was uploaded." ),
		'',
		__( "Missing a temporary folder." ),
		__( "Failed to write file to disk." ),
		__( "File upload stopped by extension." ));

	// All tests are on by default. Most can be turned off by $overrides[{test_name}] = false;
	$test_size = true;
	$test_upload = true;

	// If you override this, you must provide $ext and $type!!!!
	$test_type = true;
	$mimes = false;

	// Install user overrides. Did we mention that this voids your warranty?
	if ( is_array( $overrides ) ) {
            extract( $overrides, EXTR_OVERWRITE );
        }

	// A successful upload will pass this test. It makes no sense to override this one.
	if ( isset( $file['error'] ) && $file['error'] > 0 ) {
            return call_user_func( $upload_error_handler, $file, $upload_error_strings[ $file['error'] ] );
	}

	// A non-empty file will pass this test.
	if ( $test_size && !($file['size'] > 0 ) ) {
            if ( is_multisite() ) {
                $error_msg = __( 'File is empty. Please upload something more substantial.' );
            }
            return call_user_func($upload_error_handler, $file, $error_msg);
	}

	// A properly uploaded file will pass this test. There should be no reason to override this one.
	if ( $test_upload && ! @ is_uploaded_file( $file['tmp_name'] ) ) {
            return call_user_func($upload_error_handler, $file, __( 'Specified file failed upload test.' ));
        }
        
	// A correct MIME type will pass this test. Override $mimes or use the upload_mimes filter.
	if ( $test_type ) {
            $wp_filetype = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'], $mimes );

            extract( $wp_filetype );

            // Check to see if wp_check_filetype_and_ext() determined the filename was incorrect
            if ( $proper_filename ) {
                $file['name'] = $proper_filename;
            }
            if ( ( !$type || !$ext ) && !current_user_can( 'unfiltered_upload' ) ) {
                return call_user_func($upload_error_handler, $file, __( 'Sorry, this file type is not permitted for security reasons.' ));
            }
            if ( !$ext ) {
                $ext = ltrim(strrchr($file['name'], '.'), '.');
            }
            if ( !$type ) {
                $type = $file['type'];
            }
	} else {
            $type = '';
	}
        
        // If there is a course_id use it in the file path
        $extra_directory_part = '';
        if ( $course_id !== 0 ) {
            $extra_directory_part = "/course_$course_id";
        }

	// A writable uploads dir will pass this test. Again, there's no point overriding this one.
	if ( ! ( ( $uploads = wp_upload_dir() ) && false === $uploads['error'] ) ) {
		return call_user_func($upload_error_handler, $file, $uploads['error'] );
        }
	$filename = wp_unique_filename( $uploads['basedir'] . "/teachpress$extra_directory_part", $file['name'] );
        
	// Move the file to the uploads dir
        wp_mkdir_p($uploads['basedir'] . "/teachpress$extra_directory_part");
	$new_file = $uploads['basedir'] . "/teachpress$extra_directory_part/$filename";
	if ( false === @ move_uploaded_file( $file['tmp_name'], $new_file ) ) {
            if ( 0 === strpos( $uploads['basedir'], ABSPATH ) ) {
                $error_path = str_replace( ABSPATH, '', $uploads['basedir'] ) . "/teachpress$extra_directory_part/$filename";
            }
            else {
                $error_path = basename( $uploads['basedir'] ) . "/teachpress$extra_directory_part/$filename";
            }
            return $upload_error_handler( $file, sprintf( __('The uploaded file could not be moved to %s.' ), $error_path ) );
	}

	// Set correct file permissions
	$stat = stat( dirname( $new_file ));
	$perms = $stat['mode'] & 0000666;
	@ chmod( $new_file, $perms );

	// Compute the URL
	$url = $uploads['url'] . "/$filename";

	if ( is_multisite() ) {
            delete_transient( 'dirsize_cache' );
        }
	/**
	 * Filter the data array for the uploaded files
	 *
	 * @param array  $upload {
	 *     Array of upload data.
	 *
	 *     @type string $file       Filename of the newly-uploaded file.
	 *     @type string $url        URL of the uploaded file.
         *     @type string $path       The directory path of the uploaded file, file name included.
	 *     @type string $type       File type.
         *     @type int    $size       File size.
         *     @type string $filename   File name.
	 * }
	 * @param string $context The type of upload action. Accepts 'upload' or 'sideload'.
	 */
	return apply_filters( 'wp_handle_upload', array( 'file' => $new_file, 
                                                         'url' => $url, 
                                                         'path' => "/teachpress$extra_directory_part/$filename", 
                                                         'type' => $type, 
                                                         'size' => $file['size'],
                                                         'filename' => $filename ), 'upload' );
}

/** 
 * Get WordPress pages
 * adapted from Flexi Pages Widget Plugin
 * @param string $sort_column       Default is "menu_order"
 * @param string $sort_order        Default is "ASC"
 * @param string $selected          
 * @param string $post_type         Default is "page"
 * @param int $parent               Default is 0
 * @param int $level                Default is 0
 * @since 1.0.0
*/ 
function get_tp_wp_pages($sort_column = "menu_order", $sort_order = "ASC", $selected = '', $post_type = 'page', $parent = 0, $level = 0 ) {
    global $wpdb;
    if ( $level == 0 ) {
        $pad = isset ($pad) ? $pad : '';
        if ( $selected == '0' ) {
            $current = ' selected="selected"';
        }
        elseif (is_array($selected)) {
            if ( in_array(0, $selected) ) {
                $current = ' selected="selected"';
            }   
        }
        else {
            $current = '';
        }
        echo "\n\t<option value='0'$current>$pad " . __('none','teachpress') . "</option>";
    }
    $items = $wpdb->get_results( "SELECT `ID`, `post_parent`, `post_title` FROM $wpdb->posts WHERE `post_parent` = '" . intval($parent) . "' AND `post_type` = '" . esc_sql($post_type) . "' AND `post_status` = 'publish' ORDER BY " . esc_sql($sort_column) . " " . esc_sql($sort_order) );
    if ( $items ) {
        foreach ( $items as $item ) {
            $pad = str_repeat( '&nbsp;', $level * 3 );
            if ( $item->ID == $selected  ) {
                $current = ' selected="selected"';
            }
            elseif ( is_array($selected) ) {
                $current = ( in_array($item->ID, $selected) ) ? ' selected="selected"' : '';
            }
            else {
                $current = '';
            }	
            echo "\n\t<option value='$item->ID'$current>$pad " . get_the_title($item->ID) . "</option>";
            get_tp_wp_pages( $sort_column, $sort_order, $selected, $post_type, $item->ID,  $level + 1 );
        }
    } else {
        return false;
    }
}

/**
 * Add publication as post
 * @param string $title
 * @param string $bibtex_key
 * @param string $date
 * @param string $post_type (default is "post")
 * @param string $tags (separated by comma)
 * @param array $category
 * @return int
 * @since 4.2.0
 */
function tp_add_publication_as_post ($title, $bibtex_key, $date, $post_type = 'post', $tags = '', $category = array()) {
    $content = str_replace('[key]', 'key="' . $bibtex_key . '"', get_tp_option('rel_content_template') );
     
    $post_id = wp_insert_post(array(
      'post_title' => $title,
      'post_content' => $content,
      'tags_input' => $tags,
      'post_date' => $date . " 12:00:00",
      'post_date_gmt' => $date . " 12:00:00",
      'post_type' => $post_type,
      'post_status' => 'publish',
      'post_category' => $category,
      ));
    return $post_id;
}

/** 
 * Copy courses
 * @param array $checkbox   ID of the course you want to copy
 * @param string $copysem   semester
 * @todo Needs fixing!!!!!
*/
function tp_copy_course($checkbox, $copysem) {
    global $wpdb;
    $counter = 0;
    $counter2 = 0;
    $sub = array('number' => 0);
    for( $i = 0; $i < count( $checkbox ); $i++ ) {
        $checkbox[$i] = intval($checkbox[$i]);
        $row = tp_courses::get_course($checkbox[$i]);
        $daten[$counter]['id'] = $row->course_id;
        $daten[$counter]['name'] = $row->name;
        $daten[$counter]['type'] = $row->type;
        $daten[$counter]['room'] = $row->room;
        $daten[$counter]['lecturer'] = $row->lecturer;
        $daten[$counter]['date'] = $row->date;
        $daten[$counter]['places'] = $row->places;
        $daten[$counter]['start'] = $row->start;
        $daten[$counter]['end'] = $row->end;
        $daten[$counter]['semester'] = $row->semester;
        $daten[$counter]['comment'] = $row->comment;
        $daten[$counter]['rel_page'] = $row->rel_page;
        $daten[$counter]['parent'] = $row->parent;
        $daten[$counter]['visible'] = $row->visible;
        $daten[$counter]['waitinglist'] = $row->waitinglist;
        $daten[$counter]['image_url'] = $row->image_url;
        $counter++;
        // copy parents
        if ( $daten[$i]['parent'] == 0) {
             $merke[$counter2] = $daten[$i]['id'];
             $daten[$i]['semester'] = $copysem;
             tp_courses::add_course($daten[$i], $sub);
             $counter2++;
        }
    }	
    // copy childs
    for( $i = 0; $i < $counter ; $i++ ) {
        if ( $daten[$i]['parent'] != 0 ) {
            continue;
        }
        // check if where is a parent for the current course
        $test = 0;
        for( $j = 0; $j < $counter2 ; $j++ ) {
             if ( $daten[$i]['parent'] == $merke[$j]) {
                  $test = $merke[$j];
             }
        }
        // if is true
        if ($test != 0) {
             // search the parent
             for( $k = 0; $k < $counter ; $k++ ) {
                  if ( $daten[$k]['id'] == $test) {
                       $suche = "SELECT `course_id` FROM " . TEACHPRESS_COURSES . " WHERE `name` = '" . $daten[$k]['name'] . "' AND `type` = '" . $daten[$k]['type'] . "' AND `room` = '" . $daten[$k]['room'] . "' AND `lecturer` = '" . $daten[$k]['lecturer'] . "' AND `date` = '" . $daten[$k]['date'] . "' AND `semester` = '$copysem' AND `parent` = 0";
                       $suche = $wpdb->get_var($suche);
                       $daten[$i]['parent'] = $suche;
                       $daten[$i]['semester'] = $copysem;
                       tp_courses::add_course($daten[$i], $sub);					
                  }
             }
        }
        // if is false: create copy directly
        else {
             $daten[$i]['semester'] = $copysem;
             tp_courses::add_course($daten[$i], $sub);
        }
          
     }
}

/**
 * Set screen options
 * @param string $status
 * @param string $option
 * @param string $value
 * @since 4.2.0
 */
function tp_set_screen_option($status, $option, $value) {
    if ( 'tp_pubs_per_page' == $option || 'tp_tags_per_page' == $option ) { 
        return $value; 
    }
}
add_filter('set-screen-option', 'tp_set_screen_option', 10, 3);