<?
	Class Front_Catalog_Barcodes{

		private $registry;

		public function __construct($registry){
			$this->registry = $registry;
			$this->registry->set('CL_catalog_barcodes',$this);
		}

		public function item_rq($name,$a = NULL){
			require($this->registry['template']->TF.'item/catalog/barcodes/'.$name.'.html');
		}

		public function barcodes_avialable_check($goods_id){
			$qLnk = mysql_query(sprintf("SELECT COUNT(*) FROM goods_barcodes WHERE goods_id = '%d' AND present = 1;",$goods_id));
			return (mysql_result($qLnk,0)>0) ? true : false;
		}

		public function features_list($g){
			$features = array();
			$qLnk = mysql_query(sprintf("SELECT DISTINCT feature FROM goods_barcodes WHERE goods_id = '%d' ORDER BY sort ASC;",$g['id']));
			while($f = mysql_fetch_assoc($qLnk)) $features[] = $f['feature'];

			$count = count($features);
			$i=1;
			foreach($features as $key => $val){
				if($i>3) unset($features[$key]);
				$i++;
			}
			
			$string = implode(', ',$features);
			if(count($features)!=$count) $string.=sprintf('<a class="feats_list_more" href="/%s/%s/%s/">...</a>',
				$g['parent_level_alias'],
				$g['level_alias'],
				$g['alias']
				);

			return $string;
		}

		public function packs_list($g){
			$packs = array();
			$qLnk = mysql_query(sprintf("SELECT DISTINCT packing FROM goods_barcodes WHERE goods_id = '%d' ORDER BY sort ASC;",$g['id']));
			while($p = mysql_fetch_assoc($qLnk)) $packs[] = $p['packing'];

			$count = count($packs);
			$i=1;
			foreach($packs as $key => $val){
				if($i>4) unset($packs[$key]);
				$i++;
			}

			$string = implode(', ',$packs);
			if(count($packs)!=$count) $string.=sprintf('<a class="feats_list_more" href="/%s/%s/%s/">...</a>',
				$g['parent_level_alias'],
				$g['level_alias'],
				$g['alias']
				);

			return $string;
		}

		public function order_barcodes(){
			$barcodes = array();
			$qLnk = mysql_query(sprintf("SELECT * FROM goods_barcodes WHERE goods_id = '%d' ORDER BY sort ASC;",$this->registry['goods']['id']));
			while($b = mysql_fetch_assoc($qLnk)){				
				$barcodes[$b['packing']][] = $b;
			}
			
			ob_start();
			$count = count($barcodes);
			$i = 1;
			foreach($barcodes as $packing => $lines){

				$a = array(
					'packing' => $packing,
					'price' => $this->get_price($lines),
					'tastes' => $this->tastes_options($lines,$all_disabled),
					'first_barcode' => $lines[0]['barcode'],
					'all_disabled' => $all_disabled
					);
				$a['classes'] = $this->implode_classes($i,$count,$a);
				$a['colours'] = $this->do_colours($this->registry['goods']['id']);
				
				$this->item_rq('order',$a);

				$i++;
			}
			$html = ob_get_clean();
			
			return $html;
		}

		private function implode_classes($i,$count,$a){
			$classes = array();
			
 			if($i==$count) $classes[] = 'last';
 			if($a['tastes']===false) $classes[] = 'no_tastes';
			
			return implode(' ',$classes); 
		}
		
		private function get_price($lines){
			$l = end($lines);

			return $l['price'];
		}

		private function tastes_options($lines,&$all_disabled){
			$all_disabled = true;
			$options = array();
			$no_tastes = true;
			
			$output = array();
			foreach($lines as $key => $val){
				if($val['present']==1){
					$output[$key] = $val;
					$all_disabled = false;
				}
			}
			foreach($lines as $key => $val) if($val['present']==0) $output[$key] = $val;
			
			foreach($output as $t){
				$options[] = sprintf('<option value="%s" %s>%s</option>',
						$t['barcode'],
						($t['present']==0) ? 'disabled' : '',
						$t['feature']);
				if($t['feature']!='') $no_tastes = false;
			}
				
			return (!$no_tastes) ? implode('',$options) : false;
		}

		public function do_colours($goods_id){
			if(!isset($this->registry['feats_arr'][$goods_id])) return false;
			
			$options = array();
			
			foreach($this->registry['feats_arr'][$goods_id] as $group_id => $arr){
				foreach($arr['feats'] as $id => $data){
					$options[] = sprintf('<option value="%s">%s</option>',$id,$data['name']);						
				}
			}
			
			return implode('',$options);
		}
		
		public function min_price($g){
			$prices = array();
			$qLnk = mysql_query(sprintf("
					SELECT 
						price
					FROM 
						goods_barcodes 
					WHERE 
						goods_id = '%d' 
					GROUP BY packing",$g['id']));
			while($b = mysql_fetch_assoc($qLnk)){
				$prices[] = $b['price'];
			}			
			
			if(count($prices)==0) return '';
			elseif(count($prices)==1)
				return sprintf('<div><span>%s</span> руб.</div>',
						Common_Useful::price2read($prices[0])
				);
			else
				return sprintf('<div>от <span>%s</span> руб.</div>',
						Common_Useful::price2read(min($prices))
						);
			
		}
		
	}
?>