��          �   %   �      0  y  1  X   �            3   (     \  )   n  5  �  #   �  �   �  V   �  Q   �  �   K  r   (  G   �     �     �     	     3	     6	  -   ;	      i	     �	  %  �	  u  �  k   6     �     �  L   �           ,  J  M  -   �  �   �  u   y  ]   �    M  x   [  C   �       $   +     P     h     k  5   p  +   �     �                                  	                 
                                                                            1. enter your <a href="http://msdn.microsoft.com/en-gb/library/39cwe7zf.aspx" target="_blank">language</a> and <a href="http://msdn.microsoft.com/en-gb/library/cdax410z.aspx" target="_blank">country</a> name and eventually the <a href="http://en.wikipedia.org/wiki/Windows_code_pages" target="_blank">code page number</a> (like german_germany or german_germany.1252 for German) 2. select the (same) code page in the form PHP can handle (e.g. Windows-1252 for German) Custom Field List Custom Field Name Displays a list of custom field values of a set key Header (optional) Leave the field empty for no widget title Only list elements of custom field names with more than one custom field value have sub elements. These sub elements becoming visible by clicking on the custom field name list elements or the + sign. The other list elements with one value are the hyper links to the posts and the values are in the link title. Please, define a custom field name! Shows each custom field name as a list element with the custom field value as a sub element. All sub elements are every time visible and they are the hyper links to the posts. The server OS is Windows (which is not able to sort UTF-8) what makes it necessary to: There are no values in connection to the custom field name %1$s in the data base. This option will probably not work on this server because this plugin converts the encoding of the meta values to the encoding of the OS (Windows) with the function mb_convert_encoding but this function is not available. This option will probably not work. Because it is not possible to set "setlocale(LC_COLLATE, ... " on this server. Unable to retrieve the data of the customfield list widget from the db. database collation each element with sub elements elements per part of the list in part show only a part of the list elements at once sort the values by the last word standard layout Project-Id-Version: Custom Field List Widget v0.9.2
Report-Msgid-Bugs-To: 
POT-Creation-Date: 2009-05-30 11:46+0100
PO-Revision-Date: 2009-05-30 11:50+0100
Last-Translator: Tim Berger <timberge@cs.tu-berlin.de>
Language-Team: Tim Berger
MIME-Version: 1.0
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit
X-Poedit-Language: German
X-Poedit-Country: GERMANY
X-Poedit-KeywordsList: __;_e
X-Poedit-Basepath: .
X-Poedit-SourceCharset: utf-8
X-Poedit-SearchPath-0: H:\xampp\htdocs\wp27en\wp-content\plugins\widget_custom_field_list
 1. die <a href="http://msdn.microsoft.com/en-gb/library/39cwe7zf.aspx" target="_blank">Sprache</a> und den <a href="http://msdn.microsoft.com/en-gb/library/cdax410z.aspx" target="_blank">Landesnamen</a> und eventuell die <a href="http://en.wikipedia.org/wiki/Windows_code_pages" target="_blank">Code Page Nummer</a> (z.B. german_germany oder german_germany.1252) einzugeben 2. die (gleiche) Code Page in der Form auszuw&auml;hlen, in der PHP damit umgehen kann ( z.B. Windows-1252) Spezialfeldliste Spezialfeldname Zeigt eine Liste von Spezialfeldwerten eines bestimmten Spezialfeldnamens an &Uuml;berschrift (optional) kein Titel: das Feld leer lassen Nur Listenlemente von Spezialfeldnamen mit mehr als einem Spezialfeldwert haben Unterpunkte. Diese Unterpunkte werden sichtbar, wenn man auf den Spezialfeldnamen oder das + Zeichen klickt. Die anderen Listenelemente mit nur einem Spezialfeldwert sind selbst die Links zu den Beitr&auml;gen und die Spezialfeldwerte die Linktitel.  Bitte, definieren Sie einen Spezialfeldnamen! Stellt jeden Spezialfeldnamen als Listenelement mit dem Spezialfeldwert als Unterpunkt dar. Alle Unterpunkte sind jeder Zeit sichbar und sie sind die Links zu den Beitr&auml;gen. Das Serverbetriebssystem ist Windows (welches kann nicht UTF-8 kodierte Zeichen sortieren), weshalb es notwendig ist: Es gibt keine, mit dem Spezialfeldnamen %1$s in Verbindung stehenden, Werte in der Datenbank. Diese Option funktioniert wahrscheinlich auf diesem Server nicht, weil dieses Plugin das Encoding der Spezialfeldwerte auf das des Serverbetriebssystems (Windows) &auml;ndert muss, wof&uuml;r es die Funktion mb_convert_encoding nutzt, die aber nicht verf&uuml;gbar ist. Diese Option funtioniert wahrscheinlich nicht, weil es nicht m&ouml;glich ist "setlocale(LC_COLLATE, ... " zu verwenden. Es konnten keine Daten des Widgets aus der Datenbank geladen werde. Datenbankkollation jedes Listenelement mit Unterpunkten Elemente pro Listenteil in Teil nur einen Teil der Listenelemente auf einmal anzeigen die Werte nach deren letztem Wort sortieren Standardlayout 