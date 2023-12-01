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



// Pega as horas trabalhadas no dia do produto na máquina, e do técnico no total do dia
function getWorkedHoursDay($nif, $productId, $token, $conn) {


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


    // Query para ver se o máximo de horas-pessoa diárias foram trabalhadas ou não
    $stmtPersonHours = $conn -> prepare ("SELECT SUM(HorasPessoa) AS HorasPessoaDia FROM CargaHoraria WHERE fk_nifTecnico = ? AND Datas = CURDATE()");
    $stmtPersonHours -> bind_param('s', $nif);

    $stmtPersonHours->execute();

    $resultPerson = $stmtPersonHours->get_result();


    // Query para ver se o máximo de horas-pessoa diárias foram trabalhadas ou não
    $stmtMachHours = $conn -> prepare ("SELECT SUM(HorasMaquina) AS HorasMaquinaDia FROM CargaHoraria WHERE fk_idProduto = ? AND Datas = CURDATE()");
    $stmtMachHours -> bind_param('i', $productId);

    $stmtMachHours->execute();

    $resultMach = $stmtMachHours->get_result();


    //

    if ($resultPerson && $resultMach) {

        $personHoursDayPosted = $resultPerson->fetch_assoc();
        $machHoursDayPosted = $resultMach->fetch_assoc();

        //

         

        $response = [
            'status' => 'success',
            'mensagem' => 'Horas retornadas com sucesso',
            'hoursDayPerson' => $personHoursDayPosted['HorasPessoaDia'],
            'hoursDayMach' => $machHoursDayPosted['HorasMaquinaDia']
        ];

    }
    else {

        $response = [
            'status' => 'error',
            'mensagem' => 'Erro ao tentar retornar horas',
        ];
    }

    echo json_encode($response);

};


// Verificando a requisição
if ($_SERVER['REQUEST_METHOD'] === 'GET') {


    if (!isset($_GET['token'])) {

        $resposta = [
            'status' => 'error',
            'Mensagem' => 'erro: ' . 'token não enviado na requisição.'
        ];

        echo json_encode($resposta);

        exit;
    }

    if (!( isset($_GET['productId']) && isset($_GET['nif'])) ) {

        $response = [
            'status' => 'error',
            'Mensagem' => 'erro: ' . 'parametros não enviados na requisição.'
        ];

        echo json_encode($response);

        exit;
    }

    $productId = $_GET['productId'];
    $nif = $_GET['nif'];
    $token = $_GET['token'];


    getWorkedHoursDay($nif, $productId, $token, $conn);
    
} 
else {

    $response = [
        'status' => 'error',
        'Mensagem' => 'método de requisição não aceito'
    ];

    echo json_encode($response);
}
?>