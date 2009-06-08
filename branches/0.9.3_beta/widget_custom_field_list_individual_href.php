<?php 
if (isset($_GET['abspath'])) {
	require_once( urldecode($_GET['abspath']) . "wp-config.php");
	if ( FALSE == function_exists('wp_verify_nonce') or FALSE == wp_verify_nonce($_GET['_wpnonce'], 'customfieldlist_individual_href_security') ) {
		die (__('Security Check failed!','customfieldlist')); 
	}
	if ( TRUE == function_exists('is_user_logged_in') and TRUE == is_user_logged_in() ) {
		customfieldlist_print_action_list(intval($_GET['number']));
	} else {
		die ('Please do not load this page directly.');
	}
} else {
	die ('Please do not load this page directly.');
}

function customfieldlist_print_action_list($number) {
global $wpdb;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
	<head profile="http://gmpg.org/xfn/11">
		<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
		<title>customfieldlist_choose_an_action</title>
		<script type="text/javascript">
		//<![CDATA[
			function customfieldlist_macheRequest(widget_number) {
				http_request = false;
				var meta_id_values = document.getElementsByName('customfieldlist_individual_href_meta_ids[]');
				var id_values = document.getElementsByName('customfieldlist_individual_href_ids[]');
				var link_values = document.getElementsByName('customfieldlist_individual_href_links[]');
				var linkdescription_values = document.getElementsByName('customfieldlist_individual_href_link_descriptions[]');
				if ( meta_id_values.length == 0 || id_values.length == 0 || link_values.length == 0 || linkdescription_values.length == 0 ) {
					alert('<?php echo js_escape(__('Error: Could not process the formular data.','customfieldlist')); ?>');
					return;
				}
				var identifier = ''; 
				var print_id_array = '';
				var print_link_array = '';
				var print_linkdescription_array = '';
				for (var i = 0; i < (id_values.length); i++) {
					if ( (String(id_values[i].value) != 'none') || (String(link_values[i].value) != '') ) { // save only the values which are not 'none' or empty (saves db space and reduces the amount of data which should be transfered to the db)
						//post_ids
						if ( i == 0 ) { identifier = 'id[' + meta_id_values[i].value + ']='; } else { identifier = ('&id[' + meta_id_values[i].value + ']='); }
						print_id_array += identifier + id_values[i].value;
						//URLs
						identifier = ('&link[' + meta_id_values[i].value + ']=');
						if ( String(id_values[i].value) == 'none' ) {
							print_link_array += identifier + encodeURI(link_values[i].value);
						} else {
							print_link_array += identifier + '';
						}
						//Descriptions
						identifier = ('&descr[' + meta_id_values[i].value + ']=');
						print_linkdescription_array += identifier + linkdescription_values[i].value;
					}
				}
				if ((print_id_array + print_link_array).length > 29900) {
					alert('<?php echo js_escape(__('The formular contains to much data. It is not possible to send them to the database.','customfieldlist')); ?>');
					return;
				}
				if (window.XMLHttpRequest) { // Mozilla, Safari,...
					http_request = new XMLHttpRequest();
					if (http_request.overrideMimeType) {
						http_request.overrideMimeType('text/html');
					}
				} else if (window.ActiveXObject) { // IE
					try {
						http_request = new ActiveXObject("Msxml2.XMLHTTP");
					} catch (e) {
						try {
							http_request = new ActiveXObject("Microsoft.XMLHTTP");
						} catch (e) {}
					}
				}
				if (!http_request) {
					alert('<?php echo js_escape(__('It is not possible to create an XMLHTTP instance.','customfieldlist')); ?>');
					return false;
				}
				var cell_id = 'customfieldlist_individual_href_wrap';
				var old_cell_content = document.getElementById(cell_id).innerHTML;
				var button = document.getElementById('customfieldlist_individual_href_save1');
				button.disabled=true;
				button.style.display='none';
				button = document.getElementById('customfieldlist_individual_href_save2');
				button.disabled=true;
				button.style.display='none';
				document.getElementById(cell_id).innerHTML = '<div style="background-color:#fffccc; border:1px solid #FFDBCC; vertical-align:middle; padding:1em; margin-top:0em; font-size:0.8em; font-weight:normal;"><img src="<?php echo get_option('siteurl').'/'.WPINC; ?>/js/thickbox/loadingAnimation.gif" style="vertical-align:middle;" /> <?php echo js_escape(__('Saving the data','customfieldlist')); ?>... </div>';
				http_request.open('POST', '<?php echo CUSTOM_FIELD_LIST_WIDGET_URL;?>/widget_custom_field_list_individual_href_save_data.php', true);
				http_request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
				http_request.send( print_id_array + print_link_array + print_linkdescription_array + '&widget_number=' + widget_number +'&abspath=<?php echo urlencode(ABSPATH); ?>' + '&_ajax_nonce=<?php echo wp_create_nonce('customfieldlist_dbaction_security'); ?>' );
				http_request.onreadystatechange = function() { customfieldlist_alertInhalt(cell_id, old_cell_content); }
			}
			
			function customfieldlist_alertInhalt(cell_id, old_cell_content) {
				switch (http_request.readyState) {
					case 0 : // UNINITIALIZED
					case 1 : // LOADING
					case 2 : // LOADED
					case 3 : // INTERACTIVE
						break;
					case 4 : // COMPLETED
						if (http_request.status == 200) {
							document.getElementById(cell_id).innerHTML = '<div class="updated" style="background-color:#C1FFC1; border-color:#4EEE94; vertical-align:middle; padding:1em; font-weight:normal;"><?php echo js_escape(__('Data saved!','customfieldlist')); ?></div>';
							//tb_remove();
						} else {
							document.getElementById(cell_id).innerHTML = '<div class="error" style="vertical-align:middle; padding:1em; font-weight:normal;"><?php echo js_escape(__('There was a problem with the request. (Probably no data saved)','customfieldlist')); ?> ' + http_request.status + '</div>';
						}
						break;
					default : ; // fehlerhafter Status
				}
			}
		//]]>
		</script>
	</head>
	<body>
	<?php 
	$options = get_option('widget_custom_field_list');
 	if ( !isset($options[$number]) ) {
		return;
	} else {
		$opt = $options[$number];
	}
	if ('' != trim($opt['customfieldname'])) {
		if ( (defined('DB_COLLATE') AND '' != DB_COLLATE) OR (isset($opt['db_collate']) AND !empty($opt['db_collate'])) ) {
			if ( '' == DB_COLLATE ) {
				$collation_string = $opt['db_collate'];
			} else {
				$collation_string = DB_COLLATE;
			}
			//$querystring = 'SELECT DISTINCT CAST(meta_value as BINARY) as meta_value FROM '.$wpdb->postmeta.' WHERE meta_key = '."'".$opt['customfieldname']."'".$only_public.' ORDER BY meta_value COLLATE '.$collation_string.', LENGTH(meta_value)';
			$querystring = 'SELECT meta_id, meta_value FROM '.$wpdb->postmeta.' WHERE meta_key = '."'".$opt['customfieldname']."'".' ORDER BY meta_value COLLATE '.$collation_string.', LENGTH(meta_value)';
		} else {
			$querystring = 'SELECT meta_id, meta_value FROM '.$wpdb->postmeta.' WHERE meta_key = '."'".$opt['customfieldname']."'".' ORDER BY meta_value, LENGTH(meta_value)';
		}
		$meta_values =  $wpdb->get_results($querystring);
		$nr_meta_values = count($meta_values);
		
		if ($nr_meta_values > 0) {
			foreach ($meta_values as $meta_value) {
				$meta_values_array[$meta_value->meta_id]=$meta_value->meta_value;
			}
			$meta_unique_values=array_unique($meta_values_array);
			
			// get all post titles and IDs
			$querystring = 'SELECT ID, post_title FROM '.$wpdb->posts." WHERE post_type='post' or post_type='page' ORDER BY ID ASC";
			$post_titles_and_IDs =  $wpdb->get_results($querystring);
			$nr_post_titles_and_IDs = count($post_titles_and_IDs);
			
			echo '<p>'.__('You can specify links to posts or pages of your blog or enter different adresses. If you choose a post or a page title then the custom field value will be linked to that post or page and not to a manually set link.<br />Please, write the URLs with a http:// (or https://, ftp://, etc.) in front of the address.<br />You can also enter link descriptions which appear while you hold the mouse cursor over the links.','customfieldlist').'</p>';
			?>		
			<p class="submit" style="text-align:center;">
				<input type="button" id="customfieldlist_individual_href_save1" value="<?php _e('Save', 'customfieldlist'); ?>" onclick="javascript:customfieldlist_macheRequest('<?php echo $number; ?>');" style="padding-left:4em; padding-right:4em;" />
			</p>
			<?php
			echo '<div id="customfieldlist_individual_href_wrap">';
			$selection = FALSE;
			$i=0;
			foreach ($meta_unique_values as $meta_id => $meta_value) {
				$output='';
				if ( fmod($i, 2) != 0 ) { $styleclass = ' class="alternate"'; } else { $styleclass = ''; }
				echo '<div'.$styleclass.' style="padding:1em;">'.sprintf(__('Link "%1$s" to','customfieldlist'), $meta_value).' ';
				echo '<input name="customfieldlist_individual_href_meta_ids[]" type="hidden" value="'.strval($meta_id).'" />';
				echo '<select name="customfieldlist_individual_href_ids[]">';
				foreach ($post_titles_and_IDs as $post_title_and_ID) {
					if ($post_title_and_ID->ID == $opt['individual_href']['id'][$meta_id]) {
						$selected = ' selected="selected"';
						$selection = TRUE;
					} else {
						$selected = '';
					}
					$output .= '<option value="'.$post_title_and_ID->ID.'"'.$selected.'>'.$post_title_and_ID->post_title.'</option>';
				}
				if (TRUE === $selection) {
					echo '<option value="none">-</option>';
				} else {
					echo '<option value="none" selected="selected">-</option>';
				}
				echo $output;
				echo '</select>';
				echo ' '.__('or', 'customfieldlist').' ';
				echo '<input type="text" name="customfieldlist_individual_href_links[]" value="'.attribute_escape(urldecode($opt['individual_href']['link'][$meta_id])).'" maxlength="400" style="width:20em;" />';
				echo '<br />'.__('link description (title)', 'customfieldlist').': ';
				echo '<input type="text" name="customfieldlist_individual_href_link_descriptions[]" value="'.attribute_escape($opt['individual_href']['descr'][$meta_id]).'" maxlength="400" style="width:20em;" />';
				echo '</div>';
				$i++;
			}
			echo '</div>'; // customfieldlist_individual_href_wrap
			
			?>
			<p class="submit" style="text-align:center;">
				<input type="button" id="customfieldlist_individual_href_save2" value="<?php _e('Save', 'customfieldlist'); ?>" onclick="javascript:customfieldlist_macheRequest('<?php echo $number; ?>');" style="padding-left:4em; padding-right:4em;" />
			</p>
			<?php 
		} else {
			echo '<p>'.sprintf(__('There are no values in connection to the custom field name "%1$s" in the data base.','customfieldlist'), $opt['customfieldname']).'</p>';
		} 
	} else {
		echo '<p>'.__('Please, define a custom field name!','customfieldlist').'</p>';
	} 
	?>
	</body>
</html>
<?php
}
?>