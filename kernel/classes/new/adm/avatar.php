<?php

class Adm_Avatar
{
    
    private $registry;
    private $Adm_Avatar_Form;
    private $Adm_Avatar_Upload;
    private $Common_Avatar;
    
    function __construct($registry, $part)
    {
        $this->registry = $registry;
        $this->registry->set( 'CL_avatar', $this );
        
        $this->Common_Avatar = new Common_Avatar( $this->registry );
        $this->Adm_Avatar_Form = new Adm_Avatar_Form( $this->registry, $part );
        $this->Adm_Avatar_Upload = new Adm_Avatar_Upload( $this->registry, $part );
    }
    
}

