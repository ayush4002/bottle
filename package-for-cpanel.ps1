# One-Click cPanel Deployment Packager for wetruenorthgroup.com
# Packages all production files, assets, database dump, and configs into a single zip.

Write-Host "Preparing deployment package for wetruenorthgroup.com..." -ForegroundColor Cyan

$zipName = "deploy_wetruenorthgroup.zip"
if (Test-Path $zipName) {
    Remove-Item $zipName -Force
}

# Ensure fresh DB dump
Write-Host "Updating database dump truenorth_db.sql..." -ForegroundColor Yellow
if (Test-Path "D:\xampp\mysql\bin\mysqldump.exe") {
    & "D:\xampp\mysql\bin\mysqldump.exe" -u root truenorth_db --result-file="truenorth_db.sql"
}

# Package files using tar
Write-Host "Compressing production files..." -ForegroundColor Yellow
& tar.exe -a -c -f $zipName `
  --exclude=".git" `
  --exclude=".git/*" `
  --exclude="dist" `
  --exclude="dist/*" `
  --exclude="dist.zip" `
  --exclude=".vercel" `
  --exclude=".vercel/*" `
  --exclude=".env.local" `
  --exclude=".env" `
  --exclude="deploy_wetruenorthgroup.zip" `
  --exclude="*.log" `
  * .htaccess .env.example

if (Test-Path $zipName) {
    $fileItem = Get-Item $zipName
    $sizeMB = [math]::round($fileItem.Length / 1MB, 2)
    Write-Host "[SUCCESS] Created $zipName ($sizeMB megabytes)" -ForegroundColor Green
    Write-Host "Upload this file to cPanel File Manager in public_html and extract it." -ForegroundColor Cyan
} else {
    Write-Host "[ERROR] Failed to create zip." -ForegroundColor Red
}
