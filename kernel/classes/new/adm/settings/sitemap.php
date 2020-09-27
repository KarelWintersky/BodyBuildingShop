<?php

class Adm_Settings_Sitemap
{

    private $registry;

    private $Adm_Settings_Sitemap_Data;
    private $Adm_Settings_Sitemap_Xml;
    private $Adm_Settings_Sitemap_Txt;

    public function __construct($registry)
    {
        $this->registry = $registry;

        $this->Adm_Settings_Sitemap_Data = new Adm_Settings_Sitemap_Data( $this->registry );
        $this->Adm_Settings_Sitemap_Xml = new Adm_Settings_Sitemap_Xml( $this->registry );
        $this->Adm_Settings_Sitemap_Txt = new Adm_Settings_Sitemap_Txt( $this->registry );
    }

    public function mk_files()
    {
        $data = $this->Adm_Settings_Sitemap_Data->get_data();

        $this->Adm_Settings_Sitemap_Xml->do_file( $data );
        $this->Adm_Settings_Sitemap_Txt->do_file( $data[ 'pages' ] );
    }

}

?>