window.addEventListener("scroll", function() {
    var header = document.querySelector("header");
    header.classList.toggle("sticky", window.scrollY > 0);
});

// Updated modal handling
document.querySelectorAll('.view-btn').forEach(button => {
    button.addEventListener('click', function() {
        const carData = JSON.parse(this.getAttribute('data-car'));
        
        // Update modal content
        document.getElementById('carImage').src = `../admin/asset/uploaded_img/${carData.image_path}`;
        document.getElementById('carMake').textContent = carData.make;
        document.getElementById('carModel').textContent = carData.model;
        document.getElementById('carYear').textContent = carData.year;
        document.getElementById('carPrice').textContent = `₱${parseFloat(carData.price).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        document.getElementById('carCondition').textContent = carData.car_condition;
        document.getElementById('carDescription').textContent = carData.description;
        document.getElementById('carIdInput').value = carData.car_id;
        
        // Show modal
        document.getElementById('carModal').style.display = 'block';
    });
});

document.querySelector('#closeModal').addEventListener('click', function() {
    document.querySelector('#carModal').style.display = 'none';
});

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    const modal = document.getElementById('carModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
});

// Handle add to cart form submission
document.querySelector('.add-to-cart-btn form').addEventListener('submit', function(event) {
    event.preventDefault();
    const formData = new FormData(this);

    fetch(this.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: data.message
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while adding the car to the cart.'
        });
    });
});