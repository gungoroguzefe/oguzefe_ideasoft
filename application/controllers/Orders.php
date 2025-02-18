<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Orders extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('Order_model');
        $this->load->model('Product_model');
        $this->load->library('form_validation');
        $this->output->set_content_type('application/json');
    }

    public function index() {
        $orders = $this->Order_model->get_all();
        $this->output->set_output(json_encode($orders));
    }

    public function create() {
        $this->form_validation->set_rules('customerId', 'Müşteri Kimliği', 'required|trim');

        if ($this->form_validation->run() == FALSE) {
            $this->output->set_status_header(400);
            $this->output->set_output(json_encode([
                'error' => validation_errors()
            ]));
            return;
        }

        $data = $this->input->post();
        
        if (!isset($data['items']) || !is_array($data['items'])) {
            $this->output->set_status_header(400);
            $this->output->set_output(json_encode([
                'error' => 'Geçersiz ürün formatı.'
            ]));
            return;
        }

        foreach ($data['items'] as $item) {
            if (!isset($item['productId']) || !isset($item['quantity'])) {
                $this->output->set_status_header(400);
                $this->output->set_output(json_encode([
                    'error' => 'Eksik bilgi, lütfen ürünün id değerini ve adedini belirtiniz'
                ]));
                return;
            }

            $product = $this->Product_model->get_by_id($item['productId']);
            $product['order_quantity'] =  $item['quantity'];
            if (!$product) {
                $this->output->set_status_header(400);
                $this->output->set_output(json_encode([
                    'error' => $item['productId'] . ' ID numaralı ürün bulunamadı.'
                ]));
                return;
            }
            
            if ($product['stock'] < $item['quantity']) {
                $this->output->set_status_header(400);
                $this->output->set_output(json_encode([
                    'error' => $product['name'] . ' ürünü için yeterli stok bulunmamaktadır'
                ]));
                return;
            }

            $data['products'][] = $product;
        }


        $order_id = $this->Order_model->create($data);
        $this->output->set_status_header(201);
        $this->output->set_output(json_encode(['id' => $order_id]));
    }
}
