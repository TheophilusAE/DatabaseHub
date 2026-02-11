# Quick Start Options

You now have **TWO ways** to run the frontend:

## Option 1: Simple Mode (Recommended for Production)
**Just run Laravel - No Vite needed!**

```bash
php artisan serve
```

Or use the helper script:
```bash
.\start-simple.bat
```

✅ **Pros:**
- Simple: Only one command
- Fast: No asset compilation overhead
- Perfect for: Production, testing, demos

❌ **Cons:**
- No hot reload (need to refresh browser manually)
- Need to run `npm run build` after CSS/JS changes

---

## Option 2: Development Mode (With Hot Reload)
**Run both Laravel AND Vite for hot module replacement**

```bash
.\start.bat
```

This opens 2 terminals:
1. Laravel Server (`php artisan serve`)
2. Vite Dev Server (`npm run dev`)

✅ **Pros:**
- Hot reload: Changes appear instantly
- Better DX: No manual refresh needed
- Perfect for: Active development

❌ **Cons:**
- More complex: 2 servers running
- Slower startup

---

## When to Rebuild Assets

If you modify CSS or JavaScript files, you need to rebuild:

```bash
npm run build
```

Then restart `php artisan serve` or just refresh the browser.

---

## Current Status

✅ Assets are **already built** in `public/build/`
✅ You can run `php artisan serve` right now!
✅ Navigate to: http://localhost:8000

---

## Troubleshooting

### Assets not showing up?
Run: `npm run build`

### Hot reload not working in dev mode?
Make sure both terminals are running (Laravel + Vite)

### Route errors?
Make sure backend is running on http://localhost:8080
