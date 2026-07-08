# Roadmap de teste manual

1. Configurar `.env` com MySQL, rodar `php artisan migrate --seed` e confirmar `php artisan storage:link`.
2. Criar um usuario, acessar `/dashboard` e cadastrar um anuncio em `/admin/anuncios/create`.
3. Confirmar que os selects pesquisaveis filtram categoria, status, UF e cidade; abrir um select, clicar no campo de busca, digitar, selecionar opcao filtrada e limpar quando houver botao de limpar.
4. Confirmar que Cidade fica desabilitada sem UF, e que ao trocar UF a lista de cidades e atualizada para o estado selecionado.
5. Repetir o smoke de select pesquisavel em viewport estreita/mobile.
6. Enviar pelo menos duas imagens e confirmar que os arquivos foram salvos como WebP no disco publico.
7. Remover todas as imagens de um anuncio, adicionar uma nova, publicar e confirmar que lista, detalhe e tags sociais usam somente a imagem existente em `storage/app/public`.
8. Publicar o anuncio e validar busca, filtro por categoria/cidade/UF e pagina de detalhe em `/anuncios`.
9. Enviar contato no detalhe e conferir entrega pelo mailer configurado, inicialmente `log`.
10. No detalhe publico do anuncio, clicar em "Ver mais anuncios deste anunciante" e confirmar que `/anunciantes/{id}` lista somente os anuncios publicados, ativos e nao expirados daquele usuario, sem exibir e-mail ou dados privados da conta.
11. Criar outro usuario e validar isolamento: ele nao deve editar, excluir ou listar anuncios do primeiro usuario.
