const fs = require('fs');
let content = fs.readFileSync('resources/js/components/Modals/Workflows/AssignTask.vue', 'utf8');
const fixTemplateContent = fs.readFileSync('fix_template.js', 'utf8');
const templateMatch = fixTemplateContent.match(/const newTemplate = `([\s\S]*?)`;/);
if (templateMatch) {
  const newTemplate = templateMatch[1];
  const scriptIndex = content.indexOf('<script setup>');
  if (scriptIndex !== -1) {
    content = newTemplate + '\n' + content.substring(scriptIndex);
    fs.writeFileSync('resources/js/components/Modals/Workflows/AssignTask.vue', content);
    console.log('Fixed');
  }
}
