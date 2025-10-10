const fs = require('fs');
const path = require('path');

const distPath = path.join(__dirname, '../../../dist', 'vecsa-frontend');

function renameFiles(directory) {
  fs.readdir(directory, (err, files) => {
    if (err) {
      console.error('Error reading directory:', err);
      return;
    }

    files.forEach((file) => {
      if (file.startsWith('src_app_')) {
        const oldPath = path.join(directory, file);
        const newPath = path.join(directory, file.replace('src_app_', ''));

        // Rename files one to one
        fs.rename(oldPath, newPath, (err) => {
          if (err) {
            console.error(`Error rename file: ${ file }: `, err);
          } else {
            console.log(`Rename: ${ file } -> ${ path.basename(newPath) }`);
          }
        });
      }
    });
  });
}

// Call funtion to rename files into `dist/vecsa-frontend`
renameFiles(distPath);