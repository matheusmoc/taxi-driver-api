# API de Corridas de Táxi

Esta API permite gerenciar corridas de táxi, permitindo que passageiros solicitem corridas e motoristas as iniciem e finalizem. A API foi desenvolvida em [PHP/Laravel] e utiliza um banco de dados relacional [MySQL] para persistência dos dados.

## Funcionalidades

A API oferece os seguintes recursos:

1. **Cadastro de Passageiros e Motoristas**
2. **Solicitação de Corrida** - Passageiros podem solicitar corridas com o status inicial "Aguardando Motorista".
3. **Atualização do Status de Corrida** - Motoristas podem iniciar corridas (status "Em Andamento") e finalizá-las (status "Finalizada").
4. **Consulta de Corrida** - Permite visualizar detalhes de uma corrida específica.

## Endpoints

### Passageiros

- **POST /passengers** - Cadastrar um novo passageiro.
  - **Request Body**:
    ```json
    {
        "nome": "Matheus",
        "telefone": "38992709671",
        "updated_at": "2024-11-11T00:41:11.000000Z",
        "created_at": "2024-11-11T00:41:11.000000Z",
        "id": 1
    }
    ```
  - **Response**: Status 201 e JSON com os dados do passageiro cadastrado.

### Motoristas

- **POST /drivers** - Cadastrar um novo motorista.
  - **Request Body**:
    ```json
    {
        "nome": "Motorista 1",
        "carro": "sedan",
        "telefone": "999999999",
        "updated_at": "2024-11-11T00:41:52.000000Z",
        "created_at": "2024-11-11T00:41:52.000000Z",
        "id": 1
    }
    ```
  - **Response**: Status 201 e JSON com os dados do motorista cadastrado.

### Corridas

- **POST /rides** - Criar uma nova corrida.
  - **Request Body**:
    ```json
    {
        "passenger_id": 1,
        "status": "Em Andamento",
        "driver_id": "1",
        "origem": "Origem 1",
        "destino": "Destino 1",
        "valor": "30.00",
        "data_hora_solicitacao": "2024-11-11T00:42:28.264023Z",
        "data_hora_inicio": "2024-11-11T00:42:28.264032Z",
        "updated_at": "2024-11-11T00:42:28.000000Z",
        "created_at": "2024-11-11T00:42:28.000000Z",
        "id": 1
    }
    ```
  - **Response**: Status 201 e JSON com os dados da corrida criada.

- **PATCH /rides/{id}** - Atualizar o status de uma corrida.
  - **Request Body**:
    ```json
    {
        "message": "Dados atualizados com sucesso",
        "ride": {
            "id": 1,
            "passenger_id": 1,
            "driver_id": 1,
            "status": "Finalizada",
            "origem": "Origem 1",
            "destino": "Destino 1",
            "data_hora_solicitacao": "2024-11-11 00:42:28",
            "data_hora_inicio": "2024-11-11 00:42:28",
            "data_hora_fim": "2024-11-11 00:48:39",
            "valor": "30.00",
            "created_at": "2024-11-11T00:42:28.000000Z",
            "updated_at": "2024-11-11T00:48:39.000000Z"
        }
    }
    ```
  - **Response**: Status 200 e JSON com os dados atualizados da corrida.

- **GET /rides/{id}** - Listar os detalhes de uma corrida específica.
  - **Response**: Status 200 e JSON com os detalhes da corrida.
      ```json
    {
        "id": 1,
        "passenger_id": 1,
        "driver_id": 1,
        "status": "Finalizada",
        "origem": "Origem 1",
        "destino": "Destino 1",
        "data_hora_solicitacao": "2024-11-11 00:42:28",
        "data_hora_inicio": "2024-11-11 00:42:28",
        "data_hora_fim": "2024-11-11 00:48:39",
        "valor": "100.00",
        "created_at": "2024-11-11T00:42:28.000000Z",
        "updated_at": "2024-11-11T00:48:39.000000Z"
    }
    ```
## Regras de Negócio

1. **Criação de Corrida**: Uma corrida só pode ser criada se o passageiro existir.
   ![image](https://github.com/user-attachments/assets/afe48c5b-5181-47e5-94ff-51809c210216)

3. **Status "Em Andamento"**: Pode ser definido apenas se o status atual for "Aguardando Motorista", e é necessário informar o `motorista_id`.
4. **Status "Finalizada"**: Pode ser definido apenas se o status atual for "Em Andamento".

   ![image](https://github.com/user-attachments/assets/6ac9b272-fbde-4707-9b36-fe8d0a3e1ef1)


## Instalação e Execução

### Pré-requisitos

- Docker e Docker Compose instalados
- Composer
- RabbitMQ
- PHP 8.1^

### Passo a Passo

1. **Clone o repositório**:
   ```bash
   git clone https://github.com/matheusmoc/taxi-driver-api.git
   cd taxi-driver-api
   ```
2. **Configurar o banco de dados**:

Cria um banco de dados local

![image](https://github.com/user-attachments/assets/d2576a10-829e-4fea-b311-c0498df66169)

No arquivo docker-compose.yml, ajuste as variáveis de ambiente (como MYSQL_ROOT_PASSWORD, MYSQL_DATABASE) conforme necessário.

![image](https://github.com/user-attachments/assets/6a12fd0b-7b8c-41ad-9401-5926633afded)


4. **Subir a aplicação, rabbitmq e o banco de dados**:
    ```bash
    docker-compose down                                    <--- remover todos os processos rodando
    docker-compose up --build                              <--- subir containers
    docker exec -it laravel_app php artisan key:generate   <--- gerar key de acesso
    docker exec -it laravel_app php artisan migrate        <--- mapear migrations para o banco de dados
    ```
5. **Certificar se os serviçoes estão rodando em suas respectivas portas corretamente**
    ```bash
    dokcer ps
    ```

   ![image](https://github.com/user-attachments/assets/83151c5a-c61c-4f95-afb2-281a7da56eb2)

6. **Acessar a Aplicação e o RabbitMQ Management**
    ```bash
    http://localhost:15672/
    http://localhost:8080/
    ```

    ****OBS: Caso ocorra problema de permissão basta fazer a liberação:****
    ```bash
    docker exec -it laravel_app chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
    ```

7. **Copie o arquivo `.env.example` para criar o arquivo `.env`:**
    ```bash
    cp .env.example .env
    ```

## Diagrama de Entidade e Relacionamento (DER)

![image](https://github.com/user-attachments/assets/47933504-5f14-4c94-ada9-73916b3a108c)


## Estrutura do projeto

```plaintext
taxi-driver-api/
├── app/
│   ├── Console/
│   ├── Events/
│   │   └── DriverAvailable.php         # Evento para notificar quando um motorista está disponível
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── PassengerController.php # Controlador para gerenciamento de passageiros
│   │   │   ├── DriverController.php    # Controlador para gerenciamento de motoristas
│   │   │   └── RideController.php      # Controlador para gerenciamento de corridas
│   │   └── Requests/
│   ├── Jobs/
│   │   └── UpdateRideStatusJob.php     # Job para atualizar o status de uma corrida
│   ├── Listeners/
│   │   └── NotifyDriverAvailability.php # Listener para notificar a disponibilidade do motorista
│   └── Models/
│       ├── Ride.php                    # Modelo Ride com relacionamento e lógica adicional
│       ├── Passenger.php               # Modelo Passenger
│       └── Driver.php                  # Modelo Driver
├── config/
│   ├── app.php                         # Configuração principal do aplicativo
│   ├── queue.php                       # Configuração da fila de tarefas (RabbitMQ)
├── database/
│   └── migrations/                     # Arquivos de migração do banco de dados
├── routes/
│   └── api.php                         # Rotas da API
└── README.md                           # Documento com informações sobre o projeto
```

## Estrutura das filas com RabbitMQ

1. **Execute o comando abaixo para processar as filas**
   ```bash
   docker exec -it laravel_app php artisan queue:work
   ```
2. **Acesse o RabbitMQ local com RabbitMQ Management**
   **OBS:** Por desfault o login e senha é **guest**
   
   ![image](https://github.com/user-attachments/assets/89f3f372-a37d-488a-9c3b-c41977e6bf53)

4. **Crie uma corrida 'Em andamento' com o um passageiro de ID 1**
   
   ![image](https://github.com/user-attachments/assets/d94672c9-ff9d-4eaf-bc47-de043351c463)

5. **Crie um passageiro de ID 2**
   
   ![image](https://github.com/user-attachments/assets/661eeb1b-35a4-4bae-9459-c27d68a2803b)

6. **Coloque ela em uma corrida com o mesmo motorista**
   
   ![image](https://github.com/user-attachments/assets/e72f654e-61e1-45e2-b72a-3eb4e060e2c8)

   Quando um motorista está finalizando a corrida de um passageiro (usuário de ID 1) e recebe uma nova solicitação de outro passageiro (usuário de ID 2), a nova corrida entra em um sistema de fila (ou Broker) até que o motorista esteja disponível, nesse contexto usamos    o tipo de fila FIFO (FIRST IN FIRST OUT).

   ![image](https://github.com/user-attachments/assets/f185c986-0f5e-4ff9-8c17-1bd864573d01)

7. **Após finalizar a primeira corrida mudando o status para 'Finalizada', a proxima corrida entra com status 'Em andamento' automaticamente**

   ![image](https://github.com/user-attachments/assets/89979aa1-9813-44d1-b1b7-a5dafedbcc7f)

   ![image](https://github.com/user-attachments/assets/772f5d1d-f25e-43a3-9d9a-c187f10fd8bc)



## Teste dos Endpoints (Workspace de API)

Para facilitar o teste dos endpoints da API, incluímos um workspace de API exportado em [Postman](https://www.postman.com/) com todas as rotas definidas. Você pode importar o arquivo no Postman ou Insomnia para executar as requisições rapidamente.

[Download do Workspace de Testes](https://drive.google.com/file/d/123InssxwPypVdxcv4x_9H6QDZ2jizo2q/view?usp=sharing)

### Importando o Workspace
1. Baixe o arquivo JSON do workspace.
2. No Postman:
   - Clique em **File > Import**.
   - Selecione o arquivo JSON baixado para importar.
3. Após a importação, você verá todos os endpoints organizados no Postman para fácil acesso e testes.

### Endpoints Disponíveis

Aqui estão os principais endpoints incluídos no workspace:

#### Passageiros
- **POST** `/api/passengers` - Cadastrar um passageiro

#### Motoristas
- **POST** `/api/drivers` - Cadastrar um motorista

#### Corridas
- **POST** `/api/rides` - Criar uma nova corrida
- **PATCH** `/api/rides/{id}` - Atualizar o status de uma corrida
- **GET** `/api/rides/{id}` - Listar uma corrida específica pelo ID
- **GET** `/api/rides` - Listar todas as corridas

### Exemplos de Requisições
Cada endpoint no workspace possui exemplos de requisições com os campos necessários e respostas esperadas. 

### Configuração do Ambiente de Teste
O workspace também está configurado com um ambiente base. Lembre-se de configurar a variável `base_url` do ambiente para apontar para a URL local ou de produção da API:

- Local: `http://localhost:8000`
- Produção: `http://seu_dominio.com`




   
   


   

