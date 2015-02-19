<?
	Class Logic{

		private $registry;

		public function __construct($registry){
			$this->registry = $registry;
		}

		public function item_rq($name,$a = NULL){
			require($this->registry['template']->TF.'item/logic/'.$name.'.html');
		}

		public function register_params(){
			$qLnk = mysql_query("SELECT params.name, params.value FROM params");
			while($p = mysql_fetch_assoc($qLnk)){
				define($p['name'],$p['value'],true);
			}
		}

		public function send_account_order($order_id){
			$qLnk = mysql_query("
								SELECT
									account_orders.*,
									users.name AS user_name,
									users.email AS user_email,
									users.zip_code AS zip_code,
									users.region AS region,
									users.city AS city,
									users.street AS street,
									users.house AS house,
									users.corpus AS corpus,
									users.flat AS flat
								FROM
									account_orders
								LEFT OUTER JOIN users ON users.id = account_orders.user_id
								WHERE
									account_orders.id = '".$order_id."'
								LIMIT 1;
								");
			if(mysql_num_rows($qLnk)>0){
				$order = mysql_fetch_assoc($qLnk);

				$order['address'] = Common_Address::implode_address($order);
				$order['num'] = $order_id.'/'.$order['user_num'].'/A';
				$order['overall_price'] = $order['sum'];
				$order['from_account'] = 0;

				ob_start();
				$this->item_rq('bill',$order);
				$bill = ob_get_contents();
				ob_end_clean();

				$pdfmanager = new Pdfmanager($this->registry);
				$attach_string = $pdfmanager->fileCompose($bill);

				$replace_arr = array(
					'USER_NAME' => $order['user_name'],
					'ORDER_NUM' => $order['num']
				);

				$mailer = new Mailer($this->registry,25,$replace_arr,$order['user_email'],$attach_string);

			}
		}

	}
?>