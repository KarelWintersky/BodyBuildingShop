<?php

class Adm_Settings_Sitemap_Xml
{

    private $registry;

    private $SM;
    private $SM_urlset;

    private $file;

    public function __construct($registry)
    {
        $this->registry = $registry;

        $this->file = ROOT_PATH.'public_html/sitemap.xml';
    }

    public function do_file($data)
    {
        $this->SM = new DOMDocument( '1.0', 'UTF-8' );
        $this->SM->formatOutput = true;

        $this->SM_urlset = $this->SM->createElementNS( 'http://www.sitemaps.org/schemas/sitemap/0.9', 'urlset' );
        $this->SM->appendChild( $this->SM_urlset );

        $this->sitemap_node( THIS_URL, $data[ 'main_page' ], 'daily' );

        foreach ($data[ 'pages' ] as $first_level_alias => $arr) {
            $alias_f = ($first_level_alias != 'index')
                ? THIS_URL.$first_level_alias.'/'
                : THIS_URL;

            $this->sitemap_node( $alias_f, $arr[ 'modified' ] );

            if (!isset( $arr[ 'ch' ] )) continue;

            foreach ($arr[ 'ch' ] as $second_level_alias => $ch_arr) {

                $alias_s = $alias_f.$second_level_alias.'/';

                $this->sitemap_node( $alias_s, $ch_arr[ 'modified' ] );

                if (!isset( $ch_arr[ 'ch' ] )) continue;

                foreach ($ch_arr[ 'ch' ] as $third_level_alias => $th_arr) {

                    $alias_t = $alias_s.$third_level_alias.'/';
                    $this->sitemap_node( $alias_t, $th_arr[ 'modified' ] );
                }

            }
        }

        $this->SM->save( $this->file );
    }

    private function sitemap_node($alias, $lastmod, $changefreq = 'weekly')
    {
        $url = $this->SM->createElement( 'url' );
        $this->SM_urlset->appendChild( $url );

        $loc = $this->SM->createElement( 'loc', $alias );
        $url->appendChild( $loc );

        $lastmod = $this->SM->createElement( 'lastmod', date( 'Y-m-d', strtotime( $lastmod ) ) );
        $url->appendChild( $lastmod );

        $priority = $this->SM->createElement( 'priority', '1' );
        $url->appendChild( $priority );

        $changefreq = $this->SM->createElement( 'changefreq', $changefreq );
        $url->appendChild( $changefreq );
    }

}

