Write-Host "WARNING: This will wipe all data from the database!" -ForegroundColor Red
Write-Host "Ensure you are running this in a development environment."
$confirmation = Read-Host "Are you sure you want to proceed? (y/n)"
if ($confirmation -eq 'y') {
    Write-Host "Resetting database..."
    php artisan migrate:fresh --seed
    Write-Host "Database reset complete." -ForegroundColor Green
} else {
    Write-Host "Operation cancelled."
}
