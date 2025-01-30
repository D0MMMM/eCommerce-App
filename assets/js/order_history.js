window.addEventListener("scroll", function() {
    var header = document.querySelector("header");
    header.classList.toggle("sticky", window.scrollY > 0);
});

document.addEventListener('DOMContentLoaded', function() {
    const filterStatus = document.getElementById('filter-status');
    const sortDate = document.getElementById('sort-date');
    const orderTable = document.getElementById('order-table').getElementsByTagName('tbody')[0];

    filterStatus.addEventListener('change', filterOrders);
    sortDate.addEventListener('change', sortOrders);

    function filterOrders() {
        const status = filterStatus.value;
        const rows = orderTable.getElementsByTagName('tr');
        for (let row of rows) {
            if (status === 'all' || row.getAttribute('data-status') === status) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    }

    function sortOrders() {
        const rows = Array.from(orderTable.getElementsByTagName('tr'));
        const sortOrder = sortDate.value;
        rows.sort((a, b) => {
            const dateA = new Date(a.cells[4].textContent);
            const dateB = new Date(b.cells[4].textContent);
            return sortOrder === 'asc' ? dateA - dateB : dateB - dateA;
        });
        rows.forEach(row => orderTable.appendChild(row));
    }
});