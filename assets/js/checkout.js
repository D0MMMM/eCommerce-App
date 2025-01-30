window.addEventListener("scroll", function() {
    var header = document.querySelector("header");
    header.classList.toggle("sticky", window.scrollY > 0);
});

document.addEventListener('DOMContentLoaded', function() {
    // Get Philippines regions/states
    fetch('https://restcountries.com/v3.1/alpha/PH')
    .then(response => response.json())
    .then(data => {
        const stateSelect = document.getElementById('state');
        // Philippines regions
        const regions = [
            'NCR', 'Region I', 'Region II', 'Region III',
            'Region IV-A', 'Region IV-B', 'Region V', 'Region VI',
            'Region VII', 'Region VIII'
        ];
        
        regions.forEach(region => {
            const option = document.createElement('option');
            option.value = region;
            option.textContent = region;
            stateSelect.appendChild(option);
        });
    });

    // Populate cities based on region
    document.getElementById('state').addEventListener('change', function() {
        const region = this.value;
        const citySelect = document.getElementById('city');
        citySelect.innerHTML = '';
        
        // Major cities by region
        const citiesByRegion = {
            'NCR': ['Manila', 'Quezon City', 'Makati', 'Taguig', 'Pasig'],
            'Region III': ['Angeles', 'San Fernando', 'Olongapo', 'Malolos'],
            'Region IV-A': ['Antipolo', 'Calamba', 'Batangas City', 'Lucena'],
            'Region VII': ['Cebu City', 'Mandaue', 'Lapu-Lapu', 'Talisay', 'Carcar'],
            'Region VIII': ['Ormoc', 'Tacloban', 'Baybay', 'Maasin', 'Catbalogan']
        };

        const cities = citiesByRegion[region] || [];
        cities.forEach(city => {
            const option = document.createElement('option');
            option.value = city;
            option.textContent = city;
            citySelect.appendChild(option);
        });
    });

    const form = document.querySelector('form');
    const submitBtn = document.getElementById('placeOrderBtn');
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Basic form validation
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        // Get form data
        const formData = new FormData(form);
        const paymentMethod = formData.get('payment_method');
        const total = document.querySelector('.total-amount').textContent.replace('₱', '').replace(/,/g, '');

        // Add total amount to form data
        formData.append('total_amount', total);

        // Format total amount
        const formattedTotal = formatCurrency(total);

        // Show confirmation dialog
        const result = await Swal.fire({
            title: 'Confirm Order',
            html: `
                <div class="order-confirmation">
                    <p>Total Amount: ${formattedTotal}</p>
                    <p>Payment Method: ${paymentMethod.toUpperCase()}</p>
                    <p>Delivery Address:</p>
                    <p>${formData.get('address')}</p>
                    <p>${formData.get('city')}, ${formData.get('state')}</p>
                    <p>${formData.get('zip')}, ${formData.get('country')}</p>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Place Order',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#e32636',
            reverseButtons: true
        });

        if (!result.isConfirmed) {
            return;
        }

        // Show loading state
        submitBtn.disabled = true;
        try {
            const btnText = submitBtn.querySelector('.btn-text');
            const btnLoader = submitBtn.querySelector('.btn-loader');
            
            if (btnText && btnLoader) {
                btnText.style.display = 'none';
                btnLoader.style.display = 'inline-block';
            }
            
            // Handle different payment methods
            if (paymentMethod === 'paypal') {
                // Redirect to PayPal
                const response = await fetch('../backend/process_paypal.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                if (data.status === 'success') {
                    window.location.href = data.redirect_url;
                } else {
                    throw new Error(data.message || 'Failed to create PayPal order');
                }
                return;
            } else if (paymentMethod === 'gcash') {
                // Redirect to GCash via PayMongo
                const response = await fetch('../backend/process_gcash.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                if (data.status === 'success') {
                    window.location.href = data.redirect_url;
                } else {
                    throw new Error(data.message || 'Failed to create GCash payment');
                }
                return;
            }

            // Submit form for COD
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData
            });

            // Read response text once
            const responseText = await response.text();

            try {
                // Try parsing as JSON
                const data = JSON.parse(responseText);
                
                if (data.status === 'success') {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Order Placed Successfully!',
                        text: 'Thank you for your order.',
                        confirmButtonColor: '#e32636'
                    });
                    window.location.href = '../frontend/dashboard.php';
                } else {
                    throw new Error(data.message || 'Failed to place order');
                }
            } catch (jsonError) {
                // Handle non-JSON response
                if (responseText.includes('success')) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Order Placed Successfully!',
                        text: 'Thank you for your order.',
                        confirmButtonColor: '#e32636'
                    });
                    window.location.href = '../frontend/dashboard.php';
                } else {
                    throw new Error('Failed to place order. Please try again.');
                }
            }

        } catch (error) {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'Failed to place order. Please try again.',
                confirmButtonColor: '#e32636'
            });
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                const btnText = submitBtn.querySelector('.btn-text');
                const btnLoader = submitBtn.querySelector('.btn-loader');
                
                if (btnText) btnText.style.display = 'inline-block';
                if (btnLoader) btnLoader.style.display = 'none';
            }
        }
    });

    // Handle payment method change
    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const submitBtn = document.getElementById('placeOrderBtn');
            submitBtn.textContent = this.value === 'cod' ? 'Place Order' : 
                                  `Pay with ${this.value.toUpperCase()}`;
        });
    });
});

// Function to format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP'
    }).format(amount);
}