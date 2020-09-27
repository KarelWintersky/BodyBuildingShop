<?php

class Front_Template_Css
{

    private $registry;
    private $path;
    private $data = array();

    function __construct($registry)
    {
        $this->registry = $registry;
        $this->registry->set( 'CL_css', $this );

        $this->set( array(
            'main',
            'chosen.jquery',
        ) );

        $this->path = '/browser/front/css/';
    }

    public function set($files)
    {
        if (!is_array( $files )) $files = array( $files );

        foreach ($files as $f) $this->data[ $f ] = true;
    }

    private function special_css()
    {
        $output = array();

        $special = array(
            'ie' => array( '<!--[if lt IE 9]>', '<![endif]-->' ),
            'ie6_ban' => array( '<!--[if IE 6]>', '<![endif]-->' ),
        );

        foreach ($special as $file => $wrap)
            $output[] = sprintf( '%s<link property="stylesheet" rel="stylesheet" href="%s.css" type="text/css">%s',
                $wrap[ 0 ],
                $this->path.$file,
                $wrap[ 1 ]
            );

        return $output;
    }

    public function go()
    {
        $output = array();
        $ver = '';

        foreach ($this->data as $file => $true) {
            if (strpos( $file, 'http://' ) === false && strpos( $file, 'https://' ) === false && strpos( $file, '//' ) === false) {
                $file_css = sprintf( '%s%s.css', $this->path, $file );

                if ($this->registry[ 'config' ][ 'optimise_frontend' ]) {
                    $file_css_min = sprintf( '%s%s.min.css', $this->path, $file );

                    if (!file_exists( ROOT_PATH.'public_html'.$file_css_min ) || filemtime( ROOT_PATH.'public_html'.$file_css ) != filemtime( ROOT_PATH.'public_html'.$file_css_min )) {
                        $buffer = file_get_contents( ROOT_PATH.'public_html'.$file_css );
                        // remove comments
                        $buffer = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer );
                        // remove tabs, spaces, new lines, etc.
                        $buffer = str_replace( array( "\r\n", "\r", "\n", "\t", ), '', $buffer );
                        while (strpos( $buffer, '  ' )) {
                            $spaces = '  ';
                            for ($i = 2; $i <= 8; $i++) {
                                $buffer = str_replace( $spaces, ' ', $buffer );
                                $spaces .= ' ';
                            }
                        }
                        $buffer = preg_replace( '/^\s*/', '', $buffer );
                        // remove unnecessary spaces
                        $buffer = str_replace( ' 0px', ' 0', $buffer );
                        //$buffer = str_replace(') ', ')', $buffer);
                        $buffer = str_replace( ' )', ')', $buffer );
                        $buffer = str_replace( '( ', '(', $buffer );
                        $buffer = str_replace( ' (', '(', $buffer );
                        $buffer = str_replace( ': ', ':', $buffer );
                        $buffer = str_replace( ' :', ':', $buffer );
                        $buffer = str_replace( ', ', ',', $buffer );
                        $buffer = str_replace( ' ,', ',', $buffer );
                        $buffer = str_replace( '; ', ';', $buffer );
                        $buffer = str_replace( ' ;', ';', $buffer );
                        $buffer = str_replace( '} ', '}', $buffer );
                        $buffer = str_replace( ' }', '}', $buffer );
                        $buffer = str_replace( '{ ', '{', $buffer );
                        $buffer = str_replace( ' {', '{', $buffer );
                        $buffer = str_replace( ';}', '}', $buffer );
                        //die($buffer);
                        file_put_contents( ROOT_PATH.'public_html'.$file_css_min, $buffer );
                        touch( ROOT_PATH.'public_html'.$file_css_min );
                        touch( ROOT_PATH.'public_html'.$file_css );
                    }
                    clearstatcache(); //clear cache for filemtime
                    $file_css = $file_css_min;
                }
                $ver .= filemtime( ROOT_PATH.'public_html'.$file_css );
            } else {
                $file_css = $file;
            }
            $this->data[ $file ] = $file_css;
        }
        $ver = hash( 'crc32', $ver );

        foreach ($this->data as $file => $file_css) {
            if (strpos( $file, 'http://' ) === false && strpos( $file, 'https://' ) === false && strpos( $file, '//' ) === false) {
                $file_css = sprintf(
                    '<link property="stylesheet" rel="stylesheet" href="%s?ver=%s" type="text/css">',
                    $file_css,
                    $ver
                );
            } else {
                $file_css = sprintf(
                    '<link property="stylesheet" rel="stylesheet" href="%s" type="text/css">',
                    $file_css
                );
            }

            $output[] = $file_css;
        }

        $output = array_merge( $output, $this->special_css() );

        $this->registry[ 'CL_template_vars' ]->set( 'css', implode( "\r\n", $output ) );
    }

}

