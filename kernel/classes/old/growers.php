<?
	Class Growers{
		
		private $registry;
		
		public function __construct($registry, $frompage = true){
			$this->registry = $registry;
			
	        if($frompage){
		        $route = $this->registry['aias_path'];
		        array_shift($route);
		        	        
		        if(count($route)==0){
		        	$this->registry['f_404'] = false;
		        	$this->registry['template']->set('c','growers/main');
		        }elseif(count($route)==1 && $this->grower_exists($route[0])){
		        	$this->registry['f_404'] = false;
		        	$this->registry['template']->set('c','growers/grower');
		        }         	
	        }		
			
		}

		private function item_rq($name,$a = NULL){
			require($this->registry['template']->TF.'item/growers'.DIRSEP.$name.'.html');
		}			
		
		private function grower_exists($id){
			if(!is_numeric($id)){return false;}
			
			if($id==0){
				$this->registry['action'] = 501;
				return true;
			}			
			
			$qLnk = mysql_query("
								SELECT
									*
								FROM
									growers
								WHERE
									growers.id = '".$id."'
								LIMIT 1;
								");
			if(mysql_num_rows($qLnk)>0){
				$this->registry['grower'] = mysql_fetch_assoc($qLnk);
				$this->registry['action'] = 500;
				return true;
			}
			return false;
		}
		
		public function growers_list(){
			$qLnk = mysql_query("
								SELECT
									growers.id,
									growers.name
								FROM
									growers
								ORDER BY
									growers.sort ASC;
								");
			while($g = mysql_fetch_assoc($qLnk)){
				$this->item_rq('grower_item',$g);
			}
		}
	
		public function del_grower(){
			foreach($_POST as $key => $val){$$key = (is_array($val)) ? $val : mysql_real_escape_string($val);}
			mysql_query("DELETE FROM growers WHERE growers.id = '".$id."';");	
			
			$photomanager = new Photomanager($this->registry);
			$photomanager->unlink_grower_avatar($avatar);
			
		}
		
		public function add_grower(){
			foreach($_POST as $key => $val){$$key = (is_array($val)) ? $val : mysql_real_escape_string($val);}
			
			$alias = ($alias!='') ? $alias : Common_Useful::rus2translit($name);
			$alias = $this->urlGenerate($alias,$id);				
			
			$longtitle = ($longtitle!='') ? $longtitle : $name;
			
			mysql_query("
						INSERT INTO
							growers
							(growers.name,
								growers.alias,
									growers.longtitle,
										growers.content,
											growers.seo_kw,
												growers.seo_dsc)
							VALUES
							('".$name."',
								'".$alias."',
									'".$longtitle."',
										'".$content."',
											'".$seo_kw."',
												'".$seo_dsc."')							
						");

			$grower_id = mysql_insert_id();
			
			$rp = trim($rp,'/');
			$rp_arr = explode('/',$rp);
			$rp_arr[2] = $grower_id;

			$rp = '/'.implode('/',$rp_arr).'/';
			
			$this->registry['doer']->set_rp($rp);				
			
		}
		
		public function upd_grower(){
			foreach($_POST as $key => $val){$$key = (is_array($val)) ? $val : mysql_real_escape_string($val);}
			
			$photomanager = new Photomanager($this->registry);
			$avatar = $photomanager->upload_grower_avatar($old_avatar);			
			
			$alias = ($alias!='') ? $alias : Common_Useful::rus2translit($name);
			$alias = $this->urlGenerate($alias,$id);			
			
			$longtitle = ($longtitle!='') ? $longtitle : $name;
			
			mysql_query("
						UPDATE
							growers
						SET
							growers.name = '".$name."',
							growers.alias = '".$alias."',
							growers.longtitle = '".$longtitle."',
							growers.content = '".$content."',
							growers.seo_kw = '".$seo_kw."',
							growers.seo_dsc = '".$seo_dsc."',
							growers.avatar = '".$avatar."'
						WHERE
							growers.id = '".$id."';
						");
						
		}
		
	    private function checkFreeUrl($url,$id){
			$qLnk = mysql_query("
								SELECT
									COUNT(*)
								FROM
									growers
								WHERE
									growers.alias = '".$url."'
									AND
									growers.id <> ".$id.";
								");
			
			return (mysql_result($qLnk,0)==1) ? false : true;
			  		
	    }
	       
	    private function urlGenerate($url,$id){
	    	$workurl = $url;
	    	$i=1;
	    	while(!$this->checkFreeUrl($workurl,$id)){
	    		$workurl = $url.'-'.$i;
	    		$i++;
	    	}
	    	return $workurl;
	    }			
		
	    public function growers_sort(){
			foreach($_POST['sort'] as $id => $sort){
				mysql_query("UPDATE growers SET growers.sort = '".$sort."' WHERE growers.id = '".$id."';");
			}	    	
	    }
	    
	}
?>