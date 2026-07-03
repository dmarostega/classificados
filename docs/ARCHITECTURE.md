# Arquitetura

O produto e um sistema de classificados com dados isolados por usuario anunciante.

## Camadas

- `App\Models`: entidades de usuario, categoria, anuncio, imagem e midia.
- `App\Http\Controllers\PublicListingController`: vitrine publica, detalhe e contato.
- `App\Http\Controllers\Admin\ListingController`: CRUD autenticado dos anuncios do usuario.
- `App\Services\ListingService`: regras de criacao, atualizacao, slug e publicacao.
- `App\Services\ListingImageService`: vinculo e remocao de imagens do anuncio.
- `App\Services\MediaService`: processamento e armazenamento de arquivos, incluindo imagens WebP.
- `resources/js/pages/Public`: telas publicas.
- `resources/js/pages/Admin`: telas administrativas.

## Banco de dados

MySQL e o banco padrao do projeto. O `.env.example` define `DB_CONNECTION=mysql` e banco `rockclassifields`.
Os testes automatizados usam SQLite em memoria pelo `phpunit.xml` para serem rapidos e isolados.

## Isolamento por usuario

As consultas administrativas usam `whereBelongsTo($request->user())`, e a `ListingPolicy` bloqueia leitura, edicao e exclusao de anuncios de outro usuario.
