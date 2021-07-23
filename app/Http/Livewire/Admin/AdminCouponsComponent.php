<?php

namespace App\Http\Livewire\Admin;

use Livewire\Component;
use App\Models\Coupon;

class AdminCouponsComponent extends Component
{  
    public function deleteCoupon($coupon_id)
    {
        $coupon = Coupon::Find ($coupon_id);
        $coupon ->delete();
        session()->flash('message','Coupon  has been Deleted successfully');
    }
    public function render()
    { 
        $coupons = Coupon::all();
        return view('livewire.admin.admin-coupons-component',['coupons'=>$coupons])->layout('layouts.base');
    }
}
