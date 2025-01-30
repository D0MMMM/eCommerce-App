window.addEventListener("scroll", function() {
    var header = document.querySelector("header");
    header.classList.toggle("sticky", window.scrollY > 0);
});

document.addEventListener('DOMContentLoaded', function() {
    const minusButtons = document.querySelectorAll('.minus-btn');
    const plusButtons = document.querySelectorAll('.plus-btn');

    minusButtons.forEach(button => {
        button.addEventListener('click', function() {
            const cartId = this.getAttribute('data-cart-id');
            const row = this.closest('tr');
            const carQuantity = parseInt(row.getAttribute('data-car-quantity'));
            const quantityElement = this.nextElementSibling;
            let quantity = parseInt(quantityElement.textContent);

            if (quantity > 1) {
                quantity--;
                updateCartQuantity(cartId, quantity);
            }
        });
    });

    plusButtons.forEach(button => {
        button.addEventListener('click', function() {
            const cartId = this.getAttribute('data-cart-id');
            const row = this.closest('tr');
            const carQuantity = parseInt(row.getAttribute('data-car-quantity'));
            const quantityElement = this.previousElementSibling;
            let quantity = parseInt(quantityElement.textContent);

            if (carQuantity > 0) { // Check if there's remaining stock
                quantity++;
                updateCartQuantity(cartId, quantity);
            }
        });
    });

    function updateCartQuantity(cartId, quantity) {
        console.log(`Updating cart ID: ${cartId} to quantity: ${quantity}`);
        fetch('../backend/update_cart_quantity.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ cart_id: cartId, quantity: quantity })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Response from backend:', data);
            if (data.status === 'success') {
                const row = document.querySelector(`tr[data-cart-id="${cartId}"]`);
                const quantityElement = row.querySelector('.quantity');
                const itemTotalElement = row.querySelector('.item-total');
                const price = parseFloat(row.querySelector('.price').textContent.replace('₱', '').replace(/,/g, ''));
                const plusButton = row.querySelector('.plus-btn');
                const minusButton = row.querySelector('.minus-btn');

                // Update quantity display
                quantityElement.textContent = quantity;

                // Update item total
                itemTotalElement.textContent = `₱${(price * quantity).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

                // Update car_quantity attribute with the new stock
                row.setAttribute('data-car-quantity', data.car_quantity);
                const updatedCarQuantity = data.car_quantity;

                // Disable the plus button if the remaining stock is zero
                if (updatedCarQuantity <= 0) {
                    plusButton.disabled = true;
                } else {
                    plusButton.disabled = false;
                }

                // Enable the minus button if quantity is greater than 1
                if (quantity > 1) {
                    minusButton.disabled = false;
                } else {
                    minusButton.disabled = true;
                }

                // Update total payment
                updateTotalPayment();
            } else {
                alert(data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function updateTotalPayment() {
        let totalPayment = 0;
        document.querySelectorAll('.item-total').forEach(itemTotalElement => {
            const itemTotal = parseFloat(itemTotalElement.textContent.replace('₱', '').replace(/,/g, ''));
            totalPayment += itemTotal;
        });
        document.querySelector('.total-payment h4').textContent = `Total Payment: ₱${totalPayment.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    }

    // Initial button state setup
    plusButtons.forEach(button => {
        const row = button.closest('tr');
        const carQuantity = parseInt(row.getAttribute('data-car-quantity'));
        if (carQuantity <= 0) {
            button.disabled = true;
        }
    });

    minusButtons.forEach(button => {
        const row = button.closest('tr');
        const quantity = parseInt(row.querySelector('.quantity').textContent);
        if (quantity <= 1) {
            button.disabled = true;
        }
    });
});