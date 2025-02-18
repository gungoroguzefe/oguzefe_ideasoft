<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Products extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Product_model');
        $this->output->set_content_type('application/json');
    }

    public function index() {
        $products = $this->Product_model->get_all();
        $this->output->set_output(json_encode([
            'status' => true,
            'data' => $products
        ]));
    }

    public function detail($id) {
        $product = $this->Product_model->get_by_id($id);
        
        if ($product) {
            $this->output->set_output(json_encode([
                'status' => true,
                'data' => $product
            ]));
        } else {
            $this->output->set_status_header(404);
            $this->output->set_output(json_encode([
                'status' => false,
                'message' => 'Product not found'
            ]));
        }
    }

    public function category($category_id) {
        $products = $this->Product_model->get_by_category($category_id);
        $this->output->set_output(json_encode([
            'status' => true,
            'data' => $products
        ]));
    }

}