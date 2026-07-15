# Notas da versão em desenvolvimento

Status: **promovido para main / ciclo v0.1.0 integrado**

Branch de integração original: `develop`  
Branch estável após promoção: `main`

Última revisão: 15/07/2026

Este documento resume as entregas acumuladas no ciclo promovido de `develop` para `main`. Ele reflete o estado após a integração da release inicial do projeto Classificados.

## Implementações integradas na release

### Plataforma de classificados

- Cadastro, autenticação e painel do usuário.
- Criação, edição, publicação e exclusão de anúncios com isolamento por proprietário.
- Vitrine pública com busca e filtros por categoria, estado e cidade.
- Catálogo inicial de estados e cidades e selects pesquisáveis no formulário administrativo.
- Formulário público de contato com envio ao anunciante.

### Imagens e mídia

- Upload e processamento de imagens dos anúncios em WebP.
- Definição de imagem de capa e suporte a múltiplas imagens.
- Tolerância a arquivos ausentes no storage, evitando quebra da página pública.

### Páginas públicas e SEO

- URLs públicas canônicas baseadas em slug para anúncios e anunciantes.
- Redirecionamento das URLs legadas com ID quando o recurso é público.
- Página pública do anunciante contendo apenas anúncios publicados, ativos e não expirados.
- Metadados sociais e imagem de compartilhamento para anúncios e páginas de anunciante.
- Sitemap público dinâmico contendo home, listagem pública, anúncios públicos e anunciantes com anúncios públicos.
- Páginas privadas e operacionais usando `noindex,nofollow` quando aplicável.

### Privacidade e segurança

- Telefone do anunciante mascarado no detalhe público.
- Ausência de link telefônico antes de uma ação explícita do visitante.
- Revelação controlada do telefone por endpoint dedicado após a ação `Ver telefone`.
- Entrega do número completo apenas para anúncios publicados, ativos e não expirados.
- Ações `Copiar`, `Ligar` e `WhatsApp` exibidas somente depois da revelação.
- Rate limit, resposta sem cache e registro de auditoria com metadados técnicos minimizados para revelação de telefone.
- Regras de visibilidade que impedem acesso público a rascunhos e anúncios expirados.
- Policies e consultas administrativas limitando o gerenciamento ao proprietário do anúncio.

### Favoritos e notificações

- Favoritos autenticados para anúncios públicos.
- Página `Meus favoritos` com listagem dos anúncios salvos pelo usuário.
- Notificações por e-mail para interessados quando anúncios favoritos recebem alterações relevantes.
- Exclusão do próprio anunciante como destinatário de notificações do seu anúncio.
- Respeito ao opt-out por favorito.
- Cooldown antispam de 10 minutos por favorito.
- Link assinado para desativar notificações daquele anúncio.
- Processamento das notificações por fila Laravel.

### Qualidade e operação

- Testes automatizados para fluxos principais, visibilidade pública, SEO, isolamento, favoritos, revelação de telefone e notificações.
- Pipeline de qualidade com formatação, lint, TypeScript e testes PHP.
- Comandos de instalação e atualização em produção documentados no `README.md`.
- Roteiro de validação manual mantido em `docs/MANUAL_TESTS.md`.
- Validação operacional de deploy com migrations, build front-end, sitemap, favoritos, fila e SMTP.

## Pendências operacionais conhecidas

- Configurar worker persistente no VPS/Hostinger para processar filas continuamente em produção.
- Formalizar rotina de backup e restore antes de próximas migrations relevantes.
- Evoluir documentação de deploy/produção conforme o projeto deixar de ser MVP inicial.

## Critério para próxima publicação

Antes de promover um novo ciclo para release, as mudanças devem estar integradas na branch de desenvolvimento definida para o projeto, as migrations devem ser revisadas, o worker de fila deve estar validado quando houver jobs assíncronos e o roteiro de `docs/MANUAL_TESTS.md` deve ser executado no ambiente alvo.
