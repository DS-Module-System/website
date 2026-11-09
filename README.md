# Website

Публичен CMS към ERP-то: многоезично меню, типове страници, блог, контактна форма, документация, sitemap и robots.

## Функционалност

- Админ дърво на менюто (добавяне, пренареждане, редакция, кеш)
- Типове страници: начална, статична, блог, контакт, пренасочване (STI + езикови записи)
- Блог в админа и публичен списък/статия
- Контактна форма през `MailService` на core
- Публична документация (`/documentation`)
- `sitemap.xml` и `robots.txt`
- Динамично CMS маршрутизиране (`CmsLoader`)

## Интеграция в системата

Copy-in модул: файловете се копират в хоста под `App\`.

- Пътища: `src/Controller|Entity|Form|Repository|Service|Routing/Website/`, `templates/website/`, `translations/website/`, `assets/`
- Меню в админа: Уебсайт → Менюта (`website_menu_tree`), Блог (`website_blog_list`) — без отделна role YAML
- Маршрути: `/website/menus`, `/website/blog`, публични CMS страници, `/documentation`, `/sitemap.xml`, `/robots.txt`
- В хоста: `config/services.yaml` (`page_modules`), `config/routes.yaml` (`type: cms`)

Няма `docs/` и няма `config/roles/` в това репо. Файловете на страниците ползват core `File` / `FileAssociation`.

## Структура

- Админ: `MenuController`, `BlogController`
- Frontend: `HomePageController`, `StaticPageController`, `BlogPageController`, `ContactPageController`, `RedirectPageController`, `DocumentationController`, `SitemapController`, `RobotsController`
- Ентитети: `Menu`, `MenuLng`, `*Page` / `*PageLng`, `Blog`, `MenuImage`
- Услуги: `MenuService`, `CMSUtils`, `DocumentationCatalog`, `DocumentationRenderer`, `WebsiteSitemapBuilder`
- Routing: `CmsLoader`

## Зависимости

- **erp-core** — файлове, форми, `MailService`
