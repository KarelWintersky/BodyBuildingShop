<?php

class Front_Order_Done_Blocks extends Common_Rq
{
    
    /*
     * типовые блоки для последнего шага заказа и email-уведомлений
     * */
    
    private $registry;
    private $blocks;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
        
        $this->blocks = array(
            'address',
            'requisites',
            'webmoney',
            'post',
            'post2',
            'courier',
            'courier2',
            'self',
            'self2',
            'prepay',
        );
    }
    
    private function extra_fields($order)
    {
        /*
         * Для  оплаты  через  вебмоней  и яндексденьги в письмах и на пятом шаге
надо  заменить  е-мэйл hercules@superset.ru на of@bodybuilding-shop.ru
         * */
        $order[ 'prepay_email' ] = ($order[ 'payment_method_id' ] == 3)
            ? 'of@bodybuilding-shop.ru'
            : 'hercules@superset.ru';
        
        return $order;
    }
    
    public function get_blocks($order)
    {
        $output = array();
        
        $order = $this->extra_fields( $order );
        
        foreach ($this->blocks as $alias)
            $output[ $alias ] = $this->do_rq( $alias, $order );
        
        return $output;
    }
}

