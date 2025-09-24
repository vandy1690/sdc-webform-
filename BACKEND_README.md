# SDC Webform - Complete Backend Integration

This project now includes a complete backend system with API endpoints, database storage, and email notifications for your bid request form.

## 🚀 Quick Start

### 1. Setup Backend
```bash
# Run the setup script
./setup.sh

# Or manually:
cd backend
npm install
cp config.env .env
mkdir -p database
```

### 2. Configure Email (Required)
Edit `backend/.env` with your email settings:
```env
EMAIL_USER=your-email@gmail.com
EMAIL_PASS=your-app-specific-password
ADMIN_EMAIL=admin@yourcompany.com
ADMIN_NAME=SDC Creative Studio
```

### 3. Start the Backend Server
```bash
cd backend
npm start
```

### 4. Access Your Application
- **Form**: http://localhost:3000/sdc_bidrequest.html
- **Admin Dashboard**: http://localhost:3000/admin-dashboard.html
- **DDEV Site**: https://sdc-webform.ddev.site (still works)

## 📋 What's Included

### Backend API (`backend/server.js`)
- **RESTful API** with Express.js
- **SQLite database** for data persistence
- **Email notifications** for clients and admins
- **Rate limiting** (5 requests per 15 minutes per IP)
- **Input validation** and security
- **CORS enabled** for cross-origin requests

### Database Schema
```sql
CREATE TABLE bid_requests (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    first_name TEXT NOT NULL,
    last_name TEXT NOT NULL,
    email TEXT NOT NULL,
    phone TEXT,
    company TEXT,
    project_type TEXT NOT NULL,
    project_title TEXT NOT NULL,
    description TEXT NOT NULL,
    budget TEXT NOT NULL,
    timeline TEXT NOT NULL,
    services TEXT,
    referral TEXT,
    status TEXT DEFAULT 'new',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

### API Endpoints

#### Submit Bid Request
```http
POST /api/bid-request
Content-Type: application/json

{
  "firstName": "John",
  "lastName": "Doe",
  "email": "john@example.com",
  "phone": "+1234567890",
  "company": "Acme Corp",
  "projectType": "web-design",
  "projectTitle": "Company Website",
  "description": "Need a modern website...",
  "budget": "10k-25k",
  "timeline": "2-3-months",
  "services": ["design", "development"],
  "referral": "search"
}
```

#### Get All Bid Requests
```http
GET /api/bid-requests
```

#### Get Single Bid Request
```http
GET /api/bid-request/:id
```

#### Update Bid Status
```http
PUT /api/bid-request/:id/status
Content-Type: application/json

{
  "status": "reviewing"
}
```

#### Get Statistics
```http
GET /api/statistics
```

### Email System
- **Client Confirmation**: Professional email with project details
- **Admin Notification**: Detailed email with all submission data
- **HTML Templates**: Beautiful, responsive email designs
- **Multiple Providers**: Gmail, SendGrid, Mailgun, custom SMTP

### Admin Dashboard Features
- **Statistics Overview**: Total, new, reviewing, quoted, rejected counts
- **Bid Management**: View, filter, search all submissions
- **Status Updates**: Change bid status with dropdown
- **Detailed View**: Modal with complete bid information
- **Real-time Data**: Auto-refresh capabilities

## 🔧 Configuration

### Email Setup (Gmail)
1. Go to [Google Account Security](https://myaccount.google.com/security)
2. Enable 2-Factor Authentication
3. Generate an App-Specific Password
4. Update `EMAIL_USER` and `EMAIL_PASS` in `backend/.env`

### Environment Variables
```env
# Email Configuration
EMAIL_USER=your-email@gmail.com
EMAIL_PASS=your-app-specific-password
EMAIL_HOST=smtp.gmail.com
EMAIL_PORT=587
EMAIL_SECURE=false

# Admin Configuration
ADMIN_EMAIL=admin@yourcompany.com
ADMIN_NAME=SDC Creative Studio

# Server Configuration
PORT=3000
NODE_ENV=development

# Database Configuration
DB_PATH=./database/bid_requests.db
```

## 🛡️ Security Features

- **Rate Limiting**: 5 requests per 15 minutes per IP
- **Input Validation**: Server-side validation on all fields
- **XSS Protection**: Helmet.js security headers
- **SQL Injection Prevention**: Parameterized queries
- **CORS Configuration**: Restricted to allowed origins

## 📊 Status Workflow

1. **New**: Initial submission
2. **Reviewing**: Under consideration
3. **Quoted**: Quote sent to client
4. **Accepted**: Client accepted quote
5. **Rejected**: Not proceeding

## 🚀 Production Deployment

### Heroku
```bash
# Add to package.json
"engines": {
  "node": "18.x"
}

# Deploy
git add .
git commit -m "Add backend"
git push heroku main
```

### DigitalOcean App Platform
1. Connect GitHub repository
2. Set environment variables
3. Deploy automatically

### AWS/Docker
```dockerfile
FROM node:18-alpine
WORKDIR /app
COPY backend/package*.json ./
RUN npm install --production
COPY backend/ .
EXPOSE 3000
CMD ["npm", "start"]
```

## 🔄 DDEV Integration

The backend runs alongside your DDEV environment:
- **DDEV**: Serves static files at https://sdc-webform.ddev.site
- **Backend**: API server at http://localhost:3000
- **Integration**: Form submits to backend API

## 📈 Monitoring & Maintenance

### Database Backup
```bash
# Backup SQLite database
cp backend/database/bid_requests.db backup_$(date +%Y%m%d).db
```

### Logs
```bash
# View server logs
cd backend
npm start 2>&1 | tee server.log
```

### Health Check
```bash
curl http://localhost:3000/api/health
```

## 🐛 Troubleshooting

### Common Issues

1. **Email not sending**
   - Check email credentials in `.env`
   - Verify app-specific password for Gmail
   - Check firewall/port 587 access

2. **Database errors**
   - Ensure `database/` directory exists
   - Check file permissions
   - Verify SQLite installation

3. **CORS errors**
   - Check allowed origins in server.js
   - Verify frontend URL matches

4. **Rate limiting**
   - Wait 15 minutes or restart server
   - Check IP address in logs

### Debug Mode
```bash
# Enable debug logging
DEBUG=* npm start
```

## 📞 Support

For issues or questions:
1. Check the logs: `cd backend && npm start`
2. Verify configuration in `.env`
3. Test API endpoints with curl
4. Check browser console for frontend errors

## 🎯 Next Steps

1. **Authentication**: Add login to admin dashboard
2. **File Uploads**: Allow project briefs/attachments
3. **Webhooks**: Notify Slack/Discord on new submissions
4. **Payment Integration**: Stripe for deposits
5. **Analytics**: Track conversion rates
6. **Email Templates**: Customize email designs

---

**Your bid request system is now fully functional!** 🎉

Clients can submit forms, you'll receive email notifications, and you can manage everything through the admin dashboard. The system is production-ready with proper security, validation, and error handling.
