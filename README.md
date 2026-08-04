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

Релиз выполняется командой Claude Code, без ручных консольных команд:

> Задеплой версию 1.5.5

По этой команде Claude:

1. Обновит номер версии в шапке `wpglobus-multilingual-comments.php`.
2. Обновит `readme.txt` (`Stable tag` и запись в `== Changelog ==`).
3. Закоммитит и запушит изменения в основную ветку.
4. Создаст git-тег, совпадающий с номером версии, и запушит его.
5. Push тега автоматически запускает Action. Прогресс и логи деплоя доступны во вкладке **Actions** репозитория на GitHub.
6. После успешного завершения workflow новая версия появится в SVN-репозитории плагина и станет доступна для обновления на WordPress.org.

Перед пушем тега Claude уточняет номер версии и подтверждение — это необратимое действие (публикация релиза).

### Важно

- Тег должен быть **числовым/семантическим** и соответствовать `Stable tag` из `readme.txt` — иначе версия на WordPress.org может не обновиться корректно.
- Если тег уже был запушен по ошибке, удаление и повторное создание тега не переиздаёт релиз автоматически — потребуется либо новый тег, либо ручное вмешательство в SVN.
- Секреты `SVN_USERNAME` / `SVN_PASSWORD` должны быть действующими учётными данными аккаунта WordPress.org, имеющего доступ к SVN-репозиторию плагина.
