<?php

$servername = "localhost";
$username = "root";
$password = "12simple36";
$dbname = "projeto_teste";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro: " . $e->getMessage());
}

$sql = "SELECT pedido.num_pedido, cliente.nom_cliente 
            FROM pedido 
            INNER JOIN cliente ON pedido.cod_cliente = cliente.cod_cliente
            ORDER BY pedido.num_pedido";

$stmt = $conn->prepare($sql);
$stmt->execute();
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

function consulta_itens($conn, $num_pedido)
{
    $sql_itens = "SELECT it.num_seq_item, item.den_item, it.qtd_solicitada, it.pre_unitario 
                  FROM item_pedido AS it
                  INNER JOIN item ON it.cod_item = item.cod_item
                  WHERE it.num_pedido = :num_pedido
                  ORDER BY it.num_seq_item";

    $stmt_itens = $conn->prepare($sql_itens);
    $stmt_itens->bindParam(':num_pedido', $num_pedido, PDO::PARAM_INT);
    $stmt_itens->execute();
    $itens_result = $stmt_itens->fetchAll(PDO::FETCH_ASSOC);

    // Calculando o total enquanto percorremos os itens
    $total = 0;
    foreach ($itens_result as $item) {
        $total += $item['qtd_solicitada'] * $item['pre_unitario'];
    }

    // Retorna os itens e o total formatado
    return [
        'itens' => $itens_result,
        'total' => number_format($total, 2, ',', '.')
    ];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Pedidos</title>
    <style>
        .table_externa {
            background-color: rgb(219, 144, 219);
            border-collapse: separate;
            border-radius: 15px;
            width: 600px;
            margin: 0 auto 20px;
            /* Centraliza e adiciona margem inferior */
            overflow: hidden;
            /* Garante que as bordas arredondadas apareçam */
        }

        .table_interna {
            background-color:rgb(236, 226, 236);
            border-collapse: separate;
            border-radius: 15px;
            width: 600px;
            margin: 0 auto 10px;
            /* Centraliza e adiciona espaçamento inferior */
            overflow: hidden;
        }

        td {
            border-radius: 15px;
            padding: 8px;
        }

        .header {
            font-weight: bold;
            background-color: rgb(221, 112, 221);
        }

        button {
            text-align: center;
            background-color: lightslategray;
            color: white;
            height: 40px;
        }

        div {
            display: flex;
            justify-content: right;
            /* Centraliza os itens horizontalmente */
            align-items: flex-end;
            /* Alinha os itens na parte inferior da div */
            gap: 20px;
            /* Espaçamento de 20px entre os botões */
            padding-bottom: 15px;
        }
    </style>
    <link rel="stylesheet" type="text/css" href="easyui/themes/default/easyui.css">
    <link rel="stylesheet" type="text/css" href="easyui/themes/icon.css">
    <script type="text/javascript" src="easyui/jquery.min.js"></script>
    <script type="text/javascript" src="easyui/jquery.easyui.min.js"></script>
    <script type="text/javascript" src="easyui/locale/easyui-lang-pt_BR.js"></script>
</head>

<body style="background-color: #D8BFD8" ;>
    <div>
        <a id="btn" href="gerenciar_item.php" class="easyui-linkbutton" data-options="iconCls:'icon-search'">Gerenciar Itens</a>
        <a id="btn" href="gerenciar_cliente.php" class="easyui-linkbutton" data-options="iconCls:'icon-search'">Gerenciar Clientes</a>
        <a id="btn" href="controlar_pedido.php" class="easyui-linkbutton" data-options="iconCls:'icon-search'">Incluir Pedido</a>
    </div>
    <?php if (!empty($pedidos)): ?>
        <?php foreach ($pedidos as $row): ?>
            <table class="table_externa">
                <tr>
                    <td class="header">Pedido:</td>
                    <td><?= $row['num_pedido'] ?></td>
                    <td class="header">Cliente:</td>
                    <td><?= $row['nom_cliente'] ?></td>
                    <td class="right">
                        <a href='controlar_pedido.php?num_pedido=<?= $row['num_pedido'] ?>'>[Modificar Pedido]</a><br>
                        <a href='excluir_pedido.php?num_pedido=<?= $row['num_pedido'] ?>' onclick='return confirm("Tem certeza que deseja excluir este pedido?")'>[Excluir Pedido]</a><br>
                        <a href='controlar_item_pedido.php?num_pedido=<?= $row['num_pedido'] ?>'>[Incluir Item]</a>
                    </td>
                </tr>
                <tr>
                    <td colspan='5'>
                        <?php
                        $dados_pedido = consulta_itens($conn, $row['num_pedido']);
                        $itens = $dados_pedido['itens'];
                        ?>
                        <?php if (!empty($itens)): ?>
                            <table class="table_interna" style='width: 100%;'>
                                <tr class="header">
                                    <td>Item</td>
                                    <td>Qtde</td>
                                    <td>Preço</td>
                                    <td>Total</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <?php foreach ($itens as $item): ?>
                                    <tr>
                                        <td><?= $item['den_item'] ?></td>
                                        <td><?= $item['qtd_solicitada'] ?></td>
                                        <td>R$ <?= number_format($item['pre_unitario'], 2, ',', '.') ?></td>
                                        <td>R$ <?= number_format($item['qtd_solicitada'] * $item['pre_unitario'], 2, ',', '.') ?></td>
                                        <td><a href='controlar_item_pedido.php?num_pedido=<?= $row['num_pedido'] ?>&num_seq_item=<?= $item['num_seq_item'] ?>'>[Modificar Item]</a></td>
                                        <td><a href='excluir_item_pedido.php?num_pedido=<?= $row['num_pedido'] ?>&num_seq_item=<?= $item['num_seq_item'] ?>' onclick='return confirm("Tem certeza que deseja excluir este item?")'>[Excluir]</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        <?php else: ?>
                            <p>Nenhum item encontrado para este pedido.</p>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td colspan='5' class="right">TOTAL: R$ <?= $dados_pedido['total']; ?></td>
                </tr>
            </table>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Nenhum pedido encontrado.</p>
    <?php endif; ?>


</body>

</html>