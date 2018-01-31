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
class Customers {

    /**
     * Get Customer by id
     * @param type $id
     * @return boolean
     */
    public static function find($id) {
        if ($customers = self::load()) {
            foreach ($customers as $customer) {
                if ($customer->id == $id) {
                    return $customer;
                }
            }
            return FALSE;
        }
    }

    /**
     * Load Cutomers
     * @return boolean
     */
    public static function load() {
        $filePath = storage_path('app') . "/customers.json";

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
