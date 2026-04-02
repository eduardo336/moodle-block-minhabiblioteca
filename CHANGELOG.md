# Changelog

## [1.0.0] - 2026-04-02

### Adicionado
- Bloco `block_minhabiblioteca` com botão de acesso à Minha Biblioteca
- Integração com o endpoint `/AuthenticatedUrl` da API DLI (modelo Sob Demanda)
- `redirect.php` — chamada à API com proteção CSRF via `sesskey`, usado pelo botão do bloco
- `go.php` — chamada à API sem `sesskey`, para uso em recursos URL, menus e links avulsos do AVA
- Página de configuração no painel admin com campos para URL da API e chave de API
- Capabilities: `view`, `addinstance` e `myaddinstance`
- Strings em português (`pt_br`) e inglês (`en`)
- Redirecionamento em nova aba (`target="_blank"`)
- Exibição da URL estática (`go.php`) apenas para professores e administradores no modo de edição
- Tratamento de erros com mensagens amigáveis para falha de conexão, resposta inválida e chave não configurada
