@extends('layouts.app')

@section('title', 'Accueil - Buzz Events')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    <!-- En-tête -->
    <div class="text-center mb-8 px-4">
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-800 mb-2">
            <i class="fas fa-fire text-orange-500"></i> Événements Buzz
        </h1>
        <p class="text-gray-500 text-sm sm:text-base">
            Découvrez les dernières tendances et actualités du web
        </p>
    </div>

    <!-- Barre de recherche -->
    <div class="mb-8 max-w-xl mx-auto">
        <div class="flex flex-col sm:flex-row gap-2">
            <input
                type="text"
                id="searchInput"
                placeholder="Rechercher un événement..."
                class="flex-1 px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-600 text-sm"
            >
            <button
                onclick="searchEvents()"
                class="bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 transition font-semibold"
            >
                <i class="fas fa-search mr-1"></i> Chercher
            </button>
        </div>
    </div>

    @if($events->count())

        <!-- Grille des événements -->
        <div
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6"
            id="eventsGrid"
        >
            @foreach($events as $event)
                <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 overflow-hidden flex flex-col">

                    <!-- Image -->
                    <div class="relative">
                        <img
                            src="{{
                                $event->image
                                    ? (str_starts_with($event->image, 'http')
                                        ? $event->image
                                        : asset('storage/' . $event->image))
                                    : asset('images/placeholder.png')
                            }}"
                            alt="{{ $event->title }}"
                            class="w-full h-44 sm:h-48 object-cover"
                            loading="lazy"
                        >

                        <div class="absolute top-3 right-3 bg-black bg-opacity-50 text-white text-xs px-2 py-1 rounded-full">
                            <i class="fas fa-eye mr-1"></i>{{ $event->views }}
                        </div>
                    </div>

                    <!-- Contenu -->
                    <div class="p-4 sm:p-5 flex flex-col flex-1">
                        <h3 class="text-lg sm:text-xl font-bold text-gray-800 mb-2 line-clamp-2">
                            {{ $event->title }}
                        </h3>

                        <p class="text-gray-500 text-sm mb-3 flex-1">
                            {{ Str::limit($event->description, 100) }}
                        </p>

                        <!-- Auteur et date -->
                        <div class="flex flex-wrap gap-2 text-xs text-gray-400 mb-4">
                            @if($event->user)
                                <span>
                                    <i class="fas fa-user mr-1"></i>{{ $event->user->name }}
                                </span>
                            @endif
                            <span>
                                <i class="fas fa-calendar mr-1"></i>
                                {{ $event->created_at->diffForHumans() }}
                            </span>
                        </div>

                        <!-- Boutons -->
                        <div class="flex gap-2">
                            <a
                                href="{{ route('events.show', $event) }}"
                                class="flex-1 bg-purple-600 text-white text-sm px-3 py-2 rounded-lg hover:bg-purple-700 transition text-center flex items-center justify-center"
                            >
                                <i class="fas fa-eye mr-1"></i> Voir
                            </a>

                            <a
                                href="{{ $event->source_link }}"
                                target="_blank"
                                class="flex-1 bg-pink-600 text-white text-sm px-3 py-2 rounded-lg hover:bg-pink-700 transition text-center flex items-center justify-center"
                            >
                                <i class="fas fa-external-link-alt mr-1"></i> Source
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-10 flex justify-center">
            {{ $events->links('pagination.tailwind') }}
        </div>

    @else
        <!-- Aucun événement -->
        <div class="text-center py-16 px-4">
            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-inbox text-4xl text-gray-300"></i>
            </div>
            <p class="text-gray-500 text-lg mb-2">
                Aucun événement pour le moment
            </p>
            <p class="text-gray-400 text-sm mb-6">
                Soyez le premier à partager un événement !
            </p>
            <a
                href="{{ route('events.create') }}"
                class="inline-block bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 transition font-semibold"
            >
                <i class="fas fa-plus mr-2"></i> Ajouter un événement
            </a>
        </div>
    @endif
</div>

<!-- Recherche JS -->
<script>
    function searchEvents() {
        const input = document.getElementById('searchInput').value.toLowerCase();
        const cards = document.querySelectorAll('#eventsGrid > div');

        cards.forEach(card => {
            const title = card.querySelector('h3').textContent.toLowerCase();
            const description = card.querySelector('p').textContent.toLowerCase();
            card.style.display =
                title.includes(input) || description.includes(input)
                    ? 'block'
                    : 'none';
        });
    }

    document.getElementById('searchInput')?.addEventListener('keyup', searchEvents);
</script>
@endsection
