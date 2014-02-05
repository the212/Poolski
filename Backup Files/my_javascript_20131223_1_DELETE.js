

//LOGIN.PHP JAVASCRIPT:
    function login_function() {
        var username = document.getElementById("username").value //get username from login.php page
        var password = document.getElementById("password").value //get password from login.php page
        var visitortime = new Date(); //create new date in user's timezone
        var visitortimezone = -visitortime.getTimezoneOffset()/60; //generate user's timezone in the form "GMT - X" where X is how many hours behind/ahead of GMT the user is
        $.ajax({
            type: "POST",
            url: "login.php",
            data: {time: visitortimezone, username: username, password: password},
            success: function(){
                window.location = "login.php?login=yes"; //after ajax call, reload login.php page with login variable set to yes (which indicates that the login.php is being loaded after running thru the ajax call)
            }
        })
    };
//END OF LOGIN.PHP JAVASCRIPT


/************************************************************************************************/


//BEGINNING OF HOME PAGE JAVASCRIPT (HOME.PHP)
    function accept_invite(user_id, pool_id){
        $.ajax({
                type: "POST",
                url: "send_invite_response.php",
                data: {response : 'a', user_id : user_id, pool_id : pool_id} 
            })
                .done(function(html){ //when ajax request completes
                    $("#pool_span_"+pool_id+"").fadeOut("slow");
                    window.location.href = 'home.php' //return user to home page
                });
    }

    function decline_invite(user_id, pool_id){
        $.ajax({
                type: "POST",
                url: "send_invite_response.php",
                data: {response : 'r', user_id : user_id, pool_id : pool_id} 
            })
                .done(function(html){ //when ajax request completes
                    $("#pool_span_"+pool_id+"").fadeOut("slow");
                    //window.location.href = 'home.php' //return user to home page
                });
    }

    $(document).ready(function() {
        $("#show_invites_link").on( "click", function() {         
            $("#show_invites_link").fadeOut(200, function(){
                $("#show_invites_link").remove();
                $("#pool_invite_list").fadeIn(600);
            });
        });
    });


//END OF HOME.PHP JAVASCRIPT


/************************************************************************************************/


//BEGINNING OF EDIT_POOL.PHP JAVASCRIPT
        function add_category(){
            $("#new_category_goes_here").fadeOut(200, function(){
                $("#new_category_goes_here").replaceWith('<div id="new_category_div" style="display:none"><li style="margin-left:50px"><div id="new_category_form"><form action="javascript:save_new_category();" method="post"><input type="text" name="new_category" id="new_category" class="category_name" size="75" required><label  style="margin-left:50px" for="new_category_points">Point Value </label><input type="number" name="new_category_points" id="new_category_points" class="category_points" size="4" value="1"><input type="submit" value="Submit"><input type="button" id="remove_category_button" onclick="remove_category()" value="Cancel"></form></div></li></div>');
                $("#new_category_div").fadeIn(200);
            });
        }

        var edit_pool_settings_error = 0; //this variable should stay at 0 until an error occurs while editing pool
        var edit_pool_error_message; //this variable will hold the error message if an error occurs while editing the pool 

        $(document).ready(function() {
            //when edit_start_time and edit_end_time dialogs are fully hidden, run below function
            $("#edit_start_time, #edit_end_time").on('hidden.bfhtimepicker', function () {
                var edit_item = $(this).parent().attr("id"); //get the item that is being edited from the parent div id
                var pool_id = $("#pool_id_span").html(); //get the pool id from the span on the page
                change_date_time(edit_item, pool_id); //run change_date_time function for given inputs
            });
            //same function as above except for edit_start_date and edit_end_date dialogs:
            $("#edit_start_date, #edit_end_date").on('hidden.bfhdatepicker', function () {
                var edit_item = $(this).parent().attr("id");
                var pool_id = $("#pool_id_span").html();
                change_date_time(edit_item, pool_id);
            });
            //same function as above except for public_private input dialog:
            $("#public_private_selector").on('hidden.bfhselectbox', function() {
                var edit_item = $(this).parent().attr("id");
                var pool_id = $("#pool_id_span").html();
                change_date_time(edit_item, pool_id);
            });
        });

        function change_date_time(edit_item, pool_id) {
            var edit_value;
            switch(edit_item) {
                case 'SD':
                    edit_value = $("#edit_start_date").val();
                    break;
                case 'ST': 
                    edit_value = $("#edit_start_time").val();
                    break;
                case 'ED':
                    edit_value = $("#edit_end_date").val();
                    break;
                case 'ET':
                    edit_value = $("#edit_end_time").val();
                    break;
                case 'public_private':
                    edit_value = $("#public_private_selector").val();
                    break;
            }
            $.ajax({
                type: "GET",
                url: "send_pool_data.php",
                data: {edit_pool_settings: 1, pool_id: pool_id, edit_item: edit_item, edit_value: edit_value}
            })
            .done(function(html){ //when ajax request completes:
                edit_pool_settings_error = html; 
            });
        }

        $(document).ajaxComplete(function() {
            if(edit_pool_settings_error != null) { //if this is our "second" time though the ajaxcomplete function, we show the error/success message (without this check, we run through this code twice, once before the .done functions and once after)
                if(edit_pool_settings_error == 0) { //if there is no error:
                    //display edit pool success message
                    $("#edit_pool_success").fadeIn(600, function() {
                        $("#edit_pool_success").delay(1700).fadeOut(600);
                    });
                }
                else { //if an ajax call returned an error:
                    var error_array = edit_pool_settings_error.split(','); //split the edit_pool_settings_error string into an array.  
                        //1st array value is error message, 2nd array value is edited item, 3rd array value is the original value of the edited item prior to problematic input
                    $("#edit_pool_error_message").html(error_array[0]); //change error text to appropriate message
                    $("#edit_pool_failure").fadeIn(600, function() { //display error text
                        $("#edit_pool_failure").delay(3000).fadeOut(600);
                    });
                    $("#"+error_array[1]).val(error_array[2]); //change edited field back to its original value
                }
            }
            
            //we need to re-enable the edit in place functionality after the page is updated with AJAX
            var pool_id = $("#pool_id_span").html();
            $(".edit_pool_field").editInPlace({
                url: 'send_pool_data.php',
                params: 'pool_id='+pool_id,
                show_buttons: true,
                value_required: true
            });
        });

//END OF EDIT_POOL.PHP JAVASCRIPT    


/************************************************************************************************/


//BEGINNING OF POOL.PHP JAVASCRIPT

    //Jquery for tabs on pool.php page:
    jQuery(document).ready(function ($) {
        $('#tabs').tab();
    });


    function HomeFunction(){
        alert("hello");
    }

    //below function allows tabs on pool.php page to point to unique URL's
    $(function(){
        var hash = window.location.hash;
        hash && $('ul.nav a[href="' + hash + '"]').tab('show');

        $('.nav-tabs a').click(function (e) {
        $(this).tab('show');
        var scrollmem = $('body').scrollTop();
        window.location.hash = this.hash;
        $('html,body').scrollTop(scrollmem);
        });
    });


    //MULTIPLE CHOICE DROP DOWN SELECTOR
    $(document).ready(function() {
        $(".bfh-selectbox").on('change.bfhselectbox', function () {
            var user_id = $("#user_id_span").html();
            var new_value = $(this).val(); //get the item that is being edited from the parent div id
            var category_id = $(this).attr("id");
            var pool_id = $("#pool_id_span").html(); //get the pool id from the span on the page
            $.ajax({
                type: "POST",
                url: "send_pool_picks.php",
                data: {multiple_choice: 1, user_id: user_id, pool_id: pool_id, category_id: category_id, new_value: new_value}
            })
            /*.done(function(html){ //when ajax request completes
                alert(html);
            });*/
        });
    });


    function showUserPicks(user_id, pool_id, nickname) {
        //alert("show picks here!");
        edit_pool_settings_error = null; //set edit_pool_settings_error to null to avoid showing the pool update success/error messages
        $.ajax({
                type: "GET",
                url: "send_pool_data.php",
                data: {show_user_picks: 1, user_id: user_id, pool_id: pool_id}
            })
            .done(function(html){ //when ajax request completes
                var picks_array = JSON.parse(html); //parse ajax response text into javascript array.  this forms an array where the keys are the category ids and the array values are the given user's picks
                for(var key in picks_array) {
                    //for each category in the picks_array, replace the content of the display_pick_for_category span element on the page with the appropriate pick for the given user:
                    $("#display_user_pick_for_category_"+key).html(picks_array[key]);
                }
                $("#user_for_user_picks").html(nickname)
                $("#pool_members_container").animate({"left":"-100%"}, 400);
                $("#user_picks_container").css("display", "block");
                $("#user_picks_container").animate({"right":"0%"}, 400);
            });
    }

    function hideUserPicks(){ //when user clicks "back" button after viewing a user's picks:
        $('.display_user_pick').html("**No Pick**"); //reset user picks page values
        $("#pool_members_container").animate({"left":"0%"}, 400);
        $("#user_picks_container").animate({"right":"-100%"}, 400, function(){
            $("#user_picks_container").css("display", "none");
        });
    }


//END OF POOL.PHP JAVASCRIPT


/************************************************************************************************/


//BEGINNING OF INVITE_PEOPLE.PHP JAVASCRIPT

    $(document).ready(function() {
        $('#submit_invitee_email').click(function() {
            //when the submit button is clicked, run the add_invitee_email JS function
            input_email = $('#new_invitee_email').val();
            var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
            validation_result = regex.test(input_email);
            if(validation_result == true){
                add_invitee_email($('#new_invitee_email').val());
                $('#new_invitee_email').val("");
                $('#invite_error_message').html("");
            }
            else{
                $('#invite_error_message').html("<span>Please enter a valid email address</span>");
            }
            
        });
        //click submit button for email address entry when user presses enter:
        $('#new_invitee_email').keypress(function(e){
            if(e.keyCode==13)
            $('#submit_invitee_email').click();
        });
    });

    var invitees = new Array(); //invitees array - we store the input emails addresses here and then submit this array once the user clicks the invite button

    function add_invitee_email(email){
        //CREATE CUSTOMIZED DIV BASED ON EMAIL INPUT SO THAT REMOVE_INVITEE_EMAIL FUNCTION WILL WORK
        $("#invitee_email_list").append("<div id="+email+">"+email+" <input id='input_"+email+"' type='button' onclick='remove_invitee_email(this);' value='Remove'><br></div>");
        invitees.push(email); //add email to invitees array
    }

    function remove_invitee_email(email_div_id){
        parent_div = email_div_id.parentNode //get parent DIV of the remove input button that was clicked
        $(parent_div).remove(); //remove the entire parent div from page
        remove_email = parent_div.id; //get the id of the parent div that was removed (this will be the email address to be removed)
        remove_email_index = invitees.indexOf(remove_email); //find the index of the removed email address in the invitees array
        invitees.splice(remove_email_index, 1); //use index to remove given email from invitees array
    }

    function invite_people(pool_id){
        //when inviter clicks "invite" button:
        $.ajax({
                type: "POST",
                url: "invite_people.php",
                data: {invitees_array : invitees, invite : "1", pool_id : pool_id} //send the invitee array and an invite value of 1 to invite_people.php
            })
                .done(function(html){ //when ajax request completes
                    alert(html);
                    window.location.href = 'pool.php?pool_id='+pool_id; //return user to pool page
                });
    }

//END OF INVITE_POOL.PHP JAVASCRIPT


/************************************************************************************************/


//BEGINNING OF SCORE POOL JAVASCRIPT

    function CalculatePoolScore(pool_id){
        if(confirm("Are you sure you are finished tallying the picks? \n\nYou will not be able to change the tally after calculation completes.")){
            $.ajax({
                    type: "POST",
                    url: "send_pool_score.php",
                    data: {calculate_score : 1, pool_id : pool_id} 
                })
                    .done(function(html){ //when ajax request completes
                        alert(html);
                        var users_scored_array = JSON.parse(html);  //parse ajax response text into javascript array.
                        alert(users_scored_array.1);
                        //users_scored_array is :  {"98":0,"1":0}
                        //window.location.href = 'pool.php?pool_id='+pool_id; //return user to pool page
                    });
        }
    }

//END OF SCORE POOL JAVASCRIPT


//BEGINNING OF SCORE_POOL_MANUAL.PHP JAVASCRIPT


    function manual_score(category_id, user_id, pool_id, correct) {
        $.ajax({
                type: "POST",
                url: "send_pool_score.php",
                data: {score_pool_manual: 1, category_id: category_id, user_id: user_id, pool_id: pool_id, correct: correct} 
            })
                .done(function(html){ //when ajax request completes
                    //alert(html);
                });

        if(correct == 1){ //if answer is being marked as correct:
            //BEGIN CHANGE LABEL COLOR LOGIC
                if($("#pick_"+category_id+"_"+user_id+"").hasClass("label-danger")) {
                    $("#pick_"+category_id+"_"+user_id+"").removeClass("label label-danger").addClass("label label-success");
                }
                else{
                    $("#pick_"+category_id+"_"+user_id+"").removeClass("label label-primary").addClass("label label-success");
                }
                $("#incorrect_"+category_id+"_"+user_id+"").removeClass("label label-danger").addClass("label label-default");
                if($("#correct_"+category_id+"_"+user_id+"").hasClass("label-default")) {
                    $("#correct_"+category_id+"_"+user_id+"").removeClass("label label-default").addClass("label label-success");
                }
            //END CHANGE LABEL COLOR LOGIC
            //CHANGE BACKGROUND COLOR OF CATEGORY ROW:
                $("#category_div_"+category_id+"_"+user_id+"").css("background-color","#5cb85c"); 
        }
        else { //if answer is being marked as incorrect:

            //BEGIN CHANGE LABEL COLOR LOGIC
                if($("#pick_"+category_id+"_"+user_id+"").hasClass("label-success")) {
                    $("#pick_"+category_id+"_"+user_id+"").removeClass("label label-success").addClass("label label-danger");
                }
                else{
                    $("#pick_"+category_id+"_"+user_id+"").removeClass("label label-primary").addClass("label label-danger");
                }
                $("#correct_"+category_id+"_"+user_id+"").removeClass( "label label-success" ).addClass( "label label-default" );
                if($("#incorrect_"+category_id+"_"+user_id+"").hasClass("label-default")) {
                    $("#incorrect_"+category_id+"_"+user_id+"").removeClass("label label-default").addClass("label label-danger");
                }
            //END CHANGE LABEL COLOR LOGIC
            //CHANGE BACKGROUND COLOR OF CATEGORY ROW:
                $("#category_div_"+category_id+"_"+user_id+"").css("background-color","#d9534f");
        }
    }


//END OF SCORE_POOL_MANUAL.PHP JAVASCRIPT


/************************************************************************************************/

