<?php
if (isset($_GET['abspath'])) {
	require_once( urldecode($_GET['abspath']) . 'wp-config.php');
	if ( FALSE == function_exists('wp_verify_nonce') or FALSE == wp_verify_nonce($_GET['_wpnonce'], 'customfieldlist_long_selectbox_security') ) {
		die ('<p class="error" style="vertical-align:middle; padding:1em; font-weight:normal;">'.__('Security Check failed!','customfieldlist').'</p>'); 
	}
	customfieldlist_print_long_selectbox($_GET['selectboxid']);
} else {
	die ('Please do not load this page directly.');
}

function customfieldlist_print_long_selectbox($selectboxid) {
	echo '<div style="vertical-align:middle; padding:1em; font-weight:normal;" id="customfieldlist_long_selectbox_container">';
	?>
	<script type="text/javascript">
	//<![CDATA[
	jQuery(document).ready(function(){
		//jQuery('#customfieldlist_long_selectbox').append(jQuery('#<?php echo $selectboxid; ?>').html());
		jQuery('#<?php echo $selectboxid; ?>').children().clone().appendTo(jQuery('#customfieldlist_long_selectbox'));
	});
	jQuery('#<?php echo $selectboxid; ?>').attr('disabled', 'disabled');
	jQuery('#<?php echo $selectboxid; ?>').hide();
	jQuery('#TB_window').bind( 'unload', function() {
		jQuery('#<?php echo $selectboxid; ?>').removeAttr('disabled');
		jQuery('#<?php echo $selectboxid; ?>').show();
	});
	//]]>
	</script>
	<?php
	echo '<select id="customfieldlist_long_selectbox" class="customfieldlist_selectbox" onchange="customfieldlistwidget_go_to_target(this.id, this.selectedIndex);">';
	echo '</select>';
	echo '</div>';
	?>
	<script type="text/javascript">
	//<![CDATA[
	jQuery('#customfieldlist_long_selectbox').focus();
	//]]>
	</script>
	<?php
}
?>