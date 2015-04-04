<?
	Class f_Photogallery{
		
		private $registry;
		
		public function pgc(){}
		
		public function __construct($registry){
			$this->registry = $registry;						
			$this->registry->set('f_photogallery',$this);
		}

		public function item_rq($name,$a = NULL){
			require($this->registry['template']->TF.'item/photogallery/'.$name.'.html');
		}			
		
		public function path_check(){
			
			$this->registry['f_404'] = false;
			$path_arr = $this->registry['route_path'];
									
			if(count($path_arr)==0){
				$this->registry['template']->add2crumbs('photogallery','Фотогалерея одежды');
				$this->registry['template']->set('c','photogallery/index');
				$this->registry['longtitle'] = 'Фотогалерея одежды';
								
				return true;
			}
			
			$this->registry['f_404'] = true;
			return false;
		}
			
		public function get_gallery_list(){
			$name_etal = '';
			$qLnk = mysql_query("
								SELECT
									goods.id,
									goods.name,
									goods.alias,
									levels.alias AS level_alias,
									parent_levels.alias AS parent_alias,
									goods_photo.alias AS avatar,
									goods_photo.alt AS alt
								FROM
									goods
								LEFT OUTER JOIN levels ON levels.id = goods.level_id
								LEFT OUTER JOIN levels AS parent_levels ON parent_levels.id = levels.parent_id
								LEFT OUTER JOIN goods_photo ON goods_photo.id = goods.avatar_id
								WHERE
									parent_levels.id = ".CLOTH_PARENT_LEVEL_ID."
									AND
									goods.published = 1
									AND
									goods.parent_barcode = '0'
								ORDER BY
									levels.sort ASC,
									goods.sort ASC;
								");
			$i = 1;
			while($g = mysql_fetch_assoc($qLnk)){
				$g['class'] = ($i%4==0) ? 'r' : '';
				$g['next_line'] = ($i%4==0) ? true : false;
						
				$name_pure_arr = explode(',',$g['name']);
					$name_pure = trim($name_pure_arr[0]);		
									
				if($name_etal!=$name_pure){ //выводим только один товар, следующий подряд с одинаковым названием (до размера)
					$this->item_rq('photogallery_item',$g);	
					$i++;
				}
								
				$name_etal = $name_pure;
			}
			
		}
			
	}
?>