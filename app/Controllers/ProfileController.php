<?php

namespace App\Controllers;

use App\Models\User;
use Core\Helpers\Redirector;
use Core\Middlewares\AuthMiddleware;
use Core\Session;
use Core\Validator;
use Core\View;

/**
 * Profile Controller
 */
class ProfileController
{

    /**
     * Wir können die AuthMiddleware auch im Konstruktor aufrufen, wenn alle Actions dieselbe Methode der AuthMiddleware
     * aufgerufen hätten.
     *
     * @throws \Exception
     */
    public function __construct()
    {
        AuthMiddleware::isLoggedInOrFail();
    }

    /**
     * Benutzer*innen-Profil anzeigen
     *
     * @throws \Exception
     */
    public function profile()
    {
        /**
         * Eingeloggte*n User*in aus der DB holen.
         */
        $user = User::getLoggedIn();

        /**
         * View laden und Daten übergeben.
         */
        View::render('profile/index', [
            'user' => $user
        ]);
    }

    /**
     * Formulardaten entgegennehmen und Bernutzer*innen-Profil aktualisieren.
     *
     * @throws \Exception
     */
    public function update()
    {
        /**
         * 1) Daten validieren
         * 2) User, der aktualisiert werden soll, aus DB laden
         * 3) Email-Adresse im User Objekt aktualisieren
         * 4) Soll das Passwort aktualisiert werden? Wenn ja: Passwort im User Objekt aktualisieren; wenn nein: weiter
         * 5) User Objekt in Datenbank zurückspeichern
         * 6) Redirect zum Profil
         */

        /**
         * Eingeloggte*n User*in aus der DB holen.
         */
        $user = User::getLoggedIn();

        /**
         * Daten validieren.
         */
        $validator = new Validator();
        $validator->email($_POST['email'], 'E-Mail', required: true);
        $validator->unique($_POST['email'], 'E-Mail', 'users', 'email', ignoreThisId: $user->id);
        $validator->password($_POST['password'], 'Passwort', min: 8, required: false);
        /**
         * Das Feld 'password_repeat' braucht nicht validiert werden, weil wenn 'password' ein valides Passwort ist und
         * alle Kriterien erfüllt, und wir hier nun prüfen, ob 'password' und 'password_repeat' ident sind, dann ergibt
         * sich daraus, dass auch 'password_repeat' ein valides Passwort ist.
         */
        $validator->compare([
            $_POST['password'],
            'Passwort'
        ], [
            $_POST['password_repeat'],
            'Passwort wiederholen'
        ]);

        /**
         * Fehler aus dem Validator auslesen. Validator::getErrors() gibt uns dabei in jedem Fall ein Array zurück,
         * wenn keine Fehler aufgetreten sind, ist dieses Array allerdings leer.
         */
        $errors = $validator->getErrors();

        /**
         * Wenn der Fehler-Array nicht leer ist und es somit Fehler gibt, ...
         */
        if (!empty($errors)) {
            /**
             * ... dann speichern wir sie in die Session, damit sie im View ausgegeben werden können und leiten dann
             * zurück zum Formular.
             */
            Session::set('errors', $errors);
            Redirector::redirect('/profile');
        }

        /**
         * Kommen wir an diesen Punkt, können wir sicher sein, dass die E-Mail-Adresse und der Username noch nicht
         * verwendet werden und alle eingegebenen Daten korrekt validiert werden konnten.
         */
        $user->email = trim($_POST['email']);
        if (!empty($_POST['password'])) {
            $user->setPassword($_POST['password']);
        }

        /**
         * Neue*n User*in in die Datenbank speichern.
         *
         * Die User::save() Methode gibt true zurück, wenn die Speicherung in die Datenbank funktioniert hat.
         */
        if ($user->save()) {
            /**
             * Hat alles funktioniert und sind keine Fehler aufgetreten, leiten wir zum Login Formular.
             *
             * Um eine Erfolgsmeldung ausgeben zu können, verwenden wir dieselbe Mechanik wie für die errors.
             */
            Session::set('success', ['Profil erfolgreich aktualisiert.']);
        } else {
            /**
             * Fehlermeldung erstellen und in die Session speichern.
             */
            $errors[] = 'Leider ist ein Fehler aufgetreten. Bitte probieren Sie es erneut! :(';
            Session::set('errors', $errors);
        }

        /**
         * Redirect zurück zum Profile.
         */
        Redirector::redirect('/profile');
    }

}
