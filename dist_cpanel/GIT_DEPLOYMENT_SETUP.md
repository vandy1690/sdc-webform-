# Git Deployment Setup for cPanel

## 1. Create GitHub Repository

1. Go to [GitHub](https://github.com) and create a new repository:
   - Repository name: `sdc-webform-cpanel`
   - Description: `SDC Webform cPanel deployment package`
   - Set to **Public** (or Private if preferred)
   - Don't initialize with README (we already have files)

2. After creating the repository, copy the repository URL (e.g., `https://github.com/yourusername/sdc-webform-cpanel.git`)

## 2. Connect Local Repository to GitHub

Run these commands in the `dist_cpanel` directory:

```bash
# Add the GitHub repository as remote origin
git remote add origin https://github.com/YOURUSERNAME/sdc-webform-cpanel.git

# Push the initial commit to GitHub
git push -u origin main
```

## 3. cPanel Git Deployment Setup

### Option A: cPanel Git Version Control (Recommended)

1. **Log into cPanel**
2. **Find "Git Version Control"** in the Files section
3. **Create Repository:**
   - Click "Create"
   - Repository Path: `public_html/sdc_form` (or your preferred directory)
   - Clone URL: `https://github.com/YOURUSERNAME/sdc-webform-cpanel.git`
   - Repository Name: `sdc-webform-cpanel`

4. **Deploy the Repository:**
   - Click "Manage" next to your repository
   - Click "Pull or Deploy" tab
   - Click "Deploy HEAD Commit"

### Option B: Manual Git Setup (if Git Version Control not available)

1. **Access Terminal/SSH** in cPanel
2. **Navigate to web directory:**
   ```bash
   cd public_html
   mkdir sdc_form
   cd sdc_form
   ```

3. **Clone the repository:**
   ```bash
   git clone https://github.com/YOURUSERNAME/sdc-webform-cpanel.git .
   ```

## 4. Database Setup

After deploying files, complete the database setup:

1. **Create MySQL Database** in cPanel:
   - Database name: `yourusername_sdc_webform`
   - Create database user and assign privileges

2. **Run Installation Wizard:**
   - Visit: `https://yourdomain.com/sdc_form/install.php`
   - Enter database credentials
   - Complete setup

## 5. Ongoing Deployment Workflow

### Making Changes:

1. **Update files locally** in the `dist_cpanel` directory
2. **Commit changes:**
   ```bash
   git add .
   git commit -m "Description of changes"
   git push origin main
   ```

3. **Deploy to cPanel:**
   - **Option A:** Use cPanel Git Version Control "Pull or Deploy"
   - **Option B:** SSH and run `git pull origin main`

### Quick Commands:

```bash
# Check status
git status

# Add all changes
git add .

# Commit with message
git commit -m "Update form validation rules"

# Push to GitHub
git push origin main

# Pull latest changes (if working from multiple locations)
git pull origin main
```

## 6. Security Notes

- The `.gitignore` file excludes sensitive files like database backups
- Never commit database credentials to the repository
- Update `config.php` directly on the server after deployment
- Consider using environment-specific configuration files

## 7. Troubleshooting

### Common Issues:

1. **Permission Denied:** Ensure your cPanel account has Git access
2. **Database Connection:** Update `api/config.php` with correct credentials
3. **File Permissions:** Set appropriate permissions for PHP files (644) and directories (755)
4. **URL Rewriting:** Ensure `.htaccess` files are properly uploaded

### File Permissions:
```bash
find . -type f -name "*.php" -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod 644 .htaccess api/.htaccess
```

This setup provides a professional Git workflow for your cPanel deployment with easy updates and version control.