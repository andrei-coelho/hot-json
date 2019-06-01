<?php 

/**
 * @kind languages:array
 */

class Job {

    private $name;
    private $languages;

    public function toString(){

        $str = $this -> name . " in ";
        foreach ($this -> languages as $lang) {
            $str .= $lang." and ";
        }
        return substr($str, 0, -4);

    }

}