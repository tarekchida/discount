<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Get value of discout
 * @param type $pricce
 * @param type $percentage
 * @return type
 */
function getDiscount($price, $percentage) {
    return number_format(($price / 100) * $percentage, 2, '.', '');
}

/**
 * Get disounted price
 * @param type $pricce
 * @param type $percentage
 * @return type
 */
function getDiscountedPrice($price, $percentage) {
    return number_format($price - ($price * ($percentage / 100 )), 2, '.', '');
}
