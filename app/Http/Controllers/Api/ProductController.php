<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('user')->where('user_id', $request->user()->id)->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Product data loaded successfully',
            'data' => $products
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:products,name',
            'description' => 'required|string',
            'price' => 'required|integer',
            'stock' => 'required|integer',
            'is_available' => 'required|boolean',
            'is_favorite' => 'required|boolean',
            'image' => 'required|image',
        ]);

        $user = $request->user();
        $request->merge(['user_id' => $user->id]);

        $data = $request->all();

        $product = Product::create($data);

        $image = $request->file('image');
        $image_name = time() . '.' . $image->getClientOriginalExtension();
        $image->move('uploads/products', $image_name);

        $product->image = $image_name;
        $product->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Product added successfully',
            'data' => $product
        ]);
    }

    public function update(Request $request, $id)
    {
        $user_id = $request->user()->id;

        $product = Product::with('user')->where('user_id', $request->user()->id)->get()->find($id);

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found',
            ], 404);
        }

        $request->validate([
            'name' => [
                'required',
                'string',
                Rule::unique('products')->where(function ($query) use ($user_id) {
                    return $query->where('user_id', $user_id);
                }),
            ],
            'description' => 'required|string',
            'price' => 'required|integer',
            'stock' => 'required|integer',
            'is_available' => 'required|boolean',
            'is_favorite' => 'required|boolean',
        ]);

        $data = $request->all();

        $product->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Product updated successfully',
            'data' => $product
        ]);
    }

    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found',
            ], 404);
        }

        $product->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Product deleted successfully',
        ]);
    }
}
