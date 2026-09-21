# ChurrasTop

<p align="center">
  <a href="https://imgur.com/jmVOIdm">
    <img src="https://i.imgur.com/jmVOIdm.png" alt="Logo ChurrasTop" width="220">
  </a>
</p>

Sistema web para planejamento e organização de churrascos. A aplicação permite criar eventos, estimar quantidades e custos, montar uma lista de compras, controlar participantes e contribuições e receber solicitações por meio de um convite público.

O projeto foi desenvolvido como trabalho acadêmico do segundo bimestre, com foco em desenvolvimento web, modelagem de banco de dados, uso de PHP, organização de código e execução com Docker.

## Funcionalidades

### Autenticação

- Cadastro de usuários;
- Login com e-mail e senha;
- Senhas armazenadas com `password_hash`;
- Controle de acesso às áreas administrativas;
- Encerramento de sessão;
- Cada usuário visualiza e administra somente os próprios churrascos.

### Gerenciamento de churrascos

Cada churrasco possui:

- Nome;
- Data;
- Quantidade de adultos;
- Quantidade de crianças;
- Duração;
- Tipo de churrasco;
- Usuário responsável.

Os tipos disponíveis são:

- Econômico;
- Tradicional;
- ChurrasTop.

As durações disponíveis são:

- 2 horas;
- 4 horas;
- 6 horas ou mais.

### Calculadora e lista de compras

A calculadora considera:

- Pessoas equivalentes, considerando crianças como meia pessoa;
- Tipo do churrasco;
- Duração do evento;
- Quantidade de cada produto por pessoa;
- Preço unitário;
- Subtotal por produto;
- Total estimado;
- Valor estimado por participante.

Também é possível:

- Pesquisar produtos pelo nome;
- Filtrar por categoria;
- Filtrar por preço mínimo;
- Filtrar por preço máximo;
- Ajustar manualmente as quantidades;
- Salvar a lista de compras no banco de dados;
- Consultar posteriormente a lista salva.

### Participantes e pagamentos

O organizador pode:

- Adicionar participantes;
- Informar nome, telefone e tipo de participante;
- Registrar a contribuição financeira;
- Marcar uma contribuição como paga;
- Alterar o status para pendente;
- Consultar os participantes vinculados ao churrasco.

### Convite público

Cada churrasco possui uma página pública de convite. O convidado pode:

- Informar nome e telefone;
- Escolher um produto que levará;
- Enviar uma solicitação de participação;
- Consultar as vagas disponíveis por produto.

O sistema limita a repetição de produtos e impede solicitações duplicadas para o mesmo churrasco.

O organizador pode aprovar ou recusar as solicitações recebidas.

## Tecnologias utilizadas

### Backend

- PHP 8.3;
- Apache;
- PDO;
- Extensão `pdo_mysql`;
- Extensão `mbstring`;
- Sessões nativas do PHP;
- Prepared statements para consultas parametrizadas.

### Frontend

- HTML5;
- CSS3;
- Bootstrap 5.3.3;
- Bootstrap Icons;
- Layout responsivo;
- Componentes como navbar, cards, formulários, tabelas, alertas, badges e botões.

### Banco de dados

- MySQL 8.4;
- Modelagem relacional;
- Chaves primárias;
- Chaves estrangeiras;
- Relacionamentos um-para-muitos;
- Relacionamentos muitos-para-muitos por meio de tabelas associativas;
- Restrições `CHECK`, `UNIQUE`, `ENUM` e `NOT NULL`.

### Infraestrutura

- Docker;
- Docker Compose;
- Container separado para a aplicação;
- Container separado para o banco de dados;
- Rede Docker privada;
- IP fixo para o banco de dados;
- Volume persistente para os dados do MySQL;
- Apache configurado para impedir listagem de diretórios.

## Arquitetura do projeto

```text
.
├── assets/
│   ├── css/
│   │   └── style.css
│   └── icone.png
├── config/
│   └── database.php
├── database/
│   └── schema.sql
├── docs/
│   ├── der.html
│   └── RUBRICA-E-EXECUCAO.md
├── functions/
│   ├── calculadora.php
│   └── produtos.php
├── includes/
│   ├── auth.php
│   ├── footer.php
│   ├── header.php
│   └── navbar.php
├── pages/
│   ├── calculadora.php
│   ├── churrasco_form.php
│   ├── lista_compras.php
│   ├── participantes.php
│   └── solicitacoes.php
├── public/
│   ├── .htaccess
│   ├── cadastro.php
│   ├── index.php
│   ├── login.php
│   ├── logout.php
│   └── participar.php
├── Dockerfile
├── docker-compose.yml
└── README.md
```

## Organização do código PHP

### Configuração e acesso ao banco

O arquivo `config/database.php` centraliza a criação da conexão PDO. As configurações podem ser fornecidas por variáveis de ambiente:

```text
DB_HOST
DB_PORT
DB_NAME
DB_USER
DB_PASSWORD
```

### Autenticação

O arquivo `includes/auth.php` contém as funções:

- `requireLogin()`: bloqueia páginas administrativas para visitantes não autenticados;
- `currentUserId()`: retorna o identificador do usuário atual.

### Funções de negócio

O arquivo `functions/calculadora.php` concentra a lógica de cálculo:

- `pessoasEquivalentes()`;
- `multiplicadorTipo()`;
- `multiplicadorDuracao()`;
- `calcularLista()`;
- `totalLista()`;
- `validarArrayProdutos()`.

O arquivo `functions/produtos.php` concentra:

- `filtrarProdutos()`;
- `moeda()`.

As funções recebem dados por parâmetros e retornam resultados, evitando depender de variáveis globais.

### Templates

Os arquivos `includes/header.php`, `includes/navbar.php` e `includes/footer.php` são reutilizados nas páginas para manter um layout consistente e reduzir repetição de código.

## Banco de dados

O banco possui as seguintes tabelas principais:

| Tabela | Finalidade |
|---|---|
| `usuarios` | Armazena as contas dos organizadores |
| `churrascos` | Armazena os eventos criados |
| `participantes` | Armazena os convidados |
| `churrasco_participante` | Relaciona participantes e churrascos |
| `produtos` | Catálogo de itens da lista de compras |
| `churrasco_produto` | Relaciona produtos e churrascos |
| `solicitacoes_participacao` | Controla pedidos enviados pelos convidados |

Os relacionamentos muitos-para-muitos são implementados por:

- `churrasco_participante`;
- `churrasco_produto`.

O diagrama entidade-relacionamento está disponível em [docs/der.html](docs/der.html).

## Como executar com Docker

### Pré-requisitos

- Docker Desktop instalado;
- Docker Compose disponível;
- Git, caso o projeto seja clonado pelo repositório.

### Inicialização

Na pasta raiz do projeto, execute:

```powershell
docker compose up --build -d
```

Para verificar os containers:

```powershell
docker compose ps
```

Os serviços esperados são:

- `app`: aplicação PHP com Apache;
- `database`: MySQL.

### Acesso pela porta 8080

A aplicação estará disponível em:

```text
http://localhost:8080/
```

## Configuração do domínio local

O Apache está configurado com o domínio `churrastop.local`. Para usar esse domínio no Windows, abra o PowerShell como administrador e adicione a entrada ao arquivo `hosts`:

```powershell
Add-Content -Path "$env:WINDIR\System32\drivers\etc\hosts" -Value "`n127.0.0.1 churrastop.local"
```

Depois, acesse:

```text
http://churrastop.local:8080/
```

## Comandos úteis

Ver logs da aplicação:

```powershell
docker compose logs -f app
```

Ver logs do banco:

```powershell
docker compose logs -f database
```

Parar os containers:

```powershell
docker compose down
```

Parar os containers e remover o volume de dados:

```powershell
docker compose down -v
```

O último comando apaga os dados persistidos do banco e deve ser usado somente quando for necessário recriar o banco do zero.

## Roteiro de utilização

1. Acesse a aplicação.
2. Crie uma conta.
3. Faça login.
4. Crie um churrasco.
5. Informe data, quantidade de adultos, quantidade de crianças, duração e tipo.
6. Abra a calculadora.
7. Pesquise ou filtre produtos.
8. Ajuste as quantidades, se necessário.
9. Salve a lista de compras.
10. Abra a área de participantes.
11. Adicione participantes ou abra o convite público.
12. Envie uma solicitação pelo convite.
13. Aprove ou recuse a solicitação como organizador.
14. Registre e atualize os pagamentos.

## Capturas de tela

As imagens abaixo apresentam as principais telas do sistema.

### Diagrama entidade-relacionamento

[Abrir imagem do DER](https://imgur.com/tktnQj4)

![Diagrama entidade-relacionamento](https://i.imgur.com/tktnQj4.png)

### Dashboard

[Abrir imagem do dashboard](https://imgur.com/vXvCGdE)

![Dashboard](https://i.imgur.com/vXvCGdE.png)

### Criação de churrasco

[Abrir imagem da criação de churrasco](https://imgur.com/c9ZdaGx)

![Criar churrasco](https://i.imgur.com/c9ZdaGx.png)

### Calculadora do churrasco

[Abrir imagem da calculadora](https://imgur.com/IzckRgh)

![Calculadora do churrasco](https://i.imgur.com/IzckRgh.png)

### Participantes

[Abrir imagem dos participantes](https://imgur.com/THWyk5j)

![Participantes](https://i.imgur.com/THWyk5j.png)

### Convite para o churrasco

[Abrir imagem do convite](https://imgur.com/i6uLgog)

![Convite para o churrasco](https://i.imgur.com/i6uLgog.png)

## Atendimento aos requisitos acadêmicos

O projeto contempla:

- Diagrama entidade-relacionamento;
- Banco com mais de três tabelas;
- Chaves primárias e estrangeiras;
- Relacionamentos muitos-para-muitos;
- Bloqueio de listagem de diretórios;
- Aplicação na porta 8080;
- Configuração de domínio local;
- Banco com IP fixo na rede Docker;
- Separação da aplicação e do banco em containers;
- Uso de Bootstrap;
- Conexão com banco de dados;
- Exibição de dados recuperados do banco;
- Uso de estruturas condicionais e de repetição;
- Organização de dados em arrays;
- Funções próprias com parâmetros e retorno;
- Pesquisa e filtros;
- Validação de regras de negócio;
- Templates PHP reutilizáveis.

## Repositório

O código-fonte está disponível em:

https://github.com/LucasSC98/ChurrasTop

## Licença

Este projeto foi desenvolvido para fins acadêmicos.

## Autores

Lucas Da Silva Custodio
João Gustavo Quennehen


