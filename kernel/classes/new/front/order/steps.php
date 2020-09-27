<?php

class Front_Order_Steps
{
    
    /*
     * осуществляет проверку соблюдения очередности шагов
     * шаг 3 можно открыть только после того, как были записаны данные с субмита шага 2, например
     * */
    
    public static function write_submit($step_id, $write_this_step = false)
    {
        
        /*
         * если мы субмитили текущий шаг, значит, мы можем открывать следующий
         * поэтому, записываем +1
         *
         * $write_this_step - записываем именно тот шаг, который передаем, а не инкремент
         * */
        
        $allowed_step = ($write_this_step)
            ? $step_id
            : $step_id + 1;
        
        $_SESSION[ 'allowed_step' ] = $allowed_step;
    }
    
    public static function check_step($this_id)
    {
        $allowed_step = (isset( $_SESSION[ 'allowed_step' ] )) ? $_SESSION[ 'allowed_step' ] : 1;
        
        if ($this_id > $allowed_step) {
            $step = Front_Order_Data_Steps::get_steps( $allowed_step );
            $url = sprintf( '/%s/', $step[ 1 ] );
            
            header( sprintf( 'Location: %s', $url ) );
            exit();
        }
    }
    
}

