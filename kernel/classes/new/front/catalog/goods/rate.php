<?php

class Front_Catalog_Goods_Rate
{
    
    /*
     * пересчет рейтинга товаров
     * */
    
    private $registry;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
    }
    
    public function rate_restruct()
    {
        foreach ($goods_ids as $id => $incr) {
            mysql_query( "
					UPDATE
					goods
					SET
					popularity_index = popularity_index + ".$incr."
					WHERE
					id = ".$id.";
					" );
        }
    }
    
}
