<?
	Class f_Search{

		private $registry;

		public function pgc(){}

		public function __construct($registry){
			$this->registry = $registry;
			$this->registry->set('f_search',$this);
		}

		public function path_check(){

			$this->registry['f_404'] = false;
			$path_arr = $this->registry['route_path'];

			$this->registry['template']->add2crumbs('news','Результаты поиска');
			$this->registry['noindex'] = true;

			if(count($path_arr)==0 && isset($_GET['q']) && $_GET['q']!=''){
				$this->registry['template']->set('c','search/main');
				$this->registry['longtitle'] = 'Поиск';
				return true;
			}

			$this->registry['f_404'] = true;
			return false;
		}

		public function item_rq($name,$a = NULL){
			require($this->registry['template']->TF.'blocks/item/search/'.$name.'.html');
		}

		private function find_goods($q){
			$goods = array();
			$qLnk = mysql_query("
					SELECT
						goods.id,
						goods.name,
						goods.alias,
						levels.alias AS level_alias,
						parent_tbl.alias AS parent_level_alias,
						growers.name AS grower
					FROM
						goods
					LEFT OUTER JOIN growers ON growers.id = goods.grower_id
					LEFT OUTER JOIN levels ON levels.id = goods.level_id
					LEFT OUTER JOIN levels AS parent_tbl ON parent_tbl.id = levels.parent_id
					WHERE
						goods.published = 1
						AND
						goods.parent_barcode = 0
						AND
						goods.weight > 0
						AND
							(goods.name LIKE '%".$q."%'
							OR
							goods.content LIKE '%".$q."%'
							OR
							goods.ingredients LIKE '%".$q."%'
							OR
							goods.recommendations LIKE '%".$q."%')
					ORDER BY
						goods.name ASC;
					");
			while($g = mysql_fetch_assoc($qLnk)) $goods[$g['id']] = $g;
			if(count($goods)==0) return array();
			
			$qLnk = mysql_query(sprintf("
					SELECT
						MIN(price) AS min_price,
						goods_id
					FROM
						goods_barcodes
					WHERE
						goods_id IN (%s)
					GROUP BY
						goods_id
					",
					implode(",",array_keys($goods))
					));
			while($g = mysql_fetch_assoc($qLnk)) $goods[$g['goods_id']]['price'] = $g['min_price'];
			
			$output = array();
			foreach($goods as $g){
				$output[] = array(
						'lnk' => '/'.$g['parent_level_alias'].'/'.$g['level_alias'].'/'.$g['alias'].'/',
						'name' => (($g['grower']!='') ? '«'.$g['grower'].'». ' : '').$g['name'],
						'add' => (isset($g['price'])) ? sprintf('от %s руб.',$this->registry['logic']->price2read($g['price'])) : '',
				);
			}	

			return $output;
		}
		
		public function results(){

			$headers = array(
				'goods' => 'Товары',
				'pages' => 'Страницы',
				'articles' => 'Статьи',
				'growers' => 'Производители',
				'news' => 'Новости',
			);

			$results = array();
			$q = $_GET['q'];

			//статьи
			$qLnk = mysql_query("
								SELECT
									articles.name,
									articles.alias
								FROM
									articles
								WHERE
									articles.published = 1
									AND
									(articles.name LIKE '%".$q."%'
									OR
									articles.content LIKE '%".$q."%')
								ORDER BY
									articles.name ASC;
								");
			while($g = mysql_fetch_assoc($qLnk)){
				$results['articles'][] = array(
					'lnk' => '/articles/'.$g['alias'].'/',
					'name' => $g['name'],
				);
			}

			//новости
			$qLnk = mysql_query("
								SELECT
									news.name,
									news.alias
								FROM
									news
								WHERE
									news.published = 1
									AND
									(news.name LIKE '%".$q."%'
									OR
									news.content LIKE '%".$q."%')
								ORDER BY
									news.name ASC;
								");
			while($g = mysql_fetch_assoc($qLnk)){
				$results['news'][] = array(
					'lnk' => '/news/'.$g['alias'].'/',
					'name' => $g['name'],
				);
			}

			//страницы
			$qLnk = mysql_query("
								SELECT
									pages.name,
									pages.alias
								FROM
									pages
								WHERE
									pages.published = 1
									AND
									(pages.name LIKE '%".$q."%'
									OR
									pages.content LIKE '%".$q."%')
								ORDER BY
									pages.name ASC;
								");
			while($g = mysql_fetch_assoc($qLnk)){
				$results['pages'][] = array(
					'lnk' => '/'.$g['alias'].'/',
					'name' => $g['name'],
				);
			}

			$results['goods'] = $this->find_goods($q);

			//производители
			$qLnk = mysql_query("
								SELECT
									growers.name,
									growers.alias
								FROM
									growers
								WHERE
									growers.name LIKE '%".$q."%'
									OR
									growers.content LIKE '%".$q."%'
								ORDER BY
									growers.name ASC;
								");
			while($g = mysql_fetch_assoc($qLnk)){
				$results['growers'][] = array(
					'lnk' => '/growers/'.$g['alias'].'/',
					'name' => $g['name'],
				);
			}

			foreach($results as $g_id => $resultgroup){

				echo '<h2>'.$headers[$g_id].'<span>('.count($resultgroup).')</span></h2>';

				$i = 1;
				foreach($resultgroup as $r){
					$r['num'] = $i;
					$r['class'] = ($i==count($resultgroup)) ? 'last' : '';
					$this->item_rq('result',$r);

					$i++;
				}

			}

		}

	}
?>