<div>
    
	<main id="main" class="main-site">

		<div class="container">

			<div class="wrap-breadcrumb">
				<ul>
					<li class="item-link"><a href="/" class="link">home</a></li>
					<li class="item-link"><span>Thank You</span></li>
				</ul>
			</div>
		</div>

		@if(Session::has('warning_message'))
		<div class="alert alert-warning">
			<strong>{{Session::get('warning_message')}}</strong>
		</div>
		@endif

		@if($paymentContainer)

		<div class="container_fluid text-center">
			<div class="alert alert-success" role="alert">
				@if($decryptedPaybill['text'] != 'NO STK')
					{{ $decryptedPaybill['text'] ?? '' }}
				@endif
			</div>
		</div>

		<div class="container pb-60">
			<div class="row">
				<div class="col-md-12 text-center">
					<h2>Complete your payment</h2>
                    <p>Go to Mpesa and pay ksh{{ $decryptedAmount ?? '' }} to paybill {{ $decryptedPaybill['mpesa'] ?? '' }}.</p>
                    <p>Go to Airtell and pay ksh{{ $decryptedAmount ?? '' }} to paybill {{ $decryptedPaybill['airtel'] ?? '' }}.</p>
                    <p>Go to Equitel and pay ksh{{ $decryptedAmount ?? '' }} to paybill {{ $decryptedPaybill['equitel'] ?? '' }}.</p>

					<!-- alert to also notify the user that also apayment request has been sent to the number -->

					<button wire:click="searchPayment()" class="btn btn-sm btn-outline-danger py-0">Check Payment Status</button>
				</div>
			</div>
		</div>
		@endif
		
		@if($successContainer)
		<div class="container pb-60">
			<div class="row">
				<div class="col-md-12 text-center">
					<h2>Thank you for your order</h2>
                    <p>A confirmation email was sent.</p>
				</div>
			</div>
		</div>
		@endif

	</main>
	
</div>
