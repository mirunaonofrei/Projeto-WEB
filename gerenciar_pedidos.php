<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>PEDIDOS</title>
    <link rel="stylesheet" type="text/css" href="https://www.jeasyui.com/easyui/themes/default/easyui.css">
    <link rel="stylesheet" type="text/css" href="https://www.jeasyui.com/easyui/themes/icon.css">
    <script type="text/javascript" src="https://www.jeasyui.com/easyui/jquery.min.js"></script>
    <script type="text/javascript" src="https://www.jeasyui.com/easyui/jquery.easyui.min.js"></script>
    <script type="text/javascript" src="https://www.jeasyui.com/easyui/datagrid-detailview.js"></script>
    <script type="text/javascript" src="https://www.jeasyui.com/easyui/src/jquery.window.js"></script>


    <style>
        body {
            background-color: rgb(224, 237, 255);
        }

        .buttons {
            display: flex;
            flex-direction: row;
            justify-content: flex-end;
            background-color: rgb(155, 198, 255);
        }

        .datagrid-header,
        .datagrid-htable {
            background-color: rgb(155, 198, 255) !important;
        }
    </style>
</head>

<body>
    <div id="p" class="easyui-panel" title="Pedidos" style="width:100%;height:100%;padding:10px; background-color:rgb(210, 229, 255);" data-options="footer:'#ft'">
        <table id="dg" class="easyui-datagrid" style="height:450px; width: 100%;"
            data-options="
                        singleSelect:true,
                        fit:true, 
                        fitColumns:true, 
                        view:detailview,
                        footer:'#ft_dg',
                        url: 'pedido/buscar_pedidos.php',
                    ">
            <thead>
                <tr>
                    <th data-options="field:'num_pedido', width:'30%'">Pedido</th>
                    <th data-options="field:'nom_cliente', width:'39.5%'">Cliente</th>
                    <th data-options="field:'total_pedido', width:'30%'">Total do Pedido</th>
                </tr>
            </thead>
        </table>


    </div>

    <div id="ft_dg" style="height:auto; background-color:rgb(155, 198, 255);">
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-add',plain:true" onclick="adicionar()">Adicionar</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-remove',plain:true" onclick="remover()">Remover</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-edit',plain:true" onclick="editar()">Editar</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-undo',plain:true" onclick="cancelar()">Cancelar</a>
    </div>
    <div id="ft" class="buttons">
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-large-smartart',plain:true" onclick="gerenciar_itens()">Gerenciar Itens</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" data-options="iconCls:'icon-large-smartart',plain:true" onclick="gerenciar_clientes()">Gerenciar Clientes</a>
    </div>


</body>

</html>


<script type="text/javascript">
    $(function() {
        $('#dg').datagrid({
            detailFormatter: function(index, row) {
                return '<div class="ddv" style="padding:5px 0"></div>';
            },
            onExpandRow: function(index, row) {
                // Fecha outras linhas
                const allRows = $('#dg').datagrid('getRows');
                for (let i = 0; i < allRows.length; i++) {
                    if (i !== index) {
                        $('#dg').datagrid('collapseRow', i);
                    }
                }
                // Carrega os detalhes da linha expandida
                //$('#dg_i').datagrid('clearSelections');
                var ddv = $(this).datagrid('getRowDetail', index).find('div.ddv');
                ddv.panel({
                    href: 'item_pedido/item_pedido.php?num_pedido=' + row.num_pedido,
                    border: false,
                    cache: false,
                    footer: '<div></div>',
                    onLoad: function() {
                        $('#dg').datagrid('fixDetailRowHeight', index);
                    }
                });
                setTimeout(function() {
                    $('#dg').datagrid('fixDetailRowHeight', index);
                }, 0);
            }
        });
    });

    function adicionar() {
        $.getJSON('pedido/pedido_adicionar.php', function(data) {
            if ($('#dialogAddPedido').length) {
                $('#dialogAddPedido').remove();
            }

            $('body').append('<div id="dialogAddPedido"></div>');

            let clienteOptions = `<option value="">Selecione um cliente</option>`;
            data.clientes.forEach(cliente => {
                clienteOptions += `<option value="${cliente.cod_cliente}">${cliente.nom_cliente}</option>`;
            });

            $('#dialogAddPedido').dialog({
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
                        $('#dialogAddPedido').dialog('close');
                    }
                }]
            });
        });
    }

    function salvarNovoPedido() {
        var form = $('#dialogAddPedido').find('form');

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
            url: 'pedido/pedido_adicionar.php',
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    $('#dg').datagrid('reload');
                    $('#dialogAddPedido').dialog('close');
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

    function editar() {
        var row = $('#dg').datagrid('getSelected'); // Seleciona a linha do pedido
        if (row) {
            var pedidoToEdit = row; // Armazena o pedido selecionado
            var num_pedido = pedidoToEdit.num_pedido;

            $.getJSON('pedido/pedido_editar.php', {
                num_pedido: num_pedido
            }, function(data) {
                if ($('#dialogAddPedido').length) {
                    $('#dialogAddPedido').remove();
                }

                $('body').append('<div id="dialogAddPedido"></div>');

                let clienteOptions = `<option value="">Selecione um cliente</option>`;
                data.clientes.forEach(cliente => {
                    clienteOptions += `<option value="${cliente.cod_cliente}" ${cliente.cod_cliente == data.cod_cliente ? 'selected' : ''}>${cliente.nom_cliente}</option>`;
                });

                $('#dialogAddPedido').dialog({
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
                            $('#dialogAddPedido').dialog('close');
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

    function salvarEdicaoPedido() {
        var form = $('#dialogAddPedido').find('form');

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
            url: 'pedido/pedido_editar.php',
            type: 'POST',
            data: form.serialize(), // Envia os dados do formulário
            dataType: 'json', // Espera uma resposta JSON
            success: function(response) {
                if (response.status) {
                    $('#dg').datagrid('reload'); // Recarrega a tabela após sucesso
                    $('#dialogAddPedido').dialog('close'); // Fecha o formulário de edição
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

    function remover() {
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
                                url: 'pedido/pedido_excluir.php',
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

    function cancelar() {
        $('#dg').datagrid('rejectChanges');
        editIndex = undefined;
    }

    function gerenciar_itens() {
        $('#dg').datagrid('unselectAll');
        $('#dg_i').datagrid('unselectAll');
        if ($('#dialogGerenciarItens').length) {
            $('#dialogGerenciarItens').remove();
        }

        $('body').append('<div id="dialogGerenciarItens"></div>');

        $('#dialogGerenciarItens').dialog({
            title: 'Gerenciar Itens',
            width: 800,
            height: 400,
            closed: false,
            cache: false,
            modal: true,
            buttons: [{
                text: 'Adicionar',
                iconCls: 'icon-add',
                handler: function() {
                    abrirFormularioAdicionaItem();
                }
            }, {
                text: 'Remover',
                iconCls: 'icon-remove',
                handler: function() {
                    let item = $('#dgItens').datagrid('getSelected');

                    if (!item) {
                        $.messager.alert('Atenção', 'Selecione um item para remover.', 'warning');
                        return;
                    }
                    $.messager.confirm('Confirmação', 'Deseja realmente remover este item?', function(r) {
                        if (r) {
                            excluirItem(item.cod_item);
                        }
                    });
                }

            }, {
                text: 'Editar',
                iconCls: 'icon-edit',
                handler: function() {
                    let item = $('#dgItens').datagrid('getSelected');
                    if (!item) {
                        $.messager.alert('Atenção', 'Selecione um item para editar.', 'warning');
                        return;
                    }
                    abrirFormularioEditaItem(item.cod_item);
                }
            }, {
                text: 'Cancelar',
                iconCls: 'icon-undo',
                handler: function() {
                    $('#dialogGerenciarItens').dialog('close');
                }
            }]
        });

        $('#dialogGerenciarItens').html('<table id="dgItens"></table>');

        $('#dgItens').datagrid({
            url: 'item/buscar_itens.php',
            columns: [
                [{
                        field: 'cod_item',
                        title: 'Código',
                        width: 100
                    },
                    {
                        field: 'den_item',
                        title: 'Nome do Item',
                        width: 200
                    }
                ]
            ],
            pagination: true,
            singleSelect: true,
            fitColumns: true
        });
    }

    function abrirFormularioAdicionaItem() {
        $.getJSON('item/itens_adicionar.php', function(data) {

            $('body').append(`<div id="dialogItemForm" style="padding:10px"></div>`);

            $('#dialogItemForm').dialog({
                width: 400,
                height: 300,
                modal: true,
                content: `<form id="formItem">
            <div style="margin-bottom:10px">
                <input name="cod_item" class="easyui-textbox" label="Código:" value="${data.cod_item} "style="width:100%" readonly>
            </div>
            <div style="margin-bottom:10px">
                <input name="den_item" class="easyui-textbox" label="Nome do Item:" style="width:100%" required>
            </div>
            <div style="text-align:center; padding-top: 10px;">
                        <a href="gerenciar_pedidos.php" class="easyui-linkbutton" data-options="iconCls:'icon-back'">Voltar</a>
                        <a href="#" class="easyui-linkbutton" data-options="iconCls:'icon-ok'" onclick="adicionarNovoItem()">Salvar</a>
                    </div>
                    </form>`,
                buttons: [{
                    text: 'Fechar',
                    handler: function() {
                        $('#dialogItemForm').dialog('close');
                    }
                }]
            });

        });
    }

    function abrirFormularioEditaItem(cod_item) {
        $.getJSON('item/itens_editar.php', {
            cod_item: cod_item
        }, function(data) {
            if ($('#dialogEditItem').length) {
                $('#dialogEditItem').remove();
            }
            $('body').append('<div id="dialogEditItem"></div>');

            $('#dialogEditItem').dialog({
                title: 'Editar Item',
                width: 400,
                height: 300,
                modal: true,
                content: `<form id="formItem">
                <div style="margin-bottom:10px">
                    <input name="cod_item" class="easyui-textbox" label="Código:" value="${data.cod_item}" style="width:100%" readonly>
                </div>
                <div style="margin-bottom:10px">
                    <input name="den_item" class="easyui-textbox" label="Nome do Item:" value="${data.den_item}" style="width:100%" required>
                </div>
                <div style="text-align:center; padding-top: 10px;">
                    <a href="gerenciar_pedidos.php" class="easyui-linkbutton" data-options="iconCls:'icon-back'">Voltar</a>
                    <a href="#" class="easyui-linkbutton" data-options="iconCls:'icon-ok'" onclick="salvaEdicaoItem()">Salvar</a>
                </div>
            </form>`,
                buttons: [{
                    text: 'Fechar',
                    handler: function() {
                        $('#dialogEditItem').dialog('close');
                    }
                }]
            });
        }).fail(function() {
            $.messager.alert('Erro', 'Erro ao carregar os dados do item!', 'error');
        });
    }


    function excluirItem(cod_item) {
        $.post('item/itens_remover.php', {
            cod_item: cod_item
        }, function(res) {
            if (res.status) {
                $('#dgItens').datagrid('reload');
                $.messager.alert('Sucesso', res.msg, 'info');
                $('#dg').datagrid('reload');
            } else {
                $.messager.alert('Erro', res.msg, 'error');
            }
        }, 'json');
    }

    function adicionarNovoItem() {
        var form = $('#dialogItemForm').find('form');

        if (!form.length) {
            $.messager.alert('Erro', 'Nenhum formulário encontrado.', 'error');
            return;
        }

        var den_item = form.find('[name="den_item"]').val();
        if (!den_item || den_item === "") {
            $.messager.alert('Erro', 'Informe o nome do item para continuar.', 'error');
            return;
        }

        $.ajax({
            url: 'item/itens_adicionar.php',
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    $('#dgItens').datagrid('reload');
                    $('#dialogItemForm').dialog('close');
                    $.messager.alert('Sucesso', "Item incluído com sucesso!", 'info');
                    $('#dg').datagrid('reload');
                } else {
                    $.messager.alert('Erro', response.msg || "Erro ao incluir item!", 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error("Erro AJAX:", xhr.responseText);
                $.messager.alert('Erro', 'Falha na comunicação com o servidor.', 'error');
            }
        });
    }

    function salvaEdicaoItem() {
        var form = $('#dialogEditItem').find('form');

        if (!form.length) {
            $.messager.alert('Erro', 'Nenhum formulário encontrado.', 'error');
            return;
        }

        var den_item = form.find('[name="den_item"]').val();
        if (!den_item || den_item === "") {
            $.messager.alert('Erro', 'Informe o nome do item para continuar.', 'error');
            return;
        }

        $.ajax({
            url: 'item/itens_editar.php',
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    $('#dgItens').datagrid('reload'); // Recarrega a tabela de itens
                    $('#dialogEditItem').dialog('close'); // Fecha o diálogo após sucesso
                    $.messager.alert('Sucesso', "Item atualizado com sucesso!", 'info');
                    $('#dg').datagrid('reload');
                } else {
                    $.messager.alert('Erro', response.message || "Erro ao atualizar item!", 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error("Erro AJAX:", xhr.responseText);
                $.messager.alert('Erro', 'Falha na comunicação com o servidor.', 'error');
            }
        });
    }




    function gerenciar_clientes() {
        if ($('#dialogGerenciarClientes').length) {
            $('#dialogGerenciarClientes').remove();
        }

        $('body').append('<div id="dialogGerenciarClientes"></div>');

        $('#dialogGerenciarClientes').dialog({
            title: 'Gerenciar Clientes',
            width: 800,
            height: 400,
            closed: false,
            cache: false,
            modal: true,
            buttons: [{
                text: 'Adicionar',
                iconCls: 'icon-add',
                handler: function() {
                    abrirFormularioAdicionaCliente();
                }
            }, {
                text: 'Remover',
                iconCls: 'icon-remove',
                handler: function() {
                    let cliente = $('#dgClientes').datagrid('getSelected');

                    if (!cliente) {
                        $.messager.alert('Atenção', 'Selecione um cliente para remover.', 'warning');
                        return;
                    }
                    $.messager.confirm('Confirmação', 'Deseja realmente remover este cliente?', function(r) {
                        if (r) {
                            excluirCliente(cliente.cod_cliente);
                        }
                    });
                }

            }, {
                text: 'Editar',
                iconCls: 'icon-edit',
                handler: function() {
                    let cliente = $('#dgClientes').datagrid('getSelected');
                    if (!cliente) {
                        $.messager.alert('Atenção', 'Selecione um cliente para editar.', 'warning');
                        return;
                    }
                    abrirFormularioEditaCliente(cliente.cod_cliente);
                }
            }, {
                text: 'Cancelar',
                iconCls: 'icon-undo',
                handler: function() {
                    $('#dialogGerenciarClientes').dialog('close');
                }
            }]
        });

        $('#dialogGerenciarClientes').html('<table id="dgClientes"></table>');

        $('#dgClientes').datagrid({
            url: 'cliente/buscar_clientes.php',
            columns: [
                [{
                        field: 'cod_cliente',
                        title: 'Código',
                        width: 100
                    },
                    {
                        field: 'nom_cliente',
                        title: 'Nome do cliente',
                        width: 200
                    }
                ]
            ],
            pagination: true,
            singleSelect: true,
            fitColumns: true
        });
    }

    function abrirFormularioAdicionaCliente() {
        $.getJSON('cliente/cliente_adicionar.php', function(data) {

            $('body').append(`<div id="dialogClienteForm" style="padding:10px"></div>`);

            $('#dialogClienteForm').dialog({
                width: 400,
                height: 300,
                modal: true,
                content: `<form id="formCliente">
            <div style="margin-bottom:10px">
                <input name="cod_cliente" class="easyui-textbox" label="Código:" value="${data.cod_cliente} "style="width:100%" readonly>
            </div>
            <div style="margin-bottom:10px">
                <input name="nom_cliente" class="easyui-textbox" label="Nome do Cliente:" style="width:100%" required>
            </div>
            <div style="text-align:center; padding-top: 10px;">
                        <a href="gerenciar_pedidos.php" class="easyui-linkbutton" data-options="iconCls:'icon-back'">Voltar</a>
                        <a href="#" class="easyui-linkbutton" data-options="iconCls:'icon-ok'" onclick="adicionarNovoCliente()">Salvar</a>
                    </div>
                    </form>`,
                buttons: [{
                    text: 'Fechar',
                    handler: function() {
                        $('#dialogClienteForm').dialog('close');
                    }
                }]
            });

        });
    }

    function abrirFormularioEditaCliente(cod_cliente) {
        $.getJSON('cliente/cliente_editar.php', {
            cod_cliente: cod_cliente
        }, function(data) {
            if ($('#dialogEditCliente').length) {
                $('#dialogEditCliente').remove();
            }
            $('body').append('<div id="dialogEditCliente"></div>');

            $('#dialogEditCliente').dialog({
                title: 'Editar Cliente',
                width: 400,
                height: 300,
                modal: true,
                content: `<form id="formCliente">
                <div style="margin-bottom:10px">
                    <input name="cod_cliente" class="easyui-textbox" label="Código:" value="${data.cod_cliente}" style="width:100%" readonly>
                </div>
                <div style="margin-bottom:10px">
                    <input name="nom_cliente" class="easyui-textbox" label="Nome do Cliente:" value="${data.nom_cliente}" style="width:100%" required>
                </div>
                <div style="text-align:center; padding-top: 10px;">
                    <a href="gerenciar_pedidos.php" class="easyui-linkbutton" data-options="iconCls:'icon-back'">Voltar</a>
                    <a href="#" class="easyui-linkbutton" data-options="iconCls:'icon-ok'" onclick="salvaEdicaoCliente()">Salvar</a>
                </div>
            </form>`,
                buttons: [{
                    text: 'Fechar',
                    handler: function() {
                        $('#dialogEditCliente').dialog('close');
                    }
                }]
            });
        }).fail(function() {
            $.messager.alert('Erro', 'Erro ao carregar os dados do cliente!', 'error');
        });
    }


    function excluirCliente(cod_cliente) {
        console.log(cod_cliente)
        $.post('cliente/cliente_remover.php', {
            cod_cliente: cod_cliente
        }, function(res) {
            if (res.status) {
                $('#dgClientes').datagrid('reload');
                $.messager.alert('Sucesso', res.msg, 'info');
            } else {
                $.messager.alert('Erro', res.msg, 'error');
            }
        }, 'json');
    }

    function adicionarNovoCliente() {
        var form = $('#dialogClienteForm').find('form');

        if (!form.length) {
            $.messager.alert('Erro', 'Nenhum formulário encontrado.', 'error');
            return;
        }

        var nom_cliente = form.find('[name="nom_cliente"]').val();
        if (!nom_cliente || nom_cliente === "") {
            $.messager.alert('Erro', 'Informe o nome do cliente para continuar.', 'error');
            return;
        }

        $.ajax({
            url: 'cliente/cliente_adicionar.php',
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    $('#dgClientes').datagrid('reload');
                    $('#dg').datagrid('reload');
                    $('#dialogClienteForm').dialog('close');
                    $.messager.alert('Sucesso', "Cliente incluído com sucesso!", 'info');
                } else {
                    $.messager.alert('Erro', response.msg || "Erro ao incluir cliente!", 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error("Erro AJAX:", xhr.responseText);
                $.messager.alert('Erro', 'Falha na comunicação com o servidor.', 'error');
            }
        });
    }

    function salvaEdicaoCliente() {
        var form = $('#dialogEditCliente').find('form');

        if (!form.length) {
            $.messager.alert('Erro', 'Nenhum formulário encontrado.', 'error');
            return;
        }

        var nom_cliente = form.find('[name="nom_cliente"]').val();
        if (!nom_cliente || nom_cliente === "") {
            $.messager.alert('Erro', 'Informe o nome do cliente para continuar.', 'error');
            return;
        }

        $.ajax({
            url: 'cliente/cliente_editar.php',
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    $('#dg').datagrid('reload');
                    $('#dgClientes').datagrid('reload'); // Recarrega a tabela de clientes
                    $('#dialogEditCliente').dialog('close'); // Fecha o diálogo após sucesso
                    $.messager.alert('Sucesso', "Cliente atualizado com sucesso!", 'info');
                } else {
                    $.messager.alert('Erro', response.message || "Erro ao atualizar cliente!", 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error("Erro AJAX:", xhr.responseText);
                $.messager.alert('Erro', 'Falha na comunicação com o servidor.', 'error');
            }
        });
    }
</script>