# Notas da versao em desenvolvimento

Status: **em desenvolvimento**

Branch de integracao: `develop`

Ultima revisao: 14/07/2026

Este documento resume as entregas acumuladas no ciclo atual. Ele nao representa uma release publicada em producao.

## Implementacoes disponiveis na develop

### Plataforma de classificados

- Cadastro, autenticacao e painel do usuario.
- Criacao, edicao, publicacao e exclusao de anuncios com isolamento por proprietario.
- Vitrine publica com busca e filtros por categoria, estado e cidade.
- Catalogo inicial de estados e cidades e selects pesquisaveis no formulario administrativo.
- Formulario publico de contato com envio ao anunciante.

### Imagens e midia

- Upload e processamento de imagens dos anuncios em WebP.
- Definicao de imagem de capa e suporte a multiplas imagens.
- Tolerancia a arquivos ausentes no storage, evitando quebra da pagina publica.

### Paginas publicas e SEO

- URLs publicas canonicas baseadas em slug para anuncios e anunciantes.
- Redirecionamento das URLs legadas com ID quando o recurso e publico.
- Pagina publica do anunciante contendo apenas anuncios publicados, ativos e nao expirados.
- Metadados sociais e imagem de compartilhamento para anuncios e paginas de anunciante.

### Privacidade e seguranca

- Telefone do anunciante mascarado no detalhe publico.
- Ausencia de link telefonico antes de uma acao explicita do visitante.
- Regras de visibilidade que impedem acesso publico a rascunhos e anuncios expirados.
- Policies e consultas administrativas limitando o gerenciamento ao proprietario do anuncio.

### Qualidade e operacao

- Testes automatizados para os fluxos principais, visibilidade publica, SEO e isolamento.
- Pipeline de qualidade com formatacao, lint, TypeScript e testes PHP.
- Comandos de instalacao e atualizacao em producao documentados no `README.md`.
- Roteiro de validacao manual mantido em `docs/MANUAL_TESTS.md`.

## Em validacao antes de entrar na develop

### Revelacao controlada do telefone

- Endpoint dedicado para revelar o telefone somente apos a acao `Ver telefone`.
- Entrega do numero completo apenas para anuncios publicados, ativos e nao expirados.
- Acoes `Copiar`, `Ligar` e `WhatsApp` exibidas somente depois da revelacao.
- Rate limit, resposta sem cache e registro de auditoria com metadados tecnicos minimizados.
- Testes cobrindo ausencia do telefone no payload inicial, revelacao valida e bloqueio de anuncios nao publicos.

## Criterio para publicacao

Antes de promover este ciclo para uma release, as mudancas pendentes devem estar integradas na `develop`, as migrations devem ser revisadas e o roteiro de `docs/MANUAL_TESTS.md` deve ser executado no ambiente de homologacao.
