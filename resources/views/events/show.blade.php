@extends('layouts.app')

@section('title', $event->title)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">

        <!-- Image -->
        <div class="relative">
            <img src="{{ str_starts_with($event->image, 'http') ? $event->image : asset('storage/' . $event->image) }}" alt="{{ $event->title }}" class="w-full h-56 sm:h-72 lg:h-96 object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent"></div>
            <div class="absolute bottom-4 left-4 right-4">
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-white">{{ $event->title }}</h1>
            </div>
        </div>

        <!-- Contenu -->
        <div class="p-5 sm:p-8">

            <!-- Métadonnées -->
            <div class="flex flex-wrap items-center gap-3 text-gray-500 text-sm mb-6">
                <span class="flex items-center bg-gray-100 px-3 py-1 rounded-full">
                    <i class="fas fa-eye mr-2 text-purple-500"></i>{{ $event->views }} vues
                </span>
                <span class="flex items-center bg-gray-100 px-3 py-1 rounded-full">
                    <i class="fas fa-calendar mr-2 text-pink-500"></i>{{ $event->created_at->format('d/m/Y à H:i') }}
                </span>
                @if($event->user)
                    <span class="flex items-center bg-gray-100 px-3 py-1 rounded-full">
                        <i class="fas fa-user mr-2 text-blue-500"></i>{{ $event->user->name }}
                    </span>
                @endif
            </div>

            <!-- Description -->
            <p class="text-gray-700 text-base sm:text-lg mb-6 leading-relaxed">{{ $event->description }}</p>

            <!-- Boutons principaux -->
            <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3 mb-6">
                <a href="{{ $event->source_link }}" target="_blank" class="flex-1 bg-pink-600 text-white px-5 py-3 rounded-lg hover:bg-pink-700 transition font-semibold flex items-center justify-center">
                    <i class="fas fa-external-link-alt mr-2"></i> Voir la source
                </a>
                <a href="{{ route('events.index') }}" class="flex-1 bg-gray-100 text-gray-600 px-5 py-3 rounded-lg hover:bg-gray-200 transition font-semibold flex items-center justify-center">
                    <i class="fas fa-arrow-left mr-2"></i> Retour
                </a>
            </div>

            <!-- Actions admin (uniquement pour le créateur) -->
            @auth
                @if(Auth::id() === $event->user_id)
                    <div class="border-t pt-5 flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                        <a href="{{ route('events.edit', $event) }}" class="flex-1 sm:flex-none bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm flex items-center justify-center">
                            <i class="fas fa-edit mr-1"></i> Modifier
                        </a>
                        <form action="{{ route('events.destroy', $event) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet événement ?')" class="flex-1 sm:flex-none">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition text-sm flex items-center justify-center">
                                <i class="fas fa-trash mr-1"></i> Supprimer
                            </button>
                        </form>
                    </div>
                @endif
            @endauth
        </div>
    </div>
</div>
@endsection