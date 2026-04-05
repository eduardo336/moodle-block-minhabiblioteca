# block_minhabiblioteca

Plugin Moodle (bloco) que integra o AVA com a plataforma Minha Biblioteca. O usuário logado clica em um botão e é redirecionado automaticamente para a biblioteca digital já autenticado, sem login separado. A integração usa o endpoint `/AuthenticatedUrl` da API DLI (modelo Sob Demanda).

## Instalação

Copie a pasta `minhabiblioteca` para `blocks/` e acesse **Administração do site > Notificações** para concluir a instalação. Ou via CLI:

```bash
sudo -u www-data php admin/cli/upgrade.php
```

## Configuração

Acesse **Administração do site > Plugins > Blocos > Minha Biblioteca** e preencha a URL da API e a chave de API fornecidas pela Minha Biblioteca (DLI).

## Rollback

```bash
rm -rf blocks/minhabiblioteca
sudo -u www-data php admin/cli/upgrade.php
```
