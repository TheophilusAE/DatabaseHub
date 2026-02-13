# ðŸ”¥ Stress Testing Guide - Import/Export Performance

> **ðŸ“ Note:** No authentication tokens needed! The API is currently open for testing.

## ðŸ“– Navigation

**ðŸ†• FIRST TIME HERE?** â†’ See [STRESS_TEST_QUICKSTART.md](./STRESS_TEST_QUICKSTART.md) for a 5-minute guide!

**This document contains:**
- Complete prerequisites and setup instructions
- 8 comprehensive stress tests
- Detailed troubleshooting guide
- Performance monitoring scripts

---

## Overview

This guide helps you measure the **maximum throughput** of your upgraded Data Import Dashboard:
- **Rows per second** for CSV imports
- **MB/sec** for document uploads
- **Rows per second** for exports
- **Concurrent operations** capacity

---

## ðŸ“‹ Prerequisites & Setup

### âš¡ COMPLETE BEGINNER? START HERE!

**Fastest way to run a 30-second stress test:**

1. **Start Backend** (in one PowerShell window):
   ```powershell
   cd d:\DataImportDashboard\backend
   go run main.go
   ```
   Leave this running!

2. **Verify Backend** (in another PowerShell window):
   ```powershell
   # Test if backend is responding
   Invoke-RestMethod -Uri "http://localhost:8080/health"
   ```
   You should see: `{"status":"ok","message":"Server is running"}`

3. **Run Quick Test** (same PowerShell window):
   ```powershell
   # Create test directory
   cd d:\DataImportDashboard
   mkdir stress_tests -ErrorAction SilentlyContinue
   cd stress_tests
   
   # Generate 10k rows (fast!)
   "name,description,category,value,status" | Out-File quick.csv -Encoding UTF8
   1..10000 | % { "Record$_,Test,test,100,active" } | Out-File quick.csv -Append -Encoding UTF8
   
   # Import
   $r = Invoke-RestMethod -Uri "http://localhost:8080/upload/csv" -Method Post `
       -Form @{file=Get-Item "quick.csv"}
   
   Write-Host "SUCCESS! Imported $($r.success) rows" -ForegroundColor Green
   
   # Export
   Invoke-WebRequest -Uri "http://localhost:8080/download/csv" -OutFile export.csv
   
   Write-Host "âœ… Exported $((Get-Content export.csv).Count - 1) rows" -ForegroundColor Green
   Write-Host "`nðŸŽ‰ Your system works! Now try the detailed tests below." -ForegroundColor Cyan
   ```

**That's it! If that worked, your system is ready for bigger stress tests below.**

---

### âœ… What You Need

1. **Backend Running**
   - Go backend must be running on `http://localhost:8080`
   - Default port is 8080 (check your config if different)

2. **PowerShell**
   - Windows PowerShell 5.1+ (built into Windows)
   - Or PowerShell Core 7+ (cross-platform)
   - **No additional installation needed on Windows!**

3. **Disk Space**
   - At least 10GB free space for test files
   - More space needed for larger tests (100GB+ for 10M+ rows)

4. **Database Connection**
   - MySQL or PostgreSQL running
   - Backend connected to database

### ðŸš€ Step-by-Step Setup

#### Step 1: Start the Backend

```powershell
# Open PowerShell in project directory
cd d:\DataImportDashboard\backend

# Start the Go backend
go run main.go

# You should see:
# "Database connection established with 100 max connections"
# "Server starting on :8080"
```

**Keep this terminal open!** Backend must stay running during tests.

#### Step 2: Verify Backend is Running

**Test the health endpoint:**

```powershell
# In a NEW PowerShell window, test if backend is responding
Invoke-RestMethod -Uri "http://localhost:8080/health"

# You should see:
# @{status=ok; message=Server is running}

# Copy this token - you'll use it in all tests
```

#### Step 3: Create Test Directory

```powershell
# Create a directory for test files
cd d:\DataImportDashboard
mkdir stress_tests -ErrorAction SilentlyContinue
cd stress_tests

# This is where you'll run all stress tests
```

### âœ… You're Ready!

If you see "âœ… Token is valid!" you can now run any stress test below.

**Important Notes:**
- Run all commands in PowerShell (not CMD)
- Run from the `d:\DataImportDashboard\stress_tests` directory
- Keep backend running in a separate terminal

---

## ðŸŽ¯ Quick Stress Test Commands

### ðŸ”´ WHERE TO RUN: Open PowerShell in `d:\DataImportDashboard\stress_tests`

```powershell
# Navigate to test directory
cd d:\DataImportDashboard\stress_tests

# Set your token (replace with your actual token)
$token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."  # Your token here
```

### Test 1: Import Speed (Rows/Second)

**ðŸ“ WHERE:** PowerShell in `d:\DataImportDashboard\stress_tests`  
**â±ï¸ TIME:** ~3-5 minutes total  
**ðŸ’¾ DISK:** ~100MB file size  

#### Step-by-Step:

```powershell
# 1. Navigate to test directory (if not already there)
cd d:\DataImportDashboard\stress_tests

# 2. Generate 1 Million row CSV file (~2 minutes)
Write-Host "Generating 1 million row CSV file..." -ForegroundColor Yellow

Measure-Command {
    "name,description,category,value,status" | Out-File test_1m.csv -Encoding UTF8
    1..1000000 | ForEach-Object {
        if ($_ % 100000 -eq 0) { 
            Write-Host "  Generated: $_ rows" -ForegroundColor Cyan
        }
        "Record$_,Description $_,cat$(($_ % 10)),$(Get-Random -Max 10000),active"
    } | Out-File test_1m.csv -Append -Encoding UTF8
}

Write-Host "âœ… CSV file generated!" -ForegroundColor Green
Write-Host "File size: $([math]::Round((Get-Item test_1m.csv).Length / 1MB, 2)) MB"

# 3. Import and measure performance
Write-Host "`nStarting import test..." -ForegroundColor Yellow
$stopwatch = [System.Diagnostics.Stopwatch]::StartNew()

$response = Invoke-RestMethod `
    -Uri "http://localhost:8080/upload/csv" `
    -Method Post `
    -Form @{ file = Get-Item "test_1m.csv" }

$stopwatch.Stop()

# 4. Calculate and display results
$rowsPerSecond = 1000000 / $stopwatch.Elapsed.TotalSeconds

Write-Host "`n========================================" -ForegroundColor Green
Write-Host "Import Performance Results:" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host "Total Rows: 1,000,000"
Write-Host "Time Taken: $([math]::Round($stopwatch.Elapsed.TotalSeconds, 2)) seconds"
Write-Host "Rows/Second: $([math]::Round($rowsPerSecond, 2))" -ForegroundColor Yellow
Write-Host "Success: $($response.success)" -ForegroundColor Green
Write-Host "Failed: $($response.failed)" -ForegroundColor $(if($response.failed -eq 0){'Green'}else{'Red'})
Write-Host "========================================" -ForegroundColor Green

# 5. Performance Rating
if ($rowsPerSecond -gt 40000) {
    Write-Host "â­â­â­ OUTSTANDING PERFORMANCE!" -ForegroundColor Magenta
} elseif ($rowsPerSecond -gt 25000) {
    Write-Host "â­â­ EXCELLENT PERFORMANCE!" -ForegroundColor Green
} elseif ($rowsPerSecond -gt 15000) {
    Write-Host "â­ GOOD PERFORMANCE" -ForegroundColor Cyan
} else {
    Write-Host "âš ï¸ NEEDS TUNING - Check configuration" -ForegroundColor Yellow
}

# 6. Cleanup (optional - comment out to keep file)
# Remove-Item test_1m.csv
Write-Host "`nTest file saved as: test_1m.csv (delete manually if not needed)"
```

**Expected Results:**
- **Good**: 15,000-25,000 rows/second
- **Excellent**: 25,000-40,000 rows/second
- **Outstanding**: 40,000+ rows/second

---

### Test 2: Export Speed (Rows/Second)

**ðŸ“ WHERE:** PowerShell in `d:\DataImportDashboard\stress_tests`  
**â±ï¸ TIME:** ~30 seconds - 2 minutes (depends on data size)  
**ðŸ’¾ DISK:** Output file size depends on database records  
**âš ï¸ PREREQUISITE:** Must have data in database (run Test 1 first)  

#### Step-by-Step:

```powershell
# 1. Navigate to test directory
cd d:\DataImportDashboard\stress_tests

# 2. Start export and measure throughput
Write-Host "Starting export test..." -ForegroundColor Yellow
$stopwatch = [System.Diagnostics.Stopwatch]::StartNew()

Invoke-WebRequest `
    -Uri "http://localhost:8080/download/csv" `
    -Method Get `
    -OutFile "export_test.csv"

$stopwatch.Stop()

# 3. Analyze exported file
$lineCount = (Get-Content export_test.csv).Count - 1  # Subtract header
$fileSizeMB = (Get-Item export_test.csv).Length / 1MB
$rowsPerSecond = $lineCount / $stopwatch.Elapsed.TotalSeconds
$mbPerSecond = $fileSizeMB / $stopwatch.Elapsed.TotalSeconds

# 5. Display results
Write-Host "`n========================================" -ForegroundColor Green
Write-Host "Export Performance Results:" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host "Total Rows Exported: $lineCount"
Write-Host "File Size: $([math]::Round($fileSizeMB, 2)) MB"
Write-Host "Time Taken: $([math]::Round($stopwatch.Elapsed.TotalSeconds, 2)) seconds"
Write-Host "Rows/Second: $([math]::Round($rowsPerSecond, 2))" -ForegroundColor Yellow
Write-Host "MB/Second: $([math]::Round($mbPerSecond, 2))" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Green

# 6. Performance Rating
if ($rowsPerSecond -gt 80000) {
    Write-Host "â­â­â­ OUTSTANDING PERFORMANCE!" -ForegroundColor Magenta
} elseif ($rowsPerSecond -gt 50000) {
    Write-Host "â­â­ EXCELLENT PERFORMANCE!" -ForegroundColor Green
} elseif ($rowsPerSecond -gt 30000) {
    Write-Host "â­ GOOD PERFORMANCE" -ForegroundColor Cyan
} else {
    Write-Host "âš ï¸ NEEDS TUNING - Check database indexes" -ForegroundColor Yellow
}

# 7. Cleanup (optional)
# Remove-Item export_test.csv
Write-Host "`nExport file saved as: export_test.csv (delete manually if not needed)"
```

**Expected Results:**
- **Good**: 30,000-50,000 rows/second
- **Excellent**: 50,000-80,000 rows/second
- **Outstanding**: 80,000+ rows/second

---

### Test 3: Document Upload Speed (MB/Second)

**ðŸ“ WHERE:** PowerShell in `d:\DataImportDashboard\stress_tests`  
**â±ï¸ TIME:** Varies by file size (10MB=2s, 100MB=5s, 500MB=30s)  
**ðŸ’¾ DISK:** Creates temporary test files  
**ðŸ”§ NO INSTALLATION NEEDED:** Uses built-in PowerShell commands  

#### Step-by-Step Instructions:

```powershell
# 1. Navigate to test directory
cd d:\DataImportDashboard\stress_tests

# 2. Create the test function (copy entire function)
function Test-DocumentUpload {
    param(
        [int]$SizeMB
    )
    
    $filename = "test_${SizeMB}mb.bin"
    
    # Create test file
    Write-Host "`nCreating ${SizeMB}MB test file..." -ForegroundColor Yellow
    $stream = [System.IO.File]::Create($filename)
    $buffer = New-Object byte[] (1024 * 1024)  # 1MB buffer
    $random = New-Object Random
    
    for ($i = 0; $i -lt $SizeMB; $i++) {
        $random.NextBytes($buffer)
        $stream.Write($buffer, 0, $buffer.Length)
        if ($i % 100 -eq 0 -and $i -gt 0) { 
            Write-Host "  Created: $i MB / ${SizeMB} MB" -ForegroundColor Cyan
        }
    }
    $stream.Close()
    Write-Host "âœ… Test file created!" -ForegroundColor Green
    
    # Upload and measure
    Write-Host "Uploading ${SizeMB}MB file to server..." -ForegroundColor Yellow
    $stopwatch = [System.Diagnostics.Stopwatch]::StartNew()
    
    try {
        $response = Invoke-RestMethod `
            -Uri "http://localhost:8080/documents" `
            -Method Post `
            -Form @{
                file = Get-Item $filename
                category = "stress-test"
                description = "Stress test ${SizeMB}MB upload"
            }
        
        $stopwatch.Stop()
        $mbPerSecond = $SizeMB / $stopwatch.Elapsed.TotalSeconds
        
        # Display results
        Write-Host "`n========================================" -ForegroundColor Green
        Write-Host "Upload Performance (${SizeMB}MB):" -ForegroundColor Green
        Write-Host "========================================" -ForegroundColor Green
        Write-Host "File Size: ${SizeMB} MB"
        Write-Host "Time Taken: $([math]::Round($stopwatch.Elapsed.TotalSeconds, 2)) seconds"
        Write-Host "Upload Speed: $([math]::Round($mbPerSecond, 2)) MB/second" -ForegroundColor Yellow
        Write-Host "Document ID: $($response.document.id)"
        Write-Host "========================================" -ForegroundColor Green
        
        # Performance rating
        if ($mbPerSecond -gt 100) {
            Write-Host "â­â­â­ OUTSTANDING - SSD/NVMe!" -ForegroundColor Magenta
        } elseif ($mbPerSecond -gt 50) {
            Write-Host "â­â­ EXCELLENT - Fast disk!" -ForegroundColor Green
        } elseif ($mbPerSecond -gt 20) {
            Write-Host "â­ GOOD - Standard" -ForegroundColor Cyan
        } else {
            Write-Host "âš ï¸ SLOW - Check disk I/O" -ForegroundColor Yellow
        }
        
        $docId = $response.document.id
        
    } catch {
        $stopwatch.Stop()
        Write-Host "âŒ Upload failed: $($_.Exception.Message)" -ForegroundColor Red
        $docId = $null
    }
    
    # Cleanup local test file
    Remove-Item $filename -ErrorAction SilentlyContinue
    
    return $docId
}

# 4. Run upload tests
Write-Host "`n============================================" -ForegroundColor Cyan
Write-Host "  DOCUMENT UPLOAD STRESS TEST" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan

# Test 10MB (quick test)
$doc1 = Test-DocumentUpload -SizeMB 10 -Token $token
Start-Sleep -Seconds 1

# Test 100MB (medium test)
$doc2 = Test-DocumentUpload -SizeMB 100 -Token $token
Start-Sleep -Seconds 1

# Test 500MB (optional - uncomment if needed)
# $doc3 = Test-DocumentUpload -SizeMB 500 -Token $token

Write-Host "`nâœ… Upload tests completed!" -ForegroundColor Green
Write-Host "Uploaded document IDs: $doc1, $doc2" -ForegroundColor Cyan
```

**What This Does:**
- Creates binary test files with random data
- Uploads to backend via API
- Measures transfer speed in MB/second
- Cleans up local files (server copies remain)

**Performance Targets:**
- **> 100 MB/s**: Excellent (SSD/NVMe)
- **50-100 MB/s**: Good (Fast storage)
- **20-50 MB/s**: OK (Standard HDD)
- **< 20 MB/s**: Slow (investigate)
Test-DocumentUpload -SizeMB 100 
Test-DocumentUpload -SizeMB 500 
```

**Expected Results:**
- **Local disk**: 50-200 MB/second
- **Network storage**: 10-50 MB/second
- **Limited by**: Disk I/O, network, or CPU

---

### Test 4: Download Speed (MB/Second)

```powershell
function Test-DocumentDownload {
    param([int]$DocumentId)
    
    $outputFile = "download_test_$DocumentId.bin"
    
    Write-Host "Downloading document ID: $DocumentId..."
    $stopwatch = [System.Diagnostics.Stopwatch]::StartNew()
    
    Invoke-WebRequest `
        -Uri "http://localhost:8080/documents/$DocumentId" `
        -Method Get `
        # No auth needed `
        -OutFile $outputFile
    
    $stopwatch.Stop()
    $fileSizeMB = (Get-Item $outputFile).Length / 1MB
    $mbPerSecond = $fileSizeMB / $stopwatch.Elapsed.TotalSeconds
    
    Write-Host "================================"
    Write-Host "Download Performance:"
    Write-Host "================================"
    Write-Host "File Size: $([math]::Round($fileSizeMB, 2)) MB"
    Write-Host "Time Taken: $($stopwatch.Elapsed.TotalSeconds) seconds"
    Write-Host "Download Speed: $([math]::Round($mbPerSecond, 2)) MB/second"
    Write-Host "================================"
    
    # Cleanup
    Remove-Item $outputFile
}

# Test with document ID from upload
Test-DocumentDownload -DocumentId 1 
```

---

## ðŸ”¥ Advanced Stress Tests

### Test 5: Concurrent Imports (Parallel Load)

```powershell
# Test how many simultaneous imports the system can handle
function Start-ConcurrentImports {
    param(
        [int]$NumJobs = 5,
        [int]$RowsPerFile = 100000,
        [string]$Token
    )
    
    Write-Host "Starting $NumJobs concurrent import jobs..."
    Write-Host "Each job will import $RowsPerFile rows"
    
    $jobs = @()
    $stopwatch = [System.Diagnostics.Stopwatch]::StartNew()
    
    # Start multiple import jobs in parallel
    for ($i = 1; $i -le $NumJobs; $i++) {
        $job = Start-Job -ScriptBlock {
            param($JobNum, $Rows, $Token)
            
            # Generate CSV
            $filename = "concurrent_test_$JobNum.csv"
            "name,description,category,value,status" | Out-File $filename -Encoding UTF8
            1..$Rows | ForEach-Object {
                "Job${JobNum}_Record$_,Test,cat$JobNum,$_,active"
            } | Out-File $filename -Append -Encoding UTF8
            
            # Import
            $response = Invoke-RestMethod `
                -Uri "http://localhost:8080/upload/csv" `
                -Method Post `
                # No auth needed `
                -Form @{ file = Get-Item $filename }
            
            # Cleanup
            Remove-Item $filename
            
            return $response
        } -ArgumentList $i, $RowsPerFile, $Token
        
        $jobs += $job
    }
    
    # Wait for all jobs to complete
    $results = $jobs | Wait-Job | Receive-Job
    $stopwatch.Stop()
    
    # Calculate statistics
    $totalRows = $results | Measure-Object -Property success -Sum | Select-Object -ExpandProperty Sum
    $rowsPerSecond = $totalRows / $stopwatch.Elapsed.TotalSeconds
    
    Write-Host "================================"
    Write-Host "Concurrent Import Results:"
    Write-Host "================================"
    Write-Host "Concurrent Jobs: $NumJobs"
    Write-Host "Rows per Job: $RowsPerFile"
    Write-Host "Total Rows: $totalRows"
    Write-Host "Time Taken: $($stopwatch.Elapsed.TotalSeconds) seconds"
    Write-Host "Aggregate Throughput: $([math]::Round($rowsPerSecond, 2)) rows/second"
    Write-Host "Per-Job Average: $([math]::Round($rowsPerSecond / $NumJobs, 2)) rows/second"
    Write-Host "================================"
    
    # Cleanup
    $jobs | Remove-Job
}

# Test with 5 concurrent imports
Start-ConcurrentImports -NumJobs 5 -RowsPerFile 100000 
```

**Expected Results:**
- **5 concurrent jobs**: System should handle well
- **10 concurrent jobs**: May see some slowdown
- **20+ concurrent jobs**: Test limits

---

### Test 6: Maximum Row Capacity Test

```powershell
# Test progressively larger datasets
function Test-MaxCapacity {
    param([string]$Token)
    
    $sizes = @(100000, 500000, 1000000, 5000000, 10000000)
    $results = @()
    
    foreach ($size in $sizes) {
        Write-Host "`n=========================================="
        Write-Host "Testing with $size rows..."
        Write-Host "=========================================="
        
        # Generate file
        $filename = "capacity_test_$size.csv"
        Write-Host "Generating CSV file..."
        
        $genStopwatch = [System.Diagnostics.Stopwatch]::StartNew()
        "name,description,category,value,status" | Out-File $filename -Encoding UTF8
        1..$size | ForEach-Object {
            if ($_ % 100000 -eq 0) { 
                Write-Host "  Generated: $_ rows" 
            }
            "Record$_,Description $_,cat$(($_ % 100)),$(Get-Random -Max 10000),active"
        } | Out-File $filename -Append -Encoding UTF8
        $genStopwatch.Stop()
        
        Write-Host "Generation time: $($genStopwatch.Elapsed.TotalSeconds) seconds"
        
        # Get file size
        $fileSizeMB = (Get-Item $filename).Length / 1MB
        Write-Host "File size: $([math]::Round($fileSizeMB, 2)) MB"
        
        # Import
        Write-Host "Importing..."
        $importStopwatch = [System.Diagnostics.Stopwatch]::StartNew()
        
        try {
            $response = Invoke-RestMethod `
                -Uri "http://localhost:8080/upload/csv" `
                -Method Post `
                # No auth needed `
                -Form @{ file = Get-Item $filename } `
                -TimeoutSec 3600  # 1 hour timeout
            
            $importStopwatch.Stop()
            
            $result = @{
                Rows = $size
                FileSizeMB = [math]::Round($fileSizeMB, 2)
                ImportTimeSeconds = [math]::Round($importStopwatch.Elapsed.TotalSeconds, 2)
                RowsPerSecond = [math]::Round($size / $importStopwatch.Elapsed.TotalSeconds, 2)
                Success = $response.success
                Failed = $response.failed
                Status = "Success"
            }
            
        } catch {
            $importStopwatch.Stop()
            $result = @{
                Rows = $size
                FileSizeMB = [math]::Round($fileSizeMB, 2)
                ImportTimeSeconds = [math]::Round($importStopwatch.Elapsed.TotalSeconds, 2)
                RowsPerSecond = 0
                Success = 0
                Failed = $size
                Status = "Failed: $($_.Exception.Message)"
            }
        }
        
        $results += New-Object PSObject -Property $result
        
        # Display result
        Write-Host "`nResults for $size rows:"
        Write-Host "  Success: $($result.Success)"
        Write-Host "  Failed: $($result.Failed)"
        Write-Host "  Time: $($result.ImportTimeSeconds)s"
        Write-Host "  Speed: $($result.RowsPerSecond) rows/sec"
        Write-Host "  Status: $($result.Status)"
        
        # Cleanup
        Remove-Item $filename
        
        # Small delay between tests
        Start-Sleep -Seconds 5
    }
    
    # Summary table
    Write-Host "`n=========================================="
    Write-Host "CAPACITY TEST SUMMARY"
    Write-Host "=========================================="
    $results | Format-Table -Property Rows, FileSizeMB, ImportTimeSeconds, RowsPerSecond, Status -AutoSize
    
    return $results
}

# Run capacity test
Test-MaxCapacity 
```

---

### Test 7: Memory Usage Monitoring

```powershell
# Monitor backend memory during import
function Monitor-ImportMemory {
    param(
        [int]$Rows = 1000000,
        [string]$Token
    )
    
    # Start monitoring job
    $monitorJob = Start-Job -ScriptBlock {
        $samples = @()
        $processName = "main"  # Go process name
        
        while ($true) {
            try {
                $process = Get-Process -Name $processName -ErrorAction SilentlyContinue
                if ($process) {
                    $samples += @{
                        Time = (Get-Date).ToString("HH:mm:ss")
                        MemoryMB = [math]::Round($process.WorkingSet64 / 1MB, 2)
                        CPU = $process.CPU
                    }
                }
                Start-Sleep -Seconds 1
            } catch {
                break
            }
        }
        
        return $samples
    }
    
    Start-Sleep -Seconds 2
    
    # Generate and import
    Write-Host "Generating $Rows row CSV..."
    $filename = "memory_test.csv"
    "name,description,category,value,status" | Out-File $filename -Encoding UTF8
    1..$Rows | ForEach-Object {
        if ($_ % 100000 -eq 0) { Write-Host "  Generated: $_ rows" }
        "Record$_,Description $_,cat$(($_ % 10)),$(Get-Random -Max 10000),active"
    } | Out-File $filename -Append -Encoding UTF8
    
    Write-Host "Importing..."
    $response = Invoke-RestMethod `
        -Uri "http://localhost:8080/upload/csv" `
        -Method Post `
        # No auth needed `
        -Form @{ file = Get-Item $filename }
    
    # Stop monitoring
    Start-Sleep -Seconds 2
    Stop-Job $monitorJob
    $memoryData = Receive-Job $monitorJob
    Remove-Job $monitorJob
    
    # Display results
    Write-Host "`n=========================================="
    Write-Host "Memory Usage During Import"
    Write-Host "=========================================="
    
    if ($memoryData.Count -gt 0) {
        $maxMemory = ($memoryData | Measure-Object -Property MemoryMB -Maximum).Maximum
        $avgMemory = ($memoryData | Measure-Object -Property MemoryMB -Average).Average
        
        Write-Host "Peak Memory: $maxMemory MB"
        Write-Host "Average Memory: $([math]::Round($avgMemory, 2)) MB"
        Write-Host "Samples Collected: $($memoryData.Count)"
        
        Write-Host "`nMemory Timeline:"
        $memoryData | ForEach-Object {
            Write-Host "  $($_.Time): $($_.MemoryMB) MB"
        }
    }
    
    # Cleanup
    Remove-Item $filename
}

# Run memory monitoring test
Monitor-ImportMemory -Rows 1000000 
```

---

### Test 8: Database Connection Pool Test

```powershell
# Test database connection handling under load
function Test-ConnectionPool {
    param(
        [int]$ConcurrentRequests = 50,
        [string]$Token
    )
    
    Write-Host "Testing with $ConcurrentRequests concurrent requests..."
    
    # Generate small test file once
    $filename = "pool_test.csv"
    "name,description,category,value,status" | Out-File $filename -Encoding UTF8
    1..10000 | ForEach-Object {
        "PoolTest$_,Test,test,$_,active"
    } | Out-File $filename -Append -Encoding UTF8
    
    $stopwatch = [System.Diagnostics.Stopwatch]::StartNew()
    $jobs = @()
    
    # Launch concurrent requests
    for ($i = 1; $i -le $ConcurrentRequests; $i++) {
        $job = Start-Job -ScriptBlock {
            param($File, $Token, $JobNum)
            
            try {
                $response = Invoke-RestMethod `
                    -Uri "http://localhost:8080/upload/csv" `
                    -Method Post `
                    # No auth needed `
                    -Form @{ file = Get-Item $File }
                
                return @{
                    JobNum = $JobNum
                    Success = $true
                    Rows = $response.success
                }
            } catch {
                return @{
                    JobNum = $JobNum
                    Success = $false
                    Error = $_.Exception.Message
                }
            }
        } -ArgumentList $filename, $Token, $i
        
        $jobs += $job
        
        # Stagger starts slightly
        Start-Sleep -Milliseconds 10
    }
    
    # Wait for all
    Write-Host "Waiting for all requests to complete..."
    $results = $jobs | Wait-Job | Receive-Job
    $stopwatch.Stop()
    
    # Analyze
    $successful = ($results | Where-Object { $_.Success -eq $true }).Count
    $failed = ($results | Where-Object { $_.Success -eq $false }).Count
    
    Write-Host "================================"
    Write-Host "Connection Pool Test Results:"
    Write-Host "================================"
    Write-Host "Concurrent Requests: $ConcurrentRequests"
    Write-Host "Successful: $successful"
    Write-Host "Failed: $failed"
    Write-Host "Total Time: $($stopwatch.Elapsed.TotalSeconds) seconds"
    Write-Host "Requests/Second: $([math]::Round($ConcurrentRequests / $stopwatch.Elapsed.TotalSeconds, 2))"
    Write-Host "================================"
    
    if ($failed -gt 0) {
        Write-Host "`nFailed Requests:"
        $results | Where-Object { $_.Success -eq $false } | ForEach-Object {
            Write-Host "  Job $($_.JobNum): $($_.Error)"
        }
    }
    
    # Cleanup
    $jobs | Remove-Job
    Remove-Item $filename
}

# Test with 50 concurrent imports
Test-ConnectionPool -ConcurrentRequests 50 
```

---

## ðŸ“Š Comprehensive Stress Test Suite

### Run All Tests

```powershell
# Complete stress test suite
function Run-ComprehensiveStressTest {
    param([string]$Token)
    
    $report = @{
        TestDate = Get-Date
        Tests = @()
    }
    
    Write-Host "=========================================="
    Write-Host "COMPREHENSIVE STRESS TEST SUITE"
    Write-Host "=========================================="
    Write-Host "Started: $(Get-Date)"
    Write-Host ""
    
    # Test 1: Import Speed
    Write-Host "`n[1/6] Testing Import Speed (1M rows)..."
    # Run Test 1 code here
    
    # Test 2: Export Speed
    Write-Host "`n[2/6] Testing Export Speed..."
    # Run Test 2 code here
    
    # Test 3: Upload Speed
    Write-Host "`n[3/6] Testing Document Upload..."
    # Run Test 3 code here
    
    # Test 4: Download Speed
    Write-Host "`n[4/6] Testing Document Download..."
    # Run Test 4 code here
    
    # Test 5: Concurrent Imports
    Write-Host "`n[5/6] Testing Concurrent Operations..."
    Start-ConcurrentImports -NumJobs 5 -RowsPerFile 100000 -Token $Token
    
    # Test 6: Connection Pool
    Write-Host "`n[6/6] Testing Connection Pool..."
    Test-ConnectionPool -ConcurrentRequests 50 -Token $Token
    
    Write-Host "`n=========================================="
    Write-Host "ALL TESTS COMPLETED"
    Write-Host "=========================================="
    Write-Host "Finished: $(Get-Date)"
}

# Run everything
Run-ComprehensiveStressTest 
```

---

## ðŸŽ¯ Performance Targets

### Import Performance Targets

| Rows        | Target Time     | Target Speed         |
|-------------|-----------------|----------------------|
| 100,000     | < 5 seconds     | > 20,000 rows/sec    |
| 1,000,000   | < 60 seconds    | > 16,000 rows/sec    |
| 10,000,000  | < 10 minutes    | > 16,000 rows/sec    |
| 100,000,000 | < 2 hours       | > 13,000 rows/sec    |

### Export Performance Targets

| Rows        | Target Time     | Target Speed         |
|-------------|-----------------|----------------------|
| 100,000     | < 3 seconds     | > 30,000 rows/sec    |
| 1,000,000   | < 30 seconds    | > 33,000 rows/sec    |
| 10,000,000  | < 5 minutes     | > 33,000 rows/sec    |

### Document Transfer Targets

| Operation | Target Speed    |
|-----------|-----------------|
| Upload    | > 50 MB/sec     |
| Download  | > 100 MB/sec    |

---

## ðŸ” What to Monitor

### During Tests, Watch:

1. **CPU Usage**
   ```powershell
   # Monitor in separate window
   while ($true) {
       Get-Process -Name "main" | Select Name, CPU, @{N="MemMB";E={[math]::Round($_.WS/1MB,2)}}
       Start-Sleep -Seconds 1
   }
   ```

2. **Database Connections**
   ```sql
   -- Run in MySQL
   SHOW PROCESSLIST;
   
   -- Should see multiple INSERT/SELECT operations
   -- All 32 workers should be active during import
   ```

3. **Disk I/O**
   ```powershell
   Get-Counter "\PhysicalDisk(_Total)\Disk Bytes/sec"
   ```

4. **Network (if remote DB)**
   ```powershell
   Get-Counter "\Network Interface(*)\Bytes Total/sec"
   ```

---

## ðŸ“ˆ Interpreting Results

### Good Performance Indicators:
âœ… CPU usage 80-100% during imports  
âœ… All 32 workers processing simultaneously  
âœ… No database connection errors  
âœ… Linear scaling with data size  
âœ… Constant memory usage  
âœ… No timeout errors  

### Warning Signs:
âš ï¸ CPU usage < 50% (bottleneck elsewhere)  
âš ï¸ Memory increasing continuously (leak)  
âš ï¸ Database connection errors  
âš ï¸ Degrading performance over time  
âš ï¸ Disk I/O at 100% (storage bottleneck)  

---

## ðŸŽ¯ Quick Performance Check

**Run this 5-minute test:**

```powershell
# Quick performance validation
# No token needed

# 1. Generate 100k rows
"name,description,category,value,status" | Out-File quick_test.csv -Encoding UTF8
1..100000 | ForEach-Object { "R$_,D$_,test,$_,active" } | Out-File quick_test.csv -Append -Encoding UTF8

# 2. Import
$sw = [System.Diagnostics.Stopwatch]::StartNew()
$r = Invoke-RestMethod -Uri "http://localhost:8080/upload/csv" -Method Post `
    # No auth needed -Form @{file=Get-Item "quick_test.csv"}
$sw.Stop()

Write-Host "Import: 100k rows in $($sw.Elapsed.TotalSeconds)s = $([math]::Round(100000/$sw.Elapsed.TotalSeconds)) rows/sec"

# 3. Export
$sw.Restart()
Invoke-WebRequest -Uri "http://localhost:8080/download/csv" # No auth needed -OutFile export.csv
$sw.Stop()

$rows = (Get-Content export.csv).Count - 1
Write-Host "Export: $rows rows in $($sw.Elapsed.TotalSeconds)s = $([math]::Round($rows/$sw.Elapsed.TotalSeconds)) rows/sec"

# Cleanup
Remove-Item quick_test.csv, export.csv

Write-Host "`nSystem is performing $(if (100000/$sw.Elapsed.TotalSeconds -gt 20000) {'EXCELLENT'} elseif (100000/$sw.Elapsed.TotalSeconds -gt 10000) {'GOOD'} else {'NEEDS TUNING'})"
```

---

## â“ Troubleshooting Common Issues

### Issue 1: "Backend not running" or Connection Refused

**Error Message:**
```
Invoke-RestMethod : Unable to connect to the remote server
```

**Solution:**
```powershell
# Check if backend is running
Get-Process -Name "main" -ErrorAction SilentlyContinue

# If not running, start it:
cd d:\DataImportDashboard\backend
go run main.go

# Verify it's listening
Test-NetConnection -ComputerName localhost -Port 8080
```

---

### Issue 2: PowerShell says "command not found"

**Error Message:**
```
The term 'Invoke-RestMethod' is not recognized
```

**Solution:**
You're probably in CMD instead of PowerShell.

```cmd
# Check your prompt:
# CMD prompt looks like: C:\Users\YourName>
# PowerShell looks like: PS C:\Users\YourName>

# To open PowerShell:
# 1. Press Windows key
# 2. Type "PowerShell"
# 3. Click "Windows PowerShell"

# Or from CMD:
powershell
```

---

### Issue 4: File generation is slow

**Issue:**
Generating 1M row CSV takes 10+ minutes

**Solution - Faster CSV Generation:**
```powershell
# Much faster method using StreamWriter
$filename = "test_1m.csv"
$sw = [System.IO.StreamWriter]::new($filename)
$sw.WriteLine("name,description,category,value,status")

for ($i = 1; $i -le 1000000; $i++) {
    $sw.WriteLine("Record$i,Description $i,cat$(($i % 10)),$(Get-Random -Max 10000),active")
    if ($i % 100000 -eq 0) { Write-Host "Generated: $i" }
}

$sw.Close()
Write-Host "Done! Much faster!"
```

---

### Issue 5: Import fails with "out of memory"

**Error Message:**
```
System.OutOfMemoryException
```

**Solution:**
```powershell
# 1. Check available memory
Get-CimInstance Win32_OperatingSystem | Select FreePhysicalMemory

# 2. Reduce test size
# Instead of 1M rows, start with 100k:
1..100000 | ForEach-Object { ... }

# 3. Check backend configuration
# Reduce workers if needed (backend/config/config.go):
# ImportWorkers: 16  (instead of 32)
# ImportBatchSize: 25000  (instead of 50000)
```

---

### Issue 6: Database connection errors

**Error Message:**
```
Error 1040: Too many connections
Error 2006: MySQL server has gone away
```

**Solution:**
```sql
-- Connect to MySQL
mysql -u root -p

-- Increase max connections
SET GLOBAL max_connections = 200;

-- Check current connections
SHOW PROCESSLIST;

-- Increase wait timeout
SET GLOBAL wait_timeout = 600;
SET GLOBAL interactive_timeout = 600;

-- Verify settings
SHOW VARIABLES LIKE 'max_connections';
```

---

### Issue 7: Very slow performance (< 5,000 rows/sec)

**Checklist:**

```powershell
# 1. Check CPU usage during import
Get-Process -Name "main" | Select-Object Name,CPU,@{N="MemMB";E={[math]::Round($_.WS/1MB,2)}}

# Should show high CPU usage (80-100%)
# If CPU is low (< 50%), there's a bottleneck elsewhere

# 2. Check disk I/O
Get-Counter "\PhysicalDisk(_Total)\% Disk Time"

# Should be < 80%
# If at 100%, disk is the bottleneck

# 3. Check database response time
# In MySQL:
SHOW FULL PROCESSLIST;

# Look for queries stuck in "Locked" state

# 4. Add database indexes
# In MySQL:
CREATE INDEX idx_data_records_created_at ON data_records(created_at);
CREATE INDEX idx_data_records_category ON data_records(category);
```

---

### Issue 8: "Access denied" saving files

**Error Message:**
```
Access to the path '...' is denied
```

**Solution:**
```powershell
# Run PowerShell as Administrator, OR:

# Change to your user directory
cd $env:USERPROFILE\Desktop
mkdir stress_tests
cd stress_tests

# Now run tests here instead
```

---

### Issue 9: Tests run but no output/results

**Issue:**
Commands execute but you don't see results

**Solution:**
```powershell
# Make sure you're assigning variables correctly
$response = Invoke-RestMethod ...  # â† Note the $response =

# Then display them
Write-Host "Result: $($response.success)"

# Or use -Verbose flag
Invoke-RestMethod ... -Verbose
```

---

### Issue 10: Port 8080 already in use

**Error Message:**
```
bind: address already in use
```

**Solution:**
```powershell
# Find what's using port 8080
Get-NetTCPConnection -LocalPort 8080 -ErrorAction SilentlyContinue

# Kill the process
Stop-Process -Id [PID_FROM_ABOVE]

# Or change backend port in config
# Edit backend/config/config.go:
# Port: "8081"  (instead of 8080)

# Then use http://localhost:8081 in tests
```

---

### Issue 11: Need to test with more than 10M rows

**Question:**
How do I generate 100M+ rows for testing?

**Solution:**
```powershell
# Method 1: Use faster StreamWriter (from Issue 4)
# Method 2: Generate in chunks and append
function Generate-LargeCSV {
    param([int]$TotalRows, [int]$ChunkSize = 1000000)
    
    $filename = "large_test.csv"
    
    # Write header
    "name,description,category,value,status" | Out-File $filename -Encoding UTF8
    
    for ($chunk = 0; $chunk -lt [math]::Ceiling($TotalRows / $ChunkSize); $chunk++) {
        $start = $chunk * $ChunkSize + 1
        $end = [math]::Min(($chunk + 1) * $ChunkSize, $TotalRows)
        
        Write-Host "Generating rows $start to $end..."
        
        $start..$end | ForEach-Object {
            "Record$_,Description $_,cat$(($_ % 100)),$(Get-Random -Max 100000),active"
        } | Out-File $filename -Append -Encoding UTF8
    }
    
    Write-Host "âœ… Generated $TotalRows rows"
}

# Generate 10 million rows in 10 chunks
Generate-LargeCSV -TotalRows 10000000 -ChunkSize 1000000
```

---

### Issue 12: Need to stop a long-running test

**Press `Ctrl+C` in PowerShell**

If that doesn't work:
```powershell
# In another PowerShell window, find and kill the process
Get-Process -Name "powershell" | Where-Object {$_.CPU -gt 10}
# Note the ID of the high-CPU PowerShell process

Stop-Process -Id [ID_FROM_ABOVE]
```

---

## ðŸ†˜ Quick Reference Commands

### Check if everything is ready:
```powershell
# Backend running?
Test-NetConnection localhost -Port 8080

# Token valid?
Invoke-RestMethod -Uri "http://localhost:8080/api/data" `
    # No auth needed

# Database accessible?
mysql -u root -p -e "SELECT COUNT(*) FROM data_import_db.data_records;"
```

### Reset database for fresh test:
```powershell
# WARNING: This deletes all data!
mysql -u root -p -e "DELETE FROM data_import_db.data_records;"
mysql -u root -p -e "DELETE FROM data_import_db.import_logs;"
```

### Check system resources:
```powershell
# Memory
Get-CimInstance Win32_OperatingSystem | Select @{N="FreeMB";E={[math]::Round($_.FreePhysicalMemory/1024)}}

# Disk space
Get-PSDrive C | Select @{N="FreeGB";E={[math]::Round($_.Free/1GB,2)}}

# CPU usage
Get-Counter "\Processor(_Total)\% Processor Time"
```

---

## ðŸš€ Next Steps

After stress testing:

1. **Review Results** - Compare against targets
2. **Identify Bottlenecks** - CPU, disk, network, or database
3. **Tune Configuration** - Adjust workers, batch sizes
4. **Optimize Database** - Add indexes, tune buffer pool
5. **Scale Hardware** - If hitting limits consistently

---

## ðŸ“ž Support

If performance is below targets:
- Check [MASSIVE_SCALE_UPGRADE.md](./MASSIVE_SCALE_UPGRADE.md) troubleshooting section
- Verify database indexes exist
- Ensure SSD storage is used
- Check system resources aren't constrained
- Review configuration settings

---

**Ready to stress test your system!** ðŸ”¥

