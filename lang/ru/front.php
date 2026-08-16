<?php

/**
 * Basic Russian translation of the public site.
 *
 * Covers the navigation, hero, search, listing, footer and membership strings —
 * everything visible on the pages the design specifies for the three-language
 * switcher. Keys not listed here fall back to `app.fallback_locale`, so nothing
 * breaks; the remainder can be filled in from Nastavení → Překlady.
 */
return [

    'common' => [
        'dash' => '—',
        'not_specified' => 'Не указано',
    ],

    'membership' => [
        'period_days' => '{1} :count день|[2,4] :count дня|[5,*] :count дней',
        'period_months' => '{1} :count месяц|[2,4] :count месяца|[5,*] :count месяцев',
        'period_years' => '{1} :count год|[2,4] :count года|[5,*] :count лет',
        'activated' => 'Premium-членство активно.',
        'activation_pending' => 'Платёж получен. Членство активируется в ближайшее время.',
        'not_verified' => 'Не удалось подтвердить платёж. Если деньги были списаны, свяжитесь с нами.',
        'checkout_cancelled' => 'Платёж отменён.',
        'valid_until' => 'Ваше Premium-членство действует до :date',
        'locked_rating' => 'Рейтинг открывается с Premium-членством',
    ],

    'landing' => [
        'wearecommunity' => 'Мы сообщество людей,',
        'fucking' => 'которые любят секс.',
        'fucking_prefix' => 'которые любят',
        'fucking_keyword' => 'секс',
        'girlsregisternow' => 'Девушки, зарегистрируйтесь сегодня<br>и найдите новых клиентов.',
        'girls_registered' => 'девушек зарегистрировано',
        'gents_registered' => 'мужчин зарегистрировано',
    ],

    'nav' => [
        'home' => 'Главная',
        'vip' => 'VIP и Premium',
        'faq' => 'Вопросы и ответы',
        'ethics' => 'Этика',
        'contact' => 'Контакты',
        'register' => 'Регистрация',
        'login' => 'Вход',
        'logout' => 'Выйти',
        'logout_mobile' => 'Выйти',
        'notifications' => 'Уведомления',
        'czech' => 'Чешский',
        'english' => 'Английский',
    ],

    'footer' => [
        'registration' => 'Регистрация',
        'faq' => 'Вопросы и ответы',
        'contact' => 'Контакты',
        'privacy' => 'Защита персональных данных',
        'ethics' => 'Этика',
        'vipgirls' => 'VIP-девушки',
        'premiummale' => 'Premium для мужчин',
        'discreet' => 'Дискретно и безопасно',
        'ecological' => 'Наш проект экологичен',
        'verification' => '— благодаря проверке девушек вы не поедете напрасно',
        'copyright' => '© ZašukejSi.cz',
        'logo_primary' => 'ZAŠUKEJ',
        'logo_accent' => 'SI',
        'logo_suffix' => '.CZ',
    ],

    'countries' => [
        'title' => 'Просмотр по странам',
        'browse_by' => 'Просмотр по',
        'countries_text' => 'странам',
        'subtitle' => 'Выберите страну и регион.',
        'all_profiles' => 'Все анкеты',
    ],

    'notifications' => [
        'archived_title' => 'Архив уведомлений',
        'no_notifications' => 'Нет уведомлений',
        'no_archived' => 'Нет архивных уведомлений',
        'view_archived' => 'Показать архив уведомлений',
        'archive' => 'В архив',
        'mark_all_read' => 'Отметить все как прочитанные',
        'delete_permanently' => 'Удалить навсегда',
        'received' => 'Получено',
        'archived' => 'В архиве',
        'go_back' => 'Назад',
    ],

    'messages' => [
        'cannot_message_self' => 'Нельзя написать самому себе.',
        'message_sent' => 'Сообщение отправлено',
        'you_prefix' => 'Вы:',
        'unread_one' => 'непрочитанное сообщение',
        'unread_few' => 'непрочитанных сообщения',
        'unread_many' => 'непрочитанных сообщений',
        'today' => 'Сегодня',
        'yesterday' => 'Вчера',
        'read' => 'Прочитано',
        'delivered' => 'Доставлено',
        'view_profile' => 'Открыть профиль',
        'conversations_count' => 'Диалогов: :count',
        'deleted_user' => 'Удалённый пользователь',
        'send_hint' => 'Отправить можно и клавишами Ctrl + Enter.',
    ],

    'favorites' => [
        'save' => 'сохранить',
        'add' => 'В избранное',
        'remove' => 'Убрать из избранного',
        'removed' => 'Убрано из избранного',
        'error' => 'Не удалось сохранить',
    ],

    'profiles' => [
        'search' => [
            'title' => 'Найдите себе компанию...',
            'girls' => 'девушек',
            'men' => 'мужчин',
            'registered' => 'зарегистрировано',
            'select_country' => 'Выберите страну',
            'country_and_town' => 'Страна и город',
        ],

        'list' => [
            'detail' => 'Подробнее',
            'years' => 'лет',
            'rating' => 'Рейтинг:',
            'verified' => 'ПРОВЕРЕНО',
            'online' => 'ОНЛАЙН',
            'topresults' => 'Лучшие результаты',
            'nofound' => 'Ничего не найдено',
            'age_18_25' => '18–25 лет',
            'age_26_30' => '26–30 лет',
            'age_31_35' => '31–35 лет',
            'age_36_40' => '36–40 лет',
            'age_40_50' => '40–50 лет',
            'age_50_plus' => '50 лет +',
        ],

        'detail_page' => [
            'about_me' => 'Обо мне',
            'my_prices' => 'Мои цены',
            'services' => 'Услуги',
            'time' => 'Время',
            'age' => 'Возраст',
            'years' => 'лет',
            'weight' => 'Вес',
            'height' => 'Рост',
            'bust' => 'Грудь',
            'languages' => 'Языки',
            'kg' => 'кг',
            'cm' => 'см',
            'lbs' => 'фунтов',
            'report_profile' => 'Пожаловаться на анкету',
            'give_rating' => 'Оценить',
            'refresh_access' => 'Восстановить доступ',
            'premium_unlocks_rating' => 'Premium-аккаунт откроет рейтинг',
            'top_rated_girls' => 'Лучшие по рейтингу девушки',
            'this_month' => 'в этом месяце',
        ],
    ],

    'account' => [
        'reviews' => [
            'average' => 'Средняя оценка',
            'total' => 'Количество оценок',
            'anonymous' => 'Анонимный пользователь',
            'stars' => ':count из 5 звёзд',
            'empty_hint' => 'Оценки появятся здесь, когда участники вас оценят.',
        ],

        'statistics' => [
            'page_title' => 'Статистика',
            'no_profile' => 'Статистика появится, когда вы создадите анкету.',
        ],

        'completion' => [
            'prompt' => 'Для завершения анкеты не хватает:',
            'about' => 'текста о себе',
            'photos' => 'фотографий',
            'prices' => 'цен',
            'services' => 'услуг',
        ],

        'member' => [
            'messages' => 'Мои сообщения',
            'favorites' => 'Избранное',
            'ratings' => 'Оценки девушек',
            'girls_of_month' => 'Девушки месяца',
            'archive' => 'Архив девушек',
            'reported' => 'Жалобы',
            'settings' => 'Основные настройки',
        ],
    ],

];
