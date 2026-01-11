# BaksDev Profile Balance

[![Version](https://img.shields.io/badge/version-7.3.3-blue)](https://github.com/baks-dev/users-profile-balance/releases)
![php 8.4+](https://img.shields.io/badge/php-min%208.4-red.svg)
[![packagist](https://img.shields.io/badge/packagist-green)](https://packagist.org/packages/baks-dev/users-profile-balance)

Баланс профилей пользователя

## Установка

``` bash
$ composer require baks-dev/users-profile-balance
```

## Дополнительно

Установка конфигурации и файловых ресурсов:

``` bash
php bin/console baks:assets:install
```

Рекомендуется в composer.json проекта добавить в секцию автоматическое выполнение

``` json
"scripts": {
    "auto-scripts": {
        "baks:assets:install": "symfony-cmd",
        "baks:cache:clear": "symfony-cmd"
    },
    "post-install-cmd": [
        "@auto-scripts"
    ],
    "post-update-cmd": [
        "@auto-scripts"
    ]
}
```

Изменения в схеме базы данных с помощью миграции

``` bash
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

## Тестирование

``` bash
php bin/phpunit --group=users-profile-balance
```

## Лицензия ![License](https://img.shields.io/badge/MIT-green)

The MIT License (MIT). Обратитесь к [Файлу лицензии](LICENSE.md) за дополнительной информацией.
