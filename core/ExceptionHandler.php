<?php

namespace Core;

/**
 * Der ExceptionHandler nimmt alle Exceptions entgegen und gibt einen Fehler aus.
 */
class ExceptionHandler
{

    /**
     * Fehler in einem catch-Block entgegennehmen und exception view laden.
     *
     * @param \Exception $exception
     */
    public static function handle(\Exception $exception)
    {
        // Log Eintrag schreiben
        // ...

        /**
         * Hier laden wir einen core View und übergeben Werte aus der Exception.
         */
        View::error('errors/exception', [
            'code' => $exception->getCode(),
            'message' => $exception->getMessage()
        ], httpResponseCode: $exception->getCode() );
    }

}
