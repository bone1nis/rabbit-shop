# RabbitShop

**RabbitShop** — учебное приложение для практики работы с RabbitMQ и микросервисной архитектурой.  
Полностью разворачивается и запускается с помощью **Docker**.

---

## Технологический стек

- **Backend:** Laravel 12
- **База данных:** PostgreSQL
- **Сообщения и очередь:** RabbitMQ
- **Инфраструктура:** Docker, Docker Compose

---

## Основной функционал

### Микросервисы и их ответственность

- **Order Service**  
  Управляет заказами: создание, обновление, получение информации о заказах.  
  При создании заказа публикует событие в RabbitMQ для других сервисов.

- **Notification Service**  
  Получает события из RabbitMQ и отвечает за отправку уведомлений пользователям (например, о новых заказах или изменениях).

- **Stock Service**  
  Отвечает за управление складскими запасами: проверка доступности товаров, резервирование и обновление остатков.  
  Получает события о новых заказах и обновляет количество на складе.

### Взаимодействие между сервисами

- Сервисы обмениваются сообщениями через RabbitMQ, что обеспечивает асинхронную и надёжную коммуникацию.
- Каждый сервис имеет свою базу данных PostgreSQL для независимости и масштабируемости.
- Использование Docker и Docker Compose упрощает запуск и тестирование всей системы.
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
cp order-service/.env.example order-service/.env
cp notification-service/.env.example notification-service/.env
cp stock-service/.env.example stock-service/.env
```

Настройте параметры в order-service/.env:

```
APP_NAME=OrderService
APP_ENV=local
APP_KEY=base64:your_app_key_here
APP_DEBUG=true
APP_URL=http://localhost:8001

DB_CONNECTION=pgsql
DB_HOST=postgres-order
DB_PORT=5432
DB_DATABASE=orders_db
DB_USERNAME=user
DB_PASSWORD=pass

RABBITMQ_HOST=rabbitmq
RABBITMQ_PORT=5672
RABBITMQ_USER=guest
RABBITMQ_PASSWORD=guest
RABBITMQ_QUEUE=orders_queue
```

Настройте параметры в notification-service/.env:

```
APP_NAME=NotificationService
APP_ENV=local
APP_KEY=base64:your_app_key_here
APP_DEBUG=true
APP_URL=http://localhost:8002

DB_CONNECTION=pgsql
DB_HOST=postgres-notification
DB_PORT=5432
DB_DATABASE=notification_db
DB_USERNAME=user
DB_PASSWORD=pass

RABBITMQ_HOST=rabbitmq
RABBITMQ_PORT=5672
RABBITMQ_USER=guest
RABBITMQ_PASSWORD=guest
RABBITMQ_QUEUE=notification_queue
```

Настройте параметры в stock-service/.env:

```
APP_NAME=StockService
APP_ENV=local
APP_KEY=base64:your_app_key_here
APP_DEBUG=true
APP_URL=http://localhost:8003

DB_CONNECTION=pgsql
DB_HOST=postgres-stock
DB_PORT=5432
DB_DATABASE=stock_db
DB_USERNAME=user
DB_PASSWORD=pass

RABBITMQ_HOST=rabbitmq
RABBITMQ_PORT=5672
RABBITMQ_USER=guest
RABBITMQ_PASSWORD=guest
RABBITMQ_QUEUE=stock_queue
```

### 3. Запуск через Docker Compose

Соберите и поднимите контейнеры в фоновом режиме:

```bash
docker-compose up --build -d
```

### 4. Инициализация базы данных и миграции

Для каждого микросервиса выполните миграции и сиды:

```bash
docker exec -it order-service php artisan migrate --seed
docker exec -it notification-service php artisan migrate --seed
docker exec -it stock-service php artisan migrate --seed
```

### 5. Настройка APP_KEY

Для безопасности необходимо создать секретный ключ внутри каждого из микросервисов:

```bash
docker exec -it order-service php artisan key:generate
docker exec -it notification-service php artisan key:generate
docker exec -it stock-service php artisan key:generate
```