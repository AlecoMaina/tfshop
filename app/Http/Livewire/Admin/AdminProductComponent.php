<?php

namespace App\Http\Livewire\Admin;
use Livewire\WithPagination;
use App\Models\Product;

use Livewire\Component;

class AdminProductComponent extends Component
{ 
    use WithPagination;
    public function deleteProduct($id)
    {
        $product = Product::find($id);
        $product->delete();
        session()->flash('message','Product has been Deleted successfully');
    }

    public function render()
    {
        $products = Product::paginate(10);
        return view('livewire.admin.admin-product-component',['products'=>$products])->layout('layouts.base');
    }
}
