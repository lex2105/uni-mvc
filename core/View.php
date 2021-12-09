<?php

namespace Core;

/**
 * Views und Error Views laden
 */
class View
{

    /**
     * Diese Methode erlaubt es uns innerhalb der Controller der App (s. HomeController), einen View in nur einer
     * einzigen Zeile zu laden und auch Parameter an den View zu übergeben. Die View Parameter dienen dazu, dass Werte,
     * die in den Controllern berechnet wurden, an den View zur Darstellung übergeben werden können.
     *
     * Aufruf: View::render('ProductSingle', $productValues)
     *
     * @param string  $template
     * @param array   $params
     * @param ?string $layout
     * @param bool    $useCoreTemplates
     */
    public static function render(
        string $template,
        array $params = [],
        ?string $layout = null,
        bool $useCoreTemplates = false
    ) {
        /**
         * Standard-Layout laden, wenn kein $layout angegeben wurde.
         */
        if ($layout === null) {
            $layout = Config::get('app.default-layout', 'default');
        }

        /**
         * extract() erstellt aus jedem Wert in einem Array eine eigene Variable. Das brauchen wir aber nur zu tun, wenn
         * überhaupt $params vorhanden sind.
         */
        if (!empty($params)) {
            extract($params);
        }

        /**
         * Pfade vorbereiten, damit wir sie später verwenden können.
         */
        $viewBasePath = __DIR__ . '/../resources/views';
        $layoutsBasePath = "{$viewBasePath}/layouts";
        $templatesBasePath = "{$viewBasePath}/templates";

        /**
         * Wenn im Core mitgelieferte Templates geladen werden sollen, so definieren wir den TemplateBasePath neu.
         */
        if ($useCoreTemplates === true) {
            $templatesBasePath = __DIR__ . '/views';
        }

        /**
         * Nun definieren wir den Pfad zum Template, das geladen werden soll.
         */
        $templatePath = "$templatesBasePath/$template.php";

        /**
         * Soll ein Template aus dem Core geladen werden, so laden wir es direkt, andernfalls laden wir das Layout,
         * welches dann wiederum das Template über den $templatePath lädt.
         */
        if ($useCoreTemplates === true) {
            require_once $templatePath;
        } else {
            require_once "$layoutsBasePath/$layout.php";
        }
    }

    /**
     * Das ist eine Hilfsfunktion, damit $useCoreTemplate direkt true ist und auch ein HTTP Status Code gesetzt werden
     * kann.
     *
     * @param string  $template
     * @param array   $params
     * @param ?string $layout
     * @param bool    $useCoreTemplates
     * @param int     $httpResponseCode
     */
    public static function error(
        string $template = 'errors/exception',
        array $params = [],
        ?string $layout = null,
        bool $useCoreTemplates = true,
        int $httpResponseCode = 500
    ) {
        /**
         * Normalerweise gibt eine Website einen Status 200 OK zurück, wir wollen im Fehlerfall aber einen anderen
         * Status zurückgeben, beispielsweise 404 Not Found oder 500 Internal Server Error. Dazu erweitern wir mit
         * dieser Methode doe render()-Methode.
         */
        http_response_code($httpResponseCode);

        /**
         * Hier rufen wir dann die render()-Methode von oben auf und laden wie immer einfach einen View.
         */
        self::render($template, $params, $layout, $useCoreTemplates);
    }

}
