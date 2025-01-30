let items = document.querySelectorAll('.slider .list .item');
let next = document.getElementById('next');
let prev = document.getElementById('prev');
let thumbnails = document.querySelectorAll('.thumbnail .item');

let countItem = items.length;
let itemActive = 0;


next.onclick = function() {
    itemActive = itemActive + 1;
    if(itemActive >= countItem) {
        itemActive = 0;
    }
    showSlider();
}
prev.onclick = function() {
    itemActive = itemActive - 1;
    if(itemActive < 0) {
        itemActive = countItem - 1;
    }
    showSlider();
}
let refreshInterval = setInterval(() =>{
    next.click();
}, 5000)

function showSlider() {
    let itemActiveOld = document.querySelectorAll('.slider .list .item.active');
    let thumbnailActiveOld = document.querySelectorAll('.thumbnail .item.active');
    
    itemActiveOld.forEach(item => item.classList.remove('active'));
    thumbnailActiveOld.forEach(item => item.classList.remove('active'));

    items[itemActive].classList.add('active');
    thumbnails[itemActive].classList.add('active');
    
    clearInterval(refreshInterval);
    refreshInterval = setInterval(() =>{
        next.click();
    }, 5000)
}

thumbnails.forEach((thumbnail, index) => {
    thumbnail.onclick = function() {
        itemActive = index;
        showSlider();
    }
});

window.addEventListener("scroll", function() {
    var header = document.querySelector("header");
    header.classList.toggle("sticky", window.scrollY > 0);
});
document.getElementById('search-input').addEventListener('input', function() {
    const searchValue = this.value.toLowerCase();
    const carContainers = document.querySelectorAll('.toyota-container');

    carContainers.forEach(container => {
        const carName = container.getAttribute('data-car-name').toLowerCase();
        if (carName.includes(searchValue)) {
            container.style.display = '';
        } else {
            container.style.display = 'none';
        }
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const viewButtons = document.querySelectorAll('.view-btn');

    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const carData = JSON.parse(this.getAttribute('data-car'));
            openModal(carData);
        });
    });

    const addToCartForm = document.getElementById('add-to-cart-form');
    addToCartForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('../backend/add_to_cart.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') {
                // Update stock in dashboard
                const carId = data.car_id;
                const newStock = data.new_stock;
                const stockElement = document.getElementById(`stock-${carId}`);
                stockElement.textContent = `Stock: ${newStock}`;

                // Disable button if out of stock
                if(newStock === 0){
                    const viewBtn = document.querySelector(`.toyota-container[data-car-id="${carId}"] .view-btn`);
                    viewBtn.disabled = true;
                    viewBtn.textContent = 'OUT OF STOCK';
                }

                Swal.fire('Success', 'Car added to cart successfully!', 'success');
                closeModal();
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'An unexpected error occurred.', 'error');
        });
    });
});
