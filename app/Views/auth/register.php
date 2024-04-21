<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <!-- Include Bootstrap CSS from a CDN -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.min.css">
    <!-- Optional: Link to your custom CSS file, make sure it comes after Bootstrap to override when necessary -->
    <!-- <link rel="stylesheet" href="/path/to/your/css/styles.css"> -->
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <form id="registrationForm" class="p-4">
                            <h2 class="text-center mb-4">Register</h2>
                            <?= csrf_field() ?>
                            <div class="form-group">
                                <label for="username">Name:</label>
                                <input type="text" name="username" id="username" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email:</label>
                                <input type="email" name="email" id="email" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Password:</label>
                                <div class="input-group">
                                    <input type="password" name="password" id="password" class="form-control" required>
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="password_confirm">Confirm Password:</label>
                                <div class="input-group">
                                    <input type="password" name="password_confirm" id="password_confirm" class="form-control" required>
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Register</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Include SweetAlert2 from a CDN in your view's <head> section -->
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            $('#togglePassword').click(function() {
                let type = $('#password').attr('type') === 'password' ? 'text' : 'password';
                $('#password').attr('type', type);
                $(this).find('.bi').toggleClass('bi-eye bi-eye-slash');
            });

            $('#toggleConfirmPassword').click(function() {
                let type = $('#password_confirm').attr('type') === 'password' ? 'text' : 'password';
                $('#password_confirm').attr('type', type);
                $(this).find('.bi').toggleClass('bi-eye bi-eye-slash');
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            $('#registrationForm').submit(function(e) {
                e.preventDefault(); // Prevent the default form submission

                // Get the values from the password and confirm password fields
                var password = $('#password').val();
                var confirmPassword = $('#password_confirm').val();

                // Check if passwords match
                if (password !== confirmPassword) {
                    // If passwords do not match, show an error alert and stop the form submission
                    Swal.fire({
                        title: 'Error!',
                        text: 'Passwords do not match.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    return; // Stop the form submission
                }

                // If passwords match, proceed with the AJAX call
                var formData = {
                    username: $('#username').val(),
                    email: $('#email').val(),
                    password: password, // It's safe to use the password variable here
                };

                $.ajax({
                    type: "POST",
                    url: "/api/register", // Adjust the URL as needed
                    contentType: "application/json",
                    data: JSON.stringify(formData),
                    success: function(response) {
                        // Display success message from server response
                        Swal.fire({
                            title: 'Success!',
                            text: response.message, // Use the message from the response
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.value) {
                                window.location.href = '/'; // Redirect to the login page
                            }
                        });
                    },
                    error: function(xhr) {
                        // Default error message
                        var errorMessage = 'An unexpected error occurred. Please try again.';

                        // Attempt to parse the JSON response
                        try {
                            var responseJson = JSON.parse(xhr.responseText);
                            if (responseJson && responseJson.messages && responseJson.messages.error) {
                                errorMessage = responseJson.messages.error; // Extract the error message
                            }
                        } catch (e) {
                            console.error("Error parsing server response: ", e);
                        }

                        // Display the error message using SweetAlert2
                        Swal.fire({
                            title: 'Error!',
                            text: errorMessage,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            });
        });
    </script>
</body>

</html>