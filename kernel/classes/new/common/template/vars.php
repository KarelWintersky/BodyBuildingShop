<?php

class Common_Template_Vars
{
    
    private $registry;
    private $vars = array();
    
    public function __construct($registry)
    {
        $this->registry = $registry;
        $this->registry->set( 'CL_template_vars', $this );
    }
    
    public function set($name, $value)
    {
        $this->vars[ $name ] = $value;
    }
    
    public function vars_replace($html)
    {
        $var_reg = "/\{\{([a-z]+[^}]*)\}\}/i";
        for ($i = 1; $i <= 3; $i++) $html = preg_replace_callback( $var_reg, array( $this, 'vars_find' ), $html );
        
        return $html;
    }
    
    private function vars_find($matches)
    {
        return (isset( $this->vars[ $matches[ 1 ] ] )) ? $this->vars[ $matches[ 1 ] ] : $matches[ 0 ];
    }
    
}

