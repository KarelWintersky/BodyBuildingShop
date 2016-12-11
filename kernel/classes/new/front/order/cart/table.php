<?php
Class Front_Order_Cart_Table Extends Common_Rq{

	private $registry;
		
	public function __construct($registry){
		$this->registry = $registry;
	}	
		
	private function print_lines($data,$readonly,&$ostatkiDontMatch){
		$ostatkiDontMatch = false;

		$html = array();
				
		$goods = $data['goods'];
		
		if($readonly && $data['gift']) $goods[] = $data['gift'];

		$ostatki = $this->getOstatki($goods);

		foreach($goods as $key => $g){
			$g['url'] = (isset($g['parent_alias']))
				? sprintf('/%s/%s/%s/',
						$g['parent_alias'],
						$g['level_alias'],
						$g['alias']
						)
				: false;
			$g['name_print'] = (isset($g['grower_id']) && $g['grower_id'])
				? sprintf('«%s». %s',$g['grower_name'],$g['name'])
				: $g['name'];
			
			$g['features_colors'] = $this->features_colors($g);
			
			$g['key'] = $key;
			
			$g['readonly'] = $readonly;

			if(isset($ostatki[$g['barcode']]) && $ostatki[$g['barcode']] < $g['amount']){
				$g['ostatok'] = $ostatki[$g['barcode']];

				$ostatkiDontMatch = true;
			}else{
				$g['ostatok'] = false;
			}

			$html[] = $this->do_rq('line',$g,true);
		}
				
		return implode('',$html);
	}

	private function getOstatki($goods){
		$barcodes = array();
p($goods);
		foreach($goods as $goodsItem){
			$barcodes[] = sprintf("'%s'", $goodsItem['barcode']);
		}

		if(!count($barcodes)){
			return array();
		}

		$ostatki = array();
		$qLnk = mysql_query(sprintf("
			SELECT
				barcode,
				value
			FROM
				ostatki
			WHERE
				barcode IN (%s)
			",
				implode(', ', $barcodes)
				));
		while($row = mysql_fetch_assoc($qLnk)){
			$ostatki[$row['barcode']] = $row['value'];
		}

		return $ostatki;
	}

	private function features_colors($g){
		$feature = (isset($g['feature'])) ? $g['feature'] : false;
		$color = (isset($g['color'])) ? $g['color'] : false;
		
		if(!$feature && !$color) return false;
		
		$label = ($g['root_id']==4) ? 'Размер' : 'Вкус';

		$string = array();
			$string[] = (strpos($feature,$label)===false) 
				? sprintf('%s: %s',$label,$feature)
				: $feature;
				
		if(isset($color) && $color) $string[] = sprintf('цвет: %s',$g['color_name']);
		
		return implode(', ',$string);
	}
	
	public function do_table($data,$readonly = false,&$ostatkiDontMatch){
		
		$a = array(
				'lines' => $this->print_lines($data,$readonly,$ostatkiDontMatch),
				'readonly' => $readonly
				);
		
		return $this->do_rq('table',$a);
	}
	
}
?>