<?php

namespace Core;

/**
 * Class Session
 *
 * Wir definieren deshalb eine eigene Klasse Session, damit wir überall, wo wir auf die Session zugreifen wollen diese
 * Wrapper-Klasse verwenden, damit die Session-Engine, also der Mechanismus, der die Daten dann speichert, ganz einfach
 * getauscht werden kann, ohne dass der ganze Code, der Sessions verwendet, umgebaut werden muss.
 *
 * @package Core
 */
class Session
{

    /**
     * Session starten
     */
    public static function init()
    {
        /**
         * Hier setzen wir den Namen des Session Cookie aus dem app-slug Value aus der app-Config.
         */
        session_name(Config::get('app.app-slug', 'mvc-session-cookie'));

        /**
         * Die session_start() Funktion erlaubt es, Config-Werte zu übergeben, unter anderem das Ablaufdatum des Session
         * Cookie. Das brauchen wir unter anderem dafür, wenn wir sowas wie eine RememberMe-Checkbox einbauen möchten
         * im Login.
         */
        session_start([
            'cookie_lifetime' => 60 * 60 * 24 * 90 // 90 Tage Cookie Lifetime
        ]);
    }

    /**
     * Wert in Session schreiben
     *
     * @param string $key
     * @param mixed  $value
     */
    public static function set(string $key, mixed $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Wert aus Session auslesen
     *
     * @param string $key
     * @param null   $default
     *
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }
        return $default;
    }

    /**
     * Wert aus Session löschen
     *
     * @param string $key
     */
    public static function forget(string $key)
    {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Wert aus der Session auslesen und danach löschen
     *
     * Das ist vor allem dann relevant, wenn wir beispielsweise Fehlermeldungen in die Session schreiben, die nur beim
     * nächsten Seitenaufruf angezeigt werden sollen und dann wieder aus der Session gelöscht werden. Wir könnten das
     * auch mit Session::get() und Session::forget() machen, aber so können wir das in einer Zeile machen. Das ist eine
     * reine Convenience Funktion.
     *
     * @param string $key
     * @param null   $default
     *
     * @return mixed
     */
    public static function getAndForget(string $key, mixed $default = null): mixed
    {
        $_value = self::get($key, $default);
        self::forget($key);
        return $_value;
    }

    /**
     * Diese Methode ermöglicht es uns auf die Werte, die in dem jeweils vorhergehenden Request in ein Formular
     * eingegeben wurden, zuzugreifen. Dadurch können wir Formularfelder mit Werten befüllen, wenn ein Fehler in der
     * Validierung auftritt, der/die User*in muss dann die Werte nicht nochmal eingeben, sondern lediglich korrigieren.
     *
     * @param string $key
     * @param null   $default
     *
     * @return mixed
     */
    public static function old(string $key, mixed $default = null): mixed
    {
        /**
         * Damit sowohl POST als auch GET Formular funktionieren, suchen wir über die $_REQUEST Superglobal in beiden
         * Datenbeständen und geben den Wert zurück, wenn der $key gefunden wurde.
         */
        if (isset($_SESSION['$_request'][$key])) {
            $_value = $_SESSION['$_request'][$key];
            unset($_SESSION['$_request'][$key]);
            return $_value;
        }

        /**
         * Andernfalls geben wir wie immer den $default zurück.
         */
        return $default;
    }

    /**
     * Hier setzen wir die Werte aus der $_REQUEST Superglobals in die Session, damit wir sie in der
     * old()-Methode wieder abrufen können. Das ganze dient dazu, dass Werte aus einem Formular, die nicht korrekt
     * Validiert werden konnten, nicht wieder komplett neu eingegeben werden, nachdem die Validierungsfehler angezeigt
     * wurden. Mit dieser Mechanik können Werte, die über ein Formular abgeschickt wurden, wieder in dem Formular
     * angezeigt werden, damit die fehlerhaften Eingaben korrigiert werden können.
     */
    public static function initSuperglobals()
    {
        /**
         * Wurden POST oder GET Daten übergeben, speichern wir sie in die Session.
         */
        if (!empty($_REQUEST)) {
            self::set('$_request', $_REQUEST);
        }
    }

}
