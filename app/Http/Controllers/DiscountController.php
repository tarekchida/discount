<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Customers;
use App\Products;

class DiscountController extends Controller {

    protected $order;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        //I simulated authentification by findind user Id in Cusomers.json
        $this->middleware('auth');
    }

    public function getDiscount(Request $request) {
        $this->order = $request->all();
        // Validate order
        if (empty($this->order)) {
            return response('Order is empty', 400);
        }

        // Set global order discount
        foreach ($this->order['items'] as &$item) {
            $this->itemFree($item);
        }


        $this->totalCalculator();
        $this->globalDiscount();
        return response($this->order);
    }

    /**
     * Calculate total
     */
    private function totalCalculator() {
        $total = 0;
        foreach ($this->order['items'] as $item) {
            $total +=$item['total'];
        }
        $this->order['total'] = $total;
    }

    private function discountCheapest() {
        $items = $this->order['items'];
        foreach ($items as $item) {
            
        }
    }

    /**
     * Manage free items in order
     * @param type $item
     */
    private function itemFree(&$item) {
        $freeConfig = config('discount.free');

        foreach ($freeConfig as $cat => $number) {
            if (Products::findCategory($item['product-id']) == $cat) {

                // Get number of free items 
                $freeItems = $item['quantity'] / $number;
                //Is there free items ?
                if ((int) $freeItems >= 1) {
                    //Calculate new total
                    $item['total'] = number_format($item['unit-price'] * ($item['quantity'] - (int) $freeItems ), 2, '.', '');
                    //Add discount data 
                    $this->order['discounts']['free'][] = array(
                        'product-id' => $item['product-id'],
                        'category' => $cat,
                        'item-free' => (int) $freeItems
                    );
                }
            }
        }
    }

    /**
     * Set Global discount
     */
    private function globalDiscount() {
        $globalDiscount = config('discount.global');
        //Test if total is discountable
        if ($this->order['total'] > $globalDiscount['total']) {
            $this->order['discounts']['global'] = ( $this->order['total'] / 100) * $globalDiscount['discount'];
        }
    }

}
