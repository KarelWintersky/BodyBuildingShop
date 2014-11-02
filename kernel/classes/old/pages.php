<?
Class Pages{

	private $registry;

	public function __construct($registry, $frompage = true){
		$this->registry = $registry;

        if($frompage){
	        $route = $this->registry['aias_path'];
	        array_shift($route);

	        if(count($route)==0){
	        	$this->registry['f_404'] = false;
	        	$this->registry['template']->set('c','pages/main');
	        }elseif(count($route)==1 && $this->pageExists($route[0])){
	        	$this->registry['f_404'] = false;
	        	$this->registry['template']->set('c','pages/page');
	        }
        }

	}

	private function item_rq($name,$a = NULL){
		require($this->registry['template']->TF.'item/pages/'.$name.'.html');
	}

	private function pageExists($id){

		if(!is_numeric($id)){return false;}

		if($id==0){
			$this->registry['action'] = 201;
			return true;
		}

		$qLnk = mysql_query("SELECT * FROM pages WHERE pages.id = '".$id."';");
		if(mysql_num_rows($qLnk)>0){
			$this->registry['page'] = mysql_fetch_assoc($qLnk);
			$this->registry['action'] = 200;
			return true;
		}else{
			return false;
		}
	}

	public function pages_list(){
		$qLnk = mysql_query("
							SELECT
								pages.id,
								pages.name,
								pages.published
							FROM
								pages
							ORDER BY
								pages.name ASC;
							");
		while($p = mysql_fetch_assoc($qLnk)){
			$this->item_rq('pages_list',$p);
		}

	}

	public function pag_del(){
		foreach($_POST as $key => $val){$$key = (is_array($val)) ? $val : mysql_real_escape_string($val);}
		mysql_query("DELETE FROM pages WHERE pages.id = '".$id."';");

	}

	public function pag_add(){
		foreach($_POST as $key => $val){$$key = (is_array($val)) ? $val : mysql_real_escape_string($val);}

		$published = (isset($published) && $published==1) ? 1 : 0;
		$in_sitemap = (isset($in_sitemap) && $in_sitemap==1) ? 1 : 0;
		$socialblock = (isset($socialblock) && $socialblock==1) ? 1 : 0;

		$seo_title = ($seo_title!='') ? $seo_title : $name;
		$h2_title = ($h2_title!='') ? $h2_title : $name;

		$alias = ($alias!='') ? $alias : Common_Useful::rus2translit($name);
		$alias = $this->urlGenerate($alias,$id);

		mysql_query("
					INSERT INTO
						pages
					(pages.name,
						pages.h2_title,
							pages.alias,
								pages.seo_title,
									pages.seo_kw,
										pages.seo_dsc,
											pages.published,
												pages.socialblock,
													pages.in_sitemap,
														pages.content)
					VALUES
					('".$name."',
						'".$h2_title."',
							'".$alias."',
								'".$seo_title."',
									'".$seo_kw."',
										'".$seo_dsc."',
											'".$published."',
												'".$socialblock."',
													'".$in_sitemap."',
														'".$content."')
					");

		$page_id = mysql_insert_id();

		$rp = trim($rp,'/');
		$rp_arr = explode('/',$rp);
		$rp_arr[2] = $page_id;

		$rp = '/'.implode('/',$rp_arr).'/';

		$this->registry['doer']->set_rp($rp);

	}

	public function pag_sav(){
		foreach($_POST as $key => $val){$$key = (is_array($val)) ? $val : mysql_real_escape_string($val);}

		$published = (isset($published) && $published==1) ? 1 : 0;
		$in_sitemap = (isset($in_sitemap) && $in_sitemap==1) ? 1 : 0;
		$socialblock = (isset($socialblock) && $socialblock==1) ? 1 : 0;

		$seo_title = ($seo_title!='') ? $seo_title : $name;
		$h2_title = ($h2_title!='') ? $h2_title : $name;

		$alias = ($alias!='') ? $alias : Common_Useful::rus2translit($name);
		$alias = $this->urlGenerate($alias,$id);

		$qLnk = mysql_query("
							UPDATE
								pages
							SET
								pages.name = '".$name."',
								pages.h2_title = '".$h2_title."',
								pages.alias = '".$alias."',
								pages.seo_title = '".$seo_title."',
								pages.seo_kw = '".$seo_kw."',
								pages.seo_dsc = '".$seo_dsc."',
								pages.published = '".$published."',
								pages.socialblock = '".$socialblock."',
								pages.in_sitemap = '".$in_sitemap."',
								pages.content = '".$content."'
							WHERE
								pages.id = '".$id."';
							");

	}

    private function checkFreeUrl($url,$id){
		$qLnk = mysql_query("
							SELECT
								COUNT(*)
							FROM
								pages
							WHERE
								pages.alias = '".$url."'
								AND
								pages.id <> ".$id.";
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