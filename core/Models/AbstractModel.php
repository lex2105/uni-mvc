<?php

namespace Core\Models;

use Core\Database;
use Exception;

/**
 * Class AbstractModel
 *
 * @package Core\Models
 */
abstract class AbstractModel
{

    /**
     * Hier definieren wir, dass jede Class, die das AbstractModel erweitert, auch eine save()-Methode definieren muss.
     *
     * @return bool
     */
    public abstract function save(): bool;

    /**
     * Alle Datensätze aus der Datenbank abfragen.
     *
     * Die beiden Funktionsparameter bieten die Möglichkeit die Daten, die abgerufen werden, nach einer einzelnen Spalte
     * aufsteigend oder absteigend direkt über MySQL zu sortieren. Sortierungen sollten, sofern möglich, über die
     * Datenbank durchgeführt werden, weil das wesentlich performanter ist als über PHP.
     *
     * @param string|null $orderBy
     * @param string|null $direction
     *
     * @return array
     */
    public static function all(?string $orderBy = null, ?string $direction = null): array
    {
        /**
         * Datenbankverbindung herstellen.
         */
        $database = new Database();
        /**
         * Tabellennamen berechnen.
         */
        $tablename = self::getTablenameFromClassname();

        /**
         * Query ausführen.
         *
         * Wurde in den Funktionsparametern eine Sortierung definiert, so wenden wir sie hier an, andernfalls rufen wir
         * alles ohne Sortierung ab.
         */
        if ($orderBy === null) {
            $result = $database->query("SELECT * FROM $tablename");
        } else {
            $result = $database->query("SELECT * FROM $tablename ORDER BY $orderBy $direction");
        }

        /**
         * Datenbankergebnis verarbeiten und zurückgeben.
         */
        return self::handleResult($result);
    }

    /**
     * Ein einzelnes Objekt anhand seiner ID finden.
     *
     * @param int $id
     *
     * @return object|null
     */
    public static function find(int $id): ?object
    {
        /**
         * Datenbankverbindung herstellen.
         */
        $database = new Database();
        /**
         * Tabellennamen berechnen.
         */
        $tablename = self::getTablenameFromClassname();

        /**
         * Query ausführen.
         */
        $result = $database->query("SELECT * FROM $tablename WHERE `id` = ?", [
            'i:id' => $id
        ]);

        /**
         * Datenbankergebnis verarbeiten und zurückgeben.
         */
        return self::handleUniqueResult($result);
    }

    /**
     * find()-Methode aufrufen oder einen Fehler 404 Not Found zurückgeben, wenn kein Ergebnis aus der Datenbank
     * zurückgekommen ist.
     *
     * @param int $id
     *
     * @return object|null
     * @throws Exception
     */
    public static function findOrFail(int $id): ?object
    {
        /**
         * find()-Methode aufrufen.
         */
        $result = self::find($id);

        if (empty($result)) {
            /**
             * Wurde kein Ergebnis gefunden, so werfen wir einen Fehler, den wir über den ErrorHandler dann abfangen
             * können.
             */
            throw new Exception('Model not found', 404);
        }

        /**
         * Wurde ein Ergebnis gefunden, so geben wir es zurück.
         */
        return $result;
    }

    /**
     * Diese Methode ermöglicht es uns, jedes beliebige Model, das das AbstractModel erweitert, mit Daten aus einem
     * Array (bspw. $_GET oder $_POST) zu befüllen.
     *
     * @param array $data
     * @param bool  $ignoreEmpty
     *
     * @return object
     */
    public function fill(array $data, bool $ignoreEmpty = true): object
    {
        /**
         * 1) $data durchgehen
         * 2) Gibt es eine Property zu den $data Werten?
         *   Wenn ja: weiter, wenn nein: nix
         * 3) Wert in Property mit Wert aus $data überschreiben
         * 4) Fertiges Object zurückgeben
         */

        /**
         * Wir gehen alle Werte aus dem übergebenen Array durch.
         */
        foreach ($data as $name => $value) {
            /**
             * Existiert zu dem Wert eine namensgleiche Property in dem Objekt ...
             */
            if (property_exists($this, $name)) {
                /**
                 * ... so trimmen wir den Wert.
                 */
                $trimmedValue = trim($value);
                /**
                 * Ist der getrimmte Wert nicht leer oder möchten wir leere Werte nicht ignorieren, so überschreiben
                 * wir die Property mit dem Wert aus dem Array.
                 */
                if ($ignoreEmpty !== true || !empty($value)) {
                    $this->$name = $trimmedValue;
                }
            }
        }

        /**
         * Nun geben wir das aktualisierte Objekt zurück.
         */
        return $this;
    }

    /**
     * Objekt löschen.
     *
     * @return bool
     */
    public function delete(): bool
    {
        /**
         * Datenbankverbindung herstellen.
         */
        $database = new Database();
        /**
         * Tabellennamen berechnen.
         */
        $tablename = self::getTablenameFromClassname();

        /**
         * Query ausführen.
         */
        $result = $database->query("DELETE FROM $tablename WHERE id = ?", [
            'i:id' => $this->id
        ]);

        /**
         * Datenbankergebnis verarbeiten und zurückgeben.
         */
        return $result;
    }

    /**
     * Resultat aus der Datenbank verarbeiten.
     *
     * Wir haben das aus der self::all()-Methode ausgelagert, weil die all()-Methode nicht die einzige Methode sein
     * wird, in der wir Datenbankergebnisse verarbeiten werden müssen. Damit wir den Code nicht immer kopieren müssen,
     * was als Bad Practice gilt, haben wir eine eigene Methode gebaut.
     *
     * @param array $results
     *
     * @return array
     */
    public static function handleResult(array $results): array
    {
        /**
         * Ergebnis-Array vorbereiten.
         */
        $objects = [];

        /**
         * Ergebnisse des Datenbank-Queries durchgehen und jeweils ein neues Objekt erzeugen.
         */
        foreach ($results as $result) {
            /**
             * Auslesen, welche Klasse aufgerufen wurde und ein Objekt dieser Klasse erstellen und in den Ergebnis-Array
             * speichern. Das ist nötig, weil wir bspw. Post Objekte haben wollen und nicht ein Array voller
             * AbstractModels.
             */
            $calledClass = get_called_class();
            $objects[] = new $calledClass(...$result);
        }

        /**
         * Ergebnisse zurückgeben.
         */
        return $objects;
    }

    /**
     * Hier erweitern wir die self::handleResult()-Methode für den Fall, dass wir von einem Query kein oder maximal ein
     * Ergebnis erwarten. Bei einem Query mit einer WHERE-Abfrage auf eine UNIQUE-Spalte, würden wir maximal ein
     * Ergebnis zurückbekommen. Diese Funktion ist also mehr eine Convenience Funktion, weil sie entweder null
     * zurückgibt, wenn kein Ergebnis zurückgekommen ist (statt eines leeren Arrays in self::handleResult()) oder ein
     * einzelnes Objekt (statt eines Arrays mit einem einzigen Objekt darin).
     *
     * @param array $results
     *
     * @return ?object
     */
    public static function handleUniqueResult(array $results): ?object
    {
        /**
         * Datenbankergebnis ganz normal verarbeiten.
         */
        $objects = self::handleResult($results);

        /**
         * ist das Ergebnis aus der Datenbank leer, geben wir null zurück.
         */
        if (empty($objects)) {
            return null;
        }

        /**
         * Andernfalls geben wir das Objekt an Stelle 0 zurück, das in diesem Fall das einzige Objekt sein sollte.
         */
        return $objects[0];
    }

    /**
     * Wird ein INSERT-Query ausgeführt, so wird in den allermeisten Fällen auch eine neue ID generiert. Diese ist über
     * die Datenbankverbindung abrufbar. Hier holen wir diese ID und aktualisieren das aktuelle Objekt mit der neuen ID.
     *
     * @param Database $database
     */
    public function handleInsertResult(Database $database)
    {
        /**
         * Neu generierte id holen.
         */
        $newId = $database->getInsertId();

        /**
         * Handelt es sich um einen Integer und wurde somit eine neue id vergeben ...
         */
        if (is_int($newId)) {
            /**
             * ... aktualisieren wir das aktuelle Objekt mit diesem Wert.
             */
            $this->id = $newId;
        }
    }

    /**
     * Damit diese abstrakte Klasse für alle Models verwendet werden kann, ist es hilfreich, berechnen zu können, welche
     * Tabelle vermutlich zu dem erweiternden Model gehört.
     *
     * @return string
     */
    public static function getTablenameFromClassname(): string
    {
        /**
         * Name der aufgerufenen Klasse abfragen.
         */
        $calledClass = get_called_class(); // bspw. App\Models\User

        /**
         * Hat die aufgerufene Klasse eine Konstante TABLENAME?
         */
        if (defined("$calledClass::TABLENAME")) {
            /**
             * Wenn ja, dann verwenden wir den Wert dieser Konstante als Tabellenname. Das ermöglicht uns einen Namen
             * für eine Tabelle anzugeben, wenn der Tabellenname nicht vom Klassennamen abgeleitet werden kann.
             */
            return $calledClass::TABLENAME;
        }

        /**
         * Wenn nein, dann holen wir uns den Namen der Klasse ohne Namespace, konvertieren ihn in Kleinbuchstaben und
         * fügen hinten ein s dran. So wird bspw. aus App\Models\Product --> products
         */
        $particles = explode('\\', $calledClass); // ['App', 'Models', 'User']
        $classname = array_pop($particles); // 'User'
        $tablename = strtolower($classname) . 's'; // 'users'

        /**
         * Berechneten Tabellennamen zurückgeben.
         */
        return $tablename;
    }
}
