<?php
/**
 * Created by PhpStorm.
 * User: asus1
 * Date: 24.12.2015
 * Time: 1:14
 */
class products_controller extends controller
{
    public function index()
    {
        $this->view('common' . DS . '404');
    }

    public function index_na()
    {
        $this->index();
    }

    public function catcher($product_key)
    {
        if($product_id = $this->model('products')->getByField('product_key', $product_key)['id']) {
            $this->render('categories', $this->model('categories')->getCategories());
            $this->render('product', $this->model('products')->getProduct($product_id));
            $this->render('bestsellers', $this->model('products')->getCategoryBestsellers(null, 3));
            $this->render('related_products', $this->model('products')->getRelatedProducts($product_id, 3));
            $reviews = [];
            foreach ($this->model('reviews')->getByFields(array('entity' => 1, 'entity_id' => $product_id), true, 'create_date DESC', '10') as $review) {
                $review['create_date'] = tools_class::$months_rus[date('m', strtotime($review['create_date']))] . ' ' . date('d, Y', strtotime($review['create_date']));
                $reviews[] = $review;
            }
            $this->render('reviews', $reviews);
            $this->view('products' . DS . 'index');
        } else {
            $this->view('common' . DS . '404');
        }

    }

    public function catcher_ajax()
    {
        switch ($_REQUEST['action']) {
            case "save_review":
                $review = $_POST['review'];
                $review['create_date'] = date('Y-m-d H:i:s');
                $review['entity'] = 1;
                $row['user_ip'] = $_SERVER['REMOTE_ADDR'];
                if($this->model('reviews')->insert($review)) {
                    $review['create_date'] = tools_class::$months_rus[date('m', strtotime($review['create_date']))] . ' ' . date('d, Y', strtotime($review['create_date']));
                    $this->render('review', $review);
                    $template = $this->fetch('products' . DS . 'ajax' . DS . 'review');
                    echo json_encode(array('status' => 1, 'review' => $template));
                }
                exit;
                break;
        }
    }
}