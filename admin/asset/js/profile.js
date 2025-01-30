document.getElementById('addUserForm').addEventListener('submit', function(event) {
    event.preventDefault();

    Swal.fire({
      title: 'Are you sure?',
      text: "You are about to add a new user.",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Yes, add it!'
    }).then((result) => {
      if (result.isConfirmed) {
        this.submit(); // Submit the form
      }
    });
  });

  // SweetAlert for Delete User
  document.querySelectorAll('.deleteUserForm').forEach(form => {
    form.addEventListener('submit', function(event) {
      event.preventDefault(); // Prevent default form submission

      Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
      }).then((result) => {
        if (result.isConfirmed) {
          this.submit(); // Submit the form
        }
      });
    });
  });