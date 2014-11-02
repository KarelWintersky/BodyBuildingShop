<?
Class Orders{

	private $registry;
	public $statuses;
	public $p_opts;

	public function __construct($registry, $frompage = true){
		$this->registry = $registry;
		$this->registry->set('orders',$this);

		$this->p_opts = array(20,50,100);

		$this->statuses = array(
								1 => 'сформирован',
								2 => 'отправлен',
								3 => 'оплачен',
								4 => 'отменен',
								5 => 'деньги поступили',
								);

        if($frompage){
	        $route = $this->registry['aias_path'];
	        array_shift($route);

	        if(count($route)==0){
	        	$this->registry['f_404'] = false;
	        	$this->registry['template']->set('c','orders/main');
	        }elseif(count($route)==1 && $this->order_check($route[0])){
	        	$this->registry['f_404'] = false;
	        	$this->registry['template']->set('c','orders/order');
	        }
        }

	}

	public function statuses_options($cur_id,$all){
		$st_arr = $this->statuses;
		if($all){array_unshift($st_arr,'все');}
		foreach($st_arr as $id => $name){
			$selected = ($cur_id==$id) ? 'selected' : '';
			echo '<option value="'.$id.'" '.$selected.'>'.$name.'</option>';
		}
	}

	private function order_check($num){
		$id_arr = explode('-',$num);

		$qLnk = mysql_query("
							SELECT
								orders.*,
								users.name AS user_name,
								users.zip_code AS zip_code,
								users.region AS region,
								users.district AS district,
								users.city AS city,
								users.street AS street,
								users.house AS house,
								users.corpus AS corpus,
								users.flat AS flat
							FROM
								orders
							LEFT OUTER JOIN users ON users.id = orders.user_id
							WHERE
								orders.id = '".$id_arr[0]."'
								AND
								orders.user_num = '".$id_arr[1]."'
								AND
								orders.payment_method = '".$id_arr[2]."'
							LIMIT 1
							");
		if(mysql_num_rows($qLnk)>0){
			$o = mysql_fetch_assoc($qLnk);
						
			$o['num'] = $o['id'].'/'.$o['user_num'].'/'.$o['payment_method'];
			$this->registry['order_info'] = $o;
			return true;
		}else{

			return false;
		}
	}

	public function order_sav(){
		foreach($_POST as $key => $val){$$key = (is_array($val)) ? $val : $val;}

		$id_arr = explode('/',$num);

		$postnum = ($postnum!='') ? "'".$postnum."'" : "NULL";

		mysql_query("
					UPDATE
						orders
					SET
						orders.status = '".$status."',
						orders.sent_on = IF('".$sent_on."'='',orders.sent_on,'".date('Y-m-d',strtotime($sent_on))."'),
						orders.payed_on = IF('".$payed_on."'='',orders.payed_on,'".date('Y-m-d',strtotime($payed_on))."'),
						orders.wishes = '".$wishes."',
						orders.postnum = ".$postnum."
					WHERE
						orders.id = '".$id_arr[0]."'
						AND
						orders.user_num = '".$id_arr[1]."'
						AND
						orders.payment_method = '".$id_arr[2]."'
					");

		if($status==3){
			$BL = new Blocks($this->registry,false);
				$mail_nalog = $BL->order_nalog($id_arr,1);
				$BL->order_goods_rate($num,1);
				$mail_discount = $BL->mk_discount($id_arr,1);
				$BL->mail_user_data_change($mail_nalog,$mail_discount,$id_arr);
		}elseif($status==4){
			$BL = new Blocks($this->registry,false);
			$BL->ostatki_order_cancel_notify($num);
		}

		if($status==4){//если заказ отменен, смотрим, есть ли в нем товары по остаткам
			Settings::order_cancel($id_arr);
		}elseif($status==3){//если заказ оплачен, удаляем резерв
			Settings::order_apply($id_arr);
		}

	}

	private function item_rq($name,$a = NULL){
		require($this->registry['template']->TF.'item/orders/'.$name.'.html');
	}

	private function mk_conditions(){
		$q = array();
		if(isset($_GET['num']) && $_GET['num']!=''){
			$num_arr = explode('/',$_GET['num']);
			if(count($num_arr)==3){
				$num_arr[2] = str_replace('H','Н',$num_arr[2]);
				$q[] = "orders.id = '".$num_arr[0]."' AND orders.user_num = '".$num_arr[1]."' AND orders.payment_method = '".$num_arr[2]."'";
			}else{
				$q[] = "orders.id = '".$_GET['num']."' OR orders.user_num = '".$_GET['num']."' OR orders.payment_method = '".$_GET['num']."'";
			}
		}

		if(isset($_GET['status']) && $_GET['status']!='' && $_GET['status']!=0){
			$q[] = "orders.status = '".$_GET['status']."'";
		}

		if(isset($_GET['date_from']) && $_GET['date_from']!=''){
			$q[] = "DATE(orders.made_on) >= DATE('".$this->registry['logic']->read2date($_GET['date_from'])."')";
		}

		if(isset($_GET['date_to']) && $_GET['date_to']!=''){
			$q[] = "DATE(orders.made_on) <= DATE('".$this->registry['logic']->read2date($_GET['date_to'])."')";
		}

		$q_str = (count($q)>0) ? "WHERE ".implode(" AND ",$q) : "";

		return $q_str;

	}

	private function mk_pagination(){

		$PAGING = (isset($_COOKIE['adm_orders_paging'][1])) ? $_COOKIE['adm_orders_paging'][1] : 20;

    	$page = (isset($_GET['page'])) ? $_GET['page'] : 1;
    	$offset = $PAGING*($page-1);

		$this->registry['orders_paging'] = $PAGING;
			$this->registry['orders_display_first'] = $offset+1;
			$this->registry['orders_display_last'] = $offset+$PAGING;

    	return "LIMIT ".$offset.", ".$PAGING;
	}

	public function get_list(&$list){
		$q_str = $this->mk_conditions();

		$q_amount = $this->mk_pagination();

		$qLnk = mysql_query("
							SELECT SQL_CALC_FOUND_ROWS
								orders.*
							FROM
								orders
							".$q_str."
							ORDER BY
								orders.made_on DESC
							".$q_amount.";
							");
		$count = mysql_num_rows($qLnk);
		$qA = mysql_query("SELECT FOUND_ROWS();");
   		$this->registry['orders_amount'] = mysql_result($qA,0);

   		ob_start();
		while($o = mysql_fetch_assoc($qLnk)){
			$o['num'] = $o['id'].'/'.$o['user_num'].'/'.$o['payment_method'];
			$o['lnk'] = $o['id'].'-'.$o['user_num'].'-'.$o['payment_method'];
			$this->item_rq('order_item',$o);
		}

		if($count==0){
			$this->item_rq('order_not_found');
		}

		$list = ob_get_contents();
		ob_end_clean();
	}

	public function pagination(){
		$pages_amount = ceil($this->registry['orders_amount']/$this->registry['orders_paging']);
		$cur_page = (isset($_GET['page'])) ? $_GET['page'] : 1;
		if($pages_amount>1){
			ob_start();
			for($i=1;$i<=$pages_amount;$i++){
				$a['num'] = $i;
				$a['lnk'] = $this->get_pagination_link($i);
				$a['active'] = ($i==$cur_page) ? 'active' : '';
				$this->item_rq('orders_paging',$a);
			}
			$html = ob_get_contents();
			ob_end_clean();
			echo '<ul id="orders_paging">'.$html.'</ul><div id="orders_paginig_overall">Показаны заказы с <b>'.$this->registry['orders_display_first'].'</b> по <b>'.$this->registry['orders_display_last'].'</b> из <b>'.$this->registry['orders_amount'].'</b></div>';
		}
	}

	private function get_pagination_link($i){
		$url_arr = explode('?',$_SERVER['REQUEST_URI']);

		if(isset($url_arr[1])){
			$params = explode('&',$url_arr[1]);
			foreach($params as $key => $p){
				$p_arr = explode('=',$p);
				if($p_arr[0]=='page'){unset($params[$key]);}
			}
			$new_url = $url_arr[0].'?'.implode('&',$params);
			$delim = '&';
		}else{
			$new_url = $url_arr[0];
			$delim = '?';
		}

		return ($i==1) ? $new_url : $new_url.$delim.'page='.$i;
	}

	public function goods_list(){
		$goods = array();
		$barcodes = array();
		$qLnk = mysql_query("
				SELECT
					orders_goods.*
				FROM
					orders_goods
				WHERE
					orders_goods.order_id = '".$this->registry['order_info']['num']."'
				ORDER BY
					orders_goods.final_price DESC;
				");
		while($g = mysql_fetch_assoc($qLnk)){
			$goods[] = $g;
			$barcodes[] = $g['goods_barcode'];
		}
		if(count($goods)==0) return '';
			
		$is_barcodes = false;
		foreach($goods as $g) if($g['goods_id']==0) $is_barcodes = true;
			
		if($is_barcodes){
			
			foreach($barcodes as $key => $val) if(!$val) unset($barcodes[$key]);
			
			$qLnk = mysql_query(sprintf("
					SELECT
						goods_barcodes.barcode,
						goods_barcodes.packing,
						goods_barcodes.feature,
						goods.id,
						goods.name AS goods_name,
						levels.id AS level_id,
						parent_tbl.id AS parent_id
					FROM
						goods_barcodes
					INNER JOIN goods ON goods.id = goods_barcodes.goods_id
					LEFT OUTER JOIN levels ON levels.id = goods.level_id
					LEFT OUTER JOIN levels AS parent_tbl ON parent_tbl.id = levels.parent_id
					WHERE
						goods_barcodes.barcode IN (%s)
					",
					implode(",",$barcodes)
			));
			while($g = mysql_fetch_assoc($qLnk)){
				foreach($goods as $key => $gitem){
					if($gitem['goods_barcode']==$g['barcode']){
						$goods[$key]['goods_id'] = $g['id'];
						$goods[$key]['goods_name'] = $g['goods_name'];
						$goods[$key]['level_id'] = $g['level_id'];
						$goods[$key]['parent_id'] = $g['parent_id'];
						$goods[$key]['packing'] = $g['packing'];
						$goods[$key]['feature'] = $g['feature'];
					}
				}
			}
		
		}else{
			$ids = array();
			foreach($goods as $g) $ids[] = $g['goods_id'];
			
			$qLnk = mysql_query(sprintf("
					SELECT
						goods.id,
						levels.id AS level_id,
						parent_tbl.id AS parent_id
					FROM
						goods
					LEFT OUTER JOIN levels ON levels.id = goods.level_id
					LEFT OUTER JOIN levels AS parent_tbl ON parent_tbl.id = levels.parent_id
					WHERE
						goods.id IN (%s)
					",
					implode(",",$ids)
			));
			while($g = mysql_fetch_assoc($qLnk)){
				foreach($goods as $key => $arr){
					if($arr['goods_id']==$g['id']){
						$goods[$key]['level_id'] = $g['level_id'];
						$goods[$key]['parent_id'] = $g['parent_id'];						
					}
				}
			}
		}		
				
		foreach($goods as $g){
			$g['goods_full_name'] = ($g['goods_full_name']=='' && isset($g['goods_name'])) 
				? $g['goods_name'] 
				: $g['goods_full_name'];
			
			$this->item_rq('goods_item',$g);
		}
	}

	public function resend_message(){
		$this->registry['logic']->send_order($_POST['num'],true,false);
		$this->registry['logic']->admins_notify($_POST['num']);
	}

	public function resend_bill(){
		$this->registry['logic']->send_bill($_POST['num']);
	}

	public function pagination_options(){

		$html = '';
		foreach($this->p_opts as $opt){
			$sel = (isset($_COOKIE['adm_orders_paging'][1]) && $_COOKIE['adm_orders_paging'][1]==$opt) ? 'selected' : '';

			$html.='<option '.$sel.' value="'.$opt.'">'.$opt.'</option>';
		}

		echo $html;
	}


}
?>