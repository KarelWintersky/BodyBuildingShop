<?php

class Adm_Orders_Helper
{

    public static function get_statuses($id = false)
    {
        $statuses = array(
            1 => 'сформирован',
            2 => 'отправлен',
            3 => 'оплачен',
            4 => 'отменен',
            5 => 'деньги поступили',
        );

        if ($id) return (isset( $statuses[ $id ] )) ? $statuses[ $id ] : false;

        return $statuses;
    }

    public static function statuses_options($cur, $all = false)
    {
        $statuses = self::get_statuses();

        $data = array();
        foreach ($statuses as $id => $name)
            $data[] = array(
                'val' => $id,
                'name' => $name,
                'selected' => ($cur == $id),
            );

        return Common_Template_Select::opts(
            $data,
            ($all) ? 'все' : false
        );
    }
}

