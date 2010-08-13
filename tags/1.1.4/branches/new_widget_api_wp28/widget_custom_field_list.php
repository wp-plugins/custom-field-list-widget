<?php
/*
Plugin Name: Custom Field List Widget
Plugin URI: http://undeuxoutrois.de/custom_field_list_widget.shtml
Description: This widget lists all values of a custom field, groups equal values and (hyper-) links the values to their posts. || Dieses Widget erzeugt eine Liste aus den Werten eines Spezialfeldes, gruppiert mehrfach vorkommende Werte und verlinkt die Werte ihren Beitr&auml;gen.
Author: Tim Berger
Version: 0.9.5 RC 1
Author URI: http://undeuxoutrois.de/custom_field_list_widget.shtml
Min WP Version: 2.8
Max WP Version: 
License: GNU General Public License

Requirements:
	- WP 2.8 or newer
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

// definition of 2 constants (the actual url and directory of the this plugin)
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

// The widget class (for the widget API of WP 2.8 and newer)
class Custom_Field_List_Widget extends WP_Widget {
	// Constructor
	function Custom_Field_List_Widget() {
		// Variables for our widget
		$widget_ops = array(
			'classname' => 'customfieldlist',
			'description' => __('Displays a list of custom field values of a set key', 'customfieldlist')
		);
		// Variables for our widget options panel
		$control_ops = array(
			'width' => 400,
			'height' => 310,
		);
		$this->WP_Widget(FALSE, __('Custom Field List', 'customfieldlist'), $widget_ops, $control_ops);
	}

	// Display Widget
	function widget($args, $instance) {
		global $wpdb;
		extract( $args, EXTR_SKIP );
		
		$partlength = intval($instance['partlength']);
		
		if ( !empty($instance['title']) ) {
			$header = $instance['title'];
		} else {
			$header =  __('Custom Field List','customfieldlist');
		}
		
		echo $before_widget."\n";
			echo $before_title.$header.$after_title . "\n";
			echo '<input type="hidden" name="customfieldlist_widget_id" value="'.$this->number.'"'." />\n";
			if ('yes' === $instance['partlist'] AND $partlength >= 3) {
				echo '<input type="hidden" id="customfieldlistpartlist_'.$this->number.'" value="yes"'." />\n";
			} else {
				echo '<input type="hidden" id="customfieldlistpartlist_'.$this->number.'" value="no"'." />\n";
			}
			if (is_user_logged_in()) {
				$only_public = '';
			} else {
				$only_public = ' AND p.post_status = "publish"';
			}
			$j=$k=0;
			
			echo "<ul><!-- ul begin -->\n";
			if ( !empty($instance['customfieldname']) ) {
				if ( (defined('DB_COLLATE') AND '' != DB_COLLATE) OR (isset($instance['db_collate']) AND !empty($instance['db_collate'])) ) {
					if ( '' == DB_COLLATE ) {
						$collation_string = $instance['db_collate'];
					} else {
						$collation_string = DB_COLLATE;
					}
					$querystring = 'SELECT pm.post_id, pm.meta_value, p.guid, p.post_title FROM '.$wpdb->postmeta.' AS pm LEFT JOIN '.$wpdb->posts.' AS p ON (p.ID = pm.post_id) WHERE pm.meta_key = "'.$instance['customfieldname'].'"'.$only_public.' ORDER BY pm.meta_value COLLATE '.$collation_string.', LENGTH(pm.meta_value)';
				} else {
					$querystring = 'SELECT pm.post_id, pm.meta_value, p.guid, p.post_title FROM '.$wpdb->postmeta.' AS pm LEFT JOIN '.$wpdb->posts.' AS p ON (p.ID = pm.post_id) WHERE pm.meta_key = "'.$instance['customfieldname'].'"'.$only_public.' ORDER BY pm.meta_value, LENGTH(pm.meta_value)';
				}
				
				$meta_values =  $wpdb->get_results($querystring);
				$nr_meta_values = count($meta_values);
				
				if ($nr_meta_values > 0) {
					if ( 'lastword' === $instance['orderelement'] ) {
						$mvals=array();
						$old_locale = setlocale(LC_COLLATE, "0");
						
						if (FALSE !== strpos(strtolower(php_uname('s')), 'win') AND function_exists('mb_convert_encoding')) {
							for ( $i=0; $i < $nr_meta_values; $i++ ) {
								$mvals[] = mb_convert_encoding(str_replace("_", " ", end(preg_split("/\s+/u", $meta_values[$i]->meta_value, -1, PREG_SPLIT_NO_EMPTY))), $instance['encoding_for_win']);
							}
							// build the charset name and setlocale on Windows machines 
							$loc = setlocale(LC_COLLATE, $instance['win_country_codepage']);
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
						if ( 'lastword' === $instance['orderelement'] ) {
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
							
						switch ($instance['list_layout']) {
							case 'each_element_with_sub_element' :
								$singlevisit = TRUE;
								if ( $meta_value != $meta_value_minus_one AND $meta_value == $meta_value_plus_one AND $nr_meta_values > 1 ) {
									echo "\t<li name=".'"customfieldlistelements_'.$this->number.'_'.$j.'"'.">\n\t".$meta_value."\n\t".'<ul>'."\n";
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
									echo "\t".'<li name="customfieldlistelements_'.$this->number.'_'.$j.'">'.$meta_value.'<ul><li><a href="'.get_permalink($meta_values[$key]->post_id).'" title="'.$meta_value." ".__('in','customfieldlist')." ".$meta_values[$key]->post_title.'">'.$meta_values[$key]->post_title."</a></li></ul></li>\n";
									$k++;
								}
							break;
							default :
								$singlevisit = TRUE;
								if ( $meta_value != $meta_value_minus_one AND $meta_value == $meta_value_plus_one AND $nr_meta_values > 1 ) {
									echo "\t<li name=".'"customfieldlistelements_'.$this->number.'_'.$j.'"'.">\n\t".'<span class="customfieldtitle">'.$meta_value.'</span> <span class="customfieldplus">[ - ]</span>'."<br />\n\t".'<ul class="customfieldsublist">'."\n";
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
									echo "\t".'<li name="customfieldlistelements_'.$this->number.'_'.$j.'"><a href="'.get_permalink($meta_values[$key]->post_id).'" title="'.$meta_value." ".__('in','customfieldlist')." ".$meta_values[$key]->post_title.'">'.$meta_value."</a></li>\n";
									$k++;
								}
							break;
						}
						if ( ($k > 0) AND ($partlength < $nr_meta_values) AND $k !== $k_odd AND 0 === ($k % $partlength) ) {//($k > 0) AND ($partlength < $nr_meta_values) AND
							$j++;
						}
						$k_odd = $k;
					}
				} else {
					echo "<li>".sprintf(__('There are no values in connection to the custom field name %1$s in the data base.','customfieldlist'), $instance['customfieldname'])."</li>\n";
				}
			} else {
				echo "<li>".__('Please, define a custom field name!','customfieldlist')."</li>\n";
			}
			echo "</ul><!-- ul end --> \n";
			
			
			echo '<input type="hidden" id="customfieldlistelements_'.$this->number.'" value="'.$j.'"'." />\n";
			if ($j > 0 AND $k > $partlength) {
				echo '<p class="customfieldlistpages" id="customfieldlistpages_'.$this->number.'"'.">\n";
				echo __('part','customfieldlist').": ";
					if ( 0 === ($k % $partlength) ) {
						for ($i=0; $i<$j; $i++) {
							echo '[<a href="javascript:show_this_customfieldlistelements('.$i.', '.$j.', '.$this->number.');"> '.($i+1).' </a>] ';
						}
					} else {
						for ($i=0; $i<=$j; $i++) {
							echo '[<a href="javascript:show_this_customfieldlistelements('.$i.', '.$j.', '.$this->number.');"> '.($i+1).' </a>] ';
						}
					}
				echo "</p>\n";
			}
		echo $after_widget."<!-- after_widget -->\n";

	}

	// When Widget Control Form Is Posted
	function update($new_instance, $old_instance) {
		if (!isset($new_instance['customfieldlist-submit'])) {
			return false;
		}
		
		$instance = $old_instance;
		
		$instance['title'] = strip_tags(stripslashes(trim($new_instance['title'])));
		$instance['customfieldname'] = strip_tags(stripslashes(trim($new_instance['customfieldname'])));
		if ( !isset($new_instance['list_layout']) OR 'standard' === $new_instance['list_layout'] ) {
			$instance['list_layout'] = 'standard';
		} else {
			$instance['list_layout'] = 'each_element_with_sub_element';
		}
		if ( isset($new_instance['partlist']) ) {
			$instance['partlist'] = 'yes';
		} else {
			$instance['partlist'] = 'no';
		}
		if ( isset($new_instance['orderelement']) ) {
			$instance['orderelement'] = 'lastword';
		} else {
			$instance['orderelement'] = 'firstword';
		}
		$instance['db_collate'] = strip_tags(stripslashes(trim($new_instance['db_collate'])));
		$instance['win_country_codepage'] = strip_tags(stripslashes(trim($new_instance['win_country_codepage'])));
		$instance['encoding_for_win'] = strip_tags(stripslashes(trim($new_instance['encoding_for_win'])));
		$instance['partlength'] = intval(strip_tags(stripslashes(trim($new_instance['partlength']))));
		if ( is_nan($instance['partlength']) OR $instance['partlength'] < 3 ) {
			$instance['partlength'] = 3;
		}
		
		return $instance;
	}
 
	// Display Widget Control Form
	function form($instance) {
		global $wpdb;
		$instance = wp_parse_args((array) $instance, array('title' => __('Custom Field List','customfieldlist'), 'customfieldname' => '', 'list_layout' => 'standard', 'db_collate' => '', 'encoding_for_win' => '', 'orderelement' => '', 'partlist' => 'no', 'partlength' => 3));
		
		echo '<p style="text-align:center;"><label for="'.$this->get_field_id('title').'">'.__('Header (optional)','customfieldlist').':</label> <input type="text" id="'.$this->get_field_id('title').'" name="'.$this->get_field_name('title').'" value="'.attribute_escape($instance['title']).'" maxlength="200" /><br /><span style="font-size:0.8em;">('.__('Leave the field empty for no widget title','customfieldlist').')<span></p>';
		
		echo '<p style="text-align:right;">'.__('Custom Field Name','customfieldlist').': <input type="text" name="'.$this->get_field_name('customfieldname').'" value="'.attribute_escape($instance['customfieldname']).'" maxlength="200" /></p>';
		
		// section: select the layout
		echo '<div style="text-align:right; margin-bottom:3px;">';
		if ( !isset($instance['list_layout']) OR 'standard' === $instance['list_layout'] ) {
			$listlayoutopt1chk = ' checked="checked"';
			$listlayoutopt2chk = '';
		} else {
			$listlayoutopt1chk = '';
			$listlayoutopt2chk = ' checked="checked"';
		}
		echo '<label for="'.$this->get_field_id('list_layout').'-1">'.__('standard layout','customfieldlist').' <input type="radio" name="'.$this->get_field_name('list_layout').'" id="'.$this->get_field_id('list_layout').'-1" value="standard" '.$listlayoutopt1chk.' /></label>'."<br /> \n";
		echo '<p style="color:#999;">'.__('Only list elements of custom field names with more than one custom field value have sub elements. These sub elements becoming visible by clicking on the custom field name list elements or the + sign. The other list elements with one value are the hyper links to the posts and the values are in the link title.','customfieldlist').'</p>';
		echo '<label for="'.$this->get_field_id('list_layout').'-2">'.__('each element with sub elements','customfieldlist').' <input type="radio" name="'.$this->get_field_name('list_layout').'" id="'.$this->get_field_id('list_layout').'-2" value="each_element_with_sub_element" '.$listlayoutopt2chk.' /></label>';
		echo '<p style="color:#999;">'.__('Shows each custom field name as a list element with the custom field value as a sub element. All sub elements are every time visible and they are the hyper links to the posts.','customfieldlist').'</p>';
		echo '</div>';
		
		// section: select DB_CHARSET
		if (FALSE == defined('DB_COLLATE')) {
			echo '<p style="text-align:right;"><a href="http://dev.mysql.com/doc/refman/5.1/en/charset-charsets.html" target="_blank">'.__('database collation','customfieldlist').'</a>: <input type="text" name="'.$this->get_field_name('db_collate').'" value="'.attribute_escape($instance['db_collate']).'" maxlength="200" /></p>';
		}
		
		// section: "sort by the last word" preferences
		$old_locale = setlocale(LC_COLLATE, "0"); // get the actual locale of the server
		$loc = setlocale(LC_COLLATE, WPLANG.'.'.get_bloginfo('charset'), WPLANG, 'english_usa'); // try to set the locale with the Linux and Windows notation of the language shortage
		setlocale(LC_COLLATE, $old_locale); //set value back to the original
		if (FALSE === $loc) { // if it is not possible to set the locale show a message else show a possibility to select the encoding and the abbreviation for the language
			$message_setloc = '<span class="error" style="display:block; text-align:left;">'.__('This option will probably not work. Because it is not possible to set "setlocale(LC_COLLATE, ... " on this server.','customfieldlist').'</span>';
		} else {
			if (FALSE !== strpos(strtolower(php_uname('s')), 'win')) {
				// if the server OS string in the PHP configuration contains 'win' (if the server OS is probably Windows) then ....
				if (function_exists('mb_convert_encoding')) {
					// the encoding which PHP multibyte supports  http://www.php.net/manual/en/mbstring.supported-encodings.php (without these: 'UTF-32', 'UTF-32BE', 'UTF-32LE', 'UTF-16', 'UTF-16BE', 'UTF-16LE', 'UTF-7', 'UTF7-IMAP', 'UTF-8',
					$encodings = array('UCS-4', 'UCS-4BE', 'UCS-4LE', 'UCS-2', 'UCS-2BE', 'UCS-2LE', 'ASCII', 'EUC-JP', 'SJIS', 'eucJP-win', 'SJIS-win', 'ISO-2022-JP', 'JIS', 'ISO-8859-1', 'ISO-8859-2', 'ISO-8859-3', 'ISO-8859-4', 'ISO-8859-5', 'ISO-8859-6', 'ISO-8859-7', 'ISO-8859-8', 'ISO-8859-9', 'ISO-8859-10', 'ISO-8859-13', 'ISO-8859-14', 'ISO-8859-15', 'byte2be', 'byte2le', 'byte4be', 'byte4le', 'BASE64', 'HTML-ENTITIES', '7bit', '8bit', 'EUC-CN', 'CP936', 'HZ', 'EUC-TW', 'CP950', 'BIG-5', 'EUC-KR', 'UHC (CP949)', 'ISO-2022-KR', 'Windows-1251 (CP1251)', 'Windows-1252 (CP1252)', 'CP866 (IBM866)', 'KOI8-R');
					$message_os = '<span class="updated" style="display:block; text-align:left; margin-bottom:30px;">'.__('The server OS is Windows (which is not able to sort UTF-8) what makes it necessary to:','customfieldlist').'<br />';
					$message_os .= __('1. enter your <a href="http://msdn.microsoft.com/en-gb/library/39cwe7zf.aspx" target="_blank">language</a> and <a href="http://msdn.microsoft.com/en-gb/library/cdax410z.aspx" target="_blank">country</a> name and eventually the <a href="http://en.wikipedia.org/wiki/Windows_code_pages" target="_blank">code page number</a> (like german_germany or german_germany.1252 for German)','customfieldlist').': <input type="text" name="'.$this->get_field_name('win_country_codepage').'" value="'.attribute_escape($instance['win_country_codepage']).'" maxlength="200" style="width:100%;" /><br />';
					$message_os .= __('2. select the (same) code page in the form PHP can handle (e.g. Windows-1252 for German)','customfieldlist').': ';
					$message_os .= '<select name="'.$this->get_field_name('encoding_for_win').'">';
					foreach ($encodings as $encoding) {
						$stored_encoding = attribute_escape($instance['encoding_for_win']);
						if ($encoding == $stored_encoding) {
							$message_os .= '<option selected="selected">'.$encoding.'</option>';
						} else {
							$message_os .= '<option>'.$encoding.'</option>';
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
		if ( 'lastword' === $instance['orderelement'] ) {
			echo '<p style="text-align:right;"'.$message_os_asterisk.'>'.__('sort the values by the last word','customfieldlist').': <input type="checkbox" name="'.$this->get_field_name('orderelement').'" value="lastword" checked="checked" /></p>'.$message_os.$message_setloc.'';
		} else {
			echo '<p style="text-align:right;"'.$message_os_asterisk.'>'.__('sort the values by the last word','customfieldlist').': <input type="checkbox" name="'.$this->get_field_name('orderelement').'" value="lastword" /></p>'.$message_os.$message_setloc.'';
		}
		if ( 'yes' == $instance['partlist'] ) {
			echo '<p style="text-align:right;">'.__('show only a part of the list elements at once','customfieldlist').': <input type="checkbox" name="'.$this->get_field_name('partlist').'" value="yes" checked="checked" /></p>';
		} else {
			echo '<p style="text-align:right;">'.__('show only a part of the list elements at once','customfieldlist').': <input type="checkbox" name="'.$this->get_field_name('partlist').'" value="yes" /></p>';
		}
		
		echo '<p style="text-align:right;">'.__('elements per part of the list','customfieldlist').' (X>=3): <input type="text" name="'.$this->get_field_name('partlength').'" value="'.$instance['partlength'].'" maxlength="5" style="width:5em;" /></p>';
		echo '<input type="hidden" id="'. $this->get_field_id('customfieldlist-submit').'" name="'. $this->get_field_name('customfieldlist-submit').'" value="1" />';
	}
}

add_action('widgets_init', 'customfieldlist_widget_init');
function customfieldlist_widget_init() {
	register_widget('Custom_Field_List_Widget');
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
?>