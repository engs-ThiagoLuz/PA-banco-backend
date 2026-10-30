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
        main { max-width: 1000px; margin: 24px auto; padding: 0 16px; }
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
    </style>
</head>
<body>
    <header>
        <a href="{{ route('consultas.index') }}">Sistema Hospitalar</a>
        <a href="{{ route('consultas.index') }}">Consultas</a>
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
