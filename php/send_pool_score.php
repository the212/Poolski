<?php

/*
SEND_POOL_SCORE PHP FILE
BY EVAN PAUL, DECEMBER 20th, 2013
*/

include_once "inc/constants.inc.php";
include_once 'inc/class.pool.inc.php';

//IF _POST['score_pool_manual'] IS PASSED VIA POST, IT MEANS THAT WE ARE SCORING A POOL MANUALLY
if(isset($_POST['score_pool_manual'])) { //if the pool is being manually scored by the leader:
    $pool = new Pool();
    $score_result = $pool->ScorePickManually($_POST['category_id'], $_POST['user_id'], $_POST['pool_id'], $_POST['correct']);
    //echo $score_result;
}

if(isset($_POST['calculate_score'])) { //if we want to calculate the scores for the pool:
    $pool = new Pool();
    $calculate_pool_score_result = $pool->CalculatePoolScore($_POST['pool_id'], $_POST['finalize']); 
    //$calculate_pool_score_result will be an array whose keys are user ids and values are each user's point value
    //print_r($calculate_pool_score_result);
}

if(isset($_POST['template_score'])) { //if we are marking a template choice as correct:
    $pool = new Pool();
    $score_template_choice_result = $pool->ScoreTemplateChoice($_POST['category_id'], $_POST['correct_value']);
    echo $score_template_choice_result;
}

if(isset($_POST['template_tie_breaker_answer'])) { //if we are recording a template tie breaker answer:
    $pool = new Pool();
    $input_value = $_POST['update_value']; //get input value from page
    $update_tie_breaker_answer_for_template_result = $pool->UpdateTemplateTieBreakerAnswer($_POST['template_id'], $input_value);
    echo $input_value;
}

?>