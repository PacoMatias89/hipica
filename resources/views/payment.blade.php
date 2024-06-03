<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Gateway</title>
    <link rel="stylesheet" href="{{ asset('/css/style.css') }}">
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        .StripeElement {
            box-sizing: border-box;
            height: 40px;
            padding: 10px 12px;
            border: 1px solid transparent;
            border-radius: 4px;
            background-color: white;
            box-shadow: 0 1px 3px 0 #e6ebf1;
            transition: box-shadow 150ms ease;
        }

        .StripeElement--focus {
            box-shadow: 0 1px 3px 0 #cfd7df;
        }

        .StripeElement--invalid {
            border-color: #fa755a;
        }

        .StripeElement--webkit-autofill {
            background-color: #fefde5 !important;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .message {
            margin-bottom: 20px;
        }

        .message .success,
        .message .error {
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 4px;
            font-weight: bold;
        }

        .message .success {
            background-color: #4caf50;
            color: #fff;
        }

        .message .error {
            background-color: #f44336;
            color: #fff;
        }

        .form-row {
            margin-bottom: 20px;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            border-color: #007bff;
            outline: none;
        }

        .card-element {
            margin-top: 10px;
        }

        .submit-btn {
            text-align: center;
        }

        .btn-primary {
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 12px 20px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="message">
            @if ($message = Session::get('error'))
                <div class="error">
                    <strong>{{ $message }}</strong>
                </div>
            @endif
        </div>

        <form action="{{ route('charge') }}" method="POST" id="payment-form">
            @csrf
            <div class="form-row">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" class="form-control" placeholder="Name" value="{{ Auth::user()->name }}" readonly>
            </div>

            <div class="form-row">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="Email" value="{{ Auth::user()->email }}" readonly>
            </div>

            @if(isset($bookingData))
                <div class="form-row">
                    <label for="date">Booking Date</label>
                    <input type="date" id="date" name="date" class="form-control" value="{{ $bookingData['date'] }}" readonly>
                </div>

                <div class="form-row">
                    <label for="time">Booking Time</label>
                    <input type="text" id="time" name="time" class="form-control" value="{{ $bookingData['time'] }}" readonly>
                </div>

                <div class="form-row">
                    <label for="horse_price">Horse Price</label>
                    <input type="number" id="hidden_horse_price" name="hidden_horse_price" class="form-control" value="{{ $bookingData['horse_price'] }}" readonly>
                </div>

                <div class="form-row">
                    <label for="comments">Comments</label>
                    <textarea id="comments" name="comments" class="form-control" rows="3" readonly>{{ $bookingData['comments'] }}</textarea>
                </div>
                <input type="hidden" id="horse_id" name="horse_id" value="{{ $bookingData['horse_id'] }}">
            @else
                <div class="form-row">
                    <p>No booking data available.</p>
                </div>
            @endif

            <div class="form-row">
                <label for="card-element">Credit or debit card</label>
                <div id="card-element" class="StripeElement card-element">
                    <!-- Un contenedor para la tarjeta de crédito -->
                </div>
                <div id="card-errors" role="alert"></div>
            </div>

            <button class="btn-primary" type="submit">Pagar</button>
        </form>
    </div>

    <script>
        var stripe = Stripe('{{ config('services.stripe.publishable_key') }}');
        var elements = stripe.elements();
        var cardElement = elements.create('card');
        cardElement.mount('#card-element');

        var form = document.getElementById('payment-form');
        form.addEventListener('submit', function(event) {
            event.preventDefault();

            stripe.createToken(cardElement).then(function(result) {
                if (result.error) {
                    var errorElement = document.getElementById('card-errors');
                    errorElement.textContent = result.error.message;
                } else {
                    stripeTokenHandler(result.token);
                }
            });
        });

        function stripeTokenHandler(token) {
            var form = document.getElementById('payment-form');
            var hiddenInput = document.createElement('input');
            hiddenInput.setAttribute('type', 'hidden');
            hiddenInput.setAttribute('name', 'stripeToken');
            hiddenInput.setAttribute('value', token.id);
            form.appendChild(hiddenInput);
            form.submit();
        }
    </script>
</body>
</html>