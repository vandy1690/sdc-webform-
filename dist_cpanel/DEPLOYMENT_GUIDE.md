# Deployment Guide for StevenDesignco.com Integration

This guide will help you deploy your bid request system and integrate it with your Figma Sites website.

## 🚀 **Step 1: Deploy Your Backend**

### Option A: Heroku (Recommended)
```bash
# Install Heroku CLI
# Visit: https://devcenter.heroku.com/articles/heroku-cli

# Login to Heroku
heroku login

# Create new app
heroku create steven-design-co-bid-api

# Set environment variables
heroku config:set EMAIL_USER=your-email@gmail.com
heroku config:set EMAIL_PASS=your-app-password
heroku config:set ADMIN_EMAIL=admin@stevendesignco.com
heroku config:set ADMIN_NAME="Steven Design Co"
heroku config:set NODE_ENV=production

# Deploy
cd backend
git init
git add .
git commit -m "Initial commit"
git push heroku main

# Open your app
heroku open
```

### Option B: Vercel
```bash
# Install Vercel CLI
npm i -g vercel

# Deploy
cd backend
vercel

# Set environment variables in Vercel dashboard
```

### Option C: Netlify Functions
```bash
# Install Netlify CLI
npm i -g netlify-cli

# Deploy
cd backend
netlify deploy --prod
```

## 🎨 **Step 2: Integrate with Figma Sites**

### Method 1: iframe Embed (Easiest)
1. **In your Figma Sites page:**
   - Add a custom HTML element
   - Insert this code:

```html
<iframe 
  src="https://your-deployed-backend.herokuapp.com/standalone-form.html" 
  width="100%" 
  height="800px" 
  frameborder="0"
  style="border: none; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
</iframe>
```

### Method 2: Custom Code Integration
1. **Copy the form HTML** from `standalone-form.html`
2. **Update the API URL** in the JavaScript:
   ```javascript
   const API_URL = 'https://your-deployed-backend.herokuapp.com/api/bid-request';
   ```
3. **Add to your Figma Sites page** as custom HTML

### Method 3: External Link
1. **Create a dedicated page** on your site
2. **Link to the form** with a button:
   ```html
   <a href="https://your-deployed-backend.herokuapp.com/standalone-form.html" 
      target="_blank" 
      class="btn btn-primary">
      Request a Quote
   </a>
   ```

## 🔧 **Step 3: Configure Your Domain**

### Custom Domain (Optional)
```bash
# Add custom domain to Heroku
heroku domains:add api.stevendesignco.com

# Update DNS records
# CNAME api.stevendesignco.com -> your-app.herokuapp.com
```

### Update Form URLs
Update the `API_URL` in your form to use your custom domain:
```javascript
const API_URL = 'https://api.stevendesignco.com/api/bid-request';
```

## 📧 **Step 4: Email Configuration**

### Gmail Setup
1. **Enable 2-Factor Authentication**
2. **Generate App Password:**
   - Go to Google Account Security
   - App passwords → Generate
   - Use this password in your environment variables

### Email Templates
The system includes professional email templates:
- **Client confirmation** with project details
- **Admin notification** with full submission data

## 🎯 **Step 5: Test Your Integration**

### Test Checklist
- [ ] Form loads on your Figma Sites page
- [ ] Form submission works
- [ ] Client receives confirmation email
- [ ] Admin receives notification email
- [ ] Data appears in admin dashboard
- [ ] Mobile responsiveness works

### Test URLs
- **Form**: `https://your-backend.herokuapp.com/standalone-form.html`
- **Admin**: `https://your-backend.herokuapp.com/admin-dashboard.html`
- **API Health**: `https://your-backend.herokuapp.com/api/health`

## 🛡️ **Step 6: Security & Performance**

### Security Headers
The backend includes:
- Rate limiting (5 requests per 15 minutes)
- CORS protection
- Input validation
- XSS protection

### Performance
- SQLite database (fast for small-medium sites)
- Gzip compression
- Static file serving
- CDN-ready

## 📊 **Step 7: Monitoring**

### Heroku Monitoring
```bash
# View logs
heroku logs --tail

# Check app status
heroku ps

# View metrics
heroku addons:create newrelic:wayne
```

### Admin Dashboard
Access your admin dashboard to:
- View all bid requests
- Update status
- Export data
- Monitor statistics

## 🔄 **Step 8: Maintenance**

### Regular Tasks
- **Monitor emails** for delivery issues
- **Check database** for storage limits
- **Update dependencies** monthly
- **Backup database** weekly

### Database Backup
```bash
# Download database
heroku pg:backups:capture
heroku pg:backups:download
```

## 🎨 **Step 9: Customization**

### Branding
Update the form to match your brand:
- Colors in Tailwind classes
- Logo in header
- Company name in emails
- Custom styling

### Additional Features
- File uploads for project briefs
- Payment integration
- CRM integration
- Analytics tracking

## 🚨 **Troubleshooting**

### Common Issues

1. **Form not submitting**
   - Check API URL is correct
   - Verify CORS settings
   - Check browser console for errors

2. **Emails not sending**
   - Verify email credentials
   - Check spam folder
   - Test with different email provider

3. **Database errors**
   - Check Heroku logs
   - Verify database is accessible
   - Check environment variables

### Support
- Check Heroku logs: `heroku logs --tail`
- Test API: `curl https://your-app.herokuapp.com/api/health`
- Verify environment: `heroku config`

## 🎉 **You're Ready!**

Your bid request system is now integrated with your Figma Sites website. Clients can submit requests directly from your site, and you'll receive professional email notifications with all the details.

**Next Steps:**
1. Deploy your backend
2. Update the API URL in the form
3. Add the form to your Figma Sites page
4. Test the complete flow
5. Monitor and maintain

Your professional bid request system is now live! 🚀
