<?php 
require __DIR__ . '/../AbstractController.php';

class TestController extends AbstractController
{
    public function test()
    {
        exit('hello');
    }
}