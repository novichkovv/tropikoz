<?php
/**
 * Created by PhpStorm.
 * User: enovichkov
 * Date: 28.08.2015
 * Time: 17:20
 */
class index_controller extends controller
{
    public function index()
    {
        $this->view('index' . DS . 'index');
    }

    public function index_na()
    {
        $this->index();
    }
}