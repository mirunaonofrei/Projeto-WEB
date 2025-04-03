function abrirDialogAdicionarPedido() {
    // Obtém os dados do servidor
    $.getJSON('adicionar_pedido.php', function (data) {
        if ($('#dialogAddPedido').length) {
            $('#dialogAddPedido').remove();
        }

        // Criando a div do dialog
        $('body').append('<div id="dialogAddPedido"></div>');

        let clienteOptions = `<option value="">Selecione um cliente</option>`;
        data.clientes.forEach(cliente => {
            clienteOptions += `<option value="${cliente.cod_cliente}">${cliente.nom_cliente}</option>`;
        });

        // Usando template literals para o HTML dinâmico
        $('#dialogAddPedido').dialog({
            title: 'Adicionar Pedido',
            width: 400,
            height: 'auto',
            modal: true,
            content: `<form id="ff">
                <div style="margin-bottom:10px">
                    <input class="easyui-textbox" label="Número do Pedido:" name="num_pedido" value="${data.num_pedido}" readonly style="width:100%;">
                </div>

                <div style="margin-bottom:10px">
                    <select class="easyui-combobox" label="Cliente:" name="cod_cliente" required style="width:100%;">
                        ${clienteOptions}
                    </select>
                </div>

                <div style="text-align:center; padding-top: 10px;">
                    <a href="gerenciar_pedidos.php" class="easyui-linkbutton" data-options="iconCls:'icon-back'">Voltar</a>
                    <a href="#" class="easyui-linkbutton" data-options="iconCls:'icon-ok'" onclick="salvarForm()">Salvar</a>
                </div>
            </form>`,
            buttons: [{
                text: 'Fechar',
                handler: function () {
                    $('#dialogAddPedido').dialog('close');
                }
            }]
        });
    });
}

function salvarForm() {
    $.ajax({
        url: 'adicionar_pedido.php',
        type: 'POST',
        data: $('#ff').serialize(),
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                // Exibindo o alerta após o fechamento do diálogo
                $.messager.alert('Sucesso', response.msg, 'info', function () {
                    $('#dg').datagrid('reload'); // Recarrega o datagrid (se existir)

                    $('#dialogAddPedido').dialog('close');
                });
            } else {
                $.messager.alert('Erro', 'Ocorreu um erro ao adicionar o pedido.', 'error');
            }
        },
        error: function () {
            $.messager.alert('Erro', 'Falha na comunicação com o servidor.', 'error');
        }
    });
}
