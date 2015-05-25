<?
	require('config.php');
	require('core.php');
	
	$registry = new Registry();
		$registry->set('config',$config);
	
	$db = new Database($registry);	
	
	$logic = new Logic($registry); $logic->register_params();
	
	$registry->set ('logic', $logic);
	$registry->set ('db', $db);

	if(isset($argv[1])){
		switch($argv[1]){
			case 'do_orders':
				$blocks = new Blocks($registry,false);
				$blocks->cron_do_orders();
				break;
			case 'go_goods_present':
				$blocks = new Blocks($registry,false);
				$blocks->cron_go_goods_present();
				break;
			case 'do_goods_absence':
				$blocks = new Blocks($registry,false);
				$blocks->cron_do_goods_absence();
				break;
			case 'do_goods_prices':
				$blocks = new Blocks($registry,false);
				$blocks->cron_do_goods_prices();
				break;
			case 'do_news':
				$Adm_Settings_Mailout = new Adm_Settings_Mailout($registry,false);
				$Adm_Settings_Mailout->do_mailout();
				break;
			case 'do_rezerv_orders':
				$seettings = new Settings($registry,false);
				$seettings->do_rezerv_orders();
		}
	}

?>