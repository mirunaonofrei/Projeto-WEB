<?php
require_once 'db.php';

function consulta_itens($conn, $num_pedido)
{
    $sql_itens = "SELECT it.num_seq_item, it.num_pedido, item.den_item, it.qtd_solicitada, it.pre_unitario 
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
    <table id="dg_i" class="easyui-datagrid" style="width:750px;" data-options="singleSelect:true, footer:'#ft_dg_i'">
        <thead>
            <tr>
                <th data-options="field:'num_seq_item', width:40"></th>
                <th data-options="field:'den_item', width:264">Item</th>
                <th data-options="field:'qtd_solicitada', width:148">Qtde</th>
                <th data-options="field:'pre_unitario', width:148">Preço</th>
                <th data-options="field:'total', width:148">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dados_pedido['itens'] as $item): ?>
                <tr>
                    <td><?= $item['num_seq_item']?></td>
                    <td><?= $item['den_item'] ?></td>
                    <td><?= $item['qtd_solicitada'] ?></td>
                    <td>R$ <?= number_format($item['pre_unitario'], 2, ',', '.') ?></td>
                    <td>R$ <?= number_format($item['qtd_solicitada'] * $item['pre_unitario'], 2, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <div id="ft_dg_i" style="height:auto; background-color:rgb(155, 198, 255);">
            <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-add',plain:true" onclick="adicionarItem()">Adicionar</a>
            <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick="removerItem()">Remover</a>
            <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-edit',plain:true" onclick="editarItem()">Editar</a>
            <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-undo',plain:true" onclick="cancelarItem()">Cancelar</a>
        </div>
    </table>
<?php else: ?>
    <p>Nenhum item encontrado para este pedido.</p>
<?php endif; ?>


<script type="text/javascript">
    function adicionarItem() {
        const num_pedido = <?= json_encode($num_pedido) ?>;

        if ($('#dialogAddItem').length) {
            $('#dialogAddItem').remove();
        }

        $('body').append('<div id="dialogAddItem"></div>');

        $.getJSON('item_adicionar.php', function(data) {
            let itemOptions = `<option value="">Selecione um item</option>`;
            data.itens_result.forEach(item => {
                itemOptions += `<option value="${item.cod_item}">${item.den_item}</option>`;
            });

            $('#dialogAddItem').dialog({
                title: 'Adicionar Item',
                width: 400,
                height: 'auto',
                modal: true,
                content: `<form id="form_adiciona_item">
                    <input type="hidden" name="num_pedido" value="${num_pedido}">
                    <div style="margin-bottom:10px">
                        <label>Item: </label>
                        <select class="easyui-combobox" name="cod_item" id="cod_item_form" required style="width:100%;">${itemOptions}</select>
                        </div>
                    <div style="margin-bottom:10px">
            
                        <input class="easyui-numberbox" label="Quantidade:" name="qtd_solicitada" required style="width:100%;">
                    </div>
                    <div style="margin-bottom:10px">
                        <input class="easyui-textbox" label="Preço Unitário:" name="pre_unitario" required style="width:100%;">
                    </div>
                    <div style="text-align:center; padding: 10px;">
                        <a href="#" class="easyui-linkbutton" data-options="iconCls:'icon-ok'" onclick="salvarNovoItem()">Salvar</a>
                        <a href="#" class="easyui-linkbutton" data-options="iconCls:'icon-cancel'" onclick="$('#dialogAddItem').dialog('close')">Cancelar</a>
                    </div>
                </form>`
            });
        });
    }

    function salvarNovoItem() {
        const form = $('#form_adiciona_item');
        var item = form.find('#cod_item_form').val();
        if (!item || item === "") {
            $.messager.alert('Erro', 'Selecione um item para continuar.', 'error');
            return;
        }

        if (!form.form('validate')) {
            $.messager.alert('Erro', 'Preencha todos os campos corretamente.', 'error');
            return;
        }
        $.ajax({
            url: 'item_adicionar.php',
            type: 'POST',
            data: form.serialize(),
            success: function() {
                $('#dialogAddItem').dialog('close');
                $('#dg').datagrid('reload');
                $('#dg_i').datagrid('reload');
                //location.reload(); // recarrega a tabela após sucesso
            },
            error: function() {
                $.messager.alert('Erro', 'Erro ao adicionar item.', 'error');
            }
        });
    }

    function removerItem() {
        var itemToDelete = null;
        var row = $('#dg_i').datagrid('getSelected');
        const num_pedido = <?= json_encode($num_pedido) ?>;

        // console.log("Linha selecionada:", row);
        if (row) {
            $.messager.confirm({
                title: 'Exclusão',
                msg: 'Tem certeza que deseja excluir esse item?',
                fn: function(r) {
                    if (r) {
                        itemToDelete = row;
                        var num_seq_item = itemToDelete.num_seq_item;
                        // console.log("Enviando para exclusão:", {
                        //     num_seq_item: num_seq_item,
                        //     num_pedido: num_pedido
                        // });
                        $.ajax({
                                url: 'item_excluir.php',
                                type: 'GET',
                                data: {
                                    num_seq_item: num_seq_item,
                                    num_pedido: num_pedido
                                }
                            })
                            .done(function(response) {
                                if (response.status) {
                                    $.messager.alert('Processamento Executado', response.msg, 'info', function() {
                                        $('#dg').datagrid('reload');
                                        $('#dg_i').datagrid('reload');
                                    });
                                } else {
                                    $.messager.alert('Erro no processamento', response.msg, 'error', function() {
                                        $('#dg').datagrid('reload');
                                        $('#dg_i').datagrid('reload');
                                    });
                                }
                            })
                            .fail(function(xhr, status, error) {
                                console.error('Erro na requisição:', status, error);
                                console.error('Resposta do servidor:', xhr.responseText);
                                $.messager.alert('Erro fatal', 'Erro ao tentar excluir o item. Verifique o console para mais detalhes.', 'error');
                            });

                    }
                }
            });
        } else {
            $.messager.alert('Atenção', 'Selecione um item para ser excluído!', 'info');
            $('#dg_i').datagrid('reload');

        }
    }

    function editarItem() {
        const row = $('#dg_i').datagrid('getSelected');
        if (!row) {
            $.messager.alert('Atenção', 'Selecione um item para editar.', 'warning');
            return;
        }

        window.location.href = `item_adicionar.php?num_pedido=<?= $num_pedido ?>&num_seq_item=${row.num_seq_item}`;
    }

    function cancelarItem() {
        $('#dg_i').datagrid('rejectChanges');
    }
</script>