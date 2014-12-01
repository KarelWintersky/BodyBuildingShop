<?
Class Catalog{

	private $registry;

	public function __construct($registry, $frompage = true){
		$this->registry = $registry;

        if($frompage){
	        $route = $this->registry['aias_path'];
	        array_shift($route);

	        if(count($route)==0){
	        	$this->registry['f_404'] = false;
	        	$this->registry['template']->set('c','catalog/main');
	        }elseif(count($route)==1 && $route[0]==0){
	        	$this->registry['template']->set('c','catalog/level');
	        	$this->registry['action'] = 51;
	        	$this->registry['level'] = array('id' => 0, 'parent_id' => 0);
	        	$this->registry['f_404'] = false;
	        } elseif(count($route)==2 && $this->level_check($route[0],$route[1])){
	        	$this->registry['f_404'] = false;
	        	$this->registry['template']->set('c','catalog/level');
	        }elseif(count($route)==3 && $this->goods_check($route[0],$route[1],$route[2])){
	        	$this->registry['f_404'] = false;
	        }elseif(count($route)==4 && $this->goods_check($route[0],$route[1],$route[2]) && $route[3]=='orders'){
	        	$this->registry['template']->set('c','catalog/goodsorders');
	        	$this->registry['f_404'] = false;
	        }
        }

	}

	private function item_rq($name,$a = NULL){
		require($this->registry['template']->TF.'item/catalog/'.$name.'.html');
	}

	private function goods_check($level_parent_id,$level_id,$goods_id){

		if(($level_parent_id==0 && $goods_id!=0)  || !is_numeric($level_parent_id) || !is_numeric($level_id) || !is_numeric($goods_id)){return false;}

		$this->registry['template']->set('c','catalog/good');

		if($level_parent_id==0 && $goods_id==0){
			//добавление раздела

			$qLnk = mysql_query("
								SELECT
									0 AS id,
									levels.id AS parent_id,
									levels.name AS parent_name
								FROM
									levels
								WHERE
									levels.id = '".$level_id."'
								");
			if(mysql_num_rows($qLnk)>0){
				$this->registry['level'] = mysql_fetch_assoc($qLnk);
				$this->registry['template']->set('c','catalog/level');
				$this->registry['action'] = 51;
				return true;
			}else{
				return false;
			}

		}elseif($level_parent_id!=0 && $goods_id==0){
			//добавление товара

			$qLnk = mysql_query("
								SELECT
									parent_tbl.name AS parent_name,
									parent_tbl.id AS parent_id,
									levels.id AS level_id,
									levels.parent_id AS level_parent_id,
									levels.name AS level_name
								FROM
									levels
								LEFT OUTER JOIN levels AS parent_tbl ON levels.parent_id = parent_tbl.id
								WHERE
									levels.id = '".$level_id."'
									AND
									levels.parent_id = '".$level_parent_id."';
								");
			if(mysql_num_rows($qLnk)>0){
				$this->registry['good'] = mysql_fetch_assoc($qLnk);
				$this->registry['action'] = 101;
				return true;
			}else{
				return false;
			}
		}else{
			//выборка по товару

			$qLnk = mysql_query("
								SELECT
									parent_tbl.name AS parent_name,
									parent_tbl.id AS parent_id,
									parent_tbl.id AS level_parent_id,
									levels.name AS level_name,
									levels.alias AS level_alias,
									parent_tbl.alias AS parent_alias,
									goods.*
								FROM
									goods
								LEFT OUTER JOIN levels ON levels.id = goods.level_id
								LEFT OUTER JOIN levels AS parent_tbl ON levels.parent_id = parent_tbl.id
								WHERE
									goods.id = '".$goods_id."'
									AND
									goods.level_id = '".$level_id."'
									AND
									levels.parent_id = '".$level_parent_id."'
								");
			if(mysql_num_rows($qLnk)>0){
				$this->registry['good'] = mysql_fetch_assoc($qLnk);
				$this->registry['action'] = 100;
				return true;
			}else{
				return false;
			}
		}

	}

	private function level_check($parent_id,$level_id){

		if(!is_numeric($parent_id) || !is_numeric($level_id)){return false;}

		if($level_id==0){
			//добавляем раздел

			$qLnk = mysql_query("
								SELECT
									levels.id,
									levels.name
								FROM
									levels
								WHERE
									levels.id = '".$parent_id."';
								");
			if(mysql_num_rows($qLnk)>0){
				$this->registry['level'] = mysql_fetch_assoc($qLnk);
				$this->registry['action'] = 51;
				return true;
			}else{
				return false;
			}
		}else{
			$qLnk = mysql_query("
								SELECT
									parent_tbl.name AS parent_name,
									parent_tbl.parent_id AS parent_parent_id,
									parent_tbl.alias AS parent_parent_alias,
									levels.*
								FROM
									levels
								LEFT OUTER JOIN levels AS parent_tbl ON levels.parent_id = parent_tbl.id
								WHERE
									levels.id = '".$level_id."'
									AND
									levels.parent_id = '".$parent_id."';
								");

			if(mysql_num_rows($qLnk)>0){
				$this->registry['level'] = mysql_fetch_assoc($qLnk);
				$this->registry['action'] = 50;
				return true;
			}else{
				return false;
			}
		}
	}

	public function print_photo_list(&$count_photos){
		
		$ids = array();
		$ids[] = $this->registry['good']['id'];
		if($this->registry['good']['parent_barcode']==0){
			$qLnk = mysql_query(sprintf("
					SELECT id FROM goods WHERE parent_barcode = '%s'
					",$this->registry['good']['barcode']));
			while($g = mysql_fetch_assoc($qLnk)) $ids[] = $g['id'];
		}
				
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
		$count_photos = mysql_num_rows($qLnk);
		$i = 1;
		while($p = mysql_fetch_assoc($qLnk)){
			$p['sort'] = $i;
			$p['class'] = ($i%3==0) ? 'r' : '';

			$p['avatar_checked'] = ($p['id']==$this->registry['good']['avatar_id']) ? 'checked' : '';
			$p['photo_weight'] = $this->photo_weight($p);
			$this->item_rq('goods_photo',$p);
			$i++;
		}
	}

	private function photo_weight($photo){
		$file = ROOT_PATH.sprintf('/data/foto/goods/src/%s/%s',$photo['goods_id'],$photo['alias']);
		if(!is_file($file)) return '';
		$size = filesize($file);
		
		return $this->human_filesize($size,2);
	}
	
	function human_filesize($bytes, $decimals = 2) {
		$sz = 'BKMGTP';
		$factor = floor((strlen($bytes) - 1) / 3);
		return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
	}	
	
	public function delivery_way_options($delivery_way_id){
		$qLnk = mysql_query("
							SELECT
								delivery_ways.name,
								delivery_ways.id
							FROM
								delivery_ways
							ORDER BY
								delivery_ways.name ASC;
							");
		while($g = mysql_fetch_assoc($qLnk)){
			$g['sel'] = ($g['id']==$delivery_way_id) ? 'selected' : '';
			$g['delim'] = '';
			$this->item_rq('list_options',$g);
		}
	}

	public function grower_options($grower_id){

		$gr_arr = array(
						array(
							'id' => 0,
							'name' => 'нет',
							'sel' => '',
							'delim' => ''
						)
						);

		$qLnk = mysql_query("
							SELECT
								growers.name,
								growers.id
							FROM
								growers
							ORDER BY
								growers.name ASC;
							");
		while($g = mysql_fetch_assoc($qLnk)){
			$g['sel'] = ($g['id']==$grower_id) ? 'selected' : '';
			$g['delim'] = '';

			$gr_arr[] = $g;

		}

		foreach($gr_arr as $g){
			$this->item_rq('list_options',$g);
		}

	}

	public function level_select_options($level_id,$from_level = false){

		if($from_level){
			$qLnk = mysql_query("
								SELECT
									levels.id,
									levels.name
								FROM
									levels
								WHERE
									levels.parent_id = '0'
								ORDER BY
									levels.sort ASC;
								");
			while($l = mysql_fetch_assoc($qLnk)){
				$l['sel'] = ($l['id']==$level_id) ? 'selected' : '';
				$l['delim'] = '';
				$this->item_rq('list_options',$l);
			}
		}else{
			$arr_root = array();
			$arr_lev = array();

			$qLnk = mysql_query("
								SELECT
									levels.id,
									levels.parent_id,
									levels.name
								FROM
									levels
								ORDER BY
									levels.parent_id ASC,
									levels.sort ASC;
								");
			while($l = mysql_fetch_assoc($qLnk)){

				if($l['parent_id']==0){
					$arr_root[$l['id']] = $l;
				}else{
					$arr_lev[$l['id']] = $l;
				}
			}

			foreach($arr_root as $parent_id => $arr){
				$arr['delim'] = '';
				$arr['sel'] = 'disabled';
				$this->item_rq('list_options',$arr);

				foreach($arr_lev as $arr){
					if($arr['parent_id']==$parent_id){
						$arr['delim'] = '&nbsp;&nbsp;&nbsp;&nbsp;';
						$arr['sel'] = ($arr['id']==$level_id) ? 'selected' : '';
						$arr['id'] = $parent_id.'-'.$arr['id'];
						$this->item_rq('list_options',$arr);
					}
				}
			}
		}

	}

	public function print_goods_feature_list(){

		$goods_id = (isset($this->registry['good']['id'])) ? $this->registry['good']['id'] : 0;

		$qLnk = mysql_query("
							SELECT
								features.id,
								features.name,
								features.delimiter,
								features.group_id,
								features.image,
								feature_groups.name AS group_name,
								(SELECT COUNT(*) FROM goods_features WHERE goods_features.feature_id = features.id AND goods_features.goods_id = '".$goods_id."') as feature_checked
							FROM
								features
							INNER JOIN feature_groups ON feature_groups.id = features.group_id
							ORDER BY
								feature_groups.name ASC,
								features.sort ASC
							");
		$gr_tmp = 0;
		while($f = mysql_fetch_assoc($qLnk)){
			$f['new_group'] = ($gr_tmp!=$f['group_id']) ? true : false;
			$f['checked'] = ($f['feature_checked']>0) ? 'checked' : '';
			$this->item_rq('goods_features',$f);
			$gr_tmp = $f['group_id'];
		}

	}

	private function print_goods_list($level_id){
		$qLnk = mysql_query("
							SELECT
								goods.id,
								goods.name,
								goods.parent_barcode,
								goods.barcode,
								goods.published,
								goods.present,
								goods.hot,
								goods.new,
								goods.price_1,
								goods.price_2,
								goods.personal_discount,
								goods.packing,
								goods.alias,
								growers.name AS grower
							FROM
								goods
							LEFT OUTER JOIN growers ON growers.id = goods.grower_id
							WHERE
								goods.level_id = '".$level_id."'
								AND
								(goods.parent_barcode = '0' OR goods.parent_barcode = '') 
							ORDER BY
								goods.published DESC,
								goods.sort ASC;
							");
		$i = 1;
		ob_start();
		while($l = mysql_fetch_assoc($qLnk)){
			$l['sort'] = $i;
			$this->item_rq('goods_list',$l);
			$i++;
		}
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	public function print_level_list($parent,&$level_html=''){

		if($this->registry['level']['parent_parent_id']!=''){
			$level_html = $this->print_goods_list($this->registry['level']['id']);
			return;
		}

		if($parent==0){
			$count_str = "(SELECT COUNT(*) FROM levels AS child_levels WHERE child_levels.parent_id = levels.id) AS level_inside_count";
		}else{
			$count_str = "(SELECT COUNT(*) FROM goods WHERE goods.level_id = levels.id) AS level_inside_count";
		}

		$qLnk = mysql_query("
							SELECT
								levels.id,
								levels.parent_id,
								levels.name,
								levels.published,
								".$count_str."
							FROM
								levels
							WHERE
								levels.parent_id = '".$parent."'
							ORDER BY
								levels.sort ASC;
							");
		$i = 1;
		ob_start();
		while($l = mysql_fetch_assoc($qLnk)){
			$l['sort'] = $i;
			$this->item_rq('level_list',$l);
			$i++;
		}
		$level_html = ob_get_contents();
		ob_end_clean();
	}

	/**/

	public function generate_aliases_packing(){
		$qLnk = mysql_query("
							SELECT
								goods.id,
								goods.barcode,
								goods.alias,
								goods.name,
								goods.level_id,
								goods.grower_id,
								goods.packing,
								parent_tbl.id AS parent_id,
								growers.alias AS grower_alias
							FROM
								goods
							LEFT OUTER JOIN levels ON goods.level_id = levels.id
							LEFT OUTER JOIN levels AS parent_tbl ON parent_tbl.id = levels.parent_id
							LEFT OUTER JOIN growers ON growers.id = goods.grower_id
							WHERE
								parent_tbl.id = 2
								;
							");
		while($g = mysql_fetch_assoc($qLnk)){
			$alias = $g['alias'];
			$grower_alias = $g['grower_alias'];

			$new_alias = $grower_alias.'-'.$alias;

			$q = mysql_query("UPDATE goods SET goods.alias = '".$new_alias."' WHERE goods.id = '".$g['id']."';");
			var_dump($q);
		}
	}

	/**/
	public function generate_h2_title(){
		/*для одежды*/
			$qLnk = mysql_query("
							SELECT
								goods.id,
								goods.seo_h1,
								parent_tbl.id AS parent_id
							FROM
								goods
							LEFT OUTER JOIN levels ON goods.level_id = levels.id
							LEFT OUTER JOIN levels AS parent_tbl ON parent_tbl.id = levels.parent_id
							WHERE
								parent_tbl.id = 4;
								;");
			while($g = mysql_fetch_assoc($qLnk)){
				$h1_arr = explode(', ',$g['seo_h1']);
				$k = array_shift($h1_arr);

				$new_h1 = implode(', ',$h1_arr);

				mysql_query("UPDATE goods SET goods.seo_h1 = '".$new_h1."' WHERE goods.id = '".$g['id']."';");

				var_dump(mysql_affected_rows());

			}

	}

	/**/
	public function mk_values_2_dump(){
		$qLnk = mysql_query("
							SELECT
								goods.id,
								goods.barcode,
								goods.name,
								goods.level_id,
								goods.grower_id,
								goods.packing,
								goods.content,
								goods.recommendations,
								goods.ingredients,
								parent_tbl.id AS parent_id
							FROM
								goods
							LEFT OUTER JOIN levels ON goods.level_id = levels.id
							LEFT OUTER JOIN levels AS parent_tbl ON parent_tbl.id = levels.parent_id
								;
							");
		while($g = mysql_fetch_assoc($qLnk)){

			$good_arr = array();

			$letters = preg_split('//u', $g['name'], -1, PREG_SPLIT_NO_EMPTY);
			foreach($letters as $key => $l){
				if(ord($l)==194){
					$letters[$key] = ' ';
				}
			}
			$good_arr['name'] = implode('',$letters);

			$good_arr['seo_title'] = $this->mk_seo_title($g['level_id'],$g['grower_id'],$good_arr['name'],$g['packing']);
			$good_arr['alias'] = Common_Useful::rus2translit(mb_strtolower(strip_tags($good_arr['name']),'utf8'));

			$good_arr['content'] = $g['content'];
			$good_arr['content'] = str_replace('<b>','<strong>',$g['content']);
			$good_arr['content'] = str_replace('</b>','</strong>',$good_arr['content']);

			$good_arr['recommendations'] = str_replace('<b>','<strong>',$g['recommendations']);
			$good_arr['recommendations'] = str_replace('</b>','</strong>',$good_arr['recommendations']);

			$good_arr['ingredients'] = str_replace('<b>','<strong>',$g['ingredients']);
			$good_arr['ingredients'] = str_replace('</b>','</strong>',$good_arr['ingredients']);

			$good_arr['seo_kw'] = $this->mk_seo_kw($good_arr['seo_title'],$g['parent_id']);
			$good_arr['seo_dsc'] = $this->mk_seo_dsc($good_arr['content']);
			$good_arr['introtext'] = $good_arr['seo_dsc'];

			$q = mysql_query("
						UPDATE
							goods
						SET
							goods.name = '".$good_arr['name']."',
							goods.alias = '".$good_arr['alias']."',
							goods.seo_title = '".$good_arr['seo_title']."',
							goods.seo_h1 = '".$good_arr['seo_title']."',
							goods.seo_kw = '".$good_arr['seo_kw']."',
							goods.seo_dsc = '".$good_arr['seo_dsc']."',
							goods.introtext = '".$good_arr['introtext']."'
						WHERE
							goods.id = '".$g['id']."';
						");
			if(!$q){
				var_dump($g['barcode']);
			}

		}
	}
	/**/

    private function checkFreeUrl($url,$id,$table,$parent,$parent_type){

    	$parent_arr = explode('-',$parent);
    	$parent = ($parent_type=='level_id') ? $parent_arr[1] : $parent_arr[0];

		$qLnk = mysql_query("
							SELECT
								COUNT(*)
							FROM
								".$table."
							WHERE
								".$table.".alias = '".$url."'
								AND
								".$table.".id <> ".$id."
								AND
								".$table.".".$parent_type." = '".$parent."';
							");
		return (mysql_result($qLnk,0)==1) ? false : true;

    }

    private function urlGenerate($url,$id,$table,$parent,$parent_type){
    	$workurl = $url;
    	$i=1;
    	while(!$this->checkFreeUrl($workurl,$id,$table,$parent,$parent_type)){
    		$workurl = $url.'-'.$i;
    		$i++;
    	}

    	return $workurl;
    }

    private function mk_seo_title($level_id,$grower_id,$name,$packing){

    	$title_arr = array();

    	/*$qLnk = mysql_query("SELECT levels.name FROM levels WHERE levels.id = '".$level_id."';");
    	if(mysql_num_rows($qLnk)>0){
    		$title_arr[] = mysql_result($qLnk,0);
    	}*/

    	$title_arr[] = str_replace('&quot;','',htmlspecialchars($name));
    	
    	$qLnk = mysql_query("SELECT growers.name FROM growers WHERE growers.id = '".$grower_id."';");
    	if(mysql_num_rows($qLnk)>0){
    		$title_arr[] = mysql_result($qLnk,0);
    	}

    	//if($packing!=''){$title_arr[] = $packing;}

    	return implode(', ',$title_arr);
    }

    private function mk_seo_dsc($content){
    	$CA = explode('.',str_replace('&nbsp;','',strip_tags($content)));
    	if(count($CA)>=2){
    		return $CA[0].'. '.$CA[1].'.';
    	}elseif(count($CA)==1){
    		return $CA[0];
    	}else{
			return '';
    	}

    }

    private function mk_seo_kw($seo_title,$parent_id){
    	$kw_arr = array();
    	$qLnk = mysql_query("SELECT levels.name FROM levels WHERE levels.id = '".$parent_id."';");
    	if(mysql_num_rows($qLnk)>0){
    		$kw_arr[] = mysql_result($qLnk,0);
    	}

    	$kw_arr[] = $seo_title;

    	return implode(', ',$kw_arr);
    }

	public function sav_good(){
		foreach($_POST as $key => $val){$$key = (is_array($val)) ? $val : $val;}

		$published = (isset($published) && $published==1) ? 1 : 0;
		$present = (isset($present) && $present==1) ? 1 : 0;
		$new = (isset($new) && $new==1) ? 1 : 0;
		$hot = (isset($hot) && $hot==1) ? 1 : 0;

		$level_id_arr = explode('-',$level_id);

		$alias = ($alias=='') ? $this->mk_goods_alias($name,$level_id_arr,$packing,$grower_id) : $alias;
		$alias = $this->urlGenerate($alias,$id,'goods',$level_id,'level_id');

		$seo_title = ($seo_title=='') ? $this->mk_seo_title($level_id_arr[1],$grower_id,$name,$packing) : $seo_title;
		$seo_h1 = ($seo_h1=='') ? $seo_title : $seo_h1;
		$seo_dsc = ($seo_dsc=='') ? $this->mk_seo_dsc($content) : $seo_dsc;
		$seo_kw = ($seo_kw=='') ? $this->mk_seo_kw($seo_title,$level_id_arr[0]) : $seo_kw;

		$introtext = ($introtext!='') ? $introtext : mb_substr(strip_tags($content),0,200,'utf-8');

		$alt_first_img = $seo_title;

		mysql_query("
					UPDATE
						goods
					SET
						goods.name = '".mysql_real_escape_string(htmlspecialchars_decode($name))."',
						goods.alias = '".$alias."',
						goods.level_id = '".$level_id_arr[1]."',
						goods.content = '".mysql_real_escape_string($content)."',
						goods.introtext = '".mysql_real_escape_string($introtext)."',
						goods.recommendations = '".mysql_real_escape_string($recommendations)."',
						goods.ingredients = '".mysql_real_escape_string($ingredients)."',
						goods.published = '".$published."',
						goods.new = '".$new."',
						goods.present = '".$present."',
						goods.hot = '".$hot."',
						goods.seo_title = '".mysql_real_escape_string(htmlspecialchars_decode($seo_title))."',
						goods.seo_h1 = '".$seo_h1."',
						goods.seo_kw = '".$seo_kw."',
						goods.seo_dsc = '".$seo_dsc."',
						goods.price_1 = '".$price_1."',
						goods.barcode = '".$barcode."',
						goods.parent_barcode = '".$parent_barcode."',
						goods.packing = '".$packing."',
						goods.weight = '".$weight."',
						goods.personal_discount = '".$personal_discount."',
						goods.grower_id = '".$grower_id."',
						goods.delivery_way_id = '".$delivery_way_id."'
					WHERE
						goods.id = '".$id."';
					");

		$this->img_alt_upd($alt_first_img,$avatar_id);

		$this->goods_features_upd($id);
		$this->level_published_goods_count_upd();
		$this->goods_in_grower_upd();

		$settings = new Settings($this->registry,false);
		$settings->yandex_market_xml();


		$this->save_goods_barcodes($id);

		$rp = trim($rp,'/');
		$rp_arr = explode('/',$rp);
		$rp_arr[2] = $level_id_arr[0];
		$rp_arr[3] = $level_id_arr[1];

		$rp = '/'.implode('/',$rp_arr).'/';

		$this->registry['doer']->set_rp($rp);
	}

	private function save_goods_barcodes($goods_id){
		mysql_query(sprintf("DELETE FROM goods_barcodes WHERE goods_id = '%d'",$goods_id));

		if(isset($_POST['bc'])){
			$q = array();

			foreach($_POST['bc'] as $data)
				if(!isset($data['del']))
					$q[] = sprintf("('%d',
										'%s',
											'%s',
												'%s',
													'%s',
														'%s',
															'%s',
																'%d')",
																$goods_id,
																$data['barcode'],
																$data['packing'],
																$data['feature'],
																$data['weight'],
																$data['price'],
																(isset($data['present'])) ? 1 : 0,
																$data['sort']
																);


			if(count($q)>0)
				mysql_query(sprintf("
							INSERT INTO
								goods_barcodes
								(goods_id,
									barcode,
										packing,
											feature,
												weight,
													price,
														present,
															sort)
								VALUES
								%s
				",
				implode(", ",$q)
				));

		}

	}

	private function img_alt_upd($alt_first_img,$avatar_id){
		if($avatar_id!=0){
			$qLnk = mysql_query("SELECT goods_photo.alt FROM goods_photo WHERE goods_photo.id = '".$avatar_id."';");
			if(mysql_num_rows($qLnk)>0){
				if(mysql_result($qLnk,0)==''){
					mysql_query("UPDATE goods_photo SET goods_photo.alt = '".$alt_first_img."' WHERE goods_photo.id = '".$avatar_id."';");
				}
			}
		}
	}

	private function mk_goods_alias($name,$level_id_arr,$packing,$grower_id){

		$alias_arr = array();

		//транслит имени для всего
		$name = str_replace('&quot;','',$name);
			$name = str_replace('&quot;','',htmlspecialchars($name));
				$alias_arr[] = Common_Useful::rus2translit($name);		
		
		//производитель для всего кроме одежды (ID:4) и суперсета (ID:1)
		if($level_id_arr[0]!=4 && $level_id_arr[0]!=1){
			$qLnk = mysql_query("SELECT growers.alias FROM growers WHERE growers.id = '".$grower_id."';");
			if(mysql_num_rows($qLnk)>0){
				$alias_arr[] = mysql_result($qLnk,0);
			}
		}

		//упаковка для всего кроме одежды (ID:4)
		/*if($level_id_arr[0]!=4){
			$packing = str_replace(' ','',$packing);
				$packing = str_replace('&nbsp;','',$packing);
			$alias_arr[] = Common_Useful::rus2translit($packing);
		}*/

		$alias = implode('-',$alias_arr);

		//убираем какие-то дурацкие пробелы из алиаса, если есть
		$chars = preg_split('//', $alias, -1, PREG_SPLIT_NO_EMPTY);
		$new_chars = array();
		foreach($chars as $ch){
			if(!in_array(ord($ch),array(194,160))){
				$new_chars[] = $ch;
			}
		}
		$alias = implode('',$new_chars);

		return $alias;

	}

	public function add_good(){
		foreach($_POST as $key => $val){$$key = (is_array($val)) ? $val : mysql_real_escape_string($val);}

		$published = (isset($published) && $published==1) ? 1 : 0;
		$present = (isset($present) && $present==1) ? 1 : 0;
		$new = (isset($new) && $new==1) ? 1 : 0;
		$hot = (isset($hot) && $hot==1) ? 1 : 0;

		$level_id_arr = explode('-',$level_id);

		$alias = ($alias=='') ? $this->mk_goods_alias($name,$level_id_arr,$packing,$grower_id) : $alias;
		$alias = $this->urlGenerate($alias,0,'goods',$level_id,'level_id');

		$seo_title = ($seo_title=='') ? $this->mk_seo_title($level_id_arr[1],$grower_id,$name,$packing) : $seo_title;
		$seo_h1 = ($seo_h1=='') ? $seo_title : $seo_h1;
		$seo_dsc = ($seo_dsc=='') ? $this->mk_seo_dsc($content) : $seo_dsc;
		$seo_kw = ($seo_kw=='') ? $this->mk_seo_kw($seo_title,$level_id_arr[0]) : $seo_kw;

		$introtext = ($introtext!='') ? $introtext : mb_substr(strip_tags($content),0,200,'utf-8');

		mysql_query("
					INSERT INTO
						goods
						(name,
							alias,
								level_id,
									content,
										introtext,
											recommendations,
												ingredients,
													published,
														new,
															present,
																hot,
																	seo_title,
																		seo_h1,
																			seo_kw,
																				seo_dsc,
																					price_1,
																						barcode,
																							parent_barcode,
																								packing,
																									weight,
																										personal_discount,
																											grower_id,
																												delivery_way_id)
						VALUES
						('".htmlspecialchars_decode($name)."',
							'".$alias."',
								'".$level_id_arr[1]."',
									'".$content."',
										'".$introtext."',
											'".$recommendations."',
												'".$ingredients."',
													'".$published."',
														'".$new."',
															'".$present."',
																'".$hot."',
																	'".htmlspecialchars_decode($seo_title)."',
																		'".$seo_h1."',
																			'".$seo_kw."',
																				'".$seo_dsc."',
																					'".$price_1."',
																						'".$barcode."',
																							'".$parent_barcode."',
																								'".$packing."',
																									'".$weight."',
																										'".$personal_discount."',
																											'".$grower_id."',
																												'".$delivery_way_id."');
					");

		$goods_id = mysql_insert_id();

		mysql_query(sprintf("
				INSERT INTO
				goods_barcodes
				(goods_id,
				barcode,
				packing,
				feature,
				weight,
				price,
				present,
				sort)
				VALUES
				('%d',
				'%s',
				'%s',
				'',
				'%s',
				'%s',
				'%d',
				'1')
				",
				$goods_id,
				$barcode,
				$packing,
				$weight,
				$price_1,
				$present
		));
		
		
		$this->goods_features_upd($goods_id);
		$this->level_published_goods_count_upd();
		$this->goods_in_grower_upd();

		$settings = new Settings($this->registry,false);
		$settings->yandex_market_xml();

		$rp = trim($rp,'/');
		$rp_arr = explode('/',$rp);
		$rp_arr[2] = $level_id_arr[0];
		$rp_arr[3] = $level_id_arr[1];
		$rp_arr[4] = $goods_id;

		$rp = '/'.implode('/',$rp_arr).'/';

		$this->registry['doer']->set_rp($rp);

	}

	public function sav_level(){
		foreach($_POST as $key => $val){$$key = (is_array($val)) ? $val : $val;}

		$photomanager = new Photomanager($this->registry);
		$avatar = $photomanager->upload_level_avatar($old_avatar);

		$published = (isset($published) && $published==1) ? 1 : 0;

		$alias = ($alias=='') ? Common_Useful::rus2translit(str_replace('&quot;','',htmlspecialchars($name))) : $alias;
		$alias = $this->urlGenerate($alias,$id,'levels',$parent_id,'parent_id');

		$seo_title = ($seo_title=='') ? str_replace('&quot;','',htmlspecialchars($name)) : $seo_title;

		mysql_query("
					UPDATE
						levels
					SET
						levels.name = '".mysql_real_escape_string(htmlspecialchars_decode($name))."',
						levels.parent_id = '".$parent_id."',
						levels.alias = '".$alias."',
						levels.avatar = '".$avatar."',
						levels.published = '".$published."',
						levels.content = '".mysql_real_escape_string($content)."',
						levels.seo_title = '".mysql_real_escape_string(htmlspecialchars_decode($seo_title))."',
						levels.seo_kw = '".$seo_kw."',
						levels.seo_dsc = '".$seo_dsc."'
					WHERE
						levels.id = '".$id."';
					");

		$rp = trim($rp,'/');
		$rp_arr = explode('/',$rp);

		$rp_arr[2] = $parent_id;
		$rp_arr[3] = $id;

		$rp = '/'.implode('/',$rp_arr).'/';

		$this->registry['doer']->set_rp($rp);

	}

	public function add_level(){
		foreach($_POST as $key => $val){$$key = (is_array($val)) ? $val : mysql_real_escape_string($val);}

		$published = (isset($published) && $published==1) ? 1 : 0;

		$alias = ($alias=='') ? Common_Useful::rus2translit(str_replace('&quot;','',htmlspecialchars($name))) : $alias;
		$alias = $this->urlGenerate($alias,0,'levels',$parent_id,'parent_id');

		$seo_title = ($seo_title=='') ? str_replace('&quot;','',htmlspecialchars($name)) : $seo_title;

		mysql_query("
					INSERT INTO
						levels
						(name,
							alias,
								parent_id,
									published,
										content,
											seo_title,
												seo_kw,
													seo_dsc)
						VALUES
						('".htmlspecialchars_decode($name)."',
							'".$alias."',
								'".$parent_id."',
									'".$published."',
										'".$content."',
											'".htmlspecialchars_decode($seo_title)."',
												'".$seo_kw."',
													'".$seo_dsc."');
					");

		$rp = trim($rp,'/');
		$rp_arr = explode('/',$rp);

		if(count($rp_arr)==3){
			$rp_arr[3] = mysql_insert_id();
		}else{
			$rp_arr[2] = $parent_id;
			$rp_arr[3] = mysql_insert_id();
			unset($rp_arr[4]);
		}

		$rp = '/'.implode('/',$rp_arr).'/';

		$this->registry['doer']->set_rp($rp);

	}

	public function level_good_sort(){
		foreach($_POST as $key => $val){$$key = (is_array($val)) ? $val : mysql_real_escape_string($val);}

		foreach($sort as $id => $sort){
			mysql_query("UPDATE ".$table." SET ".$table.".sort = '".$sort."' WHERE ".$table.".id = '".$id."';");
		}

		if(isset($gr_op) && $gr_op>0) $this->group_operations($gr_op);

	}

	private function group_operations($code){
		if(isset($_POST['op'])){
			$q = "";
			switch($code){
				case 1: //в наличии
					$q = "present = '1'";
					break;
				case 2: //отсутствует
					$q = "present = '0'";
					break;
				case 3: //изъять из продажи
					$q = "present = '0', published = '0'";
					break;
				case 4: //новый
					$q = "new = '1'";
					break;
				case 5: //не новый
					$q = "new = '0'";
					break;
			}

			if($q!=""){
				mysql_query("
							UPDATE
								goods
							SET
								".$q."
							WHERE
								id IN (".implode(",",array_keys($_POST['op'])).")
							");
			}

		}
	}

	public function level_del(){
		foreach($_POST as $key => $val){$$key = (is_array($val)) ? $val : mysql_real_escape_string($val);}

		$levels_arr = array($id);

		$qLnk = mysql_query("SELECT levels.id FROM levels WHERE levels.parent_id = '".$id."';");
		while($l = mysql_fetch_assoc($qLnk)){
			$levels_arr[] = $l['id'];
		}

		$qLnk = mysql_query("SELECT goods.id FROM goods WHERE goods.level_id IN (".implode(',',$levels_arr).");");
		while($g = mysql_fetch_assoc($qLnk)){
			$this->goods_del($g['id']);
		}

		mysql_query("DELETE FROM levels WHERE levels.id IN (".implode(',',$levels_arr).");");

	}

	public function goods_del($goods_id = 0){

		$photomanager = new Photomanager($this->registry);

		$goods_id = ($goods_id==0) ? $_POST['id'] : $goods_id;
		mysql_query("DELETE FROM goods WHERE goods.id = '".$goods_id."';");

		mysql_query("DELETE FROM goods_photo WHERE goods_photo.goods_id = '".$goods_id."';");
		if(mysql_affected_rows()>0){$photomanager->unlink_images('',$goods_id,true);}

		$this->level_published_goods_count_upd();
		$this->goods_in_grower_upd();

		$settings = new Settings($this->registry,false);
		$settings->yandex_market_xml();

	}

	public function goods_photo_upload(){
		
		if($_FILES['photo']['size']>0):

			$photomanager = new Photomanager($this->registry);

			$goods_id = $_POST['goods_id'];

			$alias = str_replace('&-','',$_FILES['photo']['name']);
				$alias = str_replace('&','',$alias);

    		$i = 1;
    		while(!$this->photo_new_alias_check($alias)){
    			 $aliar_arr = explode('.',$alias);
    			 $ext = array_pop($aliar_arr);

    			 $alias = implode('.',$aliar_arr).'-'.$i.'.'.$ext;

				 $i++;
    		}
    		
    		$src_dir = $this->mk_img_dir('src',$goods_id);
    		if(!is_dir($src_dir)){mkdir($src_dir);}
    		$src_full_path = $src_dir.$alias;

			move_uploaded_file($_FILES['photo']['tmp_name'],$src_full_path);

			$photo_dim_pairs = explode(',',PHOTO_DIM_STR);

			foreach($photo_dim_pairs as $pair){
				$dest_dir = $this->mk_img_dir($pair,$goods_id);
				if(!is_dir($dest_dir)){mkdir($dest_dir);}

				$dest_full_path = $dest_dir.$alias;

				$dim_arr = explode('x',$pair);
				$photomanager->image_resize($src_full_path,$dest_full_path,intval($dim_arr[0]),intval($dim_arr[1]));
			}

			$sort = $this->goods_photo_max_sort($goods_id);

			mysql_query("INSERT INTO goods_photo (alias, sort, goods_id) VALUES ('".$alias."','".$sort."','".$goods_id."')");
			$photo_id = mysql_insert_id();

			if($sort==1){//если фото первая - делаем ее аватаркой
				mysql_query("UPDATE goods SET goods.avatar_id = '".$photo_id."' WHERE goods.id = '".$goods_id."';");
			}

		endif;
	}

	private function mk_img_dir($dim,$goods_id){
		return GOODS_PHOTO_DIR.$dim.DIRSEP.$goods_id.DIRSEP;
	}

    private function photo_new_alias_check($alias){
		$qLnk = mysql_query("SELECT COUNT(*) FROM goods_photo WHERE goods_photo.alias = '".$alias."';");
		
		return (mysql_result($qLnk,0)>0) ? false : true;
    }

	private function goods_photo_max_sort($goods_id){
		$qLnk = mysql_query("SELECT IFNULL(MAX(goods_photo.sort)+1,1) FROM goods_photo WHERE goods_photo.goods_id = '".$goods_id."';");
		return mysql_result($qLnk,0);
	}

	public function goods_photo_upd(){
		foreach($_POST as $key => $val){$$key = (is_array($val)) ? $val : mysql_real_escape_string($val);}

		foreach($photo as $id => $arr){
			if(isset($arr['del']) && $arr['del']==1){
				
				$goods_id_del = mysql_result(mysql_query(sprintf("SELECT goods_id FROM goods_photo WHERE id = '%d'",$id)),0);
				
				$photomanager = new Photomanager($this->registry);
				$photomanager->unlink_images($arr['alias'],$goods_id_del,false);

				mysql_query("DELETE FROM goods_photo WHERE goods_photo.id = '".$id."';");
			}else{
				mysql_query("UPDATE goods_photo SET goods_photo.sort = '".$arr['sort']."', goods_photo.alt = '".$arr['alt']."' WHERE goods_photo.id = '".$id."';");
			}
		}

		$avatar = (isset($avatar)) ? $avatar : 0;

		mysql_query("UPDATE goods SET goods.avatar_id = '".$avatar."' WHERE goods.id = '".$goods_id."';");

	}

	private function goods_features_upd($goods_id){

		if(isset($_POST['features'])){
			$q_arr = array();

			foreach($_POST['features'] as $feature_id => $on){
				$q_arr[] = "('".$goods_id."', '".$feature_id."')";
			}

			mysql_query("DELETE FROM goods_features WHERE goods_features.goods_id = '".$goods_id."';");
			mysql_query("
						INSERT INTO
							goods_features
							(goods_features.goods_id,
								goods_features.feature_id)
							VALUES
							".implode(', ',$q_arr)."
						");
			}

	}

	private function level_published_goods_count_upd(){
		mysql_query("
					UPDATE
						levels
					SET
						levels.goods_count = (SELECT COUNT(*) FROM goods WHERE goods.level_id = levels.id AND goods.published = 1 AND goods.weight > 0 AND goods.parent_barcode = 0)
					WHERE
						levels.parent_id <> 0
		");
	}

	private function goods_in_grower_upd(){
		mysql_query("UPDATE growers SET growers.goods_count = (SELECT COUNT(*) FROM goods WHERE goods.grower_id = growers.id AND goods.published = 1);");
	}

	public function search_goods_by_id(){
		$barcode = $_POST['barcode'];

		$qLnk = mysql_query(sprintf("
				SELECT
					goods.id,
					goods.level_id,
					parent_tbl.id AS parent_id				
				FROM
					goods_barcodes
				INNER JOIN goods ON goods.id = goods_barcodes.goods_id
				LEFT OUTER JOIN levels ON levels.id = goods.level_id
				LEFT OUTER JOIN levels AS parent_tbl ON parent_tbl.id = levels.parent_id				 
				WHERE
					goods_barcodes.barcode = '%s'
				",$barcode));
		
		if(mysql_num_rows($qLnk)>0){
			$g = mysql_fetch_assoc($qLnk);
			$lnk = '/adm/catalog/'.$g['parent_id'].'/'.$g['level_id'].'/'.$g['id'].'/';
			$this->registry['doer']->set_rp($lnk);
		}

	}

	public function barcode_check($barcode,$this_id){
		$qLnk = mysql_query("
							SELECT
								goods.id,
								goods.level_id,
								parent_tbl.id AS parent_id
							FROM
								goods
							LEFT OUTER JOIN levels ON levels.id = goods.level_id
							LEFT OUTER JOIN levels AS parent_tbl ON parent_tbl.id = levels.parent_id
							WHERE
								goods.barcode = '".$barcode."'
								AND
								goods.id <> '".$this_id."'
							");
		if(mysql_num_rows($qLnk)>0){
			$g = mysql_fetch_assoc($qLnk);
			$lnk = '/adm/catalog/'.$g['parent_id'].'/'.$g['level_id'].'/'.$g['id'].'/';
			echo $lnk;
		}else{
			echo 0;
		}
	}

	public function goods_in_orders(&$total_amount){

		$Orders = new Orders($this->registry,false);
		
		$orders = array();
		$order_ids = array();
		
		//старая система заказов
		$qLnk = mysql_query("
							SELECT DISTINCT
								orders_goods.order_id,
								orders_goods.goods_feats_str,
								orders_goods.amount
							FROM
								orders_goods
							WHERE
								orders_goods.goods_id = '".$this->registry['good']['id']."'
							");
		while($o = mysql_fetch_assoc($qLnk)){
			$orders[$o['order_id']][] = array(
					'amount' => $o['amount'],
					'goods_barcode' => '',
					'feats' => $o['goods_feats_str'],
					);
			$order_ids[] = $o['order_id'];
		}
		
		//новая система заказов
		$barcodes = array();
		$qLnk = mysql_query(sprintf("
				SELECT
					barcode,
					packing,
					feature
				FROM
					goods_barcodes
				WHERE
					goods_id = '%d'
				",$this->registry['good']['id']));
		while($b = mysql_fetch_assoc($qLnk)) $barcodes[$b['barcode']] = $b;
		
		if(count($barcodes)>0){
			$bc_keys = array();
			foreach($barcodes as $key => $arr) $bc_keys[] = sprintf("'%s'",$key);
			
			$qLnk = mysql_query(sprintf("
					SELECT
						order_id,
						goods_barcode,
						goods_feats_str,
						amount
					FROM
						orders_goods
					WHERE
						goods_barcode IN (%s)
					",
					implode(",",$bc_keys)
					));
			while($o = mysql_fetch_assoc($qLnk)){
				$orders[$o['order_id']][] = array(
						'amount' => $o['amount'],
						'goods_barcode' => $o['goods_barcode'],
						'feats' => $o['goods_feats_str'],
						);
				$order_ids[] = $o['order_id'];
			}			
		}

		if(count($order_ids)>0){
			foreach($order_ids as $key => $id) $order_ids[$key] = "'".$id."'";

			$qLnk = mysql_query("
								SELECT SQL_CALC_FOUND_ROWS
									CONCAT_WS('/',orders.id,orders.user_num,orders.payment_method) AS text_id,
									orders.id,
									orders.user_num,
									orders.payment_method,
									orders.made_on,
									orders.status
								FROM
									orders
								WHERE
									CONCAT_WS('/',orders.id,orders.user_num,orders.payment_method) IN (".implode(",",$order_ids).")
								ORDER BY
									orders.made_on DESC
								".$this->goods_in_orders_pagination()."
								");
			$qA = mysql_query("SELECT FOUND_ROWS();");
	   		$total_amount = mysql_result($qA,0);

			$month_change = false;
			while($o = mysql_fetch_assoc($qLnk)){
				$amount = 0;
				$pf = array();				
				if(isset($orders[$o['text_id']])){
					foreach($orders[$o['text_id']] as $goods){
						$amount+=$goods['amount'];
						
						$packing = (isset($barcodes[$goods['goods_barcode']]['packing'])) 
							? $barcodes[$goods['goods_barcode']]['packing']
							: false;
						$feature = (isset($barcodes[$goods['goods_barcode']]['feature'])) 
							? $barcodes[$goods['goods_barcode']]['feature']
							: false;
						
						$str = array($packing,$feature);
						foreach($str as $key => $val) if(!$val) unset($str[$key]);

						if(count($str)>0) $pf[] = implode(', ',$str);
					}
				}
				
				$o['amount'] = $amount;
				$o['pf'] = implode('<br>',$pf);
				
				$o['status_txt'] = (isset($Orders->statuses[$o['status']])) ? $Orders->statuses[$o['status']] : '';
				$o['month_change'] = ($month_change != date('m.Y',strtotime($o['made_on']))) ? true : false;
				$this->item_rq('goods_in_orders',$o);

				$month_change = date('m.Y',strtotime($o['made_on']));
			}
		}

	}

	private function goods_in_orders_pagination(){

		$PAGING = 50;

    	$page = (isset($_GET['page'])) ? $_GET['page'] : 1;
    	$offset = $PAGING*($page-1);

    	return "LIMIT ".$offset.", ".$PAGING;
	}

	public function goods_in_orders_nav($total_amount){

		$url = $_SERVER['SCRIPT_URL'];

		$PAGING = 50;
		$page = (isset($_GET['page'])) ? $_GET['page'] : 1;

		$prev = $page - 1;
			$prev_url = ($prev==1) ? $url : $url.'?page='.$prev;
		$next = $page + 1;

		if($prev>0) echo '<li><a href="'.$prev_url.'">« Пред</a></li>';
		echo '<li><a href="'.$url.'?page='.$next.'">След »</a></li>';
	}

	public function barcode_add(){

		$a = array(
			'id' => md5(time().rand()),
			'barcode' => '',
			'packing' => '',
			'feature' => '',
			'weight' => '',
			'price' => '',
			'present' => 1,
			);

		require(ROOT_PATH.'tpl/adm/item/catalog/barcode.html');
	}

	public function print_goods_barcodes(){
		$qLnk = mysql_query(sprintf("
			SELECT
				goods_barcodes.*,
				goods.id AS former_goods_id
			FROM
				goods_barcodes
			LEFT OUTER JOIN goods ON goods.barcode = goods_barcodes.barcode 	
			WHERE
				goods_id = '%d'
			ORDER BY
				sort ASC;
			",
			(isset($this->registry['good']['id'])) ? $this->registry['good']['id'] : 0
			));
		while($b = mysql_fetch_assoc($qLnk)){
			$b['id'] = md5(time().rand());
			$this->item_rq('barcode',$b);
		}
	}

}
?>