<?php

namespace App\Controllers;

/**
 * Beispiel Controller
 */
class ChannelController
{

    /**
     * Beispiel Methode
     */
    public function index()
    {
        echo "ChannelController:index";
    }

    /**
     * Beispiel Methode mit Parameter. Dieser Parameter wird über unser Routing mit Werten aus der URL befüllt.
     * @param int $id
     */
    public function show(int $id)
    {
        echo "ChannelController:show - id: $id";
    }

}
