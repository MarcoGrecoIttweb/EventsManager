# Copia valida.php legacy nella cartella eventi di XAMPP (sviluppo locale).
$here = Split-Path -Parent $MyInvocation.MyCommand.Path
$eventiRoot = Resolve-Path (Join-Path $here "..\..\..\eventi") -ErrorAction SilentlyContinue
if (-not $eventiRoot) {
    $eventiRoot = "C:\xampp\htdocs\eventi"
}
if (-not (Test-Path $eventiRoot)) {
    Write-Error "Cartella eventi non trovata: $eventiRoot"
    exit 1
}
$includeDir = Join-Path $eventiRoot "include"
if (-not (Test-Path $includeDir)) {
    New-Item -ItemType Directory -Path $includeDir -Force | Out-Null
}
Copy-Item (Join-Path $here "include\log_user_login_event.php") $includeDir -Force
Copy-Item (Join-Path $here "it\valida.php") (Join-Path $eventiRoot "it\") -Force
Copy-Item (Join-Path $here "italia\valida.php") (Join-Path $eventiRoot "italia\") -Force
Write-Host "OK: log_user_login_event.php e valida.php copiati in $eventiRoot"
