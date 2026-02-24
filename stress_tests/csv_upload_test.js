import http from 'k6/http';
import { check } from 'k6';

const BASE_URL = __ENV.BASE_URL || 'http://localhost:8080';
const ENDPOINT = '/simple-multi/upload-multiple';

// Auth fallback accepted by backend AuthRequired middleware
const USER_ID = __ENV.USER_ID || '1';
const USER_ROLE = __ENV.USER_ROLE || 'admin';
const TOKEN = __ENV.TOKEN || '';

// Same repeated fields the website sends:
// - files (multipart file, can be repeated)
// - table_names (text value, one per file)
// Use FILES_MAP_JSON for multi-file runs:
// FILES_MAP_JSON='[{\"path\":\"./sales_table_600k_rows.csv\",\"table\":\"sales\"}]'
const FILES_MAP_JSON = __ENV.FILES_MAP_JSON || '';
const DEFAULT_CSV_PATH = __ENV.CSV_PATH || './sales_table_600k_rows.csv';
const DEFAULT_TABLE_NAME = __ENV.TABLE_NAME || 'sales';
const DEFAULT_ROW_COUNT = Number(__ENV.ROW_COUNT || 600000); // For sales_table_600k_rows.csv
const TIMEOUT_SEC = Number(__ENV.TIMEOUT_SEC || 1800);
const MAX_DURATION = __ENV.MAX_DURATION || '30m';

function getFileName(pathValue) {
  return String(pathValue).split(/[\\/]/).pop();
}
function getRowCountFromPath(pathValue) {
  const match = String(pathValue).match(/(\d+)k?_rows/);
  if (match && match[1]) {
    const num = parseInt(match[1], 10);
    return isNaN(num) ? DEFAULT_ROW_COUNT : num * 1000;
  }
  return DEFAULT_ROW_COUNT;
}
function loadUploadItems() {
  if (FILES_MAP_JSON) {
    const parsed = JSON.parse(FILES_MAP_JSON);
    if (!Array.isArray(parsed) || parsed.length === 0) {
      throw new Error('FILES_MAP_JSON must be a non-empty JSON array');
    }

    return parsed.map((item, index) => {
      if (!item.path || !item.table) {
        throw new Error(`FILES_MAP_JSON[${index}] must include path and table`);
      }

      return {
        table: String(item.table),
        filename: getFileName(item.path),
        bytes: open(String(item.path), 'b'),
        rowCount: getRowCountFromPath(item.path),
      };
    });
  }

  return [{
    table: DEFAULT_TABLE_NAME,
    filename: getFileName(DEFAULT_CSV_PATH),
    bytes: open(DEFAULT_CSV_PATH, 'b'),
    rowCount: DEFAULT_ROW_COUNT,
  }];
}

const uploadItems = loadUploadItems();

export const options = {
  scenarios: {
    upload_once: {
      executor: 'per-vu-iterations',
      vus: Number(__ENV.VUS || 1),
      iterations: Number(__ENV.ITERATIONS || 1),
      maxDuration: MAX_DURATION,
    },
  },
  thresholds: {
    http_req_failed: ['rate<0.05'],
    http_req_duration: [`p(95)<${TIMEOUT_SEC * 1000}`],
  },
};

export default function () {
  let totalRows = 0;
  const firstItem = uploadItems[0];
  totalRows = firstItem.rowCount;

  const payload = {
    files: http.file(firstItem.bytes, firstItem.filename, 'text/csv'),
    table_names: firstItem.table,
  };

  const headers = {
    'X-User-ID': USER_ID,
    'X-User-Role': USER_ROLE,
  };

  if (TOKEN) {
    headers.Authorization = `Bearer ${TOKEN}`;
  }

  const apiUrl = `${BASE_URL}${ENDPOINT}?user_id=${encodeURIComponent(USER_ID)}&user_role=${encodeURIComponent(USER_ROLE)}`;
  const start = Date.now();

  const res = http.post(apiUrl, payload, {
    headers,
    timeout: TIMEOUT_SEC * 1000,
  });

  const durationSec = (Date.now() - start) / 1000;
  const rowsPerSec = durationSec > 0 ? Math.round(totalRows / durationSec) : 0;
  const ok = check(res, {
    'status is 200': (r) => r.status === 200,
    'has completion message': (r) => (r.body || '').includes('Multi-table upload completed'),
  });

  if (ok) {
    console.log(
      `✅ UPLOAD | files=1 | rows=${totalRows.toLocaleString()} | ` +
      `time=${durationSec.toFixed(1)}s | rate=${rowsPerSec.toLocaleString()} rows/sec`
    );
  } else {
    console.log(`❌ UPLOAD FAILED | status=${res.status} | time=${durationSec.toFixed(1)}s`);
    if (res.body) {
      console.log(`   response=${res.body.substring(0, 300)}`);
    }
  }
}