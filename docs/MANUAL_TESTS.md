# Roadmap de teste manual

1. Configurar `.env` com MySQL, rodar `php artisan migrate --seed` e confirmar `php artisan storage:link`.
2. Criar um usuario, acessar `/dashboard` e cadastrar um anuncio em `/admin/anuncios/create`.
3. Confirmar que os selects pesquisaveis filtram categoria, status, UF e cidade; abrir um select, clicar no campo de busca, digitar, selecionar opcao filtrada e limpar quando houver botao de limpar.
4. Confirmar que Cidade fica desabilitada sem UF, e que ao trocar UF a lista de cidades e atualizada para o estado selecionado.
5. Repetir o smoke de select pesquisavel em viewport estreita/mobile.
6. Enviar pelo menos duas imagens e confirmar que os arquivos foram salvos como WebP no disco publico.
7. Remover todas as imagens de um anuncio, adicionar uma nova, publicar e confirmar que lista, detalhe e tags sociais usam somente a imagem existente em `storage/app/public`.
8. Publicar o anuncio e validar busca, filtro por categoria/cidade/UF e pagina de detalhe em `/anuncios`.
9. Enviar contato no detalhe pela URL publica com slug e conferir entrega pelo mailer configurado, inicialmente `log`; a rota legada com ID deve retornar 404.
10. No detalhe publico do anuncio, confirmar que o telefone aparece mascarado e que o HTML/payload inicial nao contem o numero completo; clicar em "Ver telefone", validar a revelacao e somente entao testar as acoes "Copiar", "Ligar" e "WhatsApp".
11. Acessar o detalhe de um anuncio em rascunho, nao publicado ou expirado e confirmar que tanto a pagina quanto `/anuncios/{slug}/telefone` retornam 404.
12. Clicar em "Ver mais anuncios deste anunciante" e validar que `/anunciantes/{slug}` lista somente os anuncios publicados, ativos e nao expirados daquele usuario, sem exibir e-mail ou dados privados da conta.
13. Acessar `/anunciantes/{id}` de um anunciante com anuncios publicos e confirmar o redirecionamento permanente para `/anunciantes/{slug}`; anunciante sem anuncios publicos deve retornar 404.
14. Criar outro usuario e validar isolamento: ele nao deve editar, excluir ou listar anuncios do primeiro usuario.
15. Confirmar que o detalhe publico usa `/anuncios/{slug}` e que a URL legada `/anuncios/{id}` de anuncio publicado redireciona permanentemente para a URL canonica; rascunhos e anuncios expirados devem retornar 404.
16. Autenticado, abrir um anuncio publico, clicar em "Salvar nos favoritos" e confirmar o estado favoritado no detalhe, no catalogo e em `/favoritos`.
17. Remover o anuncio dos favoritos pelo detalhe, atualizar a pagina e confirmar que ele nao aparece mais em `/favoritos`.
18. Como visitante, confirmar que o detalhe oferece login para favoritar e que uma tentativa direta de `POST /favoritos/{slug}` redireciona para `/login` sem persistir dados.
19. Alterar um anuncio favoritado para rascunho ou expirado e confirmar que ele deixa de aparecer em `/favoritos` e nao pode ser favoritado novamente enquanto nao estiver publico.
20. Com um worker de fila ativo, editar titulo, categoria, descricao, preco, cidade/UF ou imagens de um anuncio publico favoritado e confirmar que o interessado recebe um unico e-mail; alterar apenas contato, validade ou status nao deve enviar aviso.
21. Confirmar que o anunciante nao recebe aviso do proprio anuncio e que alteracoes repetidas dentro de dez minutos geram no maximo um e-mail por interessado.
22. Abrir o link de cancelamento no e-mail, confirmar a mensagem de sucesso e validar que novas edicoes do anuncio nao enviam outros avisos para esse favorito.
