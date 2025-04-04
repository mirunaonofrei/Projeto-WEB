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
    <table class="easyui-datagrid" style="width:750px;" data-options="footer:'#ft_dg_i'">
        <thead>
            <tr>
                <th data-options="field:'den_item', width:304">Item</th>
                <th data-options="field:'qtd_solicitada', width:148">Qtde</th>
                <th data-options="field:'pre_unitario', width:148">Preço</th>
                <th data-options="field:'total', width:148">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dados_pedido['itens'] as $item): ?>
                <tr>
                    <td><?= $item['den_item'] ?></td>
                    <td><?= $item['qtd_solicitada'] ?></td>
                    <td>R$ <?= number_format($item['pre_unitario'], 2, ',', '.') ?></td>
                    <td>R$ <?= number_format($item['qtd_solicitada'] * $item['pre_unitario'], 2, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <div id="ft_dg_i" style="height:auto; background-color:rgb(155, 198, 255);">
            <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-add',plain:true" onclick="adicionar_item()">Adicionar</a>
            <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick="remover_item()">Remover</a>
            <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-edit',plain:true" onclick="editar_item()">Editar</a>
            <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-undo',plain:true" onclick="cancelar_item()">Cancelar</a>
        </div>
    </table>
<?php else: ?>
    <p>Nenhum item encontrado para este pedido.</p>
<?php endif; ?>