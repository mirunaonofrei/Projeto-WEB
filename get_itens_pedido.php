<?php

require_once 'db.php';

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

    $total = 0;
    foreach ($itens_result as $item) {
        $total += $item['qtd_solicitada'] * $item['pre_unitario'];
    }

    return [
        'itens' => $itens_result,
        'total' => number_format($total, 2, ',', '.')
    ];
}

$num_pedido = $_GET['num_pedido'];
$dados_pedido = consulta_itens($conn, $num_pedido);

if (!empty($dados_pedido['itens'])): ?>
    <table class="easyui-datagrid" style="width:100%;">
        <thead>
            <tr>
                <th data-options="field:'den_item', width:200">Item</th>
                <th data-options="field:'qtd_solicitada', width:100">Qtde</th>
                <th data-options="field:'pre_unitario', width:100">Preço</th>
                <th data-options="field:'total', width:100">Total</th>
                <th data-options="field:'acoes', width:200">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dados_pedido['itens'] as $item): ?>
                <tr>
                    <td><?= $item['den_item'] ?></td>
                    <td><?= $item['qtd_solicitada'] ?></td>
                    <td>R$ <?= number_format($item['pre_unitario'], 2, ',', '.') ?></td>
                    <td>R$ <?= number_format($item['qtd_solicitada'] * $item['pre_unitario'], 2, ',', '.') ?></td>
                    <td>
                        <a href='controlar_item_pedido.php?num_pedido=<?= $num_pedido ?>&num_seq_item=<?= $item['num_seq_item'] ?>'>[Modificar]</a>
                        <a href='excluir_item_pedido.php?num_pedido=<?= $num_pedido ?>&num_seq_item=<?= $item['num_seq_item'] ?>' onclick='return confirm("Tem certeza que deseja excluir este item?")'>[Excluir]</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>Nenhum item encontrado para este pedido.</p>
<?php endif; ?>
