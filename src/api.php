<?php

require __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;

class GuzzleClient
{
    private $client;

    /**
     * Inicializa o cliente GuzzleHttp com um host dado.
     * 
     * @param string $host O host a ser conectado. O padrão é http://localhost:8000.
     * @return void
     */
    public function __construct(string $host = "http://localhost:8000/api")
    {
        // Instancia o cliente GuzzleHttp com uma URI base e verificação SSL desativada.
        $this->client = new Client([
            'base_uri' => $host,
            'verify' => false // Desativar a verificação SSL pode ser perigoso, use com cautela!
        ]);
    }

    /**
     * Retorna a instância do cliente associada a este objeto.
     *
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }
}
