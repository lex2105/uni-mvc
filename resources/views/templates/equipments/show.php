<div class="row">
    <div class="col">
        <p>
            <strong>Name</strong>
        </p>
        <div>
            <?php
            echo $equipment->name; ?>
        </div>
    </div>

    <div class="col">
        <p>
            <strong>Units</strong>
        </p>
        <div>
            <?php
            echo $equipment->units; ?>
        </div>
    </div>
</div>

<div class="row">
    <div class="col">
        <p>
            <strong>Description</strong>
        </p>
        <div><?php
            echo $equipment->description; ?></div>
    </div>

    <div class="col">
        <p>
            <strong>Type</strong>
        </p>
        <div><?php
            echo $equipment->type(); ?></div>
    </div>
</div>

<div class="buttons mt-1">
    <a href="<?php
    echo BASE_URL . '/equipments'; ?>" class="btn btn-danger">zurück</a>
</div>
