<?php

class Front_Template_Texted_Li
{

    private $registry;

    function __construct($registry)
    {
        $this->registry = $registry;
    }

    public function replace_li($html)
    {
        $document = phpQuery::newDocumentHTML( $html, 'utf-8' );

        $texted = $document->find( 'div.texted' );

        foreach ($texted as $el) {
            $block = pq( $el );

            $lis = $block->find( 'li' );

            foreach ($lis as $li) {
                $li = pq( $li );

                $li->replaceWith( sprintf( '<li><span class="texted_li_gray">%s</span></li>',
                    $li->html()
                ) );
            }
        }

        return (string)$document;
    }
}

