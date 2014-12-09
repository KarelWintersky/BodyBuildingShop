<?
	Class Rss{

		private $registry;
		private $RSS;

		public function __construct($registry){
			$this->registry = $registry;
		}

		public function do_rss(){

			$file = ROOT_PATH.'public_html/rss.xml';

			$news = array();
			$qLnk = mysql_query("
								SELECT
									*
								FROM
									news
								WHERE
									published = 1
									AND
									rss = 1
								ORDER BY
									date DESC;
								");
			while($n = mysql_fetch_assoc($qLnk)){
				$news[] = $n;
			}

				$this->RSS = new DOMDocument('1.0','UTF-8');
				$this->RSS->formatOutput = true;

				//общий контейнер
				$this->container = $this->RSS->createElement('rss');
				$this->container->appendChild(
						$this->RSS->createAttribute('version'))->appendChild(
								$this->RSS->createTextNode('2.0')
						);
				$this->RSS->appendChild($this->container);

				//канал
				$this->channel = $this->RSS->createElement('channel');
				$this->container->appendChild($this->channel);

				//свойства канала
				$title = $this->RSS->createElement('title','Спортивное питание');
				$this->channel->appendChild($title);

				$link = $this->RSS->createElement('link',THIS_URL);
				$this->channel->appendChild($link);

				$description = $this->RSS->createElement('description','Магазин Спортивного Питания');
				$this->channel->appendChild($description);

				$language = $this->RSS->createElement('language','ru');
				$this->channel->appendChild($language);

				$lastBuildDate = $this->RSS->createElement('lastBuildDate',$this->prepare_date($news[0]['date']));
				$this->channel->appendChild($lastBuildDate);

				foreach($news as $n) $this->node_recruite($n);

				$this->RSS->save($file);

		}

		private function node_recruite($n){
			$item = $this->RSS->createElement('item');

				$title = $this->RSS->createElement('title',$n['name']);
					$item->appendChild($title);
				$link = $this->RSS->createElement('link',THIS_URL.'news/'.$n['alias'].'/');
					$item->appendChild($link);
				$description = $this->RSS->createElement('description',$this->prepare_content($n['content']));
					$item->appendChild($description);
				$pubDate = $this->RSS->createElement('pubDate',$this->prepare_date($n['date']));
					$item->appendChild($pubDate);

			$this->channel->appendChild($item);
		}

		private function prepare_date($date){
		   $months = array(
		   		'01' => 'Jan',
		   		'02' => 'Feb',
		   		'03' => 'Mar',
		   		'04' => 'Apr',
		   		'05' => 'May',
		   		'06' => 'Jun',
		   		'07' => 'Jul',
		   		'08' => 'Aug',
		   		'09' => 'Sep',
		   		'10' => 'Oct',
		   		'11' => 'Nov',
		   		'12' => 'Dec'
		   		);

		   	$arr = explode(' ',$date);
			$day = explode('-',$arr[0]);
			$time = explode(':',$arr[1]);

		   	$result = date('D',strtotime($date)).', '.$day[2].' '.$months[$day[1]].' '.$day[0].' '.$time[0].':'.$time[1].':'.$time[2].' +0300';

		   	return $result;
		}

		private function prepare_content($content){

			$replace = array(
				'&laquo;' => '«',
				'&raquo;' => '»',
				'&ndash;' => '-',
				'&nbsp;' => ' ',
				'&eacute;' => '',
				'&uuml;' => '',
				'&ouml;' => '',
				'&iacute;' => '',
				'&oacute;' => '',
				'&aacute;' => '',
				'&Aacute;' => '',
				'&auml;' => '',
				'&hellip;' => '',
				'&Ccedil;' => '',
				'&ccedil;' => '',
				);

			foreach($replace as $s => $r) $content = str_replace($s,$r,$content);

			$content = $this->strip_imgs($content);

			return $content;
		}

		private function strip_imgs($content){
			$reg = "/<img .+>/i";
			$content = preg_replace($reg,'',$content);

			return $content;
		}

	}
?>