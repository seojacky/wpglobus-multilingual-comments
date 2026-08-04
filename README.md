# Multilingual Comments for WPGlobus

Неофициальный плагин для WordPress, добавляющий многоязычные комментарии при использовании плагина [WPGlobus](https://wordpress.org/plugins/wpglobus/).

Полное описание, FAQ и changelog — в [`readme.txt`](readme.txt) (формат WordPress.org).

## Деплой на WordPress.org

Публикация новой версии плагина в SVN-репозиторий WordPress.org выполняется автоматически через GitHub Action **[`.github/workflows/main.yml`](.github/workflows/main.yml)** ("Deploy to WordPress.org").

### Как это работает

Workflow запускается на событие `push` git-тега (`tags: ["*"]`). При срабатывании он:

1. Делает checkout репозитория (`actions/checkout`).
2. Запускает [`10up/action-wordpress-plugin-deploy`](https://github.com/10up/action-wordpress-plugin-deploy), который:
   - собирает содержимое плагина (за вычетом файлов из [`.distignore`](.distignore));
   - коммитит его в `trunk` SVN-репозитория плагина на WordPress.org;
   - создаёт в SVN тег, соответствующий git-тегу.

Для авторизации в SVN используются секреты репозитория **`SVN_USERNAME`** и **`SVN_PASSWORD`** (Settings → Secrets and variables → Actions). Slug плагина задан явно: `wpglobus-multilingual-comments`.

### Как сделать релиз

Полный пошаговый процесс, точки отката и все нюансы — в **[`RELEASE.md`](RELEASE.md)**. Это единый источник истины; при расхождениях верен он.

Релиз выполняется командой Claude Code, без ручных консольных команд:

> Задеплой версию 1.5.5
