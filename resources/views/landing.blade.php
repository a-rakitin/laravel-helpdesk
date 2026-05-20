<!doctype html>
<html lang="en" data-theme="dark" data-lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Helpdesk API public landing page with documentation and project links.">
    <title>Helpdesk API</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Crect width='64' height='64' rx='14' fill='%23070b12'/%3E%3Cpath fill='%232dd4bf' d='M17 16h8v13h14V16h8v32h-8V35H25v13h-8z'/%3E%3C/svg%3E">
    <style>
        :root {
            color-scheme: dark;
            --bg: #070b12;
            --surface: #0d1420;
            --surface-raised: #111b2a;
            --line: #263244;
            --line-strong: #3a4658;
            --text: #f4f7fb;
            --muted: #9ba8ba;
            --soft: #c5d0dd;
            --teal: #2dd4bf;
            --teal-strong: #14b8a6;
            --amber: #f59e0b;
            --rose: #fb7185;
            --shadow: rgba(0, 0, 0, 0.36);
        }

        * {
            box-sizing: border-box;
        }

        html {
            background: var(--bg);
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            line-height: 1.5;
            color: var(--text);
            background:
                radial-gradient(circle at 30% 0%, rgba(45, 212, 191, 0.14), transparent 34rem),
                linear-gradient(180deg, #0a1019 0%, var(--bg) 46rem);
        }

        a {
            color: inherit;
        }

        .shell {
            width: min(1320px, calc(100% - 32px));
            margin: 0 auto;
            padding: 26px 0 32px;
        }

        .topbar {
            display: flex;
            justify-content: flex-end;
            margin-bottom: clamp(24px, 5vh, 44px);
        }

        .lang-toggle {
            display: inline-flex;
            gap: 4px;
            padding: 4px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: rgba(17, 27, 42, 0.78);
        }

        .lang-toggle button {
            min-width: 42px;
            min-height: 32px;
            border: 0;
            border-radius: 6px;
            color: var(--muted);
            background: transparent;
            cursor: pointer;
            font: inherit;
            font-size: 0.86rem;
            font-weight: 800;
        }

        .lang-toggle button[aria-pressed="true"] {
            color: #06221f;
            background: var(--teal);
        }

        .hero {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 28px;
            align-items: end;
        }

        h1 {
            margin: 0;
            font-size: clamp(2.7rem, 6.2vw, 5rem);
            line-height: 0.96;
            letter-spacing: 0;
            white-space: nowrap;
        }

        .summary {
            max-width: 900px;
            margin: 22px 0 0;
            color: var(--soft);
            font-size: clamp(1rem, 1.5vw, 1.16rem);
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 46px;
            padding: 0 18px;
            border-radius: 8px;
            font-weight: 850;
            text-decoration: none;
            white-space: nowrap;
        }

        .button.primary {
            border: 1px solid var(--teal-strong);
            color: #05201d;
            background: var(--teal);
            box-shadow: 0 14px 36px rgba(20, 184, 166, 0.18);
        }

        .button.github {
            border: 1px solid var(--line-strong);
            color: var(--text);
            background: rgba(17, 27, 42, 0.92);
        }

        .github-icon {
            width: 18px;
            height: 18px;
            fill: currentColor;
            flex: 0 0 auto;
        }

        .sections {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
            margin-top: clamp(34px, 7vh, 54px);
        }

        .panel {
            min-width: 0;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: rgba(17, 27, 42, 0.72);
            box-shadow: 0 20px 56px var(--shadow);
            min-height: 360px;
        }

        .panel-header {
            padding: 20px 22px 0;
            color: var(--muted);
            font-size: 0.8rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        .capabilities {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            padding: 22px;
        }

        .capability {
            min-width: 0;
            min-height: 130px;
            padding: 18px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: rgba(7, 11, 18, 0.42);
        }

        .capability strong {
            display: block;
            margin-bottom: 14px;
        }

        .capability-pill {
            display: inline-flex;
            align-items: center;
            width: fit-content;
            min-width: 0;
            max-width: 100%;
            padding: 4px 8px;
            border: 1px solid var(--line);
            border-radius: 999px;
            color: var(--teal);
            background: rgba(45, 212, 191, 0.07);
            font-size: 0.76rem;
            font-weight: 800;
            line-height: 1.35;
            white-space: normal;
            overflow-wrap: anywhere;
        }

        .resource-list {
            display: grid;
            gap: 12px;
            padding: 22px;
        }

        .resource {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 86px;
            gap: 16px;
            align-items: center;
            min-width: 0;
            min-height: 76px;
            padding: 16px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: rgba(7, 11, 18, 0.42);
            text-decoration: none;
        }

        .resource > span {
            min-width: 0;
        }

        .resource strong {
            display: block;
            margin-bottom: 4px;
            color: var(--text);
        }

        .resource span {
            display: block;
            overflow-wrap: anywhere;
            color: var(--muted);
            font-size: 0.93rem;
        }

        .resource .resource-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            justify-self: center;
            align-self: center;
            width: 86px;
            min-height: 34px;
            padding: 0 12px;
            border: 1px solid var(--line-strong);
            border-radius: 6px;
            color: var(--teal);
            font-size: 0.86rem;
            font-weight: 850;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
        }

        .resource:hover,
        .resource:focus-visible {
            border-color: var(--teal-strong);
        }

        .resource:hover .resource-action,
        .resource:focus-visible .resource-action,
        .resource .resource-action:hover,
        .resource .resource-action:focus-visible {
            color: #031917;
            background: var(--teal);
        }

        .resource .resource-action span {
            display: none;
            color: inherit;
        }

        code {
            color: var(--soft);
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
        }

        body [data-lang-block] {
            display: none;
        }

        body [data-lang-section] {
            display: none;
        }

        html[data-lang="en"] body [data-lang-block="en"],
        html[data-lang="ru"] body [data-lang-block="ru"] {
            display: inline;
        }

        html[data-lang="en"] .capability strong[data-lang-block="en"],
        html[data-lang="ru"] .capability strong[data-lang-block="ru"],
        html[data-lang="en"] .resource .resource-action [data-lang-block="en"],
        html[data-lang="ru"] .resource .resource-action [data-lang-block="ru"],
        html[data-lang="en"] .resource [data-lang-block="en"],
        html[data-lang="ru"] .resource [data-lang-block="ru"],
        html[data-lang="en"] .panel-header [data-lang-block="en"],
        html[data-lang="ru"] .panel-header [data-lang-block="ru"] {
            display: block;
        }

        html[data-lang="en"] .capability .capability-pill[data-lang-block="en"],
        html[data-lang="ru"] .capability .capability-pill[data-lang-block="ru"] {
            display: inline-flex;
        }

        @media (max-width: 1040px) {
            .hero,
            .sections {
                grid-template-columns: 1fr;
            }

            .actions {
                justify-content: flex-start;
            }

            h1 {
                white-space: normal;
            }
        }

        @media (max-width: 640px) {
            .shell {
                width: min(100% - 24px, 1320px);
                padding-top: 18px;
            }

            .topbar {
                margin-bottom: 24px;
            }

            .capabilities,
            .resource {
                grid-template-columns: 1fr;
            }

            .button,
            .resource .resource-action {
                width: 100%;
                justify-self: stretch;
            }

            .button,
            .capability-pill {
                white-space: normal;
                text-align: center;
            }

            .capability-pill {
                justify-content: center;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <main class="shell">
        <header class="topbar" aria-label="Page header">
            <div class="lang-toggle" data-lang-toggle aria-label="Language">
                <button type="button" data-lang-choice="en" aria-label="English" aria-pressed="true">EN</button>
                <button type="button" data-lang-choice="ru" aria-label="Русский" aria-pressed="false">RU</button>
            </div>
        </header>

        <section class="hero" aria-labelledby="page-title">
            <div>
                <h1 id="page-title">Helpdesk API</h1>
                <p class="summary">
                    <span data-lang-block="en">Helpdesk ticket workflow API with Sanctum authentication, role-based access, ticket comments, notifications, PostgreSQL, Redis, and Scramble-generated OpenAPI docs.</span>
                    <span data-lang-block="ru">API для работы с helpdesk-тикетами: Sanctum-аутентификация, ролевой доступ, комментарии, уведомления, PostgreSQL, Redis и OpenAPI-документация через Scramble.</span>
                </p>
            </div>

            <nav class="actions" aria-label="Primary links">
                <a class="button primary" href="/docs/api">
                    <span data-lang-block="en">Open docs</span>
                    <span data-lang-block="ru">Открыть документацию</span>
                </a>
                <a class="button github" href="https://github.com/a-rakitin/laravel-helpdesk" rel="noopener noreferrer">
                    <svg class="github-icon" aria-hidden="true" viewBox="0 0 16 16">
                        <path d="M8 0C3.58 0 0 3.67 0 8.2c0 3.63 2.29 6.7 5.47 7.78.4.08.55-.18.55-.39 0-.19-.01-.84-.01-1.53-2.01.38-2.53-.5-2.69-.96-.09-.23-.48-.96-.82-1.15-.28-.15-.68-.52-.01-.53.63-.01 1.08.59 1.23.84.72 1.24 1.87.89 2.33.68.07-.53.28-.89.51-1.09-1.78-.21-3.64-.91-3.64-4.04 0-.89.31-1.62.82-2.19-.08-.21-.36-1.04.08-2.16 0 0 .67-.22 2.2.84A7.39 7.39 0 0 1 8 4.02c.68 0 1.36.09 2 .27 1.53-1.06 2.2-.84 2.2-.84.44 1.12.16 1.95.08 2.16.51.57.82 1.3.82 2.19 0 3.14-1.87 3.83-3.65 4.04.29.26.54.75.54 1.52 0 1.1-.01 1.99-.01 2.26 0 .21.15.47.55.39A8.16 8.16 0 0 0 16 8.2C16 3.67 12.42 0 8 0Z"/>
                    </svg>
                    <span>GitHub</span>
                </a>
            </nav>
        </section>

        <section class="sections" aria-label="Project details">
            <div class="panel">
                <div class="panel-header">
                    <span data-lang-block="en">API capabilities</span>
                    <span data-lang-block="ru">Возможности API</span>
                </div>
                <div class="capabilities">
                    <div class="capability">
                        <strong data-lang-block="en">Auth</strong>
                        <strong data-lang-block="ru">Аутентификация</strong>
                        <code class="capability-pill" data-lang-block="en">Register / Login / Bearer token</code>
                        <code class="capability-pill" data-lang-block="ru">Регистрация / Вход / Bearer-токен</code>
                    </div>
                    <div class="capability">
                        <strong data-lang-block="en">Tickets</strong>
                        <strong data-lang-block="ru">Тикеты</strong>
                        <code class="capability-pill" data-lang-block="en">Create / Assign / Comment</code>
                        <code class="capability-pill" data-lang-block="ru">Создание / Назначение / Комментарии</code>
                    </div>
                    <div class="capability">
                        <strong data-lang-block="en">Roles</strong>
                        <strong data-lang-block="ru">Роли</strong>
                        <code class="capability-pill" data-lang-block="en">Admin / Agent / Customer</code>
                        <code class="capability-pill" data-lang-block="ru">Администратор / Агент / Клиент</code>
                    </div>
                    <div class="capability">
                        <strong data-lang-block="en">Infrastructure</strong>
                        <strong data-lang-block="ru">Инфраструктура</strong>
                        <code class="capability-pill" data-lang-block="en">PHP 8.4 / PostgreSQL / Redis / Nginx</code>
                        <code class="capability-pill" data-lang-block="ru">PHP 8.4 / PostgreSQL / Redis / Nginx</code>
                    </div>
                </div>
            </div>

            <div class="panel">
                <div class="panel-header">
                    <span data-lang-block="en">Project resources</span>
                    <span data-lang-block="ru">Ресурсы проекта</span>
                </div>
                <div class="resource-list">
                    <a class="resource" href="/docs/api.json">
                        <span>
                            <strong>OpenAPI JSON</strong>
                            <span data-lang-block="en">Machine-readable API contract.</span>
                            <span data-lang-block="ru">Машиночитаемый контракт API.</span>
                        </span>
                        <span class="resource-action">
                            <span data-lang-block="en">Open</span>
                            <span data-lang-block="ru">Открыть</span>
                        </span>
                    </a>
                    <a
                        class="resource"
                        href="https://github.com/a-rakitin/laravel-helpdesk#local-setup"
                        data-local-setup-link
                        data-local-setup-href-en="https://github.com/a-rakitin/laravel-helpdesk#local-setup"
                        data-local-setup-href-ru="https://github.com/a-rakitin/laravel-helpdesk/blob/main/README.ru.md#локальный-запуск"
                        rel="noopener noreferrer"
                    >
                        <span>
                            <strong data-lang-block="en">Local setup</strong>
                            <strong data-lang-block="ru">Локальный запуск</strong>
                            <span data-lang-block="en">Docker-based local setup.</span>
                            <span data-lang-block="ru">Локальный запуск через Docker.</span>
                        </span>
                        <span class="resource-action">
                            <span data-lang-block="en">Open</span>
                            <span data-lang-block="ru">Открыть</span>
                        </span>
                    </a>
                    <a class="resource" href="https://github.com/a-rakitin/laravel-helpdesk/tree/main/postman" rel="noopener noreferrer">
                        <span>
                            <strong data-lang-block="en">Postman assets</strong>
                            <strong data-lang-block="ru">Postman коллекция</strong>
                            <span data-lang-block="en">Ready-to-import API collection.</span>
                            <span data-lang-block="ru">Готовая коллекция API-запросов.</span>
                        </span>
                        <span class="resource-action">
                            <span data-lang-block="en">Open</span>
                            <span data-lang-block="ru">Открыть</span>
                        </span>
                    </a>
                </div>
            </div>
        </section>
    </main>

    <script>
        (() => {
            const root = document.documentElement;
            const choices = Array.from(document.querySelectorAll('[data-lang-choice]'));
            const localSetupLink = document.querySelector('[data-local-setup-link]');
            const saved = window.localStorage.getItem('landing-language');
            const browserLanguage = navigator.language && navigator.language.toLowerCase().startsWith('ru') ? 'ru' : 'en';
            const initialLanguage = saved || browserLanguage;

            const applyLanguage = (language) => {
                const nextLanguage = language === 'ru' ? 'ru' : 'en';
                root.dataset.lang = nextLanguage;
                root.lang = nextLanguage;
                window.localStorage.setItem('landing-language', nextLanguage);

                choices.forEach((choice) => {
                    choice.setAttribute('aria-pressed', String(choice.dataset.langChoice === nextLanguage));
                });

                if (localSetupLink) {
                    localSetupLink.href = nextLanguage === 'ru'
                        ? localSetupLink.dataset.localSetupHrefRu
                        : localSetupLink.dataset.localSetupHrefEn;
                }
            };

            choices.forEach((choice) => {
                choice.addEventListener('click', () => applyLanguage(choice.dataset.langChoice));
            });

            applyLanguage(initialLanguage);
        })();
    </script>
</body>
</html>
