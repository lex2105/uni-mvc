<?php
/**
 * Diese Datei ist der Startpunkt der gesamten Anwendung. Sie startet das Routing und ermöglicht dadurch das Laden von
 * Controllern.
 */

/**
 * spl_autoload_register()-Funktion akzeptiert einen Parameter, eine Funktion. Diese Funktion wird aufgerufen, wenn eine
 * Klasse verwendet werden soll, die noch nicht importiert wurde. Dieser Funktion wird der komplette Klassenname inkl.
 * Namespace übergeben.
 */
spl_autoload_register(function ($namespaceAndClassname) {
    /**
     * Hier versuchen wir den Namespace in einen validen Dateipfad umzuwandeln. Daher ist es wichtig, dass der
     * Klassenname und der Dateiname ident sind.
     *
     * z.B.:
     * + Core\Bootstrap => core/Bootstrap.php
     * + App\Models\User => app/Models/User.php
     */
    $namespaceAndClassname = str_replace('Core', 'core', $namespaceAndClassname);
    $namespaceAndClassname = str_replace('App', 'app', $namespaceAndClassname);
    $filepath = str_replace('\\', '/', $namespaceAndClassname);

    /**
     * Die Magic Constant __DIR__ beinhaltet immer den Ordner der Datei, in der die __DIR__ Konstante aufgerufen wurde.
     * Sie beinhaltet einen absoluten Ordner-Pfad. Das hat den Sinn, dass wir von jeder Datei aus einen absoluten Pfad
     * generieren können, während wir aber trotzdem einen relativen Pfad angeben.
     *
     * Das Problem ist nämlich, das require und import (inkl. *_once) immer von der Datei ausgehen, die alle bisherigern
     * Dateien eingebunden hat. Wir könnten also alle require Aufrufe immer von der public/index.php aus angeben, aber
     * es ist erheblich einfacher korrekte Pfade zu produzieren, wenn man von der Datei ausgeht, in die man etwas
     * einbinden möchte.
     */
    require_once __DIR__ . "/../$filepath.php";
});

/**
 * Error Reporting einschalten.
 */
\Core\Bootloader::setDisplayErrors();

/**
 * MVC "anstarten"
 */
$app = new \Core\Bootloader();
