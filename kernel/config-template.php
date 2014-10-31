<?
	define('DIRSEP',DIRECTORY_SEPARATOR,true);
	$site_path = realpath(dirname(__FILE__).DIRSEP.'..'.DIRSEP).DIRSEP;
	define ('ROOT_PATH',$site_path);

	date_default_timezone_set('Europe/Moscow');
	
	define('DB_HOST','localhost',true);
	define('DB_U','whbody2',true);
	define('DB_P','54321155',true);
	define('DB_NAME','whbody2',true);

	define('PHOTO_DIM_STR','80x80,122x122',true);
	define('LEV_PHOTO_DIM_STR','160x160',true);
	define('GOODS_PHOTO_DIR',ROOT_PATH.'public/foto/goods'.DIRSEP,true);
	define('LEV_PHOTO_DIR',ROOT_PATH.'public/foto/levels'.DIRSEP,true);
	define('FEAT_PHOTO_DIR',ROOT_PATH.'public/foto/features'.DIRSEP,true);
	define('GROWER_PHOTO_DIR',ROOT_PATH.'public/foto/growers'.DIRSEP,true);
	define('ARTICLE_PHOTO_DIR',ROOT_PATH.'public/foto/articles'.DIRSEP,true);

	define('NEWS_PAGINATE',10,true);
	define('POPULAR_MAX',15,true);

	define('TOOLTIP_PRICE_1','Цена п/п$$цена на товар при полной предоплате',true);
	define('TOOLTIP_PRICE_2','Цена н/п$$цена на товар при заказе наложенным платежом',true);

	define('TOOLTIP_PAYMENT_1','Наложенный платеж$$Оплата заказа на почте в момент получения заказа',true);
	define('TOOLTIP_PAYMENT_2','Предоплата$$Оплата квитанцией в любом отделении Сбербанка России или коммерческом банке',true);
	define('TOOLTIP_PAYMENT_3','Электронные деньги$$Оплата заказа через Webmoney или Яндекс.Деньги',true);
	define('TOOLTIP_PAYMENT_4','Оплата картой$$Мгновенная оплата заказа банковской картой в системе Робокасса',true);
	define('TOOLTIP_PAYMENT_6','Лицевой счет$$Вы можете оплатить товар, используя свой счет в нашем магазине',true);
	define('TOOLTIP_PAYMENT_7','Платежные системы$$Мгновенная оплата через любые платежные системы',true);

	define('TOOLTIP_DELIVERY_1','Почта$$Доставка заказа Почтой России',true);
	//define('TOOLTIP_DELIVERY_2','Курьер$$В данный момент курьерская доставка недоступна.',true);
	define('TOOLTIP_DELIVERY_2','Курьер$$Доставка курьерской службой в удобное для Вас время и место. <a href="/help/#courier" target="_blank">Подробнее »</a>',true);
	define('TOOLTIP_DELIVERY_3','Транспортная компания$$Доставка транспортной компанией доступна только для заказов от 10 000 рублей',true);
	define('TOOLTIP_DELIVERY_4','Самовывоз$$Самовывоз заказа из нашего магазина на Новаторов 98.<br><a href="/about/" target="_blank">Как нас найти</a>',true);

	define('TOOLTIP_COUPON','Подарочный купон$$Данные купоны выдаются администрацией или разыгрываются в различных акциях нашего магазина. Следите за новостями.',true);

	define('THIS_URL','http://www.bodybuilding-shop.ru/',true);

	//robokassa
	define('ROBOKASSA_LG','bodybuilding-shop',true);
	define('ROBOKASSA_PW','Isdfisdoj23423',true);
	define('ROBOKASSA_PW_2','sdfsd2323423ss',true);
	define('ROBOKASSA_CURR','BANKOCEAN2R',true); //валюта
	define('ROBOKASSA_LANG','ru',true); //язык

?>