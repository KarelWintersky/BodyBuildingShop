<?php

class Adm_Statistics
{
    
    private $registry;
    
    private $Adm_Template_Menu;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
        
        $this->Adm_Template_Menu = new Adm_Template_Menu( $this->registry );
    }
    
    public function template_vars()
    {
        $vars = array(
            'statistics_menu' => $this->Adm_Template_Menu->print_menu( 18 ),
        );
        
        foreach ($vars as $k => $v) $this->registry[ 'CL_template_vars' ]->set( $k, $v );
    }
    
}

