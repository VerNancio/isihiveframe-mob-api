<?php

// Específica qual URL pode acessar
header('Access-Control-Allow-Origin: *');

// Especifica qual método http é aceito
header('Access-Control-Allow-Methods: PUT');

// Cabeçalhos que podem ser recebidos
header('Access-Control-Allow-Headers: Content-Type');

// Tipo de conteúdo que é aceito no back-end
header("Content-Type: application/json");

// Buscando o arquivo do banco:
require_once '../../../database/conn.php';

// Decoder de JWT
require_once '../../../token/decodeJWT.php';



// Função para mudar a situação do produto para concluído
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {


    // Obtém o corpo da requisição e decodifica como array associativo
    $data = json_decode(file_get_contents('php://input'), true);
    

    // Caso não tenha sido enviado o token...
    if (!isset($data['token'])) {

        $response = [
            'status' => 'error',
            'mensagem' => 'Token não enviado na requisição'
        ];

        echo json_encode($response);

        exit; 
    } 


    // Caso esteja setado, é definido o valor da variavel token
    $token = $data['token'];


    // Se o token está expirado 
    if (isTokenExpired($token) === true) {

        $response = [
            'status' => 'error',
            'mensagem' => 'Token de autorização expirado'
        ];

        echo json_encode($response);

        exit; 
    }


    // Se o jwt é inválido...
    if (decodeJWT($token) === false) {

        $response = [
            'status' => 'error',
            'mensagem' => 'Token de autorização inválido'
        ];

        echo json_encode($response);

        exit; 
    }

    // 



    // Verifica se o id do produto não foi enviado na requisição...
    if (!isset($data['productId'])) {

        $response = [
            'status' => 'error',
            'mensagem' => 'Id do produto não enviado na requisição'
        ];
        
        echo json_encode($response);

        exit;
    } 



    // variavel setada se enviada na requisição
    $productId = $data['productId'];


    // Query para verificar se o produto já está concluido ou não
    $stmt = $conn -> prepare ("SELECT idProduto FROM produtos 
    WHERE idProduto = ? && Situacao != 'Concluido'");

    $stmt -> bind_param('i', $productId);

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {

        $response = [
        'status' => 'error',
        'mensagem' => 'Produto já concluído'
        ];

        echo json_encode($response);

        exit;
    }

    //



    // Query para mudar situação do produto para concluido
    $stmt = $conn->prepare('UPDATE Produtos SET Situacao = "Concluido" WHERE idProduto = ?');

    $stmt->bind_param('i', $productId);

    if ($stmt->execute()){

        $response = [
            'status' => 'success',
            'mensagem' => 'Produto concluído com sucesso'
        ];
    } 
    else {

        $response = [
            'status' => 'error',
            'mensagem' => 'Erro ao concluir produto'
        ];
    }

    echo json_encode($response);
    

} else {

   $response = [
        'status' => 'error',
        'mensagem' => 'método de requisição não aceito: ' . $_SERVER['REQUEST_METHOD']
    ];

    echo json_encode($response);
    
}