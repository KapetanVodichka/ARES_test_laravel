# ARES Test Laravel Project
Этот проект представляет собой веб-приложение, созданное на фреймворке Laravel, которое выполняет парсинг данных получаемых по API.
Использовалась БД sqlite просто для хранения сессий.
Присутствует сортировка, пагинация, фильтрация.

Ссылка на ТЗ: https://docs.google.com/document/d/1y5aPhk8Na8Hl1eVJKx1GJaZ5p3U25G9vdn87925mV2Q/edit?tab=t.0

## Требования
- **PHP**: Версия 8.2 или выше. (Использовался Laravel 12, но можно было использоать версии ниже чтобы снизить планку версии PHP. Не было в требованиях)

## Установка и запуск

### 1. Клонирование репозитория
```bash
git clone https://github.com/KapetanVodichka/ARES_test_laravel.git
cd ARES_test_laravel
```

### 2. Установка зависимостей
```bash
composer install
```

### 3. Копирование .env и генерация ключа приложения
```bash
copy .env.example .env  # Windows
# или
cp .env.example .env   # Linux/Mac
```
```bash
php artisan key:generate
```

### 4. Создание и миграция БД
При использовании команды миграции терминал укажет на отсутстие файла database/database.sqlite и предложит его создать (выбираем yes после команды migrate):

```bash
php artisan migrate
```

### 5. Запуск приложения
```bash
php artisan serve
```

Парсер будет доступен по адресу [http://127.0.0.1:8000]
