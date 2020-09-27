<?php

class Front_Contacts_Form extends Common_Rq
{
    
    private $registry;
    
    private $Front_Contacts_Form_Send;
    
    public function __construct($registry)
    {
        $this->registry = $registry;
        
        $this->Front_Contacts_Form_Send = new Front_Contacts_Form_Send( $this->registry );
    }
    
    private function print_topics()
    {
        $data = array();
        
        $qLnk = mysql_query( "
				SELECT
					id,
					name
				FROM
					feedback_mail
				ORDER BY
					sort ASC;
				" );
        while ($m = mysql_fetch_assoc( $qLnk ))
            $data[] = array(
                'val' => $m[ 'id' ],
                'name' => $m[ 'name' ],
            );
        
        return Common_Template_Select::opts( $data, 'Выберите тему' );
    }
    
    public function page_extra()
    {
        $this->Front_Contacts_Form_Send->do_send();
        
        $a = array(
            'topics' => $this->print_topics(),
            'success_message' => (count( $_POST )),
            'action' => $_SERVER[ 'REQUEST_URI' ].'#form',
        );
        
        return $this->do_rq( 'form', $a );
    }
}

