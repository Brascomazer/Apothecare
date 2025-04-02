new Vue({
    el: '#app',
    data: {
        cartItems: [
            { product: 'Paracetamol 500mg', quantity: 2, price: 5.99 },
            { product: 'Ibuprofen 400mg', quantity: 1, price: 7.50 },
            { product: 'Vitamine D3', quantity: 1, price: 12.95 }
        ],
        selectedBank: 'ING'
    },
    computed: {
        totalPrice() {
            return this.cartItems.reduce((total, item) => total + (item.price * item.quantity), 0);
        }
    },
    methods: {
        removeItem(index) {
            this.cartItems.splice(index, 1);
        },
        processPayment() {
            // Verzend de betalingsgegevens naar de server
            fetch('../api/process_payment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    cartItems: this.cartItems,
                    totalPrice: this.totalPrice,
                    selectedBank: this.selectedBank
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Betaling succesvol verwerkt!');
                    // Redirect naar een bedankpagina of orderoverzicht
                    window.location.href = '../pages/order_confirmation.html';
                } else {
                    alert('Er is een fout opgetreden: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Er is een fout opgetreden bij het verwerken van uw betaling.');
            });
        }
    }
});