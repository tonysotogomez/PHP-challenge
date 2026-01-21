# Challenges de Refactorización y Arquitectura - PHP

Este repositorio contiene la resolución de dos retos técnicos enfocados en Clean Code, Patrones de Diseño y contenedores Docker.

## Requisitos Previos
* Docker y Docker Compose instalados.
* Git.

## Instalación y Puesta en Marcha

1. Clonar repositorio

2. Levantar servicios
- docker-compose up -d

3. Instalar dependencias

# Challenge 01
- docker-compose exec game-01 composer install
- docker-compose exec game-01 php artisan key:generate
- docker-compose exec game-01 php artisan migrate --seed
# Opcional: Generar datos masivos para probar optimización
- docker-compose exec game-01 php artisan db:seed --class=MassiveDataSeeder
Acceso en http://localhost:8090

# Opcional: permisos
docker-compose exec game-01 chmod -R 777 storage bootstrap/cache


# Challenge 02
docker-compose exec game-02 composer install
docker-compose exec game-02 composer tests