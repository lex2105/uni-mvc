<!-- @todo: implement helper function for BASE_URL url generation -->
<form action="<?php
echo BASE_URL . "/room-features/store" ?>" method="post">

    <div class="form-group">
        <label for="name">Name</label>
        <input type="text" name="name" id="name" placeholder="Name" class="form-control" required>
    </div>

    <div class="form-group">
        <label for="description">Description</label>
        <textarea maxlength="255" required class="form-control" placeholder="Description" name="description" id="description"></textarea>
    </div>

    <div class="buttons mt-1">
        <button type="submit" class="btn btn-primary">Save</button>
        <a href="<?php
        echo BASE_URL . '/room-features'; ?>" class="btn btn-danger">Cancel</a>
    </div>

</form>
