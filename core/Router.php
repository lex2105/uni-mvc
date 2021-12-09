<?php

namespace Core;

/**
 * Der Router übernimmt die Übersetzung von URLs in Controllers und Actions.
 */
class Router
{

    /**
     * Wird alle Routen, die das System kennt, beinhalten.
     *
     * @var array
     */
    private array $routes = [];

    /**
     * Wird die Namen der Parameter für die gefundenen Route beinhalten.
     *
     * @var array
     */
    private array $paramNames = [];

    /**
     * Routen automatisiert laden
     */
    public function __construct()
    {
        $this->loadRoutes();
    }

    /**
     * Routen laden
     *
     * Nachdem routes/web.php und roues/api.php beide einfach nur ein Array returnen, wird dieser Wert als Return-Wert
     * für das require_once verwendet und kann somit direkt in Variablen gespeichert werden.
     */
    private function loadRoutes()
    {
        /**
         * Route-Files laden.
         */
        $webRoutes = require_once __DIR__ . '/../routes/web.php'; // bspw. /var/www/html/mvc/core/
        $apiRoutes = require_once __DIR__ . '/../routes/api.php';

        /**
         * Property setzen, damit die Routen in diesem Objekt immer überall verfügbar sind.
         */
        $this->routes = $webRoutes;

        /**
         * Alle API Routen durchgehen und prefixen.
         */
        foreach ($apiRoutes as $apiRoute => $callable) {
            /**
             * Allen API Routen /api/ voranstellen, damit sie eindeutig als API Routen erkennbar sind.
             */
            $route = "/api/$apiRoute";

            /**
             * Doppelte Slashes in allen API Routen entfernen, die durch das Prefixing entstanden sein könnten.
             */
            $route = str_replace('//', '/', $route);

            /**
             * "fertige" API Route zu den anderen Routes hinzufügen.
             */
            $this->routes[$route] = $callable;
        }
    }

    /**
     * $_GET['path'], die im .htaccess File definiert ist, verarbeiten und die richtige Controller/Action Kombination
     * aus den routes/*.php files suchen.
     */
    public function route()
    {
        /**
         * $_GET['path'] so umformen, dass immer ein führendes Slash dran steht und am Ende keines.
         * Das ist nötig, weil unsere Standard Route, wenn also kein spezieller Pfad eingegeben wurde, nur / ist, also
         * quasi ein führendes Slash mit nichts dahinter.
         */
        $path = '';
        if (isset($_GET['path'])) {
            $path = $_GET['path'];
        }

        /**
         * `rtrim()` entfernt eine Liste an Zeichen vom Ende eines Strings.
         *
         * Wenn kein Pfad übergeben wurde, ist unsere Standard Route einfach nur /.
         */
        $path = '/' . rtrim($path, '/');

        /**
         * Variablen initialisieren, damit wir sie später befüllen können.
         *
         * $callable wird dabei die in unseren Routes Files angegebene Kombination aus Klasse und Methodenname beinhalten.
         */
        $callable = [];
        $params = [];

        /**
         * Prüfen, ob der angefragte Pfad als Route in unseren Routen 1:1 vorkommt oder nicht.
         *
         * Kommt der angefragte Pfad 1:1 in einem unserer Routes Files vor, so impliziert das, dass kein Parameter in
         * der Route vorkommt und wir brauchen auch keinen Parameter auswerten, sondern können einfach direkt Controller
         * und Action auslesen.
         */
        if (array_key_exists($path, $this->routes)) {
            $callable = $this->routes[$path];
        } else {
            /**
             * Wurde die Route im if-Block nicht gefunden, so bedeutet das, dass ein Parameter in der Route vorkommt,
             * der im angefragten Pfad bereits mit einem Wert belegt ist. Wir müssen nun also alle Routen durchgehen und
             * prüfen, ob eine Route auf den angegebenen Pfad passt.
             */
            foreach ($this->routes as $route => $_callable) {
                /**
                 * Wenn eine Route eine geschwungene Klammer beinhaltet, gibt es einen Parameter und wir formen sie in
                 * eine valide Regular Expression um. Wenn Sie keine geschwungene Klammer beinhaltet, dann beinhaltet
                 * sie auch keinen Parameter, wurde im oben stehenden if-Block bereits abgedeckt und braucht nicht
                 * umgeformt zu werden.
                 */
                if (str_contains($route, '{')) {
                    /**
                     * Route in eine Regular Expression umformen.
                     */
                    $regex = $this->buildRegex($route);

                    /**
                     * Wird die einzelnen Treffer der Regular Expression beinhalten.
                     *
                     * s. https://www.php.net/manual/en/function.preg-match-all.php
                     */
                    $matches = [];

                    /**
                     * Hier prüfen wir, ob der angefragte Pfad auf die Route im aktuellen Schleifendurchlauf zutrifft.
                     *
                     * preg_match_all() gibt bei einer Übereinstimmung die Anzahl der Treffer zurück.
                     */
                    if (preg_match_all($regex, $path, $matches, PREG_SET_ORDER) >= 1) {
                        /**
                         * $_callable kommt aus der for-Schleife.
                         */
                        $callable = $_callable;

                        /**
                         * Damit wir die Parameter mit Namen und Werten extrahieren können, gehen wir alle in
                         * $this->buildRegex() aus der Route extrahierten Parameter Namen durch und holen den
                         * zugehörigen Wert aus $matches.
                         */
                        foreach ($this->paramNames as $paramName) {
                            $params[$paramName] = $matches[0][$paramName];
                        }

                        /**
                         * Zu diesem Zeitpunkt wurde ein Treffer in den Routen gefunden und Controller, Action und
                         * Parameter aufgelöst. `break` beendet nun die aktuelle Schleife, weil wir nur einen Treffer
                         * brauchen und jede weitere Berechnung daher sinnlos ist.
                         *
                         *  s. https://www.php.net/manual/en/control-structures.break.php
                         */
                        break;
                    }
                }
            }
        }

        /**
         * Wenn kein Controller gefunden wurde ...
         */
        if (empty($callable)) {
            /**
             * ... werfen wir eine Exception, die im Router gecatcht wird und über den ErrorHandler dann ausgegeben.
             */
            throw new \Exception('Not Found', 404);
        } else {
            /**
             * Controller und Action aus dem Callable laden und den Controller instanziieren.
             * $callable beinhaltete den Namen der Klasse inklusive Namespace als String und kann verwendet werden um
             * ein Objekt ohne fix definierten Klassennamen zu erzeugen, dynamisch also.
             */
            $controller = new $callable[0]();
            $action = $callable[1];

            /**
             * Nun rufen wir Controller und Action auf und verwenden den Spread Operator, weil wir mit einer
             * variablen Anzahl an Parametern umgehen müssen.
             */
            $controller->$action(...$params);
        }
    }

    /**
     * Valide Regular Expression aus einer Routen-Definition bauen.
     *
     * @param string $route
     *
     * @return string
     */
    private function buildRegex(string $route): string
    {
        /**
         * Um benannte Capture Groups erstellen zu können, müssen wir zunächst die Namen aller Parameter aus der Route
         * extrahieren. Das geht am einfachsten mit einer Regular Expression, die alles in der Form {xyz} sucht und den
         * Inhalt der Klammern in $matches schreibt.
         */
        $matches = [];
        preg_match_all('/{([a-zA-Z0-9]+)}/', $route, $matches);
        $this->paramNames = $matches[1];

        /**
         * Wir würden gerne den sehr einfachen Ausdruck aus unserem Routes File in eine valide Regular Expression
         * in der Form ^\/blog\/(?<paramName>[^\/]+)$ umformen. Dazu sind folgende Schritte notwenig:
         * - Slashes escapen (/ => \/)
         * - {param} mit einer Named Capture Group ersetzen
         * - Anfang und Ende des String setzen
         */
        $regex = str_replace('/', '\/', $route); // => '\/channels\/{id}'

        /**
         * Alle Parameter Namen durchgehen und innerhalb der $regex mit einer benannten Capture Group ersetzen, damit
         * am Ende eine valide Regular Expression raus kommt.
         */
        foreach ($this->paramNames as $paramName) {
            /**
             * {slug} => (?<slug>[^\/]+)
             * führt zu:
             * \/blog\/{slug} => \/blog\/(?<slug>[^\/]+)
             */
            $regex = str_replace("{{$paramName}}", "(?<$paramName>[^\/]+)", $regex); // => '\/channels\/(?<id>[^\/]+)'
        }

        /**
         * Anfang und Ende des Strings setzen und Regular Expression damit finalisieren.
         *
         * \/blog\/(?<slug>[^\/]+) => /^\/blog\/(?<slug>[^\/]+)$/
         */
        $regex = "/^$regex$/"; // => '/^\/channels\/(?<id>[^\/]+)$/'

        /**
         * Fertige Regular Expression zurückgeben
         */
        return $regex;
    }
}
