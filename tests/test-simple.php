<?php

require "Job.php";
require "Person.php";
require "../hot_json_functions.php";

$json_str = '{"name": "Andrei", "last_name":"Coelho", "age":30, "job":{"name":"Developer", "languages":["php", "java"]}}';

$andrei = hot_json_decode($json_str, "Person");
echo $andrei -> toString() . "\n\r";

$json_again = hot_json_encode($andrei);
echo $json_again;