<?php

header('Content-Type: text/html; charset=utf-8');
header('Content-Type: application/json');

require "User.php";
require "../hot_json_functions.php";

$json_str = '{"name": "Andrei", "email":"andreifcoelho@gmail.com", "pass":"od687qwdàçaghaoi23", "other_var_block":"this is not used"}';

$andrei = hot_json_decode($json_str, "User");
echo $andrei -> toString() . "\n\r";

$json_again = hot_json_encode($andrei);
echo $json_again;