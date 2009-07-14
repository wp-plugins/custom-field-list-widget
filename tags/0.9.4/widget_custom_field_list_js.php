<?php 
header('Content-type: application/x-javascript');
// if jQuery is available and working then deflate the sublists and change the switch sign after the page is loaded ?>
jQuery(window).load(function(){
	jQuery(".customfieldplus").text("[ + ]");
	jQuery(".customfieldsublist").hide();
	
	var widget_numbers = jQuery("[name='customfieldlist_widget_id']");

	for (var j=0; j < widget_numbers.length; j++) {
		if ('yes' == jQuery("#customfieldlistpartlist_" + String(widget_numbers[j].value)).val()) {
			var li_els=jQuery("#customfieldlistelements_" + String(widget_numbers[j].value)).val();
			for ( i=1; i <= li_els; i++ ) {
				jQuery("[name='customfieldlistelements_" + String(widget_numbers[j].value)+ "_" + String(i) + "']").hide();
			}
			jQuery("#customfieldlistpages_" + String(widget_numbers[j].value)).show();
		}
	}
});

<?php // inflate or deflate the sublists ?>
jQuery(document).ready(function(){
	jQuery(".customfieldplus").click(function() {
		var field = jQuery(this).parent().children(".customfieldplus");
		if ("[ + ]" == field.text()) {
			field.text("[ - ]");
			jQuery(this).parent().children(".customfieldsublist").show("slow");
		} else {
			field.text("[ + ]");
			jQuery(this).parent().children(".customfieldsublist").hide("slow");
		}
	});
	jQuery(".customfieldtitle").click(function() {
		var field = jQuery(this).parent().children(".customfieldplus");
		if ("[ + ]" == field.text()) {
			field.text("[ - ]");
			jQuery(this).parent().children(".customfieldsublist").show("slow");
		} else {
			field.text("[ + ]");
			jQuery(this).parent().children(".customfieldsublist").hide("slow");
		}
	});
});

function show_this_customfieldlistelements( list, lists, number ) {
	for ( i=0; i <= lists; i++ ) {
		if ( i == Number(list) ) {
			jQuery( "[name='customfieldlistelements_" + String(number) + "_" + String(i) + "']" ).show("normal");
		} else {
			jQuery( "[name='customfieldlistelements_" + String(number) + "_" + String(i) + "']" ).hide("normal");
		}
	}
}