<?php

class Front_Template_Texted
{

    /*
     * замена разных элементов в текстовых блоках (.texted)
     * */

    private $registry;

    private $Front_Template_Texted_Li;

    function __construct($registry)
    {
        $this->registry = $registry;

        $this->Front_Template_Texted_Li = new Front_Template_Texted_Li( $this->registry );
    }

    public function do_replace($html)
    {
        $html = $this->Front_Template_Texted_Li->replace_li( $html );

        return $html;
    }

}

