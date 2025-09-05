<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MenuScanOrder</title>
    <!-- Icon https://www.flaticon.com/free-icon/order_11120625 -->
    <!-- <a href="https://www.flaticon.com/free-icons/scanning" title="scanning icons">Scanning icons created by Kreev Studio - Flaticon</a>-->
    <link rel="icon" type="image/x-icon" href="<?= base_url(); ?>order.png">
    <!-- This is the main stylesheet for Bootstrap. It includes all the CSS necessary for Bootstrap's components and utilities to work. -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Include Bootstrap Icons -->
    <!-- This link imports the Bootstrap Icons library, which provides a wide range of SVG icons for use in your projects. -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= base_url(); ?>css/style_ordering.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>
    <style>
        aside {
            overflow-y: auto;
            width: 30vw;
            height: 100%;
        }
        @media screen and (min-width: 1000px) {
            aside {
                width: 300px;
            }
        }
    </style>
</head>

<body>

<header class="">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <p class="navbar-brand fw-bolder fs-2 m-0"><?= esc($restaurantName)?></p>
            <p class="navbar-brand fs-5 m-0"><?= 'Table ' . esc($tableNumber) ?></p>
        </div>
    </nav>
</header>


<main>
    <div class="d-flex flex-row justify-content-start" style="height: 100%;">
        <aside class="d-flex flex-column bd-sidebar flex-shrink-0 p-2 text-white bg-dark">
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item nav-link text-white fs-4">Category</li>
                <hr>
                <!--sidebar info-->
                <?php foreach ($dishCategories as $dishCategory): ?>
                    <li class="nav-item">
                        <a href="#category-id-<?= esc($dishCategory['CategoryID']) ?>" class="nav-link text-white" aria-current="page"><?= esc($dishCategory['CategoryName']) ?></a>
                    </li>
                <?php endforeach; ?>
            <hr>
        </aside>

        <div class="" style="overflow-y: auto; width: 100%; padding: 20px; scroll-behavior: smooth;">
            <div id="educationAlert" class="alert alert-dismissible fade show mt-3 position-fixed top-0 start-50 translate-middle-x" role="alert"
                 style="display: none; z-index: 9999;">
                <span id="educationAlertMessage"></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <!--Load all dishes category by category-->
            <?php foreach ($categorisedDishes as $categoryName => $dishes): ?>
                <div id="category-id-<?= esc($dishes[0]['CategoryID']) ?>" class="row">
                    <h3><?= htmlspecialchars($categoryName); ?></h3>
                    <table class="table">
                        <thead>
                        <tr>
                            <th style="visibility: ">Dish Name Dish Name</th>
                            <th></th><th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($dishes as $dish): ?>
                            <tr>
                                <td><?= htmlspecialchars($dish['DishName']); ?></td>
                                <td>A$<?= htmlspecialchars($dish['BasePrice']); ?>+</td>
                                <td><button type="button" class="btn btn-primary btn-sm edit-education openModal"
                                            data-bs-toggle="modal" data-bs-target="#educationModal"
                                            data-type="add-dish" data-mode=""
                                            data-value="<?= esc($dish['DishID']) ?>"
                                            data-foreign-value="<?= esc($dish['CategoryID'])?>">Add</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <hr>
                </div>
            <?php endforeach; ?>
    </div>
</main>

<footer class="text-light py-3 navbar navbar-expand-lg navbar-dark bg-dark">
    <!--  Shopping Cart, Price Display and Send Order button  -->
    <div class="container col-md-6 text-md-end px-4">
            <button type="button" class="btn btn-dark btn-m edit-education openModal py-0 px-2"
                    data-bs-toggle="modal" data-bs-target="#educationModal" data-type="shopping-cart"
                    data-mode="" data-value=""
                    data-foreign-value="">
                <i class="bi bi-cart3 fs-1 m-0 p-0"></i>
            </button>

        <p class="fs-5 lh-0 p-0 m-0 total-price" id="footer-price">Total Price: A$0</p>
        <button type="button" class="btn btn-primary btn-m edit-education openModal"
                data-bs-toggle="modal" data-bs-target="#educationModal" data-type="send-order"
                data-mode="" data-value=""
                data-foreign-value="">Send Order</button>
    </div>
</footer>

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
                <h6 id="modal-price" class="total-price">Total Price: A$0</h6>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveBtn" >Add to Order</button>
            </div>
        </div>
    </div>
</div>
<!-- This script includes all of Bootstrap's JavaScript-based components and behaviors, such as modal windows, dropdowns, and tooltips.  -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>



<script>
    var restaurantID = <?= esc($restaurantID) ?>;
    var tableID = <?= esc($tableID) ?>;
    var baseURL = '<?= base_url(); ?>';
</script>
<script src="<?= base_url(); ?>js/ordering_script.js"></script>
</body>

</html>