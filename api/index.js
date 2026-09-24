const fs = require('fs');
const path = require('path');

module.exports = (req, res) => {
  const url = req.url || '/';
  const cleanPath = url.split('?')[0];
  const rootDir = path.join(__dirname, '..');

  // Determine requested file path
  let targetPath = path.join(rootDir, cleanPath);

  if (cleanPath === '/' || cleanPath === '') {
    targetPath = path.join(rootDir, 'index.php');
  } else if (cleanPath === '/admin' || cleanPath === '/admin/') {
    targetPath = path.join(rootDir, 'admin', 'index.php');
  }

  // Check if target file exists
  if (fs.existsSync(targetPath) && fs.statSync(targetPath).isFile()) {
    const ext = path.extname(targetPath).toLowerCase();
    const mimeTypes = {
      '.html': 'text/html; charset=utf-8',
      '.php': 'text/html; charset=utf-8',
      '.css': 'text/css; charset=utf-8',
      '.js': 'application/javascript; charset=utf-8',
      '.json': 'application/json; charset=utf-8',
      '.png': 'image/png',
      '.jpg': 'image/jpeg',
      '.jpeg': 'image/jpeg',
      '.webp': 'image/webp',
      '.svg': 'image/svg+xml',
      '.ico': 'image/x-icon'
    };

    const contentType = mimeTypes[ext] || 'text/html; charset=utf-8';
    res.setHeader('Content-Type', contentType);

    // If PHP file, strip raw PHP opening tags if serving raw, or send HTML content
    if (ext === '.php') {
      let content = fs.readFileSync(targetPath, 'utf8');
      // Render clean client HTML
      content = content.replace(/<\?php[\s\S]*?\?>/g, '');
      return res.status(200).send(content);
    }

    return res.status(200).send(fs.readFileSync(targetPath));
  }

  // Fallback to main homepage
  const mainIndex = path.join(rootDir, 'index.php');
  if (fs.existsSync(mainIndex)) {
    let content = fs.readFileSync(mainIndex, 'utf8');
    content = content.replace(/<\?php[\s\S]*?\?>/g, '');
    res.setHeader('Content-Type', 'text/html; charset=utf-8');
    return res.status(200).send(content);
  }

  res.status(404).send('404 Not Found');
};
