<?php


class Registry implements ArrayAccess
{
    /**
     * @var array
     */
    private $vars = array();
    
    public function set($key, $var)
    {
        $this->vars[ $key ] = $var;
        return true;
    }
    
    public function get($key)
    {
        if (isset( $this->vars[ $key ] ) == false) {
            return null;
        }
        return $this->vars[ $key ];
    }
    
    /**
     * @param $key
     */
    public function remove($key)
    {
        unset( $this->vars[ $key ] );
    }
    
    
    public function offsetExists($offset)
    {
        return isset( $this->vars[ $offset ] );
    }
    
    public function offsetGet($offset)
    {
        return $this->get( $offset );
    }
    
    public function offsetSet($offset, $value)
    {
        $this->set( $offset, $value );
    }
    
    public function offsetUnset($offset)
    {
        unset( $this->vars[ $offset ] );
    }
}


