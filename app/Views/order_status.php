<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order Status</title>
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
</head>

<body>
<header class="">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <p class="navbar-brand fw-bolder fs-2 m-0"><?= esc($restaurantName) ?></p>
            <p class="navbar-brand fs-5 m-0"><?= 'Table ' . esc($tableNumber)?></p>
        </div>
    </nav>
</header>

<main>
    <div class="container" style="margin-top: 30px;">
        <div class="row">
            <div class="col-md-6">
                <h2 class="pb-3">Order #<?= esc($order['OrderNumber']) ?></h2>
                <p><strong>Customer Name:</strong> <?= esc($order['CustomerName']) ?></p>
                <p><strong>Order Time:</strong> <?= esc($order['OrderTime']) ?></p>
                <p><strong>Status:</strong> <?= esc($order['Status']) ?></p>
            </div>
        </div>
    </div>

    <!--  Order Details  -->
    <div class="container">
        <table class="table">
            <thead>
            <tr>
                <th>Dish Name</th>
                <th>Customisations</th>
                <th>Amount</th>
                <th>Price</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($detailedOrder as $item): ?>
                <tr>
                    <td><?= esc($item['DishName']) ?></td>
                    <td>
                        <?php foreach ($item['CustomisationOptions'] as $option): ?>
                            <div><?= esc($option['OptionName']) ?>: <?= esc($option['ValueName']) ?> </div>
                        <?php endforeach; ?>
                    </td>
                    <td><?= esc($item['Quantity']) ?></td>
                    <td>A$<?= esc($item['UnitPrice']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <strong>Note for order:</strong>
        <p><?= esc($order['Comment']) ?></p>
        <p><strong>Total Price: A$<?= esc($order['TotalPrice']) ?></strong></p>

        <a href="<?= base_url(); ?>ordering?restaurantID=<?= esc($restaurantID) ?>&tableNumber=<?= esc($tableNumber) ?>">
            <button type="button" class="btn btn-primary mb-3">Order Again</button>
        </a>
    </div>
</main>
<footer></footer>