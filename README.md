# ABDM

## Como abrir o sistema

1. No painel do XAMPP, inicie o Apache e o MySQL.
2. Para preparar um banco novo, importe o arquivo [Banco ABDM Teste.sql].

## Configuração

- Em [config/config.local.php], ajuste `db_host` (servidor), `db_port` (porta), `db_name` (nome do banco), `db_user` (usuário) e `db_password` (senha).
- O arquivo `config.local.php` também precisa de uma `encryption_key` com 32 bytes. Use a chave de teste original (Chave_Super_Secreta_ABDM_System!).
- O arquivo [config/aplicacao.php](config/aplicacao.php) carrega a configuração e conecta o sistema ao banco.
- Em [config/caminhos.php](config/caminhos.php), ajuste `BASE_URL` se o sistema estiver em outra pasta. O valor padrão é `/abdm/`.

## Usuários para teste

Use estes dados na tela de login do sistema.

| Tipo de acesso | Usuário | Senha |
| --- | --- | --- |
| Acesso limitado | `fisio` | `123456` |
| Administrador | `natan` | `123456` |
