<?

	class Database{
		
		public function __construct(){
			$lnk=mysql_connect(DB_HOST,DB_U,DB_P) or die(mysql_error());
			mysql_select_db(DB_NAME,$lnk);
			mysql_query("SET NAMES UTF8;");
		}		
						
	}
?>