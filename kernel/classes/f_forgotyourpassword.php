<?
	Class f_Forgotyourpassword{
		
		private $registry;
		private $rec_msg;

		public function pgc(){}
		
		public function __construct($registry){
			$this->registry = $registry;						
			$this->registry->set('f_forgotyourpassword',$this);
		}

		public function path_check(){
			
			if(isset($_SESSION['user_id'])){header('Location: /');}
			
			$this->registry['f_404'] = false;
			$path_arr = $this->registry['route_path'];
			
			$this->registry['template']->add2crumbs('forgotyourpassword','Восстановление пароля');
			$this->registry['noindex'] = true;
			
			if(count($path_arr)==0){
				$this->registry['template']->set('c','auth/forgotyourpassword');
				$this->registry['longtitle'] = 'Восстановление пароля';
				
				if(isset($_COOKIE['recover_msg'])){
					$this->rec_msg = $_COOKIE['recover_msg'];
					setcookie('recover_msg','',time()-900,'/');	
				}				
				
				return true;
			}
									
			$this->registry['f_404'] = true;
			return false;
		}
		
		public function pwd_recover(){
			
			$msg_types = array(
				'login' => 'логином',
				'email' => 'email'
			);
			
			$qLnk = mysql_query("
								SELECT
									users.name,
									users.login,
									users.email
								FROM
									users
								WHERE
									users.".$_POST['type']." = '".$_POST['value']."'
								LIMIT 1;
								");
			if(mysql_num_rows($qLnk)>0){
				$new_pass = mb_substr(md5(time().$_POST['value']),0,9,'utf-8');
				$user_data = mysql_fetch_assoc($qLnk);
				
				mysql_query("UPDATE users SET users.pass = '".md5($new_pass)."' WHERE users.login = '".$user_data['login']."';");
				
				$replace_arr = array(
					'USER_NAME' => $user_data['name'],
					'USER_LOGIN' => $user_data['login'],
					'NEW_PASS' => $new_pass
				);
				
				$mailer = new Mailer($this->registry,1,$replace_arr,$user_data['email']);
				
				$msg_text = 'Письмо с паролем удачно отправлено на email, который вы указали при регистрации';
				
			}else{
				$msg_text = 'Пользователь с указанным '.$msg_types[$_POST['type']].' не найден';
			}
			
			setcookie('recover_msg',$msg_text,time()+900,'/');	
			
		}

		public function rec_msg(){
			if(isset($this->rec_msg) && $this->rec_msg!=''){
				$html = '<div class="pwd_recover_hint" id="pwd_recover_msg">'.$this->rec_msg.'</div>';
				echo $html;		
			}
		}
			
	}
?>