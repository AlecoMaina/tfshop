<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Services\PaymentService;
use App\Models\Payment;
use App\Models\Order;
use App\Models\CardPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Cart;

class ThankyouComponent extends Component
{
    public $paymentSession;
    public $paymentContainer = true;
    public $successContainer = false;
    public $decryptedOrderId;
    public $decryptedPaybill;
    public $decryptedAmount;
    public $alert = false;

    public function mount(Request $request)
    {

        //dd($_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]);

        if($request->transactionType != 'mobilemoney'){
            //dd($request->all());
            $updateOrder = $this->updateOrderPayment($request->all());

            if($updateOrder){
                //dd($request->all());
                $this->restCart();
                $this->successContainer = true;
                $this->paymentContainer = false;
            }
        } else {
            //decrypt the order id
            try {
                $this->decryptedOrderId = Crypt::decryptString($request->orderId);
                $this->decryptedPaybill = $request->paybilNumber;
                $this->decryptedAmount = Crypt::decryptString($request->amount);
            } catch (DecryptException $e) {
                //
            }
        }

            
    }

    public function render()
    {
        //$this->verifyForCheckOut();
        return view('livewire.thankyou-component')->layout('layouts.base');
    }

    public function verifyForCheckOut()
    {
         if($this->$paymentSession)
            {
                return redirect()->route('thankyou');
            }
            elseif (!session()->get('checkout'))
            {
                return redirect ()->route('product.cart');
            }
    }

    public function searchPayment()
    {

        $order = Payment::where('order_id', $this->decryptedOrderId)->firstOrFail();

        //dd($order);

        $checkPayment =  new PaymentService;

        $pay = $checkPayment->searchPayment($order);

        //dd($pay);

        if($pay){

            //dd('update Ui for payment received', $order);
            $this->successContainer = true;
            $this->paymentContainer = false;

            return;

        }

        session()->flash('warning_message', 'Not yet paid');

        //return or make a message ui to say check again.
        //dd('no data to search');
    }

    public function updateOrderPayment($data)
    {
        $order = Order::findOrFail($data['p2']);

        //dd($data['p2']);

        $account = CardPayment::create([
                    'status' => $data['status'],
                    'transaction_code' => $data['txncd'],
                    'name' => $data['msisdn_id'],
                    'paid_at' => now(),
                    'payment_mode' => $data['channel'],
                    'payment_status' => 1,
                    'order_id' => $order->id,
                    'telephone' => $data['msisdn_idnum'],
                    'transaction_amount' => $data['mc']
                ]);

        return $account;
    }

    public function restCart()
    {
        Cart::instance('cart')->destroy();
        session()->forget('checkout'); 
    }
}
