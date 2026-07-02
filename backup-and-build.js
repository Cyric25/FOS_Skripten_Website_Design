import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import { execSync } from 'child_process';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

/**
 * Increment patch version (e.g., 1.5.2 -> 1.5.3)
 */
function incrementVersion(version) {
    const parts = version.split('.');
    parts[2] = parseInt(parts[2]) + 1;
    return parts.join('.');
}

/**
 * Update version in package.json
 */
function updatePackageVersion(newVersion) {
    const packagePath = path.join(__dirname, 'package.json');
    const packageJson = JSON.parse(fs.readFileSync(packagePath, 'utf8'));
    packageJson.version = newVersion;
    fs.writeFileSync(packagePath, JSON.stringify(packageJson, null, 2) + '\n', 'utf8');
    return packageJson;
}

/**
 * Update version in style.css (Version field only, Theme Name stays fixed)
 */
function updateStyleVersion(newVersion) {
    const stylePath = path.join(__dirname, 'style.css');
    let styleContent = fs.readFileSync(stylePath, 'utf8');

    // Update Version: field
    styleContent = styleContent.replace(/Version: [\d.]+/, `Version: ${newVersion}`);

    // Keep Theme Name fixed (no version suffix) so WordPress recognises the theme
    styleContent = styleContent.replace(
        /Theme Name: FOS Online Schulbuch( v[\d.]+)?/,
        'Theme Name: FOS Online Schulbuch'
    );

    fs.writeFileSync(stylePath, styleContent, 'utf8');
}

// Read current version from package.json
const packageJson = JSON.parse(fs.readFileSync(path.join(__dirname, 'package.json'), 'utf8'));
const oldVersion = packageJson.version;
const version = incrementVersion(oldVersion);

// Update version in both files
console.log(`📌 Versionsnummer erhöhen: ${oldVersion} → ${version}\n`);
updatePackageVersion(version);
updateStyleVersion(version);
console.log('   ✓ package.json aktualisiert');
console.log('   ✓ style.css aktualisiert\n');

const distDir = path.join(__dirname, 'dist');
const backupDir = path.join(__dirname, 'backups');
const backupZip = path.join(backupDir, `fos-online-schulbuch-backup.zip`);

// Erstelle backups-Verzeichnis falls nicht vorhanden
if (!fs.existsSync(backupDir)) {
    fs.mkdirSync(backupDir, { recursive: true });
}

console.log('🔄 Backup-Prozess gestartet...\n');

// Schritt 1: Altes Backup löschen (falls vorhanden)
if (fs.existsSync(backupZip)) {
    console.log('🗑️  Altes Backup wird gelöscht...');
    fs.unlinkSync(backupZip);
    console.log('   ✓ Altes Backup gelöscht\n');
}

// Schritt 2: Vorheriges ZIP sichern
const currentZip = path.join(distDir, 'fos-online-schulbuch.zip');
if (fs.existsSync(currentZip)) {
    console.log('💾 Aktuelles ZIP wird zu Backup umbenannt: fos-online-schulbuch.zip');
    fs.renameSync(currentZip, backupZip);
    const stats = fs.statSync(backupZip);
    const sizeMB = (stats.size / (1024 * 1024)).toFixed(2);
    console.log(`   ✓ Backup erstellt: fos-online-schulbuch-backup.zip (${sizeMB} MB)\n`);
} else {
    console.log('ℹ️  Kein vorheriges ZIP gefunden, kein Backup nötig\n');
}

// Schritt 3: Neues ZIP erstellen
console.log('🔨 Erstelle neues Theme-ZIP v' + version + '...\n');
try {
    execSync('npm run build:js && npm run zip', {
        stdio: 'inherit',
        cwd: __dirname
    });
    console.log('\n✅ Backup-Prozess abgeschlossen!');

    // Zeige Zusammenfassung
    console.log('\n📦 Zusammenfassung:');
    if (fs.existsSync(backupZip)) {
        const backupStats = fs.statSync(backupZip);
        console.log(`   Backup:  fos-online-schulbuch-backup.zip (${(backupStats.size / 1024).toFixed(0)} KB)`);
    }
    const newZip = path.join(distDir, 'fos-online-schulbuch.zip');
    if (fs.existsSync(newZip)) {
        const currentStats = fs.statSync(newZip);
        console.log(`   Aktuell: fos-online-schulbuch.zip v${version} (${(currentStats.size / 1024).toFixed(0)} KB)`);
    }
} catch (error) {
    console.error('❌ Fehler beim Erstellen des ZIPs:', error.message);
    process.exit(1);
}
