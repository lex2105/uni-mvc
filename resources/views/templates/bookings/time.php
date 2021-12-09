<div class="card">
    <div class="card-header">Raumbuchung: <?php echo $room->name; ?></div>
    <div class="card-body">
        <form action="<?php echo BASE_URL . "/rooms/$room->id/booking/do"; ?>" method="post">
            <div class="form-group">
                <label for="date">Datum</label>
                <input type="date" name="date" id="date" class="form-control">
            </div>

            <?php
            /**
             * Hier holen wir die Start- und Endzeiten aus der Config, damit wir wissen, welche Checkboxen wir
             * generieren müssen.
             */
            $bookingStart = \Core\Config::get('app.booking-start', 8);
            $bookingEnd = \Core\Config::get('app.booking-end', 16);

            /**
             * Nun nutzen wir eine for-schleife, um die Checkboxen zu generieren.
             */
            for ($i = $bookingStart; $i < $bookingEnd; $i++): ?>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="timeslots[]" id="timeslot[<?php echo $i; ?>]" value="<?php echo $i; ?>">
                    <label for="timeslot[<?php echo $i; ?>]" class="form-check-label">
                        <?php
                        /**
                         * Nun formatieren wir die Zählervariable $i so, dass wir eine Uhrzeit herausbekommen. Die
                         * str_pad()-Funktion fügt in diesem Fall an den Anfang der Zeichenkette $i so lange Nuller an,
                         * bis die Länge der Zeichenkette 2 ist - bspw.:
                         *
                         * + 9 => 09
                         * + 10 => 10
                         * * 6 => 06
                         */
                        echo str_pad($i, 2, '0', STR_PAD_LEFT) . ':00-' . str_pad($i + 1, 2, '0', STR_PAD_LEFT) . ':00'; ?>
                    </label>
                </div>
            <?php endfor; ?>
            <button class="btn btn-primary" type="submit">Buchen!</button>
        </form>
    </div>
</div>
