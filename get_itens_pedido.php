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
    <table id="dg_i" class="easyui-datagrid" style="width:750px;" data-options="footer:'#ft_dg_i'">
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
        $.getJSON('adicionar_item.php', function(data) {
            if ($('#dialogAddItem').length) {
                $('#dialogAddItem').remove();
            }

            $('body').append('<div id="dialogAddItem"></div>');

            let clienteOptions = `<option value="">Selecione um item</option>`;
            data.clientes.forEach(cliente => {
                clienteOptions += `<option value="${cliente.cod_cliente}">${cliente.nom_cliente}</option>`;
            });

            $('#dialogAddItem').dialog({
                title: 'Adicionar Pedido',
                width: 400,
                height: 'auto',
                modal: true,
                content: `<form id="form_adiciona_pedido">
                    <div style="margin-bottom:10px">
                        <input class="easyui-textbox" label="Número do Pedido:" name="num_pedido" value="${data.num_pedido}" readonly style="width:100%;">
                    </div>

                    <div style="margin-bottom:10px">
                        <select class="easyui-combobox" label="Cliente:" name="cod_cliente" id="cod_cliente_form" required style="width:100%;">
                            ${clienteOptions}
                        </select>
                    </div>

                    <div style="text-align:center; padding-top: 10px;">
                        <a href="gerenciar_pedidos.php" class="easyui-linkbutton" data-options="iconCls:'icon-back'">Voltar</a>
                        <a href="#" class="easyui-linkbutton" data-options="iconCls:'icon-ok'" onclick="salvarNovoPedido()">Salvar</a>
                    </div>
                </form>`,
                buttons: [{
                    text: 'Fechar',
                    handler: function() {
                        $('#dialogAddItem').dialog('close');
                    }
                }]
            });
        });
    }

    function salvarNovoItem() {
        var form = $('#dialogAddItem').find('form');

        if (!form.length) {
            $.messager.alert('Erro', 'Nenhum formulário encontrado.', 'error');
            return;
        }

        var cliente = form.find('#cod_cliente_form').val();
        if (!cliente || cliente === "") {
            $.messager.alert('Erro', 'Selecione um cliente para continuar.', 'error');
            return;
        }

        $.ajax({
            url: 'adicionar_pedido.php',
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    $('#dg').datagrid('reload');
                    $('#dialogAddItem').dialog('close');
                    $.messager.alert('Sucesso', "Pedido incluído com sucesso!", 'info');
                } else {
                    $.messager.alert('Erro', response.msg || "Erro ao incluir pedido!", 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error("Erro AJAX:", xhr.responseText);
                $.messager.alert('Erro', 'Falha na comunicação com o servidor.', 'error');
            }
        });
    }

    function editarItem() {
        var row = $('#dg').datagrid('getSelected'); // Seleciona a linha do pedido
        if (row) {
            var pedidoToEdit = row; // Armazena o pedido selecionado
            var num_pedido = pedidoToEdit.num_pedido;

            $.getJSON('editar_pedido.php', {
                num_pedido: num_pedido
            }, function(data) {
                if ($('#dialogAddItem').length) {
                    $('#dialogAddItem').remove();
                }

                $('body').append('<div id="dialogAddItem"></div>');

                let clienteOptions = `<option value="">Selecione um cliente</option>`;
                data.clientes.forEach(cliente => {
                    clienteOptions += `<option value="${cliente.cod_cliente}" ${cliente.cod_cliente == data.cod_cliente ? 'selected' : ''}>${cliente.nom_cliente}</option>`;
                });

                $('#dialogAddItem').dialog({
                    title: 'Editar Pedido',
                    width: 400,
                    height: 'auto',
                    modal: true,
                    content: `<form id="form_edita_pedido">
                    <div style="margin-bottom:10px">
                        <input class="easyui-textbox" label="Número do Pedido:" name="num_pedido" value="${data.num_pedido}" readonly style="width:100%;">
                    </div>
                    <div style="margin-bottom:10px">
                        <select class="easyui-combobox" label="Cliente:" name="cod_cliente" id="cod_cliente_form" required style="width:100%;">
                            ${clienteOptions}
                        </select>
                    </div>
                    <div style="text-align:center; padding-top: 10px;">
                        <a href="gerenciar_pedidos.php" class="easyui-linkbutton" data-options="iconCls:'icon-back'">Voltar</a>
                        <a href="#" class="easyui-linkbutton" data-options="iconCls:'icon-ok'" onclick="salvarEdicaoPedido()">Salvar</a>
                    </div>
                </form>`,
                    buttons: [{
                        text: 'Fechar',
                        handler: function() {
                            $('#dialogAddItem').dialog('close');
                        }
                    }]
                });



            }).fail(function() {
                $.messager.alert('Erro', 'Erro ao carregar os dados do pedido!', 'error');
            });

        } else {
            $.messager.alert('Atenção', 'Selecione um pedido para ser editado!', 'info');
            $('#dg').datagrid('reload');
        }
    }

    function salvarEdicaoItem() {
        var form = $('#dialogAddItem').find('form');

        if (!form.length) {
            $.messager.alert('Erro', 'Nenhum formulário encontrado.', 'error');
            return;
        }

        var cliente = form.find('#cod_cliente_form').val();
        if (!cliente || cliente === "") {
            $.messager.alert('Erro', 'Selecione um cliente para continuar.', 'error');
            return;
        }

        $.ajax({
            url: 'editar_pedido.php', 
            type: 'POST', 
            data: form.serialize(), // Envia os dados do formulário
            dataType: 'json', // Espera uma resposta JSON
            success: function(response) {
                if (response.status) {
                    $('#dg').datagrid('reload'); // Recarrega a tabela após sucesso
                    $('#dialogAddItem').dialog('close'); // Fecha o formulário de edição
                    $.messager.alert('Sucesso', "Pedido atualizado com sucesso!", 'info');
                } else {
                    $.messager.alert('Erro', response.message || "Erro ao atualizar pedido!", 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error("Erro AJAX:", xhr.responseText);
                $.messager.alert('Erro', 'Falha na comunicação com o servidor.', 'error');
            }
        });
    }

    function removerItem() {
        var pedidoToDelete = null; // Variável para armazenar o pedido a ser excluído
        var row = $('#dg').datagrid('getSelected'); // Seleciona a linha do pedido
        if (row) {
            $.messager.confirm({
                title: 'Exclusão',
                msg: 'Tem certeza que deseja excluir esse pedido?',
                fn: function(r) {
                    if (r) {
                        pedidoToDelete = row; // Armazena o pedido selecionado
                        var num_pedido = pedidoToDelete.num_pedido;
                        $.ajax({
                                url: 'excluir_pedido.php',
                                type: 'GET',
                                data: {
                                    num_pedido: num_pedido
                                }
                            })
                            .done(function(response) { // Quando a requisição for bem-sucedida
                                let jsonResponse = JSON.parse(response);

                                if (jsonResponse.status) {
                                    return $.messager.alert('Processamento Executado', jsonResponse.msg, 'info', function(r) {
                                        $('#dg').datagrid('reload');
                                    });
                                }

                                $.messager.alert('Erro no processamento', jsonResponse.msg, 'error', function(r) {
                                    $('#dg').datagrid('reload');
                                });
                            })
                    }
                }
            });
        } else {
            $.messager.alert('Atenção', 'Selecione um pedido para ser excluído!', 'info');
            $('#dg').datagrid('reload');

        }
    }

    function cancelarItem() {
        $('#dg_i').datagrid('rejectChanges');
        editIndex = undefined;
    }
</script>