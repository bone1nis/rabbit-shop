# RabbitShop

**RabbitShop** — учебное приложение для практики микросервисной архитектуры с использованием **RabbitMQ**.  
Полностью разворачивается и запускается с помощью **Docker Compose**.

---

## Технологический стек

- **Backend:** Laravel 12
- **База данных:** PostgreSQL
- **Брокер сообщений:** RabbitMQ
- **Инфраструктура:** Docker, Docker Compose

---

## Основной функционал

### Микросервисы и их ответственность

#### Order Service
- Управление заказами: создание, обновление, получение.
- При создании заказа публикует событие в RabbitMQ для других сервисов.

#### Notification Service
- Получает события из RabbitMQ.
- Отправляет уведомления пользователям по электронной почте.
- **Примечание:** на текущий момент не использует базу данных.

#### Stock Service
- Управление складскими запасами: проверка доступности, обновление остатков.
- Получает события о заказах и обновляет данные на складе.

---

## Взаимодействие между сервисами

- Все микросервисы обмениваются сообщениями через **RabbitMQ**, что обеспечивает:
  - Асинхронность
  - Надёжность
  - Независимость сервисов
- **Order Service** и **Stock Service** используют собственные экземпляры PostgreSQL для масштабируемости.
- Вся система разворачивается через **Docker Compose** одной командой.

---

## Установка и запуск проекта

### 1. Клонирование репозитория

```bash
git clone https://github.com/bone1nis/rabbit-shop.git
cd rabbit-shop
```

### 2. Конфигурация окружения

Скопируйте файлы .env:

```bash
cp ./.env.example ./.env
cp order-service/src/.env.example order-service/src/.env
cp notification-service/src/.env.example notification-service/src/.env
cp stock-service/src/.env.example stock-service/src/.env
```

Настройте .env в корне проекта:

```
# Узнать значения можно с помощью команд:
# id -u  — покажет UID
# id -g  — покажет GID

UID=1000
GID=1000
```

#### Настройка уведомлений через Email

Для отправки писем используется Mailtrap, который позволяет безопасно тестировать email-уведомления в локальной среде.

1. Зарегистрируйтесь на сайте mailtrap.io.
2. Перейдите во вкладку For developers → Sandbox → Inboxes.
3. Нажмите Add Inbox, чтобы создать новый инбокс.
4. Перейдите в созданный инбокс и откройте вкладку SMTP Settings.
5. Скопируйте username и password, вставьте их в notification-service/src/.env:

```
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
```

### 3. Запуск через Docker Compose

Соберите и поднимите контейнеры в фоновом режиме:

```bash
docker-compose up --build -d
```

### 4. Инициализация базы данных и миграции

Для микросервисов order и notification пропишите миграции и сиды

```bash
docker exec -it order-service php artisan migrate --seed
docker exec -it stock-service php artisan migrate --seed
```

### 5. Настройка APP_KEY

Для безопасности необходимо создать секретный ключ внутри каждого из микросервисов:

```bash
docker exec -it order-service php artisan key:generate
docker exec -it notification-service php artisan key:generate
docker exec -it stock-service php artisan key:generate
```