=== Custom Field List Widget ===
Contributors: ntm
Tags: custom field, meta information, guest list, widget, multiple widgets
Requires at least: 2.7
Tested up to: 3.0.1
Stable tag: 1.1.7

This plugin creates lists of custom field information in the form of sidebar widgets.


== Description ==

This plugin creates sidebar widgets with lists of the values of custom fields. The listed values can be (hyper-)linked in different ways. 
One possibility is to create a list of all values of a custom field, which will be groupped by their post (or page) and (hyper-)linked automatically to this post (or page).
Another possibility is that you can create a list of all unique values of a custom field and specify links as you like (or not).

In other words: This plugin allows you to create lists of one category of meta information (on each list). The custom field names and values can be used as categorizable tags and with this plugin you can create lists of tags of one category.

One example of usage could be: a list of the names of the guest of your podcast episodes. Or if you write about books hten you can have e.g. a list of the authors and as sub list elements the titles of the books linked to the posts containing the recension. For further details have a look to the [Other Notes](http://wordpress.org/extend/plugins/custom-field-list-widget/other_notes/) section.

This plugin supports multiple widgets (You can have more than one list at the same time in one sidebar.) and uses the jQuery framework (which is delivered with WP automatically) to make the hide and show effects e.g. of a parted list (if the browser of a visiter of your does not allow or support Javascript the full list will be visible).

Available in the languages:

* Bulgarian provided by Peter Toushkov
* Hindi provided by [Kakesh Kumar](http://kakesh.com "Kakesh Kumar of kakesh.com")
* Danish (frontend only) provided by [Peter Kirring](http://http://www.fotoblogger.dk/ "Peter Kirring of fotoblogger.dk/")
* German
* English
* Russian (only for v0.9.4 and v0.9.4.1) provided by [Michael Comfi](http://www.comfi.com/ "Michael Comfi of the ComFi.com, Corp.")
* Uzbek (only for v0.9.4 and v0.9.4.1) provided by [Alisher Safarov](http://www.comfi.com/ "Alisher Safarov of the ComFi.com, Corp.")

If like to translate this plugin into your language or if you like to continue the existing translations then you are very welcome. Contact me and I will help you with the first steps.

== Installation ==

1. Put the files and the folders from the .zip-file into a separate folder in the main plugins folder (e.g. /wp-content/plugins) of your weblog.
	The files should be stored in a path like this
	
	* /wp-content/plugins/widget\_custom\_field\_list/
	
	Files of the plugin:
	
	* widget\_custom\_field\_list.php
	* widget\_custom\_field\_list\_js.php
	* widget\_custom\_field\_list\_individual\_href.php
	* widget\_custom\_field\_list\_individual\_href\_advice.php
	* widget\_custom\_field\_list\_individual\_href\_save\_data.php
	* widget\_custom\_field\_list\_long\_selectbox.php
	* widget\_custom\_field\_list.css
	* widget\_custom\_field\_list\_widgetsettings.css
	* customfieldlist-de\_DE.mo (German localization file)
	* customfieldlist-de\_DE.po (German localization file)
	* customfieldlist-bg\_BG.mo (Bulgarian localization file)
	* customfieldlist-bg\_BG.po (Bulgarian localization file)
	* customfieldlist-hi\_IN.mo (Hindi localization file)
	* customfieldlist-hi\_IN.po (Hindi localization file)
	* customfieldlist-dk\_DK.mo (Danish localization file)
	* customfieldlist-dk\_DK.po (Danish localization file)
	* customfieldlist-ru\_RU.mo (Russian localization file)
	* customfieldlist-ru\_RU.po (Russian localization file)
	* customfieldlist-uz\_UZ.mo (Uzbek localization file)
	* customfieldlist-uz\_UZ.po (Uzbek localization file)
	* uninstall.php
	
	* custom\_field\_list\_k2\_widget.php (move this file into the /app/modules/-folder (e.g.: /wp-content/themes/k2/app/modules/) of the K2-theme if you are using the K2 theme with the old sidebar manager or the comparable plugin)
	
	* readme.txt (all about this plugin)
	* license.txt (license of the plugin: GNU GPL v2 in English)

1. Since WP 2.7 you can upload the .zip-file at once and the files will be put in the right place automatically - except for the K2 theme file.
1. Activate the plugin.


== Screenshots ==

[Please, have a look at the plugins page.](http://undeuxoutrois.de/custom_field_list_widget.shtml "Screenshots at the plugins page")


== Frequently Asked Questions ==

How can I keep the individual Links during an plugin upgrade?

Don't deactivate the plugin. Because the options of the plugin in the options table of your weblog database are going to be removed automatically during the plugin deactivation.
Further you have to upload the file of the new plugin version manually e.g. via FTP. Because during the automatic upgrade the old plugin would be deactivated. 

Is it possible to use this widget with the K2 theme sidebar manager (plugin)?
Yes, it is possible. Move the file custom\_field\_list\_k2\_widget.php to the /app/modules/-folder (e.g.: /wp-content/themes/k2/app/modules/) of the K2-theme if you are using the K2 theme with the old sidebar manager or the comparable plugin.


(Please, have look to "[Other Notes](http://wordpress.org/extend/plugins/custom-field-list-widget/other_notes/)" / "Usage" for further information, too.)

If you have questions then ask them on the [plugins page](http://undeuxoutrois.de/custom_field_list_widget.shtml#kommentar_schreiben) or in the [WP.org forum "Plugins and Hacks"](http://wordpress.org/support/forum/10). If you use the WP.org forum then add the plugins name as a tag to your post.


== Usage ==

= Lists with more than one hierarchy level =
(since v0.9.5)

You can define several custom field names for a post. The values of these custom field names will be taken from the data base like they were arranged in columns side by side.
All the values will be sorted by one of these columns. You can choose which column resp. custom field name should be the sort criterion. Further it is possible to hide this column (which should be decisive for the sorting) in the list in the sidebar.

Example:
An easy to understand example is probably a list of posts which are categorized by special dates. In this example the custom field names are "realdate" and "datename". As "realdate" you set good sortable text strings or numbers like 20091005 (consists of year, month, day) and as "datename" you can set e.g. "October 2009" which is probably not in the same way sortable as the numbers. This way you can of course build probably more useful lists. A more colourful case of use which works this way is probably if you make list of posts grouped by a none-Gregorian calendar. You can use as datenames e.g. names of months and years of the Chinese calendar. 
In this example the "datenname"s would be the main list elements and the post titles sub elements of them. The "realdate" would be hidden via the option "hide this".

If you want to create a more sub-divided list (instead of "datename" something like "year", "month", "day") then you should keep an eye on the right hierarchy order.
(e.g. 0. "realdate" (sort by / hide this), 1. "year", 2. "month", 3. "day"). The post titles will be sub elements of "day" and "day" will be sub elements of the months and so on - in the list in the side bar widget. (But for this special case there other plugins like [Collapsing Archives](http://wordpress.org/extend/plugins/collapsing-archives/ "Collapsing Archives on wordpress.org"))

Other and even better examples (in English) are the book lists at the [weblog of Larry Wilson](http://www.wilsonld.com/weblog/ "Larry Wilson's blog"). He makes list of the books he has read.

There are some basic things you should be aware of:
* Overall you can use 5 hierarchical levels (if you want to have more then write me an email).
* Every post you want to have in the sidebar list should have the same custom field names (The custom field values can differ from post to post.)
* The custom field name which should be excluded from the list should be the first or the last in the list on the widgets page (on the admin site).


= Usage of the sorting option "sorting values by the last word" =
(since v0.7)
	
You can influence which word the last word is by using a underline character between the words. If you make a underline character between two words it will be seen as one word.

Example:

	names with more than one first and family name
		
	Jon Jake Stewart Brown
	the last word is Brown
		
	Jon Jake Stewart_Brown
	the last word is "Stewart Brown"

The underline character will not displayed in the sidebar.

== Changing the appearance of the lists ==

Since v1.0 all click-able drop down menu elements have the class "customfieldlist_opt_link" so you can adapt the appearance of these elements easier.

The speed of the show/hide effects and the hide/show characters are changeable via the settings page of the plugin. The settings on this page are for all the widgets of this plugin.


== Upgrade Notice ==

ATTENTION: The options of the plugin in the options table of your weblog database are going to be removed automatically during the plugin deactivation.
This is important because the old plugin version will be deactivated during the automatic upgrade process!

To keep your settings download the plugin archive file, unpack him and upload the plugin files to the plugin folder manually.


== Deinstallation ==

1. Deactivate the plugin.
1. (The options of the plugin in the options table of your weblog database are going to be removed automatically during the plugin deactivation.)
1. Delete the folders and files of the plugin (don't forget the file from the K2 theme folder if you have used that).


== Changelog ==
= v1.2 =
* new option: sort list elements by publication date of the post
* new option: shows the drop down menu in a full screen box ([Thickbox](http://jquery.com/demo/thickbox/))
* new options: it is also possible to limit the number of characters per list element of the drop down menus

= v1.1.7 =
* Bugfix: all hyper links will be created with get_permalink instead of the GUID value of an article

= v1.1.6 =
* Bugfix: fixed a problem with the settings page of this plugin. Users will need the capability [manage_links](http://codex.wordpress.org/Roles_and_Capabilities#manage_links) to access this page. (In older version it was Level 8 which means Editor and an editor can manage links. (User levels are deprecated.))

= v1.1.5 =
* Bugfix: fixed wrong function name: attribute_escape instead of attributes_escape

= v1.1.4 =
* a different way to escape the link titles

= v1.1.3 =
* the way, how the language files are loaded, has been updated
* since the "each element with sub elements" option is an option for "simple list" as well as for "Drop Down Menu", it haved moved a little bit
* if the header of the widget is empty then there will be no empty header-tag

= v1.1.2 =
* all frontend text passages are now translated into Danish (Thanks to [Peter Kirring](http://http://www.fotoblogger.dk/ "Peter Kirring of fotoblogger.dk/"))
* I have enhanced the German language files and switch from the old translation of custom fields "Spezialfelder" to the new one "Benutzerdefinierte Felder".

= v1.1.1 =
* bug fix for v1.1 - additional Javascript to enable/disable the checkbox in case the option "sort the values by the last word" is in use

= v1.1 =
* contains fixes for the sorting order settings

= v1.0 =
* Version 0.9.9 came with the new drop down menu option but it was only possible to have the values displayed like with the option "each element with sub elements" and there was no way to change by a setting on the widgets page. That is different in version 1.0. In that version you have to select this option actively.
* additional CSS definition for the drop down menu width (=100%)
* the widget_custom_field_list.css file contains now predefined CSS definition for making the clickable elements in the drop down menus look like links (< a > elements) (the color definitions are for the default themes)
* all clickable drop down menu elements have the class "customfieldlist_opt_link"
* new function: it is now possible to sort the post titles (which are sub list elements) alphabetically (only in combination with the "standard layout" list type)
* fix: "sort the values by the last word" works with "standard layout" list type again
* small clean up of enqueue_script action

= v0.9.9 =
* new list appearance option: now it is to display the data in a drop down menu
* internal changes of the way the list with individual linked list elements
* possibility to leave the widget header/title blank
* new feature: you can group the custom field values by thier first character
* minor Javascript fixes

= v0.9.7 =
* this version adds the Javascript for the lists effects to the page heads only outside of the admin site

= v0.9.6 =
* new feature: you can now choose the type of the pagination of the list parts if you use the option "show only a part of the list elements at once". You can choose between pagination with consecutive numbers and several types of strings.
* new feature: now it is possible to fade in the number of sub elements of a list element.
* new feature: possibility to select the Hide/Show symbols (if a list element has sub elements) 
* new feature: possibility to select the Hide/Show effect speed
* fix for a bug in the pagination while the list type "a list with manually linked values"
* fix for the highlighting of the pagination
* fix for invalid usage of the attribute "name" with list elements
* better escaping of the link titles
* replaced some hard coded db table names with dynamic ones

= v0.9.5 =
* new feature: you are free to define more than one custom field name and print e.g. a list with several hierarchy levels. Further you can select one of these custom field as an order column.
* added Hindi language files (Thanks to [Kakesh Kumar](http://kakesh.com "Kakesh Kumar"))
* several small bugs fixed. the plugin is again useable with the IE.
* (Russian and Uzbek language files are not updated)

= v0.9.4.1 =
* new feature: you can choose the sort sequence (ascending / descending)
* small bugs fixed. the plugin is again useable with the IE.
* added Uzbek language files (Thanks to [Alisher Safarov](http://www.comfi.com/ "Alisher Safarov of the ComFi.com, Corp."))

= v0.9.4 =
* added Russian language files (Thanks to [Michael Comfi](http://www.comfi.com/ "Michael Comfi of the ComFi.com, Corp."))

= v0.9.3 =
* added a new layout option to the widgets preferences: you can create a list of all (unique) custom field values of a custom field (name). Each value can be linked individually to a post or a page or to something else.

= v0.9.2 =
* Fix for v0.9 and v0.9.1: I have changed the HTML structure of the widgets setting form. That corrects a problem which appaers if your weblog runs on a Windows server. These changes inluding little changes in the language files, too.

= v0.9.1 =
* Fix for v0.9: I have replaced some hardcoded folder names. The jQuery effects e.g. should work now after an automatic update, too. 

= v0.9 =
* added a new layout option to the widgets preferences
* bulgarian localization (Thanks to Peter Toushkov)
* a lot of bugs fixed including a better support for non-English character sets (Many thanks to Peter Toushkov for the diligent testing and reporting)

= v0.8.1 =
* added an error message for the case that no values in connection to the choosen custom field name can be found
* changed a description (widgets page)

= v0.8 =
* first release at wordpress.org