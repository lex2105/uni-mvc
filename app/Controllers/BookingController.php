<?php

namespace App\Controllers;

use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Core\Helpers\Redirector;
use Core\Middlewares\AuthMiddleware;
use Core\Models\DateTime;
use Core\Session;
use Core\View;

/**
 * Booking Controller
 */
class BookingController
{

    /**
     * Formular zur Buchung von Time-Slots zu einem Raum anzeigen.
     *
     * @param int $id
     *
     * @throws \Exception
     */
    public function selectSlots(int $id)
    {
        /**
         * Prüfen, ob ein*e User*in eingeloggt ist. Wenn nicht, geben wir einen Fehler 403 Forbidden zurück. Dazu haben
         * wir eine Art Middleware geschrieben, damit wir nicht immer dasselbe if-Statement kopieren müssen, sondern
         * einfach diese Funktion aufrufen können.
         */
        AuthMiddleware::isLoggedInOrFail();

        /**
         * Raum laden
         */
        $room = Room::find($id);

        /**
         * Formular anzeigen, damit Zeitslots gebucht werden können.
         */
        View::render('bookings/time', [
            'room' => $room
        ]);
    }

    /**
     * Timeslots wirklich buchen.
     *
     * @param int $id
     *
     * @throws \Exception
     */
    public function bookSlots(int $id)
    {
        /**
         * Prüfen, ob ein*e User*in eingeloggt ist. Wenn nicht, geben wir einen Fehler 403 Forbidden zurück. Dazu haben
         * wir eine Art Middleware geschrieben, damit wir nicht immer dasselbe if-Statement kopieren müssen, sondern
         * einfach diese Funktion aufrufen können.
         */
        AuthMiddleware::isLoggedInOrFail();

        /**
         * @todo: Hier müsste eigentlich validiert werden! Wir verzichten der Einfachheit halber vorerst darauf.
         */

        /**
         * Wurden Timeslots aus dem Fomular übergeben?
         */
        if (isset($_POST['timeslots']) && !empty($_POST['timeslots'])) {
            /**
             * Variable für DateTime Objekte vorbereiten.
             */
            $dates = [];

            /**
             * Alle Timeslots aus dem Formular durchgehen
             */
            foreach ($_POST['timeslots'] as $timeslot) {
                /**
                 * DateTime Objekte daraus erstellen und Datum und Uhrzeit aus den Formulardaten setzen.
                 */
                $startDate = new DateTime($_POST['date']);
                $startDate->setTime($timeslot, 0);

                /**
                 * DateTIme Objekte für das Ende des Timeslots erstellen. Dazu klonen wir das $startDate und rechnen
                 * eine Stunde dazu.
                 */
                $endDate = clone $startDate;
                $endDate->modify('+1 hour');

                /**
                 * Nun speichern wir Beginn- und Endzeit in das vorbereitete Variable.
                 */
                $dates[] = [
                    'start' => $startDate,
                    'end' => $endDate
                ];
            }

            /**
             * Schalter, den wir später bei Bedarf umschalten werden.
             */
            $allTimeslotsAvailable = true;
            /**
             * Wir gehen nun also alle $dates durch und prüfen, ob es bereits Bookings in diesem Zeitraum gibt.
             */
            foreach ($dates as $startAndEndDate) {
                if (Booking::existsForRoomAndTime($id, $startAndEndDate['start'], $startAndEndDate['end'])) {
                    /**
                     * Gibt es bereits Bookings in dem Timeslot für den Raum, so legen wir den Schalter um und beenden
                     * die Schleife.
                     */
                    $allTimeslotsAvailable = false;
                    break;
                }
            }

            /**
             * Gibt es noch keine Bookings für den Raum in dem gewünschten Zeitraum und sind somit alle gewünschten
             * Timeslots verfügbar, gehen wir wieder alle $dates durch und erstellen Booking Objekte daraus, die wir
             * auch direkt speichern.
             */
            if ($allTimeslotsAvailable === true) {
                foreach ($dates as $startAndEndDate) {
                    $booking = new Booking();
                    $booking->fill([
                        'user_id' => User::getLoggedIn()->id,
                        'foreign_table' => Room::class,
                        'foreign_id' => $id,
                        'time_from' => $startAndEndDate['start'],
                        'time_to' => $startAndEndDate['end']
                    ]);
                    $booking->save();
                }
                /**
                 * Nun speichern wir eine Erfolgsmeldung in die Session und leiten weiter.
                 */
                Session::set('success', ['Timeslots erfolgreich gebucht!']);
                Redirector::redirect("/rooms");
            }

            /**
             * In jedem anderen Fall speichern wir einen Fehler in die Session.
             */
            Session::set('errors', ['Einer der gewählten Timeslots ist bereits vergeben.']);
        } else {
            /**
             * In jedem anderen Fall speichern wir einen Fehler in die Session.
             */
            Session::set('errors', ['Keine Timeslots ausgewählt.']);
        }

        /**
         * Nun leiten wir weiter zum Buchungsformular.
         */
        Redirector::redirect("/rooms/$id/booking/time");
    }

}
