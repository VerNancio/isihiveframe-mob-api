<?php
// Específica qual URL pode acessar
header('Access-Control-Allow-Origin: *');

// Especifica qual método http é aceito
header('Access-Control-Allow-Methods: POST');

// Cabeçalhos que podem ser recebidos
header('Access-Control-Allow-Headers: Content-Type');

// Tipo de conteúdo que é aceito no back-end
header("Content-Type: application/json");

// Buscando o arquivo do banco:
require_once '../../../database/conn.php';

// Importando o criador do JWT:
require_once '../../../token/encodeJWT.php';



// Validação de senha e encode de jwt
function validatePassword($userPassword, $registerData)
{

    // Validando a senha
    if (password_verify($userPassword, $registerData['Senha'])) {

        /*
            Gerando o Token e também salvando as credênciais de login que só ficara no
            back-end para validações de autenticação e afins
        */
        $token = encodeJWT([
            'cargo' => $registerData['TipoUser'],
            'nif' => $registerData['NIF'],
            'nome' => ucwords($registerData['Nome']) . ' ' .  ucwords($registerData['Sobrenome']),
        ]);



        // Mandando a resposta de login com o token para o cliente
        $response = [
            'login' => true,
            'token' => $token,
            'cargo' => $registerData['TipoUser'],
            'nif' => $registerData['NIF'],
            'nome' => ucwords($registerData['Nome']) . ' ' .  ucwords($registerData['Sobrenome']),
            'mensagem' => 'Bem vindo ' . ucwords($registerData['Nome']) . ' ' .  ucwords($registerData['Sobrenome']),
            'status' => 'success'
        ];


    } else {

        // resposta a ser mandado para o front-end
        $response = [
            'mensagem' => 'Login inválido',
            'login' => false,
            'status' => 'error'
        ];

    }

    echo json_encode($response);


}

function validateData($data, $conn)
{

    // Validadando o email primeiro
    $email = $data['email'];
    $userPassword = $data['password'];

    // Fazendo a query para a validação
    $stmt = $conn->prepare("SELECT * FROM Usuarios WHERE Email = ? AND TipoUser = 'tec' AND `Status` = 'ativo'");
    $stmt->bind_param('s', $email);

    // Executando a query e pegando o resultado
    $stmt->execute();
    $result = $stmt->get_result();

    // Verificando se retornou algum registro
    if ($result->num_rows > 0) {

        $register = $result->fetch_assoc();

        // Se a conta não for de técnico...
        if ($register['TipoUser'] != 'tec') {

            $response = [
                'mensagem' => 'Login somente de técnicos',
                'login' => false,
                'status' => 'error'
            ];
    
            echo json_encode($response);
        } 
        else {

            validatePassword($userPassword, $register);
        }


    } else {

        $response = [
            'mensagem' => 'Login inválido',
            'login' => false,
            'status' => 'error'
        ];

        echo json_encode($response);
    }


}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pegando o valor do corpo da requisição
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    validateData($data, $conn);

} else {

    $response = [
        'mensagem' => 'Algo deu errado...',
        'login' => false,
        'status' => 'error'
    ];

    echo json_encode($response);
}
?>