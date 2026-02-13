# Quick Start: Testing Massive Scale Features

## 🚀 Quick Testing Guide

This guide helps you quickly test the upgraded massive scale features.

---

## Step 1: Start the Backend

```bash
cd backend
go run main.go
```

**Verify the new configuration is loaded:**
- Check logs for: "Database connection established with 100 max connections"
- Server should start on port 8080

---

## Step 2: Generate Test Data

### Option A: Generate Small Test (100k rows)

```bash
# Linux/Mac
cat > generate_100k.sh <<'EOF'
#!/bin/bash
echo "name,description,category,value,status" > test_100k.csv
for i in {1..100000}; do
    echo "Record$i,Test description $i,category$((i % 10)),$((RANDOM % 10000)),active" >> test_100k.csv
done
echo "Generated test_100k.csv with 100,000 rows"
EOF
chmod +x generate_100k.sh
./generate_100k.sh
```

```powershell
# Windows PowerShell
"name,description,category,value,status" | Out-File test_100k.csv -Encoding UTF8
1..100000 | ForEach-Object {
    "Record$_,Test description $_,category$(($_ % 10)),$(Get-Random -Maximum 10000),active"
} | Out-File test_100k.csv -Append -Encoding UTF8
Write-Host "Generated test_100k.csv with 100,000 rows"
```

### Option B: Generate Medium Test (1 Million rows)

```bash
# Linux/Mac - Takes ~2 minutes
cat > generate_1m.sh <<'EOF'
#!/bin/bash
echo "Generating 1M record CSV..."
echo "name,description,category,value,status" > test_1m.csv
for i in {1..1000000}; do
    echo "Record$i,Description $i,cat$((i % 100)),$((RANDOM % 100000)),active" >> test_1m.csv
    if [ $((i % 100000)) -eq 0 ]; then
        echo "Progress: $i records..."
    fi
done
echo "Generated test_1m.csv with 1,000,000 rows"
EOF
chmod +x generate_1m.sh
./generate_1m.sh
```

```powershell
# Windows PowerShell - Takes ~3 minutes
Write-Host "Generating 1M record CSV..."
"name,description,category,value,status" | Out-File test_1m.csv -Encoding UTF8
1..1000000 | ForEach-Object {
    if ($_ % 100000 -eq 0) { Write-Host "Progress: $_ records..." }
    "Record$_,Description $_,cat$(($_ % 100)),$(Get-Random -Maximum 100000),active"
} | Out-File test_1m.csv -Append -Encoding UTF8
Write-Host "Generated test_1m.csv with 1,000,000 rows"
```

### Option C: Generate Large JSON Test

```bash
# Generate 100k JSON records
cat > generate_json.sh <<'EOF'
#!/bin/bash
echo "[" > test_100k.json
for i in {1..100000}; do
    if [ $i -gt 1 ]; then echo "," >> test_100k.json; fi
    echo "{\"name\":\"Record$i\",\"description\":\"Test $i\",\"category\":\"cat$((i%10))\",\"value\":$((RANDOM%10000)),\"status\":\"active\"}" >> test_100k.json
done
echo "]" >> test_100k.json
echo "Generated test_100k.json"
EOF
chmod +x generate_json.sh
./generate_json.sh
```

---

## Step 3: Test CSV Import

### Using cURL

```bash
# Import 100k records (should take ~5-10 seconds)
curl -X POST http://localhost:8080/api/import/csv \
  -F "file=@test_100k.csv" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -v

# Import 1M records (should take ~30-60 seconds)
time curl -X POST http://localhost:8080/api/import/csv \
  -F "file=@test_1m.csv" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Using PowerShell

```powershell
# Import CSV
$headers = @{
    "Authorization" = "Bearer YOUR_TOKEN_HERE"
}

$form = @{
    file = Get-Item -Path "test_100k.csv"
}

Measure-Command {
    Invoke-RestMethod -Uri "http://localhost:8080/api/import/csv" `
        -Method Post `
        -Headers $headers `
        -Form $form
}
```

### Expected Response

```json
{
  "message": "Import completed successfully",
  "total": 100000,
  "success": 100000,
  "failed": 0,
  "import_log_id": 1
}
```

---

## Step 4: Test Export

### Export to CSV

```bash
# Export all records
time curl -X GET "http://localhost:8080/api/export/csv" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -o export_all.csv

# Check file size
ls -lh export_all.csv
wc -l export_all.csv

# Export by category
curl -X GET "http://localhost:8080/api/export/csv?category=category5" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -o export_category5.csv
```

### Export to JSON

```bash
# Export all records to JSON
time curl -X GET "http://localhost:8080/api/export/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -o export_all.json

# Check file size
ls -lh export_all.json
```

---

## Step 5: Test Large Document Upload

### Create Test Files

```bash
# Create 100MB test file
dd if=/dev/urandom of=test_100mb.bin bs=1M count=100

# Create 1GB test file
dd if=/dev/urandom of=test_1gb.bin bs=1M count=1024

# Or create a large text file
base64 /dev/urandom | head -c 100M > test_100mb.txt
```

```powershell
# Windows: Create 100MB test file
$stream = [System.IO.File]::Create("test_100mb.bin")
$random = New-Object byte[] (1024*1024*100)
(New-Object Random).NextBytes($random)
$stream.Write($random, 0, $random.Length)
$stream.Close()
Write-Host "Created test_100mb.bin"
```

### Upload Large Document

```bash
# Upload with timing
time curl -X POST http://localhost:8080/api/documents/upload \
  -F "file=@test_100mb.bin" \
  -F "category=test" \
  -F "description=Large test file" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -v

# Expected output includes size in GB
# "size_gb": 0.09313225746154785
```

### Download Document

```bash
# Download the uploaded document (use the ID from upload response)
time curl -X GET "http://localhost:8080/api/documents/download/1" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -o downloaded_file.bin

# Verify file size matches
ls -lh downloaded_file.bin
```

---

## Step 6: Performance Monitoring

### Check Database Records

```sql
-- Connect to your database
mysql -u root -p data_import_db

-- Check record count
SELECT COUNT(*) as total_records FROM data_records;

-- Check by category
SELECT category, COUNT(*) as count 
FROM data_records 
GROUP BY category 
ORDER BY count DESC;

-- Check import logs
SELECT * FROM import_logs ORDER BY created_at DESC LIMIT 10;

-- Check documents
SELECT id, original_name, file_size, created_at 
FROM documents 
ORDER BY created_at DESC;
```

### Monitor System Resources

```bash
# Monitor CPU and memory during import
top -p $(pgrep -f "go run main.go")

# Monitor disk I/O
iostat -x 1

# Monitor network
iftop

# Check Go process stats
ps aux | grep "go"
```

---

## Performance Benchmarks You Should See

### Import Performance

| Records   | Expected Time | Workers | Success Rate |
|-----------|---------------|---------|--------------|
| 1,000     | < 1 second    | 1-4     | 100%         |
| 10,000    | 1-2 seconds   | 4-8     | 100%         |
| 100,000   | 5-10 seconds  | 16-32   | 100%         |
| 1,000,000 | 30-60 seconds | 32      | 99.9%+       |

### Export Performance

| Records   | Expected Time | File Size |
|-----------|---------------|-----------|
| 10,000    | 1-2 seconds   | ~1 MB     |
| 100,000   | 3-5 seconds   | ~10 MB    |
| 1,000,000 | 15-30 seconds | ~100 MB   |

### Document Upload/Download

| File Size | Upload Time  | Download Time |
|-----------|--------------|---------------|
| 10 MB     | < 1 second   | < 1 second    |
| 100 MB    | 2-5 seconds  | 1-3 seconds   |
| 1 GB      | 20-40 seconds| 10-20 seconds |

---

## Troubleshooting

### Issue: Import Taking Too Long

**Check:**
```bash
# 1. Database connection count
SHOW PROCESSLIST;  # MySQL
SELECT * FROM pg_stat_activity;  # PostgreSQL

# 2. System resources
top
free -h
df -h
```

**Solutions:**
- Reduce worker count in config (32 → 16)
- Reduce batch size (50000 → 25000)
- Add database indexes
- Use SSD storage

### Issue: Out of Memory

**Check:**
```bash
# Memory usage
free -m
cat /proc/meminfo

# Go memory stats
curl http://localhost:8080/debug/pprof/heap
```

**Solutions:**
- Increase system RAM
- Reduce ImportWorkers (32 → 16)
- Reduce StreamBufferSize
- Enable swap (not recommended for production)

### Issue: Database Errors

**Common errors:**
```
Error 1040: Too many connections
Error 2006: MySQL server has gone away
Error 1206: Total number of locks exceeds the lock table size
```

**Solutions:**
```sql
-- Increase max connections
SET GLOBAL max_connections = 200;

-- Increase buffer pool (MySQL)
SET GLOBAL innodb_buffer_pool_size = 16*1024*1024*1024;

-- Check current settings
SHOW VARIABLES LIKE 'max_connections';
SHOW VARIABLES LIKE 'innodb_buffer_pool_size';
```

---

## Advanced Testing Scenarios

### Scenario 1: Stress Test with Parallel Imports

```bash
# Run 5 imports simultaneously
for i in {1..5}; do
    curl -X POST http://localhost:8080/api/import/csv \
      -F "file=@test_100k.csv" \
      -H "Authorization: Bearer TOKEN" &
done
wait
echo "All imports completed"
```

### Scenario 2: Test Export During Import

```bash
# Start import in background
curl -X POST http://localhost:8080/api/import/csv \
  -F "file=@test_1m.csv" \
  -H "Authorization: Bearer TOKEN" &

# Simultaneously export
sleep 5
curl -X GET "http://localhost:8080/api/export/csv" \
  -H "Authorization: Bearer TOKEN" \
  -o concurrent_export.csv
```

### Scenario 3: Memory Leak Test

```bash
# Monitor memory while importing multiple times
for i in {1..10}; do
    echo "Iteration $i"
    curl -X POST http://localhost:8080/api/import/csv \
      -F "file=@test_100k.csv" \
      -H "Authorization: Bearer TOKEN"
    
    # Check memory
    ps aux | grep "go run" | awk '{print $6}'
    sleep 2
done
```

---

## Validation Checklist

✅ CSV import completes successfully
✅ Import time scales linearly with record count
✅ All 32 workers are utilized (check with `top`)
✅ Database connections pool correctly (check with `SHOW PROCESSLIST`)
✅ Export handles millions of records without memory issues
✅ Large documents upload and download correctly
✅ File sizes match (upload vs download)
✅ No memory leaks during repeated operations
✅ System resources stay within acceptable limits
✅ Error handling works (test with invalid files)

---

## Next Steps

After successful testing:

1. **Configure Production Settings**
   - Set appropriate worker counts for your hardware
   - Configure database for production workload
   - Set up monitoring (Prometheus, Grafana)
   - Enable application logging

2. **Optimize for Your Use Case**
   - Adjust batch sizes based on testing
   - Fine-tune database indexes
   - Configure system limits (ulimit, file descriptors)
   - Set up backup strategies

3. **Scale Further**
   - Consider database sharding for 10B+ rows
   - Implement Redis job queue for distributed processing
   - Use load balancers for multiple backend instances
   - Set up read replicas for exports

4. **Monitor and Maintain**
   - Set up alerts for long-running operations
   - Monitor disk space usage
   - Regular database maintenance (VACUUM, OPTIMIZE)
   - Archive old data periodically

---

## 🎉 Success!

If you've completed all tests successfully, your system is ready for massive scale operations!

**Key Achievements:**
- ✅ Streaming import handles unlimited rows
- ✅ Parallel processing with 32 workers
- ✅ Export supports billions of records
- ✅ Document handling up to 1TB
- ✅ Optimized database operations
- ✅ Memory-efficient processing

**You're now ready to handle enterprise-scale data! 🚀**

---

## Support Resources

- **Full Documentation**: [MASSIVE_SCALE_UPGRADE.md](./MASSIVE_SCALE_UPGRADE.md)
- **API Documentation**: [backend/API_DOCUMENTATION.md](./backend/API_DOCUMENTATION.md)
- **Configuration Guide**: Check `config/config.go` for tuning options

For production deployment, consider:
- Load testing with realistic data volumes
- Database performance tuning
- Hardware sizing based on your needs
- Backup and disaster recovery planning
