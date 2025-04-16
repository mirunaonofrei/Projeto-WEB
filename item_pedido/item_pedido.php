<?php
require_once '../db.php';

function consulta_itens($conn, $num_pedido)
{
    $sql_itens = "SELECT it.num_seq_item, it.num_pedido, item.den_item, item.cod_item, it.qtd_solicitada, it.pre_unitario
                  FROM item_pedido AS it
                  INNER JOIN item ON it.cod_item = item.cod_item
                  WHERE it.num_pedido = :num_pedido
                  ORDER BY it.num_seq_item";

    $stmt_itens = $conn->prepare($sql_itens);
    $stmt_itens->bindParam(':num_pedido', $num_pedido, PDO::PARAM_INT);
    $stmt_itens->execute();
    $itens_result = $stmt_itens->fetchAll(PDO::FETCH_ASSOC);

    $total = 0;
    foreach ($itens_result as $key => $item) {
        $itens_result[$key]['total'] = $item['qtd_solicitada'] * $item['pre_unitario'];
        $total += $itens_result[$key]['total'];
    }

    return [
        'rows' => $itens_result,
        'total' => number_format($total, 2, ',', '.')
    ];
}

$num_pedido = $_GET['num_pedido'];
$dados_pedido = consulta_itens($conn, $num_pedido);
//showArray($dados_pedido);
$json_dados_pedido = json_encode($dados_pedido);
//showArray($json_dados_pedido);
?>

<table id="dg_i" class="easyui-datagrid" style="width:750px;" data-options="singleSelect:true">
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
        <?php
        $total_pedido = 0;
        foreach ($dados_pedido['rows'] as $item):
            $subtotal = $item['qtd_solicitada'] * $item['pre_unitario'];
            $total_pedido += $subtotal;
        ?>
            <tr>
                <td><?= $item['num_seq_item'] ?></td>
                <td><?= $item['den_item'] ?></td>
                <td><?= $item['qtd_solicitada'] ?></td>
                <td>R$ <?= number_format($item['pre_unitario'], 2, ',', '.') ?></td>
                <td>R$ <?= number_format($subtotal, 2, ',', '.') ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>

</table>
<div style="height:30px; width: 750px; background-color:rgb(155, 198, 255); display:flex; flex-direction: row;">
    <div style="align-items: end; width: 750px;">
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-add',plain:true" onclick="adicionarItem()">Adicionar</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick="removerItem()">Remover</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-edit',plain:true" onclick="editarItem()">Editar</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-undo',plain:true" onclick="cancelarItem()">Cancelar</a>
    </div>
</div>


<script type="text/javascript">
    function adicionarItem() {
        const num_pedido = <?= json_encode($num_pedido) ?>;

        if ($('#dialogAddItem').length) $('#dialogAddItem').remove();

        $('body').append('<div id="dialogAddItem"></div>');

        $.getJSON('item_pedido/item_adicionar.php', function(data) {
            let itemOptions = `<option value="">Selecione um item</option>`;
            data.itens_result.forEach(item => {
                itemOptions += `<option value="${item.cod_item}">${item.den_item}</option>`;
            });

            $('#dialogAddItem').dialog({
                title: 'Adicionar Item',
                width: 400,
                modal: true,
                content: `
                    <form id="form_adiciona_item">
                        <input type="hidden" name="num_pedido" value="${num_pedido}">
                        <div style="margin-bottom:10px">
                            <label>Item: </label>
                            <select class="easyui-combobox" name="cod_item" id="cod_item_form" required style="width:100%;">${itemOptions}</select>
                        </div>
                        <div style="margin-bottom:10px">
                            <input class="easyui-numberbox" label="Quantidade:" name="qtd_solicitada" required style="width:100%;">
                        </div>
                        <div style="margin-bottom:10px">
                            <input class="easyui-numberbox" label="Preço Unitário:" name="pre_unitario" id="pre_unitario" required precision="2" decimalSeparator="," groupSeparator="." style="width:100%;">
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
        const item = form.find('#cod_item_form').val();
        const qtd = form.find('[name="qtd_solicitada"]').val();
        const preco = form.find('[name="pre_unitario"]').val();

        if (!item) {
            $.messager.alert('Erro', 'Selecione um item para continuar.', 'error');
            return;
        }

        if ((qtd.match(/[.,]/g) || []).length > 1 || (preco.match(/[.,]/g) || []).length > 1) {
            $.messager.alert('Erro de Formato', 'Quantidade e preço devem usar apenas um separador decimal.', 'error');
            return;
        }

        if (!form.form('validate')) {
            $.messager.alert('Erro', 'Preencha todos os campos corretamente.', 'error');
            return;
        }

        $.ajax({
            url: 'item_pedido/item_adicionar.php',
            type: 'POST',
            data: form.serialize(),
            success: function() {
                $('#dialogAddItem').dialog('close');
                $('#dg').datagrid('reload');
                $('#dg_i').datagrid('reload');
            },
            error: function() {
                $.messager.alert('Erro', 'Erro ao adicionar item.', 'error');
            }
        });
    }

    function removerItem() {
        const row = $('#dg_i').datagrid('getSelected');
        const num_pedido = <?= json_encode($num_pedido) ?>;

        if (row) {
            $.messager.confirm('Exclusão', 'Tem certeza que deseja excluir esse item?', function(r) {
                if (r) {
                    $.ajax({
                        url: 'item_pedido/item_excluir.php',
                        type: 'GET',
                        data: {
                            num_seq_item: row.num_seq_item,
                            num_pedido: num_pedido
                        },
                        success: function(response) {
                            if (response.status) {
                                $.messager.alert('Sucesso', response.msg, 'info', function() {
                                    $('#dg').datagrid('reload');
                                    $('#dg_i').datagrid('reload');
                                });
                            } else {
                                $.messager.alert('Erro', response.msg, 'error');
                            }
                        },
                        error: function(xhr) {
                            console.error(xhr.responseText);
                            $.messager.alert('Erro', 'Erro ao excluir o item.', 'error');
                        }
                    });
                }
            });
        } else {
            $.messager.alert('Atenção', 'Selecione um item para ser excluído!', 'info');
        }
    }

    function editarItem() {
        const row = $('#dg_i').datagrid('getSelected');
        const num_pedido = <?= json_encode($num_pedido) ?>;

        if (!row) {
            $.messager.alert('Atenção', 'Selecione um item para ser editado!', 'info');
            return;
        }

        if ($('#dialogAddItem').length) $('#dialogAddItem').remove();

        $('body').append('<div id="dialogAddItem"></div>');

        $.getJSON('item_pedido/item_adicionar.php', function(data) {
            data.itens_result = data.itens_result.map(item => ({
                ...item,
                cod_item: item.cod_item.toString().trim(),
                den_item: item.den_item.toString().trim()
            }));

            const itemCorrespondente = data.itens_result.find(item => item.den_item === row.den_item.trim());
            const cod_item = itemCorrespondente?.cod_item || '';
            const den_item = itemCorrespondente?.den_item || '';

            $('#dialogAddItem').dialog({
                title: 'Editar Item',
                width: 400,
                modal: true,
                content: `
        <form id="form_edita_item">
            <input type="hidden" name="num_pedido" value="${num_pedido}">
            <input type="hidden" name="num_seq_item" value="${row.num_seq_item}">
            <div style="margin-bottom:10px">
                <label>Item: </label>
                <select class="easyui-combobox" name="den_item" id="den_item_form" required style="width:100%;"></select>
            </div>
            <div style="margin-bottom:10px">
                <input class="easyui-numberbox" label="Quantidade:" name="qtd_solicitada" required style="width:100%;" value="${row.qtd_solicitada}"/>
            </div>
            <div style="margin-bottom:10px">
                <input class="easyui-numberbox" label="Preço Unitário:" name="pre_unitario" required precision="2" decimalSeparator="," groupSeparator="." style="width:100%;" value="${parseFloat(row.pre_unitario.replace("R$", "").replace(".", "").replace(",", ".").trim())}"/>
            </div>
            <div style="text-align:center; padding: 10px;">
                <a href="#" class="easyui-linkbutton" data-options="iconCls:'icon-ok'" onclick="salvarEdicaoItem()">Salvar</a>
                <a href="#" class="easyui-linkbutton" data-options="iconCls:'icon-cancel'" onclick="$('#dialogAddItem').dialog('close')">Cancelar</a>
            </div>
        </form>`
            });

            $('#den_item_form').combobox({
                valueField: 'den_item',
                textField: 'den_item',
                data: data.itens_result,
                value: den_item,
                onSelect: function(selectedItem) {
                    console.log("Selecionado:", selectedItem);
                }
            });
        });
    }
    





    function salvarEdicaoItem() {
        const form = $('#form_edita_item');

        console.log(form.serialize())
        const qtd = form.find('[name="qtd_solicitada"]').val();
        const preco = form.find('[name="pre_unitario"]').val();
        if ((qtd.match(/[.,]/g) || []).length > 1 || (preco.match(/[.,]/g) || []).length > 1) {
            $.messager.alert('Erro de Formato', 'Quantidade e preço devem usar apenas um separador decimal.', 'error');
            return;
        }

        if (!form.form('validate')) {
            $.messager.alert('Erro', 'Preencha todos os campos corretamente.', 'error');
            return;
        }

        $.ajax({
            url: 'item_pedido/item_editar.php',
            type: 'POST',
            data: form.serialize(),
            dataType: 'json', // <- isso é importante para que o jQuery interprete o JSON corretamente
            success: function(response) {
                if (response.status) {
                    $.messager.alert('Sucesso', response.msg, 'info', function() {
                        $('#dg').datagrid('reload');
                        $('#dg_i').datagrid('reload');
                    });
                    $('#dialogAddItem').dialog('close');
                } else {
                    $.messager.alert('Erro', response.msg, 'error');
                }
            },
            error: function(xhr, status, error) {
                $.messager.alert('Erro', 'Erro ao editar o item: ' + error, 'error');
            }
        });

    }


    function cancelarItem() {
        $('#dg_i').datagrid('rejectChanges');
    }
</script>