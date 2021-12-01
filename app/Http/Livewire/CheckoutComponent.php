<?php

namespace App\Http\Livewire;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Shipping;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Cart;
use Stripe;
use Livewire\Component;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;
use SmoDav\iPay\Cashier;

class CheckoutComponent extends Component
{
    public $ship_to_different;

    public $firstname ;
    public $lastname;
    public $email;
    public $mobile;
    public $line1;
    public $line2;
    public $city;
    public $province;
    public $country;
    public $zipcode;


    public $s_firstname ;
    public $s_lastname;
    public $s_email;
    public $s_mobile;
    public $s_line1;
    public $s_line2;
    public $s_city;
    public $s_province;
    public $s_country;
    public $s_zipcode;

    public $paymentmode;
    public $thankyou;

    public $card_no;
    public $exp_month;
    public $exp_year;
    public $cvc;

    // payment variables
    public $paybill = array();
    public $totalAmount;
    public $formHash;
    public $showForm = false;
    public $submitFormButton = true;
    public $formArray = array();
    public $orderId;
    public $iframe;


    public function updated($fields)
    {
        $this->validateOnly($fields,[
            'firstname' =>'required',
            'lastname' =>'required',
            'email' =>'required |email',
            'mobile' =>'required |numeric',
            'line1' =>'required',
            'city' =>'required',
            'province' =>'required',
            'country' =>'required',
            'zipcode' =>'required',
            'paymentmode'=>'required'

        ]);

        if($this->ship_to_different)
        {
            $this->validateOnly($fields,[
                 's_firstname' =>'required',
                 's_lastname' =>'required',
                 's_email' =>'required |email',
                 's_mobile' =>'required |numeric',
                 's_line1' =>'required',
                 's_city' =>'required',
                 's_province' =>'required',
                 's_country' =>'required',
                 's_zipcode' =>'required'
             ]); 
            } 
            if($this->paymentmode == 'card') 
            {
                $this->validateonly($fields,[
                    'card_no'=> 'required|numeric',
                    'exp_month'=> 'required|numeric',
                    'exp_year'=> 'required|numeric',
                    'cvc'=> 'required|numeric'

                ]);
            }   
    }

    // public function mount()
    // {
    //     $dataPoints = array(
    //         array('id' => 21, 'name' => 'Anthony', 'status' => 'added'),
    //         array('id' => 52, 'name' => 'Paul', 'status' => 'added'),
    //         array('id' => 45, 'name' => 'Alex', 'status' => '')
    //     );

    //     array_push($dataPoints, ["id" => '' ,"name" => '', 'status' => '' ]); 

    //         dd($dataPoints);
    // }
    
    public function placeOrder()
    {
        $this->validate([
           'firstname' =>'required',
            'lastname' =>'required',
            'email' =>'required |email',
            'mobile' =>'required |numeric',
            'line1' =>'required',
            'city' =>'required',
            'province' =>'required',
            'country' =>'required',
            'zipcode' =>'required',
            'paymentmode'=>'required'
        ]);  

        $order = new Order();
        $order-> user_id = Auth::user()->id;
        $order-> ordernumber = strtoupper(uniqid('ORD-'));
        $order-> invoicenumber = strtoupper(uniqid('INV-'));
        $order-> subtotal = Session()->get('checkout')['subtotal'];
        $order-> discount = Session()->get('checkout')['discount'];
        $order-> tax = Session()->get('checkout')['tax'];
        $order-> total = Session()->get('checkout')['total'];
        $order->firstname  =$this->firstname ;
        $order->lastname =$this->lastname;
        $order->email =$this-> email;
        $order->mobile =$this->mobile;
        $order->line1 =$this-> line1;
        $order->line2 =$this-> line2;
        $order->city =$this-> city;
        $order->province =$this->province;
        $order->country =$this-> country;
        $order->zipcode =$this-> zipcode;
        $order -> status = 'ordered';
        $order -> is_shipping_different = $this->ship_to_different ? 1:0; 
        $order -> save();

        //make the payment request
        //dd($order);
        //$this->executePayment($order);


        foreach(Cart:: instance ('cart')->content()as $item)
        {
            $orderItem = new OrderItem();
            $orderItem->order_id = $order->id;
            $orderItem->product_id = $item->id;
            $orderItem->price = $item->price;
            $orderItem->quantity = $item->qty;
            $orderItem->save();

        }

        if($this->ship_to_different)
        {
            $this->validate([
                 's_firstname' =>'required',
                 's_lastname' =>'required',
                 's_email' =>'required |email',
                 's_mobile' =>'required |numeric',
                 's_line1' =>'required',
                 's_city' =>'required',
                 's_province' =>'required',
                 's_country' =>'required',
                 's_zipcode' =>'required',
             ]); 

             $shipping = new Shipping();
             $shipping->order_id = $order->id;
             $shipping->firstname  =$this->s_firstname ;
             $shipping->lastname =$this->s_lastname;
             $shipping->email =$this-> s_email;
             $shipping->mobile =$this->s_mobile;
             $shipping->line1 =$this-> s_line1;
             $shipping->line2 =$this-> s_line2;
             $shipping->city =$this-> s_city;
             $shipping->province =$this->s_province;
             $shipping->country =$this-> s_country;
             $shipping->zipcode =$this-> s_zipcode;
             $shipping->save();
        }

        
        
        if($this->paymentmode == 'cod')
        {
           $this->makeTransaction($order->id,'pending');
           $this->restCart();
        }
        else if($this->paymentmode == 'mobile')
        {
        //$stripe = Stripe::make(env('STRIPE_KEY'));

          $paymentStatus = $this->executePayment($order);

          if($paymentStatus){

              $paybillArray = array(
                  'mpesa' => $paymentStatus['mpesa']->paybill,
                  'airtel' => $paymentStatus['airtel']->paybill,
                  'equitel' => $paymentStatus['equitel']->paybill,
                  'text' => $paymentStatus['text']
                );
                
            //dd($paybillArray);

            $this->paybill = $paybillArray;
            $this->totalAmount = $order->total;
            $this->orderId = $order->id;
            $this->restCart();
          }


        }
         else if($this->paymentmode == 'card')
        {
            //enables the second form to submit to ipay for redirection and verify
            //add paymentchannels to the order
            // $cashier = new Cashier();

            // $transactChannels = [
            //     Cashier::CHANNEL_MPESA,
            //     Cashier::CHANNEL_AIRTEL,
            // ];

            // //id tfpharmacy  key *87sUmknfj5seWCy@b$vAVyK2MNQ3%#S

            // $response = $cashier
            // ->usingChannels($transactChannels)
            // ->usingVendorId(env('IPAY_VENDOR_ID', 'demo'), env('IPAY_VENDOR_KEY', 'demoCHANGED'))
            // ->withCallback(route('thankyou'))
            // ->withCustomer('0722000000', 'demo@example.com', false)
            // ->transact(10, 'your order id', 'your order secret');

            // //dd();
            // $this->iframe = $response;

            $fields = array(
                    "live"=> env('IPAY_LIVE', '0'),
                    "oid"=> $order->ordernumber,
                    "inv"=> $order->invoicenumber,
                    "ttl"=> $order->total,
                    "tel"=> $order->mobile,
                    "eml"=> $order->user->email,
                    "vid"=> env('IPAY_VENDOR_ID', 'demo'),
                    "curr"=> "KES",
                    "p1"=> "paymentforgoods",
                    "p2"=>  $order->id,
                    "p3"=> "",
                    "p4"=>  "",
                    "cbk"=> route('thankyou'),
                    "cst"=> "1",
                    "crl"=> "0",
                    "mpesa" => env('IPAY_MPESA', 0),
                    "airtel" => env('IPAY_AIRTEL', 0),
                    "equity" => env('IPAY_EQUITY', 0),
                    "debitcard"=> env('IPAY_DEBIT', 0),
                    "creditcard"=> env('IPAY_CREDIT', 0),
                    );

            $datastring =  $fields['live'].$fields['oid'].$fields['inv'].$fields['ttl'].$fields['tel'].$fields['eml'].$fields['vid'].$fields['curr'].$fields['p1'].$fields['p2'].$fields['p3'].$fields['p4'].$fields['cbk'].$fields['cst'].$fields['crl'];
            $hashkey = env('IPAY_VENDOR_KEY', 'demoCHANGED');

            $generated_hash = hash_hmac('sha1',$datastring , $hashkey);

            $this->formHash = $generated_hash;
            $this->showForm = true;
            $this->submitFormButton = false;
            $this->formArray = $fields;

            //dd($this->formHash, $generated_hash);

            //dd($fields);

            //dd($this->formArray);
        }
              
    }

    //methode to call the payment service
    public function executePayment($request)
    {

        $data = $request;

        $result = ['status' => 200];

        try {

            $result =  new PaymentService;
            $response = $result->initiatePayment($data);

        } catch (Exception $e) {

            $result = [
                'status' => 500,
                'error' => $e->getMessage()
            ];

        }

        return $response;
    }

    public function createCreditCartDeatils($data)
    {

    }

    public function restCart()
    {
        $this->thankyou = 1;
        Cart::instance('cart')->destroy();
        session()->forget('checkout'); 
    }

    public function makeTransaction($order_id,$status)
    {
        $transation = new Transaction();
        $transation ->user_id = Auth::user()->id;
        $transation ->order_id = $order_id;
        $transation->mode = $this->paymentmode;
        $transation->status = $status;
        $transation->save();
    }



    public function verifyForCheckOut()
    {
        if(!Auth::check())
        {
            return redirect()->route('login');
        }
        else if($this->thankyou)
        {
            $paybilNumber = $this->paybill;
            $totalCostAmount = $this->totalAmount;
            $order_id = $this->orderId;
            //dd($paybilNumber);
            //encrypt the order id
            return redirect()->route('thankyou', ['paybilNumber' => $this->paybill, 'amount' => Crypt::encryptString($totalCostAmount), 'transactionType' => 'mobilemoney', 'orderId' =>  Crypt::encryptString($order_id)]);
        }
        elseif (!session()->get('checkout'))
        {
            return redirect ()->route('product.cart');
        }
    }

    public function render()
    {
        $this->verifyForCheckOut();
        return view('livewire.checkout-component')->layout('layouts.base');
    }
}
