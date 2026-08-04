# ARCHITECTURE_MAP.md

## 1. Entry Points
- wpglobus-multilingual-comments.php — main plugin entry, all logic loads here

## 2. Functional Areas

### Core Comment Logic
- wpglobus-multilingual-comments.php — comment language assignment and filtering

### Release Metadata
- readme.txt — WordPress.org listing and changelog
- README.md — GitHub deploy instructions

### Deployment Automation
- .github/workflows/main.yml — tag-triggered SVN deploy

### Localization
- languages/wpglobus-multilingual-comments.pot — translation template
- languages/wpglobus-multilingual-comments-ru_RU.po — Russian translation source
- languages/wpglobus-multilingual-comments-ru_RU.mo — Russian translation compiled

### Distribution Config
- .distignore — files excluded from release package
- .gitattributes — line ending and export rules

### Store Assets
- .wordpress-org/screenshot-1.png — WordPress.org listing screenshot

### Funding Config
- .github/FUNDING.yml — sponsorship metadata

## 3. Directory Roles
- .github/ — CI automation and repo metadata
- .wordpress-org/ — WordPress.org listing assets
- languages/ — translation files

## 4. Safe Modification Rules
- Safe to edit: wpglobus-multilingual-comments.php, readme.txt, README.md, languages/*.po
- Safe to edit: .github/workflows/main.yml (deploy config only)
- Do NOT touch: languages/*.mo (generated, not hand-edited)
- Do NOT touch: .wordpress-org/screenshot-1.png unless explicitly requested
- Do NOT touch: .distignore, .gitattributes unless explicitly requested

## 5. Navigation Rules for AI Agent
- Comment/language feature task → start and stay in wpglobus-multilingual-comments.php
- Release/version bump task → wpglobus-multilingual-comments.php + readme.txt only
- Deploy/CI task → .github/workflows/main.yml only
- Translation task → languages/ only
- Do NOT scan .wordpress-org/ for code tasks
- Do NOT open languages/*.mo files
- Do NOT search for build tools, package managers, or framework configs — none exist in this repo
- This repo has exactly one PHP source file — do not search for additional source files
