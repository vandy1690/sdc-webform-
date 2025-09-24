// Script to update SVG fill colors to use CSS variable
const fs = require('fs');

const files = [
    'standalone-form.html',
    'sdc_bidrequest.html', 
    'admin-dashboard.html',
    'index.html'
];

files.forEach(file => {
    let content = fs.readFileSync(file, 'utf8');
    
    // Replace all #CCFF00 with var(--highlight)
    content = content.replace(/#CCFF00/g, 'var(--highlight)');
    
    fs.writeFileSync(file, content);
    console.log(`Updated ${file}`);
});

console.log('All SVG colors updated to use CSS variable!');
