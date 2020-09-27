<?php

class Pdfmanager
{
    
    private $registry;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
    }
    
    public function fileCompose($msg, $outputtype = 'S')
    {
        
        $pdf = new TCPDF( 'P', 'mm', 'A4', true, 'UTF-8', false );
        $pdf->setPrintHeader( false );
        $pdf->setPrintFooter( false );
        $pdf->SetFont( 'arial', '', 10 );
        $pdf->SetTextColor( 0, 0, 0 );
        $pdf->SetDisplayMode( 'fullpage', '', '' );
        
        $pdf->AddPage();
        
        $pdf->writeHTML( $msg );
        
        return $pdf->Output( 'bill.pdf', $outputtype );
        
    }
    
}
