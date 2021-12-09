<?php

namespace Core;

/**
 * Class Validator
 *
 * @package Core
 */
class Validator
{

    /**
     * Definieren der string-basierten Datentypen, die validiert werden können.
     *
     * @var string[]
     */
    private array $types = [
        'letters' => '/^[a-zA-Zßäöü ]*$/i',
        'text' => '/^[a-zA-Zßäöü .,#\-_|;:?!]*$/i',
        'textnum' => '/^[\w\sßäöü .,#\-_|;:?!]*$/i',
        'alphanumeric' => '/^[^-_]{1}[a-zA-Z0-9-_]*$/',
        'checkbox' => '/^(on|true|checked|1)$/i',
        'password' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/',
        'email' => '/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix' // taken from: https://www.w3schools.in/php-script/email-validation-php-regular-expression/
    ];

    /**
     * Definieren der numerischen Datentypen, die validiert werden können. Hier wird auch definiert, mit welcher PHP
     * Funktion die Validierung durchgeführt werden soll.
     *
     * @var string[]
     */
    private array $numericTypes = [
        'numeric' => 'is_numeric',
        'int' => 'is_int',
        'float' => 'is_float'
    ];

    /**
     * Definieren der Fehlermeldungen für alle Datentypen oben. Hier wird immer ein %s-Platzhalter definiert, damit wir
     * später, wenn wir die Fehlermeldung verwenden, mit der sprintf()-Funktion das Label des Input Feldes einfügen
     * können.
     *
     * @var string[]
     */
    private array $errorMessages = [
        'letters' => '%s darf nur Buchstaben und Leerzeichen beinhalten.',
        'text' => '%s darf nur Buchstaben und Sonderzeichen beinhalten.',
        'textnum' => '%s darf nur aus alphanumerischen Zeichen bestehen.',
        'alphanumeric' => '%s darf nur Buchstaben, Zahlen, Binde- und Unterstriche beinhalten.',
        'checkbox' => '%s enthält keinen gültigen Wert für eine Checkbox.',
        'password' => '%s muss mindestens 8 Zeichen lang sein, Groß- und Kleinbuchstabe und Sonderzeichen enthalten.',
        'email' => '%s muss eine korrekte E-Mail Adresse sein.',

        'numeric' => '%s muss numerisch sein.',
        'int' => '%s muss ganzzahlig sein.',
        'float' => '%s muss eine Fließkommazahl sein.',

        'equals' => '%s muss ident sein mit %s.',
        'compare' => '%s und %s müssen ident sein.',
        'unique' => '%s wird bereits verwendet.',

        'required' => '%s ist ein Pflichtfeld.',
        'min' => '%s muss mindestens %s sein.',
        'min-string' => '%s muss mindestens %s Zeichen haben.',
        'max' => '%s muss kleiner oder gleich %s sein.',
        'max-string' => '%s darf maximal %s Zeichen haben.',

        'file-error' => 'Es konnten nicht alle Dateien aus %s hochgeladen werden.',
        'file-type' => '%s darf nur Dateien vom Typ "%s" beinhalten.',
        'file-size' => '%s darf nur Dateien bis zu %d MB beinhalten.'
    ];

    /**
     * Definieren einer Property, in die alle aufgetretenen Fehler gespeichert werden.
     *
     * @var string[]
     */
    private array $errors = [];

    /**
     * Hier wird die gesamte Validierung der Daten durchgeführt.
     *
     * Die __call() Magic Method wird aufgerufen, wenn eine nicht zugreifbare Methode aufgerufen wird. Das betrifft
     * Methoden, die private oder protected sind, oder Methoden, die nicht existieren. Das führt also dazu, dass wir
     * $validator->email("something") aufrufen können, und in wirklichkeit wird $validator->__call("email",
     * ["something"]) aufgerufen. Dadurch müssen wir nicht für alle string-basierten Datentypen eine eigene Methode
     * schreiben, sondern können ein und die selbe Methode für alle Typen schreiben und haben trotzdem hübsch benannte
     * Methoden bei der Verwendung des Validators zur Verfügung.
     *
     * @throws \Exception
     */
    public function __call($name, $arguments)
    {
        /**
         * Namen der aufgerufenen Funktion laden, der ident sein sollte mit einem der Types.
         */
        $type = $name;
        /**
         * Werte aus dem Arguments-Array laden und mit Standardwerten auffüllen.
         */
        [$value, $label, $required, $min, $max] = $this->mergeDefaults($arguments);

        /**
         * Validierungen ausführen. Diese Methoden schreiben ihre Fehler in $this->errors.
         */
        $this->validateRequired($required, $value, $label);

        /**
         * Validierungen sollten nur dann durchgeführt werden, wenn das Formularfeld required ist oder wenn Daten
         * eingegeben wurden.
         */
        if ((bool)$required === true || !empty($value)) {
            $this->validateMin($type, $min, $value, $label);
            $this->validateMax($type, $max, $value, $label);

            /**
             * Wenn es sich um einen numerischen Typ handelt, so prüfen wir nicht mit einer Regular Expression.
             */
            if ($this->isNumericType($type)) {
                $this->validateNumericType($type, $value, $label);
            } else {
                $this->validateWithRegex($type, $value, $label);
            }
        }
    }

    /**
     * Hier prüfen wir, ob der aufgerufenen $type einer der oben definierten numericTypes ist.
     *
     * @param string $type
     *
     * @return bool
     */
    private function isNumericType(string $type): bool
    {
        return array_key_exists($type, $this->numericTypes);
    }

    /**
     * Prüfe, ob ein
     *
     * @param string $value
     * @param string $label
     * @param string $table
     * @param string $column
     * @param int    $ignoreThisId Soll ein Eintrag aktualisiert werden, so muss die Unique-Prüfung auf alle anderen
     *                             Einträge ausgeführt werden und den aktuellen Eintrag ignorieren, da sonst ein
     *                             Validierungsfehler passieren würde, wenn der Wert in der Unique-Spalte für den zu
     *                             aktualisierenden Eintrag nicht geändert werden soll.
     *
     * @return bool
     */
    public function unique(string $value, string $label, string $table, string $column, int $ignoreThisId = 0): bool
    {
        /**
         * Datenbankverbindung herstellen.
         */
        $database = new Database();

        /**
         * Datenbank-Query bauen und counten, ob es schon ein Element mit $value in $column gibt.
         */
        $result = $database->query("SELECT COUNT(*) AS count FROM $table WHERE $column = ? AND id != ?", [
            's:value' => $value,
            'i:id' => $ignoreThisId
        ]);

        /**
         * Gibt es schon ein Element in der Datenbank, so counten wir mindestens 1 und schreiben daher einen Fehler.
         */
        if ($result[0]['count'] >= 1) {
            $this->errors[] = sprintf($this->errorMessages['unique'], $label);
            return false;
        }

        /**
         * Andernfalls geben wir true zurück.
         */
        return true;
    }

    /**
     * Hier vergleichen wir zwei Werte miteinander. Das ist für Passwort und Passwort wiederholen Felder sehr praktisch.
     *
     * @param array $valueAndLabel1 [$wert, $label]
     * @param array $valueAndLabel2 [$wert, $label]
     *
     * @return bool
     */
    public function compare(array $valueAndLabel1, array $valueAndLabel2): bool
    {
        /**
         * Werte aus den beiden Arrays extrahieren.
         */
        [$value1, $label1] = $valueAndLabel1;
        [$value2, $label2] = $valueAndLabel2;

        /**
         * Stimmen die Werte nicht überein, so schrieben wir einen Fehler und geben false zurück.
         */
        if ($value1 !== $value2) {
            $this->errors[] = sprintf($this->errorMessages['compare'], $label1, $label2);
            return false;
        }

        /**
         * Andernfalls geben wir true zurück.
         */
        return true;
    }

    /**
     * Hochgeladene Dateien validieren.
     *
     * @param array       $files
     * @param string      $label
     * @param string|null $type
     * @param int|null    $maxFileSize
     *
     * @return bool
     */
    public function file(array $files, string $label, ?string $type = null, ?int $maxFileSize = null): bool
    {
        /**
         * Wurde eine Datei hochgeladen?
         */
        if ($files['error'][0] !== UPLOAD_ERR_NO_FILE) {
            /**
             * Fallback für die max-upload-size holen, falls der Parameter nicht gesetzt wurde.
             */
            if (empty($maxFileSize)) {
                $maxFileSize = Config::get('app.max-upload-size', 0);
            }

            /**
             * Alle Uploads durchgehen.
             */
            foreach ($files['name'] as $index => $filename) {
                /**
                 * Ist ein Upload-Fehler aufgetreten, schreiben wir einen Fehler.
                 */
                if ($files['error'][$index] !== UPLOAD_ERR_OK) {
                    $this->errors[] = sprintf($this->errorMessages['file-error'], $label);
                    return false;
                }

                /**
                 * Ist ein Typ definiert und stimmt dieser Type aber nicht überein, schreiben wir einen Fehler.
                 */
                if (!empty($type)) {
                    $_type = $files['type'][$index];
                    if (!str_starts_with($_type, $type)) {
                        $this->errors[] = sprintf($this->errorMessages['file-type'], $label, $type);
                        return false;
                    }
                }

                /**
                 * Überschreitet die Datei das Upload-Limit, schreiben wir einen Fehler.
                 */
                if (!empty($maxFileSize)) {
                    $_filesize = $files['size'][$index];
                    if ($_filesize > $maxFileSize) {
                        $this->errors[] = sprintf($this->errorMessages['file-size'], $label, $maxFileSize / 1024 / 1024);
                        return false;
                    }
                }
            }
        }

        /**
         * Ist bisher kein Fehler aufgetreten, war die Validierung erfolgreich.
         */
        return true;
    }

    /**
     * Diese Funktion hilft uns dabei, Standardwerte für alle Parameter in $arguments aus __call() zu setzen. Das ist
     * nötig, weil wir normalerweise Standardwerte für optionale Paramater direkt in der Funktion definieren können. Die
     * __call()-Methode erhält die Funktionsparameter aber als ein Array $arguments, wodurch wir die Funktionalität für
     * optionale Werte selbst bauen müssen.
     *
     * @param array $arguments
     *
     * @return array
     */
    private function mergeDefaults(array $arguments): array
    {
        /**
         * Standardwerte definieren.
         */
        $defaults = [
            0 => 'text',
            'label' => 'Feld',
            'required' => false,
            'min' => null,
            'max' => null
        ];

        /**
         * Finales Array vorbereiten.
         */
        $mergedArguments = [];

        /**
         * Nun gehen wir alle Standardwerte durch ...
         *
         * Hier definieren wir auch noch eine Zählervariable $1, weil für Array Destructuring weiter oben in dem File
         * ein numerisches Array nötig ist.
         */
        $i = 0;
        foreach ($defaults as $index => $value) {
            /**
             * ... und prüfen, ob an der selben Stelle im $arguments Array ein Wert steht.
             */
            if (isset($arguments[$index])) {
                /**
                 * Wenn ja, dann verwenden wir diesen Wert aus $arguments für $mergedArguments.
                 */
                $mergedArguments[$i] = $arguments[$index];
            } else {
                /**
                 * Wenn nein, verwenden wir den Wert aus $defaults, der durch die Schleife in $value liegt.
                 */
                $mergedArguments[$i] = $defaults[$index];
            }
            /**
             * Zähler erhöhen.
             */
            $i++;
        }

        /**
         * Nun geben wir das fertige Array zurück, das Werte aus $arguments enthält und überall dort, wo keine Werte
         * übergeben wurden, weil sie optional waren, enthält es die Werte aus $defaults.
         */
        return $mergedArguments;
    }

    /**
     * Prüfen, ob ein Pflichtfeld ausgefüllt wurde.
     *
     * @param bool   $required
     * @param mixed  $value
     * @param string $label
     *
     * @return bool
     */
    private function validateRequired(bool $required, mixed $value, string $label): bool
    {
        /**
         * Wenn ein Feld verpflichtend ist, aber empty, schreiben wir einen Fehler und geben false zurück.
         */
        if ($required === true && empty($value)) {
            $this->errors[] = sprintf($this->errorMessages['required'], $label);
            return false;
        }
        /**
         * Andernfalls geben wir true zurück.
         */
        return true;
    }


    /**
     * Prüfen, ob der Mindestwert unterschritten wurde.
     *
     * @param string $type
     * @param mixed  $min
     * @param mixed  $value
     * @param string $label
     *
     * @return bool
     */
    private function validateMin(mixed $type, ?int $min, mixed $value, mixed $label): bool
    {
        /**
         * Wurde $min gesetzt ...
         */
        if ($min !== null) {
            /**
             * ... so prüfen wir für numerische Typen direkt ...
             */
            if ($this->isNumericType($type) && $value < $min) {
                $this->errors[] = sprintf($this->errorMessages['min'], $label, $min);
                return false;
            }

            /**
             * ... und für string-basierte Typen die Länge des Strings.
             *
             * In beiden Fällen schreiben wir einen Fehler und geben false zurück im Fehlerfall.
             */
            if (!$this->isNumericType($type) && strlen($value) < $min) {
                $this->errors[] = sprintf($this->errorMessages['min-string'], $label, $min);
                return false;
            }
        }

        /**
         * Im Erfolgsfall geben wir true zurück.
         */
        return true;
    }

    /**
     * S. this->validateMin() nur umgekehrt.
     *
     * @param mixed  $type
     * @param mixed  $max
     * @param mixed  $value
     * @param string $label
     *
     * @return bool
     */
    private function validateMax(mixed $type, ?int $max, mixed $value, mixed $label): bool
    {
        if ($max !== null) {
            if ($this->isNumericType($type) && $value > $max) {
                $this->errors[] = sprintf($this->errorMessages['max'], $label, $max);
                return false;
            }

            if (!$this->isNumericType($type) && strlen($value) > $max) {
                $this->errors[] = sprintf($this->errorMessages['max-string'], $label, $max);
                return false;
            }
        }
        return true;
    }

    /**
     * Prüfen, ob der Wert auf die oben definierte Regex zutrifft.
     *
     * @param mixed  $type
     * @param mixed  $value
     * @param string $label
     *
     * @return bool
     * @throws \Exception
     */
    private function validateWithRegex(string $type, mixed $value, string $label): bool
    {
        /**
         * Ist der gewünschte $type nicht in $this->types definiert, so liegt ein schwerer Fehler in der Programmierung
         * vor und wir werfen einen Exception (s. https://www.php.net/manual/de/class.exception.php).
         */
        if (!array_key_exists($type, $this->types)) {
            throw new \Exception("Type $type does not exists in Validator.");
        }

        /**
         * Nun holen wir uns die Regular Expression und prüfen mit der preg_match()-Funktion.
         */
        $typeRegex = $this->types[$type];
        if (preg_match($typeRegex, $value) !== 1) {
            /**
             * Findet preg_match() keinen Treffer oder tritt ein Fehler auf, so schreiben wir einen Fehler und geben
             * false zurück.
             */
            $this->errors[] = sprintf($this->errorMessages[$type], $label);
            return false;
        }

        /**
         * Im Erfolgsfall geben wir true zurück.
         */
        return true;
    }

    /**
     * Numerische Typen werden nicht über Regular Expression validiert, sondern mit den passenden PHP Funktionen.
     *
     * @param mixed $type
     * @param mixed $value
     * @param mixed $label
     *
     * @return bool
     */
    private function validateNumericType(string $type, mixed $value, string $label): bool
    {
        /**
         * Hier holen wir uns den Namen der Funktion, mit der validiert werden soll.
         */
        $typeFunction = $this->numericTypes[$type];

        /**
         * Dann rufen wir diese Variable als Funktion auf und schreiben im Fehlerfall einen Fehler und geben false zurück.
         */
        if (!$typeFunction($value)) {
            $this->errors[] = sprintf($this->errorMessages[$type], $label);
            return false;
        }

        /**
         * Im Erfolgsfall geben wir true zurück.
         */
        return true;
    }

    /**
     * Hierbei handelt es sich um eine praktische Hilfsfunktion, mit der wir ganz einfach prüfen können, ob im Zuge der
     * Validierung Fehler aufgetreten sind.
     *
     * @return bool
     */
    public function hasErrors(): bool
    {
        if (empty($this->errors)) {
            return false;
        }
        return true;
    }

    /**
     * Nachdem $this->errors private ist, damit von außerhalb des Validators nicht darauf zugegriffen werden kann,
     * müssen wir irgendwie die Möglichkeit schaffen, die Fehler doch außerhalb zu bekommen. Daher definieren wir hier
     * einen einfachen Getter.
     *
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

}
