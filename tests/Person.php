<?php 

/**
 * @kind age:int
 * @kind job:Job
 */

class Person extends Human {

    private $name;
    private $last_name;
    private $age;
    private $job;

    public function __construct(){
        parent::__construct(true, true);
    }

    public function toString() {
        return $this->name . " " . $this->last_name . ", ". $this->age . " years old. " . 
        "Your job is: " . $this -> job -> toString() ."\n\n".
        "inherited values: \n".
        "Sleep:".$this->sleep."\n".
        "Eat:".$this->eat."\n".
        "die:".$this->die."\n"
        ;
    }

}