# DDEV ↔ cPanel Sync Workflow

## 🎯 Overview
This project maintains two synchronized versions:
- **DDEV version**: Development environment with full backend
- **cPanel version**: Production-ready static files for GoDaddy hosting

## 📁 Directory Structure
```
sdc_webform/
├── [DDEV Development Files]
│   ├── .ddev/                 # DDEV configuration
│   ├── backend/               # Node.js backend
│   ├── *.html                 # HTML files
│   └── images/                # Assets
├── dist_cpanel/               # cPanel production files
│   ├── *.html                 # Static HTML files
│   ├── images/                # Assets
│   └── api/                   # PHP backend (if used)
└── sdc_webform_cpanel.zip     # Ready-to-upload package
```

## 🔄 Sync Workflow

### **Step 1: Development (DDEV)**
```bash
# Start development environment
ddev start-project

# Make changes to files
# Test locally at: https://sdc-webform.ddev.site
```

### **Step 2: Sync to Production (cPanel)**
```bash
# Run sync script
./sync-to-cpanel.sh

# This creates:
# - Updated dist_cpanel/ directory
# - New sdc_webform_cpanel.zip package
```

### **Step 3: Deploy to cPanel**
1. Log into GoDaddy cPanel
2. Go to File Manager
3. Navigate to `public_html/sdc_form/`
4. Upload `sdc_webform_cpanel.zip`
5. Extract the zip file
6. Test at: https://s6g.7bf.mywebsitetransfer.com/sdc_form/

### **Step 4: Version Control**
```bash
# Commit changes to GitHub
git add .
git commit -m "Description of changes"
git push origin main
```

## 🚀 Quick Commands

### **Full Deploy Workflow:**
```bash
# 1. Sync to cPanel
./sync-to-cpanel.sh

# 2. Commit to GitHub
git add .
git commit -m "Update: [description]"
git push origin main

# 3. Upload sdc_webform_cpanel.zip to cPanel
```

### **Development Only:**
```bash
# Start DDEV
ddev start-project

# Stop DDEV
ddev stop
```

## 📋 What Gets Synced

### **✅ Included in cPanel Package:**
- All HTML files (`*.html`)
- Images directory (`images/`)
- Documentation files (`*.md`)
- API directory (if using PHP backend)
- Configuration files

### **❌ Excluded from cPanel Package:**
- `.ddev/` (development only)
- `backend/` (Node.js backend)
- `.git/` (version control)
- `node_modules/` (dependencies)
- `*.db` (database files)
- Development scripts

## 🔧 Backend Options

### **Option 1: Node.js Backend (DDEV only)**
- Full-featured backend with SQLite
- Admin dashboard with full functionality
- Email notifications
- Rate limiting and validation

### **Option 2: PHP Backend (cPanel compatible)**
- Simple PHP API for form submission
- Email notifications
- Basic validation
- Works with GoDaddy hosting

## 🎯 Best Practices

1. **Always test in DDEV first** before syncing
2. **Run sync script** before any cPanel upload
3. **Commit changes** to GitHub after each update
4. **Keep documentation updated** in both versions
5. **Test production site** after each deployment

## 🚨 Troubleshooting

### **Sync Issues:**
```bash
# Re-run sync script
./sync-to-cpanel.sh

# Check file permissions
chmod +x sync-to-cpanel.sh
```

### **DDEV Issues:**
```bash
# Restart DDEV
ddev restart

# Check logs
ddev logs
```

### **cPanel Issues:**
- Check file permissions in cPanel
- Verify all files uploaded correctly
- Test individual pages for errors

## 📞 Support
- **DDEV Documentation**: https://ddev.readthedocs.io/
- **cPanel Documentation**: https://www.cpanel.net/support/
- **Project Repository**: https://github.com/vandy1690/sdc-webform-