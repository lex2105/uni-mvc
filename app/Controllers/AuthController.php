<?php

namespace App\Controllers;

use App\Models\User;
use Core\Helpers\Redirector;
use Core\Session;
use Core\Validator;
use Core\View;

/**
 * Class AuthController
 *
 * @package App\Controllers
 */
class AuthController
{

    /**
     * Loin Formular anzeigen
     */
    public function loginForm()
    {
        /**
         * Wenn bereits ein*e User*in eingeloggt ist, zeigen wir das Login Formular nicht an, sondern leiten auf die
         * Startseite weiter.
         */
        if (User::isLoggedIn()) {
            Redirector::redirect('/home');
        }

        /**
         * Andernfalls laden wir das Login Formular.
         */
        View::render('auth/login');
    }

    /**
     * Daten aus Login Formular entgegennehmen und verarbeiten.
     */
    public function loginDo()
    {
        /**
         * 1) Username & Passwort ins Login Formular eingeben
         * 2) Remember Me Checkbox anhakerln (optional)
         * 3) Formular absenden
         * ---
         * 4) Gibts den/die User*in schon? ja: weiter, nein: Fehlermeldung
         * 5) Passwort aus DB abrufen (Salted Hashes)
         * 6) Passwort aus Eingabe und DB ident? ja: weiter, nein: Fehlermeldung
         * 7) "Remember Me" angehakerlt? ja: $exp=7, nein: $exp=0 (für die aktuelle Browser Session, bis der Tab
         * geschlossen wird)
         * 8) Session schreiben: logged_in=>true
         * 9) Redirect zu bspw. Dashboard/Home Seite/whatever
         */

        /**
         * User anhand einer Email-Adresse oder eines Usernames aus der Datenbank laden.
         * Diese Funktionalität kommt aus der erweiterten Klasse AbstractUser.
         */
        $user = User::findByEmailOrUsername($_POST['username-or-email']);

        /**
         * Fehler-Array vorbereiten
         */
        $errors = [];

        /**
         * Wurde ein*e User*in in der Datenbank gefunden und stimmt das eingegebene Passwort mit dem Passwort Hash
         * des/der User*in überein?
         *
         * Hier ist wichtig zu bedenken, dass wir nicht zwei unterschiedliche Fehlermeldungen ausgeben, damit wir nicht
         * einem Angreifer verraten, dass der Username richtig ist und nur das Passwort noch nicht. Dadurch wäre es
         * nämlich erheblich einfacher, das Passwort zu brute-forcen.
         */
        if (empty($user) || !$user->checkPassword($_POST['password'])) {
            /**
             * Wenn nein: Fehler!
             */
            $errors[] = 'Username/E-Mail oder Passwort sind falsch.';
        } else {
            /**
             * Wenn ja: weiter.
             */
            $user->login('/home');
        }

        /**
         * Fehler in die Session schreiben und zum Login zurückleiten. In die Session speichern wir deshalb, weil wir
         * im Login Formular nicht mehr auf die Variable $errors zugreifen können und daher eine Möglichkeit brauchen
         * über einen Request hinweg Daten zu speichern. Im Login Form laden wir die Fehler aus der Session, zeigen sie
         * an und löschen sie in der Session wieder.
         */
        Session::set('errors', $errors);
        Redirector::redirect('/login');
    }

    /**
     * Logout und redirect auf die Startseite durchführen.
     */
    public function logout()
    {
        User::logout('/');
    }

    /**
     * Registrierungsformular anzeigen
     */
    public function signupForm()
    {
        /**
         * Wenn bereits ein*e User*in eingeloggt ist, zeigen wir das Signup Formular nicht an, sondern leiten auf die
         * Startseite weiter.
         */
        if (User::isLoggedIn()) {
            Redirector::redirect('/home');
        }

        /**
         * Andernfalls laden wir das Registrierungsformular.
         */
        View::render('auth/signup');
    }

    /**
     * Daten aus dem Registrierungsformular entgegennehmen und verarbeiten.
     */
    public function signupDo()
    {
        /**
         * [x] Daten validieren
         * [x] erfolgreich: weiter, nicht erfolgreich: Fehler
         * [x] Gibts E-Mail oder Username schon in der DB?
         * [x] ja: Fehler, nein: weiter
         * [x] User Object aus den Daten erstellen & in DB speichern
         * [x] Weiterleiten zum Login
         */

        /**
         * Formulardaten validieren.
         */
        $validator = new Validator();
        $validator->email($_POST['email'], 'E-Mail', required: true);
        $validator->unique($_POST['email'], 'E-Mail', 'users', 'email');
        $validator->unique($_POST['username'], 'Username', 'users', 'username');
        $validator->password($_POST['password'], 'Passwort', min: 8, required: true);
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
         * Wenn der Fehler-Array nicht leer ist und es somit Fehler gibt ...
         */
        if (!empty($errors)) {
            /**
             * ... dann speichern wir sie in die Session, damit sie im View ausgegeben werden können und leiten dann
             * zurück zum Formular.
             */
            Session::set('errors', $errors);
            Redirector::redirect('/sign-up');
        }

        /**
         * Kommen wir an diesen Punkt, können wir sicher sein, dass die E-Mail Adresse und der Username noch nicht
         * verwendet werden und alle eingegebenen Daten korrekt validiert werden konnten.
         */
        $user = new User();
        $user->fill($_POST);
        $user->setPassword($_POST['password']);

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
            Session::set('success', ['Herzlich wilkommen!']);
            $user->login('/home');
        } else {
            /**
             * Fehlermeldung erstellen und in die Session speichern.
             */
            $errors[] = 'Leider ist ein Fehler aufgetreten. Bitte probieren Sie es erneut! :(';
            Session::set('errors', $errors);

            /**
             * Redirect zurück zum Registrierungsformular.
             */
            Redirector::redirect('/sign-up');
        }
    }

}
