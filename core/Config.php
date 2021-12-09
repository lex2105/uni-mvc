<?php

namespace Core;

/**
 * Class Config
 *
 * @package Core
 */
class Config
{

    /**
     * Config auslesen
     *
     * Config::get('database.host') => config/database.php['host']
     *
     * @param string $filenameAndKey Format: filename.arrayKey
     * @param mixed  $default        Wert der zurückgegeben wird, wenn das File oder der Index nicht gefunden wurden
     *
     * @return mixed
     */
    public static function get (string $filenameAndKey, mixed $default = null): mixed
    {
        /**
         * Dateiname und Config-Key aus dem $configString auslesen. Hier verwenden wir Array Destructuring um beide
         * Variablen direkt setzen zu können.
         */
        [$filename, $key] = explode('.', $filenameAndKey);

        /**
         * Config filename generieren
         */
        $path = __DIR__ . "/../config/$filename.php";

        /**
         * Existiert die gewünschte Config-Datei?
         */
        if (file_exists($path)) {

            /**
             * Config Datei einbinden. Hier müssen wir mit require arbeiten und nicht mit require_once, weil sonst nicht
             * zweimal hinter einander im selben Skript-Durchlauf Werte aus derselben Config Datei ausgelesen werden
             * könnten. Diese Lösung ist nicht ganz optimal und könnte eleganter umgesetzt werden, aber es ist für
             * unsere Zwecke ausreichend und einfach.
             */
            $config = require $path;

            /**
             * Wenn der Config-Key in dem entsprechenden File existiert, dann geben wir den Wert davon zurück, sonst
             * geben wir den Wert von $default zurück.
             */
            if (array_key_exists($key, $config)) {
                return $config[$key];
            }
        }

        /**
         * Standardwert zurückgeben, wenn das File nicht existiert oder der angegeben Key nicht existiert.
         */
        return $default;
    }

}
