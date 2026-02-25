# Data Import Dashboard — Report Charts (Mermaid)

Paste these directly into Markdown editors that support Mermaid (VS Code preview, GitHub, Obsidian, etc.).

## 1) ERD (Entity Relationship Diagram)

```mermaid
erDiagram
    USERS {
        uint id PK
        string name
        string email UK
        string password
        string role
        datetime created_at
        datetime updated_at
    }

    TABLE_CONFIGS {
        uint id PK
        string name
        string database_name
        string table_name
        string description
        text columns
        string primary_key
        boolean is_active
        string created_by
    }

    USER_TABLE_PERMISSIONS {
        uint id PK
        uint user_id FK
        uint table_config_id FK
        bool can_view
        bool can_edit
        bool can_delete
        bool can_export
        bool can_import
    }

    TABLE_JOINS {
        uint id PK
        string name
        uint left_table_id FK
        uint right_table_id FK
        uint target_table_id FK
        string join_type
        text join_condition
        text select_columns
    }

    IMPORT_MAPPINGS {
        uint id PK
        string name
        string source_format
        uint table_config_id FK
        text column_mapping
        text transform
    }

    EXPORT_CONFIGS {
        uint id PK
        string name
        string source_type
        uint source_id
        string target_format
        text filters
        text order_by
        text column_list
    }

    DATA_RECORDS {
        uint id PK
        string name
        text description
        string category
        float value
        string status
        text metadata
    }

    IMPORT_LOGS {
        uint id PK
        string file_name
        string import_type
        int total_records
        int success_count
        int failure_count
        string status
        text error_message
        string imported_by
    }

    DOCUMENTS {
        uint id PK
        string file_name
        string original_name
        string file_path
        int64 file_size
        string file_type
        string mime_type
        string category
        string document_type
        string uploaded_by
        string status
    }

    DOCUMENT_CATEGORIES {
        uint id PK
        string name UK
    }

    USERS ||--o{ USER_TABLE_PERMISSIONS : has
    TABLE_CONFIGS ||--o{ USER_TABLE_PERMISSIONS : controls

    TABLE_CONFIGS ||--o{ IMPORT_MAPPINGS : maps_to
    TABLE_CONFIGS ||--o{ TABLE_JOINS : left_table
    TABLE_CONFIGS ||--o{ TABLE_JOINS : right_table
    TABLE_CONFIGS ||--o{ TABLE_JOINS : target_table

    DOCUMENT_CATEGORIES ||--o{ DOCUMENTS : categorizes
```

---

## 2) Flowchart — Login and Authorization

```mermaid
flowchart TD
    A([Start]) --> B[User submits email/password]
    B --> C[Backend: bind and validate request]
    C --> D{Input valid?}
    D -- No --> E[Return 400 validation error]
    D -- Yes --> F[Find user by email]
    F --> G{User exists?}
    G -- No --> H[Return 401 Invalid credentials]
    G -- Yes --> I[Compare bcrypt password hash]
    I --> J{Password match?}
    J -- No --> H
    J -- Yes --> K[Return login success + user profile]
    K --> L([End])
    E --> L
    H --> L
```

---

## 3) Flowchart — CSV/JSON Import (Batch + Workers)

```mermaid
flowchart TD
    A([Start]) --> B[Receive uploaded file]
    B --> C{File present?}
    C -- No --> Z1[Return 400 No file uploaded]
    C -- Yes --> D[Create ImportLog status=processing]
    D --> E{Type CSV or JSON?}

    E -- CSV --> F1[Read CSV headers]
    F1 --> F2{Headers valid?}
    F2 -- No --> Z2[Update ImportLog failed + return 400]
    F2 -- Yes --> G[Stream rows and build batches]

    E -- JSON --> J1[Stream JSON decoder]
    J1 --> J2{JSON array valid?}
    J2 -- No --> Z2
    J2 -- Yes --> G

    G --> H[Push batches to worker queue]
    H --> I[Workers insert batch to repository]
    I --> K[Aggregate success/failure counters]
    K --> L[Close queue and wait workers]
    L --> M[Update ImportLog completed/failed]
    M --> N[Return summary: total, success, failed]
    N --> O([End])
    Z1 --> O
    Z2 --> O
```

---

## 4) Flowchart — Admin Table Permission Assignment

```mermaid
flowchart TD
    A([Start]) --> B[Admin sends permission assignment request]
    B --> C{Requester role = admin?}
    C -- No --> D[Return 403 Forbidden]
    C -- Yes --> E[Validate request body]
    E --> F{Valid payload?}
    F -- No --> G[Return 400 Invalid body]
    F -- Yes --> H[Revoke existing user permissions]
    H --> I[Bulk assign new table permissions]
    I --> J{DB operation success?}
    J -- No --> K[Return 500 Failed to assign]
    J -- Yes --> L[Return 200 Permissions updated]
    D --> M([End])
    G --> M
    K --> M
    L --> M
```

---

## 5) DFD Level 0 (Context Diagram)

```mermaid
flowchart LR
    U[User]
    A[Admin]
    S((Data Import Dashboard System))
    DB[(Application Database)]
    FS[(File Storage)]

    U -->|Login, view/export/import requests| S
    A -->|User mgmt, table config, permissions| S
    S -->|Responses, files, status| U
    S -->|Admin reports and confirmations| A
    S <--> |CRUD, logs, configs| DB
    S <--> |Upload/Download documents| FS
```

---

## 6) DFD Level 1 (Major Processes)

```mermaid
flowchart LR
    U[User/Admin]
    P1((1.0 Auth))
    P2((2.0 Import Engine))
    P3((3.0 Export Engine))
    P4((4.0 Permission Manager))
    P5((5.0 Document Manager))

    D1[(Users)]
    D2[(Data Records)]
    D3[(Import Logs)]
    D4[(Table Configs)]
    D5[(User Table Permissions)]
    D6[(Documents)]
    F1[(File Storage)]

    U --> P1
    P1 <--> D1

    U --> P2
    P2 <--> D2
    P2 <--> D3

    U --> P3
    P3 --> D2
    P3 --> U

    U --> P4
    P4 <--> D4
    P4 <--> D5
    P4 <--> D1

    U --> P5
    P5 <--> D6
    P5 <--> F1
```

---

## 7) Use Case Diagram

```mermaid
flowchart LR
    Admin[Admin]
    User[User]

    UC1((Register/Login))
    UC2((Import CSV/JSON))
    UC3((Export CSV/JSON))
    UC4((Manage Users))
    UC5((Assign Table Permissions))
    UC6((Manage Table Configs))
    UC7((Upload/Manage Documents))
    UC8((View Import Logs))

    User --> UC1
    User --> UC2
    User --> UC3
    User --> UC7
    User --> UC8

    Admin --> UC1
    Admin --> UC2
    Admin --> UC3
    Admin --> UC4
    Admin --> UC5
    Admin --> UC6
    Admin --> UC7
    Admin --> UC8
```

---

## 8) Sequence Diagram — Import CSV

```mermaid
sequenceDiagram
    participant U as User
    participant API as ImportHandler
    participant LOG as ImportLogRepository
    participant WRK as Worker Pool
    participant DR as DataRecordRepository

    U->>API: Upload CSV file
    API->>LOG: Create log(status=processing)
    LOG-->>API: log_id
    API->>API: Read headers + stream rows
    API->>WRK: Send record batches
    loop Each batch
        WRK->>DR: CreateBatch(records)
        DR-->>WRK: success/failure
    end
    API->>LOG: Update log(total/success/failure,status)
    API-->>U: Return import summary
```

---

## Notes for Report Writing

- Keep ERD focused on persistent entities (`users`, `table_configs`, `user_table_permissions`, `import_logs`, etc.).
- If your reviewer asks about `documents.category` vs `document_categories`, note that current model links by name, not FK ID.
- For “System Design” chapter, use DFD Level 0 + Level 1 + one sequence diagram.
- For “Database Design” chapter, use ERD and explain 1:N relationships and permission control via junction table.
