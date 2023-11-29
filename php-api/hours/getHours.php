<?php
// Específica qual URL pode acessar
header('Access-Control-Allow-Origin: *');

// Especifica qual método http é aceito
header('Access-Control-Allow-Methods: GET');

// Cabeçalhos que podem ser recebidos
header('Access-Control-Allow-Headers: Content-Type');

// Tipo de conteúdo que é aceito no back-end
header("Content-Type: application/json");

require_once '../../../database/conn.php';

// Decoder de JWT
require_once '../../../token/decodeJWT.php';



// Retorna os últimos 50 logs de horas do técnico
function getHours($nif, $token, $conn) {


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

    // $stmt = $conn->prepare('SELECT Datas, idCargaHoraria FROM CargaHoraria WHERE fk_nifTecnico = ? ORDER BY Datas DESC, idCargaHoraria DESC LIMIT ?');
    $stmt = $conn->prepare('SELECT * FROM CargaHoraria WHERE fk_nifTecnico = ? ORDER BY Datas DESC, idCargaHoraria DESC LIMIT 50');

    $stmt->bind_param('i', $nif);


    if ($stmt->execute()){

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            $hoursLog = array();
    
            while ($row = $result->fetch_assoc()) {
                $hoursLog[] = $row;
            }

            $response = [
                'status' => 'success',
                'mensagem' => 'sucesso ao puxar o histórico de horas',
                'hoursLog' => $hoursLog
            ];
        } 
        else {

            $response = [
                'status' => 'success',
                'mensagem' => 'não há histórico de horas',
                'hoursLog' => ''
            ];
        }
        

    } 
    else {
        $response = [
            'status' => 'error',
            'mensagem' => 'Erro ao puxar os produtos'
        ];

    }

    echo json_encode($response);
}


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

    if (!isset($_GET['nif'])) {

        $response = [
            'status' => 'error',
            'Mensagem' => 'erro: ' . 'nif não enviado na requisição.'
        ];

        echo json_encode($response);

        exit;
    }

    $nif = $_GET['nif'];
    $token = $_GET['token'];

    // $response = [
    //     'status' => 'error',
    //     'Mensagem' => 'erro: ' . $token
    // ];

    // echo json_encode($response);

    // exit;

    getHours($nif, $token, $conn);

} else {

    $response = [
        'status' => 'error',
        'Mensagem' => 'método de requisição não aceito'
    ];

    echo json_encode($response);
}
?>