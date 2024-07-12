<?= $this->extend('template') ?>
<?= $this->section('content') ?>

    <section class="py-5">

        <section class="py-5">
            <div class="container">
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success">
                        <?= session()->getFlashdata('success') ?>
                    </div>
                <?php elseif (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger">
                        <?= session()->getFlashdata('error') ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <h2 class="text-center mb-4">Sign up</h2>
                    <form id="signupForm" method="post" action="<?= base_url('signup') ?>">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="Name" value="" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="Email" value="" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="phone" name="Phone" value="" required>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" class="form-control" id="address" name="Address" value="" required >
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="Password"
                                   placeholder="Enter your password."
                                   value="" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirm-password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm-password" name="confirmPassword"
                                   placeholder="Confirm your password." value="" required>
                            <div id="passwordError" class="form-text text-danger" style="display: none;">Passwords do not match.</div>
                        </div>
                        <button type="submit"
                                class="btn btn-primary">Sign up your account</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <script>
        $(document).ready(function() {
            $('#signupForm').on('submit', function(event) {
                var password = $('#password').val();
                var confirmPassword = $('#confirm-password').val();

                if (password !== confirmPassword) {
                    $('#passwordError').show();  // Show error message
                    event.preventDefault();  // Prevent form submission
                } else {
                    $('#passwordError').hide();  // Hide error message
                }
            });
        });
    </script>
<?= $this->endSection() ?>