<?
	Class f_Catalog{

		private $registry;
		public $goods_sort_values;
		public $goods_display_number;
		public $goods_display_type;

		private $Front_Catalog_Barcodes;
		private $Front_Catalog_Goods_List_Sort;

		public function __construct($registry){
			$this->registry = $registry;
			$this->registry->set('CL_catalog',$this);

			$this->Front_Catalog_Barcodes = new Front_Catalog_Barcodes($this->registry);
			$this->Front_Catalog_Goods_List_Sort = new Front_Catalog_Goods_List_Sort($this->registry);

			$this->goods_display_number = array(
				'goods_list' => array(
						'10' => array('name' => '10 на стр.', 'active' => 0),
						'20' => array('name' => '20 на стр.', 'active' => 1),
						'50' => array('name' => '50 на стр.', 'active' => 0),
						'all' => array('name' => 'все', 'active' => 0),
					),
				'goods_list_table' => array(
						'50' => array('name' => '50 на стр.', 'active' => 0),
						'all' => array('name' => 'все', 'active' => 1),
					)
			);

			$this->goods_display_type = array(
				'goods_list' => array('name' => 'Подробно', 'active' => 1),
				'goods_list_table' => array('name' => 'Списком', 'active' => 0)
			);

			$this->registry['CL_css']->set(array(
					'catalog',
			));			
			
		}

		public function redirect_check(){
			$path_arr = $this->registry['route_path'];
			
			if(count($path_arr)==2 && $path_arr[0]=='shop'){
				$code_arr = explode('.html',$path_arr[1]);
				if(count($code_arr)==2 && $code_arr[1]==''){
					$code_arr = explode('shop-',$code_arr[0]);
					if(count($code_arr)==2 && $code_arr[0]==''){
						$code = $code_arr[1];

						$qLnk = mysql_query("
											SELECT
												goods.alias,
												levels.alias AS level_alias,
												parent_tbl.alias AS parent_level_alias
											FROM
												goods
												LEFT OUTER JOIN levels ON levels.id = goods.level_id
												LEFT OUTER JOIN levels AS parent_tbl ON parent_tbl.id = levels.parent_id
											WHERE
												goods.barcode = '".$code."'
											LIMIT 1;
											");
						if(mysql_num_rows($qLnk)>0){
							$g = mysql_fetch_assoc($qLnk);
							$link = '/'.$g['parent_level_alias'].'/'.$g['level_alias'].'/'.$g['alias'].'/';

							header('HTTP/1.1 301 Moved Permanently');
							header('Location: '.$link);
							exit();
						}
					}
				}
			}
			return false;
		}

		public function path_check(){

			$this->registry['f_404'] = false;
			$path_arr = $this->registry['route_path'];

			if(count($path_arr)==1 && $this->check_parent_level($path_arr[0])){
				$this->registry['template']->set('c','catalog/parent_level');
				return true;
			}elseif(count($path_arr)==2 && $this->check_level($path_arr[0],$path_arr[1])){
				$this->registry['template']->set('c','catalog/level');
				
				$Front_Catalog_Levels = new Front_Catalog_Levels($this->registry);
				$Front_Catalog_Levels->do_vars();
				
				return true;
			}elseif(count($path_arr)==3 && $this->check_level($path_arr[0],$path_arr[1]) && $this->check_goods($path_arr[2],$path_arr[0],$path_arr[1])){
				$this->registry['template']->set('c','catalog/goods');
				return true;
			}

			$this->registry['f_404'] = true;
			return false;
		}

		public function item_rq($name,$a = NULL){
			require($this->registry['template']->TF.'item/catalog/'.$name.'.html');
		}

		private function check_goods($alias,$parent_alias,$level_alias){
			
			$path = explode('?',$_SERVER['REQUEST_URI']);
			if(count($path)>1){
				header('HTTP/1.1 301 Moved Permanently');
				header('Location: '.$path[0]);
				exit();				
			}
			
			$qLnk = mysql_query("
								SELECT
									goods.*,
									(goods.personal_discount + ".OVERALL_DISCOUNT.") AS personal_discount,
									growers.name AS grower,
									goods_photo.alias AS avatar,
									parent_levels.id AS parent_id
								FROM
									goods
								LEFT OUTER JOIN growers ON growers.id = goods.grower_id
								LEFT OUTER JOIN goods_photo ON goods_photo.id = goods.avatar_id
								INNER JOIN levels ON levels.id = goods.level_id
								INNER JOIN levels AS parent_levels ON parent_levels.id = levels.parent_id
								WHERE
									goods.alias = '".$alias."'
									AND
									levels.alias = '".$level_alias."'
									AND
									parent_levels.alias = '".$parent_alias."'
								LIMIT 1;
								");
			if(mysql_num_rows($qLnk)>0){
				$g = mysql_fetch_assoc($qLnk);
				
				$this->registry['CL_css']->set(array(
					'shadowbox'
				));				
				$this->registry['CL_js']->set(array(
					'goods',
					'lib/shadowbox'
				));				
				
				$g['canonical'] = $this->get_canonical($g['parent_barcode'],$g['barcode']);

				$g['price_1_n'] = $g['price_1'] - $g['price_1']*$g['personal_discount']/100;
				$g['price_2_n'] = $g['price_2'] - $g['price_2']*$g['personal_discount']/100;

				$this->registry['goods'] = $g;

				$this->registry['template']->add2crumbs($this->registry['goods']['alias'],$this->registry['goods']['name']);

				$this->registry['longtitle'] = $this->registry['goods']['seo_title'];
				$this->registry['seo_kw'] = $this->registry['goods']['seo_kw'];
				$this->registry['seo_dsc'] = $this->registry['goods']['seo_dsc'];

				$this->page_goods_feats();

				return true;
			}
			return false;
		}

		private function get_canonical($parent_barcode,$this_barcode){
			if($parent_barcode==$this_barcode) return false;

			if($parent_barcode>0){
				$qLnk = mysql_query(sprintf("
								SELECT
									goods.alias,
									levels.alias AS level_alias,
									parent_levels.alias AS parent_alias
								FROM
									goods
								INNER JOIN levels ON levels.id = goods.level_id
								INNER JOIN levels AS parent_levels ON parent_levels.id = levels.parent_id
								WHERE
									goods.barcode = '%s'
								LIMIT 1;
					",$parent_barcode));
				$goods = mysql_fetch_assoc($qLnk);
				if($goods){
					if(!$goods['alias']) return false;
					
					$url = '/'.$goods['parent_alias'].'/'.$goods['level_alias'].'/'.$goods['alias'].'/';
					
					header('HTTP/1.1 301 Moved Permanently');
					header('Location: '.$url);
					exit();					
				}
			}

			return false;
		}

		private function check_parent_level($parent_alias){
			$qLnk = mysql_query("
								SELECT
									levels.*
								FROM
									levels
								WHERE
									levels.alias = '".$parent_alias."'
									AND
									levels.parent_id = 0
								LIMIT 1;
								");
			if(mysql_num_rows($qLnk)>0){
				$level = mysql_fetch_assoc($qLnk);
				$level['level_link'] = '/'.$level['alias'].'/';
				$this->registry['level'] = $level;

				$this->registry->set('longtitle',$this->registry['level']['seo_title']);
				$this->registry['seo_kw'] = $this->registry['level']['seo_kw'];
				$this->registry['seo_dsc'] = $this->registry['level']['seo_dsc'];


				$this->registry['template']->add2crumbs($this->registry['level']['alias'],$this->registry['level']['name']);

				return true;
			}
			return false;
		}

		private function check_level($parent_alias,$level_alias){
			$qLnk = mysql_query("
								SELECT
									levels.*,
									parent_tbl.alias AS parent_alias,
									parent_tbl.name AS parent_name
								FROM
									levels
								INNER JOIN levels AS parent_tbl ON parent_tbl.id = levels.parent_id
								WHERE
									levels.alias = '".$level_alias."'
									AND
									parent_tbl.alias = '".$parent_alias."'
								LIMIT 1;
								");
			if(mysql_num_rows($qLnk)>0){
				$level = mysql_fetch_assoc($qLnk);
				$level['level_link'] = '/'.$level['parent_alias'].'/'.$level['alias'].'/';
				$this->registry['level'] = $level;

				$longtitle = isset($_GET['page']) ? $this->registry['level']['seo_title'].' - страница '.$_GET['page'] : $this->registry['level']['seo_title'];
					$this->registry->set('longtitle',$longtitle);
				$this->registry['seo_kw'] = $this->registry['level']['seo_kw'];
				$this->registry['seo_dsc'] = $this->registry['level']['seo_dsc'];
				$this->registry['level_name'] = $this->registry['level']['parent_name'].', '.$this->registry['level']['name'];


				$this->registry['template']->add2crumbs($this->registry['level']['parent_alias'],$this->registry['level']['parent_name']);
				$this->registry['template']->add2crumbs($this->registry['level']['alias'],$this->registry['level']['name']);

				$this->registry['cookie_type'] = 'catalog';

				return true;
			}
			return false;
		}

		public function print_goods_display_type(){
			foreach($this->goods_display_type as $id => $arr){
				$active = ($arr['active']==1) ? 'active' : '';
				$onclick = ($arr['active']!=1) ? 'display_type_change(this);' : '';
				echo '<li id="'.$id.'" class="'.$active.'" onclick="'.$onclick.'">'.$arr['name'].'</li>';
			}
		}

		private function goods_list_pagination(&$list_params,$reqiure_file,$from){

			$type = ($from==0) ? 'level' : (($from==1) ? 'grower' : 'popular');

			foreach($this->goods_display_number[$reqiure_file] as $display => $arr){
					if($arr['active']==1){$PAGING = $display;}
				}

			if(isset($_COOKIE[$this->registry['cookie_type']]['display_number'][$reqiure_file][$this->registry[$type]['id']])){
				$display = $_COOKIE[$this->registry['cookie_type']]['display_number'][$reqiure_file][$this->registry[$type]['id']];
				if(in_array($display,array_keys($this->registry['f_catalog']->goods_display_number[$reqiure_file]))){
					$this->registry['f_catalog']->goods_display_number[$reqiure_file] = $this->trunc_active($this->registry['f_catalog']->goods_display_number[$reqiure_file]);
					$this->registry['f_catalog']->goods_display_number[$reqiure_file][$display]['active'] = 1;

					$PAGING = $display;
				}
			}

			$list_params['display'] = $PAGING;

			if($PAGING=='all' && $from!=2){
				$list_params['start'] = 1;
				$list_params['fin'] = NULL;
				return "";
			}elseif($PAGING=='all' && $from==2){
				$list_params['start'] = 1;
				$list_params['fin'] = POPULAR_MAX;
				return "LIMIT ".POPULAR_MAX;
			}

        	$page = (isset($_GET['page'])) ? $_GET['page'] : 1;
        	$offset = $PAGING*($page-1);

        	$list_params['start'] = $offset+1;
        	$list_params['fin'] = $offset+$PAGING;

        	return "LIMIT ".$offset.", ".$PAGING;
		}

		private function trunc_active($arr){
			foreach($arr as $id => $a){
				$arr[$id]['active'] = 0;
			}
			return $arr;
		}		
		
		private function get_display_type($from){

			$type = ($from==0) ? 'level' : (($from==1) ? 'grower' : 'popular');

			if(isset($_COOKIE[$this->registry['cookie_type']]['display_type'][$this->registry[$type]['id']])){
				$display_type = $_COOKIE[$this->registry['cookie_type']]['display_type'][$this->registry[$type]['id']];
				if(in_array($display_type,array_keys($this->registry['f_catalog']->goods_display_type))){
					$this->registry['f_catalog']->goods_display_type = $this->trunc_active($this->registry['f_catalog']->goods_display_type);
					$this->registry['f_catalog']->goods_display_type[$display_type]['active'] = 1;

					return $display_type;
				}
			}

			return 'goods_list';
		}

		private function mk_avatar($g){				
			$qLnk = mysql_query(sprintf("
					SELECT
						alias,
						goods_id,
						alt
					FROM
						goods_photo
					WHERE
						id = '%d' 
					",$g['avatar_id']));
			$photo = mysql_fetch_assoc($qLnk);
			if(!$photo) return false;
			
			return sprintf('<img src="/data/foto/goods/122x122/%d/%s" alt="%s">',
					$photo['goods_id'],
					$photo['alias'],
					htmlspecialchars($photo['alt'])
					);
		}
		
		public function goods_list(&$list_html,&$list_params,&$reqiure_file,$from){
			
			if($from==0){//level
				$q_where = "goods.level_id = '".$this->registry['level']['id']."' AND";
			}elseif($from==1){//grower
				$q_where = "goods.grower_id = '".$this->registry['grower']['id']."' AND";
			}elseif($from==2){//popular
				$q_where = "goods.price_1 > 100 AND";
			}

			$reqiure_file = $this->get_display_type($from);

			$result_arr = array();

			$result_ids = array();
			$feats_arr = array();

			$qLnk = mysql_query(sprintf("
								SELECT SQL_CALC_FOUND_ROWS
									goods.avatar_id,
									goods.id AS id,
									goods.name AS name,
									goods.alias AS alias,
									goods.introtext AS introtext,
									goods.packing AS packing,
									goods.price_1,
									goods.price_2,
									(goods.personal_discount + %s) AS personal_discount,
									goods.sort AS sort,
									goods.new AS new,
									goods.present AS present,
									goods.grower_id AS grower_id,
									growers.name AS grower,
									levels.name AS level_name,
									levels.alias AS level_alias,
									parent_levels.id AS parent_level_id,
									parent_levels.name AS parent_level_name,
									parent_levels.alias AS parent_level_alias
								FROM
									goods
								LEFT OUTER JOIN growers ON growers.id = goods.grower_id
								LEFT OUTER JOIN levels ON levels.id = goods.level_id
								LEFT OUTER JOIN levels AS parent_levels ON parent_levels.id = levels.parent_id
								WHERE
									%s
									goods.published = 1
									AND
									goods.parent_barcode = 0
									AND
									goods.weight > 0
								ORDER BY
									parent_levels.sort ASC, 
									levels.sort ASC,
									%s,
									seo_h1 ASC
								%s;
								",
								OVERALL_DISCOUNT,
								$q_where,
								$this->Front_Catalog_Goods_List_Sort->get_sort($from),
								$this->goods_list_pagination($list_params,$reqiure_file,$from)
								));
			
       		$qAmount = mysql_query("SELECT FOUND_ROWS();");
       		$total_goods_amount = mysql_result($qAmount,0);
        	$list_params['total_goods_amount'] = ($from==2 && POPULAR_MAX<$total_goods_amount) ? POPULAR_MAX : $total_goods_amount;
			$list_params['fin'] = ($list_params['fin']>$list_params['total_goods_amount'] || is_null($list_params['fin'])) ? $list_params['total_goods_amount'] : $list_params['fin'];

			if($list_params['total_goods_amount']>0):

				while($g = mysql_fetch_assoc($qLnk)){
					$result_ids[] = $g['id'];
					$result_arr[] = $g;
				}

				if(count($result_ids)==0) return false;
				
				$qLnk = mysql_query("
									SELECT
										goods_features.goods_id,
										goods_features.feature_id,
										features.name AS feature_name,
										feature_groups.id AS group_id,
										feature_groups.name AS group_name
									FROM
										goods_features
									INNER JOIN features ON features.id = goods_features.feature_id
									INNER JOIN feature_groups ON feature_groups.id = features.group_id
									WHERE
										goods_features.goods_id IN (".implode(',',$result_ids).")
									ORDER BY
										goods_features.goods_id ASC,
										feature_groups.name ASC,
										features.sort ASC;
									");
				while($f = mysql_fetch_assoc($qLnk)){
					$feats_arr[$f['goods_id']][$f['group_id']]['name'] = $f['group_name'];
					$feats_arr[$f['goods_id']][$f['group_id']]['feats'][$f['feature_id']]['name'] = $f['feature_name'];
					$feats_arr[$f['goods_id']][$f['group_id']]['feats'][$f['feature_id']]['active'] = 0;
				}

				$this->registry['feats_arr'] = $feats_arr;

				$grower_id_criteria = 0;
				$no_tastes = true;
				foreach($result_arr as $key => $g){

					$g['avatar'] = $this->mk_avatar($g);
					
					if($g['personal_discount']>0){
						$g['price_1_n'] = $g['price_1'] - $g['price_1']*$g['personal_discount']/100;
						$g['price_2_n'] = $g['price_2'] - $g['price_2']*$g['personal_discount']/100;
					}

					//$g['grower_change'] =( $q_order=='grower ASC' && $grower_id_criteria!=$g['grower_id']) ? true : false;
					$g['grower_change'] =($grower_id_criteria!=$g['grower_id']);

					$g['barcodes_avialable_check'] = $this->Front_Catalog_Barcodes->barcodes_avialable_check($g['id']);
					$g['packs_list'] = $this->Front_Catalog_Barcodes->packs_list($g);
					$g['min_price'] = $this->Front_Catalog_Barcodes->min_price($g);
					$g['packs_list_table'] = $this->packs_list_table($g['packs_list']);
					
					$g['colors_list'] = $this->colors_list($g);
					$g['colors_list_table'] = $this->features_list_table($g['colors_list']);
					
					$g['features_list'] = $this->Front_Catalog_Barcodes->features_list($g);
					$g['features_list_table'] = $this->features_list_table($g['features_list']);

					if($g['features_list_table']!='') $no_tastes = false;
					
					

					$grower_id_criteria = $g['grower_id'];
					
					$result_arr[$key] = $g;
				}

				ob_start();
				foreach($result_arr as $g){
					$g['no_tastes'] = $no_tastes;
					$this->item_rq($reqiure_file,$g);
				}
				$list_html = ob_get_clean();				
				
				if($reqiure_file=='goods_list_table'){
					ob_start();
					$this->item_rq('goods_list_table_th',$g);
					$th = ob_get_contents();
					ob_end_clean();

					$list_html = '<table cellspacing="0" class="goods_item_table">'.$th.$list_html.'</table>';
				}

			endif;

		}

		private function colors_list($g){
			$colors = array();
			$qLnk = mysql_query(sprintf("
					SELECT
						features.name
					FROM
						goods_features
					INNER JOIN features ON features.id = goods_features.feature_id  
					WHERE
						goods_features.goods_id = '%d'
					ORDER BY
						features.sort ASC;
					",$g['id']));
			echo mysql_error();
			while($f = mysql_fetch_assoc($qLnk)) $colors[] = $f['name'];

			$new_colors = array_slice($colors,0,4);
			$string = implode(', ',$new_colors);
			
			if(count($new_colors)!=count($colors)) $string.=sprintf('<a href="/%s/%s/%s/"><img src="/browser/front/i/goods_list_more.jpg"></a>',
				$g['parent_level_alias'],
				$g['level_alias'],
				$g['alias']
				);
			
			return $string; 
		}
		
		private function features_list_table($features_list){
			$str = str_replace(', ','<br>',$features_list);
			$str = str_replace('<a', '<br><a',$str);
				
			return $str;			
		}
		
		private function packs_list_table($packs_list){
			$str = str_replace(', ','<br>',$packs_list);
			$str = str_replace('<a', '<br><a',$str);
			
			return $str;
		}
		
		public function show_pagination($list_params,$from){

			if($from==0){//level
				$page_link = $this->registry['level']['level_link'];
			}elseif($from==1){//grower
				$page_link = $this->registry['grower']['level_link'];
			}elseif($from==2){//popular
				$page_link = '/popular/';
			}

			if($list_params['display']!='all'){
				$pages_amount = ceil($list_params['total_goods_amount']/$list_params['display']);
				$cur_page = (isset($_GET['page'])) ? $_GET['page'] : 1;

				if($pages_amount>1):

					$a['type'] = 1;

					ob_start();
					for($i=1;$i<=$pages_amount;$i++){
						$a['active'] = ($i==$cur_page) ? true : false;
						$a['lnk_param'] = ($i==1) ? '' : '?page='.$i;
						$a['link_href'] = $page_link;
						$a['i'] = $i;
						$this->item_rq('pagination',$a);

						if($i==$cur_page){
							$prev = ($i-1>0) ? (($i-1==1) ? ' ' : '?page='.($i-1)) : false;
							$next = ($i+1<=$pages_amount) ? '?page='.($i+1) : false;
						}

					}
					$html = ob_get_contents();
					ob_end_clean();

					$a['type'] = 2;

					if(isset($next)){
						ob_start();
							$a['link_text'] = '»';
							$a['class'] = 'next';
							$a['link_param'] = $next;
							$a['link_href'] = $page_link;
							$this->item_rq('pagination',$a);
							$next = ob_get_contents();
							ob_end_clean();
					}else $next = false;

					if(isset($prev)){
						ob_start();
							$a['link_text'] = '«';
							$a['class'] = 'prev';
							$a['link_param'] = $prev;
							$a['link_href'] = $page_link;
							$this->item_rq('pagination',$a);
							$prev = ob_get_contents();
							ob_end_clean();
					}else $prev = false;

					if($prev || $next) echo '<ul id="level_pagination">'.$prev.$html.$next.'</ul>';
					
				endif;

			}
		}

		public function goods_feats_first_values($goods_id){
			$string_arr = array();
			if(isset($this->registry['feats_arr'][$goods_id])){
				foreach($this->registry['feats_arr'][$goods_id] as $arr){
					$ids = array_keys($arr['feats']);
					$string_arr[] = array_shift($ids);
				}
			}

			echo implode(',',$string_arr);
		}
	
		public function goods_feats($goods_id,$require_file){
			if(isset($this->registry['feats_arr'][$goods_id])){
				$i=1;
				foreach($this->registry['feats_arr'][$goods_id] as $group_id => $arr){
					$arr['group_id'] = $group_id;
					$arr['goods_id'] = $goods_id;
					$arr['last'] = ($i==count($this->registry['feats_arr'][$goods_id])) ? 'last' : '';
					$this->item_rq($require_file,$arr);

					$i++;
				}
			}
		}

		private function page_goods_feats(){
			$qLnk = mysql_query("
								SELECT
									goods_features.goods_id,
									goods_features.feature_id,
									features.name AS feature_name,
									features.image AS feature_image,
									feature_groups.id AS group_id,
									feature_groups.name AS group_name
								FROM
									goods_features
								INNER JOIN features ON features.id = goods_features.feature_id
								INNER JOIN feature_groups ON feature_groups.id = features.group_id
								WHERE
									goods_features.goods_id = '".$this->registry['goods']['id']."'
								ORDER BY
									goods_features.goods_id ASC,
									feature_groups.name ASC,
									features.sort ASC;
								");
			if(mysql_num_rows($qLnk)>0){
				while($f = mysql_fetch_assoc($qLnk)){
					$feats_arr[$f['goods_id']][$f['group_id']]['name'] = $f['group_name'];
					$feats_arr[$f['goods_id']][$f['group_id']]['feats'][$f['feature_id']]['name'] = $f['feature_name'];
					$feats_arr[$f['goods_id']][$f['group_id']]['feats'][$f['feature_id']]['active'] = 0;
					$feats_arr[$f['goods_id']][$f['group_id']]['feats'][$f['feature_id']]['img'] = $f['feature_image'];
				}

				$this->registry['feats_arr'] = $feats_arr;
			}

		}

		public function print_feats_groups(){
			if(isset($this->registry['feats_arr'][$this->registry['goods']['id']]) && count($this->registry['feats_arr'][$this->registry['goods']['id']])>0){
				foreach($this->registry['feats_arr'][$this->registry['goods']['id']] as $group_id => $group_arr){
					$group_arr['group_id'] = $group_id;
					$this->item_rq('feat_img_group',$group_arr);
				}
			}
		}

		public function print_feats_photo($arr){
			foreach($arr as $feat_id => $feat_arr){
				$this->item_rq('feat_img',$feat_arr);
			}
		}

		public function goods_gallery_matrix($req_file){

			if(!isset($this->registry['goods_photo_matrix'])){
				
				$ids = array($this->registry['goods']['id']);
				$qLnk = mysql_query(sprintf("
						SELECT
							id
						FROM
							goods
						WHERE
							parent_barcode = '%s'
						ORDER BY
							sort ASC;
						",$this->registry['goods']['barcode']));
				while($g = mysql_fetch_assoc($qLnk)) $ids[] = $g['id'];
				
				$PM = array();
				$qLnk = mysql_query(sprintf("
									SELECT
										goods_photo.id,
										goods_photo.goods_id,
										goods_photo.alias,
										goods_photo.alt
									FROM
										goods_photo
									WHERE
										goods_photo.goods_id IN (%s)
									ORDER BY
										goods_photo.sort ASC;
									",
									implode(",",$ids)
									));
				while($f = mysql_fetch_assoc($qLnk)){
					$PM[] = $f;
				}

				$this->registry['goods_photo_matrix'] = $PM;

			}

			if($req_file=='goods_photo' && count($this->registry['goods_photo_matrix'])<=1){return;}

			$i=1;
			foreach($this->registry['goods_photo_matrix'] as $f){
				$f['class'] = ($i%5==0) ? 'r' : '';
				$this->item_rq($req_file,$f);

				$i++;
			}

		}

		public function levels_list(){
			$qLnk = mysql_query("
								SELECT
									levels.id,
									levels.name,
									levels.alias,
									levels.avatar,
									levels.goods_count
								FROM
									levels
								WHERE
									levels.published = 1
									AND
									levels.parent_id = '".$this->registry['level']['id']."'
								ORDER BY
									levels.sort ASC;
								");
			$i = 1;
			while($l = mysql_fetch_assoc($qLnk)){
				$l['class'] = ($i%4==0) ? 'r' : '';
				$this->item_rq('levels_list',$l);

				$i++;
			}
		}

		public function in_the_cart($goods_id,$packing){
			$barcodes = array();
			$qLnk = mysql_query(sprintf("SELECT barcode FROM goods_barcodes WHERE goods_id = '%d'",$goods_id));
			while($b = mysql_fetch_assoc($qLnk)) $barcodes[] = $b['barcode'];
			$counter = 0;
			if(isset($_COOKIE['thecart'])){
				$cart_arr = explode('|',$_COOKIE['thecart']);
				foreach($cart_arr as $goods_string){
					$goods_arr = explode(':',$goods_string);

					if(in_array($goods_arr[0],$barcodes) && $packing==$goods_arr[1]) $counter+=$goods_arr[2];
				}
			}
			echo ($counter>0) ? '<div class="added">'.$counter.'</div>' : '';
		}

		public function cart_construct(){

			$personal_discount = isset($this->registry['userdata']['personal_discount']) ? $this->registry['userdata']['personal_discount'] : 0;

			$goods_ids = array();
			$a['cart_goods_amount'] = 0;
			$a['cart_sum'] = 0;

			if(isset($_COOKIE['thecart']) && $_COOKIE['thecart']!=''){
				$cart_arr = explode('|',$_COOKIE['thecart']);
								
				foreach($cart_arr as $goods_string){
					$goods_arr = explode(':',$goods_string);
					if(count($goods_arr)<3) continue;

					$qLnk = mysql_query(sprintf("SELECT price FROM goods_barcodes WHERE barcode = '%s' AND packing = '%s'",
						$goods_arr[0],$goods_arr[1]
						));
					$price = mysql_fetch_assoc($qLnk);
					if($price){
						$a['cart_sum']+= $price['price']*$goods_arr[2];
						$a['cart_goods_amount']+=$goods_arr[2];
					}
				}

			}

			ob_start();
			$this->item_rq('cart',$a);
			$html = ob_get_contents();
			ob_end_clean();

			echo $html;

		}

		public function prev_next(){

			$sort_items = array(
				'sort|ASC' => array('goods.sort','ASC','DESC',"goods.sort > '".$this->registry['goods']['sort']."'","goods.sort < '".$this->registry['goods']['sort']."'"),
				'name|ASC' => array('goods.name','ASC','DESC',"goods.seo_h1 > '".$this->registry['goods']['seo_h1']."'","goods.seo_h1 < '".$this->registry['goods']['seo_h1']."'"),
				'name|DESC' => array('goods.name','DESC','ASC',"goods.seo_h1 < '".$this->registry['goods']['seo_h1']."'","goods.seo_h1 > '".$this->registry['goods']['seo_h1']."'"),
				'price_1|ASC' => array('goods.price_1','ASC','DESC',"goods.price_1 > '".$this->registry['goods']['price_1']."'","goods.price_1 < '".$this->registry['goods']['price_1']."'"),
				'price_1|DESC' => array('goods.price_1','DESC','ASC',"goods.price_1 < '".$this->registry['goods']['price_1']."'","goods.price_1 > '".$this->registry['goods']['price_1']."'"),
				'grower|ASC' => array('grower','ASC','DESC',"growers.name > '".$this->registry['goods']['grower']."'","growers.name < '".$this->registry['goods']['grower']."'"),
				'popularity_index|DESC' => array('goods.popularity_index','DESC','ASC',"goods.popularity_index > '".$this->registry['goods']['popularity_index']."'","goods.popularity_index < '".$this->registry['goods']['popularity_index']."'"),
				'present|DESC' => array('goods.present','DESC','ASC',"seo_h1 > '".$this->registry['goods']['seo_h1']."'","seo_h1 < '".$this->registry['goods']['seo_h1']."'"),
			);

			$level_id = $this->registry['goods']['level_id'];
			$sort_key = 'sort|ASC';
				$sort_arr = $sort_items[$sort_key];

			//next
			$qLnk = mysql_query("
								SELECT
									goods.name,
									goods.alias,
									growers.name AS grower
								FROM
									goods
								LEFT OUTER JOIN growers ON growers.id = goods.grower_id
								WHERE
									goods.parent_barcode = 0
									AND
									goods.published = 1
									AND
									goods.weight > 0
									AND
									goods.level_id = '".$level_id."'
									AND
									".$sort_arr[3]."
								ORDER BY
									".$sort_arr[0]." ".$sort_arr[1].",
									goods.seo_h1 ASC
								LIMIT 1
								");
			$next = (mysql_num_rows($qLnk)>0) ? mysql_fetch_assoc($qLnk) : false;

			//prev
			$qLnk = mysql_query("
								SELECT
									goods.name,
									goods.alias,
									growers.name AS grower
								FROM
									goods
								LEFT OUTER JOIN growers ON growers.id = goods.grower_id
								WHERE
									goods.parent_barcode = 0
									AND					
									goods.published = 1
									AND
									goods.weight > 0
									AND
									goods.level_id = '".$level_id."'
									AND
									".$sort_arr[4]."
								ORDER BY
									".$sort_arr[0]." ".$sort_arr[2].",
									goods.seo_h1 ASC
								LIMIT 1
								");
			$prev = (mysql_num_rows($qLnk)>0) ? mysql_fetch_assoc($qLnk) : false;

			$a = array($prev,$next);
			$this->item_rq('prev_next',$a);
		}

		public function print_ostatok(){
			$qLnk = mysql_query("
								SELECT
									ostatki.value
								FROM
									ostatki
								WHERE
									ostatki.goods_id = '".$this->registry['goods']['id']."'
								LIMIT 1;
								");
			if(mysql_num_rows($qLnk)>0){
				$a = mysql_fetch_assoc($qLnk);
				$this->item_rq('ostatok',$a);
			}
		}

		public function goods_ostatok_check($goods_id,$cookie_stored_data){
			$response = 1;

			$goods_in_cart = explode(':',$cookie_stored_data);
			foreach($goods_in_cart as $key => $str){
				$str = explode('|',$str);
				$goods_in_cart[$key] = $str[0];
			}
			$goods_in_cart = array_count_values($goods_in_cart);

			$cur_goods_amount = $goods_in_cart[$goods_id];

			$qLnk = mysql_query("
								SELECT
									ostatki.value
								FROM
									ostatki
								WHERE
									ostatki.goods_id = '".$goods_id."'
								LIMIT 1;
								");
			if(mysql_num_rows($qLnk)>0){
				$g = mysql_fetch_assoc($qLnk);
				if($g['value']<$cur_goods_amount){
					$response = 0;
				}
			}

			echo $response;
		}

	}
?>