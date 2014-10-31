<?
	Class f_News{

		private $registry;

		public function pgc(){}

		public function __construct($registry){
			$this->registry = $registry;
			$this->registry->set('f_news',$this);
		}

		public function path_check(){

			$this->registry['f_404'] = false;
			$path_arr = $this->registry['route_path'];

			$this->registry['template']->add2crumbs('news','Новости');

			if(count($path_arr)==0){
				$this->registry['template']->set('c','news/list');
				$this->registry['longtitle'] = 'Новости';
				return true;
			}elseif(count($path_arr)==1 && $this->news_exists($path_arr[0])){
				$this->registry['template']->set('c','news/page');
				return true;
			}

			$this->registry['f_404'] = true;
			return false;
		}

		public function item_rq($name,$a = NULL){
			require($this->registry['template']->TF.'blocks/item/news/'.$name.'.html');
		}

		public function news_exists($alias){
			$qLnk = mysql_query("
								SELECT
									news.*
								FROM
									news
								WHERE
									news.alias = '".$alias."'
									AND
									news.published = 1
								LIMIT 1;
								");
			if(mysql_num_rows($qLnk)>0){
				$this->registry['news_info'] = mysql_fetch_assoc($qLnk);

				$crumb_name = mb_substr($this->registry['news_info']['name'],0,70,'utf-8');
				$crumb_dots = ($crumb_name==$this->registry['news_info']['name']) ? '' : '...';

				$this->registry['template']->add2crumbs($this->registry['news_info']['alias'],$crumb_name.$crumb_dots);

				$this->registry['longtitle'] = $this->registry['news_info']['name'];

				return true;
			}
			return false;
		}

		private function mk_pagination(){
			$PAGING = NEWS_PAGING;

	    	$page = (isset($_GET['page'])) ? $_GET['page'] : 1;
	    	$offset = $PAGING*($page-1);

			$this->registry['news_paging'] = $PAGING;

	    	return "LIMIT ".$offset.", ".$PAGING;
		}

		public function news_list(){

			$q_amount = $this->mk_pagination();

			$qLnk = mysql_query("
								SELECT SQL_CALC_FOUND_ROWS
									news.name,
									news.date,
									news.alias
								FROM
									news
								WHERE
									news.published = 1
								ORDER BY
									news.date DESC,
									news.id DESC
								".$q_amount.";
								");
			$qA = mysql_query("SELECT FOUND_ROWS();");
	   		$this->registry['news_amount'] = mysql_result($qA,0);

			while($n = mysql_fetch_assoc($qLnk)){
				$this->item_rq('news_item',$n);
			}
		}

		public function pagination(){
			$pages_amount = ceil($this->registry['news_amount']/$this->registry['news_paging']);
			$cur_page = (isset($_GET['page'])) ? $_GET['page'] : 1;
			if($pages_amount>1){
				ob_start();
				for($i=1;$i<=$pages_amount;$i++){
					$a['num'] = $i;
					$a['lnk'] = ($i>1) ? '/news/?page='.$i : '/news/';
					$a['active'] = ($i==$cur_page) ? 'active' : '';
					$this->item_rq('news_paging',$a);
				}
				$html = ob_get_contents();
				ob_end_clean();
				echo '<ul id="news_paging">'.$html.'</ul>';
			}
		}

		public function prev_next(){
			//next
			$qLnk = mysql_query("
								SELECT
									news.name,
									news.alias
								FROM
									news
								WHERE
									news.date > '".$this->registry['news_info']['date']."'
									AND
									news.published = 1
									AND
									news.id <> '".$this->registry['news_info']['id']."'
								ORDER BY
									news.date ASC
								LIMIT 1;
								");
			$next = (mysql_num_rows($qLnk)>0) ? mysql_fetch_assoc($qLnk) : false;

			//prev
			$qLnk = mysql_query("
								SELECT
									news.name,
									news.alias
								FROM
									news
								WHERE
									news.date <= '".$this->registry['news_info']['date']."'
									AND
									news.published = 1
									AND
									news.id <> '".$this->registry['news_info']['id']."'
								ORDER BY
									news.date DESC
								LIMIT 1;
								");
			$prev = (mysql_num_rows($qLnk)>0) ? mysql_fetch_assoc($qLnk) : false;

			$a = array($prev,$next);

			$this->item_rq('prev_next',$a);
		}

	}
?>