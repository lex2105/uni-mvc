<div class="card">
    <div class="card-header">Löschen bestätigen!</div>
    <div class="card-body">Soll <?php echo $objectType;?> "<?php echo $objectTitle; ?>" gelöscht werden?</div>
    <div class="card-footer">
        <a href="<?php echo $confirmUrl; ?>" class="btn btn-danger">Ja, löschen!</a>
        <a href="<?php echo $abortUrl; ?>" class="btn btn-link">Abbrechen</a>
    </div>
</div>
