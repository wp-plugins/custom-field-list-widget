<?php
/*
Plugin Name: Custom Field List Widget
Plugin URI: http://undeuxoutrois.de/custom_field_list_widget.shtml
Description: This plugin creates sidebar widgets with lists of the values of a custom field (name). The listed values can be (hyper-)linked in different ways.
Author: Tim Berger
Version: 0.9.5
Author URI: http://undeuxoutrois.de/
Min WP Version: 2.5
Max WP Version: 2.8.6
License: GNU General Public License

Requirements:
	- min. WP 2.5 
	- max. WP 2.8.x
	- a widgets supportting theme
	
Localization:
	Bulgarian - Peter Toushkov
	Russian (not updated for v0.9.5) - Michael Comfi (http://www.comfi.com/)
	Uzbek (not updated for v0.9.5) - Alisher Safarov (http://www.comfi.com/) 
	Hindi - Kakesh Kumar (http://kakesh.com/)
	English (default) - Tim Berger
	German - Tim Berger

Usage when using "sort values by the last word":
	
	You can influence which word the last word is by using _ between the words. If you make a _ between two words it will be seen as one word.
	
	example:
	names with more than one first and family name
		
		Jon Jake Stewart Brown
		the last word is Brown
		
		Jon Jake Stewart_Brown
		the last word is "Stewart Brown"
		
	The _ will not displayed in the sidebar.
	

Copyright 2009  Tim Berger  (email : timberge@cs.tu-berlin.de)

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

// #######################################################################################
// max. number of hierarchy steps resp. number of 
if ( ! defined( 'CUSTOM_FIELD_LIST_MAX_HIERARCHY_LEVEL' ) ) { define( 'CUSTOM_FIELD_LIST_MAX_HIERARCHY_LEVEL', 5 ); }
// #######################################################################################

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

function customfieldlist_print_widget_content($n, $number, $partlength, $hierarchymaxlevel , $list_style='standard', $i=0, $j=0, $k=0) {
	if ( $i < ($hierarchymaxlevel-1) ) {
		$i++;
		switch ($list_style) {
			case 'each_element_with_sub_element' :
				foreach ($n as $key => $value) {
					if ( TRUE === is_array($value) ) { 
						echo "\t<li name=".'"customfieldlistelements_'.$number.'_'.$k.'"'.">\n\t".'<span class="customfieldtitle">'.$key.'</span> <span class="customfieldplus">[ - ]</span>'."<br />\n\t";
						echo '<ul class="customfieldsublist">'."\n";
						customfieldlist_print_widget_content($value, $number, $partlength, $hierarchymaxlevel, $list_style, $i, $j, $k);
						echo "</ul>\n";
						echo "</li>\n";
						if ( $i==1 ) { 
							$j++;
						}
						if ( $i==1 AND  0 === ($j % $partlength)  ) {
							$k++;
						}
					} else {
						echo "\t".'<li name="customfieldlistelements_'.$number.'_'.$k.'">'.__('Internal Plugin Error: value is no array', 'customfieldlist')."</li>\n";
					}
				}
			break;
			case 'standard' :
			default :
				foreach ($n as $key => $value) {
					if ( TRUE === is_array($value) AND 1 < count($value) ) { 
						echo "\t<li name=".'"customfieldlistelements_'.$number.'_'.$k.'"'.">\n\t".'<span class="customfieldtitle">'.$key.'</span> <span class="customfieldplus">[ - ]</span>'."<br />\n\t";
						echo '<ul class="customfieldsublist">'."\n";
						customfieldlist_print_widget_content($value, $number, $partlength, $hierarchymaxlevel, $list_style, $i, $j, $k);
						echo "</ul>\n";
						echo "</li>\n";
						if ( $i==1 ) { 
							$j++;
						}
						if ( $i==1 AND  0 === ($j % $partlength)  ) {
							$k++;
						}
					} else {
						if ( TRUE === is_array($value) ) {
							echo "\t".'<li name="customfieldlistelements_'.$number.'_'.$k.'"><a href="'.get_permalink($value[0]['post_id']).'" title="'.$value[0]['post_title'].'">'.$key."</a></li>\n";
							if ( $i == 1 ) { 
								$j++;
							}
							if ( $i == 1 AND  0 === ($j % $partlength)  ) {
								$k++;
							}
						} else {
							echo "\t".'<li name="customfieldlistelements_'.$number.'_'.$k.'">'.__('Internal Plugin Error: value is no array', 'customfieldlist')."</li>\n";
						}
					}
				}
			break;
		}
	} else {
		foreach ($n as $key => $value) {
			echo "\t".'<li name="customfieldlistelements_'.$number.'_'.$k.'"><a href="'.get_permalink($n[$key]['post_id']).'" title="'.$n[$key]['post_title'].'">'.$n[$key]['post_title']."</a></li>\n";
		}
	}
}

function customfieldlist_build_output_array($n, $j=0, $o=array()) {
	if ( TRUE === is_array($n) AND $j < count($n) ) { 
		$k = array_keys($n);
		$o = Array(strval($n[$k[$j]]) => $o);
		$j++;
		$o = customfieldlist_build_output_array($n, $j, $o);
	}
	return $o;
}

// This function is heavily inspired by a example in the comments to the explanation of array_merge_recursive at php.net
function customfieldlist_array_merge($arr, $ins, $hierarchymaxlevel, $i = 0) {
	if ( is_array($arr) ) {
		if ( is_array($ins) ) {
			foreach ( $ins as $k => $v ) {
				$i++;
				if ( isset($arr[$k]) && is_array($v) && is_array($arr[$k]) && $i < $hierarchymaxlevel ) {
					$arr[$k] = customfieldlist_array_merge($arr[$k], $v, $hierarchymaxlevel, $i);
				} else {
					//  add all following data as array element with a new key 
					while ( isset($arr[$k]) ) {
						// add up the key until a key is found which is not already a key in the actual array
						$k++;
					}
					$arr[$k] = $v;
				}
			}
		}
	} elseif ( !is_array($arr) && (strlen($arr)==0 || $arr==0) ) {
		$arr=$ins;
	}
	return($arr);
}

function customfieldlist_remove_empty_array_elements($in) {
	foreach($in as $key => $value) {
		if (FALSE === empty($value)) {
			$out[$key] = $value;
		} 
	}
	return $out;
}

function customfieldlist_are_the_array_elements_empty($ar) {
	foreach ($ar as $ar_val) {
		$strval = trim(strval($ar_val));
		if ( !empty($strval) ) {
			return FALSE;
		}
	}
	return TRUE;
}

function customfieldlist_clean_array_values($in) {
	return (strip_tags(stripslashes(trim($in))));
}


function customfieldlist_get_clean_unique_values($in) {
	$out = array_map('customfieldlist_clean_array_values', $in);
	return customfieldlist_remove_empty_array_elements($out);
}

// helper function - only for development purposes
function customfieldlist_var_dump($var) {
	// write the out put to the log file
	$filename = WP_PLUGIN_DIR.'/widget_custom_field_list/widget_custom_field_list_cronlog.dat';
	if (is_file($filename)) {
		chmod ($filename, 0777);
		if ((filesize($filename)/1024) > 100) { unlink($filename); } // delete the Logfile if it is bigger than 100 kByte
	}
	$handle = fopen($filename, "a");
	fputs($handle, var_export($var, TRUE)."\n");
	$status = fclose($handle);
	if (is_file($filename)) {chmod ($filename, 0644);}
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
		echo $before_title.$header . '' . $after_title . "\n";
		//echo $before_title.$header.$after_title . '<span class="customfieldplusall">[ - ]</span>'. "\n";
		echo '<input type="hidden" name="customfieldlist_widget_id" value="'.$number.'"'." />\n";
		if ('yes' === $opt['partlist'] AND $partlength >= 3) {
			echo '<input type="hidden" id="customfieldlistpartlist_'.$number.'" value="yes"'." />\n";
		} else {
			echo '<input type="hidden" id="customfieldlistpartlist_'.$number.'" value="no"'." />\n";
		}
		if (TRUE === is_user_logged_in()) {
			$only_public = '';
		} else {
			$only_public = ' AND p.post_status = "publish"';
		}
		$j=$k=0;
		
		echo '<ul id="customfieldlist_mainlist_'.$number.'">'."\n";
		if (FALSE !== $opt) {
			if ( is_array($opt['custom_field_names']) AND 1 <= count($opt['custom_field_names']) AND FALSE === customfieldlist_are_the_array_elements_empty($opt['custom_field_names']) ) {
				switch ($opt['list_type']) {
					case 'individual_href' :
						$only_public1='';

						// are both custom field names (which are only possible for that option) in use? 
						$customfieldname_0 = trim($opt['custom_field_names'][0]);
						$customfieldname_1 = trim($opt['custom_field_names'][1]);
						if ( !empty($customfieldname_0) AND !empty($customfieldname_1) ) {
							// if there are two custom field names then use the new method to produce the querystring:
							$more_than_one_custom_field_name = TRUE;
							$meta_keys = $opt['custom_field_names'];
							$customfieldname_show = $meta_keys[$opt['sort_by_custom_field_name']];
							$nr_meta_keys = 2;
							// build querystring
							if (TRUE === is_array($meta_keys) AND 0 < $nr_meta_keys) {
								for ( $i = 0; $i < $nr_meta_keys; $i++ ) {
									// select the values of the wp_postmeta table by different a name for each meta_key
									$select_meta_value_str .= 'pm'.$i.'.meta_value AS meta_value'.$i.', ';
										
									// add a LEFT JOIN for each meta_key resp. custom field name // this useful to produce a data base request result which contains a column with the meta_values of each meta_key (originally the meta_values of all meta_keys are in one column in wp_postmeta)
									if ( 0 < $i ) {
										$from_left_join_str = 'LEFT JOIN wp_postmeta AS pm'.$i.' ON (pm0.post_id = pm'.$i.'.post_id AND pm'.$i.'.meta_key="'.$meta_keys[$i].'")';
									}
								}
								
								// build "Order By" string:
								if ( (defined('DB_COLLATE') AND '' != DB_COLLATE) OR (isset($opt['db_collate']) AND !empty($opt['db_collate'])) ) {
									if ( '' == DB_COLLATE ) {
										$collation_string = $opt['db_collate'];
									} else {
										$collation_string = DB_COLLATE;
									}
									$order_by_str = 'pm'.$opt['sort_by_custom_field_name'].'.meta_value COLLATE '.$collation_string.', LENGTH(pm'.$opt['sort_by_custom_field_name'].'.meta_value)';
								} else {
									$order_by_str = 'pm'.$opt['sort_by_custom_field_name'].'.meta_value, LENGTH(pm'.$opt['sort_by_custom_field_name'].'.meta_value)';
								}
								$querystring = 'SELECT pm0.meta_id, pm0.post_id, '.$select_meta_value_str.'p.guid, p.post_title, p.post_status FROM wp_postmeta AS pm0 '.$from_left_join_str.' LEFT JOIN wp_posts AS p ON (pm0.post_id = p.ID) WHERE pm0.meta_key = "'.$customfieldname_show.'"'.$only_public1.' ORDER BY '.$order_by_str;
							}
						} else {
							// if there is only one custom field name then use the old method to produce the querystring:
							$more_than_one_custom_field_name = FALSE;
							if ( !empty($customfieldname_0) AND empty($customfieldname_1) ) {
								$customfieldname_show = $opt['custom_field_names'][0];
							} elseif ( empty($customfieldname_0) AND !empty($customfieldname_1) ) {
								$customfieldname_show = $opt['custom_field_names'][1];
							}
							if ( (defined('DB_COLLATE') AND '' != DB_COLLATE) OR (isset($opt['db_collate']) AND !empty($opt['db_collate'])) ) {
								if ( '' == DB_COLLATE ) {
									$collation_string = $opt['db_collate'];
								} else {
									$collation_string = DB_COLLATE;
								}
								$querystring = 'SELECT pm.meta_id, pm.post_id, pm.meta_value, p.post_status FROM '.$wpdb->postmeta.' AS pm LEFT JOIN '.$wpdb->posts.' AS p ON (p.ID = pm.post_id) WHERE pm.meta_key = "'.$customfieldname_show.'"'.$only_public1.' ORDER BY pm.meta_value COLLATE '.$collation_string.', LENGTH(pm.meta_value)';
							} else {
								$querystring = 'SELECT pm.meta_id, pm.post_id, pm.meta_value, p.post_status FROM '.$wpdb->postmeta.' AS pm LEFT JOIN '.$wpdb->posts.' AS p ON (p.ID = pm.post_id) WHERE pm.meta_key = "'.$customfieldname_show.'"'.$only_public1.' ORDER BY pm.meta_value, LENGTH(pm.meta_value)';
							}
						}

						if ( $customfieldname_show == $opt['individual_href']['thecustomfieldname'] ) {
							$meta_values =  $wpdb->get_results($querystring);
							$nr_meta_values = count($meta_values);
							
							if ($nr_meta_values > 0) {
								if ( TRUE === $more_than_one_custom_field_name ) {
									if ( 1 == $opt['sort_by_custom_field_name'] ) {
										$meta_valuenameindex = 'meta_value0';
									} else {
										$meta_valuenameindex = 'meta_value1';
									}
								} else {
									$meta_valuenameindex = 'meta_value';
								}
								
								$meta_values_array = array();
								foreach ($meta_values as $meta_value) {
									$meta_values_array[$meta_value->meta_id]=$meta_value->$meta_valuenameindex;
									$meta_value_post_status[$meta_value->meta_id]=$meta_value->post_status;
								}
								
								if ( 'lastword' === $opt['orderelement'] ) {
									$mvals=array();
									$old_locale = setlocale(LC_COLLATE, "0");
									$nr_meta_values = count($meta_values_array);
									if (FALSE !== strpos(strtolower(php_uname('s')), 'win') AND function_exists('mb_convert_encoding')) {
										foreach ( $meta_values_array as $key => $value ) {
											$meta_values_array_zw[$key] = mb_convert_encoding(str_replace("_", " ", end(preg_split("/\s+/u", $value, -1, PREG_SPLIT_NO_EMPTY))), $opt['encoding_for_win']);
										}
										// build the charset name and setlocale on Windows machines 
										$loc = setlocale(LC_COLLATE, $opt['win_country_codepage']);
									} else {
										foreach ( $meta_values_array as $key => $value ) {
											$meta_values_array_zw[$key] = str_replace("_", " ", end(preg_split("/\s+/u", $value, -1, PREG_SPLIT_NO_EMPTY)));
										}
										// build the charset name and setlocale on Linux (or other) machines 
										$loc = setlocale(LC_COLLATE, WPLANG.'.'.DB_CHARSET);
									}
									
									// sort the meta_values
									if ( 'desc' === $opt['sortseq'] ) {
										arsort($meta_values_array_zw, SORT_LOCALE_STRING);
									} else {
										asort($meta_values_array_zw, SORT_LOCALE_STRING);
									}
									
									$individual_href_keys=array_keys($opt['individual_href']['id']);
									
									foreach ( $meta_values_array_zw as $key => $value ) {
										foreach ( $individual_href_keys as $individual_href_key ) {
											if ( $individual_href_key === $key ) {
												$individual_href[$key] = $opt['individual_href']['id'][$key];
											}
										}
									}
									// turn the locale back
									$loc=setlocale(LC_COLLATE, $old_locale);
								} else {
									// reverse the sort sequence if the option says so
									if ( 'desc' === $opt['sortseq'] ) {
										$individual_href = array_reverse($opt['individual_href']['id'], TRUE);
									} else {
										$individual_href = $opt['individual_href']['id'];
									}
								}
								
								// get all the post_status ofpost titles and IDs
								$querystring = 'SELECT ID, post_status FROM '.$wpdb->posts." WHERE (post_type='post' or post_type='page') ORDER BY ID DESC";
								$post_status_results =  $wpdb->get_results($querystring);
								foreach ($post_status_results as $post_status_result) {
									$post_states[$post_status_result->ID]=$post_status_result->post_status;
								}
								
								foreach ($individual_href as $meta_id => $link_target_post_id) {
									$meta_value = $meta_values_array[$meta_id];
									$descr = attribute_escape($opt['individual_href']['descr'][$meta_id]);
									if ('' != $only_public AND 'publish' != $meta_value_post_status[$meta_id]) {
										$nr_meta_values--;
									} else {
										if ('none' == $link_target_post_id) { // if there is no post or page id ...
											// ... then look for an URL which was free entered into the text box
											$url = trim(urldecode($opt['individual_href']['link'][$meta_id]));
											if ('' == $url) {
												echo "\n".'<li name="customfieldlistelements_'.$number.'_'.$j.'">'.$meta_value."</li>\n";
											} else {
												echo "\n".'<li name="customfieldlistelements_'.$number.'_'.$j.'"><a href="'.$url.'" title="'.$descr.'">'.$meta_value."</a></li>\n";
											}
										} elseif ( '' != $only_public AND 'publish' != $post_states[$link_target_post_id] ) { // if there is a post_id check if the most is published and if the user is logged in
											echo "\n".'<li name="customfieldlistelements_'.$number.'_'.$j.'">'.$meta_value."</li>\n";
										} else {
											echo "\n".'<li name="customfieldlistelements_'.$number.'_'.$j.'"><a href="'.get_permalink(intval($individual_href[$meta_id])).'" title="'.$descr.'">'.$meta_value."</a></li>\n";
										}
										$k++;
										if (  ($k > 0) AND ($partlength < $nr_meta_values) AND 0 === ($k % $partlength) ) {//($k > 0) AND ($partlength < $nr_meta_values) AND
											$j++;
										}
									}
								}
								$j = floor($nr_meta_values / $partlength);
								if ( 0 < ($nr_meta_values % $partlength) ) {
									$j++;
								}
							} else {
								echo "<li>".sprintf(__('There are no values in connection to the custom field name "%1$s" in the data base.','customfieldlist'), $customfieldname_show)."</li>\n";
							}
						} else {
							if ( empty($opt['individual_href']['thecustomfieldname']) ) {
								$customfieldname_from_db = '('.__('no value', 'customfieldlist').')';
							} else {
								$customfieldname_from_db = $opt['individual_href']['thecustomfieldname'];
							}
							echo "<li>".sprintf(__('The actual custom field name "%1$s" and the custom field name "%2$s" for which the link references are saved are different. Please save the links for the values of the actual custom field name.','customfieldlist'), $customfieldname_show, $customfieldname_from_db)."</li>\n";
						}
					break;
					case 'standard':
					default:
						$meta_keys = $opt['custom_field_names'];
						$none_empty = customfieldlist_remove_empty_array_elements($meta_keys);
						$nr_meta_keys = count($none_empty);
						
						// build querystring
						if (TRUE === is_array($meta_keys) AND 0 < $nr_meta_keys) {
							for ( $i = 0; $i < $nr_meta_keys; $i++ ) {
								// select the values of the wp_postmeta table by different a name for each meta_key
								$select_meta_value_str .= 'pm'.$i.'.meta_value AS meta_value'.$i.', ';
									
								// add a LEFT JOIN for each meta_key resp. custom field name // this useful to produce a data base request result which contains a column with the meta_values of each meta_key (originally the meta_values of all meta_keys are in one column in wp_postmeta)
								if ( 0 < $i ) {
									$from_left_join_str .= 'LEFT JOIN wp_postmeta AS pm'.$i.' ON (pm0.post_id = pm'.$i.'.post_id AND pm'.$i.'.meta_key="'.$meta_keys[$i].'")';
								}
							}

							// build "Order By" string:
							if ( (defined('DB_COLLATE') AND '' != DB_COLLATE) OR (isset($opt['db_collate']) AND !empty($opt['db_collate'])) ) {
								if ( '' == DB_COLLATE ) {
									$collation_string = $opt['db_collate'];
								} else {
									$collation_string = DB_COLLATE;
								}
								$order_by_str = 'pm'.$opt['sort_by_custom_field_name'].'.meta_value COLLATE '.$collation_string.', LENGTH(pm'.$opt['sort_by_custom_field_name'].'.meta_value)';
							} else {
								$order_by_str = 'pm'.$opt['sort_by_custom_field_name'].'.meta_value, LENGTH(pm'.$opt['sort_by_custom_field_name'].'.meta_value)';
							}
							$querystring = 'SELECT pm0.post_id, '.$select_meta_value_str.'p.guid, p.post_title FROM wp_postmeta AS pm0 '.$from_left_join_str.' LEFT JOIN wp_posts AS p ON (pm0.post_id = p.ID) WHERE pm0.meta_key = "'.$meta_keys[0].'"'.$only_public.' ORDER BY '.$order_by_str;
						
							$meta_values =  $wpdb->get_results($querystring);
							$nr_meta_values = count($meta_values);
						
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
								if ( 'desc' === $opt['sortseq'] ) {
									arsort($mvals, SORT_LOCALE_STRING);
								} else {
									asort($mvals, SORT_LOCALE_STRING);
								}
								
								//turn the locale back
								$loc=setlocale(LC_COLLATE, $old_locale);
								
								// get the keys with the new order
								$mval_keys = array_keys($mvals);
							} else {
								if ( 'desc' === $opt['sortseq'] ) {// reverse the sort sequence if the option says so
									$meta_values_reverse = array_reverse($meta_values);
									$meta_values = $meta_values_reverse;
								}
							}
							
							$hierarchy = $opt['hierarchy'];

							$clean_unique_values = customfieldlist_get_clean_unique_values($meta_keys);

							$nr_none_empty_meta_keys = count($clean_unique_values);
							$used_fields=$nr_none_empty_meta_keys;
							
							$dontshowthis_id = FALSE;
							foreach ($opt['donnotshowthis_customfieldname'] as $key => $value) {
								if ( 'sel' === $value ) { // there are custom field  name which should not be included in the hierarchy
									$dontshowthis_id = $key;
								} 
							}
							$new_used_fields = $used_fields;
							for ($i=0; $i < $used_fields; $i++) {
								if ( TRUE === is_numeric($dontshowthis_id) AND $hierarchy[$i] == $dontshowthis_id ) {
									$new_used_fields = $used_fields-1;
								} else {
									$meta_value_key_names[] ='meta_value'.$hierarchy[$i];
								}
							}
							$used_fields = $new_used_fields;

							krsort($meta_value_key_names);
							
							$result=Array();
							$hierarchymaxlevel=($used_fields+1);

							foreach ( $meta_values as $meta_value ) {
								$output_key_names = array();
								foreach ( $meta_value_key_names as $meta_value_key_name ) {
									$output_key_names[] = $meta_value->$meta_value_key_name;
								}
								$result_zw = customfieldlist_build_output_array($output_key_names, 0, Array(Array('post_id' => $meta_value->post_id, 'post_guid' => $meta_value->guid, 'post_title' => $meta_value->post_title)));
								$output_array = customfieldlist_array_merge($output_array, $result_zw, $hierarchymaxlevel);
							}

							$meta_value_id = strval($opt['show_this_custom_field_name_as_heading']);
							
							$nr_of_mainlistelements = count($output_array);
							$k = $nr_of_mainlistelements;
							$j = floor($nr_of_mainlistelements / $partlength);
							if ( 0 < ($nr_of_mainlistelements % $partlength) ) {
								$j++;
							}
							
							if ( 'yes' == $opt['list_style_opt1'] ) {
								$liststyleopt = 'each_element_with_sub_element';
							} else {
								$liststyleopt = 'standard';
							}
							
							customfieldlist_print_widget_content($output_array, $number, $partlength, $hierarchymaxlevel, $liststyleopt);
						} else {
							echo "<li>".sprintf(__('There are no values which are related to the custom field names which are set on the widgets page.','customfieldlist'), $opt['customfieldname'])."</li>\n";
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
		// $k := nr of list elements //$i := list // $j := lists
		//~ echo "\n <!-- ##### \n";
		//~ echo 'k '.$k."\n";
		//~ echo 'j '.$j."\n";
		//~ echo 'partlength '.$partlength."\n";
		//~ echo "\n ##### --> \n";
		if ($j > 0 AND $k > $partlength) {
			echo '<p class="customfieldlistpages" id="customfieldlistpages_'.$number.'"'.">\n";
			echo __('part','customfieldlist').": ";
			for ($i=0; $i<$j; $i++) {
				if ( 0 === $i ) {
					$css_class=' class="customfieldlist_selectedpart"';
				} else {
					$css_class='';
				}
				echo '[<a id="customfieldlistpart_'.$number.'_'.$i.'"'.$css_class.' href="javascript:show_this_customfieldlistelements('.$i.', '.$j.', '.$number.');"> '.($i+1).' </a>] ';
			}
			echo "\n</p>\n";
		}
	echo $after_widget."<!-- after_widget -->\n";
}


/*
 * the control- or preferences panel at the widgets page
 *
 * @param array|int $widget_args Widget number. Which of the several widgets of this type do we mean.
 */
 function customfieldlist_widget_control( $widget_args = 1 ) {
	global $wp_registered_widgets, $wpdb;
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
			
			$hierarchy_error = FALSE;
			if ( TRUE === is_array($_POST['customfieldlist_opt'][$widget_number]['custom_field_names']) ) {
				$opt[$widget_number]['custom_field_names'] = array_map('customfieldlist_clean_array_values', $_POST['customfieldlist_opt'][$widget_number]['custom_field_names']); 
				$i=0;
				if ('individual_href' !== $_POST['customfieldlist_opt'][$widget_number]['list_type']) {
					foreach ( $opt[$widget_number]['custom_field_names'] as $custom_field_name ) {
						if ( 0 < $i AND ('' == $opt[$widget_number]['custom_field_names'][($i-1)] AND '' != $custom_field_name) ) {
							$hierarchy_error = TRUE;
						}
						if (TRUE === $hierarchy_error) {
							$opt[$widget_number]['custom_field_names'][$i] = '';
						}
						$i++;
					}
				}
			} else {
				$opt[$widget_number]['custom_field_names'] = array_fill(0, CUSTOM_FIELD_LIST_MAX_HIERARCHY_LEVEL, '');
			}
			
			if (TRUE === $hierarchy_error) {
				$opt[$widget_number]['sort_by_custom_field_name'] = 0;
			} else {
				$opt[$widget_number]['sort_by_custom_field_name'] = intval(strip_tags(stripslashes(trim($_POST['customfieldlist_opt'][$widget_number]['sort_by_custom_field_name']))));
			}
			
			if ( TRUE === is_array($_POST['customfieldlist_opt'][$widget_number]['hierarchy']) ) {
				$opt[$widget_number]['hierarchy'] = $_POST['customfieldlist_opt'][$widget_number]['hierarchy'];	
			} else {
				$opt[$widget_number]['hierarchy'] = range(0, (CUSTOM_FIELD_LIST_MAX_HIERARCHY_LEVEL-1));
			}
			
			if ( TRUE === is_array($_POST['customfieldlist_opt'][$widget_number]['donnotshowthis_customfieldname']) AND 1 === count($_POST['customfieldlist_opt'][$widget_number]['donnotshowthis_customfieldname']) AND FALSE === $hierarchy_error) {
				$opt[$widget_number]['donnotshowthis_customfieldname'] = array_fill(0, CUSTOM_FIELD_LIST_MAX_HIERARCHY_LEVEL, 'notsel');
				foreach ($_POST['customfieldlist_opt'][$widget_number]['donnotshowthis_customfieldname'] as $key => $value) {
					$opt[$widget_number]['donnotshowthis_customfieldname'][$value] = 'sel';
				}
			} else {
				$opt[$widget_number]['donnotshowthis_customfieldname'] = array_fill(0, CUSTOM_FIELD_LIST_MAX_HIERARCHY_LEVEL, 'notsel');
			}
			
			if ( 'standard' !== $_POST['customfieldlist_opt'][$widget_number]['list_type'] AND 'individual_href' !== $_POST['customfieldlist_opt'][$widget_number]['list_type'] ) {
				$opt[$widget_number]['list_type'] = 'standard';
			} else {
				$opt[$widget_number]['list_type'] = $_POST['customfieldlist_opt'][$widget_number]['list_type'];
			}
			
			if ( isset($_POST['customfieldlist_opt'][$widget_number]['list_style_opt1']) ) {
				$opt[$widget_number]['list_style_opt1'] = 'yes';
			} else {
				$opt[$widget_number]['list_style_opt1'] = 'no';
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
			
			if ( 'asc' === $_POST['customfieldlist_opt'][$widget_number]['customfieldsortseq'] OR 'desc' === $_POST['customfieldlist_opt'][$widget_number]['customfieldsortseq'] ) {
				$opt[$widget_number]['sortseq'] = $_POST['customfieldlist_opt'][$widget_number]['customfieldsortseq'];
			} else {
				$opt[$widget_number]['sortseq'] = 'asc';
			}
		}
		update_option('widget_custom_field_list', $opt);
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
	
	echo '<p style="text-align:center;">'.__('Header (optional)','customfieldlist').': <input type="text" name="customfieldlist_opt['.$number.'][header]" value="'.$header.'" maxlength="200" /><br /><span style="font-size:0.8em;">('.__('Leave the field empty for no widget title','customfieldlist').')</span></p>'."\n";
	
	// section: custom field names
	echo '<div class="customfieldlist_section">'."\n";
		echo '<h5>'.__('Custom Field Names','customfieldlist').'</h5>'."\n";
		
		if ( FALSE === is_array($opt[$number]['custom_field_names']) OR CUSTOM_FIELD_LIST_MAX_HIERARCHY_LEVEL > count($opt[$number]['custom_field_names']) ) {
			$opt[$number]['custom_field_names'] = array_fill(0, CUSTOM_FIELD_LIST_MAX_HIERARCHY_LEVEL, '');
		}
		
		switch ($opt[$number]['list_type']) {
			case 'individual_href' :
				$listlayoutopt1chk = '';
				$listlayoutopt3chk = ' checked="checked"';
				$liststyleopt1disabled = ' disabled="disabled"';
			break;
			case 'standard' :
			default :
				$listlayoutopt1chk = ' checked="checked"';
				$listlayoutopt3chk = '';
				$liststyleopt1disabled = '';
			break;
		}
		
		// nr of used text boxes :
		$nr_of_custom_field_names = 0;
		foreach ( $opt[$number]['custom_field_names'] as $custom_field_name ) {
			$cfn_trim = trim($custom_field_name);
			if ( !empty($cfn_trim) ) {
				$nr_of_custom_field_names++;
			}
		}
		
		$i=0;
		$thecustomfieldname='';
		echo '<div class="customfieldlist_customfieldnames_box">'."\n";
			echo '<div class="customfieldlist_row customfieldlist_head_row">'."\n";
				echo '<div class="customfieldlist_column_index">&nbsp;</div>';
				echo '<div class="customfieldlist_column_textbox customfieldlist_column_textbox_head">';
					_e('custom field names','customfieldlist');
				echo '</div>';
				echo '<div class="customfieldlist_column_radiobutton">';
					_e('sort by','customfieldlist');
				echo '</div>';
				echo '<div class="customfieldlist_column_checkbox">';
					_e('hide this','customfieldlist');
				echo '</div>'."\n";
			echo '</div>'."\n";
		foreach ( $opt[$number]['custom_field_names'] as $custom_field_name ) {
			if (($i & 1) == 1) {$style = 'alternate';} else {$style = '';}
			echo '<div class="customfieldlist_row '.$style.'">'."\n";
			echo '<div class="customfieldlist_column_index">';
			echo $i . '.';
			echo '</div>';
			
			if ( 'individual_href' === $opt[$number]['list_type'] AND 1 < $i ) {
				$readonly_text_areas = ' readonly="readonly"';
				$disable_radio_buttons = ' disabled="disabled"';
			} else {
				$readonly_text_areas = '';
				$disable_radio_buttons = '';
			}
			
			########## TEXTAREA column #########################
			echo '<div class="customfieldlist_column_textbox">';
			echo '&nbsp;<input type="text" id="customfieldnames_'.$number.'_'.$i.'" name="customfieldlist_opt['.$number.'][custom_field_names][]" value="'.attribute_escape($custom_field_name).'" maxlength="200" onchange="customfieldlist_customfieldname_changed(this.name, '.$number.');"'.$readonly_text_areas.' />';
			echo '</div>';
			
			########## RADIO BUTTON column #####################
			if ( $i == $opt[$number]['sort_by_custom_field_name'] ) {
				$checked=' checked="checked"';
				if ( 1 < $nr_of_custom_field_names ) {
					$disable_check_boxes = '';
				} else {
					$disable_check_boxes = ' disabled="disabled"';
				}
				$disable_radio_buttons = '';
			} else {
				$checked='';
				if ( 1 < $nr_of_custom_field_names ) {
					$cfn_trim = trim($opt[$number]['custom_field_names'][$i]);
					if ( !empty($cfn_trim) ) {
						$disable_radio_buttons = '';
					} else {
						$disable_radio_buttons = ' disabled="disabled"';
					}
				} else {
					$disable_radio_buttons = ' disabled="disabled"';
				}

				$disable_check_boxes = ' disabled="disabled"';
			}
			echo '<div class="customfieldlist_column_radiobutton">';
			echo '&nbsp;<input type="radio" name="customfieldlist_opt['.$number.'][sort_by_custom_field_name]" value="'.$i.'"'.$checked.' onclick="customfieldlist_radio_button_changed(this.name, '.$number.', '.$i.');"'.$disable_radio_buttons.' />';
			echo '</div>';
			
			
			########## CHECKBOX column ########################
			$checked='';
			if ( 'sel' === $opt[$number]['donnotshowthis_customfieldname'][$i] ) {
				$checked=' checked="checked"';
			} else {
				$checked='';
			}
			echo '<div class="customfieldlist_column_checkbox">';
			echo '&nbsp;<input type="checkbox" id="donnotshowthis_customfieldname_'.$number.'_'.$i.'" name="customfieldlist_opt['.$number.'][donnotshowthis_customfieldname][]" value="'.$i.'"'.$checked.$disabled.' onclick="customfieldlist_checkbox_changed(this.id, this.name, '.$number.', '.$i.');"'.$disable_check_boxes.' />';

			########## hidden HIERARCHY column ####################
			echo '<input type="hidden" name="customfieldlist_opt['.$number.'][hierarchy][]" value="'.$i.'">';
			//~ echo '<select id="customfieldhierarchy_'.$number.'" name="customfieldlist_opt['.$number.'][hierarchy][]" style="width:10%;">';
			//~ for ($j=0; $j < (CUSTOM_FIELD_LIST_MAX_HIERARCHY_LEVEL+1); $j++) {
				//~ if ( $j == intval($opt[$number]['hierarchy'][$i]) ) {
					//~ $selected=' selected="selected"';
				//~ } else {
					//~ $selected='';
				//~ }
				
				//~ if ( CUSTOM_FIELD_LIST_MAX_HIERARCHY_LEVEL == $j ) {
					//~ echo '<option value="'.$j.'"'.$selected.'>'. __('do not show this','customfieldlist') .'</option>';
				//~ } else {
					//~ echo '<option value="'.$j.'"'.$selected.'>'. $j .'</option>';
				//~ }
			//~ }
			//~ echo '</select>';
			echo '</div>';
			echo '</div>';
			
			$i++;
		}
		echo '</div>'."\n";
		
		echo '<div id="customfieldlist_hierarchy_vacancy_error_'.$number.'" class="customfieldlist_error" style="display:none;">'. __('If you want to create a list with several hierarchy levels then fill the custom field name fields one by one.','customfieldlist').'</div>'."\n";
		
		########## BEGIN: check if the custom field names are used for same posts ##########
		$notequal = FALSE;
		$where='';
		if (1 < $nr_of_custom_field_names) {
			for ( $i=0; $i < $nr_of_custom_field_names; $i++ ) {
				$zw_post_ids = Array();
				$query_postmeta_values = "SELECT post_id FROM ".$wpdb->postmeta." WHERE meta_key='".$opt[$number]['custom_field_names'][$i]."' ORDER BY post_id DESC";
				$postmeta_values = $wpdb->get_results($query_postmeta_values);
				foreach ($postmeta_values as $postmeta_value) {
					$zw_post_ids[]=$postmeta_value->post_id;
				}
				$postmetas[$i]['post_ids'] = $zw_post_ids;
				$postmetas[$i]['nr_of_values'] = sizeof($postmeta_values);
				$postmetas[$i]['meta_key'] = $opt[$number]['custom_field_names'][$i];
				// compare the post_ids
				if ( 0 < $i ) {
					if ( $postmetas[($i-1)]['nr_of_values'] == $postmetas[$i]['nr_of_values'] ) {
						for  ($j=0; $j < $nr_of_custom_field_names; $j++ ) {
							if ( $postmetas[($i-1)]['post_ids'][$j] != $postmetas[$i]['post_ids'][$j]) {
								$notequal = TRUE;
							}
						}
					} else {
						$notequal = TRUE;
					}
				}
			}
		}
		if ( TRUE === $notequal ) {
			echo '<div class="customfieldlist_advice" id="customfieldlist_advice_cfn_usage">'."\n";
				echo ''.__('You are using more than one custom field name. But these custom field names are not used in the same amount of posts or in the same amount per posts.<br />It is most likely that the appearance of the list in the side bar is as intended.<br />The table gives an overview which and how often a custom field is used:','customfieldlist').''."\n";
				$result = Array();
				foreach ( $postmetas as $postmeta ) {
					$result = array_merge($result , $postmeta['post_ids']);
				}
				$result = array_unique($result);
				$nr_unique_post_ids = count($result);
				rsort($result);
				$where='';
				$i=0;
				foreach ( $result as $ID ) {
					if ( $i < ($nr_unique_post_ids-1) ) {
						$where .= "ID=".$ID." OR ";
					} else {
						$where .= "ID=".$ID."";
					}
					$i++;
				}
				$querystring = "SELECT ID, post_title FROM ".$wpdb->posts." WHERE ".$where;
				$posttitles_class = $wpdb->get_results($querystring);
				if (FALSE == $posttitles_class) {
					$posttitles = array_fill_keys($result,'');
				} else {
					foreach ( $posttitles_class as $posttitle_class ) {
						$posttitles[$posttitle_class->ID] = $posttitle_class->post_title;
					}
				}
				echo '<table class="cc_interval_table widefat">'."\n";
				echo '<thead>'."\n";
					echo '<tr>'."\n";
					echo '<th rowspan="2">'.__('custom field names','customfieldlist').'</th>';
					echo '<th colspan="'.$nr_unique_post_ids.'">'.__('post IDs','customfieldlist').'</th>';
					echo '</tr>'."\n";
					echo '<tr>'."\n";
					for ( $i=0; $i < $nr_unique_post_ids; $i++ ) {
						echo '<th><acronym class="customfieldlist_acronym" title="'.$posttitles[$result[$i]].'">'.$result[$i].'</acronym></th>';
					}
					echo '</tr>'."\n";
				echo '</thead>'."\n";
				echo '<tbody>'."\n";
				foreach ( $postmetas as $postmeta ) {
					echo '<tr>'."\n";
						echo '<td>';
						echo $postmeta['meta_key'];
						echo '</td>';
						for ( $i=0; $i < $nr_unique_post_ids; $i++ ) {
							echo '<td>';
							$counter=0;
							foreach ( $postmeta['post_ids'] as $postmeta_id ) {
								if ( $result[$i] == $postmeta_id ) {
									$counter++;
								}
							}
							if (0 < $counter) {
								echo $counter.'x';
							}
							echo '</td>';
						}
					echo '</tr>'."\n";
					$i++;
				}
				echo '</tbody>'."\n";
				echo '</table>'."\n";
			echo '</div>'."\n";
		}
		########## END: check if the custom field names are used for same posts #####################
		
	echo '</div>'."\n"; // section: custom field names
	

	// set the custom field name to variable which will be given to the link window, too
	// are both custom field names (which are only possible for that option) in use? 
	$customfieldname_0 = trim($opt[$number]['custom_field_names'][0]);
	$customfieldname_1 = trim($opt[$number]['custom_field_names'][1]);
	if ( !empty($customfieldname_0) AND !empty($customfieldname_1) ) {
		$thecustomfieldname = $opt[$number]['custom_field_names'][intval($opt[$number]['sort_by_custom_field_name'])];
	} else {
		if ( empty($customfieldname_0) ) {
			$thecustomfieldname = $opt[$number]['custom_field_names'][1];
		} else {
			$thecustomfieldname = $opt[$number]['custom_field_names'][0];
		}
	}
	
	
	// section: Sorting Options
	echo '<div class="customfieldlist_section">'."\n";
		echo '<h5>'.__('Sorting Options','customfieldlist').'</h5>';
		$customfieldsortseq_DESC_selected='';
		$customfieldsortseq_ASC_selected='';
		if ( TRUE !== isset($opt[$number]['sortseq']) OR TRUE === empty($opt[$number]['sortseq']) OR 'asc' === $opt[$number]['sortseq'] ) {
			$customfieldsortseq_ASC_checked=' checked="checked"';
			$customfieldsortseq_DESC_checked='';
		} else {
			$customfieldsortseq_ASC_checked='';
			$customfieldsortseq_DESC_checked=' checked="checked"';
		}
		echo '<fieldset><legend>'.__('sort sequence','customfieldlist').':</legend>';
			echo '<label for="customfieldsortseq_'.$number.'_asc">'.__('ascending (ASC)','customfieldlist').'</label> <input type="radio" id="customfieldsortseq_'.$number.'_asc" name="customfieldlist_opt['.$number.'][customfieldsortseq]" value="asc"'.$customfieldsortseq_ASC_checked.' /><br />';
			echo '<label for="customfieldsortseq_'.$number.'_desc">'.__('descending (DESC)','customfieldlist').'</label> <input type="radio" id="customfieldsortseq_'.$number.'_desc" name="customfieldlist_opt['.$number.'][customfieldsortseq]" value="desc"'.$customfieldsortseq_DESC_checked.' /><br />';
		echo '</fieldset>';
	
		// section: select DB_CHARSET
		if (FALSE == defined('DB_COLLATE')) {
			echo '<p><a href="http://dev.mysql.com/doc/refman/5.1/en/charset-charsets.html" target="_blank">'.__('database collation','customfieldlist').'</a>: <input type="text" name="customfieldlist_opt['.$number.'][db_collate]" value="'.attribute_escape($opt[$number]['db_collate']).'" maxlength="200" /></p>'."\n";
		}
		
		// section: "sort by the last word" preferences
		$old_locale = setlocale(LC_COLLATE, "0");
		$loc = setlocale(LC_COLLATE, WPLANG.'.'.get_bloginfo('charset'), WPLANG, 'english_usa');
		setlocale(LC_COLLATE, $old_locale);
		if (FALSE === $loc) {
			$message_setloc = '<div class="customfieldlist_error">'.__('This option will probably not work. Because it is not possible to set "setlocale(LC_COLLATE, ... " on this server.','customfieldlist').'</div>';
			$message_os_asterisk = ' class="customfieldlist_error_chkb"';
		} else {
			if (FALSE !== strpos(strtolower(php_uname('s')), 'win')) {
				if (function_exists('mb_convert_encoding')) {
					// the encoding which PHP multibyte supports  http://www.php.net/manual/en/mbstring.supported-encodings.php (without these: 'UTF-32', 'UTF-32BE', 'UTF-32LE', 'UTF-16', 'UTF-16BE', 'UTF-16LE', 'UTF-7', 'UTF7-IMAP', 'UTF-8',
					$encodings = array('UCS-4' => 'UCS-4', 'UCS-4BE' => 'UCS-4BE', 'UCS-4LE' => 'UCS-4LE', 'UCS-2' => 'UCS-2', 'UCS-2BE' => 'UCS-2BE', 'UCS-2LE' => 'UCS-2LE', 'ASCII' => 'ASCII', 'EUC-JP' => 'EUC-JP', 'SJIS' => 'SJIS', 'eucJP-win' => 'eucJP-win', 'SJIS-win' => 'SJIS-win', 'ISO-2022-JP' => 'ISO-2022-JP', 'JIS' => 'JIS', 'ISO-8859-1' => 'ISO-8859-1', 'ISO-8859-2' => 'ISO-8859-2', 'ISO-8859-3' => 'ISO-8859-3', 'ISO-8859-4' => 'ISO-8859-4', 'ISO-8859-5' => 'ISO-8859-5', 'ISO-8859-6' => 'ISO-8859-6', 'ISO-8859-7' => 'ISO-8859-7', 'ISO-8859-8' => 'ISO-8859-8', 'ISO-8859-9' => 'ISO-8859-9', 'ISO-8859-10' => 'ISO-8859-10', 'ISO-8859-13' => 'ISO-8859-13', 'ISO-8859-14' => 'ISO-8859-14', 'ISO-8859-15' => 'ISO-8859-15', 'byte2be' => 'byte2be', 'byte2le' => 'byte2le', 'byte4be' => 'byte4be', 'byte4le' => 'byte4le', 'BASE64' => 'BASE64', 'HTML-ENTITIES' => 'HTML-ENTITIES', '7bit' => '7bit', '8bit' => '8bit', 'EUC-CN' => 'EUC-CN', 'CP936' => 'CP936', 'HZ' => 'HZ', 'EUC-TW' => 'EUC-TW', 'CP950' => 'CP950', 'BIG-5', 'EUC-KR' => 'EUC-KR', 'UHC' => 'CP949', 'ISO-2022-KR' => 'ISO-2022-KR', 'Windows-1251' => 'CP1251', 'Windows-1252' => 'CP1252', 'IBM866' => 'CP866', 'KOI8-R' => 'KOI8-R');
					$message_os = '<div class="customfieldlist_advice">'.__('The servers OS is Windows (which is not able to sort UTF-8) what makes it probably necessary for the correct functioning of this option to:','customfieldlist').'<br />';
					$message_os .= __('1. enter your <a href="http://msdn.microsoft.com/en-gb/library/39cwe7zf.aspx" target="_blank">language</a> and <a href="http://msdn.microsoft.com/en-gb/library/cdax410z.aspx" target="_blank">country</a> name and eventually the <a href="http://en.wikipedia.org/wiki/Windows_code_pages" target="_blank">code page number</a> (like german_germany or german_germany.1252 for German)','customfieldlist').': <input type="text" name="customfieldlist_opt['.$number.'][win_country_codepage]" value="'.attribute_escape($opt[$number]['win_country_codepage']).'" maxlength="200" style="width:92%;" /><br />';
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
					$message_os .= '</div>';
					$message_os_asterisk = ' class="customfieldlist_advice_chkb"';
				} else {
					$message_os = '<div class="customfieldlist_error">'.__('This option will probably not work on this server because this plugin converts the encoding of the meta values to the encoding of the OS (Windows) with the function mb_convert_encoding but this function is not available.','customfieldlist').'</div>';
					$message_os_asterisk = ' class="customfieldlist_error_chkb"';
				}
			} else {
				$message_os = '';
			}
			$message_setloc = '';
		}
		if ( 'lastword' === $opt[$number]['orderelement'] ) {
			echo '<div'.$message_os_asterisk.'><label for="customfieldlist_sortbylastword_'.$number.'">'.__('sort the values by the last word','customfieldlist').':</label> <input type="checkbox" name="customfieldlist_opt['.$number.'][orderelement]" id="customfieldlist_sortbylastword_'.$number.'" value="lastword" checked="checked" /></div>'.$message_os.$message_setloc.''."\n";
		} else {
			echo '<div'.$message_os_asterisk.'><label for="customfieldlist_sortbylastword_'.$number.'">'.__('sort the values by the last word','customfieldlist').':</label> <input type="checkbox" name="customfieldlist_opt['.$number.'][orderelement]" id="customfieldlist_sortbylastword_'.$number.'" value="lastword" /></div>'.$message_os.$message_setloc.''."\n";
		}
	echo '</div>'."\n";

	// section: select the list type
	echo '<div class="customfieldlist_section">'."\n";
		echo '<h5>'.__('List Types','customfieldlist').'</h5>'."\n";
		//echo '<div><span class="customfieldlist_help" onclick="customfieldlist_show_this_explanation(\'customfieldlist_opt_'.$number.'_list_type_opt1_explanation\')">[ ? ]</span> '.'<label for="customfieldlist_opt_'.$number.'_list_type_opt1" class="customfieldlist_label">'.__('standard layout','customfieldlist').'</label> <input type="radio" name="customfieldlist_opt['.$number.'][list_type]" id="customfieldlist_opt_'.$number.'_list_type_opt1" value="standard" '.$listlayoutopt1chk.' onclick="customfieldlist_opt_changed(this.id, '.$number.');" /></div>'."\n";
		echo '<div><a href="#customfieldlist_help" onclick="if (false == customfieldlist_show_this_explanation(\'customfieldlist_opt_'.$number.'_list_type_opt1_explanation\')) {return false;}" class="customfieldlist_help">[ ? ]</a> '.'<label for="customfieldlist_opt_'.$number.'_list_type_opt1" class="customfieldlist_label">'.__('standard layout','customfieldlist').'</label> <input type="radio" name="customfieldlist_opt['.$number.'][list_type]" id="customfieldlist_opt_'.$number.'_list_type_opt1" value="standard" '.$listlayoutopt1chk.' onclick="customfieldlist_opt_changed(this.id, '.$number.');" /></div>'."\n";
		echo '<p id="customfieldlist_opt_'.$number.'_list_type_opt1_explanation" class="customfieldlist_explanation">'.__('Only list elements of custom field names with more than one custom field value have sub elements. These sub elements becoming visible by clicking on the custom field name list elements or the + sign. The other list elements with one value are the hyper links to the posts and the values are in the link title.','customfieldlist').'</p>'."\n";
		
		//echo '<div><span class="customfieldlist_help" onclick="customfieldlist_show_this_explanation(\'customfieldlist_opt_'.$number.'_list_type_opt2_explanation\')">[ ? ]</span> '.'<label for="customfieldlist_opt_'.$number.'_list_type_opt2" class="customfieldlist_label">'.__('a list with manually linked values','customfieldlist').'</label> <input type="radio" name="customfieldlist_opt['.$number.'][list_type]" id="customfieldlist_opt_'.$number.'_list_type_opt2" value="individual_href" '.$listlayoutopt3chk.' onclick="customfieldlist_opt_changed(this.id, '.$number.');" /></div>'."\n";	
		echo '<div><a href="#customfieldlist_help" onclick="if (false == customfieldlist_show_this_explanation(\'customfieldlist_opt_'.$number.'_list_type_opt2_explanation\')) {return false;}" class="customfieldlist_help">[ ? ]</a> '.'<label for="customfieldlist_opt_'.$number.'_list_type_opt2" class="customfieldlist_label">'.__('a list with manually linked values','customfieldlist').'</label> <input type="radio" name="customfieldlist_opt['.$number.'][list_type]" id="customfieldlist_opt_'.$number.'_list_type_opt2" value="individual_href" '.$listlayoutopt3chk.' onclick="customfieldlist_opt_changed(this.id, '.$number.');" /></div>'."\n";	
		echo '<p id="customfieldlist_opt_'.$number.'_list_type_opt2_explanation" class="customfieldlist_explanation">'.__('A simple list of all unique custom field values of one custom field name. Each value can be linked individually.','customfieldlist').'</p>'."\n";
		echo '<input type="button" class="button" id="customfieldlist_opt_'.$number.'_set_links" title="'.sprintf(__('Set a Link for each custom field value of the custom field: %1$s','customfieldlist'), $thecustomfieldname).'" value="'.__('Set the links','customfieldlist').'" onclick="customfieldlist_set_links(\'\', '.$number.', this.id);" />'."\n";
		echo '<input type="hidden" id="customfieldlist_opt_'.$number.'_set_links_helper" value="'.sprintf(__('Set a Link for each custom field value of the custom field: %1$s','customfieldlist'), $thecustomfieldname).'" />'."\n";
	echo '</div>'."\n";
	
	// section: list appearance
	echo '<div class="customfieldlist_section">'."\n";
		echo '<h5>'.__('List Appearance','customfieldlist').'</h5>'."\n";
		
		if ( 'yes' == $opt[$number]['list_style_opt1'] ) {
			$liststyleopt1chk = ' checked="checked"';
		} else {
			$liststyleopt1chk = '';
		}
		//echo '<div><span class="customfieldlist_help" onclick="customfieldlist_show_this_explanation(\'customfieldlist_opt_'.$number.'_list_style_opt1_explanation\')">[ ? ]</span> '.'<label for="customfieldlist_opt_'.$number.'_list_style_opt1" class="customfieldlist_label">'.__('each element with sub elements','customfieldlist').'</label> <input type="checkbox" name="customfieldlist_opt['.$number.'][list_style_opt1]" id="customfieldlist_opt_'.$number.'_list_style_opt1" value="yes"'.$liststyleopt1chk.''.$liststyleopt1disabled.' /></div>'."\n";
		echo '<div><a href="#customfieldlist_help" onclick="if (false == customfieldlist_show_this_explanation(\'customfieldlist_opt_'.$number.'_list_style_opt1_explanation\')) {return false;}" class="customfieldlist_help">[ ? ]</a> '.'<label for="customfieldlist_opt_'.$number.'_list_style_opt1" class="customfieldlist_label">'.__('each element with sub elements','customfieldlist').'</label> <input type="checkbox" name="customfieldlist_opt['.$number.'][list_style_opt1]" id="customfieldlist_opt_'.$number.'_list_style_opt1" value="yes"'.$liststyleopt1chk.''.$liststyleopt1disabled.' /></div>'."\n";
		echo '<p id="customfieldlist_opt_'.$number.'_list_style_opt1_explanation" class="customfieldlist_explanation">'.sprintf(__('Shows each custom field name as a list element with the custom field value as a sub element. All sub elements are every time visible and they are the hyper links to the posts. (Only available in combination with list type "%1$s")','customfieldlist'),__('standard layout','customfieldlist')).'</p>'."\n";

		if ( 'yes' == $opt[$number]['partlist'] ) {
			$liststyleopt2chk = ' checked="checked"';
		} else {
			$liststyleopt2chk = '';
		}
		echo '<label for="customfieldlist_opt_'.$number.'_list_style_opt2" class="customfieldlist_label">'.__('show only a part of the list elements at once','customfieldlist').':</label> <input type="checkbox" id="customfieldlist_opt_'.$number.'_list_style_opt2" name="customfieldlist_opt['.$number.'][partlist]" value="yes"'.$liststyleopt2chk.' />'."\n";
		
		echo '<p>'.__('elements per part of the list','customfieldlist').' (X>=3): <input type="text" name="customfieldlist_opt['.$number.'][partlength]" value="'.$partlength.'" maxlength="5" style="width:5em;" /></p>'."\n";
	echo '</div>'."\n";
	echo '<input type="hidden" id="customfieldlist-submit-'.$number.'" name="customfieldlist-submit['.$number.'][submit]" value="1" />'."\n";
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

// add jquery scripts for the appearance of the widgets lists
add_action('wp_print_scripts', 'customfieldlist_widget_script');
function customfieldlist_widget_script() {
	wp_enqueue_script( 'jquery' );
	$scriptfile = CUSTOM_FIELD_LIST_WIDGET_URL.'/widget_custom_field_list_js.php';
	wp_enqueue_script( 'customfieldlist_widget_script',  $scriptfile , array('jquery') );
}

// add styles for the appearance of the widgets lists 
add_action('wp_print_styles', 'customfieldlist_widget_style');
function customfieldlist_widget_style() {
	$stylefile = CUSTOM_FIELD_LIST_WIDGET_URL.'/widget_custom_field_list.css';
	wp_enqueue_style( 'customfieldlist_widget_style', $stylefile );
}

// add js on the widgets page
add_action('admin_print_scripts-widgets.php', 'customfieldlist_widget_admin_script');
function customfieldlist_widget_admin_script() {
	?>
	<script type="text/javascript">
	//<![CDATA[
	function customfieldlist_set_links(link, number, this_id) {
		if ( document.getElementById('customfieldlist_opt_' + String(number) + '_list_type_opt2').checked == true ) {
			if ( 'unsaved_changes' != document.getElementById( this_id + '_helper' ).value ) {
				document.getElementById(this_id).title = String(document.getElementById( this_id + '_helper' ).value);
				var tst = '<?php  echo CUSTOM_FIELD_LIST_WIDGET_URL.'/widget_custom_field_list_individual_href.php?height=400&width=750&abspath='.(urlencode(ABSPATH)).'&number='; ?>' + String(number) + '<?php echo '&_wpnonce='.wp_create_nonce('customfieldlist_individual_href_security'); ?>';
			} else {
				document.getElementById(this_id).title = '<?php echo __('Unsaved changes','customfieldlist'); ?>';
				var tst = '<?php  echo CUSTOM_FIELD_LIST_WIDGET_URL.'/widget_custom_field_list_individual_href_advice.php?height=100&width=750&abspath='.(urlencode(ABSPATH)).'&advicemsg=3&_wpnonce='.wp_create_nonce('customfieldlist_individual_href_security'); ?>';
			}
		} else {
			document.getElementById(this_id).title = '<?php echo __('Not available with these widget preferences.','customfieldlist'); ?>';
			var tst = '<?php  echo CUSTOM_FIELD_LIST_WIDGET_URL.'/widget_custom_field_list_individual_href_advice.php?height=100&width=750&abspath='.(urlencode(ABSPATH)).'&advicemsg=2&_wpnonce='.wp_create_nonce('customfieldlist_individual_href_security'); ?>';
		}
		tb_show(document.getElementById( String(this_id) ).title, tst, false);
	}
	
	function customfieldlist_opt_changed (opt_id, number) {
		var txtb_elements_name = 'customfieldlist_opt[' + String(number) + '][custom_field_names][]';
		var alltxtb = document.getElementsByName(txtb_elements_name);
		var number_of_txtb = document.getElementsByName(txtb_elements_name).length;
		// when opt2 was selected, make textareas, radio buttons and check boxes read only
		if ( 'customfieldlist_opt_'+ String(number) +'_list_type_opt2' == opt_id ) {
			document.getElementById('customfieldlist_opt_' + String(number) + '_list_style_opt1').checked = false;
			document.getElementById('customfieldlist_opt_' + String(number) + '_list_style_opt1').disabled = true;
			// which radio button is selected
			var rb_elements_name = 'customfieldlist_opt[' + String(number) + '][sort_by_custom_field_name]';
			var allrb = document.getElementsByName(rb_elements_name);
			var number_of_rbuttons = document.getElementsByName(rb_elements_name).length;
			var checked_rb_index = -1;
			var status;
			for (var i = 0; i < number_of_rbuttons; i++ ) {
				status = allrb[i].checked;	
				if ( true == status ) {
					checked_rb_index = i;
				} 
			}
			
			var chkb_elements_name = 'customfieldlist_opt[' + String(number) + '][donnotshowthis_customfieldname][]';
			var allchk = document.getElementsByName(chkb_elements_name);
		
			// disable all txt boxes (with index > 1) (and clear them), radio buttons and chk boxes
			for (var i = 0; i < number_of_txtb; i++ ) {
				if ( 1 < i ) {
					alltxtb[i].value = '';
					alltxtb[i].readOnly = true;
					allrb[i].disabled = true;
				} 
				allrb[i].disabled = true;
				allchk[i].checked = false;
				allchk[i].disabled = true;
			}
			
			// count the not empty fields and get the index_of_the_last_used_field
			var nr_of_used_fields = 0;
			var index_of_the_last_used_field = -1;
			var textb_status = Array();
			for (var i = 0; i < number_of_txtb; i++ ) {
				var trimed_val = alltxtb[i].value.replace(/\s/g, '_' );
				if ( 0 < trimed_val.length ) {
					nr_of_used_fields++;
					index_of_the_last_used_field = i;
					textb_status[i] = true;
				} else {
					textb_status[i] = false;
				}
			}
			
			if ( 0 == nr_of_used_fields ) {
				allrb[0].disabled = false;
				allrb[0].checked = true;
			} else if ( 1 == nr_of_used_fields ) {
				allrb[index_of_the_last_used_field].disabled = false;
				allrb[index_of_the_last_used_field].checked = true;
			} else if ( 1 < nr_of_used_fields ) {
				// if the selected radio button has got an index > 1 then select the last used field and enable and check the checkbox right of it
				if ( 1 < checked_rb_index ) {
					checked_rb_index = index_of_the_last_used_field;
				} 
				allrb[checked_rb_index].disabled = false;
				allrb[checked_rb_index].checked = true;
				allchk[checked_rb_index].disabled = false;
				allchk[checked_rb_index].checked = true;
				// and enable the the other radio button
				if ( 0 == checked_rb_index ) {
					allrb[1].disabled = false;
				} else {
					allrb[0].disabled = false;
				}
			}
		} else {
			document.getElementById('customfieldlist_opt_' + String(number) + '_list_style_opt1').disabled = false;
			if ( true == alltxtb[2].readOnly ) {
				for (var i = 2; i < number_of_txtb; i++ ) {
					alltxtb[i].readOnly = false;
				}
			}
		}
	}
	
	function customfieldlist_customfieldname_changed (name, number) {
		var list_type_opt2 = document.getElementById('customfieldlist_opt_' + String(number) + '_list_type_opt2');
		
		var txtb_elements_name = 'customfieldlist_opt[' + String(number) + '][custom_field_names][]';
		var alltxtb = document.getElementsByName(txtb_elements_name);
		var number_of_txtb = alltxtb.length;
		
		// count the not empty fields and get the index_of_the_last_used_field
		var nr_of_used_fields = 0;
		var index_of_the_last_used_field = -1;
		var hierarchy_vacancy = false;
		var textb_status = Array();
		for (var i = 0; i < number_of_txtb; i++ ) {
			var trimed_val = alltxtb[i].value.replace(/\s/g, '_' );
			if ( 0 < trimed_val.length ) {
				nr_of_used_fields++;
				index_of_the_last_used_field = i;
				textb_status[i] = true;
			} else {
				textb_status[i] = false;
			}
			if ( 0 < i && (false == textb_status[(i-1)] && true == textb_status[i]) && false == list_type_opt2.checked ) {
				hierarchy_vacancy = true;
			} 
		}
		var message = document.getElementById('customfieldlist_hierarchy_vacancy_error_'+String(number));
		if ( true == hierarchy_vacancy ) {
			//formu.savewidget.style.display = 'none';
			message.style.display = 'block';
		} else {
			if ( 'block' == message.style.display ) {
				message.style.display = 'none';
				//formu.savewidget.style.display = 'inline';
			}
		}
		
		var rb_elements_name = 'customfieldlist_opt[' + String(number) + '][sort_by_custom_field_name]';
		var allrb = document.getElementsByName(rb_elements_name);
		var number_of_rbuttons = document.getElementsByName(rb_elements_name).length;

		// which radio button is selected
		var rb_checked_status = Array();
		var checked_rb_index = -1;
		for (var i = 0; i < number_of_rbuttons; i++ ) {
			var status = allrb[i].checked;	
			if ( true == status ) {
				rb_checked_status[i] = true;
				checked_rb_index = i;
			} else {
				rb_checked_status[i] = false;
			}
		}
		
		var chkb_elements_name = 'customfieldlist_opt[' + String(number) + '][donnotshowthis_customfieldname][]';
		var allchk = document.getElementsByName(chkb_elements_name);
		
		if ( 0 == nr_of_used_fields ) {
			for (var i = 0; i < number_of_rbuttons; i++ ) {
				allrb[i].disabled = true;
				allchk[i].checked = false;
				allchk[i].disabled = true;
			}
			allrb[0].disabled = false;
			allrb[0].checked = true;
		// if there is only one field in use then enable and select/check the radio button in this row and disable the other radio buttons
		} else if ( 1 == nr_of_used_fields ) {
			if ( index_of_the_last_used_field != checked_rb_index ) {
				checked_rb_index = index_of_the_last_used_field;
			} 
			for (var i = 0; i < number_of_rbuttons; i++ ) {
				if ( i == checked_rb_index ) {
					allrb[i].disabled = false;
					allrb[i].checked = true;
					allchk[i].disabled = false;
					allchk[i].checked = false;
				} else {
					allrb[i].disabled = true;
					allchk[i].checked = false;
					allchk[i].disabled = true;
				}
			}
		// else enable the radio buttons right of all used txt boxes (, select/check one of them) and enable checkbox right of the selected radio button
		} else if ( 1 < nr_of_used_fields ) {
			for (var i = 0; i < number_of_rbuttons; i++ ) {
				if ( true == textb_status[i] ) {
					allrb[i].disabled = false;
				} else {
					if ( i == checked_rb_index ) {
						checked_rb_index = index_of_the_last_used_field;
						allrb[checked_rb_index].checked = true;
					}
					allrb[i].disabled = true;
					allchk[i].checked = false;
					allchk[i].disabled = true;
				}
			}
			allchk[checked_rb_index].disabled = false;
		}
		
		// set the helper to "unsaved_changes" (this will be overwritten after saving the textbox values)
		document.getElementById( 'customfieldlist_opt_'+ String(number) + '_set_links_helper' ).value = 'unsaved_changes';
		
		switch (list_type_opt2.checked) {
			case true :
				//~ if ( index_of_the_last_used_field != checked_rb_index ) {
					//~ checked_rb_index = index_of_the_last_used_field;
				//~ } 
				
				if ( 1 < nr_of_used_fields ) {
					allchk[checked_rb_index].checked = true; 
				}
			break;
			case false :
			default:
			break;
		}
	}
	
	function customfieldlist_radio_button_changed (name, number, index) {
		// enable only the checkbox right of the radio button when the radio button selection was changed
		// which radio button is selected
		var checked_rb_index = Number(index);

		// select (only) the checkbox in the same row
		var chkb_elements_name = 'customfieldlist_opt[' + String(number) + '][donnotshowthis_customfieldname][]';
		var allchk = document.getElementsByName(chkb_elements_name);
		var number_of_checkboxes = document.getElementsByName(chkb_elements_name).length;
		
		// disable all check boxes
		for (var i = 0; i < number_of_checkboxes; i++ ) {
			allchk[i].checked = false;
			allchk[i].disabled = true;
		}
		
		var nr_of_used_fields = customfieldlist_get_nr_of_used_txtb( Number(number) );
		
		// check the checkbox right of radio button
		if ( 1 < nr_of_used_fields ) {
			allchk[checked_rb_index].disabled = false;
		}	
	
		// for individual href option: - check the checkbox right of the radio button
		var list_type_opt2 = document.getElementById('customfieldlist_opt_' + String(number) + '_list_type_opt2');
		switch (list_type_opt2.checked) {
			case true :
				if ( 1 < nr_of_used_fields ) {
					allchk[checked_rb_index].checked = true;
				}
			break;
			case false :
			default:
			break;
		}
	}
	
	function customfieldlist_checkbox_changed (chbk_id, elements_name, number, index) {
		var list_type_opt2 = document.getElementById('customfieldlist_opt_' + String(number) + '_list_type_opt2');
		switch (list_type_opt2.checked) {
			case true :
				var nr_of_used_fields = customfieldlist_get_nr_of_used_txtb(number);
				
				if ( true == list_type_opt2.checked && 2 == nr_of_used_fields) {
					var rb_elements_name = 'customfieldlist_opt[' + String(number) + '][sort_by_custom_field_name]';
					var allrb = document.getElementsByName(rb_elements_name);
					customfieldlist_select_only_this_chbk(chbk_id, elements_name, false);
					allrb[index].checked = true;
				} else {
					customfieldlist_select_only_this_chbk(chbk_id, elements_name, true);
				}
			break;
			case false :
			default:
			break;
		}
	}
	
	function customfieldlist_get_nr_of_used_txtb(number) {
		var txtb_elements_name = 'customfieldlist_opt[' + String(number) + '][custom_field_names][]';
		var alltxtb = document.getElementsByName(txtb_elements_name);
		var number_of_txtb = alltxtb.length;
		
		//count the not empty fields
		var nr_of_used_fields = 0;
		for (var i = 0; i < number_of_txtb; i++ ) {
			var trimed_val = alltxtb[i].value.replace(/\s/g, '_' );
			if ( 0 < trimed_val.length ) {
				nr_of_used_fields++;
			}
		}
		return nr_of_used_fields;
	}
	
	function customfieldlist_select_only_this_chbk(chbk_id, elements_name, get_status) {
		var allchk = document.getElementsByName(elements_name);
		var number_of_checkboxes = document.getElementsByName(elements_name).length;
		switch (get_status) {
			case false :
				var status = true;
			break;
			default :
			case true :
				var status = document.getElementById(chbk_id).checked;
			break;
		}

		// uncheck all
		for (var i = 0; i < number_of_checkboxes; i++ ) {
			allchk[i].checked = false;
		}
	
		if ( false == status ) {
			// uncheck the one
			document.getElementById(chbk_id).checked = false;
		} else {
			// check the one
			document.getElementById(chbk_id).checked = true;
		}
	}
	
	function customfieldlist_show_this_explanation ( explanation_id ) {
		if ( document.getElementById(explanation_id).style.display == 'none' || document.getElementById(explanation_id).style.display == '' ) {
			document.getElementById(explanation_id).style.display = 'block';
		} else {
			document.getElementById(explanation_id).style.display = 'none';
		}
		return false;
	}
	//]]>
	</script>
	<?php
}

add_action('admin_print_scripts-widgets.php', 'customfieldlist_widget_enqueue_thickbox');
function customfieldlist_widget_enqueue_thickbox() {
	wp_enqueue_script( 'thickbox' );
}

add_action('admin_print_styles-widgets.php', 'customfieldlist_widget_admin_styles');
function customfieldlist_widget_admin_styles() {
	wp_enqueue_style( 'thickbox' );
	$stylefile = CUSTOM_FIELD_LIST_WIDGET_URL.'/widget_custom_field_list_admin.css';
	wp_enqueue_style( 'customfieldlist_widget_admin_style', $stylefile );
}
?>