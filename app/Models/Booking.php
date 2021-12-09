<?php

namespace App\Models;

use Core\Database;
use Core\Models\AbstractModel;
use Core\Models\DateTime;
use Core\Traits\SoftDelete;

class Booking extends AbstractModel
{

    use SoftDelete;

    public function __construct(
        /**
         * Wir verwenden hier Constructor Property Promotion, damit wir die ganzen Klassen Eigenschaften nicht 3 mal
         * angeben müssen.
         *
         * Im Prinzip definieren wir alle Spalten aus der Tabelle mit dem richtigen Datentyp.
         */
        public ?int $id = null,
        public ?int $user_id = null,
        public ?string $time_from = null,
        public ?string $time_to = null,
        public string $foreign_table = '',
        public ?int $foreign_id = null,
        public string $created_at = '',
        public string $updated_at = '',
        public ?string $deleted_at = null
    ) {
    }

    /**
     * Objekt speichern.
     *
     * Wenn das Objekt bereits existiert hat, so wird es aktualisiert, andernfalls neu angelegt. Dadurch können wir eine
     * einzige Funktion verwenden und müssen uns nicht darum kümmern, ob das Objekt angelegt oder aktualisiert werden
     * muss.
     *
     * @return bool
     */
    public function save(): bool
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
         * Hat das Objekt bereits eine id, so existiert in der Datenbank auch schon ein Eintrag dazu und wir können es
         * aktualisieren.
         */
        if (!empty($this->id)) {
            /**
             * Query ausführen und Ergebnis direkt zurückgeben. Das kann entweder true oder false sein, je nachdem ob
             * der Query funktioniert hat oder nicht.
             */
            $result = $database->query(
                "UPDATE $tablename SET user_id = ?, time_from = ?, time_to = ?, foreign_table = ?, foreign_id = ? WHERE id = ?",
                [
                    'i:user_id' => $this->user_id,
                    's:time_from' => $this->time_from,
                    's:time_to' => $this->time_to,
                    's:foreign_table' => $this->foreign_table,
                    'i:foreign_id' => $this->foreign_id,
                    'i:id' => $this->id
                ]
            );

            return $result;
        } else {
            /**
             * Hat das Objekt keine id, so müssen wir es neu anlegen.
             */
            $result = $database->query(
                "INSERT INTO $tablename SET user_id = ?, time_from = ?, time_to = ?, foreign_table = ?, foreign_id = ?",
                [
                    'i:user_id' => $this->user_id,
                    's:time_from' => $this->time_from,
                    's:time_to' => $this->time_to,
                    's:foreign_table' => $this->foreign_table,
                    'i:foreign_id' => $this->foreign_id,
                ]
            );

            /**
             * Ein INSERT Query generiert eine neue id, diese müssen wir daher extra abfragen und verwenden daher die
             * von uns geschrieben handleInsertResult()-Methode, die über das AbstractModel verfügbar ist.
             */
            $this->handleInsertResult($database);

            /**
             * Ergebnis zurückgeben. Das kann entweder true oder false sein, je nachdem ob der Query funktioniert hat
             * oder nicht.
             */
            return $result;
        }
    }

    /**
     * Booking anhand eines Raumes und Start- und Endzeit suchen.
     *
     * @param int      $roomId
     * @param DateTime $startDate
     * @param DateTime $endDate
     *
     * @return array
     */
    public static function findByRoomAndDate(int $roomId, DateTime $startDate, DateTime $endDate): array
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
        $result = $database->query(
            "SELECT * FROM $tablename WHERE foreign_table = ? AND foreign_id = ? AND time_from >= ? AND time_to <= ?",
            [
                's:foreign_table' => Room::class,
                'i:foreign_id' => $roomId,
                's:time_from' => $startDate,
                's:time_to' => $endDate
            ]
        );

        /**
         * Datenbankergebnis verarbeiten und zurückgeben.
         */
        return self::handleResult($result);
    }

    /**
     * Prüfen, ob es bereits Bookings für einen Raum in einem bestimmten Timeslot gibt.
     *
     * @param int      $roomId
     * @param DateTime $startDate
     * @param DateTime $endDate
     *
     * @return bool
     */
    public static function existsForRoomAndTime(int $roomId, DateTime $startDate, DateTime $endDate): bool
    {
        /**
         * Wir verwenden die findByRoomAndDate()-Methode und prüfen, ob das Ergebnis leer ist oder nicht.
         */
        return !empty(self::findByRoomAndDate($roomId, $startDate, $endDate));
    }

    /**
     * Alle Raumbuchungen zu einem Account finden.
     *
     * @param ?int $userId
     *
     * @return array
     */
    public static function findRoomBookingsByUser(?int $userId)
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
         * Beachte hier den Filter auf die polymorphe Beziehung mit foreign_table.
         */
        $result = $database->query(
            "SELECT * FROM $tablename WHERE foreign_table = ? AND user_id = ? AND time_to >= NOW() ORDER BY time_from ASC",
            [
                's:foreign_table' => Room::class,
                'i:user_id' => $userId,
            ]
        );

        /**
         * Datenbankergebnis verarbeiten und zurückgeben.
         */
        return self::handleResult($result);
    }

    /**
     * Alle Raumbuchungen zu einem Account finden.
     *
     * @param ?int $userId
     *
     * @return array
     */
    public static function findEquipmentBookingsByUser(?int $userId)
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
         * Beachte hier den Filter auf die polymorphe Beziehung mit foreign_table.
         */
        $result = $database->query(
        /**
         * Groupen wäre elegant, aber damit kann leider die handleResult()-Methode unseres AbstractModel nicht umgehen:
         *
         * "SELECT *, COUNT(*) as units FROM $tablename WHERE foreign_table = ? AND user_id = ? GROUP BY foreign_id",
         */
            "SELECT * FROM $tablename WHERE foreign_table = ? AND user_id = ?",
            [
                's:foreign_table' => Equipment::class,
                'i:user_id' => $userId,
            ]
        );

        /**
         * Datenbankergebnis verarbeiten und zurückgeben.
         */
        return self::handleResult($result);
    }

    /**
     * Das buchbare Objekt zum aktuellen Booking laden.
     *
     * Ein Booking Objekt kann entweder einen Raum oder ein Equipment als buchbares Objekt (bookable Object)
     * referenzieren. Hier holen wir es, egal welchen Typ das bookable hat.
     *
     * @return object|null
     */
    public function bookable()
    {
        /**
         * Die erste runde Klammer beinhaltet einen kompletten Namespace+Klassennamen als String und somit können wir
         * direkt eine static function von dieser Klasse aufrufen, ohne vorher erst eine Variable dafür zu erstellen.
         */
        return ($this->foreign_table)::find($this->foreign_id);
    }

    /**
     * Begin-Zeit der Buchung als String formatieren.
     *
     * @return string
     * @throws \Exception
     */
    public function getTimeFromFormatted()
    {
        /**
         * Die erste runde Klammer erstellt ein neues \Core\DateTime Objekt und ruft dann direkt die format()-Methode
         * davon auf. Das hat den Sinn, dass wir nicht eine Variable dafür erstellen müssen und dann die
         * format()-Methode auf die Variable ausführen. Es hätte denselben Effekt, aber wir sparen uns eine Variable,
         * da wir ohnehin nur ein einziges Statement in dieser Funktion haben.
         */
        return (new DateTime($this->time_from))->format(DateTime::HUMAN_READABLE_AT_FROM);
    }

    /**
     * End-Zeit der Buchung als String formatieren.
     *
     * @return string
     * @throws \Exception
     */
    public function getTimeToFormatted()
    {
        /**
         * Die erste runde Klammer erstellt ein neues \Core\DateTime Objekt und ruft dann direkt die format()-Methode
         * davon auf. Das hat den Sinn, dass wir nicht eine Variable dafür erstellen müssen und dann die
         * format()-Methode auf die Variable ausführen. Es hätte denselben Effekt, aber wir sparen uns eine Variable,
         * da wir ohnehin nur ein einziges Statement in dieser Funktion haben.
         */
        return (new DateTime($this->time_to))->format(DateTime::HUMAN_READABLE_AT_TO);
    }
}
