<?
	Class Exceldoer{

		private $registry;
		private $styles;


		public function __construct($registry){
			$this->registry = $registry;
		}

		public function price_list(){

			$generate_date = date('d.m.Y',time());

			$xls = new PHPExcel();

				$this->styles = array(
					'parent_h' => array(
						'font' => array('bold' => true, 'size' => 16, 'underline' => PHPExcel_Style_Font::UNDERLINE_SINGLE, 'color' => array('rgb' => '4f81bd')),
						'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
					),
					'date' => array(
						'font' => array('size' => 7, 'color' => array('rgb' => '7f7f7f')),
						'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
					),
					'level_h' => array(
						'font' => array('bold' => true, 'size' => 13, 'underline' => PHPExcel_Style_Font::UNDERLINE_SINGLE, 'color' => array('rgb' => '4f81bd')),
						'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
					),
					'goods_block_h' => array(
						'font' => array('bold' => true),
		 				'fill' => array(
		                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
		                    'color' => array('rgb'=>'f2f2f2')
		                ),
		                'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
					),'goods_n' => array(
						'font' => array('underline' => PHPExcel_Style_Font::UNDERLINE_SINGLE, 'color' => array('rgb' => '4f81bd'))
					),
					'new' => array(
						'font' => array('italic' => true, 'bold' => true)
					),
					'present' => array(
						0 => array(
							'font' => array('bold' => true, 'color' => array('rgb' => 'ff0000'))
						),
						1 => array(
							'font' => array('bold' => true, 'color' => array('rgb' => '75923c'))
						)
					),
					'goods_block_values' => array(
						'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
					),
					'goods_block_overall' => array(
						'borders' => array(
							'allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN , 'color' => array('rgb' => '000000'))
						)
					),
					'common' => array(
						'alignment' => array('vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER )
					),
					'old_price' => array(
						'font' => array('color' => array('rgb' => 'cccccc'))
					),
				);

			$xls->getProperties()->setCreator('Bodybuilding Shop')
										 ->setLastModifiedBy('Bodybuilding Shop')
										 ->setTitle('Прайс лист от '.$generate_date)
										 ->setSubject('Прайс лист от '.$generate_date)
										 ->setDescription('Прайс лист от '.$generate_date)
										 ->setKeywords('Прайс лист от '.$generate_date)
										 ->setCategory('Прайс лист от '.$generate_date);

			$xls->getDefaultStyle()->getFont()->setName('Verdana');
			$xls->getDefaultStyle()->getFont()->setSize(8);

			$xls->removeSheetByIndex(0);

			$goods_global_arr = array();
			$qLnk = mysql_query("
								SELECT
									goods.name AS goods_name,
									goods.alias AS goods_alias,
									goods.price_1 AS price_1,
									goods.price_2 AS price_2,
									goods.packing AS packing,
									goods.new AS new,
									goods.personal_discount AS personal_discount,
									goods.present AS present,
									levels.name AS level_name,
									levels.alias AS level_alias,
									parent_tbl.name AS parent_name,
									parent_tbl.alias AS parent_alias,
									growers.name AS grower
								FROM
									goods
								INNER JOIN levels ON levels.id = goods.level_id
								INNER JOIN levels AS parent_tbl ON parent_tbl.id = levels.parent_id
								LEFT OUTER JOIN growers ON growers.id = goods.grower_id
								WHERE
									goods.published = 1
								ORDER BY
									parent_tbl.sort ASC,
									levels.sort ASC,
									goods.sort ASC;
								");

			while($g = mysql_fetch_assoc($qLnk)){
				$goods_global_arr[$g['parent_alias']]['name'] = $g['parent_name'];
				$goods_global_arr[$g['parent_alias']]['levels'][$g['level_alias']]['name'] = $g['level_name'];
				$goods_global_arr[$g['parent_alias']]['levels'][$g['level_alias']]['goods'][$g['goods_alias']] = $g;
			}

			$i = 0;
			foreach($goods_global_arr as $parent_alias => $parent_arr){

				$url_1 = THIS_URL.$parent_alias.'/';

				$sheet = $xls->createSheet();
					$sheet->setTitle($parent_arr['name']);
					$xls->setActiveSheetIndex($i);

				$j = 1;

				$xls->getActiveSheet()->setCellValueByColumnAndRow(0, $j, $parent_arr['name']);
					$xls->getActiveSheet()->getCellByColumnAndRow(0, $j)->getHyperlink()->setUrl($url_1);
						$xls->getActiveSheet()->getStyle('A'.$j)->applyFromArray($this->styles['parent_h']);
							$xls->getActiveSheet()->mergeCells('A'.$j.':G'.$j);
								$xls->getActiveSheet()->getRowDimension($j)->setRowHeight(30);
				$j++;

				$xls->getActiveSheet()->setCellValueByColumnAndRow(0, $j, 'Дата прайслиста: '.$generate_date);
					$xls->getActiveSheet()->getStyle('A'.$j)->applyFromArray($this->styles['date']);
						$xls->getActiveSheet()->mergeCells('A'.$j.':G'.$j);
							$xls->getActiveSheet()->getRowDimension($j)->setRowHeight(17);
				$j++;$j++;

				foreach($parent_arr['levels'] as $level_alias => $level_arr){

					$url_2 = $url_1.$level_alias.'/';

					$xls->getActiveSheet()->setCellValueByColumnAndRow(0, $j, $level_arr['name']);
						$xls->getActiveSheet()->getCellByColumnAndRow(0, $j)->getHyperlink()->setUrl($url_2);
							$xls->getActiveSheet()->getStyle('A'.$j)->applyFromArray($this->styles['level_h']);
								$xls->getActiveSheet()->mergeCells('A'.$j.':G'.$j);
									$xls->getActiveSheet()->getRowDimension($j)->setRowHeight(22);
					$j++;

					$block_goods_start = $j;

						$this->goods_block_head($xls,$j);
						$j++;

						foreach($level_arr['goods'] as $goods_alias => $goods_arr){

							$url_3 = $url_2.$goods_alias.'/';

							$goods_full_name = ($goods_arr['grower']!='') ? '«'.$goods_arr['grower'].'». '.$goods_arr['goods_name'] : $goods_arr['goods_name'];
							$xls->getActiveSheet()->setCellValueByColumnAndRow(0, $j, $goods_full_name);
								$xls->getActiveSheet()->getCellByColumnAndRow(0, $j)->getHyperlink()->setUrl($url_3);
									$xls->getActiveSheet()->getStyle('A'.$j)->applyFromArray($this->styles['goods_n']);

							$personal_discount = ($goods_arr['personal_discount']>0) ? $goods_arr['personal_discount'].' %' : '';
							$xls->getActiveSheet()->setCellValueByColumnAndRow(1, $j, $personal_discount);

							$xls->getActiveSheet()->setCellValueByColumnAndRow(2, $j, $goods_arr['packing']);


							$price_1 = ($goods_arr['personal_discount']>0) ? $goods_arr['price_1'] - $goods_arr['price_1']*$goods_arr['personal_discount']/100 : $goods_arr['price_1'];
							$xls->getActiveSheet()->setCellValueByColumnAndRow(3, $j, Common_Useful::price2read($goods_arr['price_1']));


							$price_2 = ($goods_arr['personal_discount']>0) ? $goods_arr['price_2'] - $goods_arr['price_2']*$goods_arr['personal_discount']/100 : $goods_arr['price_2'];
							$xls->getActiveSheet()->setCellValueByColumnAndRow(4, $j, Common_Useful::price2read($goods_arr['price_2']));

							if($goods_arr['personal_discount']>0){
								$xls->getActiveSheet()->setCellValueByColumnAndRow(3, $j+1, Common_Useful::price2read($price_1));
								$xls->getActiveSheet()->setCellValueByColumnAndRow(4, $j+1, Common_Useful::price2read($price_2));
							}

							$new_cpt = ($goods_arr['new']==1) ? 'новый' : '';
							$xls->getActiveSheet()->setCellValueByColumnAndRow(5, $j, $new_cpt);
								$xls->getActiveSheet()->getStyle('F'.$j)->applyFromArray($this->styles['new']);

							$present_cpt = ($goods_arr['present']==1) ? 'да' : 'нет';
							$xls->getActiveSheet()->setCellValueByColumnAndRow(6, $j, $present_cpt);
								$xls->getActiveSheet()->getStyle('G'.$j)->applyFromArray($this->styles['present'][$goods_arr['present']]);

							$j++;
							if($goods_arr['personal_discount']>0){
								$merge_start = $j-1;
								$merge_fin = $j;
								$xls->getActiveSheet()->mergeCells('A'.$merge_start.':A'.$merge_fin);
								$xls->getActiveSheet()->mergeCells('B'.$merge_start.':B'.$merge_fin);
								$xls->getActiveSheet()->mergeCells('C'.$merge_start.':C'.$merge_fin);
								$xls->getActiveSheet()->mergeCells('F'.$merge_start.':F'.$merge_fin);
								$xls->getActiveSheet()->mergeCells('G'.$merge_start.':G'.$merge_fin);

								$xls->getActiveSheet()->getStyle('D'.$merge_start)->applyFromArray($this->styles['old_price']);
								$xls->getActiveSheet()->getStyle('E'.$merge_start)->applyFromArray($this->styles['old_price']);

								$j++;
							}
						}

					$block_goods_fin = $j-1;
					$xls->getActiveSheet()->getStyle('B'.$block_goods_start.':G'.$block_goods_fin)->applyFromArray($this->styles['goods_block_values']);
					$xls->getActiveSheet()->getStyle('A'.$block_goods_start.':G'.$block_goods_fin)->applyFromArray($this->styles['goods_block_overall']);
					for($k=$block_goods_start;$k<=$block_goods_fin;$k++){
						$xls->getActiveSheet()->getRowDimension($k)->setRowHeight(16);
					}

					$j++;$j++;
				}

				$xls->getActiveSheet()->getStyle('A1:G'.$j)->applyFromArray($this->styles['common']);

				$xls->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
				$xls->getActiveSheet()->getColumnDimension('B')->setWidth(10);
				$xls->getActiveSheet()->getColumnDimension('C')->setWidth(13);
				$xls->getActiveSheet()->getColumnDimension('D')->setWidth(12);
				$xls->getActiveSheet()->getColumnDimension('E')->setWidth(12);
				$xls->getActiveSheet()->getColumnDimension('F')->setWidth(10);
				$xls->getActiveSheet()->getColumnDimension('G')->setWidth(13);

				$i++;
			}

			$xls->setActiveSheetIndex(0);

			$writer = PHPExcel_IOFactory::createWriter($xls, 'Excel5');
			$writer->save('sportivnoe-pitanie-price.xls');

		}

		private function goods_block_head($xls,$j){
			$xls->getActiveSheet()->setCellValueByColumnAndRow(0, $j, 'Название');
			$xls->getActiveSheet()->setCellValueByColumnAndRow(1, $j, 'Скидка');
			$xls->getActiveSheet()->setCellValueByColumnAndRow(2, $j, 'Упаковка');
			$xls->getActiveSheet()->setCellValueByColumnAndRow(3, $j, 'Цена п/п');
			$xls->getActiveSheet()->setCellValueByColumnAndRow(4, $j, 'Цена н/п');
			$xls->getActiveSheet()->setCellValueByColumnAndRow(5, $j, 'Новый');
			$xls->getActiveSheet()->setCellValueByColumnAndRow(6, $j, 'В продаже');

			$xls->getActiveSheet()->getStyle('A'.$j.':G'.$j)->applyFromArray($this->styles['goods_block_h']);

		}

	}
?>