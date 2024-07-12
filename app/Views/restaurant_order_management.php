<?= $this->extend('template') ?>
<?= $this->section('content') ?>

<section class="py-5">
    <div class="container">
        <div class="row mb-4 d-flex justify-content-center">
            <div id="educationAlert" class="alert alert-dismissible fade show mt-3" role="alert"
                 style="display: none; position: absolute; width: fit-content; top: 50px;">
                <span id="educationAlertMessage"></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><?= $restaurant['Name'] ?> - Order Management</h2>
            <a href="<?= base_url(); ?>restaurant/<?= $restaurant['RestaurantID'] ?>">
                <button type="button" class="btn btn-primary mb-3">Back to Restaurant</button>
            </a>
        </div>

        <div id="educationAlert" class="alert alert-dismissible fade show mt-3 position-fixed top-0 start-50 translate-middle-x" role="alert"
             style="display: none; z-index: 9999;">
            <span id="educationAlertMessage"></span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        <table class="table table-striped">
            <thead>
            <tr>
                <th>No.</th>
                <th>Time</th>
                <th>Table No.</th>
                <th>Customer</th>
                <th>Note</th>
                <th>Price (A$)</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td>#<?= esc($order['OrderNumber']) ?></td>
                    <td><?= esc($order['OrderTime']) ?></td>
                    <td><?= esc($order['TableNumber']) ?></td>
                    <td><?= esc($order['CustomerName']) ?></td>
                    <td><?= esc($order['Comment']) ?></td>
                    <td><?= esc($order['TotalPrice']) ?></td>
                    <td><?= esc($order['Status']) ?></td>
                    <td>
                        <button type="button" class="btn btn-sm btn-primary me-2 view-order-details openModal"
                                data-bs-toggle="modal" data-bs-target="#educationModal"
                                data-type="view-order" data-mode=""
                                data-value="<?= esc($order['OrderID']) ?>"
                                data-foreign-value="<?= esc($order['RestaurantID']) ?>"
                                data-order-status="<?= esc($order['Status']) ?>">
                            <i class="bi bi-eye-fill"></i></button>

                        <button type="button" class="btn btn-sm btn-light me-2 finalise-order"
                            data-value="<?= esc($order['OrderID']) ?>" data-type="finalise-order"
                                data-order-status="<?= esc($order['Status']) ?>">
                        <i class="bi bi-check-circle-fill"></i></button>


                        <button type="button" class="btn btn-sm btn-light me-2 cancel-order"
                                data-value="<?= esc($order['OrderID']) ?>" data-type="cancel-order" data-order-status="<?= esc($order['Status']) ?>">
                            <i class="bi bi-dash-circle-fill"></i></button>
                    </td>
                    <!-- Add more user details as needed -->
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>


    <!-- Add Dish Category Modal -->
    <div class="modal fade" id="educationModal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="modalForm"></form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveBtn" data-bs-dismiss="modal">Confirm</button>
                </div>
            </div>
        </div>
    </div>
</section>


<script>
    var restaurantID = <?= esc($restaurant['RestaurantID']) ?>;
    var baseURL = '<?= base_url(); ?>';
</script>
<script src="<?= base_url(); ?>js/script.js"></script>

<?= $this->endSection() ?>
