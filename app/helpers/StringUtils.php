<?php

class StringUtils {
    
    public static function randKey($length = 32,$mode = '') {
        $string = '';
        
        switch($mode) {
            case 'all':
                $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()-_=+[]{},./<>?~';
            break;
            case 'hex':
                $pool = '0123456789ABCDEF';
            break;
            case 'num':
                $pool = '0123456789';
            break;
            case 'alnum':
                $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            break;
            default:
                $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            break;
        }
        
        $pool_size = strlen($pool);
        for($x = 0; $x < $length; $x++) {
            $string .= $pool[rand(0, $pool_size-1)];
        }
        
        return $string;
    }//end method randKey

}