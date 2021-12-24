<?php

namespace App\Libraries\Formatter;

class Html
{
    static function anchor_name($name){

        $name = preg_replace('/[^a-z0-9-]/i', '', $name);
        // Remove leading hyphens and numbers
        $name = ltrim($name, '-0..9');
        // Add Unique Name postfix
        // $name = $name .'_'. microtime();
        return preg_replace('/\s+/', '_', $name);

    }

    static function float_or_zero($string){

        $float = filter_var($string, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        if($float){
            return $float;
        }else{
            return 0;
        }

    }
}
