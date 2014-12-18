<?php

Class Template {

        private $registry;
        private $vars = array();
        private $F_crumbs = array('/' => 'Главная');
        public $TF;
        public $front_path;
        
        public $Front_Template;

        function __construct($registry) {
          $this->registry = $registry;
          $this->vars['no_tpl'] = false;
		  $this->TF = ROOT_PATH.'tpl/front/';
		  
		  $this->Front_Template = new Front_Template($this->registry);
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
			    
			    if($this->vars['tpl']=='front') $html = $this->Front_Template->do_template($html);
			    
			    echo $html;
          }
          
		}

		function content(){
			if(isset($this->vars['c'])){
				require($this->TF.'content/'.$this->vars['c'].'.html');
			}
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

		public function F_sb_popular(){
			$goods = array();
			$qLnk = mysql_query("
								SELECT
									goods.id,
									goods.name,
									goods.alias,
									levels.alias AS level_alias,
									parent_tbl.alias AS parent_level_alias,
									goods_photo.alias AS avatar,
									goods_photo.goods_id AS photo_goods_id,
									growers.name AS grower
								FROM
									goods
								LEFT OUTER JOIN growers ON growers.id = goods.grower_id
								LEFT OUTER JOIN goods_photo ON goods_photo.id = goods.avatar_id
								LEFT OUTER JOIN levels ON levels.id = goods.level_id
								LEFT OUTER JOIN levels AS parent_tbl ON parent_tbl.id = levels.parent_id
								WHERE
									goods.published = 1
									AND
									goods.parent_barcode = 0	
								ORDER BY
									goods.popularity_index DESC;
								");
			while($g = mysql_fetch_assoc($qLnk)){
				$goods[$g['id']] = $g;
			}
			
			$excl = array();
			$qLnk = mysql_query(sprintf("
					SELECT
						goods_id,
						COUNT(*) AS the_count
					FROM
						goods_barcodes
					WHERE
						goods_id IN (%s)
						AND
						present = 1
						AND
						weight > 0
						AND
						price > 100
					GROUP BY
						goods_id
					HAVING
						the_count > 0
					",implode(",",array_keys($goods))));
			while($g = mysql_fetch_assoc($qLnk)){
				$excl[] = $g['goods_id'];
			}
						
			foreach($goods as $key => $val){
				if(!in_array($key,$excl)) unset($goods[$key]);
			}
			
			$i = 1;
			foreach($goods as $g){
				$g['class'] = ($i==1) ? 'first' : (($i==3) ? 'last' : '');
				$g['link'] = '/'.$g['parent_level_alias'].'/'.$g['level_alias'].'/'.$g['alias'].'/';
				$this->item_rq('sb_popular',$g);

				if($i==3) break;
				
				$i++;
			}
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
			if(!isset($this->registry['mainpage'])):
				$html = array();
				$link = '';
				$i = 1;
				foreach($this->F_crumbs as $alias => $name){
					$link.= ($i!=1) ? $alias.'/' : $alias;
					$inner_html = ($i==(count($this->F_crumbs))) ? $name : '<a href="'.$link.'">'.$name.'</a>';

					$class = ($i==1) ? 'first' : (($i==(count($this->F_crumbs)) && $i!=1) ? 'last' : '');

					$crumb = '<li class="'.$class.'">'.$inner_html.'</li>';
						if(!($i==(count($this->F_crumbs)))) $crumb.='<li class="divider"><img src="/browser/front/i/crumbs_li.png"></li>';

					$html[]=$crumb;
					$i++;
				}
				$html = '<ul id="crumbs">'.implode('',$html).'</ul>';
				echo $html;
			endif;
		}

		public function F_main_page_module(){
			if(isset($this->registry['mainpage'])){
				$this->item_rq('main_page_module');
			}
		}

		public function F_main_page_module_items(){

			$file = ROOT_PATH.'files/module.txt';
			if(is_file($file)){
				$lines = preg_split("/[\n\r]+/s", file_get_contents($file));
				foreach($lines as $l){
					$arr = explode('::',$l);
					if(count($arr)==3){
						$a['filename'] = $arr[0];
						$a['link'] = $arr[1];
						$a['alt'] = htmlspecialchars($arr[2]);
						$this->item_rq('module_item',$a);
					}
				}
			}

		}

		public function F_sb_news(){
			$qLnk = mysql_query("
								SELECT
									news.name,
									news.date,
									news.alias
								FROM
									news
								WHERE
									news.published = 1
								ORDER BY
									news.date DESC,
									news.id DESC
								LIMIT 3;
								");
			$i = 1;
			$count = mysql_num_rows($qLnk);
			while($n = mysql_fetch_assoc($qLnk)){
				$n['class'] = ($i==$count) ? 'last' : '';
				$this->item_rq('sb_news',$n);
				$i++;
			}
		}

		public function F_dropdown($items_array,$input_name,$id_postfix='',$on_change='',$disabled = false){

			if(count($items_array)>0){

				$add_class = ($disabled) ? 'disabled' : '';

				$id_postfix = ($id_postfix!='') ? $id_postfix : $input_name;

				$items_html = '';

				$i = 0;
				foreach($items_array as $val => $arr){

					if($i==0){
						$active['name'] = $arr['name'];
						$active['val'] = $val;
					}

					if($arr['active']==1){
						$active['name'] = $arr['name'];
						$active['val'] = $val;
					}

					$active_flag = ($arr['active']==1) ? 'active' : '';

					$items_html.='<li class="'.$active_flag.'" val="'.$val.'">'.$arr['name'].'</li>';

					$i++;

				}
				$items_html='<ul class="dropdown-list" id="dropdown_'.$id_postfix.'_list">'.$items_html.'</ul>';

				$filed_name = '<input type="text" class="dropdown_name '.$add_class.'" value="'.$active['name'].'" name="'.$input_name.'_name" id="dropdown_'.$id_postfix.'" readonly="1">';
				$filed_val = '<input type="hidden" value="'.$active['val'].'" name="'.$input_name.'_val" id="dropdown_'.$id_postfix.'_value" onchange="'.$on_change.'">';

				echo '<div class="dropdown_container">'.$filed_name.$filed_val.$items_html.'</div>';
			}

		}

		public function F_tooltip($string){
			$a = explode('$$',$string);
			$this->item_rq('tooltip',$a);
		}

		public function F_main_menu(){
			$menu_arr = array(
				0 => array('','Главная'),
				1 => array('/about/','О магазине'),
				999 => (!isset($_SESSION['user_id'])) ? array('/register/','Регистрация') : array('/profile/','Профиль'),
				7 => array('/pitanie/','Питание'),
				41 => array('/training/','Тренировки'),
				14 => array('/help/','Помощь'),
				145 => array('/help/#send','Доставка'),
				146 => array('/help/#pay','Оплата'),
				6 => array('/contacts/','Контакты')
			);

			$i = 1;
			foreach($menu_arr as $action => $arr){

				$a['first'] = ($i==1) ? 'first' : '';
				$a['active'] = ((isset($this->registry['page']['id']) && $action==$this->registry['page']['id']) || (isset($this->registry['mainpage']) && $i==1) || (isset($this->registry['register_page']) && $action==999)) ? 'active' : '';

				$a['name'] = $arr[1];
				$a['link'] = ($arr[0]=='') ? '/' : $arr[0];

				$this->item_rq('main_menu_item',$a);

				$i++;
			}

		}

		public function F_growers_list_main_page(){
			$qLnk = mysql_query("
								SELECT
									growers.id,
									growers.alias,
									growers.avatar
								FROM
									growers
								WHERE
									growers.goods_count > 0
								ORDER BY
									growers.sort ASC;
								");
			while($g = mysql_fetch_assoc($qLnk)){
				$this->item_rq('growers_carousel_item',$g);
			}
		}

		public function F_faq_select(){
			$select_array = array(
				0 => array('name' => 'FAQ / Ознакомьтесь', 'active' => 0),
				'/faq/#how-buy' => array('name' => 'Как сделать заказ?', 'active' => 0),
				'/faq/#how-send' => array('name' => 'Какие способы доставки есть?', 'active' => 0),
				'/faq/#how-pay' => array('name' => 'Как оплатить заказ?', 'active' => 0),
				'/faq/#how-money' => array('name' => 'Как быстро поступят деньги?', 'active' => 0),
				'/faq/#how-send2' => array('name' => 'Когда Вы отправите заказ?', 'active' => 0),
				'/faq/#how-send2_1' => array('name' => 'Когда я получу уведомление?', 'active' => 0),					
				'/faq/#how-goods-done' => array('name' => 'Есть ли товар в наличии?', 'active' => 0),
				'/faq/#how-shop' => array('name' => 'Есть ли у вас розничный магазин?', 'active' => 0),
				'/faq/#how-self' => array('name' => 'Могу я забрать заказ сам?', 'active' => 0),
				'/faq/#how-cost' => array('name' => 'Как формируются цены?', 'active' => 0),
				'/faq/#how-courier' => array('name' => 'Вы можете отправить заказ EMS?', 'active' => 0),
			);

			$this->F_dropdown($select_array,'s_faq','','goto_faq(this);');
		}

		public function F_grower_select(){

			$select_array = array(
				0 => array('name' => 'Производители', 'active' => 0)
			);

			$qLnk = mysql_query("
								SELECT
									growers.id,
									growers.name,
									growers.alias
								FROM
									growers
								WHERE
									growers.goods_count > 0
								ORDER BY
									growers.name ASC;
								");
			while($g = mysql_fetch_assoc($qLnk)){
				$active = (isset($this->registry['grower']) && $this->registry['grower']['id']==$g['id']) ? 1 : 0;
				$select_array[$g['alias']] = array('name' => $g['name'], 'active' => $active);
			}

			$this->F_dropdown($select_array,'s_growers','','goto_grower(this);');

		}

		public function F_hot_goods(){
			$f_catalog = new f_Catalog($this->registry);
			$this->registry->set('f_catalog',$f_catalog);
			
			$goods = array();
			$qLnk = mysql_query("
								SELECT
									goods.id,
									goods.name,
									goods.alias,
									goods.present,
									goods.new,
									(goods.personal_discount + ".OVERALL_DISCOUNT.") AS personal_discount,
									goods.price_1,
									levels.alias AS level_alias,
									parent_tbl.alias AS parent_level_alias,
									goods_photo.alias AS avatar,
									goods_photo.alt AS alt,
									growers.name AS grower
								FROM
									goods
								LEFT OUTER JOIN goods_photo ON goods_photo.id = goods.avatar_id
								LEFT OUTER JOIN growers ON growers.id = goods.grower_id
								LEFT OUTER JOIN levels ON levels.id = goods.level_id
								LEFT OUTER JOIN levels AS parent_tbl ON parent_tbl.id = levels.parent_id
								WHERE
									goods.published = 1
									AND
									goods.hot = 1
									AND
									goods.weight > 0
								ORDER BY
									RAND()
								LIMIT 6;
								");
			$i = 1;
			while($g = mysql_fetch_assoc($qLnk)) $goods[$g['id']] = $g;
			
			$qLnk = mysql_query(sprintf("
					SELECT
						MIN(price) AS min_price,
						COUNT(*) AS the_count,
						goods_id
					FROM
						goods_barcodes
					WHERE
						goods_id IN (%s)
					GROUP BY
						goods_id
					",
					implode(",",array_keys($goods))
					));
			while($g = mysql_fetch_assoc($qLnk)){
				$goods[$g['goods_id']]['price'] = ($g['the_count']>1)
					? sprintf('от <span>%s</span> руб.',
							Common_Useful::price2read($g['min_price'])
							)
					: sprintf('<span>%s</span> руб.',
							Common_Useful::price2read($g['min_price'])
							);
			}
			
			foreach($goods as $g){
				$g['class'] = ($i%3==0) ? 'r' : '';
				$g['num'] = $i;
				$g['link'] = '/'.$g['parent_level_alias'].'/'.$g['level_alias'].'/'.$g['alias'].'/';
				$this->item_rq('hot_goods',$g);

				$i++;
			}
		}

		public function F_head_cart_info(&$cart_goods_amount,&$cart_sum){
			$goods_ids = array();
			$cart_goods_amount = 0;
			$cart_sum = 0;

			if(isset($_COOKIE['cart'])){
				$cart_arr = explode(':',$_COOKIE['cart']);
				foreach($cart_arr as $goods_string){
					$goods_ids[] = array_shift(explode('|',$goods_string));
					$cart_goods_amount++;
				}

				$goods_tmp_arr = array();
				$qLnk = mysql_query("SELECT goods.id, goods.price_1 - (goods.price_1*".OVERALL_DISCOUNT."/100) AS price_1 FROM goods WHERE goods.id IN (".implode(',',array_unique($goods_ids)).");");
				while($g = mysql_fetch_assoc($qLnk)){
					$goods_tmp_arr[$g['id']] = $g['price_1'];
				}

				foreach($goods_ids as $id){
					$cart_sum+= isset($goods_tmp_arr[$id]) ? $goods_tmp_arr[$id] : 0;
				}

				return true;
			}
		}

		public function F_tech(){
			if(isset($this->registry['page']['tech']) && $this->registry['page']['tech']!='' && method_exists($this,$this->registry['page']['tech'])){
				$f = $this->registry['page']['tech'];
				$this->$f();
			}
		}

		private function F_map(){
			$this->item_rq('map');
		}

		private function F_contacts_form(){
			$this->item_rq('contacts_form');
		}

		public function F_contacts_form_mail(){
			foreach($_POST as $key => $val){$$key = (is_array($val)) ? $val : mysql_real_escape_string($val);}

			$qLnk = mysql_query("SELECT feedback_mail.email FROM feedback_mail WHERE feedback_mail.id = '".$topic_val."' LIMIT 1;");
			if(mysql_num_rows($qLnk)>0){
				$mail_to = mysql_result($qLnk,0);

				$replace_arr = array(
					'F_TOPIC' => $topic_name,
					'F_NAME' => $name,
					'F_EMAIL' => $email,
					'F_MSG' => str_replace('\r\n','<br>',$msg)
				);

				$mailer = new Mailer($this->registry,3,$replace_arr,$mail_to);

			}

		}

		public function F_contacts_form_options(){
			$opts = array(
						0 => array('name' => 'Выберите тему', 'active' => 0)
							);
			$qLnk = mysql_query("
								SELECT
									feedback_mail.id,
									feedback_mail.name
								FROM
									feedback_mail
								ORDER BY
									feedback_mail.sort ASC;
								");
			while($m = mysql_fetch_assoc($qLnk)){
				$opts[$m['id']] = array('name' => $m['name'], 'active' => 0);
			}

			$this->F_dropdown($opts,'topic');
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

		public function get_main_page_content(){
			$qLnk = mysql_query("
								SELECT
									pages.*
								FROM
									pages
								WHERE
									pages.alias = 'mainpage'
								LIMIT 1;
								");
			return mysql_fetch_assoc($qLnk);
		}

		public function F_articles_main_page(){
			$f_articles = new f_Pitanie($this->registry);
			$f_articles->main_page();
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
				echo '<meta name="robots" content="noindex, nofollow">';
	    	}

	    	if(isset($this->registry['seo_kw'])){
				echo '<meta name="keywords" content="'.$this->registry['seo_kw'].'">';
	    	}

	    	if(isset($this->registry['seo_dsc'])){
	    		echo "\r\n";
				echo '<meta name="description" content="'.$this->registry['seo_dsc'].'">';
	    	}

	    	if(isset($this->registry['CL_catalog']) && isset($this->registry['goods'])){
	    		$url = ($this->registry['goods']['canonical'])
	    			? $this->registry['goods']['canonical']
	    			: mb_strtolower($_SERVER['REQUEST_URI'],'utf-8');
	    		$url = trim(THIS_URL,'/').$url;

	    		echo "\r\n";
				echo '<link rel="canonical" href="'.$url.'">';
	    	}else{
	    		$url = explode('?',$_SERVER['REQUEST_URI']);
	    		$url = $url[0];
	    		$url = trim(THIS_URL,'/').$url;
	    		$url = trim($url,'/');
	    		$url = $url.'/';
	    		$url = mb_strtolower($url,'utf-8');

	    		echo "\r\n";
				echo '<link rel="canonical" href="'.$url.'">';
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