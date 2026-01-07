# pap_miguel

Aplicação exemplo com login e registo (Funcionário / Professor).

Instalação rápida:

1. Iniciar o servidor (WAMP/XAMPP) com MySQL disponível.
2. Importar o ficheiro `sql/create_db.sql` na sua base de dados (phpMyAdmin ou linha de comando).
3. Ajustar credenciais em `db.php` se necessário (host, user, pass).
4. Aceder a `http://localhost/pap_miguel/index.php` e registar/entrar (registe usando o Número de processo; entre com Número de processo e palavra‑passe).

Notas de segurança:
- Em produção, não utilize o utilizador `root` sem palavra-passe.
- Utilize HTTPS e políticas de sessão seguras.