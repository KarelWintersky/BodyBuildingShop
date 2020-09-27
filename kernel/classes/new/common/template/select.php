<?php

class Common_Template_Select
{
    
    /*
     * val, name, selected, disabled
     * */
    
    public static function opts($data, $no = false)
    {
        $html = array();
        
        if ($no) array_unshift( $data, array(
            'val' => 0,
            'name' => ($no === true) ? 'нет' : $no,
            'selected' => false,
        ) );
        
        foreach ($data as $d)
            $html[] = sprintf( '<option value="%s"%s%s>%s</option>',
                $d[ 'val' ],
                (isset( $d[ 'selected' ] ) && $d[ 'selected' ]) ? ' selected' : '',
                (isset( $d[ 'disabled' ] ) && $d[ 'disabled' ]) ? ' disabled' : '',
                $d[ 'name' ]
            );
        
        return implode( '', $html );
    }
    
}

