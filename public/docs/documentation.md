# Documentação do Sistema de Transferência de Valores

## Visão Geral

Este sistema permite a transferência de valores entre usuários, com validações específicas para diferentes tipos de usuários e notificações automáticas. Ele foi desenvolvido utilizando o framework Laravel.

---

## Funcionalidades Principais

1. **Transferência de Valores**:
   - Apenas usuários do tipo "common" podem realizar transferências.
   - O sistema verifica o saldo do pagador antes de realizar a transferência.
   - Transações são registradas no banco de dados.

2. **Validação de Documentos**:
   - Usuários do tipo "common" devem possuir um CPF válido.
   - Usuários do tipo "shopkeeper" devem possuir um CNPJ válido.

3. **Notificações**:
   - Após uma transferência bem-sucedida, o recebedor é notificado via e-mail.

4. **Serviço Autorizador**:
   - Antes de realizar a transferência, o sistema consulta um serviço externo para autorização.

---

## Estrutura do Projeto

### Diretórios Principais

- **app/Models**:
  - Contém os modelos `User` e `Transaction`, que representam os usuários e as transações no sistema.

- **app/Services**:
  - `TransferService`: Lida com a lógica de transferência de valores.
  - `NotificationService`: Gerencia o envio de notificações.

- **app/Http/Controllers**:
  - `TransactionController`: Controlador responsável por gerenciar as transferências.

- **app/Http/Requests**:
  - `StoreUserRequest`: Valida os dados ao criar um novo usuário.
  - `TransferRequest`: Valida os dados ao realizar uma transferência.

- **database/migrations**:
  - Contém as migrações para criar as tabelas `users` e `transactions`.

- **database/seeders**:
  - `UserSeeder`: Popula o banco de dados com usuários iniciais.

---

## Fluxo de Transferência

1. O usuário envia uma requisição para a API com os dados da transferência.
2. O `TransactionController` valida a requisição e chama o `TransferService`.
3. O `TransferService`:
   - Verifica o tipo do pagador e seu saldo.
   - Consulta o serviço autorizador externo.
   - Realiza a transferência dentro de uma transação do banco de dados.
   - Envia uma notificação ao recebedor.

---

## Configuração do Ambiente

1. Copie o arquivo `.env.example` para `.env` e configure as variáveis de ambiente.
2. Execute as migrações e os seeders:
   ```bash
   php artisan migrate
   php artisan db:seed --class=UserSeeder
   ```

---

## Testes

Execute os testes automatizados para garantir o funcionamento correto do sistema:
```bash
php artisan test
```

---

## Endpoints da API

### Transferência de Valores

- **URL**: `/api/transfer`
- **Método**: `POST`
- **Parâmetros**:
  - `value`: Valor da transferência (float).
  - `payer`: ID do pagador (int).
  - `payee`: ID do recebedor (int).
- **Respostas**:
  - `200`: Transferência realizada com sucesso.
  - `422`: Erro de validação ou saldo insuficiente.

---

## Requisitos

- **PHP**: Versão 8.2 ou superior.
- **Composer**: Para gerenciar dependências.
- **MySQL**: Banco de dados.
- **Node.js** e **npm**: Para dependências do frontend.

---

## Observações

- Certifique-se de que o serviço autorizador externo esteja acessível.
- Apenas usuários com documentos válidos podem ser criados ou realizar transferências.
