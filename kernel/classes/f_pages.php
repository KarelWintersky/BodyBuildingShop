<?
	Class f_Pages{

		private $registry;

		public function __construct($registry){
			$this->registry = $registry;
			$f_articles = new f_Pitanie($this->registry);
		}

		public function path_check(){

			$this->registry['f_404'] = false;
			$path_arr = $this->registry['route_path'];

			if(count($path_arr)==1 && $this->page_exists($path_arr[0])){
				$this->registry['template']->set('c','pages/page');
				return true;
			}elseif(count($path_arr)==1 && $this->registry['f_articles']->path_check($path_arr[0])){
				return true;
			}

			$this->registry['f_404'] = true;
			return false;
		}

		public function item_rq($name,$a = NULL){
			require($this->registry['template']->TF.'blocks/item/pages/'.$name.'.html');
		}

		private function page_exists($alias){
			$qLnk = mysql_query("
								SELECT
									pages.*
								FROM
									pages
								WHERE
									pages.alias = '".$alias."'
									AND
									pages.alias <> 'mainpage'
								LIMIT 1;
								");
			if(mysql_num_rows($qLnk)>0){
				$page = mysql_fetch_assoc($qLnk);
				$page['content'] = $this->page_content($page['content']);

				$this->registry['page'] = $page;

				$this->registry['longtitle'] = $this->registry['page']['seo_title'];
				$this->registry['seo_kw'] = $this->registry['page']['seo_kw'];
				$this->registry['seo_dsc'] = $this->registry['page']['seo_dsc'];

				$this->registry['template']->add2crumbs($this->registry['page']['alias'],$this->registry['page']['name']);

				return true;
			}

			return false;
		}

		private function page_content($content){
			$reg = "/{{a:(.*)}}/i";
			$content = preg_replace_callback($reg,array($this,'match_find'),$content);

			return $content;
		}

        private function match_find($matches){
        	$ids = explode(',',$matches[1]);
        	return (count($ids)>0) ? $this->do_articles_list($ids) : $matches[0];
        }

        private function do_articles_list($ids){
        	$data = array();
			$qLnk = mysql_query(sprintf("
				SELECT
					name,
					alias,
					introtext,
					avatar
				FROM
					articles
				WHERE
					id IN (%s)
				ORDER BY
					FIELD(id, %s)
				",
				implode(",",$ids),
				implode(",",$ids)
				));
			while($a = mysql_fetch_assoc($qLnk)) $data[] = $a;

			ob_start();
			$count = count($data);
			$i = 1;
			foreach($data as $a){
				$a['classes'] = $this->article_classes($count,$i);
				$this->item_rq('article',$a);

				$i++;
			}

			$a = array(
				'list' => ob_get_clean(),
				'class' => $this->container_class($count)
				);

			ob_start();
			$this->item_rq('articles_container',$a);
			return ob_get_clean();
        }

        private function container_class($count){
			if($count==1){
				return '';
			}elseif($count%2==0){
				return 'doubled';
			}else{
				return 'with_last';
			}
        }

        private function article_classes($count,$i){
			$classes = array();

			if($i==$count) $classes[] = 'last';
			if($i%2==0) $classes[] = 'even';

			return implode(' ',$classes);
        }

	}
?>