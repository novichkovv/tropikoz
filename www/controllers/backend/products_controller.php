<?php
/**
 * Created by PhpStorm.
 * User: asus1
 * Date: 29.08.2015
 * Time: 2:07
 */
class products_controller extends controller
{
    public function index()
    {
        $this->breadcrumbs();
        $this->deleteModal();
        $this->view('products' . DS . 'list');
    }

    public function index_ajax()
    {
        switch ($_REQUEST['action']) {
            case "get_list":
                $params = [];
                $params['table'] = 'products p';
                $params['select'] = array(
                    'CONCAT("
                    <a class=\"fancybox\" rel=\"group\" href=\"' . SITE_DIR . 'uploads/images/product_images/", i.image_name, "\">
                    <img src=\"' . SITE_DIR . 'uploads/images/product_images/", i.image_name, "\" class=\"product_table_image\"></a>")',
                    'p.id',
                    'p.product_name',
                    'p.product_key',
                    'IF(p.active = 1, "Да", "Нет")',
                    'p.create_date',
                    'CONCAT("<a href=\"' . SITE_DIR . 'products/add/?id=", p.id, "\" class=\"btn btn-icon btn-default\">
                        <i class=\"fa fa-pencil\"></i>
                    </a>
                    <a href=\"#delete_modal\" data-id=\"", p.id, "\" class=\"delete_product btn btn-icon btn-default\" data-toggle=\"modal\">
                        <i class=\"fa fa-remove text-danger\"></i>
                    </a>")'
                );
                $params['join']['product_images'] = array(
                    'as' => 'i',
                    'left' => true,
                    'on' => 'i.product_id = p.id AND i.main = 1'
                );
                echo json_encode($this->getDataTable($params));
                exit;
                break;

            case "delete_product":
                $product = $this->model('products')->getById($_POST['id']);
                $this->model('products')->deleteById($_POST['id']);
                $this->model('products_categories_relations')->delete('product_id', $_POST['id']);
                $images = $this->model('product_images')->getByField('product_id', $_POST['id'], true);
                foreach ($images as $v) {
                    $filename = ROOT_DIR . 'uploads' . DS . 'images' . DS . 'product_images' . DS . $v['image_name'];
                    if(file_exists($filename)) {
                        unlink($filename);
                    }
                }
                $this->model('product_images')->delete('product_id', $_POST['id']);
                $this->model('frontend_routes')->delete('url_key', $product['product_key']);
                echo json_encode(array('status' => 1));
                exit;
                break;
        }
    }
    
    public function add()
    {
        $this->render('image_form_id', 1);
        $categories = $this->model('categories')->getProductCategories($_GET['id']);
        if($_GET['id']) {
            $product = $this->model('products')->getProduct($_GET['id']);
            $this->log($product);

            $image_template = '';
            $copy_image_template = '';
            $i = 1;
            if ($product['images']) {
                foreach ($product['images'] as $k => $v) {
                    $this->render('type', $k);
                    if ($k != 'usual' && $k != 'inactive') {
                        $this->render('image_form_id', $i);
                        $this->render('image_name', $v);
                        $image_template .= $this->fetch('products' . DS . 'ajax' . DS . 'image_form');
                        $i ++;
                    } else {
                        foreach ($v as $image) {
                            $this->render('image_form_id', $i);
                            $this->render('image_name', $image);
                            $image_template .= $this->fetch('products' . DS . 'ajax' . DS . 'image_form');
                            $i ++;
                        }
                    }
                }
            }

            $this->render('image_name', null);
            $this->render('image_template', $image_template);
            $this->render('copy_image_template', $copy_image_template);
            $this->render('image_form_id', $i);
            $this->render('product', $product);
        }
        $this->render('categories', $categories);
        $this->render('products', $this->model('products')->getAll('product_name'));
        $this->breadcrumbs();
        $this->addScript('libs/bootstrap-summernote/summernote');
        $this->addStyle('../js/libs/bootstrap-summernote/summernote');
        $this->addStyle('../js/libs/jstree/dist/themes/default/style.min');
        $this->addScript('libs/ajax_upload');
        $this->addScript('libs/jstree/dist/jstree.min');
        $this->view('products' . DS . 'add');
    }

    public function add_ajax()
    {
        switch($_REQUEST['action']) {
            case "save_product_img":
                foreach ($_FILES as $file) {
                    $ext = array_pop(explode('.', $file['name']));
                    $new_image_name = $_POST['image'][$_POST['image_form_id']]['new'] ? $_POST['image'][$_POST['image_form_id']]['new'] : $_POST['rand'] . $_POST['image_form_id'] . '.' . $ext;
                    move_uploaded_file($file['tmp_name'], ROOT_DIR . 'tmp' . DS . 'uploaded' . DS . $new_image_name);
                    $this->render('image_form_id', $_POST['image_form_id'] + 1);
                    $image_form = $this->fetch('products' . DS . 'ajax' . DS . 'image_form');
                    $this->render('image_name', $_POST['image_name']);
                    $this->render('new_image_name', $new_image_name);
                    $this->render('image_form_id', $_POST['image_form_id']);
                    $template = $this->fetch('products' . DS . 'ajax' . DS . 'image_form');
                    echo json_encode(array('status' => 1, 'image_form' => $image_form, 'template' => $template, 'image_form_id' => $_POST['image_form_id']), JSON_HEX_TAG);
                }
                exit;
                break;

            case "save_copy_product_img":
                foreach ($_FILES as $file) {
                    $ext = array_pop(explode('.', $file['name']));
                    $new_image_name = $_POST['copy_image'][$_POST['copy_image_form_id']]['new'] ? $_POST['copy_image'][$_POST['copy_image_form_id']]['new'] : $_POST['rand'] . $_POST['copy_image_form_id'] . '.' . $ext;
                    move_uploaded_file($file['tmp_name'], ROOT_DIR . 'tmp' . DS . 'uploaded' . DS . $new_image_name);
                    $this->render('image_form_id', $_POST['image_form_id'] + 1);
                    $image_form = $this->fetch('products' . DS . 'ajax' . DS . 'copy_image_form');
                    $this->render('image_name', $_POST['copy_image_name']);
                    $this->render('new_image_name', $new_image_name);
                    $this->render('image_form_id', $_POST['copy_image_form_id']);
                    $template = $this->fetch('products' . DS . 'ajax' . DS . 'copy_image_form');
                    echo json_encode(array('status' => 1, 'image_form' => $image_form, 'template' => $template, 'image_form_id' => $_POST['copy_image_form_id']), JSON_HEX_TAG);
                }
                exit;
                break;

            case "save_product":
                $product = $_POST['product'];
                if (!$product['id']) {
                    $product['create_date'] = date('Y-m-d H:i:s');
                }
                if ($product['id'] = $this->model('products')->insert($product)) {
                    $images = [];
                    $this->model('product_images')->delete('product_id', $product['id']);
                    foreach ($_POST['image'] as $k => $v) {
                        if (is_numeric($k)) {
                            if ($v['new']) {
                                $new_image_name = ROOT_DIR . 'tmp' . DS . 'uploaded' . DS . $v['new'];
                                if (file_exists($new_image_name)) {
                                    $ext = array_pop(explode('.', $v['new']));
                                    if (!$v['old']) {
                                        $name = 'p' . $product['id'] . '_' . $k . '.' . $ext;
                                    } else {
                                        $name = $v['old'];
                                    }
                                    if ($ext) {
                                        rename($new_image_name, ROOT_DIR . 'uploads' . DS . 'images' . DS . 'product_images' . DS . $name);
                                    }
                                }
                            } elseif ($v['old']) {
                                $name = $v['old'];
                            }
                            $images[$k]['image_name'] = $name;
                            if ($_POST['usual_image'][$k]) {
                                $images[$k]['usual'] = 1;
                            } else {
                                $images[$k]['usual'] = 0;
                            }
                            if ($_POST['main_image'] == $k) {
                                $images[$k]['main'] = 1;
                            } else {
                                $images[$k]['main'] = 0;
                            }
                            if ($_POST['small_image'] == $k) {
                                $images[$k]['small'] = 1;
                            } else {
                                $images[$k]['small'] = 0;
                            }
                            $images[$k]['product_id'] = $product['id'];
                        }
                    }

                    $this->model('product_images')->insertRows($images);
                    $this->model('products_categories_relations')->delete('product_id', $product['id']);
                    $row = [];
                    $row['product_id'] = $product['id'];
                    if ($_POST['category']) {
                        foreach ($_POST['category'] as $category_id => $category) {
                            $row['category_id'] = $category_id;
                            $this->model('products_categories_relations')->insert($row);
                        }
                    }
                    $this->model('related_products')->delete('product_id', $product['id']);
                    if($_POST['related']) {
                        $row = [];
                        $row['product_id'] = $product['id'];
                        foreach ($_POST['related'] as $related_id => $related) {
                            $row['related_product_id'] = $related_id;
                            $this->model('related_products')->insert($row);
                        }
                    }
                    echo json_encode(array('status' => 1, 'product_id' => $product['id']));
                } else {
                    echo json_encode(array('status' => 2));
                }
                exit;
                break;

            case "save_copy":
                $parent = $this->model('products')->getById($_GET['id']);
                $product = $_POST['copy'];
                foreach ($parent as $key => $val) {
                    if(!in_array($key, array('id', 'main_product'))) {
                        continue;
                    }
                    if(!$product[$key]) {
                        $product[$key] = $val;
                    }
                }
                if (!$product['id']) {
                    $product['create_date'] = date('Y-m-d H:i:s');
                }
                if ($product['id'] = $this->model('products')->insert($product)) {
                    $row = [];
                    $row['url_key'] = $product['product_key'];
                    $row['controller'] = 'product_controller';
                    $row['method'] = 'view_product';
                    $row['entity_table'] = 'products';
                    $row['entity_id'] = $product['id'];
                    $this->model('frontend_routes')->insert($row);

                    $images = [];
                    $this->model('product_images')->delete('product_id', $product['id']);
                    foreach ($_POST['copy_image'] as $k => $v) {
                        if (is_numeric($k)) {
                            if ($v['new']) {
                                $new_image_name = ROOT_DIR . 'tmp' . DS . 'uploaded' . DS . $v['new'];
                                if (file_exists($new_image_name)) {
                                    $this->writeLog('test', $new_image_name);
                                    $ext = array_pop(explode('.', $v['new']));
                                    if (!$v['old']) {
                                        $name = 'p' . $product['id'] . '_' . $k . '.' . $ext;
                                    } else {
                                        $name = $v['old'];
                                    }
                                    if ($ext) {
                                        rename($new_image_name, ROOT_DIR . 'uploads' . DS . 'images' . DS . 'product_images' . DS . $name);
                                    }
                                }
                            } elseif ($v['old']) {
                                $name = $v['old'];
                            }
                            $images[$k]['image_name'] = $name;
                            if ($_POST['copy_usual_image'][$k]) {
                                $images[$k]['usual'] = 1;
                            } else {
                                $images[$k]['usual'] = 0;
                            }
                            if ($_POST['copy_main_image'] == $k) {
                                $images[$k]['main'] = 1;
                            } else {
                                $images[$k]['main'] = 0;
                            }
                            if ($_POST['copy_small_image'] == $k) {
                                $images[$k]['small'] = 1;
                            } else {
                                $images[$k]['small'] = 0;
                            }
                            $images[$k]['product_id'] = $product['id'];
                        }
                    }
                    $this->model('product_images')->insertRows($images);
                    $this->model('products_categories_relations')->delete('product_id', $product['id']);
                    $row = [];
                    $row['product_id'] = $product['id'];
                    $categories = $this->model('products_categories_relations')->getByField('product_id', $_GET['id'], true);
                    if ($categories) {
                        foreach ($categories as $category) {
                            $row['category_id'] = $category['category_id'];
                            $this->model('products_categories_relations')->insert($row);
                        }
                    }

                    if(!$product['copy_id']) {
                        $row = [];
                        $row['copy_id'] = $this->model('products_copies')->insert(array('create_date' => date('Y-m-d')));
                        $row['id'] = $product['id'];
                        $this->model('products')->insert($row);
                        $row['id'] = $_GET['id'];
                        $row['main_copy'] = 1;
                        $this->model('products')->insert($row);
                    } else {
                        $row['copy_id'] = $product['copy_id'];
                    }
                    $this->render('product_copies', $this->model('products')->getProductCopies($row['copy_id']));
                    $template = $this->fetch('products' . DS . 'ajax' . DS . 'product_copies_table');
                    echo json_encode(array('status' => 1, 'product_id' => $product['id'], 'template' => $template));
                } else {
                    echo json_encode(array('status' => 2));
                }
                exit;
                break;
        }
    }

    public function categories()
    {
        $this->addScript('libs/jquery.nestable');
        $this->addScript('libs/ajax_upload');
        $this->addStyle('libs/jquery.nestable');
        $categories = $this->model('categories')->getCategories();
        $this->render('categories', $categories);
        $this->render('inactive_categories', $this->model('categories')->getByField('active', 0, true));
        $this->breadcrumbs();
        $this->deleteModal();
        $this->view('products' . DS . 'categories');
    }

    public function categories_ajax()
    {
        switch ($_REQUEST['action']) {
            case "save_category":
                $category = $_POST['category'];
                if(!$category['position'] && !$category['id'] && $category['active']) {
                    $category['position'] = $this->model('categories')->getByField('parent', 0, false, 'position DESC', 1)['position'] + 1;
                }
                if($category['id'] = $this->model('categories')->insert($category)) {
                    if($_POST['category']['id']) {
                        if($route = $this->model('frontend_routes')->getByFields(array('entity_id' => $category['id'], 'entity_table' => 'categories'))) {
                            $route['url_key'] = $category['category_key'];
                            $this->model('frontend_routes')->insert($route);
                        }
                    } else {
                        $route = [];
                        $route['controller'] = 'category_controller';
                        $route['method'] = 'view_category';
                        $route['url_key'] = $category['category_key'];
                        $route['entity_id'] = $category['id'];
                        $route['entity_table'] = 'categories';
                        $this->model('frontend_routes')->insert($route);
                    }
                    if($category['image']) {
                        $ext = array_pop(explode('.', $category['image']));
                        $image_name = $category['id'] . '.' . $ext;
                        rename(ROOT_DIR . 'tmp' . DS . 'uploaded' . DS . $category['image'], ROOT_DIR . 'uploads' . DS . 'images' . DS . 'category_images' . DS . $image_name);
                        $this->model('categories')->insert(array('id' => $category['id'], 'image' => $image_name));
                    }
                    unset($category['image']);
                    $this->render('category', $category);
                    if ($category['active']) {
                        $template = $this->fetch('products' . DS . 'ajax' . DS . 'active_category');
                    } else {
                        $template = $this->fetch('products' . DS . 'ajax' . DS . 'inactive_category');
                    }
                    echo json_encode(array('status' => 1, 'active' => $category['active'], 'category_id' => $category['id'], 'category_name' => $category['category_name'], 'template' => $template));
                }
                exit;
                break;

            case "serialize":
                $order = $_POST['order'];
                foreach ($order as $position => $v) {
                    $row = [];
                    $row['id'] = $v['id'];
                    $row['position'] = $position;
                    $row['parent'] = 0;
                    $this->model('categories')->insert($row);
                    unset($order[$position]);
                    if($v['children']) {
                        $this->nestable_recursion($v['children'], $v['id']);
                    }
                }
                exit;
                break;

            case "get_category_form":
                $this->render('category', $this->model('categories')->getById($_POST['category_id']));
                $template = $this->fetch('products' . DS . 'ajax' . DS . 'category_form');
                echo json_encode(array('status' => 1, 'template' => $template));
                exit;
                break;

            case "get_inactive_category":
                $template = '';
                $ids = [];
                foreach ($_POST['categories'] as $id => $name) {
                    $category = [];
                    $category['id'] = $id;
                    $ids[] = $id;
                    $category['category_name'] = $name;
                    $this->render('category', $category);
                    $template .= $this->fetch('products' . DS . 'ajax' . DS . 'inactive_category');
                }
                $this->model('categories')->makeInactive($ids);
                echo json_encode(array('status' => 1, 'template' => $template));
                exit;
                break;

            case "activate_category":
                $row = [];
                $row['id'] = $_POST['id'];
                $row['active'] = 1;
                if($id = $this->model('categories')->insert($row)) {
                    $this->render('category', $this->model('categories')->getById($id));
                    $template = $this->fetch('products' . DS . 'ajax' . DS . 'active_category');
                    echo json_encode(array('status' => 1, 'template' => $template));
                } else {
                    echo json_encode(array('status' => 2));
                }
                exit;
                break;

            case "delete_category":
                if($_POST['id'] && $_POST['table']) {
                    if($this->model($_POST['table'])->deleteById($_POST['id'])) {
                        $this->model('products_categories_relations')->delete('category_id', $_POST['id']);
                        $this->model('frontend_routes')->deleteByFields(array('entity_id' => $_POST['id'], 'entity_table' => 'categories'));
                        echo json_encode(array('status' => 1));
                    } else {
                        echo json_encode(array('status' => 2));
                    }
                } else {
                    echo json_encode(array('status' => 3));
                }
                exit;
                break;

            case "save_category_img":
                foreach ($_FILES as $file) {
                    $ext = array_pop(explode('.', $file['name']['image']));
                    move_uploaded_file($file['tmp_name']['image'], ROOT_DIR . 'tmp' . DS . 'uploaded' . DS . $_POST['rand'] . '.' . $ext);
                    echo json_encode(array('status' => 1, 'img' => $_POST['rand'] . '.' . $ext));
                }
                exit;
                break;

            case "check_unique_category_key":
                if(!$this->model('frontend_routes')->getByField('url_key', $_POST['category_key'])) {
                    echo json_encode(array('status' => 1));
                } else {
                    echo json_encode(array('status' => 2));
                }
                exit;
                break;
        }
    }

    public function nestable_recursion($order, $parent)
    {
        foreach ($order as $position => $v) {
            $row = [];
            $row['id'] = $v['id'];
            $row['position'] = $position;
            $row['parent'] = $parent;
            $this->model('categories')->insert($row);
            unset($order[$position]);
            if($v['children']) {
                $this->nestable_recursion($v['children'], $v['id']);
            }
        }
    }

    public function attributes()
    {
        $this->view('products' . DS . 'attributes');
    }
}