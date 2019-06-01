<?php 

/**
 * @kind age:int
 * @kind job:Job
 */

class Person {

    private $name;
    private $last_name;
    private $age;
    private $job;

    public function toString() {
        return $this->name . " " . $this->last_name . ", ". $this->age . " years old. " . 
        "Your job is: " . $this -> job -> toString();
    }

}