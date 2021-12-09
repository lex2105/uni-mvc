<?php

namespace Core;

/**
 * Class Bootloader
 *
 * @package Core
 */
class Bootloader
{

    public function __construct()
    {
        /**
         * Session starten
         */
        Session::init();
        /**
         * Daten aus den $_GET und $_POST Superglobals in die Session speichern. Das wird benötigt, damit die old()-
         * Methode der Session Core Klasse funktioniert.
         */
        Session::initSuperglobals();

        /**
         * Damit wir nicht bei jedem Redirect die baseurl aus der Config laden müssen, erstellen wir hier eine Hilfskonstante.
         */
        define('BASE_URL', Config::get('app.baseurl', 'http://localhost:8080'));

        /**
         * Ein try-catch-Block ermöglicht es uns Code auszuführen und eine Exception, die in diesem Code auftritt
         * abzufangen, ohne dass der Skriptdurchlauf abgebrochen wird.
         */
        try {
            /**
             * Hier erstellen wir einen neuen Router und starten dann das Routing.
             */
            $router = new Router();
            $router->route();

        } catch (\Exception $exception) {
            /**
             * Ist innerhalb des try-Blocks eine Exception aufgetreten (auch innerhalb von Funktionen, die in dem Block
             * aufgerufen wurden), so wird diese Exception hier an unseren ErrorHandler übergeben. Dadurch haben wir
             * einen einzigen Handler für alle Exceptions, die in Controllern auftreten könnten.
             *
             * Exceptions selbst sind Fehler im System, die das System in einen Zustand bringen, an dem es nicht mehr
             * weiter kommt. Wenn keine Route gefunden wird, kann man drüber diskutieren, aber eine Umsetzung als
             * Exception ist meines Erachtens nach durchaus legitim.
             */
            ExceptionHandler::handle($exception);
        }
    }

    /**
     * Je nach Umgebung, welche Umgebung (dev/prod) gerade konfiguriert ist, schalten wir das error reporting ein oder
     * aus.
     */
    public static function setDisplayErrors()
    {
        /**
         * Config aus dem app.php Config File auslesen
         */
        $environment = Config::get('app.environment', 'prod');

        /**
         * Wenn grade die dev Environment konfiguriert ist ...
         */
        if ($environment === 'dev') {
            /**
             * ... zeigen wir alle Fehler an.
             *
             * Hier werden zwei PHP Einstellungen überschrieben, die in der php.ini Datei konfiguriert sind.
             */
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            /**
             * E_ALL ist eine von PHP mitgelieferte Konstante zur Konfiguration. Hier wird definiert, dass wir ALLE
             * Fehler angezeigt bekommen möchten.
             */
            error_reporting(E_ALL);
        }
    }

}
