<?php
/**
 * Created by PhpStorm.
 * User: asus1
 * Date: 09.10.2015
 * Time: 0:48
 */
class profile_controller extends controller
{
    public function index()
    {
        $this->view('profile' . DS . 'index');
    }
}