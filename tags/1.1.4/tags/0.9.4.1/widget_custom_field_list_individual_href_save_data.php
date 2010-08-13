<?php
if (isset($_POST['abspath'])) {
	require_once(urldecode($_POST['abspath']).'wp-config.php');
	if ( TRUE == function_exists('is_user_logged_in') and TRUE == is_user_logged_in() ) {
		customfieldlist_save_data(intval($_POST['widget_number']));
	} else {
		die ('Please do not load this page directly.');
	}
} else {
	die ('Please do not load this page directly.');
}

function customfieldlist_save_data($widget_number) {
	if ( TRUE == wp_verify_nonce($_POST['_ajax_nonce'], 'customfieldlist_dbaction_security') ) {
		// Data should be stored as array:  array( number => data for that instance of the widget, ... )
		$opt = get_option("widget_custom_field_list");
		if ( !is_array($opt) ) {
			$opt = array();
		}
		
		$opt[$widget_number]['individual_href']['id'] = $_POST['id'];
		$opt[$widget_number]['individual_href']['link'] = $_POST['link'];
		$opt[$widget_number]['individual_href']['descr'] = $_POST['descr'];
		
		$result = update_option('widget_custom_field_list', $opt);
		/*
		$filename = 'ajaxlog.txt';
		if (is_file($filename)) {chmod ($filename, 0777);}
		$handle = fopen($filename, 'w');
		fputs($handle, var_export($_POST['id'], true)."\n###\n".var_export($_POST['link'], true).var_export($result, true)."\n###\n"."<br />\n");
		$status = fclose($handle);
		if (is_file($filename)) {chmod ($filename, 0700);}
		if (FALSE === $status) {
			echo 'Fehler';
		}
		*/
	} else {
		_e('Security Check failed!','customfieldlist');
	}
}
?>