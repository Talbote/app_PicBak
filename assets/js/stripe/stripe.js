var stripe = Stripe('pk_test_51I0mX6L4sACyrZxiVDkU4KLAIZoNNgNwEspC7UZzXjUHhAaeljonSrXt1vbqQ8xWY5z4nA40QD7pqUpVTawNaqbv00KEc97CGJ');
var checkoutButton = document.getElementById('checkout-button');

if (document.getElementById('checkout-button')) {

    checkoutButton.addEventListener('click', function () {
        // Create a new Checkout Session using the server-side endpoint you
        // created in step 3.

        fetch('/create-checkout-session', {
            method: 'POST',
        })

            .then(function (response) {
                return response.json();
            })

            .then(function (session) {
                return stripe.redirectToCheckout({sessionId: session.id});

            })

            .then(function (result) {
                // If `redirectToCheckout` fails due to a browser or network
                // error, you should display the localized error message to your
                // customer using `error.message`.
                if (result.error) {
                    alert(result.error.message);
                }
            })
            .catch(function (error) {
                console.error('Error:', error);
            })

    });

}else {

   // console.log('okay');

}

