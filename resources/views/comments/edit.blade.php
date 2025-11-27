@extends('layouts.app')

@section('title', 'Modifica Commento')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">Modifica Commento</h4>
                            <a href="{{ route('events.show', $comment->event) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Torna all'Evento
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Stai modificando il commento per l'evento: <strong>{{ $comment->event->title }}</strong>
                        </div>

                        <form action="{{ route('comments.update', $comment) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="content" class="form-label">Commento</label>
                                <textarea class="form-control" id="content" name="content"
                                          rows="6" placeholder="Modifica il tuo commento..."
                                          required>{{ old('content', $comment->content) }}</textarea>
                                @error('content')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar"></i>
                                        Creato il: {{ $comment->created_at->format('d/m/Y H:i') }}
                                    </small>
                                    @if($comment->is_edited)
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-edit"></i>
                                            Ultima modifica: {{ $comment->edited_at->format('d/m/Y H:i') }}
                                        </small>
                                    @endif
                                </div>

                                <div class="d-flex gap-2">
                                    <a href="{{ route('events.show', $comment->event) }}" class="btn btn-secondary">
                                        Annulla
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Salva Modifiche
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TinyMCE per l'editor WYSIWYG -->
@section('scripts')
    <script src="https://cdn.tiny.cloud/1/bklljwbpvidz9oqemanmswdq49st98dpznthjvl77p3rfaf1/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            tinymce.init({
                selector: '#content',
                plugins: 'link lists code emoticons',
                toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | bullist numlist | link emoticons | code',
                menubar: false,
                height: 300,
                branding: false,
                statusbar: false,
                setup: function (editor) {
                    editor.on('change', function () {
                        editor.save();
                    });
                }
            });
        });
    </script>
@endsection
@endsection
