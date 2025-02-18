<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Order_model extends CI_Model {
    
    public function get_all() {
        $query = $this->db->get('orders');
        return $query->result_array();
    }

    public function create($data) {
            

        error_reporting(-1);
		ini_set('display_errors', 1);
        $this->db->trans_start();

    
        try {
                
            $total = 0;
            $subtotal = 0;
            foreach ($data['products'] as $item) {
            $total += $item['stock'] * floatval($item['price']);
            }
    
            $order = [
                'customer_id' => $data['customerId'],
                'total' => number_format($total, 2, '.', '')
            ];
            
            $this->db->insert('orders', $order);
            $order_id = $this->db->insert_id();

    
            foreach ($data['products'] as $product) {
                $product_total = $product['stock'] * floatval($product['price']);
                
                $order_item = [
                    'order_id' => $order_id,
                    'product_id' => $product['id'],
                    'quantity' => $product['order_quantity'],
                    'unit_price' => $product['price'],
                    'total' => number_format($product_total, 2, '.', '')
                ];
                
                $this->db->insert('order_items', $order_item);
    
                $this->db->set('stock', 'stock-' . $product['stock'], FALSE);
                $this->db->where('id', $product['id']);
                $this->db->update('products');


                if (!isset($category_counts[$product['category']])) {
                    $category_counts[$product['category']] = 0;
                    $category_products[$product['category']] = [];
                }
                $category_counts[$product['category']] += $item['stock'];
                $category_products[$product['category']][] = [
                    'product' => $product,
                    'stock' => $item['stock'],
                    'price' => floatval($item['price'])
                ];

                $subtotal += $product['stock'] * floatval($product['price']);
            }

            $final_total = $subtotal;
            $discount_amount_total = 0;

            if ($subtotal >= 1000) {
                $discount_amount = $subtotal * 0.10;
                $discounts[] = [
                    'discountReason' => '10_PERCENT_OVER_1000',
                    'discountAmount' => number_format($discount_amount, 2, '.', ''),
                    'subtotal' => number_format($subtotal - $discount_amount, 2, '.', '')
                ];
                $discount_amount_total += $discount_amount;
                $final_total -= $discount_amount;
            }

            if (isset($category_counts[2]) && $category_counts[2] >= 6) {
                $free_items = floor($category_counts[2] / 6);
                foreach ($category_products[2] as $prod) {
                    $discount_amount = $prod['price'] * $free_items;
                    $discounts[] = [
                        'discountReason' => 'BUY_5_GET_1',
                        'discountAmount' => number_format($discount_amount, 2, '.', ''),
                        'subtotal' => number_format($final_total - $discount_amount, 2, '.', '')
                    ];
                    $discount_amount_total += $discount_amount;
                    $final_total -= $discount_amount;
                    break;
                }
            }

            if (isset($category_counts[1]) && $category_counts[1] >= 2) {
                $cheapest_price = PHP_FLOAT_MAX;
                foreach ($category_products[1] as $prod) {
                    if ($prod['price'] < $cheapest_price) {
                        $cheapest_price = $prod['price'];
                    }
                }
                $discount_amount = $cheapest_price * 0.20;
                $discounts[] = [
                    'discountReason' => 'CATEGORY_1_CHEAPEST_20_PERCENT',
                    'discountAmount' => number_format($discount_amount, 2, '.', ''),
                    'subtotal' => number_format($final_total - $discount_amount, 2, '.', '')
                ];
                $discount_amount_total += $discount_amount;
                $final_total -= $discount_amount;
            }
            
            $discount['orderId'] = $order_id;
            $discount['discounts'] = json_encode($discounts);
            $discount['totalDiscount'] = $discount_amount_total;
            $discount['discountedTotal'] = $final_total;
            $this->db->insert('order_discounts', $discount);

    
            $this->db->trans_complete();
    
            if ($this->db->trans_status() === FALSE) {
                throw new Exception('İşlem başarısız oldu');
            }
    
            return $order_id;
    
        } catch (Exception $e) {
            $this->db->trans_rollback();
            throw $e;
        }
    }
}