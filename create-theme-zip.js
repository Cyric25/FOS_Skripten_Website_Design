import { createWriteStream, mkdirSync, existsSync } from 'fs';
import { readdir, stat } from 'fs/promises';
import { join, basename, dirname } from 'path';
import { createRequire } from 'module';
import archiver from 'archiver';

const require = createRequire(import.meta.url);
const packageJson = require('./package.json');

const OUTPUT_DIR = './dist';
const THEME_NAME = 'simple-clean-theme';
const VERSION = packageJson.version;
const OUTPUT_FILE = `${THEME_NAME}-v${VERSION}.zip`;

// Files and folders to include in the ZIP
const INCLUDE_PATTERNS = [
    '*.php',
    'style.css',
    'readme.md',
    'LICENSE',
    'dist/js/**/*',
    'dist/css/**/*',
    'dist/.vite/manifest.json'
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
    'dist/*.zip'
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

        // Add files to archive (directly in root, no parent folder)
        for (const file of allFiles) {
            if (await shouldIncludeFile(file)) {
                const relativePath = file.replace(/^\.[\\/]/, '');
                archive.file(file, { name: relativePath });
                console.log(`  + ${relativePath}`);
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
