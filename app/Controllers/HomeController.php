<?php

namespace App\Controllers;

use App\Models\Room;
use Core\View;

/**
 * Beispiel Controller
 */
class HomeController
{

    /**
     * Beispielmethode
     */
    public function index()
    {
        View::render('index', ['foo' => 'bar']);
    }


    /**
     * Alle Räume und Equipment auflisten
     */
    public function home()
    {
        /**
         * Alle Räume aus der Datenbank laden und von der Datenbank sortieren lassen.
         */
        $rooms = Room::all('room_nr', 'ASC');

        /**
         * View laden und Daten übergeben.
         */
        View::render('home', [
            'rooms' => $rooms
        ]);
    }

}
