# block_minhabiblioteca

Plugin do tipo **bloco** para o Moodle que integra o AVA com a plataforma [Minha Biblioteca](https://minhabiblioteca.com.br/) (biblioteca digital). O aluno logado clica em um botão e é redirecionado automaticamente para a Minha Biblioteca já autenticado, sem precisar de login separado.

---

## Requisitos

- Moodle 5.0 ou superior
- PHP com extensão cURL habilitada
- Acesso de saída do servidor à API da Minha Biblioteca (porta 443)
- Chave de API e URL base fornecidas pela Minha Biblioteca (DLI)

---

## Instalação

1. Copie a pasta `minhabiblioteca` para dentro do diretório `blocks/` da sua instalação do Moodle:

```
blocks/minhabiblioteca/
```

2. Acesse o Moodle como administrador e vá em:

**Administração do site > Notificações**

O Moodle detectará o plugin automaticamente e executará a instalação.

Ou via CLI:

```bash
sudo -u www-data php admin/cli/upgrade.php
```

---

## Configuração

Após a instalação, acesse:

**Administração do site > Plugins > Blocos > Minha Biblioteca**

Preencha os campos:

| Campo | Descrição |
|---|---|
| **URL da API** | URL base do serviço de integração fornecida pela Minha Biblioteca |
| **Chave API** | Chave de autenticação fornecida pela Minha Biblioteca |

---

## Como usar

### Adicionar o bloco a uma página

1. Ative o modo de edição na página desejada (curso, página inicial, dashboard)
2. Adicione o bloco "Minha Biblioteca"
3. O botão **Acessar Minha Biblioteca** aparecerá para todos os usuários autenticados

Ao clicar no botão, o Moodle chama a API com os dados do usuário logado (`firstname`, `lastname`, `email`) e redireciona para a URL autenticada retornada pela API. O redirecionamento abre em uma nova aba.

### URL estática para uso em outros recursos

Quando o modo de edição está ativo, professores e administradores veem abaixo do botão um campo com a URL estática do plugin:

```
https://seudominio/blocks/minhabiblioteca/go.php
```

Essa URL pode ser usada em:
- Recursos do tipo **URL** dentro de um curso
- Menus de navegação
- Qualquer outro link dentro do AVA

Ela exige que o usuário esteja autenticado no Moodle. Usuários não autenticados são redirecionados para o login antes do acesso.

---

## Estrutura de arquivos

```
blocks/minhabiblioteca/
├── block_minhabiblioteca.php   # Classe principal do bloco
├── redirect.php                # Chamada à API com proteção CSRF (usado pelo botão do bloco)
├── go.php                      # Chamada à API sem sesskey (usado em links e recursos externos)
├── settings.php                # Página de configuração no painel admin
├── version.php                 # Versão e compatibilidade do plugin
├── styles.css                  # Estilos do bloco
├── db/
│   └── access.php              # Definição de capabilities
└── lang/
    ├── en/
    │   └── block_minhabiblioteca.php   # Strings em inglês
    └── pt_br/
        └── block_minhabiblioteca.php   # Strings em português
```

---

## Capabilities

| Capability | Descrição | Padrão |
|---|---|---|
| `block/minhabiblioteca:addinstance` | Adicionar o bloco a páginas de curso/site | Professor com edição, Gerente |
| `block/minhabiblioteca:myaddinstance` | Adicionar o bloco ao Dashboard pessoal | Usuário autenticado |
| `block/minhabiblioteca:view` | Ver o bloco e acessar o link | Estudante, Professor, Gerente |

---

## Rollback

Para remover completamente o plugin:

1. Remova a pasta do plugin:

```bash
rm -rf blocks/minhabiblioteca
```

2. Execute o upgrade do Moodle:

```bash
sudo -u www-data php admin/cli/upgrade.php
```

O Moodle limpará automaticamente todas as referências ao plugin no banco de dados.

---

## Segurança

- `redirect.php` exige autenticação (`require_login`) e valida o `sesskey` do Moodle para proteção contra CSRF
- `go.php` exige apenas autenticação — adequado para uso como URL estática em recursos do AVA
- Os dados do usuário são escapados com `htmlspecialchars` antes de serem inseridos no XML enviado à API
- A URL retornada pela API é validada com `filter_var` antes do redirecionamento

---

## Licença

GNU GPL v3 ou posterior. Consulte [COPYING.txt](https://www.gnu.org/licenses/gpl-3.0.txt) para mais detalhes.
