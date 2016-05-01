<?php
/**
 * Created by PhpStorm.
 * User: asus1
 * Date: 17.12.2015
 * Time: 23:03
 */
class catalog_controller extends controller
{
    public function index()
    {
        $this->render('products', $this->model('products')->getCategoryProducts());
        $this->render('categories', $this->model('categories')->getCategories());
        $this->view('catalog' . DS . 'index');
    }

    public function index_na()
    {
        $this->index();
    }

    public function catcher($category)
    {
        $this->render('categories', $this->model('categories')->getCategories());
        $active_category = $this->model('categories')->getByField('category_key', $category);
        if($active_category) {
            $limit = 10;
            $count = $this->model('products')->countCategoryProducts($active_category['id']);
            $count_pages = ceil($count/$limit);

            if($_GET['page'] > 1 && is_numeric($_GET['page'])) {
                $lim = $limit . ', ' . ($count_pages * $limit > $count ? $count_pages * $limit : $count);
            } else {
                $lim = $limit;
            }
            $pages = [];
            for($i = 1; $i <= $count_pages; $i ++) {
                $params = $_GET;
                $params['page'] = $i;
                $url = SITE_DIR . registry::get('route') . '?' . http_build_query($params);
                $pages[$i] = $url;
            }
            $this->render('pages', $pages);
            if(isset($_GET['order'])) {
                switch($_GET['order']) {
                    case "name":
                        $order = 'p.product_name' . (isset($_GET['desc']) ? ' DESC' : '');
                        break;
                    case "price":
                        $order = 'p.price' . (isset($_GET['desc']) ? ' DESC' : '');
                        break;
                    case "bestseller":
                        $order = 'p.bestseller' . (!isset($_GET['desc']) ? ' DESC' : '');
                        break;
                }
            }
            $this->render('products', $this->model('products')->getCategoryProducts($active_category['id'], $order, $lim));
            $this->render('bestsellers', $this->model('products')->getCategoryBestsellers($active_category['id'], 3));
            $this->render('active_category', $active_category);
            $this->view('catalog' . DS . 'index');
        } else {
            $this->view('common' . DS . '404');
        }

    }
}