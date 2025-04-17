<?php
require_once '../db.php';

$cod_cliente = isset($_POST["cod_cliente"]) ? intval($_POST["cod_cliente"]) : 0;

try {
    $conn->beginTransaction();

    // Buscar todos os pedidos do cliente
    $stmt_pedidos = $conn->prepare("SELECT DISTINCT num_pedido FROM pedido WHERE cod_cliente = :cod_cliente");
    $stmt_pedidos->bindParam(':cod_cliente', $cod_cliente, PDO::PARAM_INT);
    $stmt_pedidos->execute();
    $pedidos = $stmt_pedidos->fetchAll(PDO::FETCH_COLUMN); // retorna um array com num_pedido

    if(empty($pedidos)){
        $stmt_cliente = $conn->prepare("DELETE FROM cliente WHERE cod_cliente = :cod_cliente");
        $stmt_cliente->bindParam(':cod_cliente', $cod_cliente, PDO::PARAM_INT);
        $stmt_cliente->execute();
    
        $conn->commit();

        echo json_encode([
            "status" => true,
            "msg" => "Cliente excluído com sucesso!"
        ]);
    }
    else{
        echo json_encode([
            'status' => false,
            'msg' => "Cliente ainda tem pedidos: " . json_encode($pedidos)
        ]);
        $conn->rollBack();
    }
    // // Excluir itens de todos os pedidos
    // $stmt_itens = $conn->prepare("DELETE FROM item_pedido WHERE num_pedido = :num_pedido");
    // foreach ($pedidos as $num_pedido) {
    //     $stmt_itens->bindParam(':num_pedido', $num_pedido, PDO::PARAM_INT);
    //     $stmt_itens->execute();
    // }

    // // Excluir os pedidos
    // $stmt_pedido = $conn->prepare("DELETE FROM pedido WHERE cod_cliente = :cod_cliente");
    // $stmt_pedido->bindParam(':cod_cliente', $cod_cliente, PDO::PARAM_INT);
    // $stmt_pedido->execute();

    // Excluir o cliente
   
} catch (PDOException $e) {
    $conn->rollBack();
    echo json_encode([
        'status' => false,
        'error' => $e->getMessage()
    ]);
}
exit();
