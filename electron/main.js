const { app, BrowserWindow, Menu, dialog, shell, Tray, nativeImage } = require('electron');
const path = require('path');
const { spawn, exec } = require('child_process');
const http = require('http');
const fs = require('fs');

// Keep references to prevent garbage collection
let mainWindow = null;
let splashWindow = null;
let phpProcess = null;
let tray = null;

// Configuration
const CONFIG = {
    port: 8080,
    host: 'localhost',
    isDev: !app.isPackaged,
    phpPath: null,
    appPath: null
};

// Determine paths based on environment
function initializePaths() {
    if (CONFIG.isDev) {
        // Development mode - use parent directory
        CONFIG.appPath = path.join(__dirname, '..');
        CONFIG.phpPath = 'php'; // Use system PHP
    } else {
        // Production mode - use bundled resources
        CONFIG.appPath = path.join(process.resourcesPath, 'app');
        CONFIG.phpPath = path.join(process.resourcesPath, 'php', 'php.exe');
        
        // Fallback to system PHP if bundled not found
        if (!fs.existsSync(CONFIG.phpPath)) {
            CONFIG.phpPath = 'php';
        }
    }
    console.log('App Path:', CONFIG.appPath);
    console.log('PHP Path:', CONFIG.phpPath);
}

// Create splash screen
function createSplashWindow() {
    splashWindow = new BrowserWindow({
        width: 400,
        height: 300,
        frame: false,
        transparent: true,
        alwaysOnTop: true,
        webPreferences: {
            nodeIntegration: true,
            contextIsolation: false
        }
    });

    splashWindow.loadFile(path.join(__dirname, 'splash.html'));
    splashWindow.center();
}

// Create main window
function createMainWindow() {
    mainWindow = new BrowserWindow({
        width: 1400,
        height: 900,
        minWidth: 1024,
        minHeight: 768,
        show: false,
        icon: path.join(__dirname, 'assets', 'icon.png'),
        webPreferences: {
            nodeIntegration: false,
            contextIsolation: true,
            webSecurity: true
        },
        titleBarStyle: 'default',
        autoHideMenuBar: false
    });

    // Create custom menu
    createMenu();

    // Load app when ready
    mainWindow.once('ready-to-show', () => {
        if (splashWindow) {
            splashWindow.destroy();
            splashWindow = null;
        }
        mainWindow.show();
        mainWindow.focus();
    });

    // Handle external links
    mainWindow.webContents.setWindowOpenHandler(({ url }) => {
        shell.openExternal(url);
        return { action: 'deny' };
    });

    // Handle window close
    mainWindow.on('close', (event) => {
        if (process.platform === 'darwin') {
            event.preventDefault();
            mainWindow.hide();
        }
    });

    mainWindow.on('closed', () => {
        mainWindow = null;
    });
}

// Create application menu
function createMenu() {
    const template = [
        {
            label: 'File',
            submenu: [
                {
                    label: 'Refresh',
                    accelerator: 'CmdOrCtrl+R',
                    click: () => mainWindow.reload()
                },
                { type: 'separator' },
                {
                    label: 'Exit',
                    accelerator: 'CmdOrCtrl+Q',
                    click: () => app.quit()
                }
            ]
        },
        {
            label: 'View',
            submenu: [
                {
                    label: 'Toggle Full Screen',
                    accelerator: 'F11',
                    click: () => mainWindow.setFullScreen(!mainWindow.isFullScreen())
                },
                { type: 'separator' },
                {
                    label: 'Zoom In',
                    accelerator: 'CmdOrCtrl+Plus',
                    click: () => {
                        const zoom = mainWindow.webContents.getZoomLevel();
                        mainWindow.webContents.setZoomLevel(zoom + 0.5);
                    }
                },
                {
                    label: 'Zoom Out',
                    accelerator: 'CmdOrCtrl+-',
                    click: () => {
                        const zoom = mainWindow.webContents.getZoomLevel();
                        mainWindow.webContents.setZoomLevel(zoom - 0.5);
                    }
                },
                {
                    label: 'Reset Zoom',
                    accelerator: 'CmdOrCtrl+0',
                    click: () => mainWindow.webContents.setZoomLevel(0)
                }
            ]
        },
        {
            label: 'Tools',
            submenu: [
                {
                    label: 'Developer Tools',
                    accelerator: 'F12',
                    click: () => mainWindow.webContents.toggleDevTools()
                },
                { type: 'separator' },
                {
                    label: 'Open App Folder',
                    click: () => shell.openPath(CONFIG.appPath)
                }
            ]
        },
        {
            label: 'Help',
            submenu: [
                {
                    label: 'About',
                    click: () => {
                        dialog.showMessageBox(mainWindow, {
                            type: 'info',
                            title: 'About Log App',
                            message: 'Log App v1.0.0',
                            detail: 'A desktop application for log management.\n\nBuilt with Electron + PHP'
                        });
                    }
                }
            ]
        }
    ];

    const menu = Menu.buildFromTemplate(template);
    Menu.setApplicationMenu(menu);
}

// Start PHP server
function startPHPServer() {
    return new Promise((resolve, reject) => {
        console.log('Starting PHP server...');
        console.log('Working directory:', CONFIG.appPath);
        
        // Check if PHP is available
        exec(`"${CONFIG.phpPath}" -v`, (error) => {
            if (error) {
                reject(new Error('PHP is not installed or not in PATH. Please install PHP first.'));
                return;
            }

            // Start built-in PHP server
            phpProcess = spawn(CONFIG.phpPath, [
                '-S', `${CONFIG.host}:${CONFIG.port}`,
                '-t', CONFIG.appPath
            ], {
                cwd: CONFIG.appPath,
                shell: true,
                windowsHide: true
            });

            phpProcess.stdout.on('data', (data) => {
                console.log(`PHP: ${data}`);
            });

            phpProcess.stderr.on('data', (data) => {
                console.log(`PHP: ${data}`);
            });

            phpProcess.on('error', (err) => {
                console.error('Failed to start PHP server:', err);
                reject(err);
            });

            phpProcess.on('close', (code) => {
                console.log(`PHP server exited with code ${code}`);
            });

            // Wait for server to be ready
            waitForServer(resolve, reject);
        });
    });
}

// Wait for PHP server to be ready
function waitForServer(resolve, reject, retries = 30) {
    const checkServer = () => {
        http.get(`http://${CONFIG.host}:${CONFIG.port}/`, (res) => {
            console.log('PHP server is ready!');
            resolve();
        }).on('error', (err) => {
            if (retries > 0) {
                setTimeout(() => waitForServer(resolve, reject, retries - 1), 500);
            } else {
                reject(new Error('PHP server failed to start'));
            }
        });
    };
    checkServer();
}

// Stop PHP server
function stopPHPServer() {
    if (phpProcess) {
        console.log('Stopping PHP server...');
        if (process.platform === 'win32') {
            exec(`taskkill /pid ${phpProcess.pid} /f /t`);
        } else {
            phpProcess.kill('SIGTERM');
        }
        phpProcess = null;
    }
}

// Create system tray
function createTray() {
    const iconPath = path.join(__dirname, 'assets', 'icon.png');
    if (fs.existsSync(iconPath)) {
        const icon = nativeImage.createFromPath(iconPath);
        tray = new Tray(icon.resize({ width: 16, height: 16 }));
        
        const contextMenu = Menu.buildFromTemplate([
            { label: 'Show App', click: () => mainWindow.show() },
            { type: 'separator' },
            { label: 'Quit', click: () => app.quit() }
        ]);
        
        tray.setToolTip('Log App');
        tray.setContextMenu(contextMenu);
        
        tray.on('double-click', () => {
            mainWindow.show();
        });
    }
}

// App initialization
app.whenReady().then(async () => {
    initializePaths();
    createSplashWindow();
    createMainWindow();

    try {
        await startPHPServer();
        mainWindow.loadURL(`http://${CONFIG.host}:${CONFIG.port}/`);
        createTray();
    } catch (error) {
        if (splashWindow) splashWindow.destroy();
        
        dialog.showErrorBox('Startup Error', error.message);
        app.quit();
    }
});

// Prevent multiple instances
const gotTheLock = app.requestSingleInstanceLock();
if (!gotTheLock) {
    app.quit();
} else {
    app.on('second-instance', () => {
        if (mainWindow) {
            if (mainWindow.isMinimized()) mainWindow.restore();
            mainWindow.focus();
        }
    });
}

// Handle app activation (macOS)
app.on('activate', () => {
    if (mainWindow === null) {
        createMainWindow();
    } else {
        mainWindow.show();
    }
});

// Cleanup on quit
app.on('before-quit', () => {
    stopPHPServer();
});

app.on('window-all-closed', () => {
    if (process.platform !== 'darwin') {
        stopPHPServer();
        app.quit();
    }
});

// Handle uncaught errors
process.on('uncaughtException', (error) => {
    console.error('Uncaught Exception:', error);
    stopPHPServer();
});
