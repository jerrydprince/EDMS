const fs = require('fs');
const path = require('path');

function getAllVueFiles(dir, fileList = []) {
  const files = fs.readdirSync(dir);
  files.forEach(file => {
    const filePath = path.join(dir, file);
    if (fs.statSync(filePath).isDirectory()) {
      getAllVueFiles(filePath, fileList);
    } else if (file.endsWith('.vue')) {
      fileList.push(filePath);
    }
  });
  return fileList;
}

const vueFiles = getAllVueFiles('./resources/js');
let fixedCount = 0;

vueFiles.forEach(file => {
  let content = fs.readFileSync(file, 'utf8');
  const original = content;
  
  // Fix unterminated :src attributes
  content = content.replace(/:src="'([^']+)"/g, ':src="\'$1\'"');
  
  if (content !== original) {
    fs.writeFileSync(file, content, 'utf8');
    console.log(`Fixed: ${file}`);
    fixedCount++;
  }
});

console.log(`\nTotal files fixed: ${fixedCount}`);
