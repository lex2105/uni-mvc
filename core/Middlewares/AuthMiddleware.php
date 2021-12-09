<?php

namespace Core\Middlewares;

use App\Models\User;
use JetBrains\PhpStorm\Pure;

/**
 * Class AuthMiddleware
 *
 * @package Core\Middlewares
 */
class AuthMiddleware
{

    /**
     * Prüfen, ob der/die eingeloggte User*in ein Admin ist.
     *
     * @return bool|null
     * @throws \Exception
     */
    public static function isAdmin(): ?bool
    {
        /**
         * Hier verwenden wir den Nullsafe Operator (?). Dadurch wird kein Fehler auftreten, wenn kein*e User*in
         * eingeloggt und getLoggedIn() somit keine*n User*in zurückgibt und somit dieser leere Rückgabewert auch keine
         * Property is_admin hat. Der Nullsafe Operator wird einfach den Wert des gesamten Ausdrucks auf null setzen.
         */
        return User::getLoggedIn()?->is_admin;
    }

    /**
     * Prüfen, ob der/die eingeloggte User*in ein Admin ist und andernfalls Fehler 403 Forbidden zurückgeben.
     */
    public static function isAdminOrFail()
    {
        /**
         * Prüfen, ob der/die aktuell eingeloggt User*in Admin ist.
         */
        $isAdmin = self::isAdmin();

        /**
         * Wenn nein, werfen wir einen Fehler 403 Forbidden und brechen damit die Ausführung ab. Der ExceptionHandler
         * kümmert sich um die Anzeige des Fehlers.
         */
        if ($isAdmin !== true) {
            throw new \Exception('Forbidden', 403);
        }
    }

    /**
     * Prüfen, ob eine Person eingeloggt ist.
     *
     * @throws \Exception
     */
    public static function isLoggedInOrFail()
    {
        /**
         * Prüfen, ob der/die aktuell eingeloggt User*in Admin ist.
         */
        $isLoggedIn = User::isLoggedIn();

        /**
         * Wenn nein, werfen wir einen Fehler 401 Unauthorized und brechen damit die Ausführung ab. Der ExceptionHandler
         * kümmert sich um die Anzeige des Fehlers.
         */
        if ($isLoggedIn !== true) {
            throw new \Exception('Unauthorized', 401);
        }
    }

    /**
     * Alias für User::isLoggedIn()
     *
     * @return bool
     */
    #[Pure] public static function isLoggedIn () {
        return User::isLoggedIn();
    }

}
