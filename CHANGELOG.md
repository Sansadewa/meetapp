# MeetApp Kalsel - Changelog

## [Unreleased] - 2026-02-05

### Fixed
- **Routing Conflict Resolution**: Fixed `/login` route conflict with physical directory
  - Moved `public/login/` directory to `public/assets/login/`
  - Updated all asset references in `resources/views/login/login.blade.php`
  - Prevents Apache from serving directory listing instead of routing through Laravel
  - **Files affected**: 2 files (directory move + 1 view file with 11 references)

### Changed
- **HTTPS Detection**: Implemented proper HTTPS scheme detection for Cloudflare Tunnel
  - Removed hardcoded URL hacks from 4 files (views and JavaScript)
  - Added conditional HTTPS forcing in `AppServiceProvider`
  - Moved Zoom redirect URI to environment variable
  - **Files affected**: 9 files (.env, config, provider, controller, 3 views, 1 JS file)

### Added
- Environment variables for multi-domain support:
  - `APP_URL=https://meetapp.statkalsel.com`
  - `FORCE_HTTPS=true`
  - `INTERNAL_IP=10.63.0.234`
  - `ZOOM_REDIRECT_URI=https://meetapp.statkalsel.com/callback-zoom`

### Security
- Updated `.gitignore` to exclude sensitive files:
  - `storage/app/zoom_credentials*.json`
  - Storage framework caches and logs

---

## Deployment Notes

### For Server Deployment (E:\temphtdocs\meetapp\)

After pulling from git, you must:

1. **Move the login assets directory on the server:**
   ```cmd
   move E:\temphtdocs\meetapp\public\login E:\temphtdocs\meetapp\public\assets\login
   ```

2. **Clear Laravel caches:**
   ```cmd
   php artisan view:clear
   php artisan config:clear
   php artisan cache:clear
   ```

3. **Restart Apache** to load updated VirtualHost configuration

4. **Update Cloudflare:**
   - Add DNS CNAME: `meetapp.statkalsel.com` → tunnel hostname
   - Add Cloudflare Tunnel public hostname with HTTP Host Header

5. **Update Zoom OAuth:**
   - Add redirect URI: `https://meetapp.statkalsel.com/callback-zoom`

---

## File Changes Summary

### Modified Files (11 total)
1. `.env` - Added 4 new environment variables
2. `.gitignore` - Added Zoom credentials exclusion
3. `config/app.php` - Removed hardcoded URL logic
4. `app/Providers/AppServiceProvider.php` - Added HTTPS detection
5. `app/Http/Controllers/zoomController.php` - Use env variables
6. `resources/views/layout/index.blade.php` - Removed URL hack
7. `resources/views/pages/home.blade.php` - Removed URL hack
8. `resources/views/pages/today.blade.php` - Removed URL hack
9. `resources/views/login/login.blade.php` - Updated asset paths
10. `public/login/main.js` - Removed URL hack (now at `public/assets/login/main.js`)
11. `public/assets/login/` - Moved from `public/login/`

### New Files (3 total)
- `DEPLOYMENT.md` - Deployment guide
- `FILES_TO_COPY.txt` - Manual copy checklist
- `CHANGELOG.md` - This file

---

## Testing Checklist

- [ ] Access `https://meetapp.statkalsel.com` - should show login page
- [ ] Login page assets load correctly (no 404s)
- [ ] No directory listing shown on `/login` route
- [ ] Internal access works: `http://10.63.0.234/meetapp`
- [ ] Zoom OAuth callback works correctly
- [ ] No mixed content warnings in browser console
- [ ] WhatsApp notifications still send correctly
- [ ] Meeting creation and calendar work normally

---

**Last Updated**: 2026-02-05  
**Version**: 1.1.0 - Login Assets Reorganization + HTTPS Fix
