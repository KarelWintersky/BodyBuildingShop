<?
	Class f_Profile{

		private $registry;

		public function pgc(){}

		public function __construct($registry){
			$this->registry = $registry;
			$this->registry->set('f_profile',$this);
		}

		public function path_check(){
			$Front_Profile = new Front_Profile($this->registry);
			$Front_Profile_Orders_Page = new Front_Profile_Orders_Page($this->registry);
			
			if(!isset($_SESSION['user_id'])){header('Location: /auth/');}

			$this->registry['f_404'] = false;
			$path_arr = $this->registry['route_path'];

			$this->registry['template']->add2crumbs('profile','Личный кабинет');
			$this->registry['noindex'] = true;
			$this->registry['register_page'] = 1; //для того, чтобы в меню пункт "Профиль" был активным, костыль с пункта "Регистрация"

			if(count($path_arr)==0){
				$this->registry['template']->set('c','profile/settings');
				$this->registry['longtitle'] = 'Ваши установки';
				$this->get_all_user_info();
				
				$this->registry['CL_css']->set(array(
						'profile',
				));				
				
				return true;
			}elseif(count($path_arr)==1 && $path_arr[0]=='orders'){
				$data = $Front_Profile->get_data();				
				
				$Front_Profile_Orders_List = new Front_Profile_Orders_List($this->registry);
				$Front_Profile_Orders_List->print_list($data);
				
				$this->registry['template']->set('c','profile/orders/list');
				$this->registry['longtitle'] = 'Ваши заказы';
				
				$this->registry['CL_css']->set(array(
						'profile',
				));				
				
				return true;
			}elseif(count($path_arr)==1 && $path_arr[0]=='returnaccount'){
				$this->registry['template']->set('c','profile/returnaccount');
				$this->registry['longtitle'] = 'Забрать деньги';
				$this->get_all_user_info();
				
				$this->registry['CL_css']->set(array(
						'profile',
				));				
				
				return true;
			}elseif(count($path_arr)==1 && $path_arr[0]=='accountorder'){
				$this->registry['template']->set('c','profile/accountorder');
				$this->registry['longtitle'] = 'Пополнить личный счет';
				$this->get_all_user_info();
				$this->mk_account_order();
				
				$this->registry['CL_css']->set(array(
						'profile',
				));				
				
				return true;
			}elseif(count($path_arr)==2 && $path_arr[0]=='orders' && $Front_Profile_Orders_Page->check_order($path_arr[1])){
				$data = $Front_Profile->get_data();
				
				$this->registry['template']->set('c','profile/orders/page');
				
				$this->registry['CL_css']->set(array(
						'profile',
				));				
				
				return true;
			}elseif(count($path_arr)==3 && $path_arr[0]=='orders' && $this->order_check($path_arr[1]) && $path_arr[2]=='pay' && $this->pay_check($path_arr[1])){
				$this->registry['template']->set('c','profile/order_pay');
				$this->get_all_user_info();
				$this->mk_roboxchange_data($path_arr[1]);
				
				$this->registry['CL_css']->set(array(
						'profile',
				));				
				
				return true;
			}

			$this->registry['f_404'] = true;
			return false;
		}

		private function mk_account_order(){
			if(count($_POST)>0 && isset($_POST['sum'])){
				$sum = $_POST['sum'];
				$user_id = $this->registry['userdata']['id'];

				$qLnk = mysql_query("SELECT (IFNULL(MAX(account_orders.user_num),0)+1) FROM account_orders WHERE account_orders.user_id = '".$user_id."';");
				$user_num = mysql_result($qLnk,0);

				mysql_query("
							INSERT INTO
								account_orders
								(account_orders.user_id,
									account_orders.user_num,
										account_orders.createdon,
											account_orders.sum,
												account_orders.status)
								VALUES
								('".$user_id."',
									'".$user_num."',
										NOW(),
											'".$sum."',
												1);
							");
				$order_id = mysql_insert_id();
				$order_num = $order_id.'/'.$user_num.'/А';

				$output = array(
					'num' => $order_num,
					'sum' => $sum
				);

				$this->registry['new_account_order_data'] = $output;

				$Front_Profile_Orders_Account_Make = new Front_Profile_Orders_Account_Make($this->registry);
				$Front_Profile_Orders_Account_Make->send_order($order_num);

				$this->send_to_admins($order_num);

			}
		}

		private function send_to_admins($order_num){

			$emails = explode('::',ADMINS_EMAILS);
			if(count($emails)>0){
				foreach($emails as $admin_mail){
					$replace_arr = array(
						'ORDER_NUM' => $order_num
					);
					$mailer = new Mailer($this->registry,24,$replace_arr,$admin_mail);
				}
			}

		}

		private function mk_roboxchange_data($num){

			$id_arr = explode('-',$num);
			$order_id = implode('/',$id_arr);

			$login = ROBOKASSA_LG; //$mrh_login
			$pwd = ROBOKASSA_PW; //$mrh_pass1
			$unique_id = $this->registry['orderdata']['ai'];; //$inv_id
			$desc = 'Оплата заказа № '.$order_id.' в Бодибилдинг-Магазине'; //$inv_desc
			$sum = $this->registry['orderdata']['overall_price']; //$out_summ
			$code = 1;	//$shp_item

			$crc  = md5("$login:$sum:$unique_id:$pwd:Shp_item=$code");

			$this->registry['RD'] = array(
				'login' => $login,
				'sum' => $sum,
				'unique_id' => $unique_id,
				'desc' => $desc,
				'signature' => $crc,
				'code' => $code,
				'curr' => ROBOKASSA_CURR,
				'lang' => ROBOKASSA_LANG,
			);
		}

		private function pay_check($num){
			$id_arr = explode('-',$num);
			$qLnk = mysql_query("
								SELECT
									COUNT(*)
								FROM
									orders
								WHERE
									orders.user_id = '".$this->registry['userdata']['id']."'
									AND
									orders.id = '".$id_arr[0]."'
									AND
									orders.user_num	= '".$id_arr[1]."'
									AND
									orders.payment_method = '".$id_arr[2]."'
									AND
									orders.by_card = 1
									AND
									orders.status = 1
			");

			return (mysql_result($qLnk,0)>0) ? true : false;

		}

		private function get_all_user_info(){

			$qLnk = mysql_query("SELECT users.* FROM users WHERE users.id = '".$_SESSION['user_id']."';");
			$this->registry['full_ui'] = (isset($_COOKIE['profile_data'])) ? (array)json_decode($_COOKIE['profile_data']) : mysql_fetch_assoc($qLnk);
		}

		public function item_rq($name,$a = NULL){
			require($this->registry['template']->TF.'item/profile/'.$name.'.html');
		}

		public function sav_profile(){
			$err_array = array();

			if($_POST['pass']!=$_POST['pass_confirm']){
				$err_array[] = 'Пароли не совпадают';
			}

			if($_POST['name']==''){$err_array[] = 'Укажите фио';	}

			if($_POST['email']==''){
				$err_array[] = 'Укажите email';
			}elseif(!filter_var($_POST['email'],FILTER_VALIDATE_EMAIL)){
				$err_array[] = 'Укажите корректный email';
			}

			if($_POST['city']==''){$err_array[] = 'Укажите город';}

			if($_POST['street']==''){$err_array[] = 'Укажите улицу';}

			if($_POST['house']==''){$err_array[] = 'Укажите номер дома';}

			if(count($err_array)>0){
				$cookie_timer = time()+1800;
			}else{

				$this->mk_sav();
				$this->admins_notify();

				$cookie_timer = time()-1800;
			}

			setcookie('err_array',json_encode($err_array),$cookie_timer,'/');
			setcookie('profile_data',json_encode($_POST),$cookie_timer,'/');
		}

		private function admins_notify(){
			$replace_arr = array(
				'LOGIN' => $_POST['login'],
				'ID' => $_SESSION['user_id'],
				'OLD_FIO' => $_POST['old_name'],
				'OLD_EMAIL' => $_POST['old_email'],
				'OLD_INDEX' => $_POST['old_zip_code'],
				'OLD_COUNTRY' => 'Россия',
				'OLD_REGION' => $_POST['old_region'],
				'OLD_CITY' => $_POST['old_city'],
				'OLD_STREET' => $_POST['old_street'],
				'OLD_HOUSE' => $_POST['old_house'],
				'OLD_CORPUS' => $_POST['old_corpus'],
				'OLD_FLAT' => $_POST['old_flat'],
				'OLD_WISHES' => $_POST['old_wishes'],
				'PASS' => $_POST['pass'],
				'NEW_FIO' => $_POST['name'],
				'NEW_EMAIL' => $_POST['email'],
				'NEW_INDEX' => $_POST['zip_code'],
				'NEW_COUNTRY' => 'Россия',
				'NEW_REGION' => $_POST['region'],
				'NEW_CITY' => $_POST['city'],
				'NEW_STREET' => $_POST['street'],
				'NEW_HOUSE' => $_POST['house'],
				'NEW_CORPUS' => $_POST['corpus'],
				'NEW_FLAT' => $_POST['flat'],
				'NEW_WISHES' => $_POST['wishes'],
			);

			foreach($replace_arr as $key => $val){$replace_arr[$key] = iconv('utf-8','windows-1251',$val);}

			$emails = explode('::',ADMINS_EMAILS);
			if(count($emails)>0){
				foreach($emails as $admin_mail){
					$mailer = new Mailer($this->registry,28,$replace_arr,$admin_mail,false,'windows-1251');
				}
			}

		}

		private function mk_sav(){

			$q_pass = (isset($_POST['pass']) && $_POST['pass']!='') ? "users.pass = '".md5($_POST['pass'])."'," : "";

			$get_news = (isset($_POST['get_news_val']) && $_POST['get_news_val']==1) ? 1 : 0;
			$get_catalog_changes = (isset($_POST['get_catalog_changes_val']) && $_POST['get_catalog_changes_val']==1) ? 1 : 0;

			mysql_query("
						UPDATE
							users
						SET
							users.name = '".$_POST['name']."',
							".$q_pass."
							users.phone = '".$_POST['phone']."',
							users.email = '".$_POST['email']."',
							users.zip_code = '".trim($_POST['zip_code'])."',
							users.region = '".$_POST['region']."',
							users.district = '".$_POST['district']."',
							users.city = '".$_POST['city']."',
							users.street = '".$_POST['street']."',
							users.house = '".$_POST['house']."',
							users.corpus = '".$_POST['corpus']."',
							users.flat = '".$_POST['flat']."',
							users.wishes = '".$_POST['wishes']."',
							users.get_news = '".$get_news."',
							users.get_catalog_changes = '".$get_catalog_changes."'
						WHERE
							users.id = '".$_SESSION['user_id']."'
						");
		}

		public function prof_err(){
			if(isset($_COOKIE['err_array'])){
				$html = '';
				foreach(json_decode($_COOKIE['err_array']) as $error){
					$html.= '<li>'.$error.'</li>';
				}
				echo '<ol id="profile_upper_err">'.$html.'</ol>';
			}
		}

	}
?>