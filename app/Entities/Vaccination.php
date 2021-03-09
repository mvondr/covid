<?php /** @noinspection PhpUnused */


namespace App\Entities;


/**
 * Data structure of response of the vaccination API
 */
class Vaccination
{
    public $datum;
    public $vakcina;
    public $kraj_nuts_kod;
    public $kraj_nazev;
    public $vekova_skupina;
    public $prvnich_davek;
    public $druhych_davek;
    public $celkem_davek;

    public function __construct()
    {
        $this->prvnich_davek = 0;
        $this->druhych_davek = 0;
        $this->celkem_davek = 0;
    }
}
