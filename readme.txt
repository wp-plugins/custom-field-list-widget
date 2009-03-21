=== Custom Field List Widget ===
Contributors: ntm
Donate link: http://undeuxoutrois.de/custom_field_list_widget.shtml
Tags: custom field, meta information, guest list, widget, multiple widgets
Requires at least: 2.5
Tested up to: 2.7.1
Stable tag: 0.8.1

This plugin makes a list of custom field information in the sidebar.


== Description ==

This plugin lists all values of a choosable custom field name, groups the values of of a post and (hyper-) links the values to their posts
as a sidebar widget.
This allows you to create a list of one category of meta information. The custom field names and values can be used as categorizable tags and with this plugin you can create lists of tags of one category.

One example of usage could be: a list of the guest names of your podcast episodes. (with the default tags you can set the names
as tags but the names will probably be mixed with other content describing tags)

This plugin supports multiple widgets (You can have more than one list at the same time in one sidebar.) and uses the jQuery framework (which is delivered with WP automatically) to make the hide and show effects e.g. of a parted list (if the browser of a visiter of your does not allow or support Javascript the full list will be visible).

Furthermore this plugin has got a Add On for the famous K2 theme. You can use it with the K2 Sidebar Manager.

Available in the languages: English, German


== Installation ==

* Put the files and the folders from the .zip-file into a separate folder in the main plugins folder (e.g. /wp-content/plugins) of your weblog.
* The files should be stored like this:
 /wp-content/plugins/widget_custom_field_list/widget_custom_field_list.php
 /wp-content/plugins/widget_custom_field_list/widget_custom_field_list_js.php
 /wp-content/plugins/widget_custom_field_list/widget_custom_field_list.css
 /wp-content/plugins/widget_custom_field_list/customfieldlist-de_DE.mo (german localization file)
 /wp-content/plugins/widget_custom_field_list/customfieldlist-de_DE.po (german localization file)
 /wp-content/plugins/widget_custom_field_list/uninstall.php
 
 /wp-content/plugins/widget_custom_field_list/custom_field_list_k2_widget.php (move this file into the /app/modules/-folder of the K2-theme if you are using the K2 theme e.g.: /wp-content/themes/k2/app/modules/)

* Since WP 2.7 you can upload the .zip-file at once and the files will be put in the right place automatically - except for the K2 theme file.

* Activate the plugin.


== Screenshots ==

[Please, have a look at the plugins page.](http://undeuxoutrois.de/custom_field_list_widget.shtml "Screenshots at the plugins page")


== Frequently Asked Questions ==

No questions so far. (Please, have look to "Other notes" and "Usage".)


== Usage ==

Usage of "sort values by the last word" (since v0.7):
	
You can influence which word the last word is by using _ between the words. If you make a _ between two words it will be seen as one word.

example:

	names with more than one first and family name
		
	Jon Jake Stewart Brown
	the last word is Brown
		
	Jon Jake Stewart_Brown
	the last word is "Stewart Brown"

The _ will not displayed in the sidebar.


== Deinstallation ==

Deactivate the plugin.
(The options of the plugin in the options table of your weblog database going to be removed automatically during the plugin deactivation.)
Delete the folders and files of the plugin (don't forget the file from the K2 theme folder if you have used that).


== Change Log ==

v0.8.1:
-added an error message for the case that no values in connection to the choosen custom field name can be found
-changed a description (widgets page)

v0.8:
-first release at wordpress.org
