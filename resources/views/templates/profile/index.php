<h2>Hallo, <?php
    echo $user->getDisplayName(); ?>!</h2>

<div class="row">

    <div class="col-4">
        <h3>Meine Räume</h3>
        <ul>
            <?php
            foreach ($user->roomBookings() as $roomBooking): ?>
                <li>
                    <?php
                    echo $roomBooking->bookable()->name; ?>
                    <p class="small">
                        <?php
                        echo $roomBooking->getTimeFromFormatted(); ?>
                        -
                        <?php
                        echo $roomBooking->getTimeToFormatted(); ?>
                    </p>
                </li>
            <?php
            endforeach; ?>
        </ul>
    </div>

    <div class="col-4">
        <h3>Mein Equipment</h3>
        <ul>
            <?php
            foreach ($user->equipmentBookings(true) as $equipmentBooking):
                $bookable = $equipmentBooking->bookable();
                ?>
                <li>
                    <?php
                    echo $bookable->name; ?>
                    (<?php
                    echo $equipmentBooking->units; ?>x)
                </li>
            <?php
            endforeach; ?>
        </ul>
    </div>

    <div class="col-4">
        <h3>Profil bearbeiten</h3>
        <form action="<?php
        echo BASE_URL; ?>/profile/update" method="post">

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="<?php
                echo $user->email; ?>" class="form-control">
            </div>

            <div class="form-group">
                <label for="password">Passwort ändern</label>
                <input type="password" name="password" id="password" class="form-control">
            </div>
            <div class="form-group">
                <label for="password_repeat">Passwort wiederholen</label>
                <input type="password" name="password_repeat" id="password_repeat" class="form-control">
            </div>

            <button class="btn btn-primary mt-2" type="submit">Speichern</button>

        </form>
    </div>

</div>
