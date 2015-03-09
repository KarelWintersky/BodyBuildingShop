<?php
Class Adm_News_Save{

	private $registry;
	
	private $Adm_News_Rss;
	private $Avatar;
		
	public function __construct($registry){
		$this->registry = $registry;
		
		$this->Adm_News_Rss = new Adm_News_Rss($this->registry);
		$this->Avatar = new Adm_Avatar($this->registry,'news');
	}
			
	private function do_alias($alias,$name,$id,$type){
		$alias = ($alias) 
			? $alias 
			: Common_Useful::rus2translit($name);
		
		$alias = mb_strtolower($alias,'utf-8');
		
		$alias = $this->generate_alias($alias,$id,$type);
		
		return $alias;
	}
	
	public function do_save(){
		foreach($_POST as $key => $val) $$key = $val;

		$published = (isset($published)) ? 1 : 0;
		$rss = (isset($rss)) ? 1 : 0;
		
		$longtitle = ($longtitle) ? $longtitle : $name;
		$alias = $this->do_alias($alias,$name,$id,$type);
		
		$content = Adm_Helper_Content::delete_junk($content);
		$content = Adm_Helper_Content::div_replace($content);		
		
		if($id){
			$qLnk = mysql_query(sprintf("
					UPDATE
						news
					SET
						name = '%s',
						alias = '%s',
						longtitle = '%s',
						date = '%s',
						content = '%s',
						introtext = '%s',
						published = '%d',
						rss = '%d'
					WHERE
						id = '%d';
					",
					mysql_real_escape_string($name),
					$alias,
					mysql_real_escape_string($longtitle),
					sprintf('%s %s',
							date('Y-m-d',strtotime($date)),
							date('H:i:s')
							),
					mysql_real_escape_string($content),
					mysql_real_escape_string($introtext),
					$published,
					$rss,
					$id
					));			
		}else{
			mysql_query(sprintf("
					INSERT INTO
					news
					(
						type,
						name,
						alias,
						longtitle,
						date,
						content,
						introtext,
						published,
						rss
					)
					VALUES
					(
						'%d',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%d',
						'%d'
					)
					",
					$type,
					mysql_real_escape_string($name),
					$alias,
					mysql_real_escape_string($longtitle),
					sprintf('%s %s',
							date('Y-m-d',strtotime($date)),
							date('H:i:s')
					),
					mysql_real_escape_string($content),
					mysql_real_escape_string($introtext),
					$published,
					$rss
					));		
			$id = mysql_insert_id();

			$this->registry['doer']->set_rp(
					sprintf('/adm/news/%d/',$id)
					);
		}
		
		$this->registry['CL_avatar_upload']->upload_avatars($id);
		
		$this->Adm_News_Rss->do_rss();		
	}
			
	public function do_delete(){
		$id = $_POST['id'];
		
		mysql_query(sprintf("
				DELETE FROM
					news
				WHERE
					id = '%d'
				",
				$id				
				));
		
		$this->registry['CL_avatar_delete']->delete_all($id);
	}
	
	private function generate_alias($alias,$id,$type){
		$type = Adm_News_Types::get_types($type);
		
		$classname = __CLASS__.'_'.$type[2];
		$CL = new $classname($this->registry);
		
		$workurl = $alias;
		$i=1;
		while(!$CL->check_alias($workurl,$id)){
			$workurl = $alias.'-'.$i;
			$i++;
		}
		
		return $workurl;
	}	
		
}
?>