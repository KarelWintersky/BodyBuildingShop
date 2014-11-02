<?
	Class News{

		private $registry;

		public function __construct($registry, $frompage = true){
			$this->registry = $registry;

	        if($frompage){
		        $route = $this->registry['aias_path'];
		        array_shift($route);

		        if(count($route)==0){
		        	$this->registry['f_404'] = false;
		        	$this->registry['template']->set('c','news/main');
		        }elseif(count($route)==1 && $this->newsExists($route[0])){
		        	$this->registry['f_404'] = false;
		        	$this->registry['template']->set('c','news/news');
	        	}
	        }

		}

		private function item_rq($name,$a = NULL){
			require($this->registry['template']->TF.'item/news/'.$name.'.html');
		}

		private function newsExists($id){

			if(!is_numeric($id)){return false;}

			if($id==0){
				$this->registry['action'] = 301;
				return true;
			}

			$qLnk = mysql_query("SELECT * FROM news WHERE news.id = '".$id."';");
			if(mysql_num_rows($qLnk)>0){
				$this->registry['newsitem'] = mysql_fetch_assoc($qLnk);
				$this->registry['action'] = 300;
				return true;
			}else{
				return false;
			}
		}

		public function news_list(){
			$qLnk = mysql_query("
								SELECT
									news.id,
									news.name,
									news.published,
									news.date
								FROM
									news
								ORDER BY
									news.date DESC,
									news.id DESC;
								");
			while($p = mysql_fetch_assoc($qLnk)){
				$this->item_rq('news_list',$p);
			}
		}

		public function news_del(){
			foreach($_POST as $key => $val){$$key = (is_array($val)) ? $val : mysql_real_escape_string($val);}
			mysql_query("DELETE FROM news WHERE news.id = '".$id."';");

		}

		public function news_add(){
			foreach($_POST as $key => $val){$$key = (is_array($val)) ? $val : mysql_real_escape_string($val);}

			$published = (isset($published) && $published==1) ? 1 : 0;
			$rss = (isset($rss) && $rss==1) ? 1 : 0;

			$alias = ($alias!='') ? $alias : Common_Useful::rus2translit($name);
			$alias = $this->urlGenerate($alias,$id);

			mysql_query("
						INSERT INTO
							news
						(news.name,
							news.alias,
								news.date,
									news.content,
										news.published,
											news.rss)
						VALUES
						('".$name."',
							'".$alias."',
								'".Common_Useful_Date::read2date($date)." ".date('H:i:s')."',
									'".$content."',
										'".$published."',
											'".$rss."')
						");

			$news_id = mysql_insert_id();

			$rp = trim($rp,'/');
			$rp_arr = explode('/',$rp);
			$rp_arr[2] = $news_id;

			$rp = '/'.implode('/',$rp_arr).'/';

			$this->registry['doer']->set_rp($rp);

			$Rss = new Rss($this->registry);
			$Rss->do_rss();

		}

		public function news_sav(){
			foreach($_POST as $key => $val){$$key = (is_array($val)) ? $val : mysql_real_escape_string($val);}

			$published = (isset($published) && $published==1) ? 1 : 0;

			$alias = ($alias!='') ? $alias : Common_Useful::rus2translit($name);
			$alias = $this->urlGenerate($alias,$id);

			$qLnk = mysql_query("
								UPDATE
									news
								SET
									news.name = '".$name."',
									news.alias = '".$alias."',
									news.date = '".Common_Useful_Date::read2date($date)." ".date('H:i:s')."',
									news.content = '".$content."',
									news.published = '".$published."',
									news.rss = '".$rss."'
								WHERE
									news.id = '".$id."';
								");

			$Rss = new Rss($this->registry);
			$Rss->do_rss();

		}

	    private function checkFreeUrl($url,$id){
			$qLnk = mysql_query("
								SELECT
									COUNT(*)
								FROM
									news
								WHERE
									news.alias = '".$url."'
									AND
									news.id <> ".$id.";
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

	}
?>