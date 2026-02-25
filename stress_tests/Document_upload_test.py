import requests
import time
import sys
from pathlib import Path

# YOUR EXACT FILE PATH
FILE_PATH = r"D:\DataImportDashboard\stress_tests\sample_10GB.pdf"
API_URL = "http://localhost:8080/documents"
CHUNK_SIZE = 8 * 1024 * 1024  # 8 MB chunks (memory-safe)

def format_bytes(bytes_count):
    for unit in ['B', 'KB', 'MB', 'GB']:
        if bytes_count < 1024.0:
            return f"{bytes_count:.2f} {unit}"
        bytes_count /= 1024.0
    return f"{bytes_count:.2f} TB"

def stream_upload():
    file_path = Path(FILE_PATH)
    
    if not file_path.exists():
        print(f"❌ FILE NOT FOUND: {FILE_PATH}")
        print(f"   Please verify the file exists at this location")
        input("\nPress Enter to exit...")
        sys.exit(1)
    
    file_size = file_path.stat().st_size
    file_size_gb = file_size / (1024**3)
    file_size_mb = file_size / (1024**2)
    
    print("=" * 70)
    print("📤 10 GB STREAMING UPLOAD TEST (MEMORY-SAFE)")
    print("=" * 70)
    print(f"📁 File: {FILE_PATH}")
    print(f"📏 Size: {format_bytes(file_size)} ({file_size_gb:.2f} GB)")
    print(f"🌐 API: {API_URL}")
    print(f"📦 Memory usage: ~{CHUNK_SIZE/(1024*1024):.0f} MB at a time")
    print("=" * 70)
    print("\n⏱️  Starting upload (progress updates every 2 seconds)...")
    print("-" * 70)
    
    # Generate multipart boundary
    import uuid
    boundary = uuid.uuid4().hex
    file_name = file_path.name
    
    headers = {
        'Content-Type': f'multipart/form-data; boundary={boundary}'
    }
    
    start_time = time.time()
    
    # Use mutable dict to track progress (avoids nonlocal issues)
    progress = {
        'last_time': start_time,
        'uploaded': 0
    }
    
    def generate_multipart_data():
        """Generator that streams file in 8 MB chunks"""
        # Part 1: Opening boundary + headers
        yield f'--{boundary}\r\n'.encode('utf-8')
        yield f'Content-Disposition: form-data; name="file"; filename="{file_name}"\r\n'.encode('utf-8')
        yield b'Content-Type: application/pdf\r\n'  # PDF MIME type
        yield b'\r\n'
        
        # Part 2: Stream file content in chunks
        with open(file_path, 'rb') as f:
            while True:
                chunk = f.read(CHUNK_SIZE)
                if not chunk:
                    break
                yield chunk
                
                # Update progress
                progress['uploaded'] += len(chunk)
                current_time = time.time()
                
                # Show progress every 2 seconds
                if current_time - progress['last_time'] >= 2.0:
                    uploaded = progress['uploaded']
                    progress_pct = (uploaded / file_size) * 100
                    elapsed = current_time - start_time
                    throughput = (uploaded / (1024**2)) / elapsed if elapsed > 0 else 0
                    remaining = (file_size - uploaded) / (throughput * 1024**2) if throughput > 0 else 0
                    
                    print(f"   📊 {progress_pct:5.1f}% | "
                          f"Uploaded: {format_bytes(uploaded):>10} | "
                          f"Speed: {throughput:6.1f} MB/s | "
                          f"ETA: {remaining:5.0f}s")
                    
                    progress['last_time'] = current_time
        
        # Part 3: Closing boundary
        yield b'\r\n'
        yield f'--{boundary}--\r\n'.encode('utf-8')
    
    try:
        response = requests.post(
            API_URL,
            headers=headers,
            data=generate_multipart_data(),
            timeout=None
        )
        
        total_time = time.time() - start_time
        throughput_mbs = file_size_mb / total_time
        throughput_mbps = throughput_mbs * 8
        
        print("-" * 70)
        print("✅ UPLOAD COMPLETE")
        print("=" * 70)
        print(f"📊 FINAL RESULTS:")
        print(f"   HTTP Status: {response.status_code}")
        print(f"   Total Time:  {total_time:.2f} seconds ({total_time/60:.2f} minutes)")
        print(f"   Throughput:  {throughput_mbs:.2f} MB/s")
        print(f"   Throughput:  {throughput_mbps:.2f} Mbps")
        print("=" * 70)
        
        if response.text:
            print(f"\n📝 SERVER RESPONSE (first 300 chars):")
            print(f"   {response.text[:300]}")
            if len(response.text) > 300:
                print(f"   ... (truncated)")
        
        return True
        
    except requests.exceptions.Timeout:
        elapsed = time.time() - start_time
        print(f"\n❌ TIMEOUT after {elapsed:.2f} seconds")
        print("   → Increase server timeout settings")
        return False
        
    except requests.exceptions.ConnectionError as e:
        elapsed = time.time() - start_time
        print(f"\n❌ CONNECTION ERROR after {elapsed:.2f} seconds")
        print(f"   → {str(e)[:100]}")
        return False
        
    except Exception as e:
        elapsed = time.time() - start_time
        print(f"\n❌ UNEXPECTED ERROR after {elapsed:.2f} seconds")
        print(f"   → {type(e).__name__}: {str(e)}")
        import traceback
        traceback.print_exc()
        return False

if __name__ == "__main__":
    print("\n")
    success = stream_upload()
    input("\n✅ Test finished. Press Enter to exit...")
    sys.exit(0 if success else 1)