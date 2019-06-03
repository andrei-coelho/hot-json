<?php

/**
 * @author Andrei Coelho
 * @version 2.1
 */

function hot_json_encode ($var, int $options = 0, int $depth = 512) {
    return json_encode(transform_value($var), $options, $depth);
}

function hot_json_decode (string $json, string $class = null, bool $assoc = FALSE, int $depth = 512, int $options = 0) {
    
    $json = preg_replace("#(/\*([^*]|[\r\n]|(\*+([^*/]|[\r\n])))*\*+/)|([\s\t](//).*)#", '', $json); 
    
    if($class !== null){
        $json = json_decode($json, true);
        if(json_last_error() !== JSON_ERROR_NONE) return null;
        return object_instance($class, $json);
    }

    $json = json_decode($json,$assoc,$depth,$options);
    if(json_last_error() !== JSON_ERROR_NONE) return false;
    return $json;
}

function hot_json_last_error(){
    switch (json_last_error()) {
        case JSON_ERROR_NONE:
            return [0,'No errors'];
        break;
        case JSON_ERROR_DEPTH:
            return [1,'Maximum stack depth exceeded'];
        break;
        case JSON_ERROR_STATE_MISMATCH:
            return [2,'Underflow or the modes mismatch'];
        break;
        case JSON_ERROR_CTRL_CHAR:
            return [3,'Unexpected control character found'];
        break;
        case JSON_ERROR_SYNTAX:
            return [4,'Syntax error, malformed JSON'];
        break;
        case JSON_ERROR_UTF8:
            return [5,'Malformed UTF-8 characters, possibly incorrectly encoded'];
        break;
        default:
            return [6,'Unknown error'];
        break;
    }
}

function object_instance(string $class, array $json){

    $reflex =  new ReflectionClass($class);
    $comment = $reflex -> getDocComment();
    $inspector = inspec_comments($comment);
    $bloquers = [];

    if($inspector['bound'] === false && ($parent = $reflex -> getParentClass()) !== false)
        $bloquers = get_inherited_block_values($parent, "DECODE");

    return set_values($reflex, $json, $inspector, $bloquers);
    
}

function get_inherited_block_values(ReflectionClass $parent, string $type){

    $comment = $parent -> getDocComment();
    $inspector = inspec_comments($comment);
    $bloquers = [];

    if($inspector['bound'] === false && ($parentP = $parent -> getParentClass()) !== false)
        $bloquers = get_inherited_block_values($parentP, $type);
    
    foreach($inspector['block'] as $field => $ty)
        if($ty == $type || $ty == "ALL") $bloquers[] = $field;
        
    return $bloquers;
}

function set_values(ReflectionClass $reflex, array $json, array $inspector, array $bloquers){

    $obj = $reflex -> newInstanceWithoutConstructor();
    
    foreach ($json as $field => $value) {
        
        if($value === null || in_array($field, $bloquers))
            continue;
        
        if(array_key_exists($field, $inspector['block']))
            if($inspector['block'][$field] == "ALL" || $inspector['block'][$field] == "DECODE")
                continue;

        if(array_key_exists($field, $inspector['kind']) && $inspector['kind'][$field] != ""){
            $type = $inspector['kind'][$field];
            switch($type){
                case "string": 
                    if(is_array($value))
                        $value = array_to_string($value);
                    else
                        $value = (string)$value;
                break;
                $value = (string)$value; break;
                case "int": $value = (int)$value; break;
                case "float": $value = (float)$value; break;
                case "bool": $value = (bool)$value; break;
                case "array": $value = (array)$value; break;
                default: $value = object_instance($type, $value);
            }
        }

        if($reflex -> hasProperty($field)){
            $prop = $reflex -> getProperty($field);
            $prop -> setAccessible(true);
            $prop -> setValue($obj, $value);
        }
        
    }

    return $obj;
}

function array_to_string(array $arr){
    $str = "[";
    foreach($arr as $key => $val) {
        if(!is_numeric($key)) $str .= $key . ":";
        if(is_array($val))
            $str .= array_to_string($val).",";
        else
            $str .= $val.",";
    }
    return substr($str,0,-1) . "]";
}

function inspec_comments($comment){

    $inspec = [
        "kind" => [],
        "block" => [],
        "bound" => false
    ];
    
    if($comment !== false) {
        
        preg_match_all('/(@(kind|block)+\s+[\w:]+|@bound)/', $comment, $array);
        
        foreach ($array[1] as $value) {

            preg_match('/(@([\w]+)\s+([\w:]+)|@(bound))/', $value, $params);

            if(isset($params[4]) && $params[4] == "bound"){
                $inspec['bound'] = true;
                continue;
            }

            switch($params[2]){
    
                case "kind": 
                    if( strpos( $params[3],":" ) !== false) {
                        $values = explode(":", $params[3]);
                        $inspec['kind'][$values[0]] = $values[1]; 
                    }
                break;
    
                case "block": 
                    if( strpos( $params[3],":" ) !== false) {
                        $values = explode(":", $params[3]);
                        $inspec['block'][$values[0]] = strtoupper($values[1]); 
                    } else {
                        $inspec['block'][$params[3]] = "ALL";
                    }
                break;
    
            }
    
        }
    }

    return $inspec;
}

function transform_value ($val) {

    if (is_array($val))
        $newValue = read_array($val);
    else
    if (is_object($val))
        $newValue = read_object($val);
    else
        $newValue = $val;

    return $newValue;
}

function read_object (Object $obj) {
    
    $nObj = get_class($obj); 
    $reflex =  new ReflectionClass($obj);
    $inspector = inspec_comments($reflex -> getDocComment());
    $newArray = [];
    $bloquers = [];

    if($inspector['bound'] === false && ($parent = $reflex -> getParentClass()) !== false)
        $bloquers = get_inherited_block_values($parent, "ENCODE");

    $props = $reflex -> getProperties();

    foreach ($props as $prop){
        
        if($nObj != $prop->class && in_array($prop->name, $bloquers)) continue;
        if(array_key_exists($prop->name, $inspector['block']))
            if($inspector['block'][$prop->name] == "ALL" || $inspector['block'][$prop->name] == "ENCODE")
                continue;

        $prop -> setAccessible(true);
        $newArray[$prop -> name] = transform_value($prop -> getValue($obj));

    }

    return $newArray;
}

function read_array (array $arr) {

    $newArray = [];
    foreach ($arr as $key => $val)
        $newArray[$key] = transform_value($val);

    return $newArray;

}