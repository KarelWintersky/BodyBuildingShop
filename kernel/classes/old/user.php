<?

class User{
	
    private $registry; 
    private $userInfoArray = false;
    private $q;

    function __construct($registry) {
    	    	
    	$this->q = "
					SELECT 
						users.id AS id,
						users.login AS login, 
						users.email AS email, 
						users.hash AS hash, 
						users.name AS name,									 
						users.zip_code AS zip_code,
						users.city AS city,
						users.street AS street,
						users.house AS house,
						users.corpus AS corpus,
						users.flat AS flat,
						users.max_nalog AS max_nalog,
						users.personal_discount AS personal_discount,
						users.type AS type,
						users.my_account AS my_account
					FROM 
						users     	
    				";
    	    	
        $this->registry = $registry;
        $this->registry->set('userdata',$this->getAuth());
    }
    		    
	private function getAuth(){
				
		if(isset($_POST['logout'])){
			$this->authLogout();
		}else{
			if(!$this->getSessionAuth()){
				if(!$this->getCookieAuth()){
					$this->setPassAuth();
				}
				$this->setAuthSession();					
			}			
		}
				
		return($this->userInfoArray);
					
	}
		
	private function authLogout(){
		unset($_SESSION['user_id']);
		setCookie('user_id','',time()-3600,'/'); 
		setCookie('hash','',time()-3600,'/'); 
	}

	private function getSessionAuth(){
				
		if(isset($_SESSION['user_id'])){
			$u_id = $_SESSION['user_id'];
			$qLnk=mysql_query($this->q."
								WHERE
									users.id = ".$u_id."
								LIMIT 1;");
			if(mysql_num_rows($qLnk)==0){
				$this->userInfoArray = false;
			}else{
				while($rArr=mysql_fetch_assoc($qLnk)){
					$this->userInfoArray = $rArr;
				}					
			}			
			return $this->userInfoArray;
		}else{
			return false;
		}
	}
	
	private function getCookieAuth(){
		if(isset($_COOKIE['user_id']) && isset($_COOKIE['hash'])){		
			$u_id = $_COOKIE['user_id'];
			$h = $_COOKIE['hash'];
			$qLnk=mysql_query($this->q."
								WHERE
									users.id = '".$u_id."' 
									AND
									users.hash = '".$h."' 
								LIMIT 1;");
			if(mysql_num_rows($qLnk)==0){
				$this->userInfoArray = false;
				return false;
			}else{
				while($rArr=mysql_fetch_assoc($qLnk)){
					$this->userInfoArray = $rArr;
				}					
			}			
			
							
		}else{
			return false;
		}
	}
		
	private function setPassAuth(){
				
		if(isset($_POST['login']) && isset($_POST['pass'])){			
			
			$l=trim(mysql_real_escape_string($_POST['login']));
			$p=md5($_POST['pass']);
									
			$qLnk=mysql_query($this->q."
								WHERE 
									users.login = '".$l."' 
									AND
									users.pass = '".$p."' 
								LIMIT 1;");
			if(mysql_num_rows($qLnk)==0){
				$this->userInfoArray=false;
			}else{
				while($rArr=mysql_fetch_assoc($qLnk)){
					$this->userInfoArray=$rArr;
				}		
			}			
						
			if(!$this->userInfoArray){
				header('Location: /auth/?failed=1');
				exit();
			}else{
				if(!isset($_POST['remember'])){$this->setAuthCookie();}

				if(isset($_POST['after']) && $_POST['after']){
					header('Location: '.$_POST['after']);
					exit();
				}
				
				return true;
			}
		}			
	}
		
	private function setAuthCookie(){
		$ex = 60*60*24*365; // время истечения - год
		setCookie('user_id', $this->userInfoArray['id'], time() + $ex, '/'); 
		setCookie('hash', $this->userInfoArray['hash'], time() + $ex, '/'); 
	}
		
	private function setAuthSession(){
		if($this->userInfoArray){
			$_SESSION['user_id'] = $this->userInfoArray['id'];
		}
	}    
   
	
}

?>