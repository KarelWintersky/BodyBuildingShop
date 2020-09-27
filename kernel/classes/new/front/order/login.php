<?php

class Front_Order_Login extends Common_Rq
{
    
    private $registry;
    
    private $Front_Order_Crumbs;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
        
        $this->Front_Order_Crumbs = new Front_Order_Crumbs( $this->registry );
    }
    
    public function do_vars()
    {
        $vars = array(
            'crumbs' => $this->Front_Order_Crumbs->do_crumbs( 1 ),
        );
        
        foreach ($vars as $k => $v) $this->registry[ 'CL_template_vars' ]->set( $k, $v );
    }
    
}

