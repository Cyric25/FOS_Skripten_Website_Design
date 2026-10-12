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
#   "synth:<name>"       - synthetisches Grauzonen-Bild aus synthetisch() (AP-3.fix1)
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
    # AP-3.fix1: Die beiden folgenden waren in AP-3.1 "darf nicht invertieren".
    # Betreiberentscheidung 2026-10-12 (Vorschau uploads/darkmode-test/kontrollgruppe.html,
    # Faelle 3B und 5B): schwarze Beschriftung/Umrisse auf transparentem Grund sind ohne
    # invert unlesbar -> MUSS invertieren. Bewusst umgestellt, keine Testanpassung an Code.
    ("farbig-atmungskette", "2026/02/Atmungskette-Uebersicht.png", INV, "betreiber-umgestellt",
     "Betreiberentscheidung 2026-10-12 (kontrollgruppe.html Fall 3B, vorher 'darf nicht'): "
     "schwarze Beschriftung/Pfeile auf transparentem Grund; urspruenglich: "
     "Uebersichtsgrafik mit grossen hellen Farbflaechen (wl ~168)"),
    ("farbig-ketonkoerper-transport", "2026/02/Ketonkoerper-Transport.png", INV, "betreiber-umgestellt",
     "Betreiberentscheidung 2026-10-12 (kontrollgruppe.html Fall 5B, vorher 'darf nicht'): "
     "schwarze Zell-Umrisse/Pfeile auf transparentem Grund; urspruenglich: "
     "4357x4124, helle Farbflaechen (wl ~198), sehr starke Verkleinerung"),
    ("farbig-einstabmesskette", "2025/11/Einstabmesskette.png", NICHT, "farbig-hell",
     "Geraetezeichnung mit hellen Flaechen (wl ~209); Betreiberentscheidung 2026-10-12 "
     "(kontrollgruppe.html Fall 9A) bestaetigt: nicht invertieren"),
    # --- darf nicht invertieren: heller Strich auf transparentem Grund ---
    ("hell-atp-invertiert", "hell:2025/09/ATP.png", NICHT, "heller-strich",
     "synthetisch: ATP.png mit invertiertem RGB (weisse Linien, Alpha unveraendert); "
     "in der Mediathek gibt es kein weisses Logo auf transparentem Grund"),

    # ===================== AP-3.fix1 (Befund K1 aus AP-3.rev) =====================
    # Grauzone der gewichteten Helligkeit 75-167. Nur Originale (keine Zwischengroessen),
    # damit das Fixture-Paket klein bleibt; Zwischengroessen pruefte AP-3.1 bereits.
    # --- muss invertieren: Regression gegenueber 1.5.117 (alt invertiert, AP-3.1-Messung 'hell') ---
    ("k1-phospholipide", "2025/10/phospholipide.png", INV, "k1-regression",
     "alt (1.5.117) invertiert; halbtransparente Pastell-Markerflaechen ueberstimmten die schwarzen Linien (wl 155)"),
    ("k1-sphingomyelin", "2025/10/Sphingomyelin.png", INV, "k1-regression", "wie phospholipide (wl 132)"),
    ("k1-ceramid", "2025/10/Ceramid.png", INV, "k1-regression", "wie phospholipide (wl 142)"),
    ("k1-glykolipid", "2025/10/Glykolipid.png", INV, "k1-regression", "wie phospholipide (wl 133)"),
    ("k1-glycolyse-phasen", "2025/09/Glycolyse-Phasen.png", INV, "k1-regression",
     "alt invertiert; graue/schwarze Beschriftung neben Farbflaechen (wl 116)"),
    ("k1-dc1-1", "2025/11/DC1-1.png", INV, "k1-regression",
     "alt in BOX/BILINEAR invertiert; schwarze Linien der DC-Kammer, halbtransparente Flaechen (wl 162-167)"),
    # --- muss invertieren: Strukturformeln mit Textmarker (alt wie AP-3.1 falsch) ---
    ("k1-schritt-1-vt", "2026/01/Schritt-1-vt.png", INV, "k1-textmarker", "schwarze Formel mit Textmarker (wl 137)"),
    ("k1-schritt-4-vt", "2026/01/Schritt-4-vt.png", INV, "k1-textmarker", "schwarze Formel mit Textmarker (wl 124)"),
    ("k1-schritt-5-vt", "2026/01/Schritt-5-vt.png", INV, "k1-textmarker", "schwarze Formel mit Textmarker (wl 115)"),
    # --- muss invertieren: Grenzfaelle, Betreiberentscheidung 2026-10-12 (grenzfaelle.html, alle "B") ---
    ("grenz-alpha-helix-3", "2026/03/Alpha-Helix-3.png", INV, "betreiber-grenzfall",
     "Betreiberentscheidung 2026-10-12, Vorschau dunkler Grund ohne/mit invert: invertieren. "
     "3D-Modell, CPK-Farben kippen; wl 104 (knappste transparente Alpha-Helix-Variante; Alpha-Helix 83 "
     "und -2 95 liegen tiefer und sind durch dieses Fixture mitgedeckt). Varianten mit deckend weissem "
     "Grund (-1, -2-1, -2-2, -3-1, -3-2, -4) nur ueber Autoren-Klasse fos-darkmode-invert"),
    ("grenz-spannungsreihe", "2025/11/Spannungseihe.png", INV, "betreiber-grenzfall",
     "Betreiberentscheidung 2026-10-12, Vorschau dunkler Grund ohne/mit invert: invertieren (wl 100-104)"),
    ("grenz-apo-holoenzym", "2026/04/APO-und-Holoenzym.png", INV, "betreiber-grenzfall",
     "Betreiberentscheidung 2026-10-12, Vorschau dunkler Grund ohne/mit invert: invertieren (wl 102)"),
    ("grenz-steuerung-glyc-glucon", "2025/11/Steruerung-Glycolyse-Gluconeogenese.png", INV, "betreiber-grenzfall",
     "Betreiberentscheidung 2026-10-12, Vorschau dunkler Grund ohne/mit invert: invertieren (wl 142)"),
    ("grenz-glycogen-wiki", "2025/12/Glycogen-Wiki.png", INV, "betreiber-grenzfall",
     "Betreiberentscheidung 2026-10-12, Vorschau dunkler Grund ohne/mit invert: invertieren (wl 120)"),
    ("grenz-fetttransport", "2026/02/Fetttranspoirt.png", INV, "betreiber-grenzfall",
     "Betreiberentscheidung 2026-10-12, Vorschau dunkler Grund ohne/mit invert: invertieren (wl 150)"),
    ("grenz-malat-aspartat-2", "2026/02/Malat-Aspartat-Shuttle-2.png", INV, "betreiber-grenzfall",
     "Betreiberentscheidung 2026-10-12, Vorschau dunkler Grund ohne/mit invert: invertieren (wl 166); "
     "Malat-Aspartat-Shuttle-1 (kontrollgruppe.html 4B) ist praktisch dasselbe Bild"),
    # --- muss invertieren: Kontrollgruppen-Vorschau, Betreiberentscheidung 2026-10-12 (kontrollgruppe.html) ---
    ("kg-triglycerid", "2025/05/Triglycerid.png", INV, "betreiber-kontrollgruppe",
     "Betreiberentscheidung 2026-10-12 (Fall 2B): dunkelgraue Beschriftung auf transparent (wl 126)"),
    ("kg-dc2", "2025/11/DC2.png", INV, "betreiber-kontrollgruppe",
     "Betreiberentscheidung 2026-10-12 (Fall 6B): schwarze Linien, halbtransparente Flaechen (wl 190); "
     "DC1 ist durch k1-dc1-1 gedeckt"),
    ("kg-pdc-uebersicht", "2026/01/PDC-Uebersicht.png", INV, "betreiber-kontrollgruppe",
     "Betreiberentscheidung 2026-10-12 (Fall 7B): deckende Pastellflaechen, schwarze Schrift auf transparent (wl 211)"),
    ("kg-kompetitiver-inhibitor", "2025/09/kompetetiver-Inhibitor-MMD-1.png", INV, "betreiber-kontrollgruppe",
     "Betreiberentscheidung 2026-10-12 (Fall 8B): Diagramm deckend hell, Formelzeichen schwarz auf transparent (wl 232)"),
    # --- darf nicht invertieren: Norm-Piktogramme mit deckend heller Flaeche ---
    # Betreiberentscheidung 2026-10-12 (Fall 1A, Leitlinie): GHS-Piktogramme nie invertieren.
    # Ersetzt mit den synthetischen Grauzonen-Fixtures unten das nicht erfuellbare Kriterium
    # ">= 3 echte Bilder mit wl 75-167 auf jeder Seite" (siehe Plan, AP-3.fix1).
    ("ghs01-explos", "2025/05/GHS01_explos.png", NICHT, "betreiber-piktogramm", "GHS-Piktogramm (wl 195), Leitlinie 2026-10-12"),
    ("ghs03-rondflam", "2025/05/GHS03_rondflam.png", NICHT, "betreiber-piktogramm",
     "Betreiberentscheidung 2026-10-12 (kontrollgruppe.html Fall 1A): nicht invertieren (wl 165)"),
    ("ghs04-bottle", "2025/05/GHS04_bottle.png", NICHT, "betreiber-piktogramm", "GHS-Piktogramm (wl 188), Leitlinie 2026-10-12"),
    ("ghs05-acid", "2025/05/GHS05_acid.png", NICHT, "betreiber-piktogramm", "GHS-Piktogramm (wl 188), Leitlinie 2026-10-12"),
    ("ghs06-skull", "2025/05/GHS06_skull.png", NICHT, "betreiber-piktogramm", "GHS-Piktogramm (wl 183), Leitlinie 2026-10-12"),
    ("ghs07-exclam", "2025/05/GHS07_exclam.png", NICHT, "betreiber-piktogramm", "GHS-Piktogramm (wl 188), Leitlinie 2026-10-12"),
    # --- synthetische Grauzonen-Fixtures (wl 75-167), je Seite drei ---
    ("synth-n-piktogramm", "synth:piktogramm", NICHT, "synth-grauzone",
     "synthetisch: Raute deckend hellgrau mit rotem Rahmen und grossem schwarzem Symbol innen (Norm-Piktogramm)"),
    ("synth-n-farbkarte", "synth:farbkarte", NICHT, "synth-grauzone",
     "synthetisch: deckende orange Karte, schwarze Schriftstriche nur INNERHALB der Karte"),
    ("synth-n-freigestellt", "synth:freigestellt", NICHT, "synth-grauzone",
     "synthetisch: freigestelltes 'Foto' (deckende Ellipse mit Mitteltoenen), keine dunkle Tinte"),
    ("synth-m-linien-marker", "synth:linien-marker", INV, "synth-grauzone",
     "synthetisch: schwarze Formel-Linien auf transparent, grosse halbtransparente gelbe Markerflaechen darueber"),
    ("synth-m-schrift-kaesten", "synth:schrift-kaesten", INV, "synth-grauzone",
     "synthetisch: deckende Pastellkaesten, schwarze Schriftstriche frei auf transparentem Grund daneben"),
    ("synth-m-graue-beschriftung", "synth:graue-beschriftung", INV, "synth-grauzone",
     "synthetisch: deckende Farbbalken, dunkelgraue Beschriftung frei auf transparentem Grund (wie Triglycerid)"),
]


def synthetisch(name):
    """Deterministische Grauzonen-Bilder (1200x900, transparenter Grund)."""
    from PIL import ImageDraw
    W, H = 1200, 900
    im = Image.new("RGBA", (W, H), (0, 0, 0, 0))
    d = ImageDraw.Draw(im)

    def schrift(x0, y0, zeilen, breite, farbe, staerke=7):
        # "Schrift" als kurze Striche/Boegen, reproduzierbar ohne Schriftdatei
        for z in range(zeilen):
            y = y0 + z * 48
            x = x0
            k = 0
            while x < x0 + breite:
                w = 14 + (k * 7) % 22
                d.line([(x, y), (x + w, y + 26)], fill=farbe, width=staerke)
                d.line([(x + w, y), (x, y + 26)], fill=farbe, width=staerke)
                x += w + 12
                k += 1

    if name == "piktogramm":
        raute = [(600, 60), (1040, 450), (600, 840), (160, 450)]
        d.polygon(raute, fill=(230, 230, 230, 255))
        d.line(raute + [raute[0]], fill=(215, 25, 25, 255), width=46, joint="curve")
        d.ellipse([440, 260, 760, 580], fill=(0, 0, 0, 255))
        d.ellipse([510, 330, 690, 510], fill=(230, 230, 230, 255))
        d.rectangle([430, 600, 770, 660], fill=(0, 0, 0, 255))
        d.polygon([(600, 170), (690, 300), (510, 300)], fill=(0, 0, 0, 255))
    elif name == "farbkarte":
        d.rounded_rectangle([150, 150, 1050, 750], radius=40, fill=(240, 150, 70, 255))
        schrift(230, 230, 9, 740, (0, 0, 0, 255))
    elif name == "freigestellt":
        maske = Image.new("L", (W, H), 0)
        ImageDraw.Draw(maske).ellipse([100, 80, 1100, 820], fill=255)
        foto = Image.new("RGB", (W, H))
        px = foto.load()
        for y in range(H):
            for x in range(W):
                # weicher Verlauf in Mitteltoenen (komprimiert gut, keine dunkle Tinte)
                px[x, y] = (100 + 90 * x // W, 115 + 60 * y // H, 80 + 50 * (x + y) // (W + H))
        im.paste(foto.convert("RGBA"), (0, 0), maske)
    elif name == "linien-marker":
        import math
        # halbtransparente Pastell-Markerflaechen UNTER den Linien (wie phospholipide.png)
        d.rounded_rectangle([140, 170, 560, 480], radius=60, fill=(235, 165, 195, 120))
        d.rounded_rectangle([640, 170, 1060, 480], radius=60, fill=(165, 205, 240, 120))
        d.rounded_rectangle([300, 530, 900, 800], radius=60, fill=(235, 215, 125, 120))
        for cx, cy in [(300, 320), (600, 420), (900, 320), (450, 660), (750, 660)]:
            pkt = [(cx + 120 * math.cos(math.pi / 3 * i + math.pi / 6),
                    cy + 120 * math.sin(math.pi / 3 * i + math.pi / 6)) for i in range(7)]
            d.line(pkt, fill=(0, 0, 0, 255), width=9)
        schrift(120, 60, 2, 960, (0, 0, 0, 255), 7)
    elif name == "schrift-kaesten":
        d.rounded_rectangle([60, 80, 560, 420], radius=30, fill=(130, 175, 225, 255))
        d.rounded_rectangle([640, 80, 1140, 420], radius=30, fill=(230, 150, 120, 255))
        d.rounded_rectangle([60, 500, 560, 840], radius=30, fill=(140, 200, 130, 255))
        schrift(660, 520, 7, 460, (0, 0, 0, 255), 9)
        d.line([(580, 250), (630, 250)], fill=(0, 0, 0, 255), width=6)
        d.line([(310, 440), (310, 490)], fill=(0, 0, 0, 255), width=6)
    elif name == "graue-beschriftung":
        d.rectangle([80, 80, 300, 700], fill=(70, 170, 60, 255))
        for y in (90, 330, 570):
            d.rounded_rectangle([320, y, 1120, y + 110], radius=18, fill=(235, 120, 50, 255))
        schrift(90, 760, 2, 1000, (60, 60, 60, 255))
    else:
        raise RuntimeError("unbekanntes synthetisches Bild " + name)
    return im


def lade(quelle):
    if quelle.startswith("synth:"):
        return synthetisch(quelle[6:])
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
