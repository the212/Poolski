<?php
    include_once "inc/loggedin_check.php";
    include_once "inc/constants.inc.php";
    $pageTitle = "New Pool";
    include_once "inc/header.php";

    /*
    11/30/13 - IF WE WANT TO INCLUDE THE ABILITY TO SET THE START/END DATES/TIMES FOR THE POOL ON THIS PAGE, THEN WE SHOULD JUST ADD THEM AS INPUTS ON THE PAGE AND THEN ADD PHP CODE TO TAKE THE POST VALUES AND RUN A METHOD IN THE POOL CLASS FOR THEM - SEE EDIT POOL.PHP PAGE 
   */

    if(isset($_GET['template_id'])):
        //if a template id is specified:
        include_once 'inc/class.pool.inc.php';
        $pool = new Pool(); //new instance of the Pool class
        $pool_template_info = $pool->GetBasicTemplateInfo($_GET['template_id']);
        if(!empty($_POST['pool_title'])){ //if the form was submitted with the pool title variable set
            $new_pool_result = $pool->CreateNewPool($_SESSION['Username'] , $_POST['pool_title'] , $pool_template_info['Overall Question'], $_POST['pool_description'], $pool_template_info['Tie Breaker Question'], $_POST['public_private'], $_GET['template_id']);
            header("Location: edit_pool.php?pool_id=$new_pool_result[2]");
            exit();
        }
        //if form was not submitted:
        $template_category_info = $pool->GetTemplateCategories($_GET['template_id']);
?>
    <div style="text-align:center;">
        <h2><?php echo $pool_template_info['Template Name']; ?> Template </h2>
        <h4><?php echo $pool_template_info['Template Description']; ?></h4>
    </div>
    <br>
    <div id="content">
        <ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
            <li class="active"><a href="#create_new" data-toggle="tab">Create Pool From Template</a></li>
            <li><a href="#preview_template" data-toggle="tab">Preview Template Categories</a></li>
        </ul>
        <div id="pool_tab_content" class="tab-content">
            <div class="tab-pane fade in active" id="create_new">
                <h3>First, fill in the below information:</h3>
                <br>
                <form method="post" action="create_new_template.php?template_id=<?php echo $_GET['template_id']; ?>" name="new_pool_form" id="new_pool_form">
                    <div>
                        <label for="pool_title">Pool Title</label>
                        <span class="field_label">Give your pool a name.</span>
                        <br>
                        <input type="text" name="pool_title" id="pool_title" size="100"/>
                        <br><br>
                        <label for="pool_description">Pool Description (optional)</label>
                        <br>
                        <input type="text" name="pool_description" id="pool_description" size="60"></input>
                        <br>
                        <br>
                        <input type="radio" name="public_private" value="public" checked="checked"> Make Pool Public (Default - any member can invite others to join the pool)<br>
                        <input type="radio" name="public_private" value="private"> Make Pool Private (only you as the leader can invite others)<br>

                        <h3><input type="submit" name="new_pool_button" id="new_pool_button" value="Create New Pool" class="button" /></h3>

                        <br>
                        <br>

                        <input type="hidden" name="form_sent" value="TEST"/>
                    </div>
                </form>
                <br>
            </div> <!--END OF CREATE NEW TAB DIV-->
            
            <div class="tab-pane fade in" id="preview_template">
                <h3>Template Categories</h3>
                <br>
<?php
                foreach($template_category_info as $category_id => $category_info){
?>
                    <div class="row" style="width:65%;">
                        <div class="col-md-5">
                            <h4><?php echo $category_info['Category Name']; ?> </h4>
                        </div>
                        <div class="col-md-2">
                            <h5>Point Value: <?php echo $category_info['Category Point Value']; ?></h5>
                        </div>
                        <div class="col-md-5">
                            <div class="bfh-selectbox" data-name="selectbox1" data-value="Click to see choices" data-filter="true">
                                <div data-value='Click to see choices'><span style="font-style:italic;">Click to see choices</span><!--default dropdown value if no pick has been made-->
                                </div> 
<?php
                    $category_choices = $pool->GetCategoryChoices($category_id);
                    foreach($category_choices as $choice_number => $choice){ //put all of the given category's multiple choices in the bfh-selectbox dropdown menu
?>
                                <div data-value='<?php echo $choice; ?>'><?php echo $choice; ?> <!--display category choices in a drop down-->
                                </div>                    
<?php
                    }
?>                      
                            </div>
                        </div><!--END OF COLUMN DIV-->
                    </div><!--END OF ROW DIV-->
<?php
                }
?>
            </div> <!--END OF PREVIEW_TEMPLATE TAB DIV-->

        </div> <!--END OF POOL TAB CONTENT DIV-->
    </div> <!--END OF CONTENT DIV-->
        
<?php
    else:
        //if no template_id variable is specified, we send the user home
        header("Location: home.php");
    endif;
    /*
    NEED TO EDIT THE BELOW FUNCTION - IT WILL DISPLAY THE ERROR MESSAGE EVEN IF POOL WAS SUBMITTED WITH ALL FIELDS FILLED IN
    if(isset($_POST['form_sent'])){
        //if form was sent without all required fields:
        echo "<p style='color:red'>Please fill in all required fields.</p>";
    }
    include_once 'inc/close.php';
?>
