## Парсинг xml файлов заданного формата, сохранние в БД
## Настройка для linux
- Склонировать репозиторий
- Установить пакеты
```
composer install
```
- Создать файл .env, скопировать туда содержимое файла .env.example
- Подключиться к свей БД (прописать в .env подключение) или использовать существующую:
```
в корне проекта выполнить команды:
cd mysql/
docker-compose up -d
```
 - Накатить миграции
```
в корне проекта выполнить команду:
php artisan migrate
```
## Примеры файлов для парсинга находятся:
- storage/dataForParsing/data.xml, файл по default, парсинг командой:
```
php artisan parsing:parse-auto-catalog
```
- storage storage/dataForParsing/data_changed.xml, в качестве аргумента можно передать путь к файлу
```
php artisan parsing:parse-auto-catalog storage/data_changed.xml
```
## Что можно доработать:
- Вынести часть логики в сервисы и работу с БД в репозиторий
- Сохранять обработанные файлы в архивной папке