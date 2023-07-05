<?php

require __DIR__ . "/../vendor/autoload.php";
require __DIR__ . "/models/Carro.php";
include __DIR__ . "/api.php";

use Models\Carro;

if ($argc < 2) {
    echo "Erro: você deve passar o host do servidor";
    die();
}

const OPCAO_CRIAR = '1';
const OPCAO_APAGAR = '2';
const OPCAO_EDITAR = '3';
const OPCAO_SAIR = '4';

const BALDE = "carros";

$HOST = $argv[1];

$client = new GuzzleClient($HOST);
$routes = getRoutes();

createBalde();

/**
 * Recupera as rotas do cliente e as atribui à variável global $routes.
 *
 * @throws Exception caso ocorra um erro durante a requisição.
 * @return void
 */
function getRoutes(): array
{
    global $client;

    $response = $client->getClient()->request("GET");
    return get_object_vars(
        json_decode(
            $response->getBody()->getContents()
        )
    );
}

/**
 * Cria um Balde.
 *
 * @throws GuzzleHttp\Exception\ClientException caso ocorra um erro durante a requisição.
 */
function createBalde(): void
{
    global $client;

    try {
        $client->getClient()->request("HEAD", "api" . _path("balde", [["{balde}", BALDE]]));
    } catch (GuzzleHttp\Exception\ClientException $e) {
        if ($e->getResponse()->getStatusCode() == 404) {
            $client->getClient()->put(
                "api" . _path("balde", [["{balde}", BALDE]]),
                [
                    'json' => [
                        'usuario' => 'carros',
                        'nome' => BALDE
                    ]
                ]
            );
        }
    }
}

/**
 * Retorna o caminho para uma determinada chave de URL com substituições opcionais.
 *
 * @param string $url_key A chave da URL para obter o caminho.
 * @param array $substituicoes Um array de substituições para aplicar ao caminho.
 * @return string O caminho para a chave de URL fornecida com as substituições aplicadas.
 */
function _path(string $url_key, array $substituicoes = []): string
{
    global $routes;
    $path = $routes[$url_key];
    foreach ($substituicoes as [$var, $val]) {
        $path = str_replace($var, $val, $path);
    }
    return $path;
}

/**
 * Usuario escolhe a opção do menu principal.
 *
 * @return int A escolha do usuário no menu.
 */
function menu_principal(): int
{
    echo "Escolha uma opção:\n";
    echo "1. Criar\n2. Apagar\n3. Atualizar\n4. Sair\n";
    $opcao = intval(readline());
    return ($opcao == 0) ?  -1 :  $opcao;
}

/**
* Cria um novo carro ao enviar uma solicitação POST para a API balde.
*
* @param Carro $carro O objeto carro contendo os dados a serem enviados.
* @throws Exception Se houver um erro ao criar o carro.
* @return void
*/
function req_create(Carro $carro): void
{
    global $client;

    $endpoint = "api" . _path('balde', [["{balde}", BALDE]]);
    $jsonPayload = [
        'usuario' => 'cars',
        'valor' => json_encode($carro)
    ];

    $response = $client->getClient()->post($endpoint, ['json' => $jsonPayload]);

    if ($response->getStatusCode() == 201) {
        echo "Carro criado com sucesso\n";
    } else {
        echo "Erro ao criar o carro\n";
    }
}

/**
* Cria um carro solicitando informações ao usuário e enviando uma requisição para criá-lo.
*
* @throws Exception Se houver um erro ao criar o carro.
* @return void
*/
function menu_create_car(): void
{
    $carro = new Carro();
    $carro->nome = readline("Digite o nome: ");
    $carro->ano = (int)(readline("Digite o ano: "));
    $carro->portas = readline("Digite a quantidade de portas: ");
    $carro->modelo = readline("Digite o modelo: ");
    $funciona = readline("O carro funciona? S. sim ou N. não: ");
    $carro->funciona = ($funciona == 'S') ? true : false;
    req_create($carro);
}

/**
* Recupera carros da API.
*
* @throws Exception Se houver um erro ao recuperar os dados.
* @return object Os dados dos carros recuperados.
*/
function get_cars()
{
    global $client;

    $response = $client->getClient()->get(
        "api" . _path('balde', [["{balde}", BALDE]])
    );
    if ($response->getStatusCode() != 200) {
        echo "Erro ao obter os dados";
        return;
    }
    $data = json_decode($response->getBody()->getContents());
    return $data;
}

/**
* Deleta um objeto.
*
* @param int $chave A chave do objeto a ser deletado.
* @throws GuzzleHttp\Exception\ClientException Se o objeto não for encontrado.
* @return void
*/
function req_delete_object(int $chave): void
{
    global $client;

    try {
        $response = $client->getClient()->delete(
            "api" . _path('objeto', [["{balde}", BALDE], ["{chave}", $chave]]));
        if ($response->getStatusCode() == 200) {
            echo "Carro deletada com sucesso\n";
        }
    } catch (GuzzleHttp\Exception\ClientException $e) {
        $response = $e->getResponse();
        if ($response->getStatusCode() == 404) {
            echo "Carro não encontrada\n";
        }
    }
}

/**
* Recupera um carro da API com base na chave fornecida.
*
* @param int $chave A chave do carro a ser recuperado.
* @throws \GuzzleHttp\Exception\ClientException Se a requisição à API falhar.
* @return string O valor(json em string) do carro.
*/
function req_get_car(int $chave): string
{
    global $client;

    try {
        $response = $client->getClient()->get(
            "api" . _path('objeto', [["{balde}", BALDE], ["{chave}", $chave]])
        );
    } catch (\GuzzleHttp\Exception\ClientException $e) {
        $response = $e->getResponse();
        if ($response->getStatusCode() == 404) {
            echo "Carro não encontrada\n";
            return '';
        }
    }
    return json_decode($response->getBody()->getContents())->valor;
}

function menu_remove(): void
{
    $chave = intval(readline("Digite a chave do carro: "));
    req_delete_object($chave);
}

/**
* Envia uma requisição PUT para a API para atualizar um objeto Carro.
*
* @param int $chave O parâmetro chave representa o identificador único para o objeto Carro.
* @param Carro $carro O parâmetro carro representa o objeto Carro a ser atualizado.
* @throws \GuzzleHttp\Exception\ClientException Se houver um erro no cliente HTTP.
* @return void
*/
function req_put_car(int $chave, Carro $carro): void
{
    global $client;

    try{
        $response = $client->getClient()->put(
            "api" . _path('objeto', [["{balde}", BALDE], ["{chave}", $chave]]),
            [
                'json' => [
                    'valor' => json_encode($carro)
                ]
            ]
        );
    } catch(\GuzzleHttp\Exception\ClientException $e){
        $response = $e->getResponse();
        if($response->getStatusCode() == 404){
            echo "Pessoa não encontrada\n";
            return;
        }
    }
}

function menu_edit(): void
{
    $chave = intval(readline("Digite a chave do carro: "));
    $car_obj = req_get_car($chave);
    if ($car_obj == '') return;
    $car_obj = json_decode($car_obj);
    echo "DADOS ATUAIS\n";
    list_car($chave, $car_obj);
    echo "\n\n";
    $carro = new Carro();
    $carro->nome = readline("Digite o nome: ");
    $carro->ano = intval(readline("Digite o ano: "));
    $carro->portas = readline("Digite a quantidade de portas: ");
    $carro->modelo = readline("Digite o modelo: ");
    $funciona = readline("O carro funciona? S. sim ou N. não: ");
    $carro->funciona = ($funciona == 'S') ? true : false;
    req_put_car($chave, $carro);
}

function list_car(int $chave, stdClass $car): void
{
    echo "[CHAVE: {$chave}, Nome: {$car->nome}, Ano: {$car->ano}, Portas: {$car->portas}, Modelo: {$car->modelo}, Funciona: {$car->funciona}]\n";
}

function main(): void
{
    do {
        echo "-----LISTA DE CARROS-----\n";
        $cars = get_cars();
        for ($i = 0; $i < count($cars); $i++) {
            list_car($cars[$i]->chave, json_decode($cars[$i]->valor));
        }
        echo "\n";
        $opcao = menu_principal();
        switch ($opcao) {
            case OPCAO_CRIAR:
                menu_create_car();
                break;
            case OPCAO_APAGAR:
                menu_remove();
                break;
            case OPCAO_EDITAR:
                menu_edit();
                break;
        }
    } while ($opcao != OPCAO_SAIR);
}

main();