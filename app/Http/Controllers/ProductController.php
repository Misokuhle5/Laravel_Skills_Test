<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    private $jsonFile;

    // Constructor to set file path
    public function __construct()
    {
        $this->jsonFile = storage_path('app/products.json');
        // Make sure file exists
        if (!file_exists($this->jsonFile)) {
            file_put_contents($this->jsonFile, json_encode([]));
        }
    }

    // Get all products
    public function index()
    {
        $data = json_decode(file_get_contents($this->jsonFile), true);
        // Sort by date descending
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
    public function store(Request $request)
    {
        $products = json_decode(file_get_contents($this->jsonFile), true);
        $newProduct = [
            'id' => uniqid(), // Unique ID for each product
            'product_name' => $request->input('product_name'),
            'quantity' => (int)$request->input('quantity'),
            'price' => (float)$request->input('price'),
            'submitted_at' => date('Y-m-d H:i:s')
        ];
        array_push($products, $newProduct);
        file_put_contents($this->jsonFile, json_encode($products));
        return ['status' => 'ok'];
    }

    // Update existing product
    public function update(Request $req, $id)
    {
        $products = json_decode(file_get_contents($this->jsonFile), true);
        for ($i = 0; $i < count($products); $i++) {
            if ($products[$i]['id'] == $id) {
                $products[$i]['product_name'] = $req->input('product_name');
                $products[$i]['quantity'] = (int)$req->input('quantity');
                $products[$i]['price'] = (float)$req->input('price');
                break;
            }
        }
        file_put_contents($this->jsonFile, json_encode($products));
        return ['status' => 'ok'];
    }
}
