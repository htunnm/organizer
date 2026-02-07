# 1. Run PHPCBF to fix indentation and syntax issues automatically
Write-Host "Running PHPCBF to fix code style..."
# We use the PHP script directly to ensure it runs in the current environment
if (Test-Path "vendor\squizlabs\php_codesniffer\bin\phpcbf") {
    php -d memory_limit=-1 vendor\squizlabs\php_codesniffer\bin\phpcbf --standard=WordPress .
} else {
    Write-Host "PHPCBF not found. Skipping auto-fix."
}

# 2. Strip BOM from all PHP files to fix "Byte Order Mark" errors
Write-Host "Stripping BOM from files..."
$utf8NoBom = New-Object System.Text.UTF8Encoding $false
$files = Get-ChildItem -Recurse -Filter "*.php"

foreach ($file in $files) {
    # Skip vendor and .git folders
    if ($file.FullName -match "\\vendor\\" -or $file.FullName -match "\\.git\\") { continue }
    
    $content = [System.IO.File]::ReadAllText($file.FullName)
    [System.IO.File]::WriteAllText($file.FullName, $content, $utf8NoBom)
}

Write-Host "Done. Please run the linter again to verify."