<?php

class Front_Template_Compress
{

    private $registry;

    public function __construct($registry)
    {
        $this->registry = $registry;
    }

    private function replace_spaces($html)
    {
        $search = array(
            '/\>[^\S ]+/s',  // strip whitespaces after tags, except space
            '/[^\S ]+\</s',  // strip whitespaces before tags, except space
            '/(\s)+/s'       // shorten multiple whitespace sequences
        );

        $replace = array(
            '>',
            '<',
            '\\1',
        );

        $html = preg_replace( $search, $replace, $html );

        return $html;
    }

    private function do_gzip($html)
    {
        $html = gzencode( $html );

        header( 'content-encoding: gzip' );
        header( 'vary: accept-encoding' );
        header( 'content-length: '.strlen( $html ) );

        return $html;
    }

    public function do_compress($html)
    {
        if (!$this->registry[ 'config' ][ 'optimise_frontend' ]) return $html;

        if (!isset( $this->registry[ 'userdata' ] ) || !$this->registry[ 'userdata' ] || $this->registry[ 'userdata' ][ 'type' ] == 1) {
            $html = $this->replace_spaces( $html );
        }

        $html = $this->do_gzip( $html );

        return $html;
    }


}

