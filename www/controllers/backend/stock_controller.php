<?php
/**
 * Created by PhpStorm.
 * User: asus1
 * Date: 09.10.2015
 * Time: 1:01
 */
class stock_controller extends controller
{
    public function index()
    {

    }

    public function unites()
    {
        $this->render('products', $this->model('products')->getAll('product_name'));
        $this->render('tastes', $this->model('tastes')->getAll('taste_name'));
        $this->view('stock' . DS . 'units');
    }

    public function unites_ajax()
    {
        switch ($_REQUEST['action']) {
            case "save_taste":
                $taste = $_POST['taste'];
                $taste['id'] = $this->model('tastes')->insert($taste);
                $taste['status'] = 1;
                echo json_encode($taste);
                exit;
                break;

            case "save_unit":
                $rows = [];
                $date = date('Y-m-d H:i:s');
                for($i = 0; $i < $_POST['quantity']; $i ++) {
                    $rows[$i] = $_POST['unit'];
                    $rows[$i]['create_date'] = $date;
                    $rows[$i]['creator'] = registry::get('user')['id'];
                }
                $this->model('stock_units')->insertRows($rows);
                echo json_encode(array('status' => 1));
                exit;
                break;

            case "get_unites":
                $params = [];
                $params['table'] = 'stock_units u';
                $params['join']['tastes'] = array(
                    'as' => 't',
                    'on' => 't.id = u.taste_id',
                    'left' => true
                );
                $params['join']['products'] = array(
                    'as' => 'p',
                    'on' => 'p.id = u.product_id',
                );
                $params['select'] = array(
                    'p.product_name',
                    'IF(t.taste_name, t.taste_name, " - ")',
                    'IF(t.expiration_date, t.expiration_date, " - ")'
                );
                exit;
                break;
        }
    }
}