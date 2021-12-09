<div class="row">
    <div class="col">
        <p>
            <strong>Name</strong>
        </p>
        <div>
            <?php
            echo $room->name; ?>
        </div>
    </div>

    <div class="col">
        <p>
            <strong>Room Number</strong>
        </p>
        <div>
            <?php
            echo $room->room_nr; ?>
        </div>
    </div>
</div>

<div class="row">
    <div class="col">
        <p>
            <strong>Location</strong>
        </p>
        <div><?php
            echo $room->location; ?></div>
    </div>

    <div class="col">
        <p>
            <strong>Room Features</strong>
        </p>
        <ul>
            <?php
            foreach ($room->roomFeatures() as $roomFeature): ?>
                <li>
                    <?php
                    echo $roomFeature->name; ?>
                </li>
            <?php
            endforeach; ?>
        </ul>
    </div>
</div>

<div class="row">
    <?php
    /**
     * Hier gehen wir alle Bilder aus dem Raum durch und rendern ein Thumbnail.
     */
    foreach ($room->getImages() as $image): ?>
        <div class="col col-2">
            <img src="<?php echo BASE_URL . $image; ?>" alt="<?php echo $room->name; ?>" class="thumbnail">
        </div>
    <?php
    endforeach; ?>
</div>

<div class="buttons mt-1">
    <a href="<?php
    echo BASE_URL . '/rooms'; ?>" class="btn btn-danger">zurück</a>
</div>
