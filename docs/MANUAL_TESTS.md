# Roadmap de teste manual

1. Configurar `.env` com MySQL e rodar `php artisan migrate --seed`.
2. Criar um usuario, acessar `/dashboard` e cadastrar um anuncio em `/admin/anuncios/create`.
3. Enviar pelo menos duas imagens e confirmar que os arquivos foram salvos como WebP no disco publico.
4. Publicar o anuncio e validar busca, filtro por categoria/cidade/UF e pagina de detalhe em `/anuncios`.
5. Enviar contato no detalhe e conferir entrega pelo mailer configurado, inicialmente `log`.
6. Criar outro usuario e validar isolamento: ele nao deve editar, excluir ou listar anuncios do primeiro usuario.
