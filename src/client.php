<?php

require __DIR__ . "/../vendor/autoload.php";
require __DIR__ . "/models/Pessoa.php";
include __DIR__ . "/api.php";

if ($argc < 2) {
    echo "Erro: você deve passar o host do servidor";
    die();
}

const OPCAO_SAIR = '0';
const OPCAO_CRIAR = '1';
const OPCAO_APAGAR = '2';

$HOST = $argv[1];
$client = new GuzzleClient($HOST);
$routes;

getRoutes();
createBalde();

/**
 * Obtém as rotas
 * 
 * @return void
 */
function getRoutes(): void
{
    global $client, $routes;

    $response = $client->getClient()->request("GET");
    $routes = get_object_vars(
        json_decode(
            $response->getBody()->getContents()
        )
    );
}

function createBalde()
{
    global $client;

    try {
        $response = $client->getClient()->request("HEAD", "api" . _path("balde", [["{balde}", "pessoas"]]));
    } catch (GuzzleHttp\Exception\ClientException $e) {
        $response = $e->getResponse();
        if ($response->getStatusCode() == 404) {
            $response = $client->getClient()->put(
                "api" . _path("balde", [["{balde}", "pessoas"]]),
                [
                    'json' => [
                        'usuario' => 'pessoas',
                        'nome' => 'pessoas'
                    ]
                ]
            );
        }
    }
}

function _path(string $url_key, array $substituicoes = [])
{
    global $routes;
    $path = $routes[$url_key];
    foreach ($substituicoes as [$var, $val]) {
        $path = str_replace($var, $val, $path);
    }
    return $path;
}

function menu_principal(): int
{
    echo "Escolha uma opção:\n";
    echo "1. Criar\n2. Apagar\n";
    try {
        $opcao = intval(readline());
    } catch (\Throwable $th) {
        echo "digite um numero válido";
        menu_principal();
    }
    return $opcao;
}

function menu_criar(): void
{
    global $client;
    $pessoa = new \Models\Pessoa();
    $pessoa->nome = readline("Digite o nome: ");
    $pessoa->idade = intval(readline("Digite a idade: "));
    $pessoa->sexo = readline("Digite o sexo: ");
    $doente = readline("É doente? S. sim ou N. não: ");
    $pessoa->doente = ($doente == 'S') ? true : false;
    $empregado = readline("É empregado? S. sim ou N. não: ");
    $pessoa->empregado = ($empregado == 'S') ? true : false;
    # create a guzzle post request
    var_dump(json_encode([
        'usuario' => 'pessoas',
        'valor' => $pessoa
    ]));
    die();
    $response = $client->getClient()->post(
        "api" . _path('balde', [["{balde}", "pessoas"]]),
        [
            'json' => json_encode([
                'usuario' => 'pessoas',
                'valor' => $pessoa
            ])
        ]
    );
    if ($response->getStatusCode() == 201) {
        echo "Pessoa criada com sucesso\n";
    } else {
        echo "Erro ao criar a pessoa\n";
    }
}

function main(): void
{
    $opcao = menu_principal();
    switch ($opcao) {
        case OPCAO_CRIAR:
            menu_criar();
            break;
        case OPCAO_APAGAR:
            menu_apagar();
            break;
        case OPCAO_SAIR:
            die();
    }
}

main();
