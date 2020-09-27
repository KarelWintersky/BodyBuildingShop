<?php

class Front_Template_Menu extends Common_Rq
{

    private $registry;

    function __construct($registry)
    {
        $this->registry = $registry;
    }

    private function get_data()
    {
        $items = array(
            0 => array( '', 'Главная' ),
            1 => array( '/about/', 'О магазине' ),
            999 => (!isset( $_SESSION[ 'user_id' ] )) ? array( '/register/', 'Регистрация' ) : array( '/profile/', 'Профиль' ),
            7 => array( '/pitanie/', 'Питание' ),
            41 => array( '/training/', 'Тренировки' ),
            14 => array( '/help/', 'Помощь' ),
            145 => array( '/help/#send', 'Доставка' ),
            146 => array( '/help/#pay', 'Оплата' ),
            6 => array( '/contacts/', 'Контакты' ),
        );

        return $items;
    }

    private function get_classes($action, $i)
    {
        $classes = array();

        if ((isset( $this->registry[ 'page' ][ 'id' ] ) && $action == $this->registry[ 'page' ][ 'id' ]) || (isset( $this->registry[ 'mainpage' ] ) && $i == 1) || (isset( $this->registry[ 'register_page' ] ) && $action == 999))
            $classes[] = 'active';

        return implode( ' ', $classes );
    }

    public function do_menu()
    {
        $items = $this->get_data();

        $html = array();
        $i = 1;
        foreach ($items as $action => $arr) {
            $a = array(
                'classes' => $this->get_classes( $action, $i ),
                'link' => ($arr[ 0 ] == '') ? '/' : $arr[ 0 ],
                'name' => $arr[ 1 ],
            );

            $html[] = $this->do_rq( 'item', $a, true );

            $i++;
        }

        $this->registry[ 'CL_template_vars' ]->set( 'main_menu',
            $this->do_rq( 'container',
                implode( '', $html )
            )
        );
    }
}

