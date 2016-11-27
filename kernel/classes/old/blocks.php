<?
Class Blocks{

	private $registry;

	public function __construct($registry, $frompage = true){
		$this->registry = $registry;
		$this->registry->set('blocks',$this);

        if($frompage){
	        $route = $this->registry['aias_path'];
	        array_shift($route);

	        if(count($route)==0){
	        	$this->registry['f_404'] = false;
	        	$this->registry['template']->set('c','blocks/main');

	        	$this->go_blocks();
	        }
        }

	}

	private function item_rq($name,$a = NULL){
		require(ROOT_PATH.'tpl/front/item/blocks/'.$name.'.html');
	}

	private function go_blocks(){
		if(count($_POST)>0 && isset($_POST['d_action'])){
			set_time_limit(0);
			switch($_POST['d_action']){
				case 700:
					$this->do_orders();
					break;
				case 701:
					$this->do_goods_presence();
					break;
				case 702:
					$this->do_goods_absence();
					break;
				case 703:
					$this->do_goods_prices();
					break;
			}
		}
	}

	public function cron_do_goods_absence(){

		$file = ROOT_PATH.'files/blocks_work_file.txt';
		$blocks_str = file_get_contents($file);
		$blocks_arr = explode('$$',$blocks_str);

		$q_unpub = ($blocks_arr[0]==1);

		$feedback = array();

		$lines = preg_split("/[\n\r]+/s", $blocks_arr[1]);

		$count = count($lines);
		if($lines[$count-1]==''){
			unset($lines[$count-1]);
		}

		foreach($lines as $l){

			$A = explode('::',$l);

			if(count($A)==6){
				mysql_query(sprintf("
						UPDATE 
							goods_barcodes 
						SET 
							modified = NOW(), 
							present = 0
						WHERE
							barcode = '%s';",
						$A[0]
						));
				if(mysql_affected_rows()>0){
					
					if($q_unpub){
						$id = mysql_result(mysql_query(sprintf("SELECT goods_id FROM goods_barcodes WHERE barcode = '%s'",$A[0])),0);
						
						mysql_query(sprintf("UPDATE goods SET published = 0 WHERE id = '%s';",$id));
					}
					
					$feedback[] = ($blocks_arr[0]==0) ? 'товар '.$A[1].' ('.$A[0].') помечен как отсутствующий$$1' : 'товар '.$A[1].' ('.$A[0].') изъят из продажи$$1';
				}else{
					$feedback[] = 'товар '.$A[1].' ('.$A[0].') не найден$$0';
				}
			}else{
				$feedback[] = 'неверный формат блока$$0';
			}
		}

		//$exceldoer = new Exceldoer($this->registry);
		//$exceldoer->price_list();

		$settings = new Settings($this->registry,false);
		$settings->yandex_market_xml();

		$this->mail_orders($feedback);

		foreach($feedback as $f){
			echo $f."\n";
		}

	}

	public function do_goods_absence(){

		$unpub = (isset($_POST['unpublish'])) ? 1 : 0;

		$file = ROOT_PATH.'files/blocks_work_file.txt';
		file_put_contents($file,$unpub.'$$'.$_POST['blocks_field']);
		exec('/usr/bin/php /home/wwwuser/www/bodybuilding-shop.ru/kernel/cron.php do_goods_absence',$output);

		$reply[3] = $output;
		$this->registry['reply_arr'] = $reply;
	}

	public function do_goods_presence(){
		if(isset($_POST['check_presence'])){
			$this->check_goods_presence();
		}else{
			$this->go_goods_present();
		}
	}

	private function check_goods_presence(){	
		$feedback = array();

		$lines = preg_split("/[\n\r]+/s", $_POST['blocks_field']);

		$count = count($lines);
		if($lines[$count-1]==''){
			unset($lines[$count-1]);
		}

		$etal_name = '';
		$etal_barcode = '';
		foreach($lines as $l){

			$A = explode('::',$l);

			if(count($A)==6){
				
				$name_arr = explode(',',$A[1]);
				$name = (count($name_arr)>1) ? trim($name_arr[0]).', '.trim($name_arr[1]) : trim($name_arr[0]);

				$first_line = ($name!=$etal_name) ? 1 : 0;

				$etal_name = $name;

				$qLnk = mysql_query("SELECT present FROM goods_barcodes WHERE barcode = '".$A[0]."' LIMIT 1;");
				if(mysql_num_rows($qLnk)>0){
					$g = mysql_fetch_assoc($qLnk);

					if($g['present']==1){
						$pr = 'в наличии';
						$s = 1;
					}else{
						$pr = 'отсутствует в продаже';
						$s = 2;
					}

					$feedback[] = $A[1].', '.$A[2].' &nbsp;&nbsp;-&nbsp;&nbsp; '.$pr.' ('.$A[0].')$$'.$s.'$$'.$first_line;
				}else{
					$feedback[] = $A[1].', '.$A[2].' &nbsp;&nbsp;-&nbsp;&nbsp; не найден ('.$A[0].')$$0$$'.$first_line;
				}
			}else{
				$feedback[] = 'неверный формат блока$$0';
			}

		}

		$reply[2] = $feedback;
		$this->registry['reply_arr'] = $reply;

	}

	public function cron_do_goods_prices(){

		$file = ROOT_PATH.'files/blocks_work_file.txt';
		$blocks_field = file_get_contents($file);

		$feedback = array();

		$lines = preg_split("/[\n\r]+/s", $blocks_field);

		$count = count($lines);
		if($lines[$count-1]==''){
			unset($lines[$count-1]);
		}

		foreach($lines as $l){
			$A = explode('::',$l);

			if(count($A)==6){

				mysql_query(sprintf("
									UPDATE
										goods_barcodes
									SET
										modified = NOW(),
										price = '%s'
									WHERE
										barcode = '%s'
									",
									intval($A[3]*0.7),
									$A[0]
									));

				if(mysql_affected_rows()>0){
					$feedback[] = 'цена на товар '.$A[1].' ('.$A[0].') изменена (новая цена '.intval($A[3]).' руб)$$1';
				}else{
					$feedback[] = 'товар '.$A[1].' ('.$A[0].') не найден$$0';
				}

			}else{
				$feedback[] = 'неверный формат блока$$0';
			}
		}

		$settings = new Settings($this->registry,false);
		$settings->yandex_market_xml();		
		
		$this->mail_orders($feedback);

		foreach($feedback as $f){
			echo $f."\n";
		}
	}

	public function do_goods_prices(){

		$file = ROOT_PATH.'files/blocks_work_file.txt';
		file_put_contents($file,$_POST['blocks_field']);
		exec('/usr/bin/php /home/wwwuser/www/bodybuilding-shop.ru/kernel/cron.php do_goods_prices',$output);

		$reply[4] = $output;
		$this->registry['reply_arr'] = $reply;

	}

	private function mail_goods_presence($lines){
		foreach($lines as $l){
			$A = explode('::',$l);

			if(count($A)==6){
				$goods_arr[] = "'".$A[0]."'";
			}
		}

		$ids = array();
		$qLnk = mysql_query(sprintf("
				SELECT
					goods_id,
					MIN(price) AS price
				FROM
					goods_barcodes
				WHERE
					barcode IN (%s)
				GROUP BY
					goods_id
				",
				implode(", ",$goods_arr)
				));
		while($g = mysql_fetch_assoc($qLnk)) $ids[$g['goods_id']] = $g['price'];
		
		if(count($ids)==0) return false;
		
		$qLnk = mysql_query(sprintf("
							SELECT
								goods.id,
								goods.name,
								growers.name AS grower_name,
								goods.alias,
								levels.alias AS level_alias,
								parent_tbl.alias AS parent_alias
							FROM
								goods
							LEFT OUTER JOIN growers ON growers.id = goods.grower_id
							LEFT OUTER JOIN levels ON levels.id = goods.level_id
							LEFT OUTER JOIN levels AS parent_tbl ON parent_tbl.id = levels.parent_id
							WHERE
								goods.id IN (%s)
							ORDER BY Field(goods.id, %s);
							",implode(", ",array_keys($ids)),implode(", ",array_keys($ids))));
		if(mysql_num_rows($qLnk)>0){

			ob_start();
			$this->item_rq('thead');
			while($g = mysql_fetch_assoc($qLnk)){
				$g['min_price'] = $ids[$g['id']];
				$g['goods_full_name'] = (($g['grower_name']!='') ? '«'.$g['grower_name'].'». ' : '').$g['name'];
				$this->item_rq('tline',$g);
			}
			echo '</table>';
			$goods_table = ob_get_contents();
			ob_end_clean();

		}
		
		$qLnk = mysql_query("SELECT users.id, users.name, users.email FROM users WHERE users.get_catalog_changes = 1;");
		$count = 0;
		while($u = mysql_fetch_assoc($qLnk)){

			$replace_arr = array(
				'USER_NAME' => $u['name'],
				'USER_ID' => $u['id'],
				'GOODS_TABLE' => $goods_table
			);

			$mailer = new Mailer($this->registry,7,$replace_arr,$u['email']);

			$count++;
		}

		$emails = explode('::',ADMINS_EMAILS);
		if(count($emails)>0){
			foreach($emails as $admin_mail){
				$replace_arr = array(
					'MAIL_CHAIN_NAME' => '«Наличие товаров»',
					'COUNT' => $count,
				);

				$mailer = new Mailer($this->registry,10,$replace_arr,$admin_mail);
			}
		}


	}

	public function cron_go_goods_present(){

		$file = ROOT_PATH.'files/blocks_work_file.txt';
		$blocks_field = file_get_contents($file);

		$feedback = array();

		$lines = preg_split("/[\n\r]+/s", $blocks_field);

		$count = count($lines);
		if($lines[$count-1]==''){
			unset($lines[$count-1]);
		}

		foreach($lines as $l){
			$A = explode('::',$l);		
			
			if(count($A)==6){

				mysql_query(sprintf("
						UPDATE
							goods_barcodes
						SET
							price = '%s',
							present = 1,
							modified = NOW()
						WHERE
							barcode = '%s'
						",
						intval($A[3]*0.7),
						$A[0]
						));
				
				if(mysql_affected_rows()>0){
					$feedback[] = 'товар '.$A[1].' ('.$A[0].') помечен как "в наличии"$$1';
				}else{
					$feedback[] = 'товар '.$A[1].' ('.$A[0].') не найден$$0';
				}

			}else{
				$feedback[] = 'неверный формат блока$$0';
			}
		}

		//$exceldoer = new Exceldoer($this->registry);
		//$exceldoer->price_list();

		$settings = new Settings($this->registry,false);
		$settings->yandex_market_xml();

		$this->mail_goods_presence($lines);

		$this->mail_orders($feedback);

		foreach($feedback as $f){
			echo $f."\n";
		}
	}

	public function go_goods_present(){

		$file = ROOT_PATH.'files/blocks_work_file.txt';
		file_put_contents($file,$_POST['blocks_field']);
		exec('/usr/bin/php /home/wwwuser/www/bodybuilding-shop.ru/kernel/cron.php go_goods_present',$output);

		$reply[2] = $output;
		$this->registry['reply_arr'] = $reply;

	}

	private function mail_orders($output){
		$emails = explode('::',ADMINS_EMAILS);
		if(count($emails)>0){

			//подготавливаем аутпут по цветам
			$new_output;
			foreach($output as $ln){
				$ln_arr = explode('$$',$ln);
				$color = (isset($ln_arr[1]) && $ln_arr[1]==1) ? 'green' : 'red';

				$w_link = str_replace('<a href="','<a href="http://www.bodybuilding-shop.ru',$ln_arr[0]);

				$new_output[] = '<font color="'.$color.'">'.$w_link.'</font>';
			}

			foreach($emails as $admin_mail){
				$replace_arr = array(
					'DATA' => iconv('utf-8','windows-1251',implode('<br>',$new_output)),
				);

				$mailer = new Mailer($this->registry,31,$replace_arr,$admin_mail,false,'windows-1251');

			}
		}
	}

	public function do_orders(){
		$file = ROOT_PATH.'files/blocks_work_file.txt';
		file_put_contents($file,$_POST['blocks_field']);
		exec('/usr/bin/php /home/wwwuser/www/bodybuilding-shop.ru/kernel/cron.php do_orders',$output);

		$reply[1] = $output;
		$this->registry['reply_arr'] = $reply;

	}


	public function cron_do_orders(){

		$file = ROOT_PATH.'files/blocks_work_file.txt';
		$blocks_field = file_get_contents($file);

		$feedback = array();

		$lines = preg_split("/[\n\r]+/s", $blocks_field);

		$count = count($lines);
		if($lines[$count-1]==''){
			unset($lines[$count-1]);
		}

		foreach($lines as $nkey => $l){
			$A = explode('::',$l);

			if(count($A)==4 || count($A)==5){

				$date = date('Y-m-d',strtotime($A[0]));
				$order_arr = explode('/',$A[1]);
					$order_id = $order_arr[0];
					$order_user_count = $order_arr[1];
					$order_type = str_replace('H','Н',$order_arr[2]);
				$status = $A[2];
				$comment = $A[3];
				$postnum = (isset($A[4]) && $A[4]!='') ? $A[4] : false;

				if($status==5){

					$comment = $comment.' ('.$A[0].')';

					//меняем человеку наложку и записываем комментарий
					mysql_query("
								UPDATE
									orders
								SET
									orders.modified = NOW(),
									orders.comment = CONCAT_WS(', ',orders.comment,'".$comment."')
								WHERE
									orders.id = '".$order_id."'
									AND
									orders.user_num	= '".$order_user_count."'
									AND
									orders.payment_method = '".$order_type."'
								");
					if(mysql_affected_rows()>0){
						$order_lnk = '/adm/orders/'.$order_id.'-'.$order_user_count.'-'.$order_type.'/';
						$feedback[] = $l.' (заказ <a href="'.$order_lnk.'">'.$A[1].'</a> не изменен (статус 5 - увеличение наложки))$$1';
					}else{
						$feedback[] = $l.' (заказ с номером '.$A[1].' не найден)$$0';
					}

					$mail_nalog = $this->order_nalog($order_arr,$status);

					$mail_discount = false;

					$this->mail_user_data_change($mail_nalog,$mail_discount,$order_arr);

				}else{

					if($status==0){
						$ord_status = 2;
						$status_text = 'отправлен';
						$date_q = ", orders.sent_on = '".$date."'";
					}elseif($status==1){
						$ord_status = 3;
						$status_text = 'оплачен';
						$date_q = ", orders.payed_on = '".$date."'";
					}elseif($status==2){
						$ord_status = 3;
						$status_text = 'оплачен';
						$date_q = ", orders.payed_on = '".$date."', orders.sent_on = '".$date."'";
					}elseif($status==3 || $status==4){
						$ord_status = 4;
						$status_text = 'отменен';
						$date_q = "";
					}elseif($status==6){
						$ord_status = 5;
						$status_text = 'деньги поступили '.date('d.m.Y',strtotime($date));
						$comment.=' '.date('d.m.Y',strtotime($date));
						$date_q = ", orders.payed_on = '".$date."'";
					}

					$postnum_q = ($postnum) ? ", orders.postnum = '".$postnum."'" : "";

					mysql_query("
								UPDATE
									orders
								SET
									orders.modified = NOW(),
									orders.status = '".$ord_status."',
									orders.comment = CONCAT_WS(', ',orders.comment,'".$comment."')
									".$date_q."
									".$postnum_q."
								WHERE
									orders.id = '".$order_id."'
									AND
									orders.user_num	= '".$order_user_count."'
									AND
									orders.payment_method = '".$order_type."'
								");

					if(mysql_affected_rows()>0){
						$order_lnk = '/adm/orders/'.$order_id.'-'.$order_user_count.'-'.$order_type.'/';
						$feedback[] = $l.' (статус заказа <a href="'.$order_lnk.'">'.$A[1].'</a> изменен на «'.$status_text.'»)$$1';
					}else{
						$feedback[] = $l.' (заказ с номером '.$A[1].' не найден)$$0';
					}

					$Front_Order_Write_Ostatok = new Front_Order_Write_Ostatok($this->registry);

					if($ord_status==4){//если заказ отменен, смотрим если ли в нем товары по остаткам
						$Front_Order_Write_Ostatok->unhappyRemoveReserve($A[1]);
					}elseif($ord_status==3){//если заказ оплачен, удаляем резерв
						$Front_Order_Write_Ostatok->succesfullyRemoveReserve($A[1]);
					}

					$mail_nalog = $this->order_nalog($order_arr,$status);

					$this->order_goods_rate($A[1],$status);

					$mail_discount = $this->mk_discount($order_arr,$status);

					$this->mail_user_data_change($mail_nalog,$mail_discount,$order_arr);

					$this->get_money($status,$order_arr);

				}

			}else{
				$feedback[] = $l.' (неверный формат блока)$$0';
			}

		}

		$this->mail_orders($feedback);

		foreach($feedback as $f){
			echo $f."\n";
		}

	}

	private function get_money($status,$order_arr){
		if($status==6){

			$order_id = $order_arr[0];
			$order_user_count = $order_arr[1];
			$order_type = str_replace('H','Н',$order_arr[2]);

			$qLnk = mysql_query("
								SELECT
									users.id,
									users.name,
									users.email
								FROM
									users
								WHERE
									users.id > 0
									AND
									users.id = (
										SELECT
											orders.user_id
										FROM
											orders
										WHERE
											orders.id = '".$order_id."'
											AND
											orders.user_num	= '".$order_user_count."'
											AND
											orders.payment_method = '".$order_type."'
									)
								");

			if(mysql_num_rows($qLnk)>0){
				$ud = mysql_fetch_assoc($qLnk);

				$replace_arr = array(
					'USER_NAME' => $ud['name'],
					'ORDER_NUM' => implode('/',$order_arr)
				);

				if(filter_var($ud['email'],FILTER_VALIDATE_EMAIL)){
					$mailer = new Mailer($this->registry,38,$replace_arr,$ud['email']);
				}
			}
		}
	}

	public function blocks_reply($t){
		if(isset($this->registry['reply_arr'][$t])){
			foreach($this->registry['reply_arr'][$t] as $reply_line){
				$la = explode('$$',$reply_line);

				$class = (isset($la[1]) && $la[1]==1) ? 'success' : (isset($la[1]) && ($la[1]==0) ? 'failure' : 'failure_1');
				$first = (isset($la[2]) && $la[2]==1) ? 'first' : '';
				$msg = $la[0];

				echo '<div class="blocks_reply '.$class.' '.$first.'">'.$msg.'</div>';
			}
		}
	}

	public function mail_user_data_change($mail_nalog,$mail_discount,$order_arr){

		if($mail_nalog || $mail_discount){

			$order_id = $order_arr[0];
			$order_user_count = $order_arr[1];
			$order_type = str_replace('H','Н',$order_arr[2]);

			$qLnk = mysql_query("
								SELECT
									users.id,
									users.name,
									users.email,
									users.max_nalog,
									users.personal_discount
								FROM
									users
								WHERE
									users.id > 0
									AND
									users.id = (
										SELECT
											orders.user_id
										FROM
											orders
										WHERE
											orders.id = '".$order_id."'
											AND
											orders.user_num	= '".$order_user_count."'
											AND
											orders.payment_method = '".$order_type."'
									)
								");

			if(mysql_num_rows($qLnk)>0){
				$ud = mysql_fetch_assoc($qLnk);

				if($mail_nalog && $mail_discount){
					$tpl_id = 9;
				}elseif($mail_nalog){
					$tpl_id = 5;
				}elseif($mail_discount){
					$tpl_id = 8;
				}

				$replace_arr = array(
					'USER_NAME' => $ud['name'],
					'MAX_NALOG' => $ud['max_nalog'],
					'PERSONAL_DISCOUNT' => $ud['personal_discount'],
					'SITE_URL' => THIS_URL
				);

				if(filter_var($ud['email'],FILTER_VALIDATE_EMAIL)){
					$mailer = new Mailer($this->registry,$tpl_id,$replace_arr,$ud['email']);
				}

			}

		}

	}

	public function mk_discount($order_arr,$status){
		if($status==1 || $status==2){
			$order_id = $order_arr[0];
			$order_user_count = $order_arr[1];
			$order_type = str_replace('H','Н',$order_arr[2]);

			mysql_query("
								UPDATE
									users
								SET
									users.personal_discount = IFNULL((
																	SELECT
																		discount_gradations.percent
																	FROM
																		discount_gradations
																	WHERE
																		discount_gradations.sum <= (
																									SELECT
																										SUM(orders.overall_price)
																									FROM
																										orders
																									WHERE
																										orders.user_id = users.id
																										AND
																										orders.status = 3
																									)
																	ORDER BY
																		discount_gradations.sum DESC
																	LIMIT 1)
																	,0)
								WHERE
									users.id = (
												SELECT
													orders.user_id
												FROM
													orders
												WHERE
													orders.id = '".$order_id."'
													AND
													orders.user_num	= '".$order_user_count."'
													AND
													orders.payment_method = '".$order_type."'
												)
								");

			return (mysql_affected_rows()>0) ? true : false;

		}

		return false;

	}

	public function order_goods_rate($order_num,$status){
		if($status==1 || $status==2){

			$ga_id = array();
			$ga_barcode = array();
			$qLnk = mysql_query("
								SELECT
									orders_goods.goods_id AS id,
									orders_goods.goods_barcode AS barcode,
									orders_goods.amount
								FROM
									orders_goods
								WHERE
									orders_goods.order_id = '".$order_num."';
								");
			while($g = mysql_fetch_assoc($qLnk)){
				if($g['id']>0)
					$ga_id[$g['id']] = (isset($ga_id[$g['id']])) ? ($ga_id[$g['id']] + $g['amount']) : $g['amount'];
				
				if($g['barcode']!='')
					$ga_barcode[$g['barcode']] = (isset($ga_barcode[$g['barcode']])) ? ($ga_barcode[$g['id']] + $g['amount']) : $g['amount'];
			}

			foreach($ga_barcode as $barcode => $incr){
				mysql_query("
						UPDATE
							goods, goods_barcodes
						SET
							goods.popularity_index = goods.popularity_index + ".$incr."
						WHERE
							goods_barcodes.barcode = ".$barcode."
							AND
							goods.id = goods_barcodes.goods_id;
						");
			}			
			
			foreach($ga_id as $id => $incr){
				mysql_query("
							UPDATE
								goods
							SET
								goods.popularity_index = goods.popularity_index + ".$incr."
							WHERE
								goods.id = ".$id.";
							");
			}

		}
	}

	public function order_nalog($order_arr,$status){

		$mail = false;

		$order_id = $order_arr[0];
		$order_user_count = $order_arr[1];
		$order_type = str_replace('H','Н',$order_arr[2]);

		$qLnk = mysql_query("
							SELECT
								users.id,
								users.name,
								users.email,
								users.max_nalog
							FROM
								users
							WHERE
								users.id > 0
								AND
								users.id = (
									SELECT
										orders.user_id
									FROM
										orders
									WHERE
										orders.id = '".$order_id."'
										AND
										orders.user_num	= '".$order_user_count."'
										AND
										orders.payment_method = '".$order_type."'
								)
							");

		if(mysql_num_rows($qLnk)>0){
			$user_arr = mysql_fetch_assoc($qLnk);

			$qLnk = mysql_query("
								SELECT orders.overall_price FROM orders
								WHERE
									orders.id = '".$order_id."'
									AND
									orders.user_num	= '".$order_user_count."'
									AND
									orders.payment_method = '".$order_type."'
								");
			$order_sum = (mysql_num_rows($qLnk)>0) ? mysql_result($qLnk,0) : 0;

			$go = false;

			if($status==3 && $order_type=='Н'){
				$max_nalog = 0;
				$go = true;
			}elseif(($status==1 || $status==2 || $status==5) && (($order_sum>$user_arr['max_nalog'] && $user_arr['max_nalog']>0 && $user_arr['max_nalog']<MAX_NALOG) || ($user_arr['max_nalog']==MIN_NALOG))){
				$max_nalog = MAX_NALOG;

				$go = true;
				$mail = true;

			}

			if($go){
				mysql_query("
							UPDATE
								users
							SET
								users.max_nalog = '".$max_nalog."'
							WHERE
								users.id = '".$user_arr['id']."';
							");

			}

		}

		return $mail;

	}

	public function ostatki_order_cancel_notify($num){
		$qLnk = mysql_query("
							SELECT
								COUNT(*)
							FROM
								rezerv
							WHERE
								rezerv.order_id = '".$num."'
							");
		if(mysql_result($qLnk,0)>0){
			$id_arr = explode('/',$num);
			$qLnk = mysql_query("
								SELECT
									users.email
								FROM
									orders
								INNER JOIN users ON users.id = orders.user_id
								WHERE
									orders.id = '".$id_arr[0]."'
									AND
									orders.user_num = '".$id_arr[1]."'
									AND
									orders.payment_method = '".$id_arr[2]."'
								LIMIT 1;
								");
			if(mysql_num_rows($qLnk)>0){
				$u = mysql_fetch_assoc($qLnk);

				$replace_arr = array(
					'ORDER_NUM' => $num,
				);

				$mailer = new Mailer($this->registry,36,$replace_arr,$u['email']);
			}
		}

	}

}
?>