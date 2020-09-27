<?php

class f_Ajax
{
    
    private $registry;
    
    public function pgc()
    {
    }
    
    public function __construct($registry)
    {
        $this->registry = $registry;
        $this->registry->set( 'f_ajax', $this );
    }
    
    public function path_check()
    {
        
        $path_arr = $this->registry[ 'route_path' ];
        $this->registry[ 'template' ]->set( 'no_tpl', true );
        
        if (count( $path_arr ) == 0 && isset( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) && !empty( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) && strtolower( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) == 'xmlhttprequest') {
            if (isset( $_POST[ 'method' ] ) && method_exists( $this, $_POST[ 'method' ] )) {
                $method = $_POST[ 'method' ];
            } elseif (isset( $_GET[ 'method' ] ) && method_exists( $this, $_GET[ 'method' ] )) {
                $method = $_GET[ 'method' ];
            } else {
                return false;
            }
            
            $this->registry[ 'f_404' ] = false;
            
            $this->$method();
            
            return true;
        }
        
    }
    
    private function barcode_add()
    {
        $catalog = new Catalog( $this->registry, false );
        $catalog->barcode_add( $_POST[ 'goods_id' ] );
    }
    
    private function goods_barcode_check()
    {
        $Adm_Catalog_Goods_Barcodes_Check = new Adm_Catalog_Goods_Barcodes_Check( $this->registry, false );
        $Adm_Catalog_Goods_Barcodes_Check->barcode_check();
    }
    
    private function cart_construct()
    {
        $Front_Cart = new Front_Cart( $this->registry );
        echo $Front_Cart->head_cart();
    }
    
    private function goods_ostatok_check()
    {
        $catalog = new f_Catalog( $this->registry );
        $catalog->goods_ostatok_check( $_POST[ 'barcode' ], $_POST[ 'cart' ] );
    }
    
    private function stat_goods_table()
    {
        $stat = new Statistics( $this->registry, false );
        $stat->stat_goods_table_item( $_POST[ 'goods_id' ] );
    }
    
    private function cart_restruct()
    {
        Front_Order_Steps::write_submit( 1, true );
        
        $Front_Cart_Manage = new Front_Cart_Manage( $this->registry );
        $Front_Cart_Manage->restruct( $_POST[ 'goods' ] );
    }
    
    private function apply_coupon()
    {
        Front_Order_Steps::write_submit( 1, true );
        
        $Front_Order_Cart_Coupon = new Front_Order_Cart_Coupon( $this->registry );
        $Front_Order_Cart_Coupon->apply_coupon( $_POST[ 'coupon' ] );
    }
    
    private function delivery_courier()
    {
        Front_Order_Steps::write_submit( 2, true );
        
        $Front_Order_Delivery_Courier = new Front_Order_Delivery_Courier( $this->registry );
        $Front_Order_Delivery_Courier->recalculate();
    }
}
