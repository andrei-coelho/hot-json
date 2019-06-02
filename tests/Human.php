<?php 

/**
 * @kind sleep:bool
 * @kind eat:bool
 * @block sleep
 */

class Human extends Animal{

    protected $sleep;
    protected $eat;

    public function __construct(bool $sleep, bool $eat){
        $this->sleep = $sleep;
        $this->eat = $eat;
    }

}