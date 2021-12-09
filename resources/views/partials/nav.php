<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="#"><?php echo \Core\Config::get('app.app-name'); ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/home">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/rooms">Räume</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/room-features">Raum Features</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/equipments">Equipments</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/profile">Profile</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>/cart">Cart (<?php echo \App\Services\CartService::getCount(); ?>)</a>
                </li>
            </ul>
            <?php
            /**
             * Ist ein*e User*in eingeloggt, so zeigen wir den Username an und einen Logout Button. Andernfalls einen
             * Login Button.
             */
            if (\App\Models\User::isLoggedIn()):?>
                <div class="d-flex">
                    Eingeloggt: <?php echo \App\Models\User::getLoggedIn()->username; ?>
                    (<a href="<?php echo BASE_URL ?>/logout">Logout</a>)
                </div>
            <?php else: ?>
                <a class="btn btn-primary" href="<?php echo BASE_URL; ?>/login">Login</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
