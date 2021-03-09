<?php


namespace App\Services;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Vaccination API (by Ministry of Health)
 */
class Covid19Api
{
    /**
     * @var Client
     */
    private $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * Get data about vaccination from API
     * @throws GuzzleException
     * @throws Exception
     */
    public function getVaccinationByRegions(): string
    {
        $res = $this->client->request('GET', 'https://onemocneni-aktualne.mzcr.cz/api/v2/covid-19/ockovani.json');
        if($res->getStatusCode()) {
            return $res->getBody()->getContents();
        } else {
            throw new Exception("Error " . $res->getStatusCode());
        }
    }

}
