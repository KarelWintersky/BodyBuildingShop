<?
	Class f_Pitanie{

		private $registry;

		public function __construct($registry){
			$this->registry = $registry;
			$this->registry->set('f_articles',$this);
		}

		public function path_check($alias){

			$this->registry['f_404'] = false;

			if($this->article_exists($alias)){
				$this->registry['template']->set('c','articles/article');
				return true;
			}

			$this->registry['f_404'] = true;
			return false;
		}

		public function item_rq($name,$a = NULL){
			require($this->registry['template']->TF.'blocks/item/articles/'.$name.'.html');
		}

		private function article_exists($alias){
			$qLnk = mysql_query("
								SELECT
									articles.*
								FROM
									articles
								WHERE
									articles.alias = '".$alias."'
									AND
									articles.published = 1
								LIMIT 1;
								");
			if(mysql_num_rows($qLnk)>0){
				$article = mysql_fetch_assoc($qLnk);
				$article['content'] = $this->page_content($article['content']);

				$this->registry['article'] = $article;

				$this->registry['longtitle'] = $this->registry['article']['longtitle'];
				$this->registry['seo_kw'] = $this->registry['article']['seo_kw'];
				$this->registry['seo_dsc'] = $this->registry['article']['seo_dsc'];

				$this->registry['template']->add2crumbs('pitanie','Обмен опытом');
				$this->registry['template']->add2crumbs($this->registry['article']['alias'],$this->registry['article']['name']);

				return true;
			}

			return false;
		}

		public function main_page(){
			$qLnk = mysql_query("
								SELECT
									articles.main_h2,
									articles.alias,
									articles.img_alt,
									articles.introtext,
									articles.avatar
								FROM
									articles
								WHERE
									articles.published = 1
									AND
									articles.id IN (1,2,3)
								LIMIT 3;
								");
			while($a = mysql_fetch_assoc($qLnk)){
				$this->item_rq('mainpage_item',$a);
			}
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