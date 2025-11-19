# FOS Online Schulbuch - WordPress Theme

WordPress-Theme für das FOS Online Schulbuch mit Container Blocks, Glossar-System und hierarchischer Navigation.

## Features

- ✅ Hierarchische Seitennavigation (collapsible tree)
- ✅ Glossar-System mit automatischer Verlinkung
- ✅ Glossar-Editor im Block-Editor (vollständiges Formular)
- ✅ Container Block Designer Integration
- ✅ Modulare Blöcke Integration
- ✅ Custom Field für Navigation ein/aus
- ✅ Vollständig responsiv (Desktop, Tablet, Mobile)
- ✅ Touch-optimiert (Swipe-Gesten)
- ✅ Farbschema: #71230a (Text) & #e24614 (UI)
- ✅ SEO-freundlich

## Installation als ZIP-Datei

### Schritt 1: ZIP-Datei erhalten
Die ZIP-Datei wird automatisch beim Build erstellt:
```bash
npm run build
```
Die fertige ZIP-Datei befindet sich dann in `dist/fos-online-schulbuch-v1.0.0.zip`

### Schritt 2: Installation in WordPress
1. Loggen Sie sich in Ihr WordPress-Admin-Panel ein
2. Gehen Sie zu **Design > Themes**
3. Klicken Sie auf **"Theme hinzufügen"**
4. Klicken Sie auf **"Theme hochladen"**
5. Wählen Sie Ihre ZIP-Datei aus
6. Klicken Sie auf **"Jetzt installieren"**
7. Nach der Installation klicken Sie auf **"Aktivieren"**

### Schritt 3: Menü einrichten
1. Gehen Sie zu **Design > Menüs**
2. Erstellen Sie ein neues Menü oder bearbeiten Sie ein vorhandenes
3. Fügen Sie Ihre gewünschten Seiten zum Menü hinzu
4. Weisen Sie das Menü der Position **"Hauptmenü"** zu
5. Speichern Sie die Änderungen

## Theme-Struktur

```
fos-online-schulbuch/
├── style.css             # Haupt-Stylesheet
├── functions.php         # Theme-Setup, Glossar, REST API
├── page.php              # Seiten mit Sidebar
├── sidebar.php           # Hierarchische Navigation
├── header.php            # Header (optional per Custom Field)
├── footer.php            # Footer
├── index.php             # Blog-Posts
├── single.php            # Einzelne Posts
├── archive-glossar.php   # Glossar-Archiv
├── single-glossar.php    # Einzelner Glossar-Eintrag
├── src/                  # Source-Dateien (JavaScript, CSS)
│   ├── js/
│   │   ├── main.js           # Theme-JavaScript (Sidebar, Menu)
│   │   ├── glossar.js        # Glossar-Frontend
│   │   └── glossar-editor.js # Glossar Block-Editor
│   └── css/
│       └── glossar.css       # Glossar-Styles
├── dist/                 # Build-Output (generiert)
└── README.md             # Diese Anleitung
```

## Anpassungen

### Logo hinzufügen
Gehen Sie zu **Design > Customizer > Website-Identität** und laden Sie Ihr Logo hoch.

### Farben anpassen
Bearbeiten Sie die CSS-Variablen in der `style.css` Datei:
- Hauptfarbe: `#0073aa`
- Textfarbe: `#333`
- Hintergrundfarbe: `#fff`

### Schriftarten ändern
Ändern Sie die `font-family` Eigenschaft in der `style.css` Datei.

## Browser-Support

- Chrome (neueste Version)
- Firefox (neueste Version)
- Safari (neueste Version)
- Edge (neueste Version)
- Internet Explorer 11+

## Responsive Breakpoints

- Desktop: 1200px+
- Tablet: 768px - 1199px
- Mobile: bis 767px
- Kleine Mobile: bis 480px

## Support

Dieses Theme ist selbst-unterstützt. Bei Problemen überprüfen Sie:

1. WordPress-Version (mindestens 5.0)
2. PHP-Version (mindestens 7.4)
3. Theme-Dateien vollständig hochgeladen
4. Menü korrekt zugewiesen

## Changelog

### Version 1.0
- Erstes Release
- Grundlegende Theme-Funktionen
- Responsive Design
- Mobile Navigation

---

**Theme Name:** FOS Online Schulbuch
**Version:** 1.0
**Author:** Martin Huber
**WordPress Kompatibilität:** 5.0+
**PHP Kompatibilität:** 7.4+
**Text Domain:** fos-online-schulbuch