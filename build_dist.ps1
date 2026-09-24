Write-Host "Building production distribution directory 'dist'..." -ForegroundColor Green

if (Test-Path "dist") {
    Remove-Item -Path "dist" -Recurse -Force
}

New-Item -ItemType Directory -Path "dist" | Out-Null

$itemsToCopy = @(
    "admin",
    "api",
    "app",
    "brand_assets",
    "config",
    "pet-bottles",
    "public",
    "ADMIN-GUIDE.md",
    "app.js",
    "audit_assets.py",
    "benefit_supply.webp",
    "branded_bottles.webp",
    "data.js",
    "factory.webp",
    "favicon.svg",
    "index.php",
    "logo.jpg",
    "logo.png",
    "logo.webp",
    "logo_svg.svg",
    "manufacturing.webp",
    "quote.php",
    "styles.css",
    "truenorth_db.sql",
    ".htaccess"
)

foreach ($item in $itemsToCopy) {
    if (Test-Path $item) {
        Copy-Item -Path $item -Destination "dist" -Recurse -Force
    }
}

Write-Host "Creating dist.zip distribution package..." -ForegroundColor Green

if (Test-Path "dist.zip") {
    Remove-Item -Path "dist.zip" -Force
}

Compress-Archive -Path "dist\*" -DestinationPath "dist.zip" -CompressionLevel Optimal -Force

$zipSize = (Get-Item "dist.zip").Length / 1MB
Write-Host ("dist.zip created successfully! Size: {0:N2} MB" -f $zipSize) -ForegroundColor Cyan
