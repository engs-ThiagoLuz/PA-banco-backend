<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo', 'Sistema Hospitalar')</title>
    <style>
        :root { --cor: #0f766e; --cor-escura: #115e59; --borda: #d4d4d8; --fundo: #f4f4f5; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: system-ui, sans-serif; background: var(--fundo); color: #18181b; }
        header { background: var(--cor); color: #fff; padding: 14px 24px; display: flex; gap: 24px; align-items: center; }
        header a { color: #fff; text-decoration: none; font-weight: 600; }
        main { max-width: 1440px; margin: 36px auto; padding: 0 32px; }
        .cartao { background: #fff; border: 1px solid var(--borda); border-radius: 8px; padding: 20px; margin-bottom: 20px; }
        h1 { margin-top: 0; font-size: 1.5rem; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 10px 8px; border-bottom: 1px solid var(--borda); }
        th { font-size: .8rem; text-transform: uppercase; color: #52525b; }
        .btn { display: inline-block; padding: 7px 14px; border-radius: 6px; border: 1px solid var(--cor); background: var(--cor); color: #fff; text-decoration: none; cursor: pointer; font-size: .9rem; }
        .btn:hover { background: var(--cor-escura); }
        .btn-sec { background: #fff; color: var(--cor); }
        .btn-sec:hover { background: #f0fdfa; }
        .btn-perigo { background: #b91c1c; border-color: #b91c1c; }
        .btn-perigo:hover { background: #991b1b; }
        .acoes { display: flex; gap: 6px; flex-wrap: wrap; }
        .acoes form { margin: 0; }
        .alerta { padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; }
        .alerta.sucesso { background: #dcfce7; border: 1px solid #86efac; }
        .alerta.erro { background: #fee2e2; border: 1px solid #fca5a5; }
        .campo { margin-bottom: 16px; }
        label { display: block; font-weight: 600; margin-bottom: 4px; }
        input, select, textarea { width: 100%; padding: 8px; border: 1px solid var(--borda); border-radius: 6px; font: inherit; }
        .erro-campo { color: #b91c1c; font-size: .85rem; margin-top: 4px; }
        .filtros { display: flex; gap: 10px; align-items: end; flex-wrap: wrap; margin-bottom: 16px; }
        .filtros .campo { margin: 0; flex: 1; min-width: 160px; }
        .selo { padding: 2px 10px; border-radius: 99px; font-size: .8rem; font-weight: 600; }
        .selo.agendada { background: #dbeafe; color: #1e40af; }
        .selo.realizada { background: #dcfce7; color: #166534; }
        .selo.cancelada { background: #fee2e2; color: #991b1b; }
        .topo { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
        .paginacao { display: flex; justify-content: space-between; align-items: center; margin-top: 16px; }
        .paginacao span.desativado { color: #a1a1aa; }
        dl { display: grid; grid-template-columns: 160px 1fr; gap: 8px 16px; margin: 0; }
        dt { font-weight: 600; color: #52525b; }
        dd { margin: 0; }
        body { background: #f5f7f8; color: #203537; font-size: 14px; }
        header { background: #fff; color: #203537; border-bottom: 1px solid #e0e8e7; padding: 18px 32px; }
        header a { color: #0f5f59; }
        .marca { display: flex; align-items: center; gap: 12px; }
        .marca-icone { display: grid; place-items: center; background: #0f766e; color: white; height: 34px; width: 34px; border-radius: 9px; font-size: 25px; }
        .nav-atual { margin-left: auto; padding: 8px 16px; background: #e8f4f1; border-radius: 6px; font-size: 13px; }
        h1 { font-size: 30px; letter-spacing: -.8px; margin-bottom: 8px; }
        h2 { font-size: 17px; margin: 0; }
        .sobrelinha { color: #6c8583; letter-spacing: 1.8px; font-size: 10px; font-weight: 700; margin: 0 0 12px; }
        .texto-suave { color: #708380; }
        .cabecalho-pagina { display: flex; align-items: center; justify-content: space-between; gap: 20px; margin-bottom: 28px; }
        .cabecalho-pagina p:last-child { margin: 0; }
        .btn { font-weight: 600; padding: 10px 16px; font-size: 13px; }
        .indicadores { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; margin-bottom: 28px; }
        .indicador { text-decoration: none; color: inherit; background: #fff; border: 1px solid #dee7e5; border-radius: 10px; padding: 20px 22px; display: flex; flex-direction: column; gap: 10px; border-top: 3px solid #0f766e; transition: border-color .15s, box-shadow .15s; }
        .indicador:hover { box-shadow: 0 4px 16px #183e3910; border-color: #7ab0a8; }
        .indicador > span { font-size: 13px; font-weight: 600; color: #536c68; }
        .indicador strong { font-size: 32px; font-weight: 650; letter-spacing: -1px; font-variant-numeric: tabular-nums; }
        .indicador small { font-size: 11px; color: #81918e; }
        .indicador-azul { border-top-color: #7da6d4; }.indicador-verde { border-top-color: #62a18b; }.indicador-cinza { border-top-color: #adb9bb; }
        .cartao { border-color: #dee7e5; border-radius: 10px; }
        .painel-consultas { padding: 0; overflow: hidden; }
        .titulo-painel { padding: 24px; display: flex; justify-content: space-between; gap: 12px; border-bottom: 1px solid #edf1f0; }
        .titulo-painel > span { font-size: 12px; }
        .filtros-agenda { padding: 22px 24px; margin: 0; display: grid; grid-template-columns: 1.5fr 1fr 1.3fr 1fr 1fr; gap: 16px; background: #fcfdfd; }
        .filtros-agenda .campo { min-width: 0; }
        .filtros-agenda label { font-size: 12px; color: #536c68; margin-bottom: 7px; }
        .filtros-agenda input, .filtros-agenda select { background: white; padding: 10px; border-color: #dce5e3; min-height: 40px; font-size: 13px; }
        .filtros-acoes { grid-column: 2 / -1; justify-content: flex-end; }
        .resultado-resumo { padding: 16px 24px; border-top: 1px solid #edf1f0; display: flex; justify-content: space-between; gap: 12px; font-size: 12px; }
        .tabela-container { overflow-x: auto; }
        .tabela-container table { min-width: 1080px; }
        .tabela-container th { background: #f6f9f8; color: #6d8380; font-size: 10px; letter-spacing: .6px; white-space: nowrap; }
        .tabela-container th, .tabela-container td { padding: 17px 16px; border-color: #edf1f0; }
        .tabela-container th:first-child, .tabela-container td:first-child { padding-left: 24px; }
        .tabela-container td { font-size: 12px; line-height: 1.5; }
        .tabela-container tbody tr:hover { background: #fbfdfc; }
        .linha-secundaria { display: block; font-size: 11px; color: #7d8e8b; margin-top: 3px; }
        .sem-quebra { white-space: nowrap; }.acoes-linha { flex-wrap: nowrap; gap: 12px; }
        .link-acao { border: 0; background: none; padding: 0; color: #0f766e; font: inherit; font-weight: 600; cursor: pointer; text-decoration: none; }
        .link-acao:hover { text-decoration: underline; }.link-perigo { color: #b65c5c; }
        .selo { display: inline-block; font-size: 10px; padding: 4px 10px; }
        .painel-consultas .paginacao { padding: 20px 24px; margin: 0; font-size: 12px; gap: 12px; }
        .desativado { opacity: .5; cursor: default; }
        .tabela-container td.estado-vazio { text-align: center; padding: 52px 24px; }
        .painel-consultas > .alerta { margin: 16px 24px; }.alerta ul { margin: 0; padding-left: 16px; }
        a:focus-visible, button:focus-visible, input:focus-visible, select:focus-visible, [tabindex]:focus-visible { outline: 3px solid #5db9ad; outline-offset: 3px; }
        @media (max-width: 1000px) { .filtros-agenda { grid-template-columns: repeat(3, minmax(0, 1fr)); }.filtros-acoes { grid-column: 1 / -1; }.indicadores { gap: 10px; }.indicador { padding: 16px; } }
        @media (max-width: 640px) { main { margin: 24px auto; padding: 0 16px; }header { padding: 16px; gap: 12px; }.marca { gap: 8px; }.indicadores { grid-template-columns: repeat(2, minmax(0, 1fr)); }.indicador strong { font-size: 28px; }.cabecalho-pagina { align-items: flex-start; flex-direction: column; }h1 { font-size: 27px; }.filtros-agenda { grid-template-columns: repeat(2, minmax(0, 1fr)); padding: 18px; }.campo-busca { grid-column: 1 / -1; }.titulo-painel { padding: 18px; flex-direction: column; }.resultado-resumo { padding: 16px 18px; }.painel-consultas .paginacao { padding: 16px 12px; }.paginacao .btn { padding: 8px; }dl { grid-template-columns: 1fr; }dd { margin-bottom: 12px; } }
    </style>
</head>
<body>
    <header>
        <a class="marca" href="{{ route('consultas.index') }}"><span class="marca-icone" aria-hidden="true">+</span>Sistema Hospitalar</a>
        <a class="nav-atual" href="{{ route('consultas.index') }}" aria-current="page">Consultas</a>
    </header>

    <main>
        @if (session('sucesso'))
            <div class="alerta sucesso">{{ session('sucesso') }}</div>
        @endif
        @if (session('erro'))
            <div class="alerta erro">{{ session('erro') }}</div>
        @endif

        @yield('conteudo')
    </main>
</body>
</html>
