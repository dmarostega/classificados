<h1>Um anuncio favorito foi atualizado</h1>

<p>O anuncio <strong>{{ $favorite->listing->title }}</strong> recebeu uma alteracao relevante.</p>

<p><a href="{{ $favorite->listing->publicUrl() }}">Ver anuncio atualizado</a></p>

<p>
    Voce recebeu este e-mail porque salvou o anuncio nos favoritos.
    <a href="{{ $unsubscribeUrl }}">Desativar estes avisos para o anuncio</a>.
</p>
