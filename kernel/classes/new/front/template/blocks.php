<?php

class Front_Template_Blocks extends Common_Rq
{

    private $registry;

    public function __construct($registry)
    {
        $this->registry = $registry;
    }

    public function sidebar_faq()
    {
        $items = array(
            0 => 'FAQ / Ознакомьтесь',
            '/faq/#how-buy' => 'Как сделать заказ?',
            '/faq/#how-send' => 'Какие способы доставки есть?',
            '/faq/#how-pay' => 'Как оплатить заказ?',
            '/faq/#how-money' => 'Как быстро поступят деньги?',
            '/faq/#how-send2' => 'Когда Вы отправите заказ?',
            '/faq/#how-send2_1' => 'Когда я получу уведомление?',
            '/faq/#how-goods-done' => 'Есть ли товар в наличии?',
            '/faq/#how-shop' => 'Есть ли у вас розничный магазин?',
            '/faq/#how-self' => 'Могу я забрать заказ сам?',
            '/faq/#how-cost' => 'Как формируются цены?',
            '/faq/#how-courier' => 'Вы можете отправить заказ EMS?',
        );

        $data = array();
        foreach ($items as $key => $name)
            $data[] = array(
                'val' => $key,
                'name' => $name,
            );

        return Common_Template_Select::opts( $data );
    }

    public function sidebar_growers()
    {
        $data = array();
        $qLnk = mysql_query( "
				SELECT
					id,
					name,
					alias
				FROM
					growers
				WHERE
					goods_count > 0
				ORDER BY
					name ASC;
				" );
        while ($g = mysql_fetch_assoc( $qLnk ))
            $data[] = array(
                'val' => $g[ 'alias' ],
                'name' => $g[ 'name' ],
                'selected' => (isset( $this->registry[ 'grower' ] ) && $this->registry[ 'grower' ][ 'id' ] == $g[ 'id' ]),
            );

        return Common_Template_Select::opts( $data, 'Производитель' );
    }

    private function this_page_url()
    {
        $url = trim( $_SERVER[ 'REQUEST_URI' ], '/' );

        return $url;
    }

    public function do_blocks()
    {
        $blocks = array(
            'sidebar_faq' => $this->sidebar_faq(),
            'sidebar_growers' => $this->sidebar_growers(),
            'this_page_url' => $this->this_page_url(),
            'share' => $this->do_rq( 'share', NULL ),
        );

        if (!isset( $this->registry[ 'mainpage' ] )) $blocks[ 'module' ] = false;

        foreach ($blocks as $k => $v) $this->registry[ 'CL_template_vars' ]->set( $k, $v );
    }

}

