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
                        <div style="margin-left:50px" class="well well-sm"> 
                            <div class="row">
                                <div class="col-md-5">
                                    <h4> Category name: &nbsp; 
                                        <span class="label label-info"><span class="edit_pool_field" id="category_n_span<?php echo $category_info['Category ID']; ?>" style="margin-left:0px;"><?php echo $category_info['Category Name']; ?></span></span>
                                    </h4>
                                </div>
                                <div class="col-md-7">
                                    <h4>
                                        <span style="margin-left:20px;">Point Value: <span class="label label-info" style="margin-left:62px">&nbsp;<span class="edit_pool_field" id="category_p_span<?php echo $category_info['Category ID']; ?>" style="margin-left:0px">&nbsp;<?php echo $category_info['Category Point Value']; ?>&nbsp; </span>&nbsp;</span></span>
                                        <input type="button" onclick="remove_category(<?php echo $category_counter; ?>, <?php echo $category_info['Category ID']; ?>)" value="Remove category"> 
                                    </h4>
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
