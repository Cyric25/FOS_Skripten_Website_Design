import { createWriteStream, mkdirSync, existsSync } from 'fs';
import { readdir, stat } from 'fs/promises';
import { join, basename, dirname } from 'path';
import { createRequire } from 'module';
import archiver from 'archiver';

const require = createRequire(import.meta.url);
const packageJson = require('./package.json');

const OUTPUT_DIR  = './dist';
const THEME_NAME  = 'fos-online-schulbuch';
const VERSION     = packageJson.version;
// Fixed filename so WordPress always updates the same theme folder.
// Files live inside a THEME_NAME/ subfolder inside the ZIP – required by WP.
const OUTPUT_FILE = `${THEME_NAME}.zip`;

// Files and folders to include in the ZIP
const INCLUDE_PATTERNS = [
    '*.php',
    'style.css',
    'readme.md',
    'LICENSE',
    'dist/js/**/*',
    'dist/css/**/*',
    'dist/.vite/manifest.json',
    'blocks/**/*.{php,json}'
];

// Files and folders to exclude
const EXCLUDE_PATTERNS = [
    'node_modules',
    '.git',
    'src',
    '.gitignore',
    'package.json',
    'package-lock.json',
    'vite.config.js',
    'create-theme-zip.js',
    'CLAUDE.md',
    '.vscode',
    '.idea',
    'dist/*.zip',
    // Pruefharnische gehoeren nicht auf die Live-Site.
    //
    // ACHTUNG, leicht zu uebersehen: Die Einschlussregel weiter unten lautet
    // filePath.match(/\.php$/) und trifft damit JEDE PHP-Datei in jedem
    // Unterverzeichnis, nicht nur die im Wurzelverzeichnis. Ohne diesen
    // Ausschluss landete tools/test-sichtbarkeit.php im Verteilungspaket
    // (aufgefallen beim Bau von v1.5.77).
    //
    // Mit Schraegstrich, damit kuenftige Dateien wie includes/tools-helper.php
    // nicht versehentlich mit ausgeschlossen werden.
    'tools/'
];

async function shouldIncludeFile(filePath) {
    const relativePath = filePath.replace(/\\/g, '/');

    // Check excludes first
    for (const pattern of EXCLUDE_PATTERNS) {
        if (relativePath.includes(pattern)) {
            return false;
        }
    }

    // Include all PHP files and style.css in root
    if (filePath.match(/\.php$/) || filePath === 'style.css' || filePath === 'readme.md' || filePath === 'LICENSE') {
        return true;
    }

    // Include dist/js, dist/css, and dist/.vite/manifest.json
    if (relativePath.startsWith('dist/js/') ||
        relativePath.startsWith('dist/css/') ||
        relativePath === 'dist/.vite/manifest.json') {
        return true;
    }

    // Include PHP and JS files in subdirectories (e.g. includes/)
    if (relativePath.startsWith('includes/') && filePath.match(/\.(php|js)$/)) {
        return true;
    }

    // Block-Definitionen (blocks/<name>/block.json und ggf. render.php).
    //
    // WICHTIG: .json wird bewusst NUR unterhalb von blocks/ freigegeben, nicht
    // pauschal fuers ganze Theme - sonst landeten package.json und
    // package-lock.json im Verteilungspaket.
    //
    // Faellt diese Regel weg, fehlt block.json im ZIP, der Block ist auf der
    // Live-Site unbekannt und die Seite zeigt einen Blockfehler. Der Fehler
    // taucht erst nach dem Hochladen auf, nicht beim Bauen. Dieselbe
    // Fehlerklasse gab es schon einmal beim Plugin-ZIP (fehlender Autoloader).
    if (relativePath.startsWith('blocks/') && filePath.match(/\.(php|json)$/)) {
        return true;
    }

    return false;
}

async function getAllFiles(dirPath, arrayOfFiles = []) {
    const files = await readdir(dirPath);

    for (const file of files) {
        const filePath = join(dirPath, file);
        const fileStat = await stat(filePath);

        if (fileStat.isDirectory()) {
            // Skip excluded directories
            if (!EXCLUDE_PATTERNS.some(pattern => file === pattern || filePath.includes(pattern))) {
                arrayOfFiles = await getAllFiles(filePath, arrayOfFiles);
            }
        } else {
            arrayOfFiles.push(filePath);
        }
    }

    return arrayOfFiles;
}

async function createThemeZip() {
    try {
        // Ensure dist directory exists
        if (!existsSync(OUTPUT_DIR)) {
            mkdirSync(OUTPUT_DIR, { recursive: true });
        }

        const outputPath = join(OUTPUT_DIR, OUTPUT_FILE);
        const output = createWriteStream(outputPath);
        const archive = archiver('zip', {
            zlib: { level: 9 } // Maximum compression
        });

        console.log(`Creating theme ZIP: ${OUTPUT_FILE}`);

        // Listen to archive events
        output.on('close', () => {
            const sizeInMB = (archive.pointer() / 1024 / 1024).toFixed(2);
            console.log(`✓ Theme ZIP created successfully!`);
            console.log(`  File: ${outputPath}`);
            console.log(`  Size: ${sizeInMB} MB (${archive.pointer()} bytes)`);
        });

        archive.on('error', (err) => {
            throw err;
        });

        archive.on('warning', (err) => {
            if (err.code === 'ENOENT') {
                console.warn('Warning:', err);
            } else {
                throw err;
            }
        });

        // Pipe archive to output file
        archive.pipe(output);

        // Get all files
        const allFiles = await getAllFiles('.');

        // Add files into THEME_NAME/ subfolder so WP installs to a fixed directory
        for (const file of allFiles) {
            if (await shouldIncludeFile(file)) {
                const relativePath = file.replace(/^\.[\\/]/, '').replace(/\\/g, '/');
                const zipPath = `${THEME_NAME}/${relativePath}`;
                archive.file(file, { name: zipPath });
                console.log(`  + ${zipPath}`);
            }
        }

        // Finalize the archive
        await archive.finalize();

    } catch (error) {
        console.error('Error creating theme ZIP:', error);
        process.exit(1);
    }
}

createThemeZip();
