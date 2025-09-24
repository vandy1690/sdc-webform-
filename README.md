# SDC Webform - DDEV Setup

This project contains a static HTML webform for bid requests, served using DDEV with a custom nginx configuration.

## Project Structure

```
sdc_webform/
├── .ddev/
│   ├── config.yaml              # DDEV configuration
│   └── commands/
│       ├── host/
│       │   └── start-project    # Custom start command with helpful output
│       └── web/
│           ├── setup            # Initial setup
│           ├── serve            # Serve files with nginx
│           ├── copy-files       # Copy files to web root
│           └── live-reload      # Enable live reload
├── index.html                   # Project homepage
├── sdc_bidrequest.html          # Main webform
└── README.md                    # This file
```

## Prerequisites

- [DDEV](https://ddev.readthedocs.io/en/stable/users/install/) installed on your system
- Docker Desktop running

## Getting Started

1. **Start the DDEV environment:**
   ```bash
   ddev start
   ```

2. **Access your site:**
- Main site: https://sdc-webform.ddev.site
- Webform: https://sdc-webform.ddev.site/sdc_bidrequest.html

## 🌐 Live Demo

- **Project Portal**: [https://s6g.7bf.mywebsitetransfer.com/sdc_form/](https://s6g.7bf.mywebsitetransfer.com/sdc_form/)
- **Bid Request Form**: [https://s6g.7bf.mywebsitetransfer.com/sdc_form/sdc_bidrequest.html](https://s6g.7bf.mywebsitetransfer.com/sdc_form/sdc_bidrequest.html)
- **Admin Dashboard**: [https://s6g.7bf.mywebsitetransfer.com/sdc_form/admin-dashboard.html](https://s6g.7bf.mywebsitetransfer.com/sdc_form/admin-dashboard.html)

3. **Custom DDEV commands:**
   ```bash
   # Start with helpful output
   ddev start-project
   
   # Initial setup (run once)
   ddev setup
   
   # Copy files to web root
   ddev copy-files
   
   # Enable live reload for development
   ddev live-reload
   ```

## Features

- **Static HTML serving** with nginx
- **DDEV development environment** with PHP 8.2
- **Project homepage** with navigation
- **Live reload** capability for development
- **Custom DDEV commands** for easy management
- **Mutagen file sync** for fast development

## Development

The webform uses:
- **Tailwind CSS** (via CDN) for styling
- **Alpine.js** for interactivity
- **Responsive design** for mobile and desktop
- **Form validation** and submission handling

## File Structure

- `index.html` - Project homepage with navigation
- `sdc_bidrequest.html` - Main webform with contact and project details
- `.ddev/config.yaml` - DDEV configuration for PHP project
- `.ddev/commands/` - Custom DDEV commands for development

## Troubleshooting

If you encounter issues:

1. **Restart DDEV:**
   ```bash
   ddev restart
   ```

2. **Check logs:**
   ```bash
   ddev logs
   ```

3. **Rebuild containers:**
   ```bash
   ddev poweroff
   ddev start
   ```

4. **Check nginx configuration:**
   ```bash
   ddev ssh
   nginx -t
   ```

## Stopping the Environment

To stop the DDEV environment:
```bash
ddev stop
```

To completely remove the environment:
```bash
ddev delete
```
