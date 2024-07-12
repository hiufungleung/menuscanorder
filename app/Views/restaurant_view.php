<?= $this->extend('template') ?>
<?= $this->section('content') ?>

    <section class="py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Overview of <?= esc($restaurant['Name']) ?></h1>
            </div>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success">
                    <?= session()->getFlashdata('success'); ?>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger">
                    <?= session()->getFlashdata('error'); ?>
                </div>
            <?php endif; ?>
            <div class="alert alert-danger fade" role="alert" id="timeLimitedAlert" style="position: fixed; top: 20px; left: 50%; transform: translateX(-50%); z-index: 9999; display: none;">
                This is a top alert!
            </div>

            <div id="educationAlert" class="alert alert-dismissible fade show mt-3 position-fixed top-0 start-50 translate-middle-x" role="alert"
                 style="display: none; z-index: 9999;">
                <span id="educationAlertMessage"></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

            <section>
                <div class="container">
                    <!-- Restaurant Overview -->
                    <div class="row">
                        <div class="col-md-6">
                            <h2>Restaurant Information</h2>
                            <div class="card">
                                <div class="card-body">
                                    <p><strong>Email:</strong> <?= esc($restaurant['Email']) ?></p>
                                    <p><strong>Phone:</strong> <?= esc($restaurant['Phone']) ?></p>
                                    <p><strong>Address:</strong> <?= esc($restaurant['Address']) ?></p>
                                </div>
                            </div>
                        </div>
                        <?php if ($restaurant): ?>
                            <div class="col-md-6">
                                <h2>Shortcut</h2>
                                <div class="card">
                                    <div class="card-body">
                                        <strong>Order Management</strong><p></p>
                                        <a href="<?= base_url(); ?>restaurant/<?= esc($restaurantID) ?>/ordermanagement">
                                            <button type="button" class="btn btn-primary mb-3">Manage Orders</button>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!--Load Dish Category Info-->
                    <div class="row">
                        <h2>Dish Category</h2>
                        <table class="table">
                            <thead>
                            <tr>
                                <th>Category Name</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody id="dishCategoryTable">
                            <?php foreach ($dishCategories as $dishCategory): ?>
                                <tr>
                                    <td class="dishCategoryNameOnTable"><?= esc($dishCategory['CategoryName']) ?></td>
                                    <td>
                                        <input type="hidden" class="row-id"
                                               value="<?= esc($dishCategory['CategoryID']) ?>">
                                        <input type="hidden" class="row-foreign-id" value="<?= esc($dishCategory['RestaurantID']) ?>">
                                        <button type="button" class="btn btn-primary btn-sm edit-education openModal"
                                                data-bs-toggle="modal" data-bs-target="#educationModal" data-type="dishCategory"
                                                data-mode="edit" data-value="<?= esc($dishCategory['CategoryID']) ?>"
                                                data-foreign-value="<?= esc($dishCategory['RestaurantID']) ?>">Edit</button>
                                        <button type="button" class="btn btn-danger btn-sm delete-education" data-type="dishCategory"
                                                data-value="<?= esc($dishCategory['CategoryID']) ?>">Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-primary mb-3 openModal" data-bs-toggle="modal"
                                data-bs-target="#educationModal" id="addDishCategoryBtn"
                                data-type="dishCategory" data-mode="add" data-value=""
                                data-foreign-value="<?= esc($restaurantID) ?>">Add Dish Category</button>
                    </div>

                    <!--Load Customisation Option Info-->
                    <div class="row">
                        <h2>Customisation Options</h2>
                        <table class="table">
                            <thead>
                            <tr>
                                <th>Option</th>
                                <th>Available Values (Extra Price)</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody id="customisationOptionTable">
                            <?php foreach ($customisationOptions as $customisationOption): ?>
                                <tr>
                                    <td class="optionNameOnTable"><?= esc($customisationOption['OptionName']) ?></td>
                                    <td class="valuesPricesOnTable"><?= esc($customisationOption['ValuesPrices']) ?></td>
                                    <td>
                                        <input type="hidden" class="row-id"
                                               value="<?= esc($customisationOption['OptionID']) ?>">
                                        <input type="hidden" class="user-id" value="<?= esc($customisationOption['RestaurantID']) ?>">
                                        <button type="button" class="btn btn-primary btn-sm edit-education openModal"
                                                data-bs-toggle="modal" data-bs-target="#educationModal"
                                                data-type="customisationOption" data-mode="edit"
                                                data-value="<?= esc($customisationOption['OptionID']) ?>"
                                                data-foreign-value="<?= esc($customisationOption['RestaurantID']) ?>">Edit</button>
                                        <button type="button" class="btn btn-danger btn-sm delete-education"
                                                data-type="customisationOption"
                                                data-value="<?= esc($customisationOption['OptionID']) ?>">Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-primary mb-3 openModal" data-bs-toggle="modal"
                                data-bs-target="#educationModal" id="addCustomisationOptionBtn"
                                data-type="customisationOption" data-mode="add" data-value=""
                                data-foreign-value="<?= esc($restaurantID) ?>">
                                Add Customisation Option</button>
                    </div>

                    <!--Load Dish Info-->
                    <div class="row">
                        <h2>Dish</h2>
                        <table class="table">
                            <thead>
                            <tr>
                                <th>In Category</th>
                                <th>Dish Name</th>
                                <th>Description</th>
                                <th>Base Price</th>
                                <th>Available Customisation</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody id="dishTable">
                            <?php foreach ($dishes as $dish): ?>
                                <tr>
                                    <td class="categoryNameOnTable"><?= esc($dish['CategoryName']) ?></td>
                                    <td class="dishNameOnTable"><?= esc($dish['DishName']) ?></td>
                                    <td class="descriptionOnTable"><?= truncateText(esc($dish['Description'])) ?></td>
                                    <td class="basePriceOnTable">A$<?= esc($dish['BasePrice']) ?></td>
                                    <td class="optionNameOnTable"><?= esc($dish['OptionName']) ?></td>
                                    <td>
                                        <input type="hidden" class="row-id" value="<?= esc($dish['DishID']) ?>">
                                        <input type="hidden" class="row-foreign-id" value="<?= esc($dish['CategoryID']) ?>">
                                        <button type="button" class="btn btn-primary btn-sm edit-education openModal"
                                                data-bs-toggle="modal" data-bs-target="#educationModal"
                                                data-type="dish" data-mode="edit" data-value="<?= esc($dish['DishID']) ?>"
                                                data-foreign-value="<?= esc($dish['CategoryID']) ?>">Edit</button>
                                        <button type="button" class="btn btn-danger btn-sm delete-education"
                                                data-type="dish" data-value="<?= esc($dish['DishID']) ?>">Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-primary mb-3 openModal" data-bs-toggle="modal"
                                data-bs-target="#educationModal" id="addDishBtn" data-type="dish" data-mode="add"
                                data-value=""
                                data-foreign-value="<?= esc($restaurantID) ?>">Add Dish</button>
                    </div>

                    <!--Load Table Info-->
                    <div class="row">
                        <h2>Table</h2>
                        <table class="table">
                            <thead>
                            <tr>
                                <th>Table Number</th>
                                <th>Capacity</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody id="tableTable">
                            <?php foreach ($tables as $table): ?>
                                <tr>
                                    <td class="tableNumberOnTable"><?= esc($table['TableNumber']) ?></td>
                                    <td class="tableCapacityOnTable"><?= esc($table['Capacity']) ?></td>
                                    <td>
                                        <input type="hidden" class="row-id"
                                               value="<?= esc($table['TableID']) ?>">
                                        <input type="hidden" class="row-foreign-id" value="<?= esc($table['RestaurantID']) ?>">
                                        <button type="button" class="btn btn-primary btn-sm edit-education openModal"
                                                data-bs-toggle="modal" data-bs-target="#educationModal" data-type="qrcode"
                                                data-mode="qrcode" data-value="<?= esc($table['TableNumber']) ?>"
                                                data-foreign-value="<?= esc($table['RestaurantID']) ?>">QR Code</button>
                                        <button type="button" class="btn btn-primary btn-sm edit-education openModal"
                                                data-bs-toggle="modal" data-bs-target="#educationModal" data-type="table"
                                                data-mode="edit" data-value="<?= esc($table['TableID']) ?>"
                                                data-foreign-value="<?= esc($table['RestaurantID']) ?>">Edit</button>
                                        <button type="button" class="btn btn-danger btn-sm delete-education" data-type="table"
                                                data-value="<?= esc($table['TableID']) ?>">Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-primary mb-3 openModal" data-bs-toggle="modal"
                                data-bs-target="#educationModal" id="addDishCategoryBtn"
                                data-type="table" data-mode="add" data-value=""
                                data-foreign-value="<?= esc($restaurantID) ?>">Add Table</button>
                    </div>
                </div>
            </section>

        </div>
    </section>

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
                    <button type="button" class="btn btn-primary" id="saveBtn">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!--Template of a row of dish Category-->
    <template id="dishCategoryRowTemplate">
        <tr>
            <td class="dishCategoryNameOnTable"></td>
            <td>
                <input type="hidden" class="row-id" value="">
                <input type="hidden" class="row-foreign-id" value="">
                <button type="button" class="btn btn-primary btn-sm edit-education openModal"
                        data-bs-toggle="modal" data-bs-target="#educationModal" data-type="dishCategory"
                        data-mode="edit" data-value="" data-foreign-value="">Edit</button>
                <button type="button" class="btn btn-danger btn-sm delete-education"
                        data-type="dishCategory" data-value="">Delete</button>
            </td>
        </tr>
    </template>

    <!--Template of a row of customisation option-->
    <template id="customisationOptionRowTemplate">
        <tr>
            <td class="optionNameOnTable"></td>
            <td class="valuesPricesOnTable"></td>
            <td>
                <input type="hidden" class="row-id" value="">
                <input type="hidden" class="row-foreign-id" value="">
                <button type="button" class="btn btn-primary btn-sm edit-education openModal"
                        data-bs-toggle="modal" data-bs-target="#educationModal"
                        data-type="customisationOption" data-mode="edit" data-value=""
                        data-foreign-value="">Edit</button>
                <button type="button" class="btn btn-danger btn-sm delete-education"
                        data-type="customisationOption" data-value="">Delete</button>
            </td>
        </tr>
    </template>

    <!--Template of a row of dish-->
    <template id="dishRowTemplate">
        <tr>
            <td class="categoryNameOnTable"></td>
            <td class="dishNameOnTable"></td>
            <td class="descriptionOnTable"></td>
            <td class="basePriceOnTable"></td>
            <td class="optionNameOnTable"></td>
            <td>
                <input type="hidden" class="row-id" value="">
                <input type="hidden" class="row-foreign-id" value="">
                <button type="button" class="btn btn-primary btn-sm edit-education openModal"
                        data-bs-toggle="modal" data-bs-target="#educationModal" data-type="dish"
                        data-mode="edit" data-value="" data-foreign-value="">Edit</button>
                <button type="button" class="btn btn-danger btn-sm delete-education"
                        data-type="dish" data-value="">Delete</button>
            </td>
        </tr>
    </template>

    <!--Template of a row of table-->
    <template id="tableRowTemplate">
        <tr>
            <td class="tableNumberOnTable"></td>
            <td class="tableCapacityOnTable"></td>
            <td>
                <input type="hidden" class="row-id"
                       value="">
                <input type="hidden" class="row-foreign-id" value="">
                <button type="button" class="btn btn-primary btn-sm edit-education openModal"
                        data-bs-toggle="modal" data-bs-target="#educationModal" data-type="qrcode"
                        data-mode="qrcode" data-value=""
                        data-foreign-value="">QR Code</button>
                <button type="button" class="btn btn-primary btn-sm edit-education openModal"
                        data-bs-toggle="modal" data-bs-target="#educationModal" data-type="table"
                        data-mode="edit" data-value=""
                        data-foreign-value="">Edit</button>
                <button type="button" class="btn btn-danger btn-sm delete-education" data-type="table"
                        data-value="">Delete</button>
            </td>
        </tr>
    </template>

    <script>
        var restaurantID = <?= esc($restaurant['RestaurantID']) ?>;
        var baseURL = '<?= base_url(); ?>';
    </script>
    <script src="<?= base_url(); ?>js/script.js"></script>

<?= $this->endSection() ?>


<?php
/**
 * Truncate a text if too long.
 * @param string $text The text to be processed
 * @param int $maxChars The maximum of the result.
 * @return string Original text if the length is below $maxChars, truncated if above $maxChars.
 */
function truncateText(string $text, int $maxChars = 30): string
{
    if (strlen($text) > $maxChars) {
        $text = substr($text, 0, $maxChars + 1);
        if ($last_space = strrpos($text, ' ')) {
            $text = substr($text, 0, $last_space);
        } else {
            $text = substr($text, 0, $maxChars);
        }
        $text .= '...';
    }
    return $text;
}
