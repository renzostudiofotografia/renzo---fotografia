param([int]$Port = 8010, [switch]$Setup)

$ErrorActionPreference = 'Stop'
Set-Location -LiteralPath $PSScriptRoot
$hasSqlite = & php -r 'echo extension_loaded("pdo_sqlite") ? "yes" : "no";'
if ($hasSqlite -ne 'yes') {
    $runtimeIniDirectory = Join-Path $PSScriptRoot 'storage/app/php-ini'
    New-Item -ItemType Directory -Path $runtimeIniDirectory -Force | Out-Null
    Set-Content -LiteralPath (Join-Path $runtimeIniDirectory 'sqlite.ini') -Value 'extension=php_pdo_sqlite.dll' -Encoding ascii
    $env:PHP_INI_SCAN_DIR = $runtimeIniDirectory
}

if ($Setup) {
    & php artisan site:setup
    exit $LASTEXITCODE
}

$router = Join-Path $PSScriptRoot 'vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php'
Set-Location -LiteralPath (Join-Path $PSScriptRoot 'public')
& php -S "127.0.0.1:$Port" $router
exit $LASTEXITCODE
