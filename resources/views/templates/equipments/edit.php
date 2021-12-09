<!-- @todo: implement helper function for BASE_URL url generation -->
<form action="<?php echo BASE_URL . "/equipments/{$equipment->id}/update" ?>" method="post">

    <div class="row">
        <div class="col">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" name="name" id="name" placeholder="Name" class="form-control" value="<?php echo $equipment->name; ?>" required>
            </div>
        </div>

        <div class="col">
            <div class="form-group">
                <label for="units">Units</label>
                <input type="number" min="1" step="1" required class="form-control" placeholder="Units" name="units" id="units" value="<?php echo $equipment->units; ?>">
            </div>
        </div>

        <div class="col">
            <div class="form-group">
                <label for="type">Type</label>
                <select class="form-select" name="type" id="type" disabled>
                    <option value="_default">Bitte auswählen ...</option>
                </select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <div class="form-group mt-1">
                <label for="description">Description</label>
                <textarea name="description" id="description" class="form-control" placeholder="Description"><?php echo $equipment->description; ?></textarea>
            </div>
        </div>
    </div>

    <div class="buttons mt-1">
        <button type="submit" class="btn btn-primary">Save</button>
        <a href="<?php
        echo BASE_URL . '/rooms'; ?>" class="btn btn-danger">Cancel</a>
    </div>

</form>
