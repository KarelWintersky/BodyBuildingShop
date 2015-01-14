<?
Class Articles{

	private $registry;

	public function __construct($registry, $frompage = true){
		$this->registry = $registry;
		$this->registry->set('articles',$this);

        if($frompage){
	        $route = $this->registry['aias_path'];
	        array_shift($route);

	        if(count($route)==0){
	        	$this->registry['f_404'] = false;
	        	$this->registry['template']->set('c','articles/main');
	        }elseif(count($route)==1 && $this->article_check($route[0])){
	        	$this->registry['f_404'] = false;
	        	$this->registry['template']->set('c','articles/article');
	        }
        }

	}

	private function item_rq($name,$a = NULL){
		require($this->registry['template']->TF.'item/articles/'.$name.'.html');
	}

	private function article_check($id){

		if($id==0){
			$this->registry['action'] = 902;
			return true;
		}

		if(!is_numeric($id)){return false;}

		$qLnk = mysql_query("
							SELECT
								articles.*
							FROM
								articles
							WHERE
								articles.id = '".$id."'
							LIMIT 1;
							");
		if(mysql_num_rows($qLnk)>0){
			$this->registry['article'] = mysql_fetch_assoc($qLnk);
			$this->registry['action'] = 901;
			return true;
		}
		return false;
	}

	public function articles_list(){
		$qLnk = mysql_query("
							SELECT
								articles.*
							FROM
								articles
							ORDER BY
								articles.name ASC;
							");
		$i = 1;
		while($a = mysql_fetch_assoc($qLnk)){
			$a['sort'] = $i;
			$this->item_rq('list_item',$a);
			$i++;
		}
	}

	public function articles_sort(){
		foreach($_POST['sort'] as $id => $sort){
			mysql_query("UPDATE articles SET articles.sort = '".$sort."' WHERE articles.id = '".$id."';");
		}
	}

    private function checkFreeUrl($url,$id){
		$qLnk = mysql_query("
							SELECT
								COUNT(*)
							FROM
								articles
							WHERE
								articles.alias = '".$url."'
								AND
								articles.id <> ".$id.";
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

    public function article_add(){
		foreach($_POST as $key => $val){$$key = (is_array($val)) ? $val : mysql_real_escape_string($val);}

		$published = (isset($published) && $published==1) ? 1 : 0;
		$in_sitemap = (isset($in_sitemap) && $in_sitemap==1) ? 1 : 0;
		$socialblock = (isset($socialblock) && $socialblock==1) ? 1 : 0;

		$longtitle = ($longtitle!='') ? $longtitle : $name;
		$main_h2 = ($main_h2!='') ? $main_h2 : $name;
		$alias = ($alias!='') ? $alias : Common_Useful::rus2translit($name);
			$alias = mb_strtolower($alias,'utf-8');
		$alias = $this->urlGenerate($alias,$id);

		$qLnk = mysql_query("SELECT MAX(articles.sort)+1 FROM articles;");
		$sort = mysql_result($qLnk,0);

		$img_alt = str_replace('"','',$img_alt);

		$content = Adm_Helper_Content::delete_junk($content);
		
		mysql_query("
					INSERT INTO
						articles
						(articles.name,
							articles.longtitle,
								articles.alias,
									articles.seo_kw,
										articles.seo_dsc,
											articles.introtext,
												articles.content,
													articles.published,
														articles.in_sitemap,
															articles.sort,
																articles.socialblock,
																	articles.img_alt,
																		articles.main_h2)
						VALUES
						('".$name."',
							'".$longtitle."',
								'".$alias."',
									'".$seo_kw."',
										'".$seo_dsc."',
											'".$introtext."',
												'".$content."',
													'".$published."',
														'".$in_sitemap."',
															'".$sort."',
																'".$socialblock."',
																	'".$img_alt."',
																		'".$main_h2."')
					");

		$id = mysql_insert_id();

		$rp = trim($rp,'/');
		$rp_arr = explode('/',$rp);
		$rp_arr[2] = $id;

		$rp = '/'.implode('/',$rp_arr).'/';

		$this->registry['doer']->set_rp($rp);
    }

	public function article_sav(){

		foreach($_POST as $key => $val){$$key = (is_array($val)) ? $val : mysql_real_escape_string($val);}

		$photomanager = new Photomanager($this->registry);
		$avatar = $photomanager->upload_article_avatar($old_avatar,$id);

		$published = (isset($published) && $published==1) ? 1 : 0;
		$in_sitemap = (isset($in_sitemap) && $in_sitemap==1) ? 1 : 0;
		$socialblock = (isset($socialblock) && $socialblock==1) ? 1 : 0;

		$longtitle = ($longtitle!='') ? $longtitle : $name;
		$main_h2 = ($main_h2!='') ? $main_h2 : $name;
		$alias = ($alias!='') ? $alias : Common_Useful::rus2translit($name);
			$alias = mb_strtolower($alias,'utf-8');
		$alias = $this->urlGenerate($alias,$id);

		$img_alt = str_replace('"','',$img_alt);

		$content = Adm_Helper_Content::delete_junk($content);
		
		mysql_query("
					UPDATE
						articles
					SET
						articles.name = '".$name."',
						articles.longtitle = '".$longtitle."',
						articles.alias = '".$alias."',
						articles.seo_kw = '".$seo_kw."',
						articles.seo_dsc = '".$seo_dsc."',
						articles.introtext = '".$introtext."',
						articles.content = '".$content."',
						articles.published = '".$published."',
						articles.in_sitemap = '".$in_sitemap."',
						articles.socialblock = '".$socialblock."',
						articles.avatar = '".$avatar."',
						articles.img_alt = '".$img_alt."',
						articles.main_h2 = '".$main_h2."'
					WHERE
						articles.id = '".$id."'
					");

	}

}
?>