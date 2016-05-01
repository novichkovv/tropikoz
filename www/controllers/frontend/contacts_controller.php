<?php
/**
 * Created by PhpStorm.
 * User: asus1
 * Date: 18.12.2015
 * Time: 0:49
 */
class contacts_controller extends controller
{
    public function index()
    {
        $this->view('contacts' . DS . 'index');
    }

    public function index_na()
    {
        $this->index();
    }
}