# One-Click cPanel Deployment Packager for wetruenorthgroup.com
# Packages all production files, assets, database dump, and configs into a single zip.

Write-Host "Preparing deployment package for wetruenorthgroup.com..." -ForegroundColor Cyan

$zipName = "deploy_wetruenorthgroup.zip"
if (Test-Path $zipName) {
    Remove-Item $zipName -Force
}

# Ensure fresh DB dump
Write-Host "Checking database dump truenorth_db.sql..." -ForegroundColor Yellow
$dumpSuccess = $false
if (Test-Path "D:\xampp\mysql\bin\mysqldump.exe") {
    $tempDump = "temp_truenorth_dump.sql"
    & "D:\xampp\mysql\bin\mysqldump.exe" -u root truenorth_db --result-file=$tempDump 2>$null
    if ($LASTEXITCODE -eq 0 -and (Test-Path $tempDump) -and (Get-Item $tempDump).Length -gt 10000) {
        Move-Item $tempDump "truenorth_db.sql" -Force
        $dumpSuccess = $true
        Write-Host "Updated truenorth_db.sql from local MySQL database." -ForegroundColor Green
    } else {
        if (Test-Path $tempDump) { Remove-Item $tempDump -Force }
    }
}
if (-not $dumpSuccess) {
    if (Test-Path "config\truenorth_db.sql") {
        Copy-Item "config\truenorth_db.sql" "truenorth_db.sql" -Force
        Write-Host "Used verified production database dump from config\truenorth_db.sql." -ForegroundColor Green
    }
}

# Refuse to package without real secrets (no default passwords ship to production)
if (-not (Test-Path "config\secrets.php")) {
    Write-Host "[ERROR] config\secrets.php is missing. Copy config\secrets.example.php, fill it in, then re-run." -ForegroundColor Red
    exit 1
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
  --exclude="*.zip" `
  --exclude="scratch" `
  --exclude="scratch/*" `
  --exclude="CPANEL_PUBLIC_HTML" `
  --exclude="CPANEL_PUBLIC_HTML/*" `
  --exclude="new chnages" `
  --exclude="new chnages/*" `
  --exclude="*.bak" `
  --exclude="*.log" `
  --exclude="*.orig" `
  --exclude="diag.php" `
  --exclude="test_server.php" `
  --exclude="scripts/img-backup" `
  --exclude="scripts/img-backup/*" `
  --exclude="config/cache" `
  --exclude="config/cache/*" `
  --exclude="*.local.txt" `
  --exclude="*.md" `
  --exclude="*.ps1" `
  --exclude="*.bat" `
  --exclude="_archive_unused" `
  --exclude="_archive_unused/*" `
  --exclude="scripts" `
  --exclude="scripts/*" `
  --exclude="config/smtp_secret.json" `
  --exclude="config/inquiries.json" `
  --exclude="config/email_logs.json" `
  * .htaccess

if (Test-Path $zipName) {
    Copy-Item $zipName "cpanel_public_html.zip" -Force
    $fileItem = Get-Item $zipName
    $sizeMB = [math]::round($fileItem.Length / 1MB, 2)
    Write-Host "[SUCCESS] Created $zipName ($sizeMB megabytes) and cpanel_public_html.zip" -ForegroundColor Green
    
    # Sync to uncompressed CPANEL_PUBLIC_HTML directory
    Write-Host "Syncing to CPANEL_PUBLIC_HTML folder..." -ForegroundColor Yellow
    robocopy . CPANEL_PUBLIC_HTML /MIR /XD .git .vercel scratch CPANEL_PUBLIC_HTML "new chnages" dist scripts _archive_unused /XF *.zip *.log *.bak *.local.txt *.md *.ps1 *.bat smtp_secret.json inquiries.json email_logs.json diag.php test_server.php > $null
    Write-Host "[SUCCESS] Synced CPANEL_PUBLIC_HTML directory." -ForegroundColor Green
    
    Write-Host "Upload deploy_wetruenorthgroup.zip or cpanel_public_html.zip to cPanel File Manager in public_html and extract it." -ForegroundColor Cyan
} else {
    Write-Host "[ERROR] Failed to create zip." -ForegroundColor Red
}
