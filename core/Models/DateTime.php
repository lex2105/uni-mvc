<?php

namespace Core\Models;

/**
 * Wir erweitern hier die von PHP mitgelieferte \DateTime-Klasse.
 */
class DateTime extends \DateTime
{
    /**
     * Um möglichst nah an der erweiterten Klasse zu sein, definieren wir eine Klassenkonstante für das Ausgabeformat.
     */
    const MYSQL_DATETIME = 'Y-m-d H:i:s';

    /**
     * Hier definieren wir uns custom Formate zur Formatierung von Zeitslots, die wir verwenden können, wann immer wir
     * sie brauchen.
     */
    const HUMAN_READABLE_AT_FROM = 'd.m.Y, H:i';
    const HUMAN_READABLE_AT_TO = 'H:i';

    /**
     * Was soll ausgegeben werden, wenn das Objekt in einen String konvertiert werden soll?
     *
     * @return string
     */
    public function __toString()
    {
        return $this->format(self::MYSQL_DATETIME);
    }

}
