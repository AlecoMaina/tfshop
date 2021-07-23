<?php

namespace App\Http\Livewire\Admin;

use Livewire\Component;
use Illuminate\Support\Str;
use App\Models\Product;
use Illuminate\Support\Carbon;
use Livewire\WithFileUploads;
use App\Models\Category;

class AdminEditProductComponent extends Component
{   use WithFileUploads;
    public $name;
    public $slug;
    public $short_description;
    public $description;
    public $regular_price;
    public $sale_price;
    public $sku;
    public $stock_status;
    public $feature;
    public $quantity;
    public $image;
    public $category_id;
    public $newimage;
    Public $product_id;

    public function mount($product_slug){
        $product = Product::where('slug',$product_slug)->first();
       $this->name = $product->name;
       $this->slug = $product->slug;
       $this->short_description = $product->short_description;
       $this->description = $product->description;
       $this->regular_price = $product->regular_price;
       $this->sale_price = $product->sale_price;
       $this->sku = $product->product;
       $this->stock_status = $product->stock_status;
       $this->feature = $product->feature;
       $this->quantity = $product->quantity;
       $this->image = $product->image;
       $this->category_id = $product->category_id;
       $this->product_id = $product->id;
    }
    public function generateslug()
        {
            $this->slug = Str::slug($this->name,'-');
        }
        public function updated($fields){
            $this->validateOnly($fields,[
            'name' => 'required',
            'slug' => 'required|unique:products',
            'short_description' => 'required',
            'description' => 'required',
            'regular_price' => 'required|numeric',
            'sale_price' => 'numeric',
            'sku' => 'required',
            'stock_status' => 'required',
            'quantity' => 'required|numeric',
            'newimage' => 'required|mimes:jpeg,png',
           'category_id'=>'required',
            ]);
        }

        public function updateProduct()
        {    $this->validate([
            'name' => 'required',
            'slug' => 'required|unique:products',
            'short_description' => 'required',
            'description' => 'required',
            'regular_price' => 'required|numeric',
            'sale_price' => 'numeric',
            'sku' => 'required',
            'stock_status' => 'required',
            'quantity' => 'required|numeric',
            'newimage' => 'required|mimes:jpeg,png',
            'category_id' =>'required',
            
        ]);
            $product = Product:: find($this->product_id);
            $product->name = $this->name;
            $product->slug = $this->slug;
            $product->short_description = $this->short_description;
            $product->description = $this->description;
            $product->regular_price = $this->regular_price;
            $product->sale_price = $this->sale_price;
            $product->sku = $this->sku;
            $product->stock_status = $this->stock_status;
            $product->feature = $this->feature;
            $product->quantity = $this->quantity;
            if($this->newimage)
            {
                $imageName = Carbon::now()->timestamp. '.' .$this->newimage->extension();
                $this->newimage->storeAs('products',$imageName);
                $product->image = $imageName;
            }
            
            $product->category_id = $this->category_id;
            $product->save();
            session()->flash('message','Product has been updated successfully');
    
        }

    public function render()
    { 
        $categories = Category::all();
        return view('livewire.admin.admin-edit-product-component',['categories'=>$categories])->layout('layouts.base');
    }
}
