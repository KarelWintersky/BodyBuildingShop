<?
	session_start();
	
	require('../kernel/config.php');
	require('../kernel/core.php');
	
	$registry = new Registry();
	$db = new Database();
	
	$qLnk = mysql_query("SELECT NOW() FROM goods;");
	echo mysql_result($qLnk,0);
?>
