<?php
/**
 * Created by PhpStorm.
 * User: asus1
 * Date: 01.09.2015
 * Time: 19:51
 */
class finance_controller extends controller
{
    public function index()
    {
        $this->view('finance' . DS . 'index');
    }

    public function streams()
    {
        $this->render('stream_types', $this->model('finance_stream_types')->getAll('type_name'));
        $this->view('finance' . DS . 'streams');
    }

    public function streams_ajax()
    {
        switch ($_REQUEST['action']) {
            case "finance_streams_table":
                $params = [];
                $params['table'] = 'finance_streams s';
                $params['select'] = array(
                    'IF(s.stream_type = 1, "Приход","Расход")',
                    't.type_name',
                    'IF(s.custom_comment, s.custom_comment, t.type_comment)',
                    'CONCAT(u.user_surname, " ", SUBSTRING(u.user_name, 1,1), ".")',
                    's.stream_sum',
                    's.create_date'
                );
                $params['join']['finance_stream_types'] = array(
                    'as' => 't',
                    'on' => 's.type_id = t.id'
                );
                $params['join']['backend_users'] = array(
                    'as' => 'u',
                    'on' => 's.creator = u.id'
                );
                echo json_encode($this->getDataTable($params));
                exit;
                break;

            case "save_stream":
                $stream = $_POST['stream'];
                if($this->callEvent('finance_stream', $stream)) {
                    echo json_encode(array('status' => 1));
                } else {
                    echo json_encode(array('status' => 2));
                }
                exit;
                break;

            case "save_stream_type":
                if($selected_type = $this->model('finance_stream_types')->insert($_POST['type'])) {
                    $this->render('types', $this->model('finance_stream_types')->getByField('stream_type', $_POST['type']['stream_type'], true));
                    $this->render('selected_type', $selected_type);
                    $options = $this->fetch('finance' . DS . 'ajax' . DS . 'type_options');
                    echo json_encode(array('status' => 1, 'options' => $options));
                } else {
                    echo json_encode(array('status' => 2));
                }
                exit;
                break;
        }
    }
}