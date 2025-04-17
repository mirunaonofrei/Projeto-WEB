<?php

require_once '../db.php';

// Recupera os itens já existentes (para o caso de usar no frontend ou outro propósito)
$queryRecuperaClientes = "SELECT cod_cliente, nom_cliente FROM cliente";
$stmt = $conn->prepare($queryRecuperaClientes);
$stmt->execute();
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Consulta para obter o próximo código disponível para o cliente
$queryProxCliente = "SELECT (COALESCE(MAX(cod_cliente), 0) + 1) AS cod_cliente FROM cliente";
$exeProxCliente = $conn->prepare($queryProxCliente);
$exeProxCliente->execute();
$rowProxCliente = $exeProxCliente->fetch(PDO::FETCH_ASSOC);
$cod_cliente = $rowProxCliente['cod_cliente'];

// Se for requisição GET, retorna o próximo código (cod_cliente) apenas
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    header('Content-Type: application/json');
    echo json_encode([
        "cod_cliente" => $cod_cliente  // Retorna apenas o próximo código disponível
    ]);
    exit();
}

// Se for requisição POST, insere um novo cliente com cod_cliente e nom_cliente
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $cod_cliente = $_POST["cod_cliente"];
        $nom_cliente = $_POST["nom_cliente"];

        $queryVerificaDuplicado = "SELECT COUNT(*) FROM cliente WHERE nom_cliente = :nom_cliente";
        $stmt = $conn->prepare($queryVerificaDuplicado);
        $stmt->bindParam(':nom_cliente', $nom_cliente);
        $stmt->execute();
        $existe = $stmt->fetchColumn();

        if ($existe > 0) {

            echo json_encode([
                'status' => false,
                'msg' => "Já existe um cliente com esse nome!"
            ]);
        } else {

            $queryInserecliente = "INSERT INTO cliente (cod_cliente, nom_cliente) VALUES (:cod_cliente, :nom_cliente)";
            $stmt = $conn->prepare($queryInserecliente);
            $stmt->bindParam(':cod_cliente', $cod_cliente);
            $stmt->bindParam(':nom_cliente', $nom_cliente);
            $stmt->execute();
            header('Content-Type: application/json');
            echo json_encode(["status" => true]);
        }
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode(["status" => false . $e->getMessage()]);
    }
    exit();
}
