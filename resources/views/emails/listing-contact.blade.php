<p>Você recebeu um novo contato pelo anúncio <strong>{{ $listing->title }}</strong>.</p>

<p>
    <strong>Nome:</strong> {{ $contact['name'] }}<br>
    <strong>E-mail:</strong> {{ $contact['email'] }}<br>
    @if (! empty($contact['phone']))
        <strong>Telefone:</strong> {{ $contact['phone'] }}<br>
    @endif
</p>

<p>{{ $contact['message'] }}</p>

<p>
    Anúncio:
    <a href="{{ $listing->publicUrl() }}">{{ $listing->publicUrl() }}</a>
</p>
