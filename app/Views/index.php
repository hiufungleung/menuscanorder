<?= $this->extend('template') ?>
<?= $this->section('content') ?>
<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-6">
                <h1 class="display-4">Manage your restaurant easily</h1>
                <p class="lead">Simplify everything in your restaurant with this platform.</p>
                <a href="#" class="btn btn-primary btn-lg mb-3 mb-lg-0">Get Started</a>
            </div>
            <div class="col-lg-6">
                <img src="<?= base_url(); ?>cover.webp" alt="ResumeBuilder Screenshot" class="img-fluid rounded shadow">
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-4">Key Features</h2>
        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Digital Menu Creation</h4>
                        <p class="card-text">It allows businesses to easily create and manage a digital menu
                            with categories, items, and pricing. Customised options
                            are available for customers to satisfy their preferences.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">QR Code Generation</h4>
                        <p class="card-text">Automatically generates unique QR codes for each table,
                            facilitating easy access to the menu by guests.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Seamless Ordering</h4>
                        <p class="card-text">Guests can scan the QR code at their table to view the menu and
                            place orders directly from their smartphones. Guests are
                            able to customise their beverages and food (e.g. allergy considerations, taste
                            preferences) if applicable. Before the
                            order is sent, guests can be informed of the final price of all the items they order
                            and extra prices for the customised
                            options they choose, promoting transparency and trust.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Order Management</h4>
                        <p class="card-text">Staff can view and manage orders in real-time, ensuring a smooth
                            dining experience for guests. Real-time order tracking
                            allows staff to monitor order statuses from preparation to delivery, improving the
                            service.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>