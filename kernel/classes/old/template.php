<?php

Class Template {

        private $registry;
        private $vars = array();
        private $F_crumbs = array('/' => 'Главная');
        public $TF;
        public $front_path;
        
        public $Adm_Template;

        function __construct($registry) {
          $this->registry = $registry;
          $this->vars['no_tpl'] = false;
		  $this->TF = ROOT_PATH.'tpl/front/';
		  
		  $this->Front_Template = new Front_Template($this->registry);
		  $this->Adm_Template = new Adm_Template($this->registry);
		  $this->Template_Vars = new Common_Template_Vars($this->registry);
        }
        
		function set($varname, $value, $overwrite=false) {
		        $this->vars[$varname] = $value;
		        return true;
		}


		function remove($varname) {
		      unset($this->vars[$varname]);
		      return true;
		}


		function show() {

          if($this->registry['f_404']){
          		header("HTTP/1.0 404 Not Found");
          		echo('PAGE NOT FOUND');
          }else{
				if($this->vars['no_tpl']) return false;
				
				$this->TF = ROOT_PATH.'tpl/'.$this->vars['tpl'].'/';
				$this->front_path = '/browser/'.$this->vars['tpl'].'/';

				$path = $this->TF.'main.html';
							    
				foreach ($this->vars as $key => $value) $$key = $value;
			    
				ob_start();
			    include ($path);
			    $html = ob_get_clean();
			    
			    if($this->vars['tpl']=='front'){
			    	$html = $this->Front_Template->do_template($html);
			    }else{ 
			    	$html = $this->Adm_Template->do_template($html);
			    }
			    
			    echo $html;
          }
          
		}

		function content(){
			if(!isset($this->vars['c'])) return false;
			
			$file = $this->TF.'content/'.$this->vars['c'].'.html';
			
			if(is_file($file)) require($file);
			else Front_Template_Content::do_content(
					$this->vars['c'],
					(isset($this->vars['tpl'])) ? $this->vars['tpl'] : 'front'
					);
		}

		private function item_rq($name,$a = NULL){
			require($this->TF.'item/template/'.$name.'.html');
		}

		public function sb_menu(){
			$qLnk = mysql_query("
								SELECT
									main_parts.alias,
									main_parts.name,
									parent_tbl.alias AS parent_alias
								FROM
									main_parts
								INNER JOIN main_parts AS parent_tbl ON parent_tbl.id = main_parts.parent_id
								WHERE
									main_parts.parent_id = '".$this->registry['main_part_id']."'
								ORDER BY
									main_parts.sort ASC;
								");
			while($r = mysql_fetch_assoc($qLnk)){
				$r['active'] = ((count($this->registry['sub_aias_path'])==0 && $r['alias']=='') || (count($this->registry['sub_aias_path'])>0 && $r['alias']==$this->registry['sub_aias_path'][0])) ? 'active' : '';
				$this->item_rq('sb_menu',$r);
			}
		}

		public function main_menu(){

			$q_ogr = ($this->registry['userdata']['type']==1) ? "AND main_parts.id <> 8" : "";

			$qLnk = mysql_query("
								SELECT
									main_parts.id,
									main_parts.alias,
									main_parts.name
								FROM
									main_parts
								WHERE
									main_parts.parent_id = 0
									".$q_ogr."
								ORDER BY
									main_parts.sort ASC;
								");
			while($r = mysql_fetch_assoc($qLnk)){
				if(count($this->registry['aias_path'])>0 && $r['alias']==$this->registry['aias_path'][0]){
					$this->registry['main_part_id']	= $r['id'];
				}
				$r['active'] = ((count($this->registry['aias_path'])==0 && $r['alias']=='') || (count($this->registry['aias_path'])>0 && $r['alias']==$this->registry['aias_path'][0])) ? 'active' : '';
				$this->item_rq('main_menu',$r);
			}

		}

		public function main_part_check($alias){
			$qLnk = mysql_query("SELECT COUNT(*) FROM main_parts WHERE main_parts.alias = '".$alias."';");
			return (mysql_result($qLnk,0)>0) ? true : false;
		}

		public function F_sb_catalog_items($childs_arr){
			if($childs_arr):
				$i=1;
				foreach($childs_arr as $id => $arr){
					$a['level_arr'] = $arr;
					$a['class'] = ($i==(count($childs_arr))) ? 'last' : '';
					$this->item_rq('sb_catalog_line',$a);
					$i++;
				}
			endif;
		}

		public function F_sb_catalog(){

			$ca = array();

			$qLnk = mysql_query("
								SELECT
									levels.id,
									levels.parent_id,
									levels.name,
									levels.alias,
									parent_tbl.alias AS parent_alias,
									levels.goods_count
								FROM
									levels
								LEFT OUTER JOIN levels AS parent_tbl ON parent_tbl.id = levels.parent_id
								WHERE
									levels.published = 1
									AND
									IFNULL(parent_tbl.published,1) = 1
								ORDER BY
									levels.parent_id ASC,
									levels.sort ASC;
								");
			while($l = mysql_fetch_assoc($qLnk)){
				$ca[$l['parent_id']][$l['id']] = $l;
			}

			if(isset($ca[0])){
				$i = 1;
				foreach($ca[0] as $parent_id => $arr){
					$a['parent_info'] = $arr;
					$a['childs_arr'] = (isset($ca[$parent_id])) ? $ca[$parent_id] : false;
					$a['class'] = ($i==count($ca[0])) ? 'last' : '';

					$a['visibility_class'] = (isset($_COOKIE['sb_catalog_tog'][$parent_id])) ? 'block_closed' : 'block_opened';
					$a['visibility_dispay'] = (isset($_COOKIE['sb_catalog_tog'][$parent_id])) ? 'none' : 'block';

					$this->item_rq('sb_catalog_root',$a);
					$i++;
				}
			}

		}

		public function add2crumbs($alias,$name){
			$this->F_crumbs[$alias] = $name;
		}

		public function F_crumbs(){
			if(!isset($this->registry['mainpage']) && count($this->F_crumbs)>1):
				$html = array();
				$link = '';
				$i = 1;
				foreach($this->F_crumbs as $alias => $name){
					$link.= ($i!=1) ? $alias.'/' : $alias;
					$inner_html = ($i==(count($this->F_crumbs))) ? $name : '<a href="'.$link.'">'.$name.'</a>';

					$class = ($i==1) ? 'first' : (($i==(count($this->F_crumbs)) && $i!=1) ? 'last' : '');

					$crumb = '<li class="'.$class.'">'.$inner_html.'</li>';
						if(!($i==(count($this->F_crumbs)))) $crumb.='<li class="divider">/</li>';

					$html[]=$crumb;
					$i++;
				}
				$html = '<ul id="crumbs">'.implode('',$html).'</ul>';
				echo $html;
			endif;
		}


		public function F_tech(){
			if(!isset($this->registry['page']['tech']) || !$this->registry['page']['tech']) return false;
			
			$Front_Pages_Extra = new Front_Pages_Extra($this->registry);
			$extra = $Front_Pages_Extra->get_extra($this->registry['page']['tech']);
			if($extra) echo $extra;
			
			if(method_exists($this,$this->registry['page']['tech'])){
				$f = $this->registry['page']['tech'];
								
				$this->$f();
			}
		}

		private function F_map(){
			$this->registry['CL_js']->set(array(
					'http://api-maps.yandex.ru/2.0/?load=package.full&lang=ru-RU',
					'map',
			));			
			
			$this->item_rq('map');
		}

		private function F_prices_link($frompage = true){
			$f = ROOT_PATH.'/public_html/data/sportivnoe-pitanie-price.xls';
			if(is_file($f)){
				$size_bytes = filesize($f);

				if($size_bytes<1024){
				        $a['size'] = $size_bytes.' байт';
				    }elseif($size_bytes<1048576){
				        $a['size'] = round($size_bytes/1024,2).' Кб';
				    }elseif($size_bytes<1073741824){
				        $a['size'] = round($size_bytes/1048576,2).' Мб';
				    }

				 $a['time'] = date('d.m.Y',filemtime($f));

				if($frompage){
					$this->item_rq('prices_link',$a);
				}else{
					echo $a['size'];
				}

			}

		}

		private function F_text_sitemap(){

			$SM = array();

			//pages
			$qLnk = mysql_query("
								SELECT
									pages.name,
									pages.alias
								FROM
									pages
								WHERE
									pages.in_sitemap = 1
									AND
									pages.published = 1
								ORDER BY
									pages.sort ASC;
								");
			while($p = mysql_fetch_assoc($qLnk)){
				$SM[$p['alias']] = array(
										'name' => $p['name']
										);
			}

			//articles
			$qLnk = mysql_query("
								SELECT
									articles.name,
									articles.alias
								FROM
									articles
								WHERE
									articles.in_sitemap = 1
									AND
									articles.published = 1
								ORDER BY
									articles.sort ASC,
									articles.name ASC;
								");
			while($a = mysql_fetch_assoc($qLnk)){
				$SM['pitanie']['ch'][$a['alias']] = array(
										'name' => $a['name']
										);
			}

			//news
			$SM['news'] = array(
								'name' => 'Новости',
								'ch' => array()
								);

			$qLnk = mysql_query("
								SELECT
									news.name,
									news.alias
								FROM
									news
								WHERE
									news.published = 1
								ORDER BY
									news.date DESC;
								");
			while($p = mysql_fetch_assoc($qLnk)){
				$SM['news']['ch'][$p['alias']] = array(
										'name' => $p['name']
										);
			}

			//growers
			$SM['growers'] = array(
								'name' => 'Производители',
								'ch' => array()
								);

			$qLnk = mysql_query("
								SELECT
									growers.name,
									growers.alias
								FROM
									growers
								WHERE
									alias <> ''
									AND
									goods_count > 0
								ORDER BY
									growers.name ASC;
								");
			while($p = mysql_fetch_assoc($qLnk)){
				$SM['growers']['ch'][$p['alias']] = array(
										'name' => $p['name']
										);
			}

			//catalog
			$qLnk = mysql_query("
								SELECT
									levels.name,
									levels.alias,
									levels.parent_id,
									parent_tbl.alias AS parent_alias
								FROM
									levels
								LEFT OUTER JOIN levels AS parent_tbl ON levels.parent_id = parent_tbl.id
								WHERE
									levels.published = 1
								ORDER BY
									levels.parent_id ASC,
									levels.sort ASC;
								");
			while($p = mysql_fetch_assoc($qLnk)){

				if($p['parent_id']==0){
					$SM[$p['alias']] = array(
										'name' => $p['name'],
										'ch' => array()
										);
				}else{
					$SM[$p['parent_alias']]['ch'][$p['alias']] = array(
																		'name' => $p['name'],
																		'ch' => array()
																		);
				}

			}

			$qLnk = mysql_query("
								SELECT
									goods.alias,
									goods.name,
									levels.alias AS level_alias,
									parent_tbl.alias AS parent_alias
								FROM
									goods
								LEFT OUTER JOIN levels ON goods.level_id = levels.id
								LEFT OUTER JOIN levels AS parent_tbl ON levels.parent_id = parent_tbl.id
								WHERE
									levels.published = 1
									AND
									parent_tbl.published = 1
									AND
									(goods.parent_barcode = 0 OR goods.parent_barcode = '') 
								ORDER BY
									goods.sort ASC;
								");
			while($p = mysql_fetch_assoc($qLnk)){
				$SM[$p['parent_alias']]['ch'][$p['level_alias']]['ch'][$p['alias']] = array(
																							'name' => $p['name']
																						);
			}

			echo '<div class="texted"><ul id="sitemap_list">';
			foreach($SM as $alias => $arr){
				if(isset($arr['name'])) echo '<li><a href="/'.$alias.'/">'.$arr['name'].'</a></li>';
				if(isset($arr['ch']) && count($arr['ch'])>0){
					echo '<ul>';
					foreach($arr['ch'] as $alias_2 => $arr_2){

						$fst_al = ($alias!='pitanie') ? $alias.'/' : '';

						echo '<li><a href="/'.$fst_al.$alias_2.'/">'.$arr_2['name'].'</a></li>';
						if(isset($arr_2['ch']) && count($arr_2['ch'])>0){
							echo '<ul>';
							foreach($arr_2['ch'] as $alias_3 => $arr_3){
								echo '<li><a href="/'.$alias.'/'.$alias_2.'/'.$alias_3.'/">'.$arr_3['name'].'</a></li>';
							}
							echo '</ul>';
						}
					}
					echo '</ul>';
				}
			}
			echo '</ul></div>';

		}

	    private function F_delivery_count(){
	    	$this->registry['logic']->zip_code_data($_GET['index']);
	    }

	    public function F_articles_list(){
	    	$qLnk = mysql_query("
	    						SELECT
	    							articles.name,
	    							articles.alias,
	    							articles.alphabet
	    						FROM
	    							articles
	    						WHERE
	    							articles.published = 1
	    							AND
	    							articles.mainpage = 0
	    						ORDER BY
	    							articles.alphabet ASC,
	    							articles.sort ASC,
	    							articles.name ASC
	    						");
	    	$type = '';
	    	$fl = '';
	    	$i = 1;
	    	while($a = mysql_fetch_assoc($qLnk)){

	    		$a['type_change'] = ($type!=$a['alphabet'] && $i>1) ? true : false;
	    		$a['first_letter'] = mb_substr($a['name'],0,1,'utf-8');
	    		$a['letter_change'] = ($fl!=$a['first_letter']) ? true : false;

	    		$this->item_rq('article_item',$a);

	    		$type = $a['alphabet'];
	    		$fl = $a['first_letter'];
	    		$i++;
	    	}
	    }

	    public function F_meta_tags(){
	    	if(isset($this->registry['noindex'])){
				echo '<meta name="robots" content="noindex, nofollow" />';
	    	}

	    	if(isset($this->registry['seo_kw'])){
				echo '<meta name="keywords" content="'.$this->registry['seo_kw'].'" />';
	    	}

	    	if(isset($this->registry['seo_dsc'])){
	    		echo "\r\n";
				echo '<meta name="description" content="'.$this->registry['seo_dsc'].'" />';
	    	}

	    	if(isset($this->registry['CL_catalog']) && isset($this->registry['goods'])){
	    		$url = ($this->registry['goods']['canonical'])
	    			? $this->registry['goods']['canonical']
	    			: mb_strtolower($_SERVER['REQUEST_URI'],'utf-8');
	    		$url = trim(THIS_URL,'/').$url;

	    		echo "\r\n";
				echo '<link rel="canonical" href="'.$url.'" />';
	    	}else{
	    		$url = mb_strtolower($_SERVER['REQUEST_URI'],'utf-8');
	    		
	    		/*$url = explode('?',$_SERVER['REQUEST_URI']);
	    		$url = $url[0];
	    		$url = trim(THIS_URL,'/').$url;
	    		$url = trim($url,'/');
	    		$url = $url.'/';
	    		$url = mb_strtolower($url,'utf-8');*/

	    		echo "\r\n";
				echo '<link rel="canonical" href="'.$url.'" />';
	    	}

	    }

	    public function catalog_quick_opts(){
			$levels = $this->quick_opts_q(0);
			foreach($levels as $id => $data){
				$levels[$id]['children'] = $this->quick_opts_q($id);
			}

			$opts = '<option value="0" selected disabled>переход в раздел каталога</option>';
			foreach($levels as $parent_id => $data){
				$opts.='<optgroup label="'.$data['name'].'">';
				foreach($data['children'] as $id => $ch_data) $opts.='<option value="/adm/catalog/'.$parent_id.'/'.$id.'/">'.$ch_data['name'].'</option>';
				$opts.='</optgroup>';
			}

			echo $opts;
	    }

	    private function quick_opts_q($parent_id){
	    	$data = array();
			$qLnk = mysql_query("
				SELECT
					levels.id,
					levels.name
				FROM
					levels
				WHERE
					parent_id = '".$parent_id."'
				ORDER BY
					levels.sort ASC
				");
			while($l = mysql_fetch_assoc($qLnk)){
				$data[$l['id']]['name'] = $l['name'];
			}

			return $data;
	    }

}
?>