<?php 

function hot_json_encode ($var, int $options = 0, int $depth = 512) {
    return json_encode(transform_value($var), $options, $depth);
}

function hot_json_decode (string $json, string $class = null, bool $assoc = FALSE, int $depth = 512, int $options = 0) {

    if($class !== null){
        $json = json_decode($json, true);
        /*
            verificar se houve erro na geração do json
        */
        return object_instance($class, $json);
    }
    $json = json_decode($json,$assoc,$depth,$options);
    /*
        verificar se houve erro na geração do json
    */
    return $json;
}

function object_instance(string $class, array $json){

    $reflex =  new ReflectionClass($class);
    $comment = $reflex -> getDocComment();
    $inspector = inspec_comments($comment);
    return set_values($reflex, $json, $inspector);
    
}

function set_values(ReflectionClass $reflex, array $json, array $inspector){

    $obj = $reflex -> newInstanceWithoutConstructor();
    
    foreach ($json as $field => $value) {

        if($value === null) continue;

        if(array_key_exists($field, $inspector['block'])){
            if($inspector['block'][$field] == "ALL" || $inspector['block'][$field] == "DECODE"){
                continue;
            }
        }

        if(array_key_exists($field, $inspector['kind']) && $inspector['kind'][$field] != ""){
            $type = $inspector['kind'][$field];
            switch($type){
                case "string": $value = (string)$value; break;
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

function inspec_comments($comment){

    $inspec = [
        "kind" => [],
        "block" => []
    ];
    
    if($comment !== false) {

        preg_match_all('/@(kind|block)+\s+[\w:]+/', $comment, $array);
        
        foreach ($array[0] as $value) {

            preg_match('/@([\w]+)\s+([\w:]+)/', $value, $params);
            switch($params[1]){
    
                case "kind": 
                    if( strpos( $params[2],":" ) !== false) {
                        $values = explode(":", $params[2]);
                        $inspec['kind'][$values[0]] = $values[1]; 
                    }
                break;
    
                case "block": 
                    if( strpos( $params[2],":" ) !== false) {
                        $values = explode(":", $params[2]);
                        $inspec['block'][$values[0]] = strtoupper($values[1]); 
                    } else {
                        $inspec['block'][$params[2]] = "ALL";
                    }
                break;
    
            }
    
        }
    }

    return $inspec;
}

function transform_value ($val) {

    if (is_array($val)){
        $newValue = read_array($val);
    } else
    if (is_object($val)){
        $newValue = read_object($val);
    } else {
        $newValue = $val;
    }

    return $newValue;
}

function read_object (Object $obj) {

    $newArray = [];
    $reflex =  new ReflectionClass($obj);
    $inspector = inspec_comments($reflex -> getDocComment());
    $props = $reflex -> getProperties();

    foreach ($props as $prop){

        if(array_key_exists($prop -> getName(), $inspector['block'])){
            if($inspector['block'][$prop -> getName()] == "ALL" || $inspector['block'][$prop -> getName()] == "ENCODE"){
                continue;
            }
        }
        $prop -> setAccessible(true);
        $newArray[$prop -> name] = transform_value($prop -> getValue($obj));

    }

    return $newArray;
}

function read_array (array $arr) {

    $newArray = [];

    foreach ($arr as $key => $val){
        $newArray[$key] = transform_value($val);
    }

    return $newArray;
}