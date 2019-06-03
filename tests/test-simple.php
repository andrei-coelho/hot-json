<?php

header('Content-Type: text/html; charset=utf-8');
header('Content-Type: application/json');

require "Job.php";
require "Animal.php";
require "Human.php";
require "Person.php";
require "../hot_json_functions.php";

$json_str = '{"name": "Andrei", "last_name":"Coelho", "age":30, "job":{"name":"Developer", "languages":["php", "java"]}, "eat":true, "sleep":true, "die":false}';

$andrei = hot_json_decode($json_str, "Person");
$errors = hot_json_last_error();

if($errors[0] != 0){
    echo $errors[1]; 
    // do something
    exit;
}
echo $andrei -> toString() . "\n\r";

//$json_again = hot_json_encode($andrei);
//echo $json_again;

