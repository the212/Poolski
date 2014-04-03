/*
 * Another In Place Editor - a jQuery edit in place plugin
 *
 * Copyright (c) 2009 Dave Hauenstein
 *
 * License:
 * This source file is subject to the BSD license bundled with this package.
 * Available online: {@link http://www.opensource.org/licenses/bsd-license.php}
 * If you did not receive a copy of the license, and are unable to obtain it,
 * email davehauenstein@gmail.com,
 * and I will send you a copy.
 *
 * Project home:
 * http://code.google.com/p/jquery-in-place-editor/
 *
 */
$(document).ready(function(){
	
	//get pool ID from page:
	var pool_id = $("#pool_id_span").html();
	var user_id = $("#user_id_span").html();
	var template_id = $("#template_id_span").html();
	
	//Edit pool fields (for leader - edit_pool.php):
	$(".edit_pool_field").editInPlace({
		url: 'send_pool_data.php',
		params: 'pool_id='+pool_id,
		show_buttons: true,
		value_required: true
	});

	//Edit pool fields (for leader - edit_pool.php):
	$(".edit_template_field").editInPlace({
		url: 'send_pool_data.php',
		params: 'template_id='+template_id,
		show_buttons: true,
		value_required: true
	});

	//Edit picks for a pool (for pool participant) - pool.php:
	$(".edit_pick").editInPlace({
		url: 'send_pool_picks.php',
		params: 'pool_id='+pool_id+'&user_id='+user_id,
		show_buttons: true,
		value_required: true
	});

	//Edit tie breaker (for pool participant) - TEMPLATES ONLY - my_picks_MC.php:
	//as of 2/19/14, we expect the tie breaker value to be numeric
	$(".edit_tie_breaker_choice_template").editInPlace({
		url: 'send_pool_picks.php',
		params: 'pool_id='+pool_id+'&user_id='+user_id+'&template=1',
		show_buttons: true,
		value_required: true
	});

	$(".edit_nickname").editInPlace({
		url: 'send_pool_data.php',
		params: 'pool_id='+pool_id+'&user_id='+user_id,
		show_buttons: true,
		value_required: true
	});

	//Set Tie breaker answer for template 
	//(Called from score_template_choices.php)
	$(".edit_template_tie_breaker").editInPlace({
		url: 'send_pool_score.php',
		params: 'template_id='+template_id+'&template_tie_breaker_answer=1',
		show_buttons: true,
		value_required: true
	});

	//Set Tie breaker answer for custom pool 
	//(Called from score_pool_manual.php)
	$(".edit_custom_pool_tie_breaker").editInPlace({
		url: 'send_pool_score.php',
		params: 'pool_id='+pool_id+'&custom_pool_tie_breaker_answer=1',
		show_buttons: true,
		value_required: true
	});





	// This example shows how to call the function and display a textarea
	// instead of a regular text box. A few other options are set as well,
	// including an image saving icon, rows and columns for the textarea,
	// and a different rollover color.
	$("#editme2").editInPlace({
		callback: function(unused, enteredText) { return enteredText; },
		// url: "./server.php",
		bg_over: "#cff",
		field_type: "textarea",
		textarea_rows: "15",
		textarea_cols: "35",
		saving_image: "./images/ajax-loader.gif"
	});

	// A select input field so we can limit our options
	$("#editme3").editInPlace({
		callback: function(unused, enteredText) { return enteredText; },
		// url: "./server.php",
		field_type: "select",
		select_options: "Change me to this, No way:no"
	});

	// Using a callback function to update 2 divs
	$("#editme4").editInPlace({
		callback: function(original_element, html, original){
			$("#updateDiv1").html("The original html was: " + original);
			$("#updateDiv2").html("The updated text is: " + html);
			return(html);
		}
	});
	
	$("#editme5").editInPlace({
		saving_animation_color: "#ECF2F8",
		callback: function(idOfEditor, enteredText, orinalHTMLContent, settingsParams, animationCallbacks) {
			animationCallbacks.didStartSaving();
			setTimeout(animationCallbacks.didEndSaving, 2000);
			return enteredText;
		}
	});
	
	// If you need to remove an already bound editor you can call

	// > $(selectorForEditors).unbind('.editInPlace')

	// Which will remove all events that this editor has bound. You need to make sure however that the editor is 'closed' when you call this.
	
});