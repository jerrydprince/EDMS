const http = require('http');
const { exec } = require('child_process');
const fs = require('fs');
const path = require('path');
const os = require('os');

const PORT = 9880;
const NAPS2_PATH = '"C:\\Program Files\\NAPS2\\naps2.console.exe"';

let activeScanProcess = null;

const server = http.createServer((req, res) => {
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, PATCH, DELETE');
    res.setHeader('Access-Control-Allow-Headers', 'X-Requested-With,content-type');

    if (req.method === 'OPTIONS') {
        res.writeHead(200);
        res.end();
        return;
    }

    if (req.method === 'DELETE' && req.url.startsWith('/scan')) {
        if (activeScanProcess) {
            console.log(`[Proxy] Cancelling active scan process (PID: ${activeScanProcess.pid})...`);
            exec(`taskkill /PID ${activeScanProcess.pid} /T /F`, (err) => {
                if (err) console.error(`[Proxy] Failed to kill process: ${err.message}`);
            });
            activeScanProcess = null;
            res.writeHead(200);
            res.end("Scan cancelled successfully.");
        } else {
            res.writeHead(404);
            res.end("No active scan to cancel.");
        }
        return;
    }

    if (req.method === 'GET' && req.url.startsWith('/scan')) {
        if (activeScanProcess) {
            res.writeHead(429);
            res.end("A scan is already in progress.");
            return;
        }

        const urlParams = new URL(req.url, `http://${req.headers.host}`);
        let profile = urlParams.searchParams.get('profile'); 
        let type = urlParams.searchParams.get('type') || 'single'; // 'single' or 'multiple'
        let source = urlParams.searchParams.get('source'); // 'glass', 'feeder', or 'duplex'
        
        if (!profile) {
            const xmlPath = path.join(process.env.APPDATA || process.env.USERPROFILE + '\\AppData\\Roaming', 'NAPS2', 'profiles.xml');
            try {
                if (fs.existsSync(xmlPath)) {
                    const xml = fs.readFileSync(xmlPath, 'utf8');
                    const match = xml.match(/<DisplayName>(.*?)<\/DisplayName>/);
                    if (match && match[1]) {
                        profile = match[1];
                        console.log(`[Proxy] Dynamically extracted profile name '${profile}' from profiles.xml`);
                    }
                }
            } catch (err) {
                console.error(`[Proxy] Failed to read profiles.xml: ${err.message}`);
            }
        }
        
        console.log(`[Proxy] Starting native NAPS2 scan via CLI with profile: ${profile}, type: ${type}...`);
        
        const timestamp = Date.now();
        const tempDir = fs.mkdtempSync(path.join(os.tmpdir(), 'naps2-scan-'));
        
        let cmd = '';
        const sourceFlag = source ? ` --source ${source}` : '';
        if (type === 'multiple') {
            const tempPdfTemplate = path.join(tempDir, `Scanned_Document_${timestamp}_$(n).pdf`);
            cmd = profile ? `${NAPS2_PATH} -p "${profile}" -o "${tempPdfTemplate}" -f${sourceFlag}` : `${NAPS2_PATH} -o "${tempPdfTemplate}" -f${sourceFlag}`;
        } else {
            const tempPdf = path.join(tempDir, `Scanned_Document_${timestamp}.pdf`);
            cmd = profile ? `${NAPS2_PATH} -p "${profile}" -o "${tempPdf}" -f${sourceFlag}` : `${NAPS2_PATH} -o "${tempPdf}" -f${sourceFlag}`;
        }
        
        console.log(`[Proxy] Executing: ${cmd}`);
        
        activeScanProcess = exec(cmd, (error, stdout, stderr) => {
            activeScanProcess = null;
            
            if (error) {
                const fullError = stderr || stdout || error.message;
                console.error(`[Proxy] NAPS2 CLI Error/Cancelled: ${fullError}`);
                if (!res.writableEnded) {
                    res.writeHead(500);
                    res.end(`Scanner Error: ${fullError.substring(0, 1000)}`);
                }
                return;
            }
            
            const files = fs.readdirSync(tempDir);
            if (files.length === 0) {
                console.error(`[Proxy] No PDF found in ${tempDir}. User may have cancelled.`);
                if (!res.writableEnded) {
                    res.writeHead(204); 
                    res.end();
                }
                return;
            }
            
            console.log(`[Proxy] Scan successful! Returning results to browser...`);
            
            if (type === 'multiple') {
                const results = [];
                files.forEach((file) => {
                    const filePath = path.join(tempDir, file);
                    const fileData = fs.readFileSync(filePath);
                    results.push({
                        name: file,
                        type: 'application/pdf',
                        data: fileData.toString('base64')
                    });
                    fs.unlinkSync(filePath);
                });
                
                if (!res.writableEnded) {
                    res.writeHead(200, { 'Content-Type': 'application/json' });
                    res.end(JSON.stringify(results));
                }
            } else {
                const tempPdf = path.join(tempDir, files[0]);
                if (!res.writableEnded) {
                    res.writeHead(200, {
                        'Content-Type': 'application/pdf',
                        'Content-Length': fs.statSync(tempPdf).size,
                        'Content-Disposition': `attachment; filename="${files[0]}"`
                    });
                    
                    const readStream = fs.createReadStream(tempPdf);
                    readStream.pipe(res);
                    
                    readStream.on('end', () => {
                        fs.unlinkSync(tempPdf);
                        fs.rmdirSync(tempDir);
                    });
                }
            }
            
            try {
                if (fs.existsSync(tempDir)) fs.rmdirSync(tempDir);
            } catch(e) {}
        });
        return;
    }

    res.writeHead(404);
    res.end('Not Found');
});

server.listen(PORT, () => {
    console.log(`=======================================================`);
    console.log(`[Service] NAPS2 Native CLI Proxy is running!`);
    console.log(`[Service] Listening securely on: http://localhost:${PORT}`);
    console.log(`=======================================================`);
});
