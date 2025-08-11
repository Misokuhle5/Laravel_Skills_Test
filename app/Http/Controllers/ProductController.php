<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    private $jsonFile;

    public function __construct()
    {
        $this->jsonFile = storage_path('app/products.json');

        if (!Storage::exists('products.json')) {
            Storage::put('products.json', json_encode([]));
        }
    }

    // Get all products
    public function index()
    {
        $data = json_decode(Storage::get('products.json'), true);
        if (!$data) $data = [];

        $sorted = [];
        for ($i = 0; $i < count($data); $i++) {
            $sorted[] = $data[$i];
        }
        for ($i = 0; $i < count($sorted) - 1; $i++) {
            for ($j = $i + 1; $j < count($sorted); $j++) {
                if (strtotime($sorted[$i]['submitted_at']) < strtotime($sorted[$j]['submitted_at'])) {
                    $temp = $sorted[$i];
                    $sorted[$i] = $sorted[$j];
                    $sorted[$j] = $temp;
                }
            }
        }
        return response()->json($sorted);
    }

    // Save new product
    public function store(Request $req)
    {
        // Load existing products
        $products = json_decode(Storage::get('products.json'), true);
        if (!$products) $products = []; 
        // Create new product
        $newProduct = [
            'id' => uniqid(),
            'product_name' => $req->input('product_name'),
            'quantity' => (int)$req->input('quantity'),
            'price' => (float)$req->input('price'),
            'submitted_at' => date('Y-m-d H:i:s')
        ];
        $products[] = $newProduct;

        Storage::put('products.json', json_encode($products, JSON_PRETTY_PRINT));
        return ['status' => 'ok'];
    }

    // Update existing product
    public function update(Request $req, $id)
    {
        $products = json_decode(Storage::get('products.json'), true);
        if (!$products) $products = [];
        for ($i = 0; $i < count($products); $i++) {
            if ($products[$i]['id'] == $id) {
                $products[$i]['product_name'] = $req->input('product_name');
                $products[$i]['quantity'] = (int)$req->input('quantity');
                $products[$i]['price'] = (float)$req->input('price');
                break;
            }
        }
        Storage::put('products.json', json_encode($products, JSON_PRETTY_PRINT));
        return ['status' => 'ok'];
    }
}
