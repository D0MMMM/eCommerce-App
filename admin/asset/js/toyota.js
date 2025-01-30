document.querySelectorAll('.edit-btn').forEach(button => {
    button.addEventListener('click', function() {
        const carData = JSON.parse(this.getAttribute('data-car'));
        
        // Populate modal fields
        document.getElementById('editCarId').value = carData.id;
        document.getElementById('editBrand').value = carData.make;
        document.getElementById('editModel').value = carData.model;
        document.getElementById('editDateDropdown').value = carData.year;
        document.getElementById('editPrice').value = carData.price;
        document.getElementById('editQuantity').value = carData.quantity;
        document.getElementById('editConditionDropdown').value = carData.car_condition;
        document.getElementById('editDescription').value = carData.description;
        
        // Show modal with animation
        const modal = document.getElementById('editModal');
        modal.style.display = 'block';
        modal.classList.add('show-modal');
    });
});

document.getElementById('closeEditModal').addEventListener('click', function() {
    const modal = document.getElementById('editModal');
    modal.classList.remove('show-modal');
    setTimeout(() => {
        modal.style.display = 'none';
    }, 300);
});

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    const modal = document.getElementById('editModal');
    if (event.target === modal) {
        modal.classList.remove('show-modal');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }
});

// Populate year dropdown in edit modal
let editDateDropdown = document.getElementById('editDateDropdown'); 
let current_Year = new Date().getFullYear();    
let earliest_Year = 1970;     
while (current_Year >= earliest_Year) {      
    let date_Option = document.createElement('option');          
    date_Option.text = current_Year;      
    date_Option.value = current_Year;        
    editDateDropdown.add(date_Option);      
    current_Year -= 1;    
}

// Populate condition dropdown in edit modal
let editConditionDropdown = document.getElementById('editConditionDropdown'); 
let defOption = document.createElement('option');
defOption.text = '--- SELECT CONDITION ---';
defOption.value = '';
defOption.disabled = true;
defOption.selected = true;
editConditionDropdown.add(defOption);
editConditionDropdown.add(new Option('BRAND NEW', 'BRAND NEW'));
editConditionDropdown.add(new Option('USED', 'USED'));
