<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product_model extends CI_Model {
    
    private $table = 'products';

    public function __construct() {
        parent::__construct();
    }

    public function get_all() {
        $query = $this->db->get($this->table);
        return $query->result_array();
    }

    public function get_by_id($id) {
        $query = $this->db->get_where($this->table, ['id' => $id]);
        return $query->row_array();
    }

    public function check_stock($id, $quantity) {
        $product = $this->get_by_id($id);
        return ($product && $product['stock'] >= $quantity);
    }

    public function update_stock($id, $quantity) {
        $this->db->where('id', $id);
        return $this->db->update($this->table, [
            'stock' => $this->db->select('stock')->get_where($this->table, ['id' => $id])->row()->stock - $quantity
        ]);
    }

    public function get_by_category($category_id) {
        $query = $this->db->get_where($this->table, ['category' => $category_id]);
        return $query->result_array();
    }
}