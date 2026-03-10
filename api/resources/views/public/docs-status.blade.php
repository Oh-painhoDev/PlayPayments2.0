@php
$docs = DB::table('document_verifications')
    ->where('user_id', Auth::id())
    ->first();
@endphp

@if($docs)

    @if($docs->status === 'pendente')
        <div class="status-box">
            Seus documentos foram enviados e estão <b>pendentes de verificação</b>.
        </div>
    @elseif($docs->status === 'rejeitado' || $docs->status === 'recusado')
        <div class="status-box status-error">
            Alguns documentos foram <b>rejeitados</b>.  
            <a href="/onboarding" style="color:#b10000;">Clique aqui para reenviar.</a>
        </div>
    @endif

@endif
