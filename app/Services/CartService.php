<?php

namespace App\Services;

use App\Models\Equipment;

/**
 * Cart Service
 *
 * Services sind üblicherweise Klassen, die Funktionalitäten beinhalten, die weder ein Controller noch ein Model sind.
 * Oft werden sie auch verwendet um Logik, die nicht zwangsläufig auf einen Controller beschränkt ist, wiederverwendbar
 * zu machen.
 */
class CartService
{
    /**
     * Wir definieren den Namen des Carts innerhalb der Session.
     */
    const SESSION_KEY = 'equipment-cart';

    /**
     * Equipment ins Cart hinzufügen.
     *
     * @param Equipment $equipment
     *
     * @return array
     */
    public static function add(Equipment $equipment): array
    {
        /**
         * Cart initialisieren.
         */
        self::init();

        /**
         * Gibt es das Equipment bereits im Cart ...
         */
        if (self::has($equipment)) {
            /**
             * ... so legen wir es ein weiteres Mal hinein, indem wir den aktuellen Counter um 1 erhöhen.
             */
            $_SESSION[self::SESSION_KEY][$equipment->id]++;
        } else {
            /**
             * Andernfalls legen wir es genau 1-mal hinein.
             */
            $_SESSION[self::SESSION_KEY][$equipment->id] = 1;
        }

        /**
         * Neuen Inhalt des Carts zurückgeben.
         */
        return self::get();
    }

    /**
     * Eine Einheit eines Equipments aus dem Cart entfernen.
     *
     * @param Equipment $equipment
     *
     * @return array
     */
    public static function remove(Equipment $equipment): array
    {
        /**
         * Cart initialisieren.
         */
        self::init();

        /**
         * Gibt es das Equipment im Cart ...
         */
        if (self::has($equipment)) {
            /**
             * ... so reduzieren wir es um 1.
             */
            $_SESSION[self::SESSION_KEY][$equipment->id]--;

            /**
             * Ist der Counter für ein Equipment im Cart auf 0 gefallen, so entfernen wir das Equipment aus dem Cart.
             */
            if ($_SESSION[self::SESSION_KEY][$equipment->id] <= 0) {
                self::removeAll($equipment);
            }
        }

        /**
         * Neuen Inhalt des Carts zurückgeben.
         */
        return self::get();
    }

    /**
     * Alle Einheiten eines Equipments aus dem Cart entfernen.
     *
     * @param Equipment $equipment
     *
     * @return array
     */
    public static function removeAll(Equipment $equipment): array
    {
        /**
         * Cart initialisieren.
         */
        self::init();

        /**
         * Gibt es das Equipment im Cart ...
         */
        if (self::has($equipment)) {
            /**
             * So entfernen wir alle Einheiten davon indem wir den entsprechenden Array-Key unsetten.
             */
            unset($_SESSION[self::SESSION_KEY][$equipment->id]);
        }

        /**
         * Neuen Inhalt des Carts zurückgeben.
         */
        return self::get();
    }

    /**
     * Inhalt des Carts ausgeben.
     *
     * @return array
     * @throws \Exception
     */
    public static function get(): array
    {
        /**
         * Cart initialisieren.
         */
        self::init();

        /**
         * Array vorbereiten.
         */
        $equipments = [];
        /**
         * Alle Einträge aus dem Cart durchgehen, ...
         */
        foreach ($_SESSION[self::SESSION_KEY] as $equipmentId => $number) {
            /**
             * ... jeweils das zugehörige Equipment aus der Datenbank laden, ...
             */
            $equipment = Equipment::findOrFail($equipmentId);
            /**
             * ... eine zusätzliche Property dynamisch hinzufügen, ...
             */
            $equipment->count = $number;
            /**
             * ... und "fertiges" Equipment Objekt in das vorbereitete Array speichern.
             */
            $equipments[] = $equipment;
        }

        /**
         * Liste aller Equipments aus dem Cart zurückgeben.
         */
        return $equipments;
    }

    /**
     * Anzahl der Elemente im Cart zurückgeben.
     *
     * @return int
     */
    public static function getCount(): int
    {
        /**
         * Cart initialisieren.
         */
        self::init();

        /**
         * Counter vorbereiten.
         */
        $count = 0;

        /**
         * Alle Einträge aus dem Cart durchgehen ...
         */
        foreach ($_SESSION[self::SESSION_KEY] as $equipmentId => $number) {
            /**
             * ... und die Anzahl pro Eintrag zum Counter hinzufügen.
             */
            $count = $count + $number;
        }

        /**
         * Ergebnis zurückgeben.
         */
        return $count;
    }

    /**
     * Cart vorbereiten.
     */
    private static function init()
    {
        /**
         * Existiert der als Klassenkonstante definierte Key noch nicht in der Session, erstellen wir ihn als leeres
         * Array.
         */
        if (!isset($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = [];
        }
    }

    /**
     * Convenience Function zur Prüfung, ob ein Equipment bereits im Cart liegt oder nicht.
     *
     * @param Equipment $equipment
     *
     * @return bool
     */
    private static function has(Equipment $equipment): bool
    {
        return isset($_SESSION[self::SESSION_KEY][$equipment->id]);
    }

    /**
     * Cart komplett aus der Session löschen.
     */
    public static function destroy()
    {
        if (isset($_SESSION[self::SESSION_KEY])) {
            unset($_SESSION[self::SESSION_KEY]);
        }
    }
}
