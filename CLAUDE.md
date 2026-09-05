# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Überblick

Dieses Paket (`timonkreis/deployer-recipes`) stellt wiederverwendbare [Deployer](https://deployer.org/)-Rezepte und Standardkonfigurationen bereit. Es wird von anderen PHP-Projekten per Composer als Dev-Dependency eingebunden (`composer require --dev timonkreis/deployer-recipes:@dev`) und dort in deren `deploy.php` importiert. Es ist selbst kein deploybares Projekt, sondern eine Bibliothek von Deployer-Recipes.

## Architektur

- `recipes/common.php` – Basiskonfiguration, die von allen anderen Recipes eingebunden wird (`require_once __DIR__ . '/common.php'`). Setzt Deployer-Defaults (`branch`, `writable_dirs`, `keep_releases`, `writable_mode`), leitet `repository` automatisch aus der lokalen `.git/config` des Zielprojekts ab und definiert generische Tasks zum Ansehen/Herunterladen sensibler Dateien (`.env`, `auth.json`).
- `recipes/typo3.php`, `recipes/wordpress.php`, `recipes/matomo.php` – CMS-/Anwendungsspezifische Recipes. Jedes davon baut auf dem jeweiligen offiziellen Deployer-Recipe auf (`require_once 'recipe/typo3.php';` bzw. `wordpress.php`) und lädt zusätzlich `common.php`. Sie überschreiben `writable_dirs`, `shared_dirs` und `shared_files` passend zur jeweiligen Anwendung und definieren anwendungsspezifische Download-Tasks (z. B. `download:fileadmin` für TYPO3, `download:uploads` für WordPress).
- `src/functions.php` – globale Hilfsfunktionen ohne Namespace, die von den Recipes genutzt werden:
  - `project_root()` bestimmt das Wurzelverzeichnis des Projekts, das dieses Paket als Dependency einbindet, relativ zum eigenen Pfad (`dirname(__DIR__, 4)` – setzt die übliche `vendor/<vendor>/<package>/src`-Verzeichnistiefe voraus).
  - `load_json_from_file()` liest und dekodiert JSON-Dateien relativ zu `project_root()` (wird z. B. genutzt, um `composer.json`/`composer.lock` des Zielprojekts auszulesen und daraus TYPO3-Webroot/-Version oder den WordPress-Webroot zu ermitteln).
- Rezept-Dateien laufen im Namespace `Deployer`, `src/functions.php` bewusst nicht (globale Funktionen, die aus dem `Deployer`-Namespace heraus per führendem `\` oder unqualifiziert über den globalen Fallback aufgerufen werden).

## Konventionen in diesem Repo

- Jede PHP-Datei beginnt mit `declare(strict_types=1);`.
- Werte, die dynamisch vom Zielprojekt abhängen (z. B. Webroot-Pfad, Framework-Version, vorhandene Dateien), werden als Closures via `set('key', function() { ... })` definiert, nicht als statische Werte – so werden sie lazy zur Deploy-Zeit ausgewertet.
- Optionale Dateien in `shared_files` werden nur aufgenommen, wenn sie im Zielprojekt tatsächlich existieren (Prüfung via `@is_file(project_root() . '/' . parse($possibleFile))`).
- Neue Downloads/Betrachtungs-Tasks für sensible Dateien folgen dem etablierten Muster: `askConfirmation(...)`, dann `download()` mit `flags => '-azLP'`, ggf. `readfile()` + `unlink()` bei reinen "view"-Tasks.
- Änderungen werden in `CHANGELOG.md` mit Datum (`## YYYY-MM-DD`) und Stichpunkten dokumentiert.

## Entwicklung

- Es gibt keine Build-, Lint- oder Test-Kommandos in diesem Repository (kein Testverzeichnis, kein CI-Setup, keine Scripts in `composer.json`).
- Abhängigkeiten installieren: `composer install`.
- Da dieses Paket nur als Bibliothek eingebunden wird, lässt sich eine Änderung realistisch nur testen, indem sie in einem echten Projekt eingebunden wird (z. B. via lokalem Composer-Path-Repository) und dort ein `dep deploy` (oder `dep <task>`) gegen ein Zielsystem ausgeführt wird.
