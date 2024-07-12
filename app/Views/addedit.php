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

    <!--    Load the restaurant information form.   -->
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <h2 class="text-center mb-4"><?= isset($restaurant) ? 'Edit Restaurant' : 'Add Restaurant' ?></h2>
                <form method="post"
                    action="<?= base_url('admin/addedit' . (isset($restaurant) ? '/' . $restaurant['RestaurantID'] : '')) ?>">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="Name"
                            value="<?= isset($restaurant) ? esc($restaurant['Name']) : '' ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="Email"
                            value="<?= isset($restaurant) ? esc($restaurant['Email']) : '' ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="phone" name="Phone"
                               value="<?= isset($restaurant) ? esc($restaurant['Phone']) : '' ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <input type="text" class="form-control" id="address" name="Address"
                               value="<?= isset($restaurant) ? esc($restaurant['Address']) : '' ?>" required >
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="Password"
                               placeholder="<?= isset($restaurant) ? 'Not Change.' : 'Required'?>"
                            value="" <?= isset($restaurant) ? '' : 'required'?>>
                    </div>
                    <div class="mb-3">
                        <label for="isAdmin" class="form-label">Is this account is an administrator?</label>
                        <select class="form-control" id="isAdmin" name="isAdmin" required>
                            <option value="0" <?= isset($restaurant) && $restaurant['isAdmin'] === '0' ? 'selected' : '' ?>>No</option>
                            <option value="1" <?= isset($restaurant) && $restaurant['isAdmin'] === '1' ? 'selected' : '' ?>>Yes</option>
                        </select>
                    </div>
                    <button type="submit"
                        class="btn btn-primary"><?= isset($restaurant) ? 'Update Restaurant' : 'Add Restaurant' ?></button>
                </form>
            </div>
        </div>
    </div>
</section>

<?= $this->endSection() ?>