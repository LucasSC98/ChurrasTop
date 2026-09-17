# ChurrasTop — Rubrica e execução

## Executar

Na raiz do projeto:

```powershell
docker compose up --build -d
```

Aplicação: `http://localhost:8080`

## DNS local no Windows

Abra o PowerShell como administrador e execute:

```powershell
Add-Content -Path "$env:WINDIR\System32\drivers\etc\hosts" -Value "`n127.0.0.1 churrastop.local"
```

Depois acesse `http://churrastop.local:8080`.

O Apache já está configurado com `ServerName churrastop.local` no
[Dockerfile](../Dockerfile), e o bloqueio de listagem está em
[public/.htaccess](../public/.htaccess).

## Evidências da rubrica

- DER: [docs/der.html](./der.html)
- Banco, chaves e relacionamentos N:N: [database/schema.sql](../database/schema.sql)
- IP fixo do banco e serviços separados em containers: [docker-compose.yml](../docker-compose.yml)
- Funções, cálculo, filtro e validações: [functions/calculadora.php](../functions/calculadora.php) e [functions/produtos.php](../functions/produtos.php)
- Template, Bootstrap e layout: [includes/header.php](../includes/header.php) e [assets/css/style.css](../assets/css/style.css)

## Roteiro de demonstração

1. Criar uma conta e entrar.
2. Criar um churrasco com adultos, crianças, data, tipo e duração.
3. Abrir a calculadora, pesquisar um produto e filtrar por categoria/preço.
4. Ajustar e salvar a lista de compras.
5. Abrir o convite em uma janela anônima.
6. Enviar uma solicitação de participação.
7. Aprovar a solicitação no painel do organizador.
8. Marcar a contribuição como paga.
