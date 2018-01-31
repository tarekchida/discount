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

        //Set Free items discount
        foreach ($this->order['items'] as &$item) {
            $this->itemFree($item);
        }

        //Get disount for cheapest item
        $this->discountCheapest();

        //Recalculate Total
        $this->totalCalculator();

        // Set global order discount
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

    /**
     * Check disount for cheapest item
     */
    private function discountCheapest() {
        $cheapestConfig = config('discount.cheapest');

        foreach ($cheapestConfig as $cat => $discount) {
            $relatedProducts = array();
            foreach ($this->order['items'] as $item) {
                if (Products::findCategory($item['product-id']) == $cat) {
                    $relatedProducts[$item['product-id']] = $item['unit-price'];
                }
            }

            if (count($relatedProducts > 1)) {
                $this->setDiscountCheapest($relatedProducts, $discount, $cat);
            }
        }
    }

    /**
     * Set disount for cheapest item
     * @param type $relatedProducts
     * @param type $discount
     * @param type $cat
     */
    private function setDiscountCheapest($relatedProducts, $discount, $cat) {
        //Sort ASC by price 
        asort($relatedProducts);

        //Get the lowest price
        reset($relatedProducts);
        $productId = key($relatedProducts);

        //Edit order wtih new data
        foreach ($this->order['items'] as &$item) {
            if ($item['product-id'] == $productId) {
                $item['unit-price'] = getDiscountedPrice($item['unit-price'], $discount);
                $item['total'] = $item['unit-price'] * $item['quantity'];
                //Add discount data 
                $this->order['discounts']['cheapest'][] = array(
                    'product-id' => $item['product-id'],
                    'category' => $cat,
                    'discount' => $item['unit-price']
                );
            }
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
            $this->order['discounts']['global'] = getDiscount($this->order['total'], $globalDiscount['discount']);
        }
    }

}
