<?php
if (isset($_POST['abspath'])) {
	require_once(urldecode($_POST['abspath']).'wp-config.php');
	if ( FALSE == function_exists('wp_verify_nonce') or FALSE == wp_verify_nonce($_POST['_ajax_nonce'], 'customfieldlist_dbaction_security') ) {
		die(__('Security Check failed!','customfieldlist')); 
	}
	if ( TRUE == function_exists('is_user_logged_in') and TRUE == is_user_logged_in() ) {
		if ( isset($_POST['widget_number']) AND FALSE === empty($_POST['widget_number'])) {
			customfieldlist_save_data(intval($_POST['widget_number']));
		} else {
			die (__('The widget number was not transmitted.','customfieldlist'));
		}
	} else {
		die(__('You have to be logged in for this action.','customfieldlist'));
	}
} else {
	die ('Please do not load this page directly.');
}

function customfieldlist_save_data($widget_number) {
	// Data should be stored as array:  array( number => data for that instance of the widget, ... )
	$opt = get_option("widget_custom_field_list");
	if ( !is_array($opt) ) {
		$opt = array();
	}
	$opt[$widget_number]['individual_href']['id'] = $_POST['id'];
	$opt[$widget_number]['individual_href']['link'] = $_POST['link'];
	$opt[$widget_number]['individual_href']['descr'] = $_POST['descr'];
	$opt[$widget_number]['individual_href']['thecustomfieldname'] = $_POST['thecustomfieldname'];
	
	$result = update_option('widget_custom_field_list', $opt);
}
?>