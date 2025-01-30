$(document).ready(function() {
    const ordersTable = $('#orders-table').DataTable({
        "order": [[6, "desc"]], // Sort by date descending
        // "pageLength": 4,
        // "lengthChange": false,
        "paging" : false
    });

    // Status filter
    $('#statusFilter').on('change', function() {
        const status = this.value;
        if (status) {
            ordersTable.column(5).search('^' + status + '$', true, false).draw();
        } else {
            ordersTable.column(5).search('').draw();
        }
    });

    // Status update handler
    $('.status-select').on('change', function() {
        const orderId = $(this).data('order-id');
        const newStatus = $(this).val();
        const row = $(this).closest('tr');

        $.post('../backend/update_order_status.php', {
            order_id: orderId,
            status: newStatus
        })
        .done(function(response) {
            Swal.fire({
                icon: 'success',
                title: 'Status Updated',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
            // Update the status cell in the table
            row.find('.order-status').text(newStatus);
        })
        .fail(function() {
            Swal.fire({
                icon: 'error',
                title: 'Update Failed',
                text: 'Please try again'
            });
        });
    });

    // View order details in modal
    $('.view-btn').on('click', function() {
        const orderId = $(this).data('order-id');
        $.get(`../backend/get_order_details.php?id=${orderId}`, function(data) {
            $('#orderDetails').html(data);
            $('#orderModal').show();
        });
    });

    // Close modal
    $('.close').on('click', function() {
        $('#orderModal').hide();
    });

    // Close modal when clicking outside of it
    $(window).on('click', function(event) {
        if (event.target.id === 'orderModal') {
            $('#orderModal').hide();
        }
    });
});