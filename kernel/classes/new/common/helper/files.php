<?php

class Common_Helper_Files
{
    
    public static function filename2translit($filename)
    {
        $filename = explode( '.', $filename );
        $ext = array_pop( $filename );
        
        $filename = implode( '.', $filename );
        
        $filename = Common_Useful::rus2translit( $filename );
        
        return $filename.'.'.$ext;
    }
    
    public static function clear_dir($dir, $delete_this = false)
    {
        if (!is_dir( $dir )) return false;
        
        $objects = scandir( $dir );
        foreach ($objects as $object) {
            if ($object != "." && $object != "..")
                if (filetype( $dir."/".$object ) == "dir") self::clear_dir( $dir."/".$object, true );
                else unlink( $dir."/".$object );
        }
        reset( $objects );
        
        $objects = scandir( $dir );
        
        if ($delete_this) self::delete_dir_if_empty( $dir );
    }
    
    public static function create_dir($dir)
    {
        if (!is_dir( $dir )) mkdir( $dir );
    }
    
    public static function delete_dir_if_empty($dir)
    {
        if (self::is_dir_empty( $dir )) rmdir( $dir );
    }
    
    private static function is_dir_empty($dir)
    {
        if (!is_readable( $dir )) return false;
        
        $handle = opendir( $dir );
        while (false !== ($entry = readdir( $handle )))
            if ($entry != "." && $entry != "..") return false;
        
        return true;
    }
    
}

