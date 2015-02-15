<?
	Class f_Register{
		
		private $registry;

		public function pgc(){}
		
		public function __construct($registry){
			$this->registry = $registry;						
			$this->registry->set('f_register',$this);
		}

		public function path_check(){
			
			if(isset($_SESSION['user_id']) && !isset($_SESSION['allow_reg_page'])){header('Location: /profile/');}
			
			$this->registry['f_404'] = false;
			$path_arr = $this->registry['route_path'];
			
			$this->registry['template']->add2crumbs('register','Регистрация покупателя');
			$this->registry['register_page'] = 1;
			$this->registry['noindex'] = true;
			
			if(count($path_arr)==0){
				$this->registry['template']->set('c','register/intro');
				$this->registry['longtitle'] = 'Регистрация покупателя';
				
				$this->registry['CL_css']->set(array(
						'profile',
				));				
				
				return true;
			}elseif(count($path_arr)==1 && $path_arr[0]=='1'){
				$this->registry['template']->set('c','register/step_1');
				$this->registry['longtitle'] = 'Регистрация покупателя шаг 1';
				$this->index_check();
				
				$this->registry['CL_css']->set(array(
						'profile',
				));				
				
				return true;
			}elseif(count($path_arr)==1 && $path_arr[0]=='2'){
				$this->registry['template']->set('c','register/step_2');
				$this->registry['longtitle'] = 'Регистрация покупателя шаг 2';
				$this->reg_data();
				
				$this->registry['CL_css']->set(array(
						'profile',
				));				
				
				return true;
			}elseif(count($path_arr)==1 && $path_arr[0]=='3'){
				$this->registry['template']->set('c','register/step_3');
				$this->registry['longtitle'] = 'Регистрация покупателя шаг 3';
				
				$this->registry['CL_css']->set(array(
						'profile',
				));				
				
				return true;
			}
			
			$this->registry['f_404'] = true;
			return false;
		}
			
		public function item_rq($name,$a = NULL){
			require($this->registry['template']->TF.'item/register/'.$name.'.html');
		}	
			
		private function index_check(){
			if(isset($_GET['index'])){
				$qLnk = mysql_query("
									SELECT
										indexes.region,
										indexes.city
									FROM
										indexes
									WHERE
										indexes.ind = '".$_GET['index']."'
									LIMIT 1;
									");
				if(mysql_num_rows($qLnk)>0){
					$this->registry['r_region_data'] = mysql_fetch_assoc($qLnk);
				}else{
					$qLnk = mysql_query("
										SELECT
											indexes.region,
											indexes.city,
											indexes.ind AS index_changed
										FROM
											indexes
										WHERE
											indexes.ind_old = '".$_GET['index']."'
										LIMIT 1;
										");		
					if(mysql_num_rows($qLnk)>0){
						$this->registry['r_region_data'] = mysql_fetch_assoc($qLnk);
					}else{
						$this->registry['index_not_found'] = true;
					}			
				}
			}
		}
		
		public function reg_err(){
						
			if(isset($_COOKIE['err_array'])){
				$html = '';
				foreach(json_decode($_COOKIE['err_array']) as $error){
					$html.= '<li>'.$error.'</li>';
				}
				echo '<ol id="register_upper_err">'.$html.'</ol>';
			}			
		}
		
		private function reg_data(){
			
			if(isset($_COOKIE['reg_data'])){
				$cookie_reg_data = json_decode($_COOKIE['reg_data']);
			}
						
			$reg_arr['lgn'] = (isset($cookie_reg_data->lgn)) ? $cookie_reg_data->lgn : '';
			$reg_arr['fio'] = (isset($cookie_reg_data->fio)) ? $cookie_reg_data->fio : '';
			$reg_arr['email'] = (isset($cookie_reg_data->email)) ? $cookie_reg_data->email : '';
			$reg_arr['index'] = (isset($cookie_reg_data->index)) ? $cookie_reg_data->index : ((isset($_GET['index'])) ? $_GET['index'] : '');
			$reg_arr['region'] = (isset($cookie_reg_data->region)) ? $cookie_reg_data->region : ((isset($_GET['region'])) ? $_GET['region'] : '');
			$reg_arr['city'] = (isset($cookie_reg_data->city)) ? $cookie_reg_data->city : ((isset($_GET['city'])) ? $_GET['city'] : '');
			$reg_arr['street'] = (isset($cookie_reg_data->street)) ? $cookie_reg_data->street : '';
			$reg_arr['house'] = (isset($cookie_reg_data->house)) ? $cookie_reg_data->house : '';
			$reg_arr['corp'] = (isset($cookie_reg_data->corp)) ? $cookie_reg_data->corp : '';
			$reg_arr['flat'] = (isset($cookie_reg_data->flat)) ? $cookie_reg_data->flat : '';
			$reg_arr['wishes'] = (isset($cookie_reg_data->wishes)) ? $cookie_reg_data->wishes : '';
			
			$this->registry['reg_arr'] = $reg_arr;
		}
		
		public function step_2(){
			$err_array = array();
			
			if($_POST['pwd']==''){
				$err_array[] = 'Укажите пароль';
			}elseif($_POST['pwd']!=$_POST['pwd_confirm']){
				$err_array[] = 'Пароли не совпадают';
			}
			
			if($_POST['lgn']==''){
				$err_array[] = 'Укажите логин';
			}elseif(!$this->login_avialable($_POST['lgn'])){
				$err_array[] = 'Такой логин уже есть в базе. Пожалуйста, выберите другой';
			}
			
			if($_POST['fio']==''){$err_array[] = 'Укажите фио';	}			
			
			if($_POST['email']==''){
				$err_array[] = 'Укажите email';
			}elseif(!filter_var($_POST['email'],FILTER_VALIDATE_EMAIL)){
				$err_array[] = 'Укажите корректный email';
			}elseif(!$this->email_avialable($_POST['email'])){
				$err_array[] = 'Пользователь с таким email уже зарегистрирован. Если вы забыли пароль, можете воспользоваться восстановлением.';
			}
						
			if($_POST['city']==''){$err_array[] = 'Укажите город';}
			
			if($_POST['street']==''){$err_array[] = 'Укажите улицу';}
			
			if($_POST['house']==''){$err_array[] = 'Укажите номер дома';}
			
			if(!isset($_POST['terms'])){$err_array[] = 'Нужно согласиться с правилами';}
			
			if(count($err_array)>0){
				$cookie_timer = time()+1800;
			}else{
				
				$this->mk_register();
				
				$cookie_timer = time()-1800;
								
				$this->registry['doer']->set_rp('/register/3/');
			}
			
			setcookie('err_array',json_encode($err_array),$cookie_timer,'/');
			setcookie('reg_data',json_encode($_POST),$cookie_timer,'/');

			if(isset($_POST['after'])){
				header('Location: '.$_POST['after']);
				exit();
			}			
		}
		
		private function mk_register(){
			mysql_query("
						INSERT INTO
							users
							(type,
								login,
									email,
										name,
											pass,
												hash,
													zip_code,
														country,
															region,
																city,
																	street,
																		house,
																			corpus,
																				flat,
																					wishes,
																						registred_on,
																							personal_discount,
																								max_nalog,
																									my_account)
							VALUES
							('0',
								'".$_POST['lgn']."',
									'".$_POST['email']."',
										'".$_POST['fio']."',
											'".md5($_POST['pwd'])."',
												'".md5($_POST['lgn'].time())."',
													'".trim($_POST['index'])."',
														'Россия',
															'".$_POST['region']."',
																'".$_POST['city']."',
																	'".$_POST['street']."',
																		'".$_POST['house']."',
																			'".$_POST['corp']."',
																				'".$_POST['flat']."',
																					'".$_POST['wishes']."',
																						NOW(),
																							0,
																								'".MIN_NALOG."',
																									0)	
						");
			
			$new_user_id = mysql_insert_id();
			
			$this->mail_register($_POST);
			$this->admins_mail_register($_POST,$new_user_id);
			
			$_SESSION['user_id'] = $new_user_id;
			$_SESSION['allow_reg_page'] = true;
			
		}
		
		private function admins_mail_register($data,$new_user_id){
			$replace_arr = array(
				'USER_LOGIN' => $data['lgn'],
				'USER_PASS' => $data['pwd'],
				'USER_NAME' => $data['fio'],
				'USER_EMAIL' => $data['email'],
				'USER_ID' => $new_user_id,
				'USER_INDEX' => $data['index'],
				'USER_COUNTRY' => 'Россия',
				'USER_REGION' => $data['region'],
				'USER_DISTRICT' => '',
				'USER_CITY' => $data['city'],
				'USER_STREET' => $data['street'],
				'USER_HOUSE' => $data['house'],
				'USER_CORPUS' => $data['corp'],
				'USER_FLAT' => $data['flat'],
				'USER_WISHES' => $data['wishes'],
			);
			
			foreach($replace_arr as $key => $val){$replace_arr[$key] = iconv('utf-8','windows-1251',$val);}
			
			$emails = explode('::',ADMINS_EMAILS);
			if(count($emails)>0){
				foreach($emails as $admin_mail){
					$mailer = new Mailer($this->registry,27,$replace_arr,$admin_mail,false,'windows-1251');						
				}
			} 			
						
		}
		
		private function mail_register($data){
			$replace_arr = array(
				'USER_NAME' => $data['fio'],
				'USER_PASS' => $data['pwd'],
				'USER_LOGIN' => $data['lgn'],
				'SITE_URL' => THIS_URL
			);
			
			$mailer = new Mailer($this->registry,2,$replace_arr,$data['email']);				
		}
		
		public function unset_allow_reg(){
			unset($_SESSION['allow_reg_page']);
		}
		
		private function email_avialable($email){
			$qLnk = mysql_query("SELECT COUNT(*) FROM users WHERE users.email = '".$email."';");
			return (mysql_result($qLnk,0)>0) ? false : true;			
		}
		
		private function login_avialable($login){
			$qLnk = mysql_query("SELECT COUNT(*) FROM users WHERE users.login = '".$login."';");
			return (mysql_result($qLnk,0)>0) ? false : true;
		}
		
	}
?>