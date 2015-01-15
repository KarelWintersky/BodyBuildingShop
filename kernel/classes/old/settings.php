<?
	Class Settings{

		private $registry;
		private $Settings_Indexes;

		public function __construct($registry, $frompage = true){
			$this->registry = $registry;
			
	        if($frompage){
	        	$this->Settings_Indexes = new Settings_Indexes($this->registry);
	        	
		        $route = $this->registry['aias_path'];
		        array_shift($route);

		        if($this->registry['userdata']['type']==1){return false;}

		        if(count($route)==0){
		        	$this->registry['f_404'] = false;
		        	$this->registry['template']->set('c','settings/main');
		        }elseif(count($route)==1 && $this->sub_level_check($route[0])){
		        	$this->registry['template']->set('c','settings/'.$route[0]);
		        	$this->registry['f_404'] = false;
		        }

		        $this->registry['sub_aias_path'] = $route;
	        }

		}

		private function sub_level_check($alias){
			$qLnk = mysql_query("
								SELECT
									main_parts.*
								FROM
									main_parts
								WHERE
									main_parts.parent_id <> 0
									AND
									main_parts.alias = '".$alias."'
								LIMIT 1;
								");
			if(mysql_num_rows($qLnk)>0){
				$this->registry['sub_level_info'] = mysql_fetch_assoc($qLnk);
				return true;
			}
			return false;
		}

		private function item_rq($name,$a = NULL){
			require($this->registry['template']->TF.'item/settings/'.$name.'.html');
		}

		public function sav_mail_tpls(){
			foreach($_POST['tpl'] as $id => $arr){
				mysql_query("UPDATE mailtpls SET mailtpls.name = '".$arr['name']."', mailtpls.subject = '".$arr['subject']."', mailtpls.content = '".$arr['content']."' WHERE mailtpls.id = '".$id."';");
			}
		}

		public function mail_tpls_list(){
			$qLnk = mysql_query("
								SELECT
									mailtpls.*
								FROM
									mailtpls
								ORDER BY
									mailtpls.id ASC;
								");
			while($t = mysql_fetch_assoc($qLnk)){
				$this->item_rq('mail_tpl_item',$t);
			}
		}

		public function sav_order_msgs(){
			foreach($_POST['msg'] as $id => $arr){
				mysql_query("UPDATE order_msgs SET order_msgs.name = '".$arr['name']."', order_msgs.text = '".$arr['text']."' WHERE order_msgs.id = '".$id."';");
			}
		}

		public function order_msgs_list(){
			$qLnk = mysql_query("
								SELECT
									order_msgs.*
								FROM
									order_msgs
								ORDER BY
									order_msgs.id ASC;
								");
			while($t = mysql_fetch_assoc($qLnk)){
				$this->item_rq('order_msg_item',$t);
			}
		}

		public function feedback_mail_list(&$sort){
			$qLnk = mysql_query("
								SELECT
									feedback_mail.*
								FROM
									feedback_mail
								ORDER BY
									feedback_mail.sort ASC;
								");
			$i = 1;
			while($m = mysql_fetch_assoc($qLnk)){
				$m['sort'] = $i;
				$this->item_rq('feedback_mail',$m);

				$i++;
			}

			$sort = $i;
		}

		public function add_mail_opt(){
			foreach($_POST as $key => $val){$$key = (is_array($val)) ? $val : mysql_real_escape_string($val);}

			mysql_query("
						INSERT INTO
							feedback_mail
							(name,
								email,
									sort)
							VALUES
							('".$name."',
								'".$email."',
									'".$sort."');
						");
		}

		public function sav_mail_opts(){

			if(isset($_POST['mail'])){
				foreach($_POST['mail'] as $id => $arr){
					if(isset($arr['del'])){
						mysql_query("DELETE FROM feedback_mail WHERE feedback_mail.id = '".$id."';");
					}else{
						mysql_query("
									UPDATE
										feedback_mail
									SET
										feedback_mail.name = '".$arr['name']."',
										feedback_mail.email = '".$arr['email']."',
										feedback_mail.sort = '".$arr['sort']."'
									WHERE
										feedback_mail.id = '".$id."';
									");
					}
				}
			}

		}

		public function params_list(){
			$qLnk = mysql_query("
								SELECT
									params.*
								FROM
									params
								ORDER BY
									params.name ASC;
								");
			while($p = mysql_fetch_assoc($qLnk)){
				$this->item_rq('params',$p);
			}
		}

		public function sav_params(){
			if(isset($_POST['params']) && count($_POST['params'])>0){
				foreach($_POST['params'] as $name => $value){
					mysql_query("
								UPDATE
									params
								SET
									params.value = '".$value."'
								WHERE
									params.name = '".$name."';
								");
				}
			}
		}

		public function discount_gradations_list(){
			$qLnk = mysql_query("
								SELECT
									discount_gradations.*
								FROM
									discount_gradations
								ORDER BY
									discount_gradations.sum ASC;
								");
			while($g = mysql_fetch_assoc($qLnk)){
				$this->item_rq('discount_gradation',$g);
			}
		}

		public function add_gradation(){
			foreach($_POST as $key => $val){$$key = (is_array($val)) ? $val : mysql_real_escape_string($val);}

			mysql_query("
						INSERT INTO
							discount_gradations
							(discount_gradations.sum,
								discount_gradations.percent)
							VALUES
							('".$sum."',
								'".$percent."');
						");
		}

		public function sav_gradations(){
			if(isset($_POST['dg']) && count($_POST['dg'])>0){
				foreach($_POST['dg'] as $id => $arr){
					if(isset($arr['del'])){
						mysql_query("DELETE FROM discount_gradations WHERE discount_gradations.id = '".$id."';");
					}else{
						mysql_query("
									UPDATE
										discount_gradations
									SET
										discount_gradations.sum = '".$arr['sum']."',
										discount_gradations.percent = '".$arr['percent']."'
									WHERE discount_gradations.id = '".$id."';
									");
					}
				}
			}
		}

		public function admins_list($type){
			$qLnk = mysql_query("
								SELECT
									users.id,
									users.name,
									users.login,
									users.email
								FROM
									users
								WHERE
									users.type = '".$type."'
								ORDER BY
									users.name ASC;
								");
			while($u = mysql_fetch_assoc($qLnk)){
				$this->item_rq('admins_line',$u);
			}
		}

		public function admin_change_type(){
			foreach($_POST as $key => $val){$$key = (is_array($val)) ? $val : mysql_real_escape_string($val);}
			mysql_query("
						UPDATE
							users
						SET
							users.type = '".$type."'
						WHERE
							users.login = '".$login."';
						");
			$this->update_admins_mails();
		}

		private function update_admins_mails(){
			$emails = array();
			$qLnk = mysql_query("SELECT DISTINCT users.email FROM users WHERE users.type IN (1,2);");
			while($e = mysql_fetch_assoc($qLnk)){
				$emails[] = $e['email'];
			}

			mysql_query("UPDATE params SET params.value = '".implode('::',$emails)."' WHERE params.name = 'ADMINS_EMAILS';");
		}

		public function del_user_from_maillist(){
			foreach($_POST as $key => $val){$$key = (is_array($val)) ? $val : mysql_real_escape_string($val);}
			$qLnk = mysql_query("
								UPDATE
									users
								SET
									users.get_news = 0,
									users.get_catalog_changes = 0,
									users.changedon = NOW()
								WHERE
									users.email = '".$email."';
								");
			$result = mysql_affected_rows();

			$msg = ($result>0) ? 'Адрес успешно найден и удален из обеих рассылок' : 'Адрес не найден';

			setcookie('maildeleted',$msg,time()+10,'/');

		}

		public function cron_do_news(){

			$file = ROOT_PATH.'files/news_work_file.txt';
			$news_str =  file_get_contents($file);


			$news_d = explode('::',$news_str);

			$start_time = date('d.m.Y H:i');

			$qLnk = mysql_query("SELECT users.id, users.name, users.email FROM users WHERE users.get_news = 1;");
			$count = 0;
			while($u = mysql_fetch_assoc($qLnk)){

				//$news_text = preg_replace('/\v+|\\\[rn]/','<br/>',$news_d[1]);
				$news_text = $news_d[1];

				$replace_arr = array(
					'USER_NAME' => iconv('utf-8','windows-1251',$u['name']),
					'USER_ID' => $u['id'],
					'NEWS_TEXT' => iconv('utf-8','windows-1251',$news_text),
					'NEWS_TOPIC' => iconv('utf-8','windows-1251',$news_d[0])
				);

				$mailer = new Mailer($this->registry,6,$replace_arr,$u['email'],false,'windows-1251');

				$count++;
			}

			$fin_time = date('d.m.Y H:i');

			$emails = explode('::',ADMINS_EMAILS);
			if(count($emails)>0){
				foreach($emails as $admin_mail){
					$replace_arr = array(
						'MAIL_CHAIN_NAME' => '«Новости»',
						'COUNT' => $count,
						'MAIL_CHAIN_START' => $start_time,
						'MAIL_CHAIN_FIN' => $fin_time,
						'MAIL_TOPICS' => $news_d[0],
					);

					$mailer = new Mailer($this->registry,10,$replace_arr,$admin_mail);
				}
			}

		}

		public function news_send(){
			foreach($_POST as $key => $val){$$key = $val;}

			$file = ROOT_PATH.'files/news_work_file.txt';
			file_put_contents($file,$news_topic.'::'.$news_text);
			exec('/usr/local/bin/php /Web/WebHosting/whbody2/data/kernel/cron.php do_news',$output);

		}

		public function module_txt_file(){
			$file = ROOT_PATH.'files/module.txt';
			if(is_file($file)){
				echo file_get_contents($file);
			}
		}

		public function sav_module_file(){
			$file = ROOT_PATH.'files/module.txt';

			$fh = fopen($file, 'w');
			$lines = preg_split("/[\n\r]+/s", $_POST['content']);
			foreach($lines as $l){
				fwrite($fh, $l."\r\n");
			}
			fclose($fh);

		}

		public function yandex_market_xml(){

			$output_files = array(
					1 => ROOT_PATH.'public_html/y_market.xml',
					2 => ROOT_PATH.'public_html/y_market_2.xml',
					);

			foreach($output_files as $num => $file){

				$generate_date = date('Y-m-d H:i',time());

				$this->SM = new DOMDocument('1.0','UTF-8');
				$this->SM->formatOutput = true;

				$this->SM_ymlcatalog = $this->SM->createElement('yml_catalog');
				$this->SM_ymlcatalog->appendChild(
						$this->SM->createAttribute('date'))->appendChild(
								$this->SM->createTextNode($generate_date)
						);
				$this->SM->appendChild($this->SM_ymlcatalog);

				$this->SM_shop = $this->SM->createElement('shop');
				$this->SM_ymlcatalog->appendChild($this->SM_shop);

				$shop_name = $this->SM->createElement('name',THIS_URL);
				$this->SM_shop->appendChild($shop_name);

				$shop_company = $this->SM->createElement('company','Бодибилдинг Магазин');
				$this->SM_shop->appendChild($shop_company);

				$shop_url = $this->SM->createElement('url',THIS_URL);
				$this->SM_shop->appendChild($shop_url);

				$currencies = $this->SM->createElement('currencies');
				$this->SM_shop->appendChild($currencies);

				$currency = $this->SM->createElement('currency');
				$currency->appendChild(
						$this->SM->createAttribute('id'))->appendChild(
								$this->SM->createTextNode('RUR')
						);
				$currency->appendChild(
						$this->SM->createAttribute('rate'))->appendChild(
								$this->SM->createTextNode(1)
						);
				$currencies->appendChild($currency);

				$this->mk_y_categories(); //дерево разделов магазина
				$this->mk_y_goods($num); //дерево товаров магазина

				$this->SM->save($file);
			}

		}

		private function mk_y_goods($num){
			$data = array();
			
			$qLnk = mysql_query("
					SELECT
						goods_barcodes.barcode,
						goods.id,
						goods.name,
						goods.introtext,
						goods_barcodes.packing,
						goods_barcodes.feature,
						goods.present,
						goods_barcodes.price,
						goods.level_id,
						goods.alias,
						levels.alias AS level_alias,
						parent_tbl.alias AS parent_alias,
						growers.name AS grower					
					FROM
						goods_barcodes
					INNER JOIN goods ON goods_barcodes.goods_id = goods.id
					INNER JOIN levels ON levels.id = goods.level_id
					INNER JOIN levels AS parent_tbl ON parent_tbl.id = levels.parent_id	
					LEFT OUTER JOIN growers ON growers.id = goods.grower_id				
					WHERE
						goods_barcodes.weight > 0
						AND
						goods.published = 1
						AND
						goods_barcodes.present = 1
						AND
						goods.name <> ''
						AND
						goods_barcodes.price >0
						AND
						(goods.parent_barcode = '' OR goods.parent_barcode = '0') 
						AND
						levels.published = 1
						AND
						levels.id NOT IN (19,22,21,20,23,2)
					ORDER BY
						goods.level_id ASC,
						goods.sort ASC;										
					");
			
			/*$qLnk = mysql_query("
								SELECT
									goods.id,
									goods.barcode,
									goods.name,
									goods.introtext,
									goods.packing,
									goods.present,
									goods.price_1,
									goods.level_id,
									goods.introtext,
									goods.alias,
									levels.alias AS level_alias,
									parent_tbl.alias AS parent_alias,
									growers.name AS grower
								FROM
									goods
								INNER JOIN levels ON levels.id = goods.level_id
								INNER JOIN levels AS parent_tbl ON parent_tbl.id = levels.parent_id
								LEFT OUTER JOIN growers ON growers.id = goods.grower_id
								WHERE
									goods.published = 1
									AND
									goods.weight > 0
									AND
									goods.present = 1
									AND
									goods.name <> ''
									AND
									goods.price_1 > 0
									AND
									levels.published = 1
									AND
									levels.id NOT IN (19,22,21,20,23,2)
								ORDER BY
									goods.level_id ASC,
									goods.sort ASC;
								");*/
			while($g = mysql_fetch_assoc($qLnk)){
				$data[] = $g;
			}

			$photos = $this->get_goods_photos($data);

			$goods = $this->SM->createElement('offers');
				$this->SM_shop->appendChild($goods);

			foreach($data as $goods_arr){

				$present = ($goods_arr['present']==1) ? 'true' : 'false';
				$url = THIS_URL.$goods_arr['parent_alias'].'/'.$goods_arr['level_alias'].'/'.$goods_arr['alias'].'/';

				if($num==1){
					$name = str_replace('&','and',$goods_arr['name']);
					$name = ($goods_arr['feature']!='') ? $name.', '.$goods_arr['feature'] : $name;
					$name = ($goods_arr['packing']!='') ? $name.', '.$goods_arr['packing'] : $name;
					$name = ($goods_arr['grower']!='') ? '«'.$goods_arr['grower'].'». '.$name : $name;
				}else{
					$name = str_replace('&','and',$goods_arr['name']);
				}

				$vendor = ($goods_arr['grower']!='') ? $goods_arr['grower'] : 'Суперсет';

				$intro = htmlspecialchars(str_replace('&','and',$goods_arr['introtext']));

				$item = $this->SM->createElement('offer');
				$item->appendChild(
										$this->SM->createAttribute('id'))->appendChild(
											$this->SM->createTextNode($goods_arr['barcode'])
											);
				$item->appendChild(
										$this->SM->createAttribute('available'))->appendChild(
											$this->SM->createTextNode($present)
											);
				$item->appendChild(
										$this->SM->createElement('url',$url)
											);
				$item->appendChild(
										$this->SM->createElement('price',$goods_arr['price'])
											);
				$item->appendChild(
										$this->SM->createElement('currencyId','RUR')
											);
				$item->appendChild(
										$this->SM->createElement('categoryId',$goods_arr['level_id'])
											);

				if(isset($photos[$goods_arr['id']])){
					foreach($photos[$goods_arr['id']] as $picture){
						
						//$picture = str_replace(' ','%20',$picture);
						
						$item->appendChild(
												$this->SM->createElement('picture',THIS_URL.'data/foto/goods/src/'.$goods_arr['id'].'/'.rawurlencode($picture))
													);
					}
				}

				$item->appendChild(
										$this->SM->createElement('delivery','true')
											);
				$item->appendChild(
										$this->SM->createElement('name',$name)
											);
				$item->appendChild(
										$this->SM->createElement('description',$intro)
											);

				$item->appendChild(
										$this->SM->createElement('sales_notes','Необходима предоплата')
											);

				if($num==2){
					$item->appendChild(
							$this->SM->createElement('vendor',$vendor)
					);
					$item->appendChild(
							$this->SM->createElement('model',$goods_arr['packing'])
					);
				}

				$item->appendChild(
										$this->SM->createElement('barcode',$goods_arr['barcode'])
											);

				$goods->appendChild($item);
			}

		}

		private function get_goods_photos($data){
			$ids = array();
			foreach($data as $g) $ids[] = $g['id'];
			$ids = array_unique($ids);
			
			$photo = array();
			$qLnk = mysql_query("
								SELECT
									goods_photo.goods_id,
									goods_photo.alias
								FROM
									goods_photo
								WHERE
									goods_photo.goods_id IN (".implode(",",$ids).")
								ORDER BY
									goods_photo.goods_id ASC,
									goods_photo.sort ASC;
								");
			while($ph = mysql_fetch_assoc($qLnk)){
				$photo[$ph['goods_id']][] = $ph['alias'];
			}
			
			foreach($photo as $goods_id => $items) $photo[$goods_id] = array_slice($items,0,10);
			
			return $photo;
		}

		private function mk_y_categories(){
			$items = array();
			$qLnk = mysql_query("
								SELECT
									levels.id,
									levels.parent_id,
									levels.name
								FROM
									levels
								WHERE
									levels.published = 1
									AND
									levels.id NOT IN (19,22,21,20,23,2)
								ORDER BY
									levels.parent_id ASC,
									levels.sort ASC;
								");
			while($l = mysql_fetch_assoc($qLnk)){
				$items[$l['id']] = array(
										'name' => $l['name'],
										'parent_id' => $l['parent_id'],
										);
			}

			$categories = $this->SM->createElement('categories');
				$this->SM_shop->appendChild($categories);

			foreach($items as $id => $i){
				$level = $this->SM->createElement('category',$i['name']);
				$level->appendChild(
										$this->SM->createAttribute('id'))->appendChild(
											$this->SM->createTextNode($id)
											);
				$level->appendChild(
										$this->SM->createAttribute('parentId'))->appendChild(
											$this->SM->createTextNode($i['parent_id'])
											);
				$categories->appendChild($level);
			}

		}

		public function get_fdata($file){
			$file = ROOT_PATH.'public_html/'.$file;
			if(is_file($file)){
				$modified = filemtime($file);
				echo date('d.m.Y в H:i',$modified);
			}else{
				echo 'файл еще не сгенерирован';
			}
		}

		public function coupons_list(){
			$qLnk = mysql_query("
								SELECT
									coupons.*,
									users_cr.name AS created_user,
									users_us.name AS used_user
								FROM
									coupons
								INNER JOIN users AS users_cr ON users_cr.id = coupons.createdby
								LEFT OUTER JOIN users AS users_us ON users_us.id = coupons.usedby
								ORDER BY
									coupons.createdon DESC;
								");
			while($c = mysql_fetch_assoc($qLnk)){

				$c['order_link'] = str_replace('/','-',$c['order_id']);

				$this->item_rq('coupons_list',$c);
			}
		}

		public function coupons_statuses_list($current){
			$statuses = array(
				1 => 'активен',
				2 => 'аннулирован по времени',
				3 => 'аннулирован администратором',
				4 => 'использован'
			);

			foreach($statuses as $id => $val){
				$sel = ($current==$id) ? 'selected' : '';
				echo '<option '.$sel.' value="'.$id.'">'.$val.'</option>';
			}

		}

		public function coupons_list_sav(){

			if(isset($_POST['coupon'])){
				foreach($_POST['coupon'] as $id => $data){
					if($data['status']!=$data['status_old']){
						mysql_query("UPDATE coupons SET status = '".$data['status']."' WHERE id = '".$id."';");
					}
				}
			}

		}

		private function mk_coupon_hash(){

			do{
				$rand = rand(0,time());
					$rand=$rand.md5(time());
				$hash = mb_substr(md5($rand),0,5,'utf-8');
			}while(!$this->coupon_hash_loop($hash));

			return $hash;
		}

		private function coupon_hash_loop($hash){
			$qLnk = mysql_query("SELECT COUNT(*) FROM coupons WHERE hash = '".$hash."'");
			return (mysql_result($qLnk,0)>0) ? false : true;
		}

		public function coupon_add(){
			foreach($_POST as $key => $val){$$key = $val;}

			$hash = $this->mk_coupon_hash();

			mysql_query("
						INSERT INTO
							coupons
							(hash,
								createdby,
									createdon,
										validtill,
											percent)
							VALUES
							('".$hash."',
								'".$this->registry['userdata']['id']."',
									NOW(),
										'".date('Y-m-d',strtotime($validtill))."',
											'".$percent."');
						");
		}

		public function ostatki_list(){
			$qLnk = mysql_query("
								SELECT
									ostatki.*,
									goods.name AS goods_name,
									goods.barcode AS barcode,
									goods.level_id AS goods_level_id,
									levels.parent_id AS goods_parent_id,
									users.name AS added_by_name,
									(SELECT COUNT(*) FROM rezerv WHERE rezerv.ostatok_id = ostatki.id) AS rezerv_count
								FROM
									ostatki
								INNER JOIN goods ON goods.id = ostatki.goods_id
								INNER JOIN levels ON levels.id = goods.level_id
								LEFT OUTER JOIN users ON users.id = ostatki.added_by
								ORDER BY
									ostatki.added_on DESC
								");
			while($o = mysql_fetch_assoc($qLnk)){
				$this->item_rq('ostatki_list',$o);
			}
		}

		public function rezerv_list(){
			$qLnk = mysql_query("
								SELECT
									rezerv.*,
									goods.barcode AS goods_barcode,
									goods.name AS goods_name,
									goods.id AS goods_id,
									goods.level_id AS goods_level_id,
									levels.parent_id AS goods_parent_id
								FROM
									rezerv
								INNER JOIN ostatki ON ostatki.id = rezerv.ostatok_id
								INNER JOIN goods ON goods.id = ostatki.goods_id
								INNER JOIN levels ON levels.id = goods.level_id
								ORDER BY
									rezerv.date DESC;
								");
			while($r = mysql_fetch_assoc($qLnk)){

				$r['order_link_alias'] = str_replace('/','-',$r['order_id']);

				$this->item_rq('rezerv_list',$r);
			}
		}


		public function ostatok_blocks_add(){

			$lines = preg_split("/[\n\r]+/s", $_POST['block']);

			foreach($lines as $l){
				$data = explode('::',$l);
				if(count($data)==2){
					$this->ostatok_add($data[1],$data[0]);
				}
			}


		}

		public function ostatok_add_init(){
			foreach($_POST as $key => $val){$$key = $val;}

			$this->ostatok_add($value,$barcode);
		}

		public function ostatok_add($value,$barcode){

			if($barcode!='' && $value!=''){
				$qLnk = mysql_query("
									SELECT
										goods.id
									FROM
										goods
									WHERE
										goods.barcode = '".$barcode."'
									LIMIT 1;
									");
				if(mysql_num_rows($qLnk)>0){
					$goods_id = mysql_result($qLnk,0);

					$qLnk = mysql_query("
										UPDATE
											ostatki
										SET
											ostatki.value = ostatki.value + ".$value."
										WHERE
											ostatki.goods_id = '".$goods_id."';
										");
					if(mysql_affected_rows()==0){
						mysql_query("
									INSERT INTO
										ostatki
										(goods_id, value, added_on, added_by)
										VALUES
										('".$goods_id."', '".$value."', NOW(), '".$this->registry['userdata']['id']."');
									");
					}

				}
			}

		}

		public function order_cancel($id_arr){
			$ostatki = array();
			$qLnk = mysql_query("
								SELECT
									rezerv.*
								FROM
									rezerv
								WHERE
									rezerv.order_id = '".implode('/',$id_arr)."'
								");
			while($r = mysql_fetch_assoc($qLnk)){
				mysql_query("
							UPDATE
								ostatki
							SET
								ostatki.value = ostatki.value + '".$r['amount']."'
							WHERE
								ostatki.id = '".$r['ostatok_id']."'
							");
				$ostatki[] = $r['ostatok_id'];
			}
			if(count($ostatki)>0){
				$qLnk = mysql_query("SELECT ostatki.goods_id FROM ostatki WHERE ostatki.id IN (".implode(",",$ostatki).");");
				while($o = mysql_fetch_assoc($qLnk)){
					mysql_query("UPDATE goods SET goods.present = 1 WHERE goods.id = '".$o['goods_id']."'");
				}
			}

			mysql_query("DELETE FROM rezerv WHERE rezerv.order_id = '".implode('/',$id_arr)."';");
		}

		public function order_apply($id_arr){
			mysql_query("DELETE FROM rezerv WHERE rezerv.order_id = '".implode('/',$id_arr)."'");
		}

		public function do_rezerv_orders(){
			//функция запускается по крону раз в сутки - отменяем заказы по резервам за опр. кол-во дней
			$qLnk = mysql_query("
								SELECT DISTINCT
									rezerv.order_id
								FROM
									rezerv
								WHERE
									DATE(rezerv.date) < DATE(DATE_SUB(DATE(NOW()),INTERVAL ".REZERV_ORDER_DAYS." DAY))
								");
			while($r = mysql_fetch_assoc($qLnk)){
				$id_arr = explode('/',$r['order_id']);
				$this->order_cancel($id_arr);
			}
		}

		public function ostatki_sav(){
			if(isset($_POST['ostatok'])){
				foreach($_POST['ostatok'] as $ostatok_id => $data){
					if(isset($data['del'])){
						mysql_query("DELETE FROM ostatki WHERE ostatki.id = '".$ostatok_id."';");
						mysql_query("DELETE FROM rezerv WHERE rezerv.ostatok_id = '".$ostatok_id."';");
					}else{
						mysql_query("
									UPDATE
										ostatki
									SET
										ostatki.value = '".$data['val']."'
									WHERE
										ostatki.id = '".$ostatok_id."'
									");
					}
				}
			}
		}

		public function dp_params_sav(){
			foreach($_POST['params'] as $name => $value)
				mysql_query(sprintf("
						UPDATE
							dp_params
						SET
							value = '%s'
						WHERE
							name = '%s';
						",
						mysql_real_escape_string($value),
						$name
						));
		}
		
		public function dp_params_list(){
			$qLnk = mysql_query("SELECT * FROM dp_params");
			while($p = mysql_fetch_assoc($qLnk)){
				$this->item_rq('dp_param',$p);
			}
		}
		
	}
?>