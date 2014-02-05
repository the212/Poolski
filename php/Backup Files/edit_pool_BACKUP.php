<?php
    /*
    TO DO AS OF 9:00 PM ON 11/10:
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
        //if the page loads after submitting the "Add new category form", we add the new category before generating the array of all the pool categories:
        if(isset($_POST['new_category'])){ 
            $new_category = $_POST['new_category'];
            $new_category_pt_value = $_POST['new_category_points'];
            $pool->AddCategory($pool_fetch_result["Pool ID"], $new_category, $new_category_pt_value);
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
    <!--Jquery script for adding and removing new categories to form-->
    <script>
        //BEGINNING OF EDIT_POOL.PHP JAVASCRIPT
        function add_category(){
            $("#new_category_goes_here").replaceWith('<div id="new_category_div"><li style="margin-left:50px"><div id="new_category_form"><form action="edit_pool.php?pool_id=<?php echo $pool_fetch_result["Pool ID"]; ?>" method="post"><input type="text" name="new_category" id="new_category" class="category_name" size="75" required><label  style="margin-left:50px" for="new_category_points">Point Value </label><input type="number" name="new_category_points" id="new_category_points" class="category_points" size="4" value="1"><input type="submit" value="Submit"><input type="button" id="remove_category_button" onclick="remove_category()" value="Remove"></form></div></li></div>');
        }

        function remove_category(category_div_id, category_id){ 
            //NOTE WE WILL PROBABLY NEED TO MODIFY THIS FUNCTION SO THAT IT ALSO REMOVES THE GIVEN CATEGORY FROM THE DATABASE!!
            if (category_div_id === undefined){ //if category_div_id is undefined, it means that we are removing a new category that hasn't yet been written to DB
                var div_id = $("#remove_category_button").parents().eq(3).attr("id"); //get the id of the "new category" div
                $("#"+div_id).replaceWith('<div id="new_category_goes_here"><input type="button" onclick="add_category()" value="Add new category"></div>'); //remove new category div and replace it with the original add_category button and its enclosing new_Category_goes_here div:
            }
            else{ //if category_div_id was set, it means we are removing an existing category from the DB
                //AJAX call to send_pool_data.php with the "remove_category" variable set in URL - send_pool_data.php will run the remove category method
                var xmlhttp;
                if (window.XMLHttpRequest){
                    // code for IE7+, Firefox, Chrome, Opera, Safari
                    xmlhttp=new XMLHttpRequest();
                }
                else{
                // code for IE6, IE5
                xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
                }
                xmlhttp.open("GET","send_pool_data.php?remove_category="+category_id,true); 
                xmlhttp.send();
                //fade out and remove the category div from the html of the page:
                $("#category_"+category_div_id).fadeOut(500, function(){
                    $("#category_"+category_div_id).remove(); 
                    location.reload(); //refresh the page
                });
            }
        }
    //END OF EDIT_POOL.PHP JAVASCRIPT    
    </script>

    <h2>Edit pool:</h2>
    <span style="font-style:italic">Click on an item to edit it</span>
    <span id="pool_id_span" style="display:none"><?php echo $pool_fetch_result["Pool ID"]; ?></span> <!--This is here so that the form_edit.js function knows which pool we are looking at.  That function passes the pool ID to the server with each input-->
    <br>
    <br>
        <div>
            <h3 class="edit_pool_heading">Pool Title</h3>
            <p class="edit_pool_field" id="Title"><?php echo $pool_fetch_result["Title"]; ?> </p>

            <h3 class="edit_pool_heading">Pool Description (optional)</h3>
            <p class="edit_pool_field" id="Description"><?php echo $pool_fetch_result['Description']; ?></p>

            <h3 class="edit_pool_heading">Overall Pool Question</h3>
            <p class="edit_pool_field" id="Overall Question"><?php echo $pool_fetch_result['Overall Question']; ?></p>

            <h3 class="edit_pool_heading">Tie-Breaker Question</h3>
            <p class="edit_pool_field" id="Tie-Breaker Question"><?php echo $pool_fetch_result['Tie-Breaker Question']; ?></p>

            <br>

            <ol>
                <div id="category_space" style="margin-left:50px">
                    <h3 class="edit_pool_heading" style="text-decoration:underline">Categories</h3>            
<?php
            if($number_of_saved_categories>0){
                //if saved categories exist in given pool:
?>
                    <p style="font-style:italic">Click on a category name or its point value to edit it</p>
                    <br>
<?php
                $category_counter = 1;
                //create list of saved pool categories for given pool by walking through pool_categories array:
                foreach($pool_categories as $category_id => $category_info){
?>          
                    <div id="category_<?php echo $category_counter; ?>">
                        <li style="margin-left:50px"> 
                            <p style="margin-left:30px">Category name:<span class="edit_pool_field" id="category_n_span<?php echo $category_info['Category ID']; ?>"><?php echo $category_info['Category Name']; ?></span></p>
                            <div style="margin-left:30px">
                                <p>Point Value: <span style="background-color:#FFFF66; margin-left:62px">&nbsp;<span class="edit_pool_field" id="category_p_span<?php echo $category_info['Category ID']; ?>" style="margin-left:0px"><?php echo $category_info['Category Point Value']; ?></span>&nbsp;</span></p>
                                <input type="button" onclick="remove_category(<?php echo $category_counter; ?>, <?php echo $category_info['Category ID']; ?>)" value="Remove category"> 
                            </div> 
                        </li>
                    </div>
                    <br><br>
<?php 
                $category_counter++;
                }
            }
            else{
?>
                <p style="font-style:italic">This pool does not have any saved categories.  Click the "add new category" button below to add some.</p>
                <br>
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



<!--BELOW HTML IS THE NEW CATEGORY DIV - FOR TESTING PURPOSES ONLY.  WHEN IT IS READY, IT SHOULD BE ADDED TO THE "ADD_CATEGORY_ JAVASCRIPT FUNCTION FOR THIS PAGE"
                    <div id="new_category_div">
                        <li style="margin-left:50px">
                        <div id="new_category_form">
                            <form action="edit_pool.php?pool_id=<?php echo $pool_fetch_result["Pool ID"]; ?>" method="post">
                                <input type="text" name="new_category" id="new_category" class="category_name" size="75" required> 
                                <label  style="margin-left:50px" for="new_category_points">Point Value </label>
                                <input type="number" name="new_category_points" id="new_category_points" class="category_points" size="4" value="1">
                                <input type="submit" value="Submit">
                                <input type="button" id="remove_category_button" onclick="remove_category()" value="Remove">
                            </form>
                        </div>
                        </li>
                    </div>
                    -->
