<?php

// Específica qual URL pode acessar
header('Access-Control-Allow-Origin: *');

// Especifica qual método http é aceito
header('Access-Control-Allow-Methods: GET');

// Cabeçalhos que podem ser recebidos
header('Access-Control-Allow-Headers: Content-Type');

// Tipo de conteúdo que é aceito no back-end
header("Content-Type: application/json");

// Buscando o arquivo do banco:
require_once '../../database/conn.php';

// Decoder de JWT
require_once '../../token/decodeJWT.php';



// Retorna o total de horas lançadas pelo técnico, seja horas-pessoa ou horas-máquina
function getTecTotalHours($nif, $token, $conn) {


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



    //


    // Query para ver se o máximo de horas daquele produto foi trabalhado ou não
    $stmt = $conn -> prepare ("SELECT SUM(HorasPessoa) AS HorasPessoa, SUM(HorasMaquina) AS HorasMaquina FROM CargaHoraria WHERE fk_nifTecnico = ?");
    $stmt -> bind_param('i', $nif);
    

    if ($stmt->execute()) {

        $result = $stmt->get_result();

        $totalHours = $result->fetch_assoc();

        if ($totalHours['HorasPessoa'] != null || $totalHours['HorasMaquina'] != null) {

            $response = [
                'status' => 'success',
                'mensagem' => 'Sucesso ao puxar as horas totais do técnico',
                'tecHours' => $totalHours
            ];
        }
        else {
            $response = [
                'status' => 'success',
                'mensagem' => 'Sucesso ao puxar as horas totais do técnico',
                'tecHours' => [
                    'HorasPessoa' => 0,
                    'HorasMaquina' => 0
                    ]
            ];
        }

    } 
    else {

        $response = [
            'status' => 'success',
            'mensagem' => 'Erro ao tentar puxar as horas totais do técnico',
            'tecHours' => ''
        ];

    }

    echo json_encode($response);


};


//Função para verificar o metodo de requisição vindo do JavaScript
if ($_SERVER['REQUEST_METHOD'] === 'GET') {


    if (!isset($_GET['token'])) {

        $resposta = [
            'status' => 'error',
            'Mensagem' => 'erro: ' . 'token não enviado na requisição.'
        ];

        echo json_encode($resposta);

        exit;
    }

    // nif do técnico
    if (!isset($_GET['nif'])) {

        $resposta = [
            'status' => 'error',
            'mensagem' => 'NIF não enviado'
        ];

        echo json_encode($resposta);

        exit;
    }

    $nif = $_GET['nif'];
    $token = $_GET['token'];

    getTecTotalHours($nif, $token, $conn);
    
} 
else {
    $resposta = [
        'status' => 'error',
        'mensagem' => 'método HTTP inválido'
    ];

    echo json_encode($resposta);
}

?>