<?php
            if($number_of_saved_categories>0){
                //if saved categories exist in given pool:
                include_once 'inc/update_categories_list.php';
?>
                <p style="font-style:italic">Click on a category name or its point value to edit it</p>
                <br>
                <div id="saved_category_space"> 
<?php
                $category_list = Update_Category_List($pool_categories, 1);
                echo $category_list;
                /* 3/26/14 - I COMMENTED OUT THE BELOW CODE - WE CAN CURRENTLY FETCH ALL OF IT USING THE UPDATE_CATEGORY_LIST FUNCTION ABOVE (FOUND IN THE SEND_POOL_DATA.PHP FILE)
                $category_counter = 1;
                //create list of saved pool categories for given pool by walking through pool_categories array:
                foreach($pool_categories as $category_id => $category_info){
                    $category_choices = $pool->GetCategoryChoices($category_id);
?>          
                    <div id="category_<?php echo $category_counter; ?>">
                        <div style="margin-left:50px; width:100%;" class="well well-sm"> 
                            <div class="row">
                                <div class="col-md-2">
                                    <h4> Category name: &nbsp; </h4>
                                </div>
                                <div class="col-md-6">
                                    <h2>
                                        <span class="label label-info"><span class="edit_pool_field" id="category_n_span<?php echo $category_info['Category ID']; ?>" style="margin-left:0px; white-space:normal;"><?php echo $category_info['Category Name']; ?></span></span>
                                    </h2>
                                </div>
                                <div class="col-md-2">
                                    <h4>
                                        Point Value: <span class="label label-info">&nbsp;<span class="edit_pool_field" id="category_p_span<?php echo $category_info['Category ID']; ?>">&nbsp;<?php echo $category_info['Category Point Value']; ?>&nbsp; </span>&nbsp;</span>
                                    </h4>  
                                </div>
                                <div class="col-md-2"> 
                                    <h5> 
                                        <input type="button" onclick="remove_category(<?php echo $category_counter; ?>, <?php echo $category_info['Category ID']; ?>, 1)" value="Remove category"> 
                                    </h5>
                                </div>
                            </div>
                            <div id="category_choices_container">
                                <h4 class="edit_category_choices_heading"> Category Choices: &nbsp; </h4>
                                <div id="category<?php echo $category_id; ?>_choice_space">
<?php
                    //BEGIN CATEGORY CHOICE LIST:
                    $choice_counter = 1;
                    foreach($category_choices as $choice_id => $choice){
?>                                  <div id="category<?php echo $category_id; ?>_choice_<?php echo $choice_counter; ?>">
                                        <div class="col-md-2">
                                            <h5>Choice <?php echo $choice_counter; ?>: &nbsp; </h5>
                                        </div>
                                        <div class="col-md-9">
                                            <h4>
                                                <span class="label label-info"><span class="edit_pool_field" id="choice_span<?php echo $choice_id; ?>" style="margin-left:0px; white-space:normal;"><?php echo $choice; ?></span></span>
                                            </h4>
                                        </div>
                                        <div class="col-md-1"> 
                                            <h5> 
                                                <input type="button" onclick="remove_category_choice(<?php echo $category_id; ?>, <?php echo $choice_counter; ?>, <?php echo $choice_id; ?>)" value="Remove choice"> 
                                            </h5>
                                        </div>
                                    </div>
<?php
                        $choice_counter++;
                    } //END OF CATEGORY CHOICE FOREACH STATEMENT
?>                              </div>
                                 <div id="new_category<?php echo $category_id; ?>_choice_goes_here" class="new_category_choice_goes_here_div">
                                    <h4><input type="button" onclick="add_category_choice(<?php echo $category_id; ?>)" value="Add new choice"></h4>
                                </div>
                            </div> <!--END OF CATEGORY_CHOICE_CONTAINER DIV-->
                        </div>
                    </div>
<?php 
                $category_counter++;
                }*/
?>
                </div> <!--END OF saved_category_space DIV -->
<?php
            }
            else{
?>
                <br>
                <div id="add_category_instruction">
                    <h4 style="font-style:italic; font-weight:bold;">This pool does not have any saved categories.  Click the "add new category" button below to add some.</h4>
                    <br>
                    <h4 style="font-style:italic">Categories are the things things that you bet on in the pool. </h4>
                    <h4 style="font-style:italic">E.g., if your pool's overall topic is "who will win each Academy Award?", the pool categories would be things like "Best Picture", "Best Film Editing", "Best Director", etc.</h4>
                    <br>
                </div>
                <div id="saved_category_space"> 
                </div>
<?php
            }
?>                
            <div id="new_category_goes_here">
                <h4><input type="button" onclick="add_category(1)" value="Add new category"></h4>
            </div>
