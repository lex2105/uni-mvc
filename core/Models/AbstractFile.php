<?php

namespace Core\Models;

use Core\Config;

/**
 * Class AbstractFile
 *
 * Damit wir eine Abstraktionsebene über das Dateisystem legen können, bauen wir eine eigene Klasse, die die Arbeit mit
 * einzelnen Dateien vereinfachen soll.
 *
 * @package Core\Models
 */
abstract class AbstractFile
{

    public function __construct(
        public string $name,
        public ?string $type,
        public ?string $tmp_name,
        public ?int $error,
        public ?int $size,
    ) {
    }

    /**
     * Prüfen ob Fehler während des Uploads aufgetreten sind oder nicht.
     *
     * @return bool
     */
    public function hasUploadError(): bool
    {
        return $this->error !== UPLOAD_ERR_OK;
    }

    /**
     * Prüfen, ob Dateien hochgeladen wurden unter einem bestimmten input Namen.
     *
     * @param string $keyInSuperglobal
     *
     * @return bool
     */
    public static function filesHaveBeenUploaded(string $keyInSuperglobal = 'files'): bool
    {
        return ($_FILES[$keyInSuperglobal]['error'][0] !== UPLOAD_ERR_NO_FILE);
    }

    /**
     * AbstractFile Objekte aus den Daten aus der $_FILES Superglobal erstellen.
     *
     * @param string $keyInSuperglobal Name des Upload Feldes im Formular
     *
     * @return array
     */
    public static function createFromUploadedFiles(string $keyInSuperglobal = 'files'): array
    {
        /**
         * Wurden überhaupt Dateien hochgeladen?
         */
        if (self::filesHaveBeenUploaded($keyInSuperglobal)) {
            /**
             * Daten zu einem bestimmten Upload Feld aus $_FILES holen.
             */
            $files = $_FILES[$keyInSuperglobal];

            /**
             * Liste vorbereiten.
             */
            $filesObjects = [];

            /**
             * Alle Dateinamen durchgehen und über den zugehörigen $key alle Daten in ein jeweils neues AbstractFile
             * füllen.
             */
            foreach ($files['name'] as $key => $name) {
                $file = new File(
                    $name,
                    $files['type'][$key],
                    $files['tmp_name'][$key],
                    $files['error'][$key],
                    $files['size'][$key]
                );
                $filesObjects[] = $file;
            }

            /**
             * Liste der generierten File Objekte zurückgeben.
             */
            return $filesObjects;
        }
        /**
         * Leeres Array zurückgeben, wenn keine Dateien hochgeladen wurden.
         */
        return [];
    }

    /**
     * Datei an in den Uploads Ordner speichern.
     *
     * @return string Filepath, an den das File gespeichert wurde
     *
     * @throws \Exception
     */
    public function putToUploadsFolder(): string
    {
        /**
         * Zielpfad holen.
         */
        $destinationPath = $this->getDestinationPath();
        /**
         * Temporäre Datei verschieben.
         */
        if (move_uploaded_file($this->tmp_name, $destinationPath)) {
            /**
             * Hat alles funktioniert, berechnen wir einen relativen Pfad der Datei und geben ihn zurück.
             */
            return self::relativeUploadPathFromAbsolutePath($destinationPath);
        } else {
            /**
             * Andernfalls werfen wir einen Fehler.
             */
            throw new \Exception('Uploaded files can not be stored.');
        }
    }

    /**
     * Zielpfad berechnen.
     *
     * @return string
     */
    public function getDestinationPath(): string
    {
        /**
         * Uploads Ordner aus Config holen.
         */
        $uploadsFolder = Config::get('app.uploads-folder');

        /**
         * Storage Pfad holen und Ziel Pfad berechnen.
         */
        $storageFolderAbsolutePath = self::getAbsoluteStoragePath();
        $destinationPath = realpath("{$storageFolderAbsolutePath}/{$uploadsFolder}/");
        $destinationName = time() . "_{$this->name}";

        /**
         * Fertigen Zielpfad zurückgeben.
         */
        return "{$destinationPath}/{$destinationName}";
    }

    /**
     * Absoluten Pfad in einen Pfad relativ zum Storage Ordner umrechnen.
     *
     * @param string $absolutePath
     *
     * @return string
     */
    static function relativeUploadPathFromAbsolutePath(string $absolutePath): string
    {
        /**
         * Uploads Ordner aus der Config holen.
         */
        $uploadsFolder = Config::get('app.uploads-folder');
        /**
         * Prüfen, wo der $uploadsFolder im absoluten Pfad anfängt ...
         */
        $uploadsFolderStrpos = strpos($absolutePath, $uploadsFolder);
        /**
         * ... und den String bis dahin kürzen.
         */
        $relativePath = substr($absolutePath, $uploadsFolderStrpos);
        /**
         * "storage/" entfernen, falls es noch dabei sein sollte. Dadurch können dir das Bild ganz einfach im HTML
         * verwenden.
         */
        $relativePathWithoutStorage = str_replace('storage/', '', $relativePath);

        /**
         * Berechneten Pfad zurück geben.
         */
        return $relativePathWithoutStorage;
    }

    /**
     * Hilfsfunktion zur Berechnung des Storage Path absolut zum Server Wurzelverzeichnis (Root).
     *
     * @return string
     */
    public static function getAbsoluteStoragePath(): string
    {
        /**
         * Wir definieren unseren Pfad ausgehend von dem Ordner, in dem diese Datei liegt, "relative".
         */
        $absoluteStoragePath = __DIR__ . '/../../storage';
        /**
         * Die realpath()-Methode löst bspw. ".." und "~" in Pfaden auf und erstellt einen absoluten Pfad daraus.
         */
        $absoluteStoragePath = realpath($absoluteStoragePath);
        /**
         * Diesen Pfad geben wir zurück.
         */
        return $absoluteStoragePath;
    }

    /**
     * Datei physisch löschen.
     *
     * @param string $filepathRelativeToStorage
     *
     * @return bool|int
     */
    public static function delete(string $filepathRelativeToStorage): bool|int
    {
        /**
         * Existiert eine Datei an dem Pfad, der übergeben wurde ...
         */
        if (file_exists($filepathRelativeToStorage)) {
            /**
             * ... so löschen wir die Datei.
             */
            return unlink($filepathRelativeToStorage);
        }
        /**
         * Andernfalls geben wir -1 zurück. Dadurch können wir zwischen Erfolg (true) und Fehler (false) der unlink()-
         * Methode unterscheiden und dem Status, dass die Datei nicht existiert.
         */
        return -1;
    }

}
