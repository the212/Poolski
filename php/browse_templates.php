<?php
    include_once "inc/loggedin_check.php";
    include_once "inc/constants.inc.php";
    $pageTitle = "Browse Pool Templates";
    include_once "inc/header.php";

?>

    <h1>Browse Pool Templates</h1>
    <div id="carousel-example-generic" class="carousel slide" data-ride="carousel">
        <!-- Indicators -->
        <ol class="carousel-indicators">
        <li data-target="#carousel-example-generic" data-slide-to="0" class="active"></li>
        <li data-target="#carousel-example-generic" data-slide-to="1"></li>
        <li data-target="#carousel-example-generic" data-slide-to="2"></li>
        </ol>

        <!-- Wrapper for slides -->
        <div class="carousel-inner">
            <div class="item active">
                <img data-src="holder.js/900x500/auto/#777:#7a7a7a/text:First slide" alt="First slide">
                <div class="carousel-caption">
                    <h1><a href="create_new_template.php?template_id=3">Academy Awards Pick 'em</a></h1>
                    <p>Make your picks for who is taking home the golden statue.</p>
                </div>
            </div>
            <!--
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
            -->
        </div>

        <!-- Controls -->
        <a class="left carousel-control" href="#carousel-example-generic" data-slide="prev">
        <span class="glyphicon glyphicon-chevron-left"></span>
        </a>
        <a class="right carousel-control" href="#carousel-example-generic" data-slide="next">
        <span class="glyphicon glyphicon-chevron-right"></span>
        </a>
    </div>

    <div id="template_container">
        <h2 style="text-decoration:underline;">All Pool Templates:</h2>

        <span style="text-align:center;">
            <h3><a href="create_new_template.php?template_id=3">Academy Awards Pick 'em</a></h3>
            <p>Make your picks for who is taking home the golden statue.</p>
        </span>

        <br><br><br><br><br><br><br><br><br><br><br><br>s

    </div>

<?php    
    include_once 'inc/close.php';
?>
