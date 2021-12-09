<h2>Summary</h2>

<div class="row">
    <div class="col">
        <p><strong>Username</strong></p>
        <p><?php echo $user->username; ?></p>
    </div>
    <div class="col">
        <p><strong>Email</strong></p>
        <p><?php echo $user->email; ?></p>
    </div>
</div>

<table class="table table-striped">
    <thead>
    <th>#</th>
    <th>Name</th>
    <th>Description</th>
    <th># in cart</th>
    </thead>
    <?php
    /**
     * Alle Räume durchgehen und eine List ausgeben.
     */
    foreach ($cartContent as $equipment): ?>

        <tr>
            <td><?php
                echo $equipment->id; ?></td>
            <td>
                <a href="<?php
                echo BASE_URL; ?>/equipments/<?php
                echo $equipment->id; ?>/show"><?php
                    echo $equipment->name; ?>
                </a>
            </td>
            <td><?php
                echo $equipment->description; ?></td>
            <td><?php
                echo $equipment->count; ?></td>
        </tr>

    <?php
    endforeach; ?>
</table>

<?php if (\App\Models\User::isLoggedIn()): ?>
    <a href="<?php echo BASE_URL; ?>/checkout/finish" class="btn btn-primary">Finish</a>
    <a href="<?php echo BASE_URL; ?>/cart" class="btn btn-danger">Abort</a>
<?php endif; ?>
