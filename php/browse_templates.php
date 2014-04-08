<?php
    include_once "inc/loggedin_check.php";
    include_once "inc/constants.inc.php";
    $pageTitle = "Browse Pool Templates";
    include_once "inc/header.php";
    include_once 'inc/class.pool.inc.php';
    $pool = new Pool(); 
    $published_templates_array = $pool->GetPublishedTemplates();

?>
    <!--
    <h1>Browse Pool Templates</h1>
    <div id="carousel-example-generic" class="carousel slide" data-ride="carousel">
        <ol class="carousel-indicators">
        <li data-target="#carousel-example-generic" data-slide-to="0" class="active"></li>
        <li data-target="#carousel-example-generic" data-slide-to="1"></li>
        <li data-target="#carousel-example-generic" data-slide-to="2"></li>
        </ol>

        <div class="carousel-inner">
            <div class="item active">
                <img data-src="holder.js/900x500/auto/#777:#7a7a7a/text:First slide" alt="First slide">
                <div class="carousel-caption">
                    <h1><a href="create_new_template.php?template_id=3">Academy Awards Pick 'em</a></h1>
                    <p>Make your picks for who is taking home the golden statue.</p>
                </div>
            </div>
            
            <div class="item">
                <img data-src="holder.js/900x500/auto/#777:#7a7a7a/text:First slide" alt="Second slide">
                <div class="carousel-caption">
                    <h1><a href="create_new_template.php?template_id=2">World Cup 2014</a></h1>
                    <p>Choose the winner of each world cup match.</p>
                </div>
            </div>
            <div class="item">
                <img data-src="holder.js/900x500/auto/#777:#7a7a7a/text:First slide" alt="Third slide">
                <div class="carousel-caption">
                    <h1><a>Game of Thrones Pick 'em</a></h1>
                    <p>Who will live and who will die during season 4?  If you've read the book, then don't bother.</p>
                </div>
            </div>
        </div>

        <a class="left carousel-control" href="#carousel-example-generic" data-slide="prev">
        <span class="glyphicon glyphicon-chevron-left"></span>
        </a>
        <a class="right carousel-control" href="#carousel-example-generic" data-slide="next">
        <span class="glyphicon glyphicon-chevron-right"></span>
        </a>
    </div>
    -->
    <div id="template_container" style="text-align: center">
        <h2>Choose one of our pre-set templates for your new pool!  </h2>
        <div style="margin-left:15%; margin-right:15%; padding-top:10px; text-align:left;">
            <h4>All pick 'em categories and questions are already made, and scoring is done automatically when the pool ends.  
                You don't have to worry about anything except making your picks!</h4>
            <br><br>
        </div>
        <br>
<?php
    foreach($published_templates_array as $template_id => $template_info){ //for each published template:
?>
        <div class="thumbnail" style="margin-left:15%; margin-right:15%;">
            <h2><a href="create_new_template.php?template_id=<?php echo $template_id; ?>"><?php echo $template_info['Template Name']; ?></a></h2>
            <h4><?php echo $template_info['Template Description']; ?></h4>
        </div>    
        <br><br>
<?php
    }
?>
        <br><br>
        <h4>More templates coming soon...</h4>
    </div>

<?php    
    include_once 'inc/close.php';
?>
