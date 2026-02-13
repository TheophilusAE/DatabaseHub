import http from 'k6/http';

const CSV_PATH = './upload_trial_800k.csv'; // EXACT filename from your path
const API_URL = 'http://localhost:8080/upload/csv';
const TIMEOUT_SEC = 600; // 10 minutes – safe for 800k rows
const ROWS_PER_FILE = 800000; // 800k rows

const csvData = open(CSV_PATH, 'b');

export let options = {
  vus: 1,           // START WITH 1 USER ONLY
  duration: '300s', // 5 minutes test
};

export default function () {
  const start = Date.now();
  
  const res = http.post(API_URL, {
    file: http.file(csvData, 'upload.csv', 'text/csv'),
  }, {
    timeout: TIMEOUT_SEC * 1000,
  });

  const durationSec = (Date.now() - start) / 1000;
  const success = res.status >= 200 && res.status < 300;
  
  if (success) {
    const rowsPerSec = ROWS_PER_FILE / durationSec;
    console.log(`✅ ${ROWS_PER_FILE.toLocaleString()} rows | ${durationSec.toFixed(1)}s | ${rowsPerSec.toFixed(0)} rows/sec`);
  } else {
    console.log(`❌ FAILED | Status=${res.status} | Time=${durationSec.toFixed(1)}s`);
    if (res.body) console.log(`   Response: ${res.body.substring(0, 150)}`);
  }
}