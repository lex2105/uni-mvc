<!-- @todo: implement helper function for BASE_URL url generation -->
<form action="<?php
echo BASE_URL . "/rooms/{$room->id}/update" ?>" method="post" enctype="multipart/form-data">

    <div class="row">
        <div class="col">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" name="name" id="name" placeholder="Name" value="<?php
                echo $room->name; ?>" class="form-control" required>
            </div>
        </div>

        <div class="col">
            <div class="form-group">
                <label for="room_nr">Room Number</label>
                <input type="text" maxlength="10" required class="form-control" value="<?php
                echo $room->room_nr; ?>" placeholder="Room Number" name="room_nr">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <div class="form-group mt-1">
                <label for="location">Location</label>
                <textarea name="location" id="location" class="form-control" placeholder="Location"><?php
                    echo $room->location; ?></textarea>
            </div>
        </div>

        <div class="col">
            <label for="room-features">Room Features</label>
            <?php
            /**
             * Wir möchten die Checkboxen pre-selecten, daher holen wir uns eine Liste der mit dem Raum verknüpften
             * RoomFeatures und holen uns mit der array_map()-Funktion eine Liste nur der IDs dieser Features.
             * In der foreach-Schleife unten prüfen wir dann mit einem Ternary Operator, ob die Checkbox, die gerade
             * gerendert wird, in der Liste der bereits verknüpften Features vorkommt und selecten sie, oder eben nicht.
             */
            $featuresOfCurrentRoom = $room->roomFeatures();
            $idsOfFeaturesOfCurrentRoom = array_map(function ($roomFeature) {
                return $roomFeature->id;
            }, $featuresOfCurrentRoom);

            foreach ($roomFeatures as $roomFeature): ?>
                <div class="form-check">
                    <input type="checkbox" value="<?php echo $roomFeature->id; ?>" name="room-features[]" id="room-features[<?php echo $roomFeature->id; ?>]" class="form-check-input"<?php echo (in_array($roomFeature->id, $idsOfFeaturesOfCurrentRoom)) ? ' checked' : '' ?>>
                    <label class="form-check-label" for="room-features[<?php echo $roomFeature->id; ?>]"><?php echo $roomFeature->name; ?></label>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <label for="images">Images</label>
            <input type="file" class="form-control" id="images" name="images[]" multiple>
        </div>
    </div>

    <div class="row">
        <?php
        /**
         * Hier gehen wir alle Bilder aus dem Raum durch und rendern ein Thumbnail und eine Checkbox zum Löschen der
         * Bilder.
         */
        foreach ($room->getImages() as $image): ?>
        <div class="col col-2">
            <img src="<?php echo BASE_URL . $image; ?>" alt="<?php echo $room->name; ?>" class="thumbnail">

            <div class="form-check">
                <input type="checkbox" value="<?php echo $image; ?>" name="delete-images[]" id="delete-images[<?php echo $image; ?>]" class="form-check-input">
                <label class="form-check-label" for="delete-images[<?php echo $image; ?>]">Löschen?</label>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="buttons mt-1">
        <button type="submit" class="btn btn-primary">Save</button>
        <a href="<?php
        echo BASE_URL . '/rooms'; ?>" class="btn btn-danger">Cancel</a>
    </div>

</form>
