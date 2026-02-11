# JSON Import Format Guide

## Overview

This guide explains the correct JSON format for importing data records into the system.

---

## ✅ Correct JSON Format

### Basic Structure

The JSON file must contain an **array of objects** where each object represents a data record:

```json
[
  {
    "name": "Product Name",
    "description": "Product description",
    "category": "category_name",
    "value": 99.99,
    "status": "active"
  },
  {
    "name": "Another Product",
    "description": "Another description",
    "category": "electronics",
    "value": 199.99,
    "status": "active"
  }
]
```

---

## 📋 Field Specifications

| Field | Type | Required | Description | Example |
|-------|------|----------|-------------|---------|
| `name` | string | ✅ Yes | Name of the record | "Gaming Laptop" |
| `description` | string | ❌ No | Detailed description | "High-end gaming laptop" |
| `category` | string | ❌ No | Category name | "electronics" |
| `value` | number | ❌ No | Numeric value | 2499.99 |
| `status` | string | ❌ No | Record status (default: "active") | "active", "inactive" |
| `metadata` | string or null | ❌ No | Additional metadata (can be JSON string or null) | "{\"brand\":\"ASUS\"}" |

---

## 📝 Valid Examples

### Example 1: Minimal Record (Only Required Fields)

```json
[
  {
    "name": "Simple Product"
  }
]
```

### Example 2: Complete Record with All Fields

```json
[
  {
    "name": "Gaming Laptop ASUS ROG",
    "description": "High-end gaming laptop with RTX 4080",
    "category": "electronics",
    "value": 2499.99,
    "status": "active",
    "metadata": "{\"brand\":\"ASUS\",\"gpu\":\"RTX 4080\",\"ram\":\"32GB\"}"
  }
]
```

### Example 3: Record with Null Metadata

```json
[
  {
    "name": "Office Chair",
    "description": "Ergonomic office chair",
    "category": "furniture",
    "value": 299.99,
    "status": "active",
    "metadata": null
  }
]
```

### Example 4: Record Without Metadata Field

```json
[
  {
    "name": "Standing Desk",
    "description": "Electric height-adjustable standing desk",
    "category": "furniture",
    "value": 599.99,
    "status": "active"
  }
]
```

### Example 5: Multiple Records (Mixed)

```json
[
  {
    "name": "Product 1",
    "description": "With metadata",
    "category": "electronics",
    "value": 99.99,
    "status": "active",
    "metadata": "{\"color\":\"black\"}"
  },
  {
    "name": "Product 2",
    "description": "Without metadata",
    "category": "accessories",
    "value": 49.99,
    "status": "active"
  },
  {
    "name": "Product 3",
    "description": "With null metadata",
    "category": "furniture",
    "value": 299.99,
    "status": "inactive",
    "metadata": null
  }
]
```

---

## ❌ Common Errors

### Error 1: Not an Array

**❌ Wrong:**
```json
{
  "name": "Product"
}
```

**✅ Correct:**
```json
[
  {
    "name": "Product"
  }
]
```

### Error 2: Invalid JSON Syntax

**❌ Wrong:**
```json
[
  {
    name: "Product",  // Missing quotes around key
    "value": 99.99,
  }  // Trailing comma
]
```

**✅ Correct:**
```json
[
  {
    "name": "Product",
    "value": 99.99
  }
]
```

### Error 3: Wrong Data Types

**❌ Wrong:**
```json
[
  {
    "name": "Product",
    "value": "99.99"  // String instead of number
  }
]
```

**✅ Correct:**
```json
[
  {
    "name": "Product",
    "value": 99.99  // Number without quotes
  }
]
```

### Error 4: Missing Required Field (Name)

**❌ Wrong:**
```json
[
  {
    "description": "Product without name",
    "value": 99.99
  }
]
```

**✅ Correct:**
```json
[
  {
    "name": "Product Name",
    "description": "Product description",
    "value": 99.99
  }
]
```

---

## 🧪 Testing JSON Import

### Using Postman

1. **Set Method:** `POST`
2. **URL:** `http://localhost:8080/upload/json`
3. **Body:** Select `form-data`
4. **Add Field:**
   - Key: `file` (Type: File)
   - Value: Select your JSON file
5. Click **Send**

### Using cURL (Command Line)

```bash
curl -X POST http://localhost:8080/upload/json \
  -F "file=@sample_data.json"
```

### Using PowerShell

```powershell
curl.exe -X POST http://localhost:8080/upload/json `
  -F "file=@sample_data.json"
```

### Using Test Script

```bash
# Windows
.\test-json-import.bat
```

---

## 📤 Expected Response

### Successful Import

```json
{
  "message": "Import completed successfully",
  "total": 6,
  "success": 6,
  "failed": 0,
  "import_log_id": 1
}
```

### Failed Import (Invalid JSON)

```json
{
  "error": "Failed to parse JSON file. Ensure it's a valid JSON array of records.",
  "details": "invalid character '}' looking for beginning of value"
}
```

### Empty File

```json
{
  "message": "No records found in JSON file",
  "total": 0,
  "import_log_id": 1
}
```

---

## 🔍 Validating Your JSON File

### Online Validators

1. **JSONLint:** https://jsonlint.com/
2. **JSON Formatter:** https://jsonformatter.org/

### VS Code

1. Open your JSON file in VS Code
2. Look for syntax highlighting errors (red underlines)
3. Format the document: `Shift + Alt + F`

### Command Line

```bash
# Using Python
python -m json.tool sample_data.json

# Using Node.js
node -e "console.log(JSON.stringify(require('./sample_data.json'), null, 2))"
```

---

## 📊 Sample Data File

The `sample_data.json` file in the backend folder contains a valid example with:
- ✅ 6 records total
- ✅ Some with metadata
- ✅ Some without metadata
- ✅ One with null metadata
- ✅ Various categories: electronics, accessories, furniture, education

You can use this as a template for your own JSON imports.

---

## 💡 Best Practices

1. **Validate JSON** before importing using online tools
2. **Start small** - test with 1-2 records first
3. **Use consistent formatting** for easier debugging
4. **Include metadata** as JSON strings when needed
5. **Use null** instead of empty strings for optional fields
6. **Check encoding** - use UTF-8 for international characters
7. **Remove BOM** (Byte Order Mark) if present

---

## 🚀 Advanced: Nested Metadata

If you want to store complex metadata, stringify it properly:

```json
[
  {
    "name": "Advanced Product",
    "category": "electronics",
    "value": 999.99,
    "metadata": "{\"specs\":{\"cpu\":\"Intel i9\",\"ram\":\"32GB\"},\"warranty\":\"2 years\"}"
  }
]
```

When retrieved, you can parse this metadata in your frontend:

```javascript
const record = await fetch('/data/1').then(r => r.json());
const metadata = JSON.parse(record.data.metadata);
console.log(metadata.specs.cpu); // "Intel i9"
```

---

## ❓ Troubleshooting

### Issue: "Failed to parse JSON"
**Solution:** Validate your JSON syntax at jsonlint.com

### Issue: "No file uploaded"
**Solution:** Ensure you're using form-data and the field name is "file"

### Issue: Database errors
**Solution:** Check that required fields (name) are present in all records

### Issue: Import shows 0 records
**Solution:** Verify your JSON is an array, not a single object

---

## 📞 Need Help?

If you're still having issues:

1. Check the import logs: `GET /upload/history`
2. Verify your JSON at jsonlint.com
3. Compare your file to `sample_data.json`
4. Check server logs for detailed error messages

---

**Happy Importing! 🎉**
