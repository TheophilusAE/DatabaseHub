import http from 'k6/http';
import { check } from 'k6';

const BASE_URL = __ENV.BASE_URL || 'http://localhost:8080';
const EXPORT_ENDPOINT = '/unified/export';

// Auth fallback accepted by backend AuthRequired middleware
const USER_ID = __ENV.USER_ID || '1';
const USER_ROLE = __ENV.USER_ROLE || 'admin';
const TOKEN = __ENV.TOKEN || '';

// Export configuration
// TABLES_JSON='[{"table_name":"sales","columns":[],"filters":""}]'
// For sales table with 600k rows: EXPECTED_ROWS=600000
const TABLES_JSON = __ENV.TABLES_JSON || JSON.stringify([
  {
    table_name: 'sales',
    columns: [],
    filters: ''
  }
]);
const EXPORT_FORMAT = __ENV.FORMAT || 'csv';
const EXPECTED_ROWS = Number(__ENV.EXPECTED_ROWS || 600000);
const TIMEOUT_SEC = Number(__ENV.TIMEOUT_SEC || 600);

export const options = {
  vus: Number(__ENV.VUS || 1),
  duration: __ENV.DURATION || '300s',
  thresholds: {
    http_req_failed: ['rate<0.05'],
    http_req_duration: ['p(95)<120000'],
  },
};

export default function () {
  let tables;
  try {
    tables = JSON.parse(TABLES_JSON);
    if (!Array.isArray(tables) || tables.length === 0) {
      throw new Error('TABLES_JSON must be a non-empty array');
    }
  } catch (e) {
    console.error(`Failed to parse TABLES_JSON: ${e.message}`);
    return;
  }

  const payload = {
    tables: tables,
    format: EXPORT_FORMAT,
  };

  const headers = {
    'Content-Type': 'application/json',
    'X-User-ID': USER_ID,
    'X-User-Role': USER_ROLE,
  };

  if (TOKEN) {
    headers.Authorization = `Bearer ${TOKEN}`;
  }

  const apiUrl = `${BASE_URL}${EXPORT_ENDPOINT}?user_id=${encodeURIComponent(USER_ID)}&user_role=${encodeURIComponent(USER_ROLE)}`;
  const start = Date.now();

  const res = http.post(apiUrl, JSON.stringify(payload), {
    headers,
    timeout: TIMEOUT_SEC * 1000,
  });

  const durationSec = (Date.now() - start) / 1000;
  
  // Estimate rows from response body size (CSV ~80-150 bytes per row, JSON ~200-300 bytes per row)
  let estimatedRows = EXPECTED_ROWS;
  const contentLength = res.headers['Content-Length'] ? parseInt(res.headers['Content-Length'], 10) : 0;
  const responseSize = (res.body || '').length || contentLength;
  
  if (responseSize > 0 && EXPORT_FORMAT === 'csv') {
    // Rough estimate: CSV averages ~100 bytes per row
    estimatedRows = Math.round(responseSize / 100);
  } else if (responseSize > 0 && EXPORT_FORMAT === 'json') {
    // Rough estimate: JSON averages ~250 bytes per row
    estimatedRows = Math.round(responseSize / 250);
  }
  
  const rowsPerSec = durationSec > 0 ? Math.round(estimatedRows / durationSec) : 0;
  const ok = check(res, {
    'status is 200': (r) => r.status === 200,
    'has response body': (r) => r.body && r.body.length > 0,
  });

  if (ok) {
    console.log(
      `✅ EXPORT | tables=${tables.length} | format=${EXPORT_FORMAT} | ` +
      `rows≈${estimatedRows.toLocaleString()} | time=${durationSec.toFixed(1)}s | ` +
      `rate=${rowsPerSec.toLocaleString()} rows/sec | size=${(responseSize/1024/1024).toFixed(2)}MB`
    );
  } else {
    console.log(`❌ EXPORT FAILED | status=${res.status} | time=${durationSec.toFixed(1)}s`);
    if (res.body) {
      console.log(`   response=${res.body.substring(0, 300)}`);
    }
  }
}
