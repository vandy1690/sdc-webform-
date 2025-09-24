const express = require('express');
const sqlite3 = require('sqlite3').verbose();
const nodemailer = require('nodemailer');
const cors = require('cors');
const helmet = require('helmet');
const rateLimit = require('express-rate-limit');
const { body, validationResult } = require('express-validator');
const path = require('path');
require('dotenv').config();

const app = express();
const PORT = process.env.PORT || 3000;

// Security middleware
app.use(helmet());
app.use(cors({
    origin: ['https://sdc-webform.ddev.site', 'http://localhost:3000'],
    credentials: true
}));

// Rate limiting
const limiter = rateLimit({
    windowMs: 15 * 60 * 1000, // 15 minutes
    max: 5, // limit each IP to 5 requests per windowMs
    message: 'Too many requests from this IP, please try again later.',
    standardHeaders: true,
    legacyHeaders: false,
});
app.use('/api/', limiter);

// Body parsing middleware
app.use(express.json({ limit: '10mb' }));
app.use(express.urlencoded({ extended: true, limit: '10mb' }));

// Serve static files
app.use(express.static(path.join(__dirname, '../')));

// Database setup
const dbPath = process.env.DB_PATH || './database/bid_requests.db';
const db = new sqlite3.Database(dbPath);

// Create database table
db.serialize(() => {
    db.run(`CREATE TABLE IF NOT EXISTS bid_requests (
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
    )`);
});

// Email transporter setup
const createTransporter = () => {
    return nodemailer.createTransporter({
        host: process.env.EMAIL_HOST || 'smtp.gmail.com',
        port: process.env.EMAIL_PORT || 587,
        secure: process.env.EMAIL_SECURE === 'true',
        auth: {
            user: process.env.EMAIL_USER,
            pass: process.env.EMAIL_PASS
        }
    });
};

// Email templates
const getClientEmailTemplate = (data) => {
    return `
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>Bid Request Confirmation</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #2563eb; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9fafb; }
            .footer { padding: 20px; text-align: center; color: #666; font-size: 14px; }
            .highlight { background: #dbeafe; padding: 15px; border-radius: 5px; margin: 15px 0; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>Thank You for Your Bid Request!</h1>
            </div>
            <div class="content">
                <p>Dear ${data.first_name} ${data.last_name},</p>
                <p>Thank you for reaching out to SDC Creative Studio! We've received your bid request and are excited to learn more about your project.</p>
                
                <div class="highlight">
                    <h3>Project Details:</h3>
                    <p><strong>Project:</strong> ${data.project_title}</p>
                    <p><strong>Type:</strong> ${data.project_type.replace('-', ' ').toUpperCase()}</p>
                    <p><strong>Budget Range:</strong> ${data.budget.replace('-', ' - ').toUpperCase()}</p>
                    <p><strong>Timeline:</strong> ${data.timeline.replace('-', ' ').toUpperCase()}</p>
                </div>
                
                <p>Our team will review your project details and get back to you within 24 hours with a detailed quote and next steps.</p>
                
                <p>If you have any questions in the meantime, please don't hesitate to reach out to us.</p>
                
                <p>Best regards,<br>
                The SDC Creative Studio Team</p>
            </div>
            <div class="footer">
                <p>This email was sent in response to your bid request submitted on ${new Date().toLocaleDateString()}.</p>
            </div>
        </div>
    </body>
    </html>
    `;
};

const getAdminEmailTemplate = (data) => {
    return `
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>New Bid Request - ${data.project_title}</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #dc2626; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9fafb; }
            .section { margin: 20px 0; padding: 15px; background: white; border-radius: 5px; }
            .highlight { background: #fef3c7; padding: 10px; border-radius: 5px; margin: 10px 0; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>New Bid Request Received!</h1>
            </div>
            <div class="content">
                <div class="section">
                    <h3>Contact Information</h3>
                    <p><strong>Name:</strong> ${data.first_name} ${data.last_name}</p>
                    <p><strong>Email:</strong> ${data.email}</p>
                    <p><strong>Phone:</strong> ${data.phone || 'Not provided'}</p>
                    <p><strong>Company:</strong> ${data.company || 'Not provided'}</p>
                </div>
                
                <div class="section">
                    <h3>Project Details</h3>
                    <p><strong>Project Title:</strong> ${data.project_title}</p>
                    <p><strong>Project Type:</strong> ${data.project_type.replace('-', ' ').toUpperCase()}</p>
                    <p><strong>Budget Range:</strong> ${data.budget.replace('-', ' - ').toUpperCase()}</p>
                    <p><strong>Timeline:</strong> ${data.timeline.replace('-', ' ').toUpperCase()}</p>
                    <p><strong>Services Needed:</strong> ${data.services ? data.services.join(', ') : 'None specified'}</p>
                    <p><strong>How they heard about us:</strong> ${data.referral || 'Not specified'}</p>
                </div>
                
                <div class="section">
                    <h3>Project Description</h3>
                    <p>${data.description}</p>
                </div>
                
                <div class="highlight">
                    <p><strong>Action Required:</strong> Please review this bid request and respond within 24 hours.</p>
                    <p><strong>Bid ID:</strong> #${data.id}</p>
                </div>
            </div>
        </div>
    </body>
    </html>
    `;
};

// Validation rules
const bidRequestValidation = [
    body('firstName').trim().isLength({ min: 1 }).withMessage('First name is required'),
    body('lastName').trim().isLength({ min: 1 }).withMessage('Last name is required'),
    body('email').isEmail().normalizeEmail().withMessage('Valid email is required'),
    body('phone').optional().isMobilePhone().withMessage('Valid phone number required'),
    body('projectType').isIn(['brand-identity', 'web-design', 'print-design', 'digital-marketing', 'ui-ux', 'packaging', 'other']).withMessage('Valid project type required'),
    body('projectTitle').trim().isLength({ min: 1 }).withMessage('Project title is required'),
    body('description').trim().isLength({ min: 10 }).withMessage('Description must be at least 10 characters'),
    body('budget').isIn(['under-5k', '5k-10k', '10k-25k', '25k-50k', '50k-100k', 'over-100k']).withMessage('Valid budget range required'),
    body('timeline').isIn(['asap', '1-month', '2-3-months', '3-6-months', '6-months-plus']).withMessage('Valid timeline required'),
    body('services').optional().isArray().withMessage('Services must be an array'),
    body('referral').optional().isIn(['search', 'social', 'referral', 'portfolio', 'other']).withMessage('Valid referral source required')
];

// API Routes

// Submit bid request
app.post('/api/bid-request', bidRequestValidation, async (req, res) => {
    try {
        // Check validation errors
        const errors = validationResult(req);
        if (!errors.isEmpty()) {
            return res.status(400).json({
                success: false,
                message: 'Validation failed',
                errors: errors.array()
            });
        }

        const {
            firstName, lastName, email, phone, company,
            projectType, projectTitle, description, budget, timeline,
            services, referral
        } = req.body;

        // Insert into database
        const sql = `INSERT INTO bid_requests 
            (first_name, last_name, email, phone, company, project_type, 
             project_title, description, budget, timeline, services, referral)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`;

        const servicesJson = JSON.stringify(services || []);

        db.run(sql, [
            firstName, lastName, email, phone, company,
            projectType, projectTitle, description, budget, timeline,
            servicesJson, referral
        ], function(err) {
            if (err) {
                console.error('Database error:', err);
                return res.status(500).json({
                    success: false,
                    message: 'Failed to save bid request'
                });
            }

            const bidId = this.lastID;
            const bidData = {
                id: bidId,
                firstName, lastName, email, phone, company,
                projectType, projectTitle, description, budget, timeline,
                services: services || [], referral
            };

            // Send emails
            sendEmails(bidData);

            res.json({
                success: true,
                message: 'Bid request submitted successfully',
                bidId: bidId
            });
        });

    } catch (error) {
        console.error('Server error:', error);
        res.status(500).json({
            success: false,
            message: 'Internal server error'
        });
    }
});

// Get all bid requests (for admin dashboard)
app.get('/api/bid-requests', (req, res) => {
    const sql = `SELECT * FROM bid_requests ORDER BY created_at DESC`;
    
    db.all(sql, [], (err, rows) => {
        if (err) {
            console.error('Database error:', err);
            return res.status(500).json({
                success: false,
                message: 'Failed to fetch bid requests'
            });
        }

        // Parse services JSON
        const processedRows = rows.map(row => ({
            ...row,
            services: row.services ? JSON.parse(row.services) : []
        }));

        res.json({
            success: true,
            data: processedRows
        });
    });
});

// Get single bid request
app.get('/api/bid-request/:id', (req, res) => {
    const { id } = req.params;
    const sql = `SELECT * FROM bid_requests WHERE id = ?`;
    
    db.get(sql, [id], (err, row) => {
        if (err) {
            console.error('Database error:', err);
            return res.status(500).json({
                success: false,
                message: 'Failed to fetch bid request'
            });
        }

        if (!row) {
            return res.status(404).json({
                success: false,
                message: 'Bid request not found'
            });
        }

        // Parse services JSON
        row.services = row.services ? JSON.parse(row.services) : [];

        res.json({
            success: true,
            data: row
        });
    });
});

// Update bid request status
app.put('/api/bid-request/:id/status', (req, res) => {
    const { id } = req.params;
    const { status } = req.body;

    const validStatuses = ['new', 'reviewing', 'quoted', 'accepted', 'rejected'];
    if (!validStatuses.includes(status)) {
        return res.status(400).json({
            success: false,
            message: 'Invalid status'
        });
    }

    const sql = `UPDATE bid_requests SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?`;
    
    db.run(sql, [status, id], function(err) {
        if (err) {
            console.error('Database error:', err);
            return res.status(500).json({
                success: false,
                message: 'Failed to update status'
            });
        }

        if (this.changes === 0) {
            return res.status(404).json({
                success: false,
                message: 'Bid request not found'
            });
        }

        res.json({
            success: true,
            message: 'Status updated successfully'
        });
    });
});

// Get statistics
app.get('/api/statistics', (req, res) => {
    const sql = `SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_count,
        SUM(CASE WHEN status = 'reviewing' THEN 1 ELSE 0 END) as reviewing_count,
        SUM(CASE WHEN status = 'quoted' THEN 1 ELSE 0 END) as quoted_count,
        SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as accepted_count,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_count
        FROM bid_requests`;

    db.get(sql, [], (err, row) => {
        if (err) {
            console.error('Database error:', err);
            return res.status(500).json({
                success: false,
                message: 'Failed to fetch statistics'
            });
        }

        res.json({
            success: true,
            data: row
        });
    });
});

// Email sending function
async function sendEmails(bidData) {
    const transporter = createTransporter();

    if (!transporter) {
        console.log('Email not configured, skipping email notifications');
        return;
    }

    try {
        // Send confirmation email to client
        await transporter.sendMail({
            from: `"${process.env.ADMIN_NAME || 'SDC Creative Studio'}" <${process.env.EMAIL_USER}>`,
            to: bidData.email,
            subject: 'Thank you for your bid request - SDC Creative Studio',
            html: getClientEmailTemplate(bidData)
        });

        // Send notification email to admin
        await transporter.sendMail({
            from: `"${process.env.ADMIN_NAME || 'SDC Creative Studio'}" <${process.env.EMAIL_USER}>`,
            to: process.env.ADMIN_EMAIL,
            subject: `New Bid Request: ${bidData.projectTitle}`,
            html: getAdminEmailTemplate(bidData)
        });

        console.log('Emails sent successfully');
    } catch (error) {
        console.error('Email sending failed:', error);
    }
}

// Health check endpoint
app.get('/api/health', (req, res) => {
    res.json({
        success: true,
        message: 'Server is running',
        timestamp: new Date().toISOString()
    });
});

// Error handling middleware
app.use((err, req, res, next) => {
    console.error('Unhandled error:', err);
    res.status(500).json({
        success: false,
        message: 'Internal server error'
    });
});

// 404 handler
app.use((req, res) => {
    res.status(404).json({
        success: false,
        message: 'Endpoint not found'
    });
});

// Start server
app.listen(PORT, '0.0.0.0', () => {
    console.log(`🚀 Server running on port ${PORT}`);
    console.log(`📧 Email configured: ${process.env.EMAIL_USER ? 'Yes' : 'No'}`);
    console.log(`🗄️  Database: ${dbPath}`);
    console.log(`🌐 Access your form at: http://localhost:${PORT}/sdc_bidrequest.html`);
    console.log(`📊 Admin dashboard: http://localhost:${PORT}/admin-dashboard.html`);
});

// Graceful shutdown
process.on('SIGINT', () => {
    console.log('\n🛑 Shutting down server...');
    db.close((err) => {
        if (err) {
            console.error('Error closing database:', err);
        } else {
            console.log('✅ Database connection closed');
        }
        process.exit(0);
    });
});
