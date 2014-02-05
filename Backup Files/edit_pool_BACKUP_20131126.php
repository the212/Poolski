<?php
    /*
    TO DO AS OF 10:30 PM ON 11/11:
        -CREATE "SUBMIT POOL" FUNCTIONALITY
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
        <p>Error: pool does not exist</p>
        <p><a href="home.php">Click here to return to home page</a></p>
<?php
    else:
  
?>
    <script>
        function save_new_category(){
            //note we can't remove this JS function from the page and put in separate .js file due to the document.getElementByID functions
            new_category_name = document.getElementById('new_category').value
            new_category_points = document.getElementById('new_category_points').value
            //ajax request for adding new category
            $.ajax({
                type: "GET",
                url: "send_pool_data.php",
                data: {pool_id: "<?php echo $pool_fetch_result['Pool ID']; ?>", new_category: new_category_name, new_category_points: new_category_points}
            })
                .done(function(html){ //when ajax request completes
                    $("#saved_category_space").fadeOut(300, function(){
                        $("#saved_category_space").html(html);
                        $("#saved_category_space").fadeIn(300);
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

        function remove_category(category_div_id, category_id){ 
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
                        data: {pool_id: "<?php echo $pool_fetch_result['Pool ID']; ?>", remove_category: category_id}
                    })
                    .done(function(html) { //when ajax request completes
                        $("#saved_category_space").html(html);
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
    </script>

    <h2>Edit pool. <span style="margin-left:100px;"><input type="button" onclick="submit_pool()" value="Finalize Pool"></span><span style="font-size:50%"> Click here when everything looks the way you want it.</span></h2>
    <br>
    <p style="font-style:italic">Click on an item to edit it</p>
    <span id="pool_id_span" style="display:none"><?php echo $pool_fetch_result["Pool ID"]; ?></span> <!--This is here so that the form_edit.js function knows which pool we are looking at.  That function passes the pool ID to the server with each input-->
    <div id="pool_space">
        <h3 class="edit_pool_heading">Pool Title</h3>
        <span class="edit_field_wrapper"><span class="edit_pool_field" id="Title"><?php echo $pool_fetch_result["Title"]; ?> </span></span>

        <h3 class="edit_pool_heading">Pool Description (optional)</h3>
        <span class="edit_field_wrapper"><span class="edit_pool_field" id="Description"><?php echo $pool_fetch_result['Description']; ?></span></span>

        <h3 class="edit_pool_heading">Overall Pool Question</h3>
        <span class="edit_field_wrapper"><span class="edit_pool_field" id="Overall Question"><?php echo $pool_fetch_result['Overall Question']; ?></span></span>

        <h3 class="edit_pool_heading">Tie-Breaker Question</h3>
        <span class="edit_field_wrapper"><span class="edit_pool_field" id="Tie-Breaker Question"><?php echo $pool_fetch_result['Tie-Breaker Question']; ?></span></span>

        <br>

        <ol>
            <div id="category_space">
                <h3 class="edit_pool_heading" style="text-decoration:underline">Categories</h3>            
<?php
            if($number_of_saved_categories>0){
                //if saved categories exist in given pool:
?>
                <p style="font-style:italic">Click on a category name or its point value to edit it</p>
                <br>
                <div id="saved_category_space"> 
<?php
                $category_counter = 1;
                //create list of saved pool categories for given pool by walking through pool_categories array:
                foreach($pool_categories as $category_id => $category_info){
?>          
                    <div id="category_<?php echo $category_counter; ?>">
                        <li style="margin-left:50px"> 
                            <span class="edit_field_wrapper"><span class="edit_category_field" id="category_n_span<?php echo $category_info['Category ID']; ?>" style="margin-left:0px;"><?php echo $category_info['Category Name']; ?></span></span>
                            <span style="margin-left:20px;">Point Value: <span class="edit_field_wrapper" style="margin-left:62px">&nbsp;<span class="edit_pool_field" id="category_p_span<?php echo $category_info['Category ID']; ?>" style="margin-left:0px">&nbsp;<?php echo $category_info['Category Point Value']; ?>&nbsp;</span>&nbsp;</span></span>
                            <input type="button" onclick="remove_category(<?php echo $category_counter; ?>, <?php echo $category_info['Category ID']; ?>)" value="Remove category"> 
                        </li>
                    </div>
                    <br><br>
<?php 
                $category_counter++;
                }
?>
                </div>
<?php
            }
            else{
?>
                <p style="font-style:italic">This pool does not have any saved categories.  Click the "add new category" button below to add some.</p>
                <br>
                <div id="saved_category_space"> 
                </div>
<?php
            }
?>                
                <div id="new_category_goes_here">
                    <input type="button" onclick="add_category()" value="Add new category">
                </div>
            </div> <!--end of category_space div-->
        </ol>
        <br>
        <p><a href="home.php" class="button">Account Home</a></p>
        <input type="hidden" name="form_sent" value="TEST"/>
    </div>
    <br>
        
        
<?php
    endif;
    
    //include_once 'common/close.php';
?>

