[CmdletBinding()]
param(
    [string] $PostgresBin = 'C:\Program Files\PostgreSQL\18\bin',
    [string] $BancoRestauracao = ('hospital_backup_' + (Get-Date -Format 'yyyyMMdd_HHmmss'))
)

$ErrorActionPreference = 'Stop'
$projectPath = (Resolve-Path (Join-Path $PSScriptRoot '..\..')).Path
$dumpTool = Join-Path $PostgresBin 'pg_dump.exe'
$sqlTool = Join-Path $PostgresBin 'psql.exe'
if (!(Test-Path -LiteralPath $dumpTool) -or !(Test-Path -LiteralPath $sqlTool)) {
    throw 'Informe -PostgresBin com a pasta bin do PostgreSQL (pg_dump e psql).'
}
if ($BancoRestauracao -notmatch '^hospital_backup_[a-z0-9_]{1,40}$') {
    throw 'Use um banco novo com prefixo hospital_backup_ e apenas letras minusculas, numeros e sublinhado.'
}

$environmentNames = @('PGHOST', 'PGPORT', 'PGDATABASE', 'PGUSER', 'PGPASSWORD', 'PGSSLMODE', 'PGCONNECT_TIMEOUT', 'PGOPTIONS', 'PGCLIENTENCODING')
$previousEnvironment = @{}
foreach ($variableName in $environmentNames) {
    $previousEnvironment[$variableName] = [Environment]::GetEnvironmentVariable($variableName, 'Process')
}

Push-Location -LiteralPath $projectPath
try {
    # Le a configuracao efetiva do Laravel sem exibir usuario ou senha.
    $configurationReader = @'
<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$connection = Illuminate\Support\Facades\DB::connection();
echo json_encode($connection->getConfig(), JSON_THROW_ON_ERROR);
'@
    $configurationJson = $configurationReader | & php
    if ($LASTEXITCODE -ne 0) { throw 'Nao foi possivel ler a configuracao do Laravel.' }
    $configuration = ($configurationJson -join "`n") | ConvertFrom-Json
    if ($configuration.driver -ne 'pgsql') { throw 'Configure PostgreSQL no Laravel.' }
    if ($configuration.host -match '-pooler') { throw 'Use o host direto do Neon, sem -pooler, no .env.' }
    if ($BancoRestauracao -eq $configuration.database) { throw 'O destino deve ser diferente da origem.' }

    $env:PGHOST = $configuration.host
    $env:PGPORT = [string] $configuration.port
    $env:PGDATABASE = $configuration.database
    $env:PGUSER = $configuration.username
    $env:PGPASSWORD = $configuration.password
    $env:PGSSLMODE = $configuration.sslmode
    $env:PGCONNECT_TIMEOUT = '20'
    $env:PGOPTIONS = ''
    $env:PGCLIENTENCODING = 'UTF8'

    $existingDatabase = & $sqlTool -X -w -qAt --set=ON_ERROR_STOP=1 --command="SELECT 1 FROM pg_database WHERE datname = '$BancoRestauracao'"
    if ($LASTEXITCODE -ne 0) { throw 'Falha ao consultar o servidor PostgreSQL.' }
    if ($existingDatabase -eq '1') { throw 'O banco de destino ja existe. Escolha outro nome; nada foi sobrescrito.' }

    $runId = Get-Date -Format 'yyyyMMdd_HHmmss_fff'
    $artifactPath = Join-Path $projectPath "storage\app\backups\$runId"
    New-Item -ItemType Directory -Path $artifactPath | Out-Null
    $backupFile = Join-Path $artifactPath 'hospital.sql'
    $summaryFile = Join-Path $artifactPath 'evidencias.txt'
    $originFile = Join-Path $artifactPath 'origem.csv'
    $restoredFile = Join-Path $artifactPath 'restaurado.csv'
    $validationFile = Join-Path $PSScriptRoot 'verificar-backup.sql'
    $startedAt = [DateTimeOffset]::Now.ToString('o')
    @("Inicio: $startedAt", "Origem: $($configuration.database)", "Destino novo: $BancoRestauracao") | Set-Content -LiteralPath $summaryFile -Encoding UTF8

    Write-Host '1/4 Gerando backup SQL completo...'
    & $dumpTool -w --format=plain --no-owner --no-privileges --file=$backupFile 2> (Join-Path $artifactPath 'backup.stderr.txt')
    if ($LASTEXITCODE -ne 0) { throw "Backup falhou. Consulte $artifactPath\backup.stderr.txt" }
    $backupInfo = Get-Item -LiteralPath $backupFile
    if ($backupInfo.Length -eq 0) { throw 'O backup esta vazio.' }
    $backupHash = (Get-FileHash -LiteralPath $backupFile -Algorithm SHA256).Hash
    @("Backup concluido: $($backupInfo.Name)", "Bytes: $($backupInfo.Length)", "SHA256: $backupHash") | Add-Content -LiteralPath $summaryFile -Encoding UTF8

    Write-Host '2/4 Registrando contagens e assinaturas da origem...'
    & $sqlTool -X -w -q --csv --set=ON_ERROR_STOP=1 --file=$validationFile --output=$originFile 2> (Join-Path $artifactPath 'origem.stderr.txt')
    if ($LASTEXITCODE -ne 0) { throw 'Falha ao verificar a origem.' }

    Write-Host "3/4 Criando $BancoRestauracao e restaurando..."
    & $sqlTool -X -w -q --set=ON_ERROR_STOP=1 --command="CREATE DATABASE $BancoRestauracao TEMPLATE template0" 1> (Join-Path $artifactPath 'criacao.stdout.txt') 2> (Join-Path $artifactPath 'criacao.stderr.txt')
    if ($LASTEXITCODE -ne 0) { throw 'Falha ao criar banco separado; verifique CREATEDB no seu usuario.' }
    & $sqlTool -X -w --dbname=$BancoRestauracao --set=ON_ERROR_STOP=1 --single-transaction --file=$backupFile 1> (Join-Path $artifactPath 'restauracao.stdout.txt') 2> (Join-Path $artifactPath 'restauracao.stderr.txt')
    if ($LASTEXITCODE -ne 0) { throw 'Restauracao falhou; a transacao foi revertida. Consulte restauracao.stderr.txt.' }
    'Restauracao concluida: psql retornou codigo 0.' | Add-Content -LiteralPath $summaryFile -Encoding UTF8

    Write-Host '4/4 Comparando dados, sequencias, rotinas, triggers, indices e restricoes...'
    & $sqlTool -X -w -q --dbname=$BancoRestauracao --csv --set=ON_ERROR_STOP=1 --file=$validationFile --output=$restoredFile 2> (Join-Path $artifactPath 'restaurado.stderr.txt')
    if ($LASTEXITCODE -ne 0) { throw 'Falha ao verificar o banco restaurado.' }
    $differences = Compare-Object (Get-Content -LiteralPath $originFile) (Get-Content -LiteralPath $restoredFile)
    if ($differences) {
        $differences | Out-File -LiteralPath (Join-Path $artifactPath 'diferencas.txt') -Encoding UTF8
        throw 'Comparacao divergiu. Verifique diferencas.txt e se houve gravacoes na origem durante a execucao.'
    }
    @('Comparacao: IDENTICA.', 'Resultado: BACKUP E RESTAURACAO VALIDADOS.', "Fim: $([DateTimeOffset]::Now.ToString('o'))") | Add-Content -LiteralPath $summaryFile -Encoding UTF8
    Get-Content -LiteralPath $summaryFile
    Write-Host "Evidencias salvas em: $artifactPath"
    Write-Host 'O banco restaurado foi mantido para consulta e capturas de tela.'
} catch {
    if ($summaryFile) { "FALHA: $($_.Exception.Message)" | Add-Content -LiteralPath $summaryFile -Encoding UTF8 }
    throw
} finally {
    foreach ($variableName in $environmentNames) {
        [Environment]::SetEnvironmentVariable($variableName, $previousEnvironment[$variableName], 'Process')
    }
    Pop-Location
}
