<?php 

class Session 
{
    public function getAttribute(string $attribute)
    {
        return isset($_SESSION[$attribute]) ? $_SESSION[$attribute] : null;
    }

    public function setAttribute(string $attribute, mixed $value)
    {
        $_SESSION[$attribute] = $value;
    }

    public function hasAttribute(string $attribute): bool
    {
        return isset($_SESSION[$attribute]) ?? false;
    }
}