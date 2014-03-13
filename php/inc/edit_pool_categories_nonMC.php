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
                        <div style="margin-left:50px; width:100%;" class="well well-sm"> 
                            <div class="row">
                                <div class="col-md-2">
                                    <h4> Category name: &nbsp; </h4>
                                </div>
                                <div class="col-md-5">
                                    <h4>
                                        <span class="label label-info"><span class="edit_pool_field" id="category_n_span<?php echo $category_info['Category ID']; ?>" style="margin-left:0px; white-space:pre-line;"><?php echo $category_info['Category Name']; ?></span></span>
                                    </h4>
                                </div>
                                <div class="col-md-3">
                                    <h4>
                                        Point Value: <span class="label label-info">&nbsp;<span class="edit_pool_field" id="category_p_span<?php echo $category_info['Category ID']; ?>">&nbsp;<?php echo $category_info['Category Point Value']; ?>&nbsp; </span>&nbsp;</span>
                                    </h4>  
                                </div>
                                <div class="col-md-2"> 
                                    <h5> 
                                        <input type="button" onclick="remove_category(<?php echo $category_counter; ?>, <?php echo $category_info['Category ID']; ?>)" value="Remove category"> 
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>
<?php 
                $category_counter++;
                }
?>
                </div>
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
                <h4><input type="button" onclick="add_category()" value="Add new category"></h4>
            </div>
