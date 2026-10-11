#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Erzeugt die Pixel-Fixtures fuer tools/test-darkmode-klassifizierung.js
(PLAN-Rueckmeldungen-Blockverweis-iPad-Tafel-Darkmode-Formeln.md, AP-3.1, E9).

Aufruf (Pillow noetig, isoliert ausfuehren):

    python -I tools/erzeuge-darkmode-fixtures.py [UPLOADS-ORDNER]

Standard fuer UPLOADS-ORDNER ist die Mediathek der lokalen Testinstallation
(C:\\allinkl-testserver\\www\\htdocs\\w0000001\\fos\\wp-content\\uploads).
Die Mediathek wird nur gelesen.

WARUM DREI VERKLEINERUNGSARTEN
------------------------------
Meldung #7107: Feine schwarze Linien grosser Formel-PNGs werden beim
Verkleinern auf die Abtastgroesse halbtransparent. Wie stark, haengt vom
Browser ab (WebKit mittelt vermutlich flaechig, Chrome nutzt mit
imageSmoothingQuality 'high' ein hochwertiges Filter, ohne Glaettung
wird nur abgetastet). Jedes Bild wird darum in drei Verfahren verkleinert:

  BOX       flaechenmittelnd (Naeherung fuer WebKit/Safari/Edge iOS)
  BILINEAR  Pillow-Bilinear mit Antialiasing (Naeherung fuer Chrome 'high')
  NEAREST   reine Punktabtastung (ungeglaettet; schlimmster Fall fuer
            Linien, die zwischen die Abtastpunkte fallen)

Pillow verkleinert RGBA intern vormultipliziert (RGBa) - wie Browser-Canvas.

Zielgroesse: laengste Kante hoechstens ABTASTGROESSE (muss mit
MAX_SAMPLE_DIMENSION in src/js/darkmode-image-invert.js uebereinstimmen),
gerundet wie im Script: max(1, round(W * scale)).

AUSGABE
-------
tools/fixtures/darkmode/<id>__<verfahren>.rgba.gz  - RGBA-Rohdaten, gzip
tools/fixtures/darkmode/index.json                 - Metadaten

Die Rohdaten sind gzip-komprimiert (Abweichung von der Plan-Endung .rgba),
damit das Fixture-Paket unter ~5 MB bleibt; Node entpackt mit zlib.
"""
import base64
import gzip
import io
import json
import os
import re
import sys

from PIL import Image

ABTASTGROESSE = 512
UPLOADS = sys.argv[1] if len(sys.argv) > 1 else \
    r"C:\allinkl-testserver\www\htdocs\w0000001\fos\wp-content\uploads"
HIER = os.path.dirname(os.path.abspath(__file__))
ZIEL = os.path.join(HIER, "fixtures", "darkmode")

VERFAHREN = [("BOX", Image.BOX), ("BILINEAR", Image.BILINEAR), ("NEAREST", Image.NEAREST)]

INV = "invertieren"
NICHT = "nicht-invertieren"

# (id, quelle, erwartet, gruppe, begruendung)
# quelle: Pfad relativ zu UPLOADS; Sonderformen:
#   "svgpng:<pfad.svg>"  - eingebettetes PNG aus einer SVG des Zwischenablage-Uploaders
#   "hell:<pfad.png>"    - RGB invertiert, Alpha unveraendert (heller Strich auf transparent)
BILDER = [
    # --- muss invertieren: Fehlfaelle aus #7107 (Original + groesste Zwischengroesse) ---
    ("glycolyse-zweite-atp", "2025/09/Glycolyse-zweite-ATP-Bildung.png", INV, "fehlfall", "0 Strichpixel bei alter Logik"),
    ("glycolyse-zweite-atp-2048", "2025/09/Glycolyse-zweite-ATP-Bildung-2048x617.png", INV, "fehlfall", "groesste Zwischengroesse"),
    ("nad-regeneration", "2025/11/NAD-regeneration.png", INV, "fehlfall", "0 Strichpixel bei alter Logik, 4593 px breit"),
    ("nad-regeneration-2048", "2025/11/NAD-regeneration-2048x844.png", INV, "fehlfall", "groesste Zwischengroesse"),
    ("gluconeogenese", "2025/11/Gluconeogenese.png", INV, "fehlfall", "0 Strichpixel, Hochformat 1833x5022, farbige Pfeile"),
    ("gluconeogenese-2048", "2025/11/Gluconeogenese-748x2048.png", INV, "fehlfall", "groesste Zwischengroesse"),
    ("schritt-2-vt", "2026/01/Schritt-2-vt.png", INV, "fehlfall", "0 Strichpixel bei alter Logik"),
    ("schritt-2-vt-2048", "2026/01/Schritt-2-vt-2048x644.png", INV, "fehlfall", "groesste Zwischengroesse"),
    ("fettsaeure-akrivierung", "2026/02/Fettsaeure-Akrivierung.png", INV, "fehlfall", "0 Strichpixel bei alter Logik"),
    ("fettsaeure-akrivierung-2048", "2026/02/Fettsaeure-Akrivierung-2048x939.png", INV, "fehlfall", "groesste Zwischengroesse"),
    ("bild5", "2025/05/Bild5.png", INV, "fehlfall", "0 Strichpixel bei alter Logik"),
    ("bild5-1024", "2025/05/Bild5-1024x318.png", INV, "fehlfall", "groesste Zwischengroesse"),
    # --- muss invertieren: knappe Faelle (kippen je Browser) ---
    ("harnstoffzyklus", "2026/04/Harnstoffzyklus.png", INV, "knapp", "1-15 Restpixel bei alter Logik"),
    ("harnstoffzyklus-1536", "2026/04/Harnstoffzyklus-1536x1471.png", INV, "knapp", "groesste Zwischengroesse"),
    ("peptidbindung-2", "2026/03/Peptidbindung-2.png", INV, "knapp", "1-15 Restpixel bei alter Logik"),
    ("peptidbindung-2-1536", "2026/03/Peptidbindung-2-1536x482.png", INV, "knapp", "groesste Zwischengroesse"),
    ("cis", "2026/02/CIS.png", INV, "knapp", "1-15 Restpixel bei alter Logik"),
    ("cis-1135", "2026/02/CIS-1135x1536.png", INV, "knapp", "groesste Zwischengroesse"),
    ("isomere-c3h8o", "2025/10/ISOMERE-C3H8O.png", INV, "knapp", "1-15 Restpixel bei alter Logik"),
    ("isomere-c3h8o-1536", "2025/10/ISOMERE-C3H8O-1536x424.png", INV, "knapp", "groesste Zwischengroesse"),
    # --- muss invertieren: Kontrolle positiv ---
    ("atp", "2025/09/ATP.png", INV, "kontrolle-positiv", "wurde schon mit alter Logik erkannt"),
    ("atp-1024", "2025/09/ATP-1024x573.png", INV, "kontrolle-positiv", "groesste Zwischengroesse"),

    # --- darf nicht invertieren: Fotos (JPG, kein Alphakanal) ---
    ("foto-bunsenbrenner", "2026/07/Bunsen_burner_flame_types.jpg", NICHT, "foto", "Foto"),
    ("foto-pattaya", "2026/07/pattaya-5789606_1280-1.jpg", NICHT, "foto", "Foto"),
    # Designer.jpg / Im-Computerbildschirm*.jpg waeren je ~1 MB Fixture (detailreiche
    # KI-Bilder komprimieren kaum) - ersetzt durch kleinere JPGs, damit das Paket ~5 MB bleibt.
    ("foto-logo", "2025/05/Logo.jpg", NICHT, "foto", "Logo als JPG (deckend)"),
    ("foto-betha-faltblatt", "2026/03/Betha-faltblatt.jpg", NICHT, "foto", "kleines JPG unter Abtastgroesse (keine Verkleinerung)"),
    ("foto-bildschirmfoto", "2025/11/Bildschirmfoto_12-11-2025_121610_.jpg", NICHT, "foto", "Bildschirmfoto"),
    # --- darf nicht invertieren: PNG mit deckendem Hintergrund ---
    ("deckend-designer-png", "2025/05/Designer.png", NICHT, "deckend", "deckend UND dunkel (wl ~98): nur die Transparenzpruefung schuetzt"),
    ("deckend-chromatogramm", "2026/01/Chromatogramm.png", NICHT, "deckend", "weisser Hintergrund, ~1 % transparenter Rand"),
    ("deckend-beta-carotin", "2025/12/Beta-Carotin.png", NICHT, "deckend", "weisser Hintergrund, Strukturformel"),
    # --- darf nicht invertieren: SVG aus dem Zwischenablage-Uploader (eingebettetes PNG, deckend weiss) ---
    ("svg-pphw-atp", "svgpng:2026/05/PPHW_-_ATP.svg", NICHT, "deckend-svg", "eingebettetes PNG, deckend weiss"),
    ("svg-pphw-nadph", "svgpng:2026/05/PPHW_-_NADPH.svg", NICHT, "deckend-svg", "eingebettetes PNG, deckend weiss"),
    ("svg-zeichnung", "svgpng:2026/05/zeichnung.svg", NICHT, "deckend-svg", "eingebettetes PNG, deckend weiss"),
    # --- darf nicht invertieren: farbige transparente Grafiken mit hellen Flaechen ---
    ("farbig-ghs02-flamme", "2025/05/GHS02_flamme.png", NICHT, "farbig-hell",
     "GHS-Piktogramm: roter Rahmen, weisse Innenflaeche, 50 % transparent; Invertieren macht es cyan/schwarz"),
    ("farbig-atmungskette", "2026/02/Atmungskette-Uebersicht.png", NICHT, "farbig-hell",
     "Uebersichtsgrafik mit grossen hellen Farbflaechen (wl ~168)"),
    ("farbig-ketonkoerper-transport", "2026/02/Ketonkoerper-Transport.png", NICHT, "farbig-hell",
     "4357x4124, helle Farbflaechen (wl ~198), sehr starke Verkleinerung"),
    ("farbig-einstabmesskette", "2025/11/Einstabmesskette.png", NICHT, "farbig-hell",
     "Geraetezeichnung mit hellen Flaechen (wl ~209)"),
    # --- darf nicht invertieren: heller Strich auf transparentem Grund ---
    ("hell-atp-invertiert", "hell:2025/09/ATP.png", NICHT, "heller-strich",
     "synthetisch: ATP.png mit invertiertem RGB (weisse Linien, Alpha unveraendert); "
     "in der Mediathek gibt es kein weisses Logo auf transparentem Grund"),
]


def lade(quelle):
    if quelle.startswith("svgpng:"):
        pfad = os.path.join(UPLOADS, quelle[7:])
        with open(pfad, "r", encoding="utf-8") as f:
            text = f.read()
        m = re.search(r'href="data:image/png;base64,([^"]+)"', text)
        if not m:
            raise RuntimeError("kein eingebettetes PNG in " + pfad)
        return Image.open(io.BytesIO(base64.b64decode(m.group(1)))).convert("RGBA")
    if quelle.startswith("hell:"):
        im = Image.open(os.path.join(UPLOADS, quelle[5:])).convert("RGBA")
        r, g, b, a = im.split()
        from PIL import ImageOps
        rgb = ImageOps.invert(Image.merge("RGB", (r, g, b)))
        r2, g2, b2 = rgb.split()
        return Image.merge("RGBA", (r2, g2, b2, a))
    return Image.open(os.path.join(UPLOADS, quelle)).convert("RGBA")


def zielgroesse(w, h):
    s = min(1.0, ABTASTGROESSE / float(max(w, h)))
    return max(1, int(round(w * s))), max(1, int(round(h * s)))


def main():
    os.makedirs(ZIEL, exist_ok=True)
    for alt in os.listdir(ZIEL):
        if alt.endswith(".rgba.gz"):
            os.remove(os.path.join(ZIEL, alt))
    eintraege = []
    gesamt = 0
    for (bid, quelle, erwartet, gruppe, grund) in BILDER:
        im = lade(quelle)
        W, H = im.size
        w, h = zielgroesse(W, H)
        for (vname, vconst) in VERFAHREN:
            klein = im if (w, h) == (W, H) else im.resize((w, h), vconst)
            roh = klein.tobytes()
            assert len(roh) == w * h * 4
            datei = "%s__%s.rgba.gz" % (bid, vname)
            with open(os.path.join(ZIEL, datei), "wb") as f:
                f.write(gzip.compress(roh, compresslevel=9, mtime=0))
            gesamt += os.path.getsize(os.path.join(ZIEL, datei))
            eintraege.append({
                "datei": datei, "id": bid, "quelle": quelle, "original": [W, H],
                "breite": w, "hoehe": h, "verfahren": vname,
                "erwartet": erwartet, "gruppe": gruppe, "begruendung": grund,
            })
        print("%-34s %5dx%-5d -> %3dx%-3d %s" % (bid, W, H, w, h, erwartet))
    with open(os.path.join(ZIEL, "index.json"), "w", encoding="utf-8") as f:
        json.dump({"abtastgroesse": ABTASTGROESSE, "fixtures": eintraege}, f, indent=1, ensure_ascii=False)
    print("Fixtures: %d, gesamt %.2f MB" % (len(eintraege), gesamt / 1048576.0))


if __name__ == "__main__":
    main()
