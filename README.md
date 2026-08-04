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

### Пошаговая инструкция по релизу

1. **Обновите номер версии** в шапке `wpglobus-multilingual-comments.php`:
   ```php
   * Version: 1.5.5
   ```
2. **Обновите `readme.txt`**:
   - `Stable tag: 1.5.5` (должен совпадать с версией плагина и с тегом);
   - добавьте запись в `== Changelog ==` с описанием изменений.
3. **Закоммитьте изменения** в основную ветку:
   ```bash
   git add wpglobus-multilingual-comments.php readme.txt
   git commit -m "Release 1.5.5"
   git push origin main
   ```
4. **Создайте и запушьте git-тег**, совпадающий с номером версии:
   ```bash
   git tag 1.5.5
   git push origin 1.5.5
   ```
5. Push тега автоматически запускает Action. Прогресс и логи деплоя можно посмотреть во вкладке **Actions** репозитория на GitHub.
6. После успешного завершения workflow новая версия появится в SVN-репозитории плагина и станет доступна для обновления на WordPress.org.

### Важно

- Тег должен быть **числовым/семантическим** и соответствовать `Stable tag` из `readme.txt` — иначе версия на WordPress.org может не обновиться корректно.
- Если тег уже был запушен по ошибке, удаление и повторное создание тега не переиздаёт релиз автоматически — потребуется либо новый тег, либо ручное вмешательство в SVN.
- Секреты `SVN_USERNAME` / `SVN_PASSWORD` должны быть действующими учётными данными аккаунта WordPress.org, имеющего доступ к SVN-репозиторию плагина.
