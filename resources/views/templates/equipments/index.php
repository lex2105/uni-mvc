<h2>
    Equipment
    <?php
    if (\App\Models\User::isLoggedIn()): ?>
        <a href="<?php
        echo BASE_URL; ?>/equipments/create" class="btn btn-primary btn-sm">New</a>
    <?php
    endif; ?>
</h2>

<table class="table table-striped">
    <thead>
    <th>#</th>
    <th>Name</th>
    <th>Description</th>
    <th>Units</th>
    <th>Type</th>
    <th>Actions</th>
    </thead>
    <?php
    /**
     * Alle Räume durchgehen und eine List ausgeben.
     */
    foreach ($equipments as $equipment): ?>

        <tr>
            <td><?php
                echo $equipment->id; ?></td>
            <td>
                <a href="<?php echo BASE_URL; ?>/equipments/<?php echo $equipment->id; ?>/show"><?php
                    echo $equipment->name; ?>
                </a>
            </td>
            <td><?php
                echo $equipment->description; ?></td>
            <td><?php
                echo $equipment->units; ?></td>
            <td><?php
                echo $equipment->type(); ?></td>
            <td>
                <?php
                if (\Core\Middlewares\AuthMiddleware::isAdmin()): ?>
                    <a href="<?php
                    echo BASE_URL . "/equipments/$equipment->id"; ?>" class="btn btn-primary">Edit</a>

                    <a href="<?php
                    echo BASE_URL . "/equipments/$equipment->id/delete"; ?>" class="btn btn-danger">Delete</a>
                <?php
                endif; ?>

                <?php
                if (\Core\Middlewares\AuthMiddleware::isLoggedIn()): ?>
                    <a href="<?php
                    echo BASE_URL . "/equipments/$equipment->id/add-to-cart"; ?>" class="btn btn-success">Add To Cart</a>
                <?php
                endif; ?>
            </td>
        </tr>

    <?php
    endforeach; ?>
</table>
