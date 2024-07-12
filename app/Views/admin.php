<?= $this->extend('template') ?>
<?= $this->section('content') ?>

    <section class="py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Restaurant Management - Admin Panel</h2>
            </div>

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

            <div class="row mb-4">
                <div class="col-md-6 mb-3 mb-lg-0">
                    <form method="get" action="<?= base_url('admin/'); ?>">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Enter your search..." name="search">
                            <button class="btn btn-primary" type="submit">Search</button>
                        </div>
                    </form>
                </div>
                <div class="col-md-6 text-md-end">
                    <a class="btn btn-primary" href="<?= base_url('admin/addedit'); ?>">Add Restaurant</a>
                </div>
            </div>

            <table class="table table-striped">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>isAdmin</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <!-- Load all the restaurants' info-->
                <?php foreach ($restaurants as $restaurant): ?>
                    <tr>
                        <td><?= esc($restaurant['Name']) ?></td>
                        <td><?= esc($restaurant['Email']) ?></td>
                        <td><?= esc($restaurant['Phone']) ?></td>
                        <td><?= esc($restaurant['Address']) ?></td>
                        <td><?= esc($restaurant['isAdmin'] ? 'True' : 'False') ?></td>
                        <td>
                            <a class="btn btn-sm btn-info me-2"
                               href="<?= base_url('restaurant/' . $restaurant['RestaurantID']); ?>"><i
                                        class="bi bi-eye-fill"></i></a>
                            <a class="btn btn-sm btn-primary me-2"
                               href="<?= base_url('admin/addedit/' . $restaurant['RestaurantID']); ?>"><i
                                        class="bi bi-pencil-fill"></i></a>
                            <a class="btn btn-sm btn-warning me-2"
                               href="<?= base_url('admin/delete/' . $restaurant['RestaurantID']) ?>"
                               onclick="return confirm('Are you sure you want to delete this restaurant?')"><i
                                        class="bi bi-dash-circle-fill"></i></a>
                        </td>

                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

<?= $this->endSection() ?>