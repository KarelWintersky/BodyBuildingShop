<?php

class Adm_Prices_Excel_Style
{

    private $registry;

    public function __construct($registry)
    {
        $this->registry = $registry;
    }

    public function style()
    {
        return array(
            'parent_h' => array(
                'font' => array( 'bold' => true, 'size' => 16, 'underline' => PHPExcel_Style_Font::UNDERLINE_SINGLE, 'color' => array( 'rgb' => '4f81bd' ) ),
                'alignment' => array( 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER ),
            ),
            'date' => array(
                'font' => array( 'size' => 7, 'color' => array( 'rgb' => '7f7f7f' ) ),
                'alignment' => array( 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER ),
            ),
            'level_h' => array(
                'font' => array( 'bold' => true, 'size' => 13, 'underline' => PHPExcel_Style_Font::UNDERLINE_SINGLE, 'color' => array( 'rgb' => '4f81bd' ) ),
                'alignment' => array( 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER ),
            ),
            'goods_block_h' => array(
                'font' => array( 'bold' => true ),
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array( 'rgb' => 'f2f2f2' ),
                ),
                'alignment' => array( 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER ),
            ), 'goods_n' => array(
                'font' => array( 'underline' => PHPExcel_Style_Font::UNDERLINE_SINGLE, 'color' => array( 'rgb' => '4f81bd' ) ),
            ),
            'new' => array(
                'font' => array( 'italic' => true, 'bold' => true ),
            ),
            'present' => array(
                0 => array(
                    'font' => array( 'bold' => true, 'color' => array( 'rgb' => 'ff0000' ) ),
                ),
                1 => array(
                    'font' => array( 'bold' => true, 'color' => array( 'rgb' => '75923c' ) ),
                ),
            ),
            'goods_block_values' => array(
                'alignment' => array( 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER ),
            ),
            'goods_block_overall' => array(
                'borders' => array(
                    'allborders' => array( 'style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array( 'rgb' => '000000' ) ),
                ),
            ),
            'common' => array(
                'alignment' => array( 'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER ),
            ),
            'old_price' => array(
                'font' => array( 'color' => array( 'rgb' => 'cccccc' ) ),
            ),
        );
    }

}

