<?php 
/**
 * @block pass:encode
 * @block other_var_block
 */
class User {
    
    private $name, $email, $pass;
    private $other_var_block;

    public function toString(){
        return $this -> name . " | email > " . $this -> email . " | pass > ". $this->pass . " | other_var_block > ". $this->other_var_block;
    }

}
