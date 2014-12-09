<?php
Class Adm_Prices_Excel{

	private $registry;
	
	private $XLS;
	private $RO;
	private $style;
	
	private $Adm_Prices_Excel_Data;
	private $Adm_Prices_Excel_Style;
	
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->Adm_Prices_Excel_Data = new Adm_Prices_Excel_Data($this->registry);
		$this->Adm_Prices_Excel_Style = new Adm_Prices_Excel_Style($this->registry);
	}
	
	private function meta($time){
		$this->XLS->getProperties()->setCreator('Bodybuilding Shop')
			->setLastModifiedBy('Bodybuilding Shop')
			->setTitle('Прайс лист от '.$time)
			->setSubject('Прайс лист от '.$time)
			->setDescription('Прайс лист от '.$time)
			->setKeywords('Прайс лист от '.$time)
			->setCategory('Прайс лист от '.$time);
		
		$this->XLS->getDefaultStyle()->getFont()->setName('Verdana');
		$this->XLS->getDefaultStyle()->getFont()->setSize(8);
		
		$this->XLS->removeSheetByIndex(0);		
	}
	
	private function sheets($data,$time){
		$i = 0;
		foreach($data['parents'] as $parent_id => $p){
			$S = $this->XLS->createSheet();
			$S->setTitle($p['name']);
			$this->XLS->setActiveSheetIndex($i);

			$this->RO = 1;
			
			$this->XLS->getActiveSheet()->setCellValueByColumnAndRow(0, $this->RO, $p['name']);
			$this->XLS->getActiveSheet()->getCellByColumnAndRow(0, $this->RO)->getHyperlink()->setUrl($p['url']);
			$this->XLS->getActiveSheet()->getStyle('A'.$this->RO)->applyFromArray($this->style['parent_h']);
			$this->XLS->getActiveSheet()->mergeCells('A'.$this->RO.':G'.$this->RO);
			$this->XLS->getActiveSheet()->getRowDimension($this->RO)->setRowHeight(30);
			$this->RO++;
			
			$this->XLS->getActiveSheet()->setCellValueByColumnAndRow(0, $this->RO, 'Дата прайслиста: '.$time);
			$this->XLS->getActiveSheet()->getStyle('A'.$this->RO)->applyFromArray($this->style['date']);
			$this->XLS->getActiveSheet()->mergeCells('A'.$this->RO.':G'.$this->RO);
			$this->XLS->getActiveSheet()->getRowDimension($this->RO)->setRowHeight(17);
			$this->RO+=2;			
			
			$this->levels($parent_id,$data);
			
			$this->XLS->getActiveSheet()->getStyle('A1:G'.$this->RO)->applyFromArray($this->style['common']);
			
			$this->XLS->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
			$this->XLS->getActiveSheet()->getColumnDimension('B')->setWidth(10);
			$this->XLS->getActiveSheet()->getColumnDimension('C')->setWidth(13);
			$this->XLS->getActiveSheet()->getColumnDimension('D')->setWidth(12);
			$this->XLS->getActiveSheet()->getColumnDimension('E')->setWidth(12);
			$this->XLS->getActiveSheet()->getColumnDimension('F')->setWidth(10);
			
			$i++;
		}
	}
	
	private function levels($parent_id,$data){
		foreach($data['levels'][$parent_id] as $level_id => $l){
			$this->XLS->getActiveSheet()->setCellValueByColumnAndRow(0, $this->RO, $l['name']);
			$this->XLS->getActiveSheet()->getCellByColumnAndRow(0, $this->RO)->getHyperlink()->setUrl($l['url']);
			$this->XLS->getActiveSheet()->getStyle('A'.$this->RO)->applyFromArray($this->style['level_h']);
			$this->XLS->getActiveSheet()->mergeCells('A'.$this->RO.':G'.$this->RO);
			$this->XLS->getActiveSheet()->getRowDimension($this->RO)->setRowHeight(22);
			$this->RO++;
			
			$this->goods($level_id,$data);
				
		}
	}
	
	private function goods($level_id,$data){
		$this->XLS->getActiveSheet()->setCellValueByColumnAndRow(0, $this->RO, 'Название');
		$this->XLS->getActiveSheet()->setCellValueByColumnAndRow(1, $this->RO, 'Скидка');
		$this->XLS->getActiveSheet()->setCellValueByColumnAndRow(2, $this->RO, 'Упаковка');
		$this->XLS->getActiveSheet()->setCellValueByColumnAndRow(3, $this->RO, 'Цена');
		$this->XLS->getActiveSheet()->setCellValueByColumnAndRow(4, $this->RO, 'Новый');
		$this->XLS->getActiveSheet()->setCellValueByColumnAndRow(5, $this->RO, 'В продаже');		
		$this->XLS->getActiveSheet()->getStyle('A'.$this->RO.':F'.$this->RO)->applyFromArray($this->style['goods_block_h']);
		$this->RO++;
		
		$block_goods_start = $this->RO; 
		foreach($data['goods'][$level_id] as $g){
			$this->XLS->getActiveSheet()->setCellValueByColumnAndRow(0, $this->RO, $g['name']);
			$this->XLS->getActiveSheet()->getCellByColumnAndRow(0, $this->RO)->getHyperlink()->setUrl($g['url']);
			$this->XLS->getActiveSheet()->getStyle('A'.$this->RO)->applyFromArray($this->style['goods_n']);
			
			$this->XLS->getActiveSheet()->setCellValueByColumnAndRow(1, $this->RO, $g['discount']);
			$this->XLS->getActiveSheet()->setCellValueByColumnAndRow(2, $this->RO, $g['packing']);
			
			$this->XLS->getActiveSheet()->setCellValueByColumnAndRow(3, $this->RO, $g['price']);
			
			$this->XLS->getActiveSheet()->setCellValueByColumnAndRow(4, $this->RO, $g['new']);
			$this->XLS->getActiveSheet()->getStyle('E'.$this->RO)->applyFromArray($this->style['new']);
			
			$this->XLS->getActiveSheet()->setCellValueByColumnAndRow(5, $this->RO, $g['present']);
			$this->XLS->getActiveSheet()->getStyle('F'.$this->RO)->applyFromArray($this->style['present'][$g['present_val']]);			
			$this->RO++;
			
			if($g['discount']){
				$this->XLS->getActiveSheet()->setCellValueByColumnAndRow(3, $this->RO, $g['new_price']);
			
				$merge_start = $this->RO-1;
				$merge_fin = $this->RO;
				$this->XLS->getActiveSheet()->mergeCells('A'.$merge_start.':A'.$merge_fin);
				$this->XLS->getActiveSheet()->mergeCells('B'.$merge_start.':B'.$merge_fin);
				$this->XLS->getActiveSheet()->mergeCells('C'.$merge_start.':C'.$merge_fin);
				$this->XLS->getActiveSheet()->mergeCells('E'.$merge_start.':E'.$merge_fin);
				$this->XLS->getActiveSheet()->mergeCells('F'.$merge_start.':F'.$merge_fin);
			
				$this->XLS->getActiveSheet()->getStyle('D'.$merge_start)->applyFromArray($this->style['old_price']);
			
				$this->RO++;
			}		
		}
		
		$block_goods_fin = $this->RO-1;
		$this->XLS->getActiveSheet()->getStyle('B'.$block_goods_start.':F'.$block_goods_fin)->applyFromArray($this->style['goods_block_values']);
		$this->XLS->getActiveSheet()->getStyle('A'.$block_goods_start.':F'.$block_goods_fin)->applyFromArray($this->style['goods_block_overall']);
		for($k=$block_goods_start;$k<=$block_goods_fin;$k++) $this->XLS->getActiveSheet()->getRowDimension($k)->setRowHeight(16);
			
		$this->RO+=2;		
	}
	
	public function make_pricelist(){
		$data = $this->Adm_Prices_Excel_Data->get_data();
		
		$this->XLS = new PHPExcel();
		$this->style = $this->Adm_Prices_Excel_Style->style();
		
		$time = date('d.m.Y');
		$this->meta($time);
		
		$this->sheets($data,$time);
		
		$this->XLS->setActiveSheetIndex(0);
		
		$writer = PHPExcel_IOFactory::createWriter($this->XLS, 'Excel5');
		$writer->save(ROOT_PATH.'/public_html/data/sportivnoe-pitanie-price.xls');
	}	
}
?>