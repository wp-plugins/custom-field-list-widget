<?php
/*
Plugin Name: Custom Field List Widget
Plugin URI: http://undeuxoutrois.de/custom_field_list_widget.shtml
Description: This plugin creates sidebar widgets with lists of the values of a custom field (name). The listed values can be (hyper-)linked in different ways.
Author: Tim Berger
Version: 0.9.3
Author URI: http://undeuxoutrois.de/custom_field_list_widget.shtml
Min WP Version: 2.5
Max WP Version: 2.8
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
if ( ! defined( 'CUSTOM_FIELD_LIST_WIDGET_DIR' ) ) { define( 'CUSTOM_FIELD_LIST_WIDGET_DIR', WP_PLUGIN_DIR.'/'.dirname(plugin_basename(__FILE__)) ); }
if ( ! defined( 'CUSTOM_FIELD_LIST_WIDGET_URL' ) ) { define( 'CUSTOM_FIELD_LIST_WIDGET_URL', WP_PLUGIN_URL.'/'.dirname(plugin_basename(__FILE__)) ); }


// load the translation file
if (function_exists('load_plugin_textdomain')) {
	load_plugin_textdomain( 'customfieldlist', str_replace(ABSPATH, '', CUSTOM_FIELD_LIST_WIDGET_DIR) );
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
		$header =  __('Custom Field List','customfieldlist');
	}
	
	echo $before_widget."\n";
		echo $before_title.$header.$after_title . "\n";
		echo '<input type="hidden" name="customfieldlist_widget_id" value="'.$number.'"'." />\n";
		if ('yes' === $opt['partlist'] AND $partlength >= 3) {
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
				switch ($opt['list_layout']) {
					case 'individual_href':
						if ( (defined('DB_COLLATE') AND '' != DB_COLLATE) OR (isset($opt['db_collate']) AND !empty($opt['db_collate'])) ) {
							if ( '' == DB_COLLATE ) {
								$collation_string = $opt['db_collate'];
							} else {
								$collation_string = DB_COLLATE;
							}
							$querystring = 'SELECT pm.meta_id FROM '.$wpdb->postmeta.' AS pm LEFT JOIN '.$wpdb->posts.' AS p ON (p.ID = pm.post_id) WHERE pm.meta_key = "'.$opt['customfieldname'].'"'.$only_public.' ORDER BY pm.meta_value COLLATE '.$collation_string.', LENGTH(pm.meta_value)';
						} else {
							$querystring = 'SELECT pm.meta_id, pm.meta_value FROM '.$wpdb->postmeta.' AS pm LEFT JOIN '.$wpdb->posts.' AS p ON (p.ID = pm.post_id) WHERE pm.meta_key = "'.$opt['customfieldname'].'"'.$only_public.' ORDER BY pm.meta_value, LENGTH(pm.meta_value)';
						}
						$meta_values =  $wpdb->get_results($querystring);
						$nr_meta_values = count($meta_values);
						
						if ($nr_meta_values > 0) {
							foreach ($meta_values as $meta_value) {
								$meta_values_array[$meta_value->meta_id]=$meta_value->meta_value;
							}
							$meta_unique_values=array_unique($meta_values_array);
							foreach ($meta_unique_values as $meta_id => $meta_value) {
								$descr = attribute_escape($opt['individual_href']['descr'][$meta_id]);
								if ('none' == $opt['individual_href']['id'][$meta_id]) {
									$url = trim(urldecode($opt['individual_href']['link'][$meta_id]));
									if ('' == $url) {
										echo "\n".'<li name="customfieldlistelements_'.$number.'_'.$j.'">'.$meta_value."</li>\n";
									} else {
										echo "\n".'<li name="customfieldlistelements_'.$number.'_'.$j.'"><a href="'.$url.'" title="'.$descr.'">'.$meta_value."</a></li>\n";
									}
								} elseif (NULL == $opt['individual_href']['id'][$meta_id]) { // if there is no such meta_id in (post_)id array
									echo "\n".'<li name="customfieldlistelements_'.$number.'_'.$j.'">'.$meta_value."</li>\n";
								} else {
									echo "\n".'<li name="customfieldlistelements_'.$number.'_'.$j.'"><a href="'.get_permalink(intval($opt['individual_href']['id'][$meta_id])).'" title="'.$descr.'">'.$meta_value."</a></li>\n";
								}
								$k++;
								if (  ($k > 0) AND ($partlength < $nr_meta_values) AND 0 === ($k % $partlength) ) {//($k > 0) AND ($partlength < $nr_meta_values) AND
									$j++;
								}
							}
						} else {
							echo "<li>".sprintf(__('There are no values in connection to the custom field name "%1$s" in the data base.','customfieldlist'), $opt['customfieldname'])."</li>\n";
						}
					break;
					case 'each_element_with_sub_element':
					case 'standard':
					default:
						if ( (defined('DB_COLLATE') AND '' != DB_COLLATE) OR (isset($opt['db_collate']) AND !empty($opt['db_collate'])) ) {
							if ( '' == DB_COLLATE ) {
								$collation_string = $opt['db_collate'];
							} else {
								$collation_string = DB_COLLATE;
							}
							$querystring = 'SELECT pm.post_id, pm.meta_value, p.guid, p.post_title FROM '.$wpdb->postmeta.' AS pm LEFT JOIN '.$wpdb->posts.' AS p ON (p.ID = pm.post_id) WHERE pm.meta_key = "'.$opt['customfieldname'].'"'.$only_public.' ORDER BY pm.meta_value COLLATE '.$collation_string.', LENGTH(pm.meta_value)';
						} else {
							$querystring = 'SELECT pm.post_id, pm.meta_value, p.guid, p.post_title FROM '.$wpdb->postmeta.' AS pm LEFT JOIN '.$wpdb->posts.' AS p ON (p.ID = pm.post_id) WHERE pm.meta_key = "'.$opt['customfieldname'].'"'.$only_public.' ORDER BY pm.meta_value, LENGTH(pm.meta_value)';
						}
						
						$meta_values =  $wpdb->get_results($querystring);
						$nr_meta_values = count($meta_values);
						
						if ($nr_meta_values > 0) {
							if ( 'lastword' === $opt['orderelement'] ) {
								$mvals=array();
								$old_locale = setlocale(LC_COLLATE, "0");
								
								if (FALSE !== strpos(strtolower(php_uname('s')), 'win') AND function_exists('mb_convert_encoding')) {
									for ( $i=0; $i < $nr_meta_values; $i++ ) {
										$mvals[] = mb_convert_encoding(str_replace("_", " ", end(preg_split("/\s+/u", $meta_values[$i]->meta_value, -1, PREG_SPLIT_NO_EMPTY))), $opt['encoding_for_win']);
									}
									// build the charset name and setlocale on Windows machines 
									$loc = setlocale(LC_COLLATE, $opt['win_country_codepage']);
								} else {
									for ( $i=0; $i < $nr_meta_values; $i++ ) {
										$mvals[] = str_replace("_", " ", end(preg_split("/\s+/u", $meta_values[$i]->meta_value, -1, PREG_SPLIT_NO_EMPTY)));
									}
									// build the charset name and setlocale on Linux (or other) machines 
									$loc = setlocale(LC_COLLATE, WPLANG.'.'.DB_CHARSET);
								}
								
								// sort the meta_values
								asort($mvals, SORT_LOCALE_STRING);
								
								//turn the locale back
								$loc=setlocale(LC_COLLATE, $old_locale);
								
								// get the keys with the new order
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
									if (($nr_meta_values-1) == $i) {
										$meta_value_plus_one = "";
									} else {
										$meta_value_plus_one = str_replace("_", " ", $meta_values[(intval($mval_keys[$i+1]))]->meta_value);
									}
									$key = intval($mval_keys[$i]);						
								} else {
									$meta_value = str_replace("_", " ", $meta_values[$i]->meta_value);
									$meta_value_minus_one = str_replace("_", " ", $meta_values[($i-1)]->meta_value);
									$meta_value_plus_one = str_replace("_", " ", $meta_values[($i+1)]->meta_value);
									$key = $i;
								}
								
								switch ($opt['list_layout']) {
									case 'each_element_with_sub_element' :
										$singlevisit = TRUE;
										if ( $meta_value != $meta_value_minus_one AND $meta_value == $meta_value_plus_one AND $nr_meta_values > 1 ) {
											echo "\t<li name=".'"customfieldlistelements_'.$number.'_'.$j.'"'.">\n\t".$meta_value."\n\t".'<ul>'."\n";
											$singlevisit = FALSE;
											$k++;
										}
										if ( $meta_value == $meta_value_minus_one OR $meta_value == $meta_value_plus_one AND $nr_meta_values > 1 ) {
											echo "\t\t".'<li><a href="'.get_permalink($meta_values[$key]->post_id).'" title="'.$meta_value." ".__('in','customfieldlist')." ".$meta_values[$key]->post_title.'">'.$meta_values[$key]->post_title."</a></li>\n";
											$singlevisit = FALSE;
										}
										if ( $meta_value == $meta_value_minus_one AND $meta_value != $meta_value_plus_one OR ($i == ($nr_meta_values-1) AND FALSE === $singlevisit) ) {
											echo "\t</ul>\n\t</li>\n";
										}
										
										if ( $singlevisit === TRUE ) {
											echo "\t".'<li name="customfieldlistelements_'.$number.'_'.$j.'">'.$meta_value.'<ul><li><a href="'.get_permalink($meta_values[$key]->post_id).'" title="'.$meta_value." ".__('in','customfieldlist')." ".$meta_values[$key]->post_title.'">'.$meta_values[$key]->post_title."</a></li></ul></li>\n";
											$k++;
										}
									break;
									default :
										$singlevisit = TRUE;
										if ( $meta_value != $meta_value_minus_one AND $meta_value == $meta_value_plus_one AND $nr_meta_values > 1 ) {
											echo "\t<li name=".'"customfieldlistelements_'.$number.'_'.$j.'"'.">\n\t".'<span class="customfieldtitle">'.$meta_value.'</span> <span class="customfieldplus">[ - ]</span>'."<br />\n\t".'<ul class="customfieldsublist">'."\n";
											$singlevisit = FALSE;
											$k++;
										}
										if ( $meta_value == $meta_value_minus_one OR $meta_value == $meta_value_plus_one AND $nr_meta_values > 1 ) {
											echo "\t\t".'<li><a href="'.get_permalink($meta_values[$key]->post_id).'" title="'.$meta_value." ".__('in','customfieldlist')." ".$meta_values[$key]->post_title.'">'.$meta_values[$key]->post_title."</a></li>\n";
											$singlevisit = FALSE;
										}
										if ( $meta_value == $meta_value_minus_one AND $meta_value != $meta_value_plus_one OR ($i == ($nr_meta_values-1) AND FALSE === $singlevisit)  ) {
											echo "\t</ul>\n\t</li>\n";
										}
										
										if ( $singlevisit === TRUE ) {
											echo "\t".'<li name="customfieldlistelements_'.$number.'_'.$j.'"><a href="'.get_permalink($meta_values[$key]->post_id).'" title="'.$meta_value." ".__('in','customfieldlist')." ".$meta_values[$key]->post_title.'">'.$meta_value."</a></li>\n";
											$k++;
										}
									break;
								}
								if (  ($k > 0) AND ($partlength < $nr_meta_values) AND $k !== $k_odd AND 0 === ($k % $partlength) ) {//($k > 0) AND ($partlength < $nr_meta_values) AND
									$j++;
								}
								$k_odd = $k;
							}
						} else {
							echo "<li>".sprintf(__('There are no values in connection to the custom field name "%1$s" in the data base.','customfieldlist'), $opt['customfieldname'])."</li>\n";
						}
					break;
				}
			} else {
				echo "<li>".__('Please, define a custom field name!','customfieldlist')."</li>\n";
			}
		} else {
			echo "<li>".__('Unable to retrieve the data of the customfield list widget from the db.','customfieldlist')."</li>\n";
		}
		echo "</ul><!-- ul end --> \n";
		
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
	echo $after_widget."<!-- after_widget -->\n";
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
			$opt[$widget_number]['header'] = strip_tags(stripslashes(trim($_POST['customfieldlist_opt'][$widget_number]['header'])));
			$opt[$widget_number]['customfieldname'] = strip_tags(stripslashes(trim($_POST['customfieldlist_opt'][$widget_number]['customfieldname'])));
			if ( 'standard' !== $_POST['customfieldlist_opt'][$widget_number]['list_layout'] AND 'each_element_with_sub_element' !== $_POST['customfieldlist_opt'][$widget_number]['list_layout'] AND 'individual_href' !== $_POST['customfieldlist_opt'][$widget_number]['list_layout'] ) {
				$opt[$widget_number]['list_layout'] = 'standard';
			} else {
				$opt[$widget_number]['list_layout'] = $_POST['customfieldlist_opt'][$widget_number]['list_layout'];
			}
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
			$opt[$widget_number]['db_collate'] = strip_tags(stripslashes(trim($_POST['customfieldlist_opt'][$widget_number]['db_collate'])));
			$opt[$widget_number]['win_country_codepage'] = strip_tags(stripslashes(trim($_POST['customfieldlist_opt'][$widget_number]['win_country_codepage'])));
			$opt[$widget_number]['encoding_for_win'] = strip_tags(stripslashes(trim($_POST['customfieldlist_opt'][$widget_number]['encoding_for_win'])));
			
			$opt[$widget_number]['partlength'] = intval(strip_tags(stripslashes(trim($_POST['customfieldlist_opt'][$widget_number]['partlength']))));
			if ( is_nan($opt[$widget_number]['partlength']) OR $opt[$widget_number]['partlength'] < 3 ) {
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
		$header = attribute_escape($opt[$number]['header']);
		$partlength = $opt[$number]['partlength'];
	}

	echo '<p style="text-align:center;">'.__('Header (optional)','customfieldlist').': <input type="text" name="customfieldlist_opt['.$number.'][header]" value="'.$header.'" maxlength="200" /><br /><span style="font-size:0.8em;">('.__('Leave the field empty for no widget title','customfieldlist').')<span></p>';
	
	echo '<p style="text-align:right;">'.__('Custom Field Name','customfieldlist').': <input type="text" id="customfieldname_'.$number.'" name="customfieldlist_opt['.$number.'][customfieldname]" value="'.attribute_escape($opt[$number]['customfieldname']).'" maxlength="200" onchange="javascript:customfieldlist_show_message(\'customfieldlist_opt_'.$number.'_list_layout_opt3_message\');" /></p>';
	
	// section: select the layout
	echo '<div style="text-align:right; margin-bottom:3px;">';
	switch ($opt[$number]['list_layout']) {
		case 'each_element_with_sub_element' :
			$listlayoutopt1chk = '';
			$listlayoutopt2chk = ' checked="checked"';
			$listlayoutopt3chk = '';
		break;
		case 'individual_href' :
			$listlayoutopt1chk = '';
			$listlayoutopt2chk = '';
			$listlayoutopt3chk = ' checked="checked"';
		break;
		case 'standard' :
		default :
			$listlayoutopt1chk = ' checked="checked"';
			$listlayoutopt2chk = '';
			$listlayoutopt3chk = '';
		break;
	}
	echo '<label for="customfieldlist_opt_'.$number.'_list_layout_opt1">'.__('standard layout','customfieldlist').' <input type="radio" name="customfieldlist_opt['.$number.'][list_layout]" id="customfieldlist_opt_'.$number.'_list_layout_opt1" value="standard" '.$listlayoutopt1chk.' /></label>'."<br /> \n";
	echo '<p style="color:#999;">'.__('Only list elements of custom field names with more than one custom field value have sub elements. These sub elements becoming visible by clicking on the custom field name list elements or the + sign. The other list elements with one value are the hyper links to the posts and the values are in the link title.','customfieldlist').'</p>';
	echo '<label for="customfieldlist_opt_'.$number.'_list_layout_opt2">'.__('each element with sub elements','customfieldlist').' <input type="radio" name="customfieldlist_opt['.$number.'][list_layout]" id="customfieldlist_opt_'.$number.'_list_layout_opt2" value="each_element_with_sub_element" '.$listlayoutopt2chk.' /></label>';
	echo '<p style="color:#999;">'.__('Shows each custom field name as a list element with the custom field value as a sub element. All sub elements are every time visible and they are the hyper links to the posts.','customfieldlist').'</p>';
	echo '<label for="customfieldlist_opt_'.$number.'_list_layout_opt3">'.__('a list of all values with manually set links','customfieldlist').' <input type="radio" name="customfieldlist_opt['.$number.'][list_layout]" id="customfieldlist_opt_'.$number.'_list_layout_opt3" value="individual_href" '.$listlayoutopt3chk.' /></label>';
	echo '<p id="customfieldlist_opt_'.$number.'_list_layout_opt3_message" class="error" style="text-align:left; padding-left:1em; padding-right:1em; display:none; margin-bottom:0px;">'.__('You need to save the widget settings before you can set the links for the values of the new custom field name.','customfieldlist').'</p>';
	echo '<p style="color:#999; margin-bottom:2em;">'.__('A simple list of all custom field values of one custom field (name). Each value can be linked individually.','customfieldlist');
	echo ' <a href="'.CUSTOM_FIELD_LIST_WIDGET_URL.'/widget_custom_field_list_individual_href.php?height=400&width=750&abspath='.(urlencode(ABSPATH)).'&number='.$number.'&_wpnonce='.wp_create_nonce('customfieldlist_individual_href_security').'" class="thickbox" title="'.sprintf(__('Set a Link for each custom field value of the custom field: %1$s','customfieldlist'), $opt[$number]['customfieldname']).'">'.__('Set the Links','customfieldlist').'</a>'.'</p>';
	echo '</div>';
	
	// section: select DB_CHARSET
	if (FALSE == defined('DB_COLLATE')) {
		echo '<p style="text-align:right;"><a href="http://dev.mysql.com/doc/refman/5.1/en/charset-charsets.html" target="_blank">'.__('database collation','customfieldlist').'</a>: <input type="text" name="customfieldlist_opt['.$number.'][db_collate]" value="'.attribute_escape($opt[$number]['db_collate']).'" maxlength="200" /></p>';
	}
	
	// section: "sort by the last word" preferences
	$old_locale = setlocale(LC_COLLATE, "0");
	$loc = setlocale(LC_COLLATE, WPLANG.'.'.get_bloginfo('charset'), WPLANG, 'english_usa');
	setlocale(LC_COLLATE, $old_locale);
	if (FALSE === $loc) {
		$message_setloc = '<span class="error" style="display:block; text-align:left;">'.__('This option will probably not work. Because it is not possible to set "setlocale(LC_COLLATE, ... " on this server.','customfieldlist').'</span>';
	} else {
		if (FALSE !== strpos(strtolower(php_uname('s')), 'win')) {
			if (function_exists('mb_convert_encoding')) {
				// the encoding which PHP multibyte supports  http://www.php.net/manual/en/mbstring.supported-encodings.php (without these: 'UTF-32', 'UTF-32BE', 'UTF-32LE', 'UTF-16', 'UTF-16BE', 'UTF-16LE', 'UTF-7', 'UTF7-IMAP', 'UTF-8',
				$encodings = array('UCS-4' => 'UCS-4', 'UCS-4BE' => 'UCS-4BE', 'UCS-4LE' => 'UCS-4LE', 'UCS-2' => 'UCS-2', 'UCS-2BE' => 'UCS-2BE', 'UCS-2LE' => 'UCS-2LE', 'ASCII' => 'ASCII', 'EUC-JP' => 'EUC-JP', 'SJIS' => 'SJIS', 'eucJP-win' => 'eucJP-win', 'SJIS-win' => 'SJIS-win', 'ISO-2022-JP' => 'ISO-2022-JP', 'JIS' => 'JIS', 'ISO-8859-1' => 'ISO-8859-1', 'ISO-8859-2' => 'ISO-8859-2', 'ISO-8859-3' => 'ISO-8859-3', 'ISO-8859-4' => 'ISO-8859-4', 'ISO-8859-5' => 'ISO-8859-5', 'ISO-8859-6' => 'ISO-8859-6', 'ISO-8859-7' => 'ISO-8859-7', 'ISO-8859-8' => 'ISO-8859-8', 'ISO-8859-9' => 'ISO-8859-9', 'ISO-8859-10' => 'ISO-8859-10', 'ISO-8859-13' => 'ISO-8859-13', 'ISO-8859-14' => 'ISO-8859-14', 'ISO-8859-15' => 'ISO-8859-15', 'byte2be' => 'byte2be', 'byte2le' => 'byte2le', 'byte4be' => 'byte4be', 'byte4le' => 'byte4le', 'BASE64' => 'BASE64', 'HTML-ENTITIES' => 'HTML-ENTITIES', '7bit' => '7bit', '8bit' => '8bit', 'EUC-CN' => 'EUC-CN', 'CP936' => 'CP936', 'HZ' => 'HZ', 'EUC-TW' => 'EUC-TW', 'CP950' => 'CP950', 'BIG-5', 'EUC-KR' => 'EUC-KR', 'UHC' => 'CP949', 'ISO-2022-KR' => 'ISO-2022-KR', 'Windows-1251' => 'CP1251', 'Windows-1252' => 'CP1252', 'IBM866' => 'CP866', 'KOI8-R' => 'KOI8-R');
				$message_os = '<span class="updated" style="display:block; text-align:left; margin-bottom:30px;">'.__('The server OS is Windows (which is not able to sort UTF-8) what makes it necessary to:','customfieldlist').'<br />';
				$message_os .= __('1. enter your <a href="http://msdn.microsoft.com/en-gb/library/39cwe7zf.aspx" target="_blank">language</a> and <a href="http://msdn.microsoft.com/en-gb/library/cdax410z.aspx" target="_blank">country</a> name and eventually the <a href="http://en.wikipedia.org/wiki/Windows_code_pages" target="_blank">code page number</a> (like german_germany or german_germany.1252 for German)','customfieldlist').': <input type="text" name="customfieldlist_opt['.$number.'][win_country_codepage]" value="'.attribute_escape($opt[$number]['win_country_codepage']).'" maxlength="200" style="width:100%;" /><br />';
				$message_os .= __('2. select the (same) code page in the form PHP can handle (e.g. Windows-1252 for German)','customfieldlist').': ';
				$message_os .= '<select name="customfieldlist_opt['.$number.'][encoding_for_win]">';
				foreach ($encodings as $keyname => $encoding) {
					$stored_encoding = attribute_escape($opt[$number]['encoding_for_win']);
					if ($encoding == $stored_encoding) {
						$message_os .= '<option value="'.$encoding.'" selected="selected">'.$keyname.'</option>';
					} else {
						$message_os .= '<option value="'.$encoding.'">'.$keyname.'</option>';
					}
				}
				$message_os .= '</select>';
				$message_os .= '</span>';
				$message_os_asterisk = ' class="updated"';
			} else {
				$message_os = '<span class="error" style="display:block;">'.__('This option will probably not work on this server because this plugin converts the encoding of the meta values to the encoding of the OS (Windows) with the function mb_convert_encoding but this function is not available.','customfieldlist').'</span>';
			}
		} else {
			$message_os = '';
		}
		$message_setloc = '';
	}
	if ( 'lastword' === $opt[$number]['orderelement'] ) {
		echo '<p style="text-align:right;"'.$message_os_asterisk.'>'.__('sort the values by the last word','customfieldlist').': <input type="checkbox" name="customfieldlist_opt['.$number.'][orderelement]" value="lastword" checked="checked" /></p>'.$message_os.$message_setloc.'';
	} else {
		echo '<p style="text-align:right;"'.$message_os_asterisk.'>'.__('sort the values by the last word','customfieldlist').': <input type="checkbox" name="customfieldlist_opt['.$number.'][orderelement]" value="lastword" /></p>'.$message_os.$message_setloc.'';
	}
	if ( 'yes' == $opt[$number]['partlist'] ) {
		echo '<p style="text-align:right;">'.__('show only a part of the list elements at once','customfieldlist').': <input type="checkbox" name="customfieldlist_opt['.$number.'][partlist]" value="yes" checked="checked" /></p>';
	} else {
		echo '<p style="text-align:right;">'.__('show only a part of the list elements at once','customfieldlist').': <input type="checkbox" name="customfieldlist_opt['.$number.'][partlist]" value="yes" /></p>';
	}
	echo '<p style="text-align:right;">'.__('elements per part of the list','customfieldlist').' (X>=3): <input type="text" name="customfieldlist_opt['.$number.'][partlength]" value="'.$partlength.'" maxlength="5" style="width:5em;" /></p>';
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
		'width' => 500,
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
	$scriptfile = CUSTOM_FIELD_LIST_WIDGET_URL.'/widget_custom_field_list_js.php';
	wp_enqueue_script( 'customfieldlist_widget_script',  $scriptfile , array('jquery') );
}

add_action('wp_print_styles', 'customfieldlist_widget_style');
function customfieldlist_widget_style() {
	$stylefile = CUSTOM_FIELD_LIST_WIDGET_URL.'/widget_custom_field_list.css';
	wp_enqueue_style( 'customfieldlist_widget_style', $stylefile );
}


add_action('admin_print_scripts-widgets.php', 'customfieldlist_widget_admin_script');
function customfieldlist_widget_admin_script() {
	?>
	<script type="text/javascript">
	//<![CDATA[
	function customfieldlist_show_message(cell_id) {
		var cell = document.getElementById(cell_id);
		if ( cell.style.display == 'none' ) {
			cell.style.display = 'block';
		} 
	}
	//]]>
	</script>
	<?php
}

add_action('init', 'customfieldlist_widget_enqueue_thickbox');
function customfieldlist_widget_enqueue_thickbox() {
	global $pagenow;
	if ( 'widgets.php' == basename($_SERVER[ 'REQUEST_URI' ]) ) {
		wp_enqueue_script( 'thickbox' );
	}
}

add_action('admin_print_styles-widgets.php', 'customfieldlist_widget_admin_style');
function customfieldlist_widget_admin_style() {
	wp_enqueue_style( 'thickbox' );
}
?>