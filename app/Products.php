<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App;

/**
 * Description of Customers
 *
 * @author Tarek.Chida
 */
class Products {

    /**
     * Get category by product id
     * @param type $id
     * @return boolean / cat_id
     */
    public static function findCategory($id) {
        if ($products = self::load()) {
            foreach ($products as $product) {
                if ($product->id == $id) {
                    return $product->category;
                }
            }
            return FALSE;
        }
    }

    /**
     * Load Produts
     * @return boolean
     */
    public static function load() {
        $filePath = storage_path('app') . "/products.json";

        if (file_exists($filePath)) {
            $string = file_get_contents($filePath);
            $customers = json_decode($string);
            return $customers;
        } else {
            Log::error('File JSON not found');
            return FALSE;
        }
    }

}
