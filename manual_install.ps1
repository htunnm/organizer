# Manually create vendor directories as requested
New-Item -Path "vendor\phpunit\phpunit" -ItemType Directory -Force | Out-Null
New-Item -Path "vendor\squizlabs" -ItemType Directory -Force | Out-Null
New-Item -Path "vendor\wp-coding-standards" -ItemType Directory -Force | Out-Null
New-Item -Path "vendor\composer" -ItemType Directory -Force | Out-Null

Write-Host "Vendor directories created."

# 2. Download PHPUnit
Write-Host "Downloading PHPUnit..."
Invoke-WebRequest -Uri "https://phar.phpunit.de/phpunit-9.phar" -OutFile "vendor\phpunit\phpunit\phpunit.phar"

# 3. Clone PHP_CodeSniffer
Write-Host "Cloning PHP_CodeSniffer..."
if (-not (Test-Path "vendor\squizlabs\php_codesniffer")) {
    git clone https://github.com/squizlabs/PHP_CodeSniffer.git vendor\squizlabs\php_codesniffer
}

# 4. Clone WPCS
Write-Host "Cloning WordPress Coding Standards..."
if (-not (Test-Path "vendor\wp-coding-standards\wpcs")) {
    git clone https://github.com/WordPress/WordPress-Coding-Standards.git vendor\wp-coding-standards\wpcs
}

# 5. Install PHPCSUtils (Dependency for WPCS)
Write-Host "Installing PHPCSUtils..."
if (-not (Test-Path "vendor\phpcsstandards\phpcsutils")) {
    New-Item -Path "vendor\phpcsstandards" -ItemType Directory -Force | Out-Null
    git clone https://github.com/PHPCSStandards/PHPCSUtils.git vendor\phpcsstandards\phpcsutils
}

# 6. Install PHPCSExtra (Dependency for WPCS)
Write-Host "Installing PHPCSExtra..."
if (-not (Test-Path "vendor\phpcsstandards\phpcsextra")) {
    git clone https://github.com/PHPCSStandards/PHPCSExtra.git vendor\phpcsstandards\phpcsextra
}

# 7. Configure PHPCS installed_paths
Write-Host "Configuring PHPCS installed_paths..."
php vendor\squizlabs\php_codesniffer\bin\phpcs --config-set installed_paths "vendor\wp-coding-standards\wpcs,vendor\phpcsstandards\phpcsutils,vendor\phpcsstandards\phpcsextra"

# Generate a simple PSR-4 autoloader for the plugin classes
$autoloadContent = @'
<?php
// Manual autoloader for Organizer plugin
spl_autoload_register(function ($class) {
    // Project-specific namespace prefix
    $prefix = 'Organizer\\';

    // Base directory for the namespace prefix
    $base_dir = __DIR__ . '/../includes/';

    // Does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    // Get the relative class name
    $relative_class = substr($class, $len);

    // Replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // If the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});

// Load PHP_CodeSniffer autoloader if available
if (file_exists(__DIR__ . '/squizlabs/php_codesniffer/autoload.php')) {
    require_once __DIR__ . '/squizlabs/php_codesniffer/autoload.php';
}
'@

Set-Content -Path "vendor\autoload.php" -Value $autoloadContent
Write-Host "Generated vendor/autoload.php for plugin functionality."