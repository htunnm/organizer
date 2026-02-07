# Disable Git SSL verification to prevent clone failures
git config --global http.sslVerify false

# Ensure vendor structure exists
New-Item -Path "vendor\squizlabs" -ItemType Directory -Force | Out-Null
New-Item -Path "vendor\wp-coding-standards" -ItemType Directory -Force | Out-Null
New-Item -Path "vendor\phpcsstandards" -ItemType Directory -Force | Out-Null

# Function to force reinstall a repo
function Reinstall-Repo ($path, $url) {
    Write-Host "Reinstalling $path..."
    if (Test-Path $path) {
        Remove-Item -Path $path -Recurse -Force -ErrorAction SilentlyContinue
    }
    git clone $url $path
}

# Reinstall all PHPCS related repos to ensure consistency
Reinstall-Repo "vendor\squizlabs\php_codesniffer" "https://github.com/PHPCSStandards/PHP_CodeSniffer.git"
Reinstall-Repo "vendor\wp-coding-standards\wpcs" "https://github.com/WordPress/WordPress-Coding-Standards.git"
Reinstall-Repo "vendor\phpcsstandards\phpcsutils" "https://github.com/PHPCSStandards/PHPCSUtils.git"
Reinstall-Repo "vendor\phpcsstandards\phpcsextra" "https://github.com/PHPCSStandards/PHPCSExtra.git"

# Verify and Run
if (Test-Path "vendor\squizlabs\php_codesniffer\bin\phpcs") {
    Write-Host "PHPCS found. Configuring paths..."
    
    # Use absolute paths to avoid configuration issues
    $wpcs = (Get-Item "vendor\wp-coding-standards\wpcs").FullName
    $utils = (Get-Item "vendor\phpcsstandards\phpcsutils").FullName
    $extra = (Get-Item "vendor\phpcsstandards\phpcsextra").FullName
    
    $paths = "$wpcs,$utils,$extra"
    
    php vendor\squizlabs\php_codesniffer\bin\phpcs --config-set installed_paths $paths
    
    Write-Host "Running Linter..."
    # Run without arguments to use phpcs.xml.dist (which excludes vendor/)
    php -d memory_limit=-1 vendor\squizlabs\php_codesniffer\bin\phpcs
} else {
    Write-Host "Failed to install PHPCS. Please check git output above."
}