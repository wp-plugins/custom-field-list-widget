<?php
/*
Plugin Name: Custom Field List Widget
Plugin URI: http://undeuxoutrois.de/custom_field_list_widget.shtml
Description: This widget lists all values of a custom field, groups equal values and (hyper-) links the values to their posts. || Dieses Widget erzeugt eine Liste aus den Werten eines Spezialfeldes, gruppiert mehrfach vorkommende Werte und verlinkt die Werte ihren Beitr&auml;gen.
Author: Tim Berger
Version: 0.8
Author URI: http://undeuxoutrois.de/custom_field_list_widget.shtml
Min WP Version: 2.5
Max WP Version: 
License: GNU General Public License

Requirements:
	- WP 2.5 or newer
	- a widgets supportting theme

Usage when using "sort values by the last word":
	
	You can influence which word the last word is by using _ between the words. If you make a _ between two words it will be seen as one word.
	
	example:
	names with more than one first and family name
		
		Jon Jake Stewart Brown
		the last word is Brown
		
		Jon Jake Stewart_Brown
		the last word is "Stewart Brown"
		
	The _ will not displayed in the sidebar.
*/


/*  Copyright 2009  Tim Berger  (email : timberge@cs.tu-berlin.de)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA


Parts of this plugin are based on the multiple-widgets-pattern example from the file /wp-includes/widgets.php of WP 2.7.1

*/

// Pre-2.6 compatibility 
if ( ! defined( 'WP_CONTENT_URL' ) ) { define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' ); }
if ( ! defined( 'WP_CONTENT_DIR' ) ) { define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' ); }
if ( ! defined( 'WP_PLUGIN_URL' ) ) { define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' ); }
if ( ! defined( 'WP_PLUGIN_DIR' ) ) { define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' ); }

// load the translation file
if (function_exists('load_plugin_textdomain')) {
	load_plugin_textdomain( 'customfieldlist', str_replace(ABSPATH, '', WP_PLUGIN_DIR.'/'.dirname(plugin_basename(__FILE__))) );
}


// on plugin deactivation
register_deactivation_hook( (__FILE__), 'customfieldlist_on_deactivation' );
function customfieldlist_on_deactivation() { 
	delete_option('widget_custom_field_list');
}

// produces the list in the sidebar
function customfieldlist($args=array(), $widget_args=1) {
	global $wpdb;
	extract( $args, EXTR_SKIP );
	
	if ( is_numeric($widget_args) ) {
		$widget_args = array( 'number' => $widget_args );
	}
	$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
	extract( $widget_args, EXTR_SKIP );
	
	$options = get_option('widget_custom_field_list');
 
	if ( !isset($options[$number]) ) {
		return;
	} else {
		$opt = $options[$number];
	}
	
	$partlength = intval($opt['partlength']);
	
	if ( FALSE !== $opt AND !empty($opt['header']) ) {
		$header = $opt['header'];
	} else {
		$header =  __('Custom Field List','customfieldlist') ;
	}
	
	echo $before_widget."\n";
		echo $before_title.$header.$after_title . "\n";
		echo '<input type="hidden" name="customfieldlist_widget_id" value="'.$number.'"'." />\n";
		if ( 'yes' === $opt['partlist'] AND $partlength >= 3) {
			echo '<input type="hidden" id="customfieldlistpartlist_'.$number.'" value="yes"'." />\n";
		} else {
			echo '<input type="hidden" id="customfieldlistpartlist_'.$number.'" value="no"'." />\n";
		}
		if (is_user_logged_in()) {
			$only_public = '';
		} else {
			$only_public = ' AND p.post_status = "publish"';
		}
		$j=$k=0;
		echo "<ul>\n";
		if (FALSE !== $opt) {
			if ( !empty($opt['customfieldname']) ) {
				$querystring = 'SELECT pm.post_id, pm.meta_value, p.guid, p.post_title FROM '.$wpdb->postmeta.' AS pm LEFT JOIN '.$wpdb->posts.' AS p ON (p.ID = pm.post_id) WHERE pm.meta_key = "'.$opt['customfieldname'].'"'.$only_public.' ORDER BY pm.meta_value ASC';
				$meta_values =  $wpdb->get_results($querystring);
				$nr_meta_values = count($meta_values);
				
				if ( 'lastword' === $opt['orderelement'] ) {
					for ( $i=0; $i < $nr_meta_values; $i++ ) {
						$mvals[] = str_replace("_", " ", end(str_word_count($meta_values[$i]->meta_value, 1, "0123456789_.")));
					}
					asort($mvals);
					$mval_keys = array_keys($mvals);
				}
				
				for ( $i=0; $i < $nr_meta_values; $i++ ) {
					if ( 'lastword' === $opt['orderelement'] ) {
						$meta_value = str_replace("_", " ", $meta_values[intval($mval_keys[$i])]->meta_value);
						if (0 == $i) {
							$meta_value_minus_one = "";
						} else {
							$meta_value_minus_one = str_replace("_", " ", $meta_values[(intval($mval_keys[$i-1]))]->meta_value);
						}
						$meta_value_plus_one = str_replace("_", " ", $meta_values[(intval($mval_keys[$i+1]))]->meta_value);
						$key = intval($mval_keys[$i]);						
					} else {
						$meta_value = str_replace("_", " ", $meta_values[$i]->meta_value);
						$meta_value_minus_one = str_replace("_", " ", $meta_values[($i-1)]->meta_value);
						$meta_value_plus_one = str_replace("_", " ", $meta_values[($i+1)]->meta_value);
						$key = $i;
					}
					$singlevisit = TRUE;
					if ( $meta_value != $meta_value_minus_one AND $meta_value == $meta_value_plus_one ) {
						echo "\t<li name=".'"customfieldlistelements_'.$number.'_'.$j.'"'.">\n\t".'<span class="customfieldtitle">'.$meta_value.'</span> <span class="customfieldplus">[ - ]</span>'."<br />\n\t".'<ul class="customfieldsublist">'."\n";
						$singlevisit = FALSE;
						$k++;
					}
					if ( $meta_value == $meta_value_minus_one OR $meta_value == $meta_value_plus_one ) {
						echo "\t\t".'<li><a href="'.get_permalink($meta_values[$key]->post_id).'" title="'.$meta_value." ".__('in','customfieldlist')." ".$meta_values[$key]->post_title.'">'.$meta_values[$key]->post_title."</a></li>\n";
						$singlevisit = FALSE;
					}
					if ( $meta_value == $meta_value_minus_one AND $meta_value != $meta_value_plus_one ) {
						echo "\t</ul>\n\t</li>\n";
						$singlevisit = FALSE;
					}
					
					if ( $singlevisit === TRUE ) {
						echo "\t".'<li name="customfieldlistelements_'.$number.'_'.$j.'"><a href="'.get_permalink($meta_values[$key]->post_id).'" title="'.$meta_value." ".__('in','customfieldlist')." ".$meta_values[$key]->post_title.'">'.$meta_value."</a></li>\n";
						$k++;
					}
					
					if (  ($k > 0) AND ($partlength < $nr_meta_values) AND $k !== $k_odd AND 0 === ($k % $partlength) ) {//($k > 0) AND ($partlength < $nr_meta_values) AND
						$j++;
					}
					$k_odd = $k;
				}
			} else {
				echo "<li>".__('Please, define a custom field name!','customfieldlist')."</li>";
			}
		} else {
			echo "<li>".__('Unable to retrieve the data of the customfield list widget from the db.','customfieldlist')."</li>";
		}
		echo "</ul>\n";
		echo '<input type="hidden" id="customfieldlistelements_'.$number.'" value="'.$j.'"'." />\n";
		if ($j > 0 AND $k > $partlength) {
		echo '<p class="customfieldlistpages" id="customfieldlistpages_'.$number.'"'.">\n";
		echo __('part','customfieldlist').": ";
			if ( 0 === ($k % $partlength) ) {
				for ($i=0; $i<$j; $i++) {
					echo '[<a href="javascript:show_this_customfieldlistelements('.$i.', '.$j.', '.$number.');"> '.($i+1).' </a>] ';
				}
			} else {
				for ($i=0; $i<=$j; $i++) {
					echo '[<a href="javascript:show_this_customfieldlistelements('.$i.', '.$j.', '.$number.');"> '.($i+1).' </a>] ';
				}
			}
		echo "</p>\n";
		}
	echo $after_widget."\n";
}

/*
 * the control- or preferences panel at the widgets page
 *
 * @param array|int $widget_args Widget number. Which of the several widgets of this type do we mean.
 */
 function customfieldlist_widget_control( $widget_args = 1 ) {
	global $wp_registered_widgets;
	static $updated = false; // Whether or not we have already updated the data after a POST submit
	
	if ( is_numeric($widget_args) ) {
		$widget_args = array( 'number' => $widget_args );
	}
	
	$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
	
	extract( $widget_args, EXTR_SKIP );

	// Data should be stored as array:  array( number => data for that instance of the widget, ... )
	$opt = get_option("widget_custom_field_list");
	if ( !is_array($opt) ) {
		$opt = array();
	}
	
	// We need to update the data
	if ( !$updated && !empty($_POST['sidebar']) ) {
		// Tells us what sidebar to put the data in
		$sidebar = (string) $_POST['sidebar'];
		$sidebars_widgets = wp_get_sidebars_widgets();
		if ( isset($sidebars_widgets[$sidebar]) ) {
			$this_sidebar =& $sidebars_widgets[$sidebar];
		} else {
			$this_sidebar = array();
		}
		foreach ( $this_sidebar as $_widget_id ) {
			// Remove all widgets of this type from the sidebar.  We'll add the new data in a second.  This makes sure we don't get any duplicate data
			// since widget ids aren't necessarily persistent across multiple updates
			if ( 'customfieldlist' == $wp_registered_widgets[$_widget_id]['callback'] && isset($wp_registered_widgets[$_widget_id]['params'][0]['number']) ) {
				$widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];
				if ( !in_array( "customfieldlist-$widget_number", $_POST['widget-id'] ) ) { // the widget has been removed. "many-$widget_number" is "{id_base}-{widget_number}
					unset($opt[$widget_number]);
				}
			}
		}
		
		foreach ( (array) $_POST['customfieldlist-submit'] as $widget_number => $customfieldlist_option ) {
			// compile data from $widget_many_instance
			if ( !isset($_POST['customfieldlist_opt'][$widget_number]) OR !is_array($_POST['customfieldlist_opt'][$widget_number]) ) {// user clicked cancel
				continue;
			}
			$opt[$widget_number]['header'] = attribute_escape(strip_tags(trim($_POST['customfieldlist_opt'][$widget_number]['header'])));
			$opt[$widget_number]['customfieldname'] = attribute_escape(strip_tags(trim($_POST['customfieldlist_opt'][$widget_number]['customfieldname'])));
			if ( isset($_POST['customfieldlist_opt'][$widget_number]['partlist']) ) {
				$opt[$widget_number]['partlist'] = 'yes';
			} else {
				$opt[$widget_number]['partlist'] = 'no';
			}
			if ( isset($_POST['customfieldlist_opt'][$widget_number]['orderelement']) ) {
				$opt[$widget_number]['orderelement'] = 'lastword';
			} else {
				$opt[$widget_number]['orderelement'] = 'firstword';
			}
			$opt[$widget_number]['partlength'] = intval(attribute_escape(strip_tags(trim($_POST['customfieldlist_opt'][$widget_number]['partlength']))));
			if ( $opt[$widget_number]['partlength'] < 3 ) {
				$opt[$widget_number]['partlength'] = 3;
			} 
		}
		update_option("widget_custom_field_list", $opt);
		$updated = true; // So that we don't go through this more than once
	}
	
	// Here we echo out the form
	if ( -1 == $number ) { // We echo out a template for a form which can be converted to a specific form later via JS
		$partlength = 3;
		$header =  __('Custom Field List','customfieldlist');
		$number = '%i%';
	} else {
		$header = $opt[$number]['header'];
		$partlength = $opt[$number]['partlength'];
	}

	echo '<p style="text-align:center;">'.__('Header (optional)','customfieldlist').': <input type="text" name="customfieldlist_opt['.$number.'][header]" value="'.$header.'" maxlength="200" /><br /><span style="font-size:0.8em;">('.__('Leave the field empty for no widget title','customfieldlist').')<span></p>';
	echo '<p style="text-align:center;">'.__('Custom Field Name','customfieldlist').': <input type="text" name="customfieldlist_opt['.$number.'][customfieldname]" value="'.$opt[$number]['customfieldname'].'" maxlength="200" /></p>';
	if ( 'lastword' === $opt[$number]['orderelement'] ) {
		echo '<p style="text-align:center;">'.__('sort the values by the last word','customfieldlist').': <input type="checkbox" name="customfieldlist_opt['.$number.'][orderelement]" value="lastword" checked="checked" /></p>';
	} else {
		echo '<p style="text-align:center;">'.__('sort the values by the last word','customfieldlist').': <input type="checkbox" name="customfieldlist_opt['.$number.'][orderelement]" value="lastword" /></p>';
	}
	if ( 'yes' == $opt[$number]['partlist'] ) {
		echo '<p style="text-align:center;">'.__('show only a part of the list elements at once','customfieldlist').': <input type="checkbox" name="customfieldlist_opt['.$number.'][partlist]" value="yes" checked="checked" /></p>';
	} else {
		echo '<p style="text-align:center;">'.__('show only a part of the list elements at once','customfieldlist').': <input type="checkbox" name="customfieldlist_opt['.$number.'][partlist]" value="yes" /></p>';
	}
	echo '<p style="text-align:center;">'.__('points per part of the list','customfieldlist').' (X>=3): <input type="text" name="customfieldlist_opt['.$number.'][partlength]" value="'.$partlength.'" maxlength="200" /></p>';
	echo '<input type="hidden" id="customfieldlist-submit-'.$number.'" name="customfieldlist-submit['.$number.'][submit]" value="1" />';
}

add_action('widgets_init', 'customfieldlist_widget_init');
function customfieldlist_widget_init() {
	if ( !$options = get_option('widget_custom_field_list') ) {
		$options = array();
	}
	
	// Variables for our widget
	$widget_ops = array(
		'classname' => 'customfieldlist',
		'description' => __('Displays a list of custom field values of a set key', 'customfieldlist')
	);
	
	// Variables for our widget options panel
	$control_ops = array(
		'width' => 400,
		'height' => 310,
		'id_base' => 'customfieldlist'
	);
	
	$registered = false;
	
	foreach ( array_keys($options) as $o ) {
		// Per Automattic: "Old widgets can have null values for some reason"
		if ( !isset($options[$o]['header']) ) {
			continue;
		}
		
		$id = $control_ops['id_base'].'-'.$o;
		
		// Register the widget and then the widget options menu
		wp_register_sidebar_widget($id, __('Custom Field List','customfieldlist'), 'customfieldlist', $widget_ops, array('number' => $o));
		wp_register_widget_control($id, __('Custom Field List','customfieldlist'), 'customfieldlist_widget_control', $control_ops, array('number' => $o));
	}

	if ( !$registered ) {
		wp_register_sidebar_widget($control_ops['id_base'].'-1', __('Custom Field List','customfieldlist'), 'customfieldlist', $widget_ops, array('number' => -1));
		wp_register_widget_control($control_ops['id_base'].'-1', __('Custom Field List','customfieldlist'), 'customfieldlist_widget_control', $control_ops, array('number' => -1));
	}
}

add_action('wp_print_scripts', 'customfieldlist_widget_script');
function customfieldlist_widget_script() {
	wp_enqueue_script( 'jquery' );
	$scriptfile = WP_PLUGIN_URL.'/widget_custom_field_list/widget_custom_field_list_js.php';
	wp_enqueue_script( 'customfieldlist_widget_script',  $scriptfile , array('jquery') );
}

add_action('wp_print_styles', 'customfieldlist_widget_style');
function customfieldlist_widget_style() {
	$stylefile = WP_PLUGIN_URL.'/widget_custom_field_list/widget_custom_field_list.css';
	wp_enqueue_style( 'customfieldlist_widget_style', $stylefile );
}
?>