<?php

namespace App\Http\Livewire;
use App\Models\Category;

use Livewire\Component;

class HeaderSearchComponent extends Component
{
    public $search;
    Public $product_cat;
    public $product_cat_id;

    public function mount()
    {
        $this->product_cat = 'All Category';
        $this->fill(request()->only('search','product_cat','product_cat_id'));
    }
    public function render()
    { $categories= category::all();
        return view('livewire.header-search-component',['categories'=>$categories]);
    }
}
