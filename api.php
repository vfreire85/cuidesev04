<?php
session_start();
include_once 'session_handler.php';

header("Content-Type: application/json");

// Configuração do banco de dados PostgreSQL
$host = 'localhost'; 
$dbname = 'cuidesedb';
$user = 'cuidesedb'; // Usuário do banco de dados
$password = '123456'; // Senha do banco de dados

// Conectar ao banco de dados usando PDO
try {
    $db = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Erro ao conectar ao banco de dados: ' . $e->getMessage()]);
    exit;
}

// Obter o método HTTP
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST': // Login
        $usuario = $_POST['usuario'] ?? '';
        $senha = $_POST['senha'] ?? '';

        try {
            $stmt = $db->prepare('SELECT * FROM usuarios WHERE usuario = :usuario AND senha = :senha');
            $stmt->bindValue(':usuario', $usuario, PDO::PARAM_STR);
            $stmt->bindValue(':senha', $senha, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                echo json_encode(['status' => 'success', 'usuario' => $usuario]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Credenciais inválidas']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'GET': // Obter nome do usuário
        $usuario = $_GET['usuario'] ?? '';
        echo json_encode(['status' => 'success', 'usuario' => $usuario]);
        break;

    case 'PUT': // Alterar senha
        parse_str(file_get_contents('php://input'), $put_vars);
        $usuario = $put_vars['usuario'] ?? '';
        $novaSenha = $put_vars['novaSenha'] ?? '';

        try {
            $stmt = $db->prepare('UPDATE usuarios SET senha = :senha WHERE usuario = :usuario');
            $stmt->bindValue(':senha', $novaSenha, PDO::PARAM_STR);
            $stmt->bindValue(':usuario', $usuario, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                echo json_encode(['status' => 'success', 'message' => 'Senha alterada com sucesso']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Erro ao alterar senha ou usuário não encontrado']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'DELETE': // Deletar usuário (apenas para admin)
        parse_str(file_get_contents('php://input'), $delete_vars);
        $usuario = $delete_vars['usuario'] ?? '';

        try {
            $stmt = $db->prepare('DELETE FROM usuarios WHERE usuario = :usuario');
            $stmt->bindValue(':usuario', $usuario, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                echo json_encode(['status' => 'success', 'message' => 'Usuário deletado']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Erro ao deletar usuário ou usuário não encontrado']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Método não suportado']);
}

// Configurar exibição de erros para depuração
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
