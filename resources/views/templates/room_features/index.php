<h2>
    Room Features
    <?php
    if (\App\Models\User::isLoggedIn()): ?>
        <a href="<?php
        echo BASE_URL; ?>/room-features/create" class="btn btn-primary btn-sm">New</a>
    <?php
    endif; ?>
</h2>

<table class="table table-striped">
    <thead>
    <th>#</th>
    <th>Name</th>
    <th>Description</th>
    <?php
    if (\Core\Middlewares\AuthMiddleware::isAdmin()): ?>
        <th>Actions</th>
    <?php
    endif; ?>
    </thead>
    <?php
    /**
     * Alle Räume durchgehen und eine List ausgeben.
     */
    foreach ($roomFeatures as $roomFeature): ?>

        <tr>
            <td><?php
                echo $roomFeature->id; ?></td>
            <td><?php
                echo $roomFeature->name; ?></td>
            <td><?php
                echo $roomFeature->description; ?></td>
            <?php
            if (\Core\Middlewares\AuthMiddleware::isAdmin()): ?>
                <td>
                    <a href="<?php
                    echo BASE_URL . "/room-features/$roomFeature->id"; ?>" class="btn btn-primary">Edit</a>

                    <a href="<?php
                    echo BASE_URL . "/room-features/$roomFeature->id/delete"; ?>" class="btn btn-danger">Delete</a>
                </td>
            <?php
            endif; ?>
        </tr>

    <?php
    endforeach; ?>
</table>
