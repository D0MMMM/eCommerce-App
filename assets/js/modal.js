function openModal(carData) {
    const modal = document.getElementById('carModal');
    const carIdInput = document.getElementById('carIdInput');
    
    document.getElementById('carImage').src = `../admin/asset/uploaded_img/${carData.image_path}`;
    document.getElementById('carMake').textContent = carData.make;
    document.getElementById('carModel').textContent = carData.model;
    document.getElementById('carYear').textContent = carData.year;
    document.getElementById('carPrice').textContent = `₱${parseFloat(carData.price).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    document.getElementById('carCondition').textContent = carData.car_condition;
    document.getElementById('carDescription').textContent = carData.description;

    carIdInput.value = carData.car_id;
    
    modal.style.display = 'block';
}

function closeModal() {
    const modal = document.getElementById('carModal');
    modal.style.display = 'none';
}

document.getElementById('closeModal').addEventListener('click', closeModal);
window.addEventListener('click', function(event) {
    const modal = document.getElementById('carModal');
    if (event.target == modal) {
        closeModal();
    }
});
