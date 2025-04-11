<p align="center">
  <a href="https://laravel.com" target="_blank">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
  </a>
</p>

<p align="center">
  <a href="https://github.com/laravel/framework/actions">
    <img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status">
  </a>
  <a href="https://packagist.org/packages/laravel/framework">
    <img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads">
  </a>
  <a href="https://packagist.org/packages/laravel/framework">
    <img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version">
  </a>
  <a href="https://packagist.org/packages/laravel/framework">
    <img src="https://img.shields.io/packagist/l/laravel/framework" alt="License">
  </a>
</p>

---

## 1. Pré-requisitos

Certifique-se de ter os seguintes requisitos instalados:

- **PHP** (versão 8.2 ou superior)
- **Composer** (para gerenciar dependências do Laravel)
- **MySQL**
- **Node.js** e **npm** (para gerenciar dependências do frontend)

---

## 2. Clonar o repositório

```bash
git clone <https://github.com/AlineJesus/desafio_pagamento.git>
cd desafio_pagamento
```

---

## 3. Instalar dependências

Execute os seguintes comandos para instalar as dependências do projeto:

```bash
composer install
npm install
```

---

## 4. Configurar o ambiente

Copie o arquivo de exemplo `.env` e configure as variáveis de ambiente:

```bash
cp .env.example .env
```

Edite o arquivo `.env` conforme necessário:

```env
APP_NAME=DesafioPagamento
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=desafio_pagamento
DB_USERNAME=root
DB_PASSWORD=
```

---

## 5. Gerar a chave da aplicação

Gere a chave de criptografia do Laravel:

```bash
php artisan key:generate
```

---

## 6. Migrar e popular o banco de dados

Execute as migrações para criar as tabelas no banco de dados:

```bash
php artisan migrate
```

Popule o banco de dados com dados iniciais usando os seeders:

```bash
php artisan db:seed --class=UserSeeder
```

---

## 7. Executar o servidor

Inicie o servidor de desenvolvimento do Laravel:

```bash
php artisan serve
```

O projeto estará disponível em: [http://localhost:8000](http://localhost:8000)

---

## 8. Testar o projeto

Execute os testes automatizados do projeto:

```bash
php artisan test
