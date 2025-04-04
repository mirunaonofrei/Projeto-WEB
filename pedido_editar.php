<?php

require_once 'db.php';

// Problema de editar estava aqui
$num_pedido = isset($_POST['num_pedido']) ? $_POST['num_pedido'] : (isset($_GET['num_pedido']) ? $_GET['num_pedido'] : null);




if ($num_pedido) {
    // Obtém os dados do pedido
    $stmt = $conn->prepare("SELECT * FROM pedido WHERE num_pedido = :num_pedido");
    $stmt->bindParam(':num_pedido', $num_pedido);
    $stmt->execute();
    $pedido = $stmt->fetch();
    if (!$pedido) {
        echo json_encode(["status" => false, "message" => "Pedido não encontrado"]);
        exit();
    }

    $cod_cliente = $pedido ? $pedido['cod_cliente'] : "";

    // Se for requisição GET, retorna os dados
    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        // Carregar os clientes para o formulário
        $stmtClientes = $conn->prepare("SELECT cod_cliente, nom_cliente FROM cliente");
        $stmtClientes->execute();
        $clientes = $stmtClientes->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode([
            "num_pedido" => $num_pedido,
            "cod_cliente" => $cod_cliente,
            "clientes" => $clientes
        ]);
        exit();
    }

    // Processar requisição POST para editar o pedido
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        try {
            if (!isset($_POST["cod_cliente"]) || !isset($_POST["num_pedido"])) {
                echo json_encode(["status" => false, "message" => "Dados inválidos"]);
                exit();
            }
            $cod_cliente = $_POST["cod_cliente"];
            $num_pedido = $_POST["num_pedido"];

            $stmt = $conn->prepare("UPDATE pedido SET cod_cliente = :cod_cliente WHERE num_pedido = :num_pedido");
            $stmt->bindParam(':num_pedido', $num_pedido);
            $stmt->bindParam(':cod_cliente', $cod_cliente);
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
    echo json_encode(["status" => false, "message" => "Número do pedido não fornecido"]);
    exit();
}
?>