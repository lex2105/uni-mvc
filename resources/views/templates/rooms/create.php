<!-- @todo: implement helper function for BASE_URL url generation -->
<form action="<?php echo BASE_URL . "/rooms/store" ?>" method="post">

    <div class="row">
        <div class="col">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" name="name" id="name" placeholder="Name" class="form-control" required>
            </div>
        </div>

        <div class="col">
            <div class="form-group">
                <label for="room_nr">Room Number</label>
                <input type="text" maxlength="10" required class="form-control" placeholder="Room Number" name="room_nr">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <div class="form-group mt-1">
                <label for="location">Location</label>
                <textarea name="location" id="location" class="form-control" placeholder="Location"></textarea>
            </div>
        </div>

        <div class="col">
            <label for="room-features">Room Features</label>
            <?php
            foreach ($roomFeatures as $roomFeature): ?>
                <div class="form-check">
                    <input type="checkbox" value="<?php echo $roomFeature->id; ?>" name="room-features[]" id="room-features[<?php echo $roomFeature->id; ?>]" class="form-check-input">
                    <label class="form-check-label" for="room-features[<?php echo $roomFeature->id; ?>]"><?php echo $roomFeature->name; ?></label>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="buttons mt-1">
        <button type="submit" class="btn btn-primary">Save</button>
        <a href="<?php
        echo BASE_URL . '/rooms'; ?>" class="btn btn-danger">Cancel</a>
    </div>

</form>
