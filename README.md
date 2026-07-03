# Classificados

Aplicacao Laravel com Inertia, Vue 3 e TypeScript para publicar e gerenciar anuncios classificados por usuario.

## Stack

- Laravel
- PHP 8.4
- Inertia
- Vue 3 com TypeScript
- MySQL
- Intervention Image para otimizar imagens enviadas para WebP

## Configuracao local

1. Copie `.env.example` para `.env`.
2. Crie um banco MySQL chamado `rockclassifields`.
3. Ajuste `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` e `DB_PASSWORD`.
4. Rode as dependencias e migrations:

```bash
composer install
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm install
npm run build
```

## Fluxos principais

- Visitantes acessam `/anuncios`, filtram anuncios e enviam contato ao anunciante.
- Usuarios autenticados acessam `/dashboard` e `/admin/anuncios`.
- Cada usuario gerencia apenas seus proprios anuncios.
- Imagens do anuncio sao processadas pelo `MediaService` e armazenadas no disco configurado por `MEDIA_DISK`.
- URLs do disco publico usam `PUBLIC_STORAGE_URL=/storage` por padrao, evitando dependência de host/porta local.
- Estados e cidades sao carregados por seeder a partir de `database/seeders/data/locations.json`; o arquivo e um catalogo inicial de MVP e pode ser expandido conforme demanda real.

## Teste manual breve

1. Criar conta e entrar no painel.
2. Criar um anuncio publicado com categoria, preco, cidade, contato e imagens.
3. Usar os selects pesquisaveis para categoria, UF e cidade.
4. Abrir a vitrine publica em `/anuncios` e confirmar filtros, imagem de capa e detalhe.
5. Enviar contato pelo detalhe do anuncio e verificar o log/e-mail configurado.
6. Entrar com outro usuario e confirmar que ele nao acessa nem altera anuncios do primeiro usuario.
