<h2>
    Rooms
    <?php
    if (\App\Models\User::isLoggedIn()): ?>
        <a href="<?php
        echo BASE_URL; ?>/rooms/create" class="btn btn-primary btn-sm">New</a>
    <?php
    endif; ?>
</h2>

<div class="filters container">
    <form class="form-inline" method="get">
        <div class="row">
            <?php
            /**
             * @todo: CONTINUE HERE!
             * @todo: Pre-select checkboxes when present in $_GET.
             */
            foreach ($roomFeatures as $roomFeature): ?>
                <div class="form-check col">
                    <input type="checkbox" value="<?php
                    echo $roomFeature->id; ?>" class="form-check-input" name="filters[]" id="filters[<?php
                    echo $roomFeature->id; ?>]">
                    <label for="filters[<?php
                    echo $roomFeature->id; ?>]"><?php
                        echo $roomFeature->name; ?></label>
                </div>
            <?php
            endforeach; ?>
            <div class="col">
                <button class="btn btn-primary" type="submit">Filtern</button>
            </div>
        </div>
    </form>
</div>

<table class="table table-striped">
    <thead>
    <th>#</th>
    <th>RNr.</th>
    <th>Thumbnail</th>
    <th>Name</th>
    <th>Location</th>
    <th>Actions</th>
    </thead>
    <?php
    /**
     * Alle Räume durchgehen und eine List ausgeben.
     */
    foreach ($rooms as $room): ?>

        <tr>
            <td><?php
                echo $room->id; ?></td>
            <td><?php
                echo $room->room_nr; ?></td>
            <td>
                <?php
                if ($room->hasImages()): ?>
                    <img src="<?php
                    echo BASE_URL . $room->getImages()[0]; ?>" class="thumbnail--table">
                <?php
                endif; ?>
            </td>
            <td>
                <a href="<?php
                echo BASE_URL; ?>/rooms/<?php
                echo $room->id; ?>/show"><?php
                    echo $room->name; ?>
                </a>
            </td>
            <td><?php
                echo $room->location; ?></td>
            <td>
                <?php
                if (\Core\Middlewares\AuthMiddleware::isAdmin()): ?>
                    <a href="<?php
                    echo BASE_URL . "/rooms/$room->id"; ?>" class="btn btn-primary">Edit</a>

                    <a href="<?php
                    echo BASE_URL . "/rooms/$room->id/delete"; ?>" class="btn btn-danger">Delete</a>
                <?php
                endif; ?>

                <?php
                if (\Core\Middlewares\AuthMiddleware::isLoggedIn()): ?>
                    <a href="<?php
                    echo BASE_URL . "/rooms/$room->id/booking/time"; ?>" class="btn btn-success">Book</a>
                <?php
                endif; ?>
            </td>
        </tr>

    <?php
    endforeach; ?>
</table>
