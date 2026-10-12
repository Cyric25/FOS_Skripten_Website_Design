#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Regressionswaechter Darkmode-Bildinvertierung, Teil 1: Abtastung der Mediathek
(PLAN-Rueckmeldungen-Blockverweis-iPad-Tafel-Darkmode-Formeln.md, AP-3.fix1).

Nur LESEND gegenueber der Mediathek. Verkleinert alle Original-PNG/GIF/WebP
(keine WordPress-Zwischengroessen "-WxH") in drei Verfahren (BOX, BILINEAR,
NEAREST - wie tools/erzeuge-darkmode-fixtures.py) auf
  - 200 px  (Abtastgroesse der alten Logik, Theme 1.5.117)
  - 512 px  (Abtastgroesse der neuen Logik)
und legt die RGBA-Rohdaten gzip-komprimiert in einem Zwischenordner AUSSERHALB
des Repos ab. Die eigentliche Klassifizierung mit dem ECHTEN alten und neuen
Script macht Teil 2:

    python -I tools/vergleiche-darkmode-mediathek.py [UPLOADS] [ZWISCHENORDNER]
    node tools/vergleiche-darkmode-mediathek.js [ZWISCHENORDNER] [--alle]

Standard UPLOADS: Mediathek der lokalen Testinstallation.
Standard ZWISCHENORDNER: <Temp>/fos-darkmode-mediathek
Bereits abgetastete Bilder (gleiche Groesse/Aenderungszeit) werden uebersprungen.
"""
import gzip
import json
import os
import re
import sys
import tempfile

from PIL import Image

UPLOADS = sys.argv[1] if len(sys.argv) > 1 else \
    r"C:\allinkl-testserver\www\htdocs\w0000001\fos\wp-content\uploads"
ZWISCHEN = sys.argv[2] if len(sys.argv) > 2 else \
    os.path.join(tempfile.gettempdir(), "fos-darkmode-mediathek")

VARIANTE = re.compile(r"-\d+x\d+\.(png|gif|webp)$", re.I)
GROESSEN = [200, 512]
VERFAHREN = [("BOX", Image.BOX), ("BILINEAR", Image.BILINEAR), ("NEAREST", Image.NEAREST)]


def zielgroesse(w, h, kante):
    s = min(1.0, kante / float(max(w, h)))
    return max(1, int(round(w * s))), max(1, int(round(h * s)))


def main():
    os.makedirs(ZWISCHEN, exist_ok=True)
    indexpfad = os.path.join(ZWISCHEN, "index.json")
    alt = {}
    if os.path.exists(indexpfad):
        with open(indexpfad, "r", encoding="utf-8") as f:
            for e in json.load(f).get("bilder", []):
                alt[e["pfad"]] = e
    bilder = []
    fehler = []
    nr = 0
    for wurzel, ordner, dateien in os.walk(UPLOADS):
        ordner.sort()
        for d in sorted(dateien):
            if not d.lower().endswith((".png", ".gif", ".webp")) or VARIANTE.search(d):
                continue
            p = os.path.join(wurzel, d)
            rel = os.path.relpath(p, UPLOADS).replace("\\", "/")
            if not re.match(r"^\d{4}/", rel):
                continue  # nur Mediathek-Jahresordner, keine Plugin-Caches
            st = os.stat(p)
            kennung = "%d-%d" % (st.st_size, int(st.st_mtime))
            if rel in alt and alt[rel].get("kennung") == kennung and \
                    all(os.path.exists(os.path.join(ZWISCHEN, x["datei"])) for x in alt[rel]["abtastungen"]):
                bilder.append(alt[rel])
                continue
            try:
                im = Image.open(p)
                im.seek(0)
                im = im.convert("RGBA")
            except Exception as ex:  # defekte Datei: melden, weitermachen
                fehler.append({"pfad": rel, "fehler": str(ex)})
                continue
            nr += 1
            W, H = im.size
            stamm = "b%05d" % len(bilder)
            abtastungen = []
            for kante in GROESSEN:
                w, h = zielgroesse(W, H, kante)
                for (vname, vconst) in VERFAHREN:
                    klein = im if (w, h) == (W, H) else im.resize((w, h), vconst)
                    datei = "%s_%d_%s.rgba.gz" % (stamm, kante, vname)
                    with open(os.path.join(ZWISCHEN, datei), "wb") as f:
                        f.write(gzip.compress(klein.tobytes(), compresslevel=1, mtime=0))
                    abtastungen.append({"datei": datei, "kante": kante, "verfahren": vname,
                                        "breite": w, "hoehe": h})
            bilder.append({"pfad": rel, "kennung": kennung, "original": [W, H],
                           "abtastungen": abtastungen})
    with open(indexpfad, "w", encoding="utf-8") as f:
        json.dump({"uploads": UPLOADS, "bilder": bilder, "fehler": fehler}, f, indent=1, ensure_ascii=False)
    print("Bilder: %d (neu abgetastet: %d), nicht lesbar: %d -> %s" % (len(bilder), nr, len(fehler), ZWISCHEN))
    for e in fehler:
        print("  NICHT LESBAR: %s (%s)" % (e["pfad"], e["fehler"]))


if __name__ == "__main__":
    main()
