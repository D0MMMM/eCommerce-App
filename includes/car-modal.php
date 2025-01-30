<div id="carModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Car Details</h2>
            <button class="close" id="closeModal" style="height: 15px; width: 15px"><i class="fa-solid fa-x"></i></button>
        </div>
        <div class="modal-body">
            <p id="modalDetails">
                <img id="carImage" style="margin-left: 4.5em; width: 30rem; height: 20rem" alt="Car Image"><br>
                <strong style="margin-left: 2em; margin-right: 1em; text-transform: uppercase">BRAND:</strong> <span id="carMake"></span><br>
                <strong style="margin-left: 2em; margin-right: 1em; text-transform: uppercase">Model:</strong> <span id="carModel"></span><br>
                <strong style="margin-left: 2em; margin-right: 2em; text-transform: uppercase">Year:</strong> <span id="carYear"></span><br>
                <strong style="margin-left: 2em; margin-right: 1em; text-transform: uppercase">Amount:</strong> <span id="carPrice"></span> PHP<br>
                <strong style="margin-left: 2em; margin-right: 1em; text-transform: uppercase">Condition:</strong> <span id="carCondition"></span><br>
                <strong style="margin-left: 2em; margin-right: 1em; text-transform: uppercase">About car:</strong> <span id="carDescription"></span>
            </p>
        </div>
        <div class="add-to-cart-btn">
            <form id="add-to-cart-form">
                <input type="hidden" name="car_id" id="carIdInput">
                <button type="submit" class="add-to-cart-btn">
                    Add to Cart <i class="fa-solid fa-cart-shopping"></i>
                </button>
            </form>
        </div>
    </div>
</div>