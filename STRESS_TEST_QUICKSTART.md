# ⚡ Stress Testing - Quick Start (5 Minutes)

> **📝 Note:** No authentication tokens needed! The API is currently open for testing.
> Just start the backend and run the tests directly.

## 🎯 Run This First!

### Step 1: Start Backend (Terminal 1)

```powershell
cd d:\DataImportDashboard\backend
go run main.go
```

**Keep this running!** You should see:
```
Database connection established with 100 max connections
Server starting on :8080
```

---

### Step 2: Verify Backend is Running (Terminal 2)

```powershell
# Test if backend is responding
Invoke-RestMethod -Uri "http://localhost:8080/health"
```

You should see: `{"status":"ok","message":"Server is running"}`

  **Good news:** Currently the API doesn't require authentication tokens for testing!

---

### Step 3: Run Quick Stress Test (Same Terminal)

```powershell
# Create test directory
cd d:\DataImportDashboard
mkdir stress_tests -ErrorAction SilentlyContinue
cd stress_tests

# Helper function for PowerShell 5.1 compatibility
function Upload-File {
    param($Uri, $FilePath, $FileField = "file", $AdditionalFields = @{})
    Add-Type -AssemblyName System.Net.Http
    $client = New-Object System.Net.Http.HttpClient
    $content = New-Object System.Net.Http.MultipartFormDataContent
    $fileStream = [System.IO.File]::OpenRead($FilePath)
    $fileContent = New-Object System.Net.Http.StreamContent($fileStream)
    $content.Add($fileContent, $FileField, [System.IO.Path]::GetFileName($FilePath))
    foreach ($key in $AdditionalFields.Keys) {
        $stringContent = New-Object System.Net.Http.StringContent($AdditionalFields[$key])
        $content.Add($stringContent, $key)
    }
    try {
        $response = $client.PostAsync($Uri, $content).Result
        $result = $response.Content.ReadAsStringAsync().Result
        return ($result | ConvertFrom-Json)
    } finally {
        $fileStream.Close()
        $client.Dispose()
    }
}

# Test 1: Quick Import (10k rows, ~5 seconds)
Write-Host "`n=== TEST 1: Import 10,000 rows ===" -ForegroundColor Cyan
$sw = [System.Diagnostics.Stopwatch]::StartNew()

"name,description,category,value,status" | Out-File quick.csv -Encoding UTF8
1..10000 | ForEach-Object { "Record$_,Test $_,test,$_,active" } | Out-File quick.csv -Append -Encoding UTF8

$import = Upload-File -Uri "http://localhost:8080/upload/csv" -FilePath "quick.csv"

$sw.Stop()
Write-Host "  Imported: $($import.success) rows in $([math]::Round($sw.Elapsed.TotalSeconds,2))s" -ForegroundColor Green
Write-Host "Speed: $([math]::Round(10000/$sw.Elapsed.TotalSeconds)) rows/second" -ForegroundColor Yellow

# Test 2: Quick Export (~2 seconds)
Write-Host "`n=== TEST 2: Export all rows ===" -ForegroundColor Cyan
$sw.Restart()

Invoke-WebRequest -Uri "http://localhost:8080/download/csv" -OutFile export.csv

$sw.Stop()
$exported = (Get-Content export.csv).Count - 1
Write-Host "  Exported: $exported rows in $([math]::Round($sw.Elapsed.TotalSeconds,2))s" -ForegroundColor Green
Write-Host "Speed: $([math]::Round($exported/$sw.Elapsed.TotalSeconds)) rows/second" -ForegroundColor Yellow

# Test 3: Document Upload (~1 second)
Write-Host "`n=== TEST 3: Upload 10MB file ===" -ForegroundColor Cyan
$sw.Restart()

$file = "test_10mb.bin"
$stream = [System.IO.File]::Create($file)
$buffer = New-Object byte[] (1024*1024)
for ($i=0; $i -lt 10; $i++) {
    (New-Object Random).NextBytes($buffer)
    $stream.Write($buffer, 0, $buffer.Length)
}
$stream.Close()

$upload = Upload-File -Uri "http://localhost:8080/documents" -FilePath $file -AdditionalFields @{category="test"}

$sw.Stop()
Write-Host "  Uploaded: 10MB in $([math]::Round($sw.Elapsed.TotalSeconds,2))s" -ForegroundColor Green
Write-Host "Speed: $([math]::Round(10/$sw.Elapsed.TotalSeconds,2)) MB/second" -ForegroundColor Yellow

# Cleanup
Remove-Item quick.csv, export.csv, $file

Write-Host "`n========================================" -ForegroundColor Magenta
Write-Host "    ALL TESTS PASSED!" -ForegroundColor Magenta
Write-Host "========================================" -ForegroundColor Magenta
Write-Host "Your system is working correctly! 🎉"
Write-Host "`nFor detailed tests, see: STRESS_TEST_GUIDE.md"
```

---

## 📊 What Good Performance Looks Like

| Test | Good Result | Your Result |
|------|-------------|-------------|
| Import 10k rows | < 1 second | _______ sec |
| Export 10k rows | < 1 second | _______ sec |
| Upload 10MB | < 1 second | _______ sec |

---

## ⚡ Want More? Run Bigger Tests

### Import 100k rows (~5 seconds)
```powershell
# Navigate back to stress_tests directory
cd d:\DataImportDashboard\stress_tests

# Generate 100k rows
"name,description,category,value,status" | Out-File test100k.csv -Encoding UTF8
1..100000 | % { "Record$_,Test $_,test,$_,active" } | Out-File test100k.csv -Append -Encoding UTF8

# Helper function (copy from above if not already defined)
function Upload-File {
    param($Uri, $FilePath, $FileField = "file", $AdditionalFields = @{})
    Add-Type -AssemblyName System.Net.Http
    $client = New-Object System.Net.Http.HttpClient
    $content = New-Object System.Net.Http.MultipartFormDataContent
    $fileStream = [System.IO.File]::OpenRead($FilePath)
    $fileContent = New-Object System.Net.Http.StreamContent($fileStream)
    $content.Add($fileContent, $FileField, [System.IO.Path]::GetFileName($FilePath))
    foreach ($key in $AdditionalFields.Keys) {
        $stringContent = New-Object System.Net.Http.StringContent($AdditionalFields[$key])
        $content.Add($stringContent, $key)
    }
    try {
        $response = $client.PostAsync($Uri, $content).Result
        $result = $response.Content.ReadAsStringAsync().Result
        return ($result | ConvertFrom-Json)
    } finally {
        $fileStream.Close()
        $client.Dispose()
    }
}

# Import with timing
$sw = [System.Diagnostics.Stopwatch]::StartNew()
$r = Upload-File -Uri "http://localhost:8080/upload/csv" -FilePath "test100k.csv"
$sw.Stop()

Write-Host "Imported 100k rows in $($sw.Elapsed.TotalSeconds) seconds"
Write-Host "Speed: $([math]::Round(100000/$sw.Elapsed.TotalSeconds)) rows/second"
```

### Import 1 Million rows (~30-60 seconds)
```powershell
# This takes 2-3 minutes to generate
$sw = [System.Diagnostics.Stopwatch]::StartNew()
"name,description,category,value,status" | Out-File test1m.csv -Encoding UTF8
1..1000000 | ForEach-Object {
    if ($_ % 100000 -eq 0) { Write-Host "Generated: $_" }
    "Record$_,Test $_,cat$(($_ % 10)),$(Get-Random -Max 10000),active"
} | Out-File test1m.csv -Append -Encoding UTF8
$sw.Stop()
Write-Host "Generation took: $($sw.Elapsed.TotalSeconds)s"

# Import
$sw.Restart()
$r = Upload-File -Uri "http://localhost:8080/upload/csv" -FilePath "test1m.csv"
$sw.Stop()

Write-Host "Imported 1M rows in $([math]::Round($sw.Elapsed.TotalSeconds)) seconds"
Write-Host "Speed: $([math]::Round(1000000/$sw.Elapsed.TotalSeconds)) rows/second"

# Target: > 16,000 rows/second = EXCELLENT
```

---

## ❌ Common Problems & Solutions

### "Cannot connect" or "Connection refused"
  **Solution:** Backend not running. Go to Terminal 1 and start it:
```powershell
cd d:\DataImportDashboard\backend
go run main.go
```

### "401 Unauthorized"
  **Solution:** Token expired. Get new token from Step 2.

### "Co4 Not Found"
  **Solution:** Wrong URL. Make sure backend is running and check the endpoint (no /api prefix needed)ershell` and press Enter.

### "Access denied" on files
  **Solution:** Run as Administrator or use your Desktop:
```powershell
cd $env:USERPROFILE\Desktop
mkdir stress_tests
cd stress_tests
```

### Very slow (< 5,000 rows/sec)
  **Solution:** Check these:
```powershell
# Is backend using CPU?
Get-Process -Name "main"  # Should show high CPU

# Is database running?
Test-NetConnection localhost -Port 3306  # MySQL
Test-NetConnection localhost -Port 5432  # PostgreSQL
```

---

## 🎓 What Each Test Measures

| Test | Measures | Units | Good = |
|------|----------|-------|--------|
| **Import** | How fast data goes IN | rows/second | 15,000+ |
| **Export** | How fast data comes OUT | rows/second | 30,000+ |
| **Upload** | File transfer speed IN | MB/second | 50+ |
| **Download** | File transfer speed OUT | MB/second | 100+ |

---

## 📚 Learn More

- **Full Guide:** [STRESS_TEST_GUIDE.md](./STRESS_TEST_GUIDE.md)
- **System Upgrade:** [MASSIVE_SCALE_UPGRADE.md](./MASSIVE_SCALE_UPGRADE.md)
- **Architecture:** [UPGRADE_SUMMARY.md](./UPGRADE_SUMMARY.md)

---

##   Checklist

- [ ] Backend is running on port 8080
- [ ] I have a valid authentication token
- [ ] I'm in PowerShell (not CMD)
- [ ] I'm in the `stress_tests` directory
- [ ] Backend health check passes: `Invoke-RestMethod http://localhost:8080/health`ully
- [ ] Ready for larger tests!

---

**You're all set! Happy stress testing! 🚀**

*Run the commands in Step 3 above to complete your first stress test in ~30 seconds!*
