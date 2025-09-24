// Script to update form styling for dark theme
const fs = require('fs');

// Read the current file
let content = fs.readFileSync('standalone-form.html', 'utf8');

// Replace all form styling for dark theme
const replacements = [
    // Labels
    [/text-gray-700/g, 'text-gray-300'],
    
    // Input fields
    [/border-gray-300/g, 'border-gray-600'],
    [/bg-white/g, 'bg-gray-800'],
    [/text-gray-900/g, 'text-white'],
    [/placeholder-gray-500/g, 'placeholder-gray-400'],
    
    // Add text-white and placeholder-gray-400 to inputs
    [/class="w-full px-4 py-2 border border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"/g, 
     'class="w-full px-4 py-2 bg-gray-800 border border-gray-600 text-white rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors placeholder-gray-400"'],
    
    // Section headers
    [/text-gray-900/g, 'text-white'],
    
    // Borders
    [/border-gray-200/g, 'border-gray-700'],
    
    // Checkbox labels
    [/text-gray-700/g, 'text-gray-300'],
    
    // Required field text
    [/text-gray-500/g, 'text-gray-400']
];

// Apply replacements
replacements.forEach(([pattern, replacement]) => {
    content = content.replace(pattern, replacement);
});

// Write back to file
fs.writeFileSync('standalone-form.html', content);

console.log('Form styling updated for dark theme!');
