<?php

require_once 'db.php';

$cod_cliente = isset($_POST['cod_cliente']) ? $_POST['cod_cliente'] : (isset($_GET['cod_cliente']) ? $_GET['cod_cliente'] : null);

if ($cod_cliente) {
    // Obtém os dados do cliente
    $stmt = $conn->prepare("SELECT * FROM cliente WHERE cod_cliente = :cod_cliente");
    $stmt->bindParam(':cod_cliente', $cod_cliente);
    $stmt->execute();
    $cliente = $stmt->fetch();
    if (!$cliente) {
        echo json_encode(["status" => false, "message" => "cliente não encontrado"]);
        exit();
    }

    $nom_cliente = $cliente ? $cliente['nom_cliente'] : "";

    // Se for requisição GET, retorna os dados
    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        // Carregar os clientes para o formulário
        $stmtitem = $conn->prepare("SELECT nom_cliente FROM cliente");
        $stmtitem->execute();

        header('Content-Type: application/json');
        echo json_encode([
            "cod_cliente" => $cod_cliente,
            "nom_cliente" => $nom_cliente
        ]);
        exit();
    }

    // Processar requisição POST para editar o cliente
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        try {
            if (!isset($_POST["nom_cliente"]) || !isset($_POST["cod_cliente"])) {
                echo json_encode(["status" => false, "message" => "Dados inválidos"]);
                exit();
            }
            $nom_cliente = $_POST["nom_cliente"];
            $cod_cliente = $_POST["cod_cliente"];

            $stmt = $conn->prepare("UPDATE cliente SET nom_cliente = :nom_cliente WHERE cod_cliente = :cod_cliente");
            $stmt->bindParam(':cod_cliente', $cod_cliente);
            $stmt->bindParam(':nom_cliente', $nom_cliente);
            $stmt->execute();

            header('Content-Type: application/json');
            echo json_encode(["status" => true]);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(["status" => false, "message" => $e->getMessage()]);
        }
        exit();
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(["status" => false, "message" => "Número do cliente não fornecido"]);
    exit();
}
?>