<?php
    /*
    TO DO AS OF 9:45 PM ON 3/12:
        -CREATE "SORTING" FUNCTIONALITY (ALLOW USERS TO SORT CATEGORIES) - NEXT RELEASE?
        -ADD INSTRUCTION TEXT ON PAGE FOR EDITING FIELDS 
    */
    include_once "inc/loggedin_check.php";
    include_once "inc/constants.inc.php";
    $pageTitle = "Edit Pool";
    include_once "inc/header.php";

    if(!isset($_GET['pool_id'])){
        //if no pool ID is specified in URL, return the user to the homepage:
        header("Location: home.php");
    }
    else {
        //if we successfully got the pool id from the URL:
        include_once 'inc/class.pool.inc.php';
        $pool_id = $_GET['pool_id']; //get pool ID from URL of page
        $pool = new Pool(); //new instance of the Pool class
        $pool_fetch_result = $pool->GetPoolData($pool_id); //get pool data based on pool ID that is passed in URL
        $pool_start_date = substr($pool_fetch_result['Start Time'], 0, 10);
        $pool_start_time = substr($pool_fetch_result['Start Time'], 11);
        $pool_start_time = $pool->timestampTo12HourConversion($pool_start_time); //convert pool start time into appropriate time
        $pool_end_date = substr($pool_fetch_result['End Time'], 0, 10);
        $pool_end_time = substr($pool_fetch_result['End Time'], 11);
        $pool_end_time = $pool->timestampTo12HourConversion($pool_end_time); //convert pool start time into appropriate time

        if (isset($pool_fetch_result['Leader ID']) && $_SESSION['Username']<>$pool_fetch_result['Leader ID']){
            //if the currently logged in user is not the pool leader, we do not allow them to edit the pool and we return them to the home page:
            header("Location: home.php");
            exit();
        }
       
        //create an array of all of the categories for the given pool ($pool_categories)
        $pool_categories = $pool->GetPoolCategoryData($pool_id);
        //get the number of saved cateogires
        $number_of_saved_categories = count($pool_categories);
    }

    if($pool_fetch_result==0):
    //if the pool id passed thru url does not exist in database:
?>
        <h3>Error: pool does not exist</h3>
        <p><a href="home.php">Click here to return to home page</a></p>
<?php
    else:
?>
    <script>
        function save_new_category(multiple_choice){
            //note we can't remove this JS function from the page and put in separate .js file due to the document.getElementByID functions
            multiple_choice = multiple_choice || 0; //set multiple_choice variable if it is passed (default is 0 which means category is NOT multiple choice)
            new_category_name = document.getElementById('new_category').value
            new_category_points = document.getElementById('new_category_points').value
            //ajax request for adding new category
            $.ajax({
                type: "GET",
                url: "send_pool_data.php",
                data: {pool_id: "<?php echo $pool_fetch_result['Pool ID']; ?>", new_category: new_category_name, new_category_points: new_category_points, multiple_choice: multiple_choice}
            })
                .done(function(html){ //when ajax request completes
                    var return_array = JSON.parse(html); //return html is sent as an array - 1st value is the html to generate new category space, 2nd value is the new category ID
                    $("#saved_category_space").fadeOut(300, function(){
                        $("#saved_category_space").html(return_array[0]); //insert html for new category space
                        $("#add_choice_button_for_category_"+return_array[1]).css("left", "-120%"); //temporarily move the "add choice" button to the left of the screen (we animate it on screen below)
                        $("#saved_category_space").fadeIn(300, function(){
                            $("#add_choice_button_for_category_"+return_array[1]).animate({"left":"0.5%"}, 400, function(){
                                $("#add_choice_button_for_category_"+return_array[1]).animate({"font-size":"160%"}, 200, function(){
                                    $("#add_choice_button_for_category_"+return_array[1]).animate({"font-size":"119%"}, 200);
                                });
                            });
                        });
                        //re-add the edit in place functionality since it will not initially work after fadeout above:
                        var pool_id = $("#pool_id_span").html();
                        $(".edit_pool_field").editInPlace({
                            url: 'send_pool_data.php',
                            params: 'pool_id='+pool_id,
                            show_buttons: true,
                            value_required: true
                        });
                    });
                });
                //reset inputs for new category name and point value:
                $("#new_category").val("");
                $("#new_category_points").val("1");
        }

        function save_new_category_choice(category_id){
            new_category_choice_name = document.getElementById('new_category'+category_id+'_choice').value
            //ajax request for adding new category
            $.ajax({
                type: "GET",
                url: "send_pool_data.php",
                data: {pool_id: "<?php echo $pool_fetch_result['Pool ID']; ?>", category_id: category_id, new_category_choice: new_category_choice_name}
            })
                .done(function(html){ //when ajax request completes
                    $("#category"+category_id+"_choice_space").fadeOut(300, function(){
                        $("#category"+category_id+"_choice_space").html(html);
                        $("#category"+category_id+"_choice_space").fadeIn(300);
                        //re-add the edit in place functionality since it will not initially work after fadeout above:
                        var pool_id = $("#pool_id_span").html();
                        $(".edit_pool_field").editInPlace({
                            url: 'send_pool_data.php',
                            params: 'pool_id='+pool_id,
                            show_buttons: true,
                            value_required: true
                        });
                    });
                });
                //reset inputs for new category name and point value:
                $("#new_category"+category_id+"_choice").val("");
        }

        function remove_category(category_div_id, category_id, multiple_choice){ 
            if (category_div_id === undefined){ //if category_div_id is undefined, it means that we are removing a new category that hasn't yet been written to DB
                var div_id = $("#remove_category_button").parents().eq(3).attr("id"); //get the id of the "new category" div
                $("#"+div_id).replaceWith('<div id="new_category_goes_here"><input type="button" onclick="add_category()" value="Add new category"></div>'); //remove new category div and replace it with the original add_category button and its enclosing new_Category_goes_here div:
            }
            else{ //if category_div_id was set, it means we are removing an existing category from the DB
                //fade out and remove the category div from the html of the page:
                $("#category_"+category_div_id).fadeOut(500, function(){
                   //AJAX call to send_pool_data.php with the "remove_category" variable set in URL - send_pool_data.php will run the remove category method
                    $.ajax({
                        type: "GET",
                        url: "send_pool_data.php",
                        data: {pool_id: "<?php echo $pool_fetch_result['Pool ID']; ?>", remove_category: category_id, multiple_choice: multiple_choice}
                    })
                    .done(function(html) { //when ajax request completes
                        $("#saved_category_space").html(html);
                    }); 
                });
            }
        }

        function remove_category_choice(category_id, choice_div_id, choice_id){ 
            if (choice_div_id === undefined){ //if choice_div_id is undefined, it means that we are removing a new category that hasn't yet been written to DB (e.g., user is clicking the "cancel" button after clicking "add choice")
                var div_id = $("#remove_category_choice_button_"+category_id).parents().eq(3).attr("id"); //get the id of the "new category" div
                $("#"+div_id).replaceWith('<div id="new_category'+category_id+'_choice_goes_here" style="margin-left:40%;"><h4><span class="add_category_choice_button"><input type="button" onclick="add_category_choice('+category_id+')" value="Add new choice"></span></h4></div>'); //remove new category div and replace it with the original add_category button and its enclosing new_Category_goes_here div:
            }
            else{ //if choice_div_id was set, it means we are removing an existing choice from the DB
                //fade out and remove the category div from the html of the page:
                $("#category"+category_id+"_choice_"+choice_div_id).fadeOut(500, function(){
                   //AJAX call to send_pool_data.php with the "remove_category_choice" variable set in URL - send_pool_data.php will run the remove category choice method
                    $.ajax({
                        type: "GET",
                        url: "send_pool_data.php",
                        data: {category_id: category_id, remove_category_choice: choice_id}
                    })
                    .done(function(html) { //when ajax request completes
                        $("#category"+category_id+"_choice_space").html(html);
                    }); 
                });
            }
        }

        function submit_pool(){
            if(confirm("Are you sure you want to finalize the pool? \n\nYou will not be able to edit these settings again once the pool is finalized.")){
                //AJAX call to submit pool:
                $.ajax({
                    type: "GET",
                    url: "send_pool_data.php",
                    data: {pool_id: "<?php echo $pool_fetch_result['Pool ID']; ?>", submit_pool: "test"}
                })
                .done(function(html){
                    //redirect user to home page after ajax call
                    window.location.replace("./pool.php?pool_id=<?php echo $pool_id; ?>");
                });
            }
        } 
    //END OF JAVASCRIPT FOR PAGE
    </script>



    <div id="edit_pool_header_container">
        <div style="text-align:center;">
            <h1>Edit pool </h1>
            <h5>Configure the pool settings to your liking before inviting others.</h5>
        </div>
        <h2 style="margin-left:20px;"><input type="button" onclick="submit_pool()" value="Finalize Pool"><span style="font-size:50%"> Click here when everything looks the way you want it.</span></h2>
    
        <!--POOL UPDATE SUCCESS MESSAGE -->
        <span class="alert alert-success alert-dismissable" id="edit_pool_success" style="display:none; padding:8px; width:250px; float:right">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            Pool successfully updated!.
        </span>

        <!--POOL UPDATE FAILURE MESSAGE -->
        <span class="alert alert-danger alert-dismissable" id="edit_pool_failure" style="display:none; padding:8px; width:250px; float:right">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <span id="edit_pool_error_message">ERROR!</span>
        </span>

        <br>
        <span id="pool_id_span" style="display:none"><?php echo $pool_fetch_result["Pool ID"]; ?></span> <!--This is here so that the form_edit.js function knows which pool we are looking at.  That function passes the pool ID to the server with each input-->
    </div>

    <div id="content">
        <ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
            <li><a href="#pool_info" data-toggle="tab">Edit Pool Info</a></li>
<?php
    if(!isset($pool_fetch_result['Template ID'])){ //only display the edit categories tab if this is NOT a template
?>
            <li class="active"><a href="#pool_categories" data-toggle="tab">Edit Categories</a></li>
<?php
    }
?>
            <li><a href="#pool_settings" data-toggle="tab">Edit Pool Settings</a></li>
        </ul>
        <div id="pool_tab_content" class="tab-content">

            <!--EDIT POOL INFO DIV -->
            <div class="tab-pane fade in" id="pool_info">
                <br><h4 class="field_label">Click on an item to edit it</h4>
                <div id="pool_info_container">
                    <div class="well well-sm">
                        <div class="row">
                            <div class="col-md-5">
                                <h3 class="edit_pool_heading">Pool Title:</h3>
                            </div>
                            <div class="col-md-7">
                                <h2><span class="label label-info"><span class="edit_pool_field" id="Title"><?php echo $pool_fetch_result["Title"]; ?> </span></span></h2>
                            </div>
                        </div>
                    </div>
                    <div class="well well-sm">
                        <div class="row">
                            <div class="col-md-5">
                                <h3 class="edit_pool_heading">Pool Description (optional): </h3>
                            </div>
                            <div class="col-md-7">
                                <h3><p class="label label-info" style="display:block; white-space:normal;"><span class="edit_pool_field" id="Description"><?php echo $pool_fetch_result['Description']; ?></span></p></h3>
                            </div>
                        </div>
                    </div> 
<?php
    if(!isset($pool_fetch_result['Template ID'])){ //only allow user to edit the overall and tie breaker questions if this is NOT a template:
?>
                    <div class="well well-sm">
                        <div class="row">
                            <div class="col-md-5">
                                <h3 class="edit_pool_heading">Overall Pool Topic: </h3> </div>
                            <div class="col-md-7">
                                <h3><span class="label label-info"><span class="edit_pool_field" id="Overall Question"><?php echo $pool_fetch_result['Overall Question']; ?></span></span></h3>
                            </div>
                        </div>
                    </div>
                    <div class="well well-sm">
                        <div class="row">
                            <div class="col-md-5">
                                <h3 class="edit_pool_heading">Tie-Breaker Question: </h3>
                            </div>
                            <div class="col-md-7">
                                <h3><span class="label label-info"><span class="edit_pool_field" id="Tie-Breaker Question"><?php echo $pool_fetch_result['Tie-Breaker Question']; ?></span></span></h3>
                            </div>
                        </div>
                    </div>
<?php
    }
?>
                    <br>
                </div>
            </div> <!--END OF POOL INFO DIV -->

<!-- ********************************* -->
<?php
    if(!isset($pool_fetch_result['Template ID'])){ //only display the edit categories tab if this is NOT a template
?>
            <!--EDIT POOL CATEGORIES DIV -->
            <div class="tab-pane fade in active" id="pool_categories">
                <div id="category_space" style="width:95%">
                    <h3 class="edit_pool_heading" style="text-decoration:underline">Categories</h3>            
<?php 
                    if($pool_fetch_result['Multiple Choice?'] == 0){
                        include_once "inc/edit_pool_categories_nonMC.php"; //NON MULTIPLE CHOICE EDIT CATEGORIES FILE
                    }
                    else{
                        include_once "inc/edit_pool_categories_MC.php"; //MULTIPLE CHOICE EDIT CATEGORIES FILE
                    }
?>   
                </div> <!--end of category_space div-->
            </div>
            <!--END OF EDIT POOL CATEGORIES DIV -->
<?php
    }
?>
<!-- ********************************* -->
            
            <!--EDIT POOL SETTINGS DIV -->

            <div class="tab-pane fade in" id="pool_settings">
<?php
    if(!isset($pool_fetch_result['Template ID'])){
        //IF POOL IS NOT A TEMPLATE:
?>
                <br>
                <h4 class="edit_settings_instruction">Click on a setting to edit it. &nbsp;Start/End Dates and Times are optional.</h4>
                <div class="row">
                    <div class="col-md-6">
                        <h4>Pool Start Date:</h4>
                        <div id="SD">
                            <div class="bfh-datepicker" id="edit_start_date" data-format="y-m-d" data-date='<?php echo $pool_start_date; ?>'>
                            </div>
                        </div> 
                    </div>
                    <div class="col-md-6">
                        <h4>Pool Start Time: <span class="hint_subtext">(default time is midnight)<span></h4>
                        <div id="ST">
                            <div class="bfh-timepicker" id="edit_start_time" data-time='<?php echo $pool_start_time; ?>'>
                            </div>
                        </div> 
                    </div>
                </div>
                <p class="field_label">If you set a start time, all user's picks will be locked at the specified time.</p>
                <br>
                <div class="row">
                    <div class="col-md-6">
                        <h4>Pool End Date:</h4>
                        <div id="ED">
                            <div class="bfh-datepicker" id="edit_end_date" data-format="y-m-d" data-date='<?php echo $pool_end_date; ?>'>
                            </div>
                        </div> 
                    </div>
                    <div class="col-md-6">
                        <h4>Pool End Time: <span class="hint_subtext">(default time is midnight)<span></h4>
                        <div id="ET">
                            <div class="bfh-timepicker" id="edit_end_time" data-time='<?php echo $pool_end_time; ?>'>
                            </div>
                        </div> 
                     </div>
                </div>
                <p class="field_label">If you set an end time,the pool will automatically end at the specified time.</p>
                <br><br>
                <h4>Make Pool Public or Private?</h4>
                <div id="public_private">
                    <div class="bfh-selectbox" id="public_private_selector" data-name="selectbox1" data-value='<?php echo $pool_fetch_result['Private?']; ?>'>
                        <div data-value="0">Public</div>
                        <div data-value="1">Private</div>
                    </div>
                </div>
                <br>
                <div id="public_private_instructions">
                    <p>"Public" means that anyone who joins can invite others to join</p>
                    <p>"Private" means that only you as the admin can invite others to join</p>
                </div>
<?php
    }
    else { 
        //IF POOL IS A TEMPLATE:
?>
                <div>
                    <br>
                    <h4>Make Pool Public or Private?</h4>
                    <div id="public_private">
                        <div class="bfh-selectbox" id="public_private_selector" data-name="selectbox1" data-value='<?php echo $pool_fetch_result['Private?']; ?>'>
                            <div data-value="0">Public</div>
                            <div data-value="1">Private</div>
                        </div>
                    </div>
                    <br>
                    <div id="public_private_instructions">
                        <p>"Public" means that anyone who joins can invite others to join</p>
                        <p>"Private" means that only you as the admin can send invites</p>
                    </div>
                    <br>
                    <!--BEGIN STATIC POOL SETTINGS-->
                    <div style="width:50%;">
                        <div class="row">
                            <div class="col-md-6">
                                <h4>Picks will be locked in at: </h4>
                            </div>
                            <div class="col-md-6">
                                <h4><?php echo $pool_start_time; ?> EST on <?php echo $pool_start_date; ?></h4>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-6">
                                <h4>Pool will end at: </h4>
                            </div>
                            <div class="col-md-6">
                                <h4><?php echo $pool_end_time; ?> EST on <?php echo $pool_end_date; ?></h4>
                            </div>
                        </div>
                    </div>
                    <!--END STATIC POOL SETTINGS-->
                </div>
<?php
    } //END OF TEMPLATE IF STATEMENT FOR POOL SETTINGS TAB
?>
            <br><br>
            </div> <!--END OF EDIT POOL SETTINGS DIV -->
        </div> <!-- END OF POOL_TAB_CONTENT DIV-->
    </div> <!--END OF CONTENT DIV-->
    <br>
    <br>
        
        
<?php
    endif;
    
    include_once 'inc/close.php';
?>

