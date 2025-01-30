<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Edit Car Details</h2>
            <button class="close" id="closeEditModal"><i class="fa-solid fa-x""></i></button>
        </div>
        <div class="modal-body">
            <form action="../backend/update_car.php" method="post" enctype="multipart/form-data" id="editCarForm">
                <input type="hidden" name="car_id" id="editCarId">
                <input type="text" name="brand" id="editBrand" readonly required>
                <input type="text" name="model" id="editModel" placeholder="Model" required>
                <select id='editDateDropdown' name="year" required>
                </select>
                <input type="number" placeholder="Price" min="0" step="1" name="price" id="editPrice" required="required">
                <input type="number" placeholder="Quantity" min="0" step="1" name="quantity" id="editQuantity" required>
                <select name="car_condition" id="editConditionDropdown" required>
                </select>
                <textarea name="description" id="editDescription" placeholder="Description"></textarea>
                <input type="file" name="car_img" accept="image/png, image/jpg, image/jpeg">
                <input type="submit" value="Update" name="update_car">
            </form>
        </div>
    </div>
</div>