$(document).ready(function(){
            //MULTIPLE CHOICE CHECKBOX TOGGLE:
            $('#category_space').on("click", ".multiple_choice_check", function(){
                //get parent div id - we will be appending the choice section to it
                var id = $(this).closest('div').attr('id');
                //get value of checkbox (checked or unchecked)
                var check = this.checked ? 'yes' : 'no';
                if(check=='yes'){
                    $("#"+id).append('<p id=multiple_choice'+counter+'>test123</p>');               }
                else {
                    $("#multiple_choice"+counter).remove();
                }
            });

            //ADD CHOICE FUNCTION
            $('#category_space').on("click", ".add_choice_button", function(){
                //get id of the button that was clicked (this indicates the choice number for the given category - e.g., "add_choice_button1" means we're adding the first choice):
                var choice_number = $(this).closest('input').attr('id');
                //strip away id words leaving just the number corresponding to the choice to be added
                choice_number = choice_number.substr(17,18);
                //get the number of the category that we are in (number of category is appended onto id of containing DIV)
                var category_number = $(this).closest('div').attr('id');
                category_number = category_number.substr(9,10);
                //get div id for div in which we want to add the new choice (corresponds to category number)
                var id = 'choice_div'+(category_number);
                alert(id); //for testing purposes only
                //add new choice:
                $("#"+id).append('<div id="choice_div'+category_number+'_choice'+choice_number+'"><br><input type="text" name="category_'+category_number+'_choice'+choice_number+'" id="category_'+category_number+'_choice'+choice_number+'" size="50"><input type="button" onclick="remove_choice()" id="remove_choice_category'+category_number+'_choice'+choice_number+'" class="remove_choice_button" value="Remove choice"><br></div>');
                //set add choice button id have new "number" variable value so that future choice additions have correct numbers
                choice_number = ++choice_number;
                $(this).attr("id",'add_choice_button'+choice_number);
            });

            //REMOVE CHOICE FUNCTION
            $('#category_space').on("click", ".remove_choice_button", function(){
                //get clicked button id
                var remove_button_id = $(this).closest('input').attr('id');
                alert(remove_button_id); //for testing purposes only
                //get parent div id 
                var id = $(this).closest('div').attr('id');
                $("#"+id).remove();
            });
        });


/*HTML FOR ADDING/REMOVING CHOICES SECTION ON CREATE NEW POOL PAGE
//MULTIPLE CHOICE CHECKBOX:
<input style="margin-left:25px" type="checkbox" class="multiple_choice_check" name="multiple_choice" value="multiple_choice">Multiple choice?


//CHOICES DIV (NEEDS TO BE ONE FOR EACH CATEGORY):
<div id="choice_div1" class="choice" style="margin-left:75px">
    <label for="choice1">Possible Choices:</label>                    
</div>


//ADD NEW CHOICE BUTTON
<input type="button" onclick="add_choice()" id="add_choice_button1" class="add_choice_button" value="Add choice">
*/






