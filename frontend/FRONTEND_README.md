# Data Import Dashboard - Frontend

Laravel-based frontend for the Data Import Dashboard application.

## Overview

This is the frontend interface for managing data records, documents, imports, and exports. It communicates with the Go backend API running on port 8080.

## Tech Stack

- **Framework**: Laravel 11
- **CSS Framework**: Tailwind CSS 4.0
- **Build Tool**: Vite
- **JavaScript**: Vanilla JS with Axios

## Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js 18 or higher
- npm or yarn
- Backend API running on `http://localhost:8080`

## Installation

### 1. Install PHP Dependencies

```bash
composer install
```

### 2. Install Node Dependencies

```bash
npm install
```

### 3. Environment Configuration

Copy the `.env.example` file to `.env`:

```bash
cp .env.example .env
```

Generate application key:

```bash
php artisan key:generate
```

### 4. Configure Backend API

The frontend is configured to connect to the backend at `http://localhost:8080`. If your backend runs on a different URL, update it in:

- `resources/js/app.js` - Update `window.API_BASE_URL`

## Running the Application

### Development Mode

Start the Laravel development server:

```bash
php artisan serve
```

In a separate terminal, start Vite for asset compilation:

```bash
npm run dev
```

The application will be available at: `http://localhost:8000`

### Production Build

Build assets for production:

```bash
npm run build
```

## Features

### 📊 Dashboard
- Overview of total records, documents, and imports
- Server health status
- Recent activity feed
- Quick action buttons

### 📝 Data Records Management
- View all data records with pagination
- Create new records
- Edit existing records
- Delete records
- Filter by category and status
- Search functionality

### 📁 Document Management
- Upload any file type (PDF, images, videos, etc.)
- View all documents in a grid layout
- Download documents
- Delete documents
- Filter by category
- Drag-and-drop file upload

### 📤 Import Data
- Import data from CSV files
- Import data from JSON files
- Real-time upload progress
- View recent import history
- Success/failure statistics

### 📥 Export Data
- Export to CSV format
- Export to JSON format
- Export to Excel format
- Filter exports by category
- View total records by category

### 📜 Import History
- View all import logs
- See success/failure counts
- Filter and search history
- Pagination support

## API Integration

All views communicate directly with the Go backend API at `http://localhost:8080`. The API endpoints used:

- `GET /health` - Server health check
- `GET /data` - Get all records
- `POST /data` - Create record
- `PUT /data/:id` - Update record
- `DELETE /data/:id` - Delete record
- `GET /documents` - Get all documents
- `POST /documents` - Upload document
- `GET /documents/:id/download` - Download file
- `DELETE /documents/:id` - Delete document
- `POST /upload/csv` - Import CSV
- `POST /upload/json` - Import JSON
- `GET /upload/history` - Get import logs
- `GET /download/csv` - Export to CSV
- `GET /download/json` - Export to JSON
- `GET /download/excel` - Export to Excel

## Project Structure

```
frontend/
├── app/
│   └── Http/
│       └── Controllers/     # Laravel controllers
│           ├── DashboardController.php
│           ├── DataRecordController.php
│           ├── DocumentController.php
│           ├── ImportController.php
│           └── ExportController.php
├── resources/
│   ├── views/              # Blade templates
│   │   ├── layouts/
│   │   │   └── app.blade.php
│   │   ├── dashboard.blade.php
│   │   ├── data-records/
│   │   ├── documents/
│   │   ├── import/
│   │   └── export/
│   ├── js/
│   │   └── app.js         # JavaScript entry point
│   └── css/
│       └── app.css        # Tailwind CSS
├── routes/
│   └── web.php            # Web routes
├── public/                # Public assets
├── composer.json          # PHP dependencies
├── package.json           # Node dependencies
└── vite.config.js         # Vite configuration
```

## Available Routes

| Route | Description |
|-------|-------------|
| `/` | Dashboard |
| `/data-records` | View all data records |
| `/data-records/create` | Create new record |
| `/data-records/{id}/edit` | Edit record |
| `/documents` | View all documents |
| `/documents/create` | Upload document |
| `/import` | Import data page |
| `/import/history` | Import history |
| `/export` | Export data page |

## Styling

The application uses **Tailwind CSS 4.0** for styling. All views are fully responsive and work on mobile, tablet, and desktop devices.

Color scheme:
- Primary: Blue (#2563eb)
- Success: Green (#10b981)
- Warning: Yellow (#f59e0b)
- Danger: Red (#ef4444)

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## Development Tips

### Clearing Cache

```bash
php artisan cache:clear
php artisan view:clear
php artisan config:clear
```

### Watching for Changes

Vite automatically watches for file changes when running `npm run dev`.

### Adding New Views

1. Create controller in `app/Http/Controllers/`
2. Add route in `routes/web.php`
3. Create Blade view in `resources/views/`
4. Style with Tailwind CSS classes

## Troubleshooting

### Backend Connection Error

**Issue**: Cannot connect to backend API

**Solution**:
1. Ensure backend server is running on port 8080
2. Check `resources/js/app.js` for correct `API_BASE_URL`
3. Verify CORS is properly configured in backend

### Styling Not Loading

**Issue**: Tailwind CSS not working

**Solution**:
1. Run `npm run dev` or `npm run build`
2. Clear browser cache
3. Check Vite is running without errors

### File Upload Fails

**Issue**: Documents/files not uploading

**Solution**:
1. Check backend `uploads/` directory exists and is writable
2. Verify file size is under 10MB
3. Check backend server logs for errors

## Contributing

1. Create a new branch for your feature
2. Make your changes
3. Test thoroughly
4. Submit a pull request

## License

This project is part of the Data Import Dashboard system.

## Support

For issues and questions:
- Check backend documentation
- Review API endpoints
- Check browser console for errors
- Verify backend server is running

---

**Made with ❤️ using Laravel and Tailwind CSS**
