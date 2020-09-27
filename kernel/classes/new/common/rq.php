<?php

abstract class Common_Rq
{
    
    protected function do_rq($name, $a, $loop = false)
    {
        $path = explode( '_', get_called_class() );
        foreach ($path as $key => $val) $path[ $key ] = mb_strtolower( $val, 'utf-8' );
        
        $folder = array_shift( $path );
        
        $dir = sprintf( '%stpl/%s/rq/',
            ROOT_PATH,
            $folder
        );
        
        $file = sprintf( '%s%s/%s%s.html',
            $dir,
            implode( '/', $path ),
            ($loop) ? '_' : '',
            $name
        );
        
        ob_start();
        require($file);
        return ob_get_clean();
    }
    
}

