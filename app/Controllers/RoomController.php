<?php

namespace App\Controllers;

use App\Models\Room;
use App\Models\RoomFeature;
use Core\Helpers\Redirector;
use Core\Middlewares\AuthMiddleware;
use Core\Models\File;
use Core\Session;
use Core\Validator;
use Core\View;

/**
 * Room Controller
 */
class RoomController
{

    /**
     * Alle Einträge listen.
     */
    public function index()
    {
        /**
         * Alle Objekte über das Model aus der Datenbank laden.
         *
         * Wurden filter-Checkboxen angehakerlt und über die $_GET Parameter übergeben, so filtern wir hier die Räume.
         * Wurden keine Filter übergeben, zeigen wir alle Räume.
         */
        if (isset($_GET['filters']) && !empty($_GET['filters'])) {
            $rooms = Room::getByRoomFeaturesFilter($_GET['filters']);
        } else {
            $rooms = Room::all();
        }
        $roomFeatures = RoomFeature::all();

        /**
         * View laden und Daten übergeben.
         */
        View::render('rooms/index', [
            'rooms' => $rooms,
            'roomFeatures' => $roomFeatures
        ]);
    }

    /**
     * Einzelnen Raum anzeigen.
     *
     * @param int $id
     *
     * @throws \Exception
     */
    public function show(int $id)
    {
        /**
         * Gewünschten Raum aus der DB laden.
         */
        $room = Room::findOrFail($id);

        /**
         * View laden und Daten übergeben.
         */
        View::render('rooms/show', [
            'room' => $room
        ]);
    }


    /**
     * Bearbeitungsformular anzeigen
     *
     * @param int $id
     *
     * @throws \Exception
     */
    public function edit(int $id)
    {
        /**
         * Prüfen, ob ein*e User*in eingeloggt ist und ob diese*r eingeloggte User*in Admin ist. Wenn nicht, geben wir
         * einen Fehler 403 Forbidden zurück. Dazu haben wir eine Art Middleware geschrieben, damit wir nicht immer
         * dasselbe if-Statement kopieren müssen, sondern einfach diese Funktion aufrufen können.
         */
        AuthMiddleware::isAdminOrFail();

        /**
         * Gewünschtes Element über das zugehörige Model aus der Datenbank laden.
         */
        $room = Room::findOrFail($id);

        /**
         * Alle Room Features aus der Datenbank laden, damit wir im View Checkboxen generieren können.
         */
        $roomFeatures = RoomFeature::all();

        /**
         * View laden und Daten übergeben.
         */
        View::render('rooms/edit', [
            'room' => $room,
            'roomFeatures' => $roomFeatures
        ]);
    }

    /**
     * Formulardaten aus dem Bearbeitungsformular entgegennehmen und verarbeiten.
     *
     * @param int $id
     *
     * @throws \Exception
     */
    public function update(int $id)
    {
        /**
         * Prüfen, ob ein*e User*in eingeloggt ist und ob diese*r eingeloggte User*in Admin ist. Wenn nicht, geben wir
         * einen Fehler 403 Forbidden zurück. Dazu haben wir eine Art Middleware geschrieben, damit wir nicht immer
         * dasselbe if-Statement kopieren müssen, sondern einfach diese Funktion aufrufen können.
         */
        AuthMiddleware::isAdminOrFail();

        /**
         * 1) Daten validieren
         * 2) Model aus der DB abfragen, das aktualisiert werden soll
         * 3) Model in PHP überschreiben
         * 4) Model in DB zurückspeichern
         * 5) Redirect irgendwohin
         */

        /**
         * Nachdem wir exakt dieselben Validierungen durchführen für update und create, können wir sie in eine eigene
         * Methode auslagern und überall dort verwenden, wo wir sie brauchen.
         */
        $validationErrors = $this->validateFormData($id);

        /**
         * Sind Validierungsfehler aufgetreten ...
         */
        if (!empty($validationErrors)) {
            /**
             * ... dann speichern wir sie in die Session um sie in den Views dann ausgeben zu können ...
             */
            Session::set('errors', $validationErrors);
            /**
             * ... und leiten zurück zum Bearbeitungsformular. Der Code weiter unten in dieser Funktion wird dadurch
             * nicht mehr ausgeführt.
             */
            Redirector::redirect("/rooms/${id}");
        }

        /**
         * Gewünschten Room über das ROom-Model aus der Datenbank laden. Hier verwenden wir die findOrFail()-Methode aus
         * dem AbstractModel, die einen 404 Fehler ausgibt, wenn das Objekt nicht gefunden wird. Dadurch sparen wir uns
         * hier zu prüfen, ob ein Post gefunden wurde oder nicht.
         */
        $room = Room::findOrFail($id);

        /**
         * Sind keine Fehler aufgetreten legen aktualisieren wir die Werte des vorher geladenen Objekts ...
         */
        $room->fill($_POST);

        /**
         * Hochgeladene Dateien verarbeiten.
         */
        $room = $this->handleUploadedFiles($room);
        /**
         * Checkboxen verarbeiten, ob eine Datei gelöscht werden soll oder nicht.
         */
        $room = $this->handleDeleteFiles($room);

        /**
         * RoomFeature Selections speichern.
         *
         * Wurden Raum Features im Formular ausgewählt, so holen wir hier die gewählten IDs und überschreiben die
         * aktuell verknüpften Raum Features. Andernfalls löschen wir alle Zuweisung, weil alle Checkboxen
         * abgewählt wurden.
         */
        if (isset($_POST['room-features'])) {
            $room->setRoomFeatures($_POST['room-features']);
        } else {
            $room->setRoomFeatures([]);
        }

        /**
         * Schlägt die Speicherung aus irgendeinem Grund fehl ...
         */
        if (!$room->save()) {
            /**
             * ... so speichern wir einen Fehler in die Session und leiten wieder zurück zum Bearbeitungsformular.
             */
            Session::set('errors', ['Speichern fehlgeschlagen.']);
        }

        /**
         * Wenn alles funktioniert hat, leiten wir zurück zur /home-Route.
         */
        Redirector::redirect("/rooms/${id}");
    }

    /**
     * Erstellungsformular anzeigen
     *
     * @throws \Exception
     */
    public function create()
    {
        /**
         * Prüfen, ob ein*e User*in eingeloggt ist und ob diese*r eingeloggte User*in Admin ist. Wenn nicht, geben wir
         * einen Fehler 403 Forbidden zurück. Dazu haben wir eine Art Middleware geschrieben, damit wir nicht immer
         * dasselbe if-Statement kopieren müssen, sondern einfach diese Funktion aufrufen können.
         */
        AuthMiddleware::isAdminOrFail();

        /**
         * Alle Room Features aus der Datenbank laden, damit wir im View Checkboxen generieren können.
         */
        $roomFeatures = RoomFeature::all();

        /**
         * View laden und Daten übergeben.
         */
        View::render('rooms/create', [
            'roomFeatures' => $roomFeatures
        ]);
    }

    /**
     * Formulardaten aus dem Erstellungsformular entgegennehmen und verarbeiten.
     *
     * @throws \Exception
     */
    public function store()
    {
        /**
         * Prüfen, ob ein*e User*in eingeloggt ist und ob diese*r eingeloggte User*in Admin ist. Wenn nicht, geben wir
         * einen Fehler 403 Forbidden zurück. Dazu haben wir eine Art Middleware geschrieben, damit wir nicht immer
         * dasselbe if-Statement kopieren müssen, sondern einfach diese Funktion aufrufen können.
         */
        AuthMiddleware::isAdminOrFail();

        /**
         * 1) Daten validieren
         * 2) Model aus der DB abfragen, das aktualisiert werden soll
         * 3) Model in PHP überschreiben
         * 4) Model in DB zurückspeichern
         * 5) Redirect irgendwohin
         */

        /**
         * Nachdem wir exakt dieselben Validierungen durchführen für update und create, können wir sie in eine eigene
         * Methode auslagern und überall dort verwenden, wo wir sie brauchen.
         */
        $validationErrors = $this->validateFormData();

        /**
         * Sind Validierungsfehler aufgetreten ...
         */
        if (!empty($validationErrors)) {
            /**
             * ... dann speichern wir sie in die Session um sie in den Views dann ausgeben zu können ...
             */
            Session::set('errors', $validationErrors);
            /**
             * ... und leiten zurück zum Bearbeitungsformular. Der Code weiter unten in dieser Funktion wird dadurch
             * nicht mehr ausgeführt.
             */
            Redirector::redirect("/rooms/create");
        }

        /**
         * Neuen Room erstellen und mit den Daten aus dem Formular befüllen.
         */
        $room = new Room();
        $room->fill($_POST);

        /**
         * Schlägt die Speicherung aus irgendeinem Grund fehl ...
         */
        if (!$room->save()) {
            /**
             * ... so speichern wir einen Fehler in die Session und leiten wieder zurück zum Bearbeitungsformular.
             */
            Session::set('errors', ['Speichern fehlgeschlagen.']);
            Redirector::redirect("/rooms/create");
        }

        /**
         * Wenn alles funktioniert hat, leiten wir zurück zur /home-Route.
         */
        Redirector::redirect('/home');
    }

    /**
     * Confirmation Page für die Löschung eines Raumes laden.
     *
     * @param int $id
     *
     * @throws \Exception
     */
    public function delete(int $id)
    {
        /**
         * Prüfen, ob ein*e User*in eingeloggt ist und ob diese*r eingeloggte User*in Admin ist. Wenn nicht, geben wir
         * einen Fehler 403 Forbidden zurück. Dazu haben wir eine Art Middleware geschrieben, damit wir nicht immer
         * dasselbe if-Statement kopieren müssen, sondern einfach diese Funktion aufrufen können.
         */
        AuthMiddleware::isAdminOrFail();

        /**
         * Raum, der gelöscht werden soll, aus der DB laden.
         */
        $room = Room::findOrFail($id);

        /**
         * View laden und relativ viele Daten übergeben. Die große Anzahl an Daten entsteht dadurch, dass der
         * helpers/confirmation-View so dynamisch wie möglich sein soll, damit wir ihn für jede Delete Confirmation
         * Seite verwenden können, unabhängig vom Objekt, das gelöscht werden soll. Wir übergeben daher einen Typ und
         * einen Titel, die für den Text der Confirmation verwendet werden, und zwei URLs, eine für den
         * Bestätigungsbutton und eine für den Abbrechen-Button.
         */
        View::render('helpers/confirmation', [
            'objectType' => 'Raum',
            'objectTitle' => $room->name,
            'confirmUrl' => BASE_URL . '/rooms/' . $room->id . '/delete/confirm',
            'abortUrl' => BASE_URL . '/rooms'
        ]);
    }

    /**
     * Raum löschen.
     *
     * @param int $id
     *
     * @throws \Exception
     */
    public function deleteConfirm(int $id)
    {
        /**
         * Prüfen, ob ein*e User*in eingeloggt ist und ob diese*r eingeloggte User*in Admin ist. Wenn nicht, geben wir
         * einen Fehler 403 Forbidden zurück. Dazu haben wir eine Art Middleware geschrieben, damit wir nicht immer
         * dasselbe if-Statement kopieren müssen, sondern einfach diese Funktion aufrufen können.
         */
        AuthMiddleware::isAdminOrFail();

        /**
         * Raum, der gelöscht werden soll, aus DB laden.
         */
        $room = Room::findOrFail($id);
        /**
         * Raum löschen.
         */
        $room->delete();

        /**
         * Erfolgsmeldung für später in die Session speichern.
         */
        Session::set('success', ['Raum erfolgreich gelöscht.']);
        /**
         * Weiterleiten zur Home Seite.
         */
        Redirector::redirect('/home');
    }

    /**
     * Validierungen kapseln, damit wir sie überall dort, wo wir derartige Objekte validieren müssen, verwenden können.
     *
     * @param int $id Wird dieser Wert übergeben, so ignoriert die unique Methode den Eintrag mit der übergebenen ID.
     *
     * @return array
     */
    private function validateFormData(int $id = 0): array
    {
        /**
         * Neues Validator Objekt erstellen.
         */
        $validator = new Validator();

        /**
         * Gibt es überhaupt Daten, die validiert werden können?
         */
        if (!empty($_POST)) {
            /**
             * Daten validieren. Für genauere Informationen zu den Funktionen s. Core\Validator.
             *
             * Hier verwenden wir "named params", damit wir einzelne Funktionsparameter überspringen können.
             */
            $validator->textnum($_POST['name'], label: 'Name', required: true, max: 255);
            $validator->textnum($_POST['location'], label: 'Location');
            $validator->alphanumeric($_POST['room_nr'], label: 'Room Number', required: true, max: 10, min: 1);
            $validator->unique(
                $_POST['room_nr'],
                label: 'Room Number',
                table: Room::getTablenameFromClassname(),
                column: 'room_nr',
                ignoreThisId: $id
            );
            $validator->file($_FILES['images'], label: 'Images', type: 'image');
            /**
             * @todo: implement Validate Array + Contents
             */
        }

        /**
         * Fehler aus dem Validator zurückgeben.
         */
        return $validator->getErrors();
    }

    /**
     * Hochgeladene Dateien verarbeiten.
     *
     * @param Room $room
     *
     * @return Room|null
     */
    public function handleUploadedFiles(Room $room): ?Room
    {
        /**
         * Wir erstellen zunächst einen Array an Objekten, damit wir Logik, die zu einer Datei gehört, in diesen
         * Objekten kapseln können.
         */
        $files = File::createFromUploadedFiles('images');

        /**
         * Nun gehen wir alle Dateien durch ...
         */
        foreach ($files as $file) {
            /**
             * ... speichern sie in den Uploads Ordner ...
             */
            $storagePath = $file->putToUploadsFolder();
            /**
             * ... und verknüpfen sie mit dem Raum.
             */
            $room->addImages([$storagePath]);
        }
        /**
         * Nun geben wir den aktualisierten Raum wieder zurück.
         */
        return $room;
    }


    /**
     * Löschen-Checkboxen der Bilder eines Raumes verarbeiten.
     *
     * @param Room $room
     *
     * @return Room
     */
    private function handleDeleteFiles(Room $room): Room
    {
        /**
         * Wir prüfen, ob eine der Checkboxen angehakerlt wurde.
         */
        if (isset($_POST['delete-images'])) {
            /**
             * Wenn ja, gehen wir alle Checkboxen durch ...
             */
            foreach ($_POST['delete-images'] as $deleteImage) {
                /**
                 * Lösen die Verknüpfung zum Room ...
                 */
                $room->removeImages([$deleteImage]);
                /**
                 * ... und löschen die Datei aus dem Uploads-Ordner.
                 */
                File::delete($deleteImage);
            }
        }

        return $room;
    }

}
