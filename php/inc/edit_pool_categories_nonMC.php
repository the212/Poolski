<?php
            if($number_of_saved_categories>0){
                //if saved categories exist in given pool:
                include_once 'inc/update_categories_list.php';
?>
                <p style="font-style:italic">Click on a category name or its point value to edit it</p>
                <br>
                <div id="saved_category_space"> 
<?php
                $category_list = Update_Category_List($pool_categories, 0);
                echo $category_list;
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
                <h4>
                    <span class="add_category_button"><input type="button" onclick="add_category()" value="Add new category"></span>
                </h4>
            </div>
