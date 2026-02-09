@extends('layouts.app')

@section('title', 'Modifier - ' . $event->title)

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">

        <!-- En-tête -->
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 p-6">
            <h2 class="text-xl sm:text-2xl font-bold text-white">
                <i class="fas fa-edit mr-2"></i> Modifier l'événement
            </h2>
            <p class="text-blue-200 text-sm mt-1">Mettez à jour les informations de l'événement</p>
        </div>

        <!-- Formulaire -->
        <div class="p-6">
            <form action="{{ route('events.update', $event) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Titre -->
                <div class="mb-5">
                    <label class="block text-gray-700 font-semibold mb-2 text-sm">Titre *</label>
                    <input type="text" name="title" value="{{ old('title', $event->title) }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 text-sm @error('title') border-red-500 @enderror"
                        required>
                    @error('title')
                        <p class="text-red-500 text-xs mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                    @enderror
                </div>

                <!-- Image avec prévisualisation -->
                <div class="mb-5">
                    <label class="block text-gray-700 font-semibold mb-2 text-sm">Image actuelle</label>
                    <div id="previewContainer" class="mb-3">
                        <img id="imagePreview" src="{{ str_starts_with($event->image, 'http') ? $event->image : asset('storage/' . $event->image) }}" alt="{{ $event->title }}" class="max-h-40 mx-auto rounded-lg">
                    </div>

                    <label class="block text-gray-700 font-semibold mb-2 text-sm">Nouvelle image (optionnelle)</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-blue-400 transition cursor-pointer" id="dropZone">
                        <i class="fas fa-cloud-upload-alt text-3xl text-gray-300 mb-2"></i>
                        <p class="text-gray-500 text-sm">Cliquez ou glissez une image ici</p>
                        <p class="text-gray-400 text-xs mt-1">JPEG, PNG, GIF - Max 2Mo</p>
                        <input type="file" name="image" id="imageInput" accept="image/*" class="hidden">
                    </div>
                    @error('image')
                        <p class="text-red-500 text-xs mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="mb-5">
                    <label class="block text-gray-700 font-semibold mb-2 text-sm">Description *</label>
                    <textarea name="description" rows="3" maxlength="250"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 text-sm resize-none @error('description') border-red-500 @enderror"
                        required>{{ old('description', $event->description) }}</textarea>
                    <div class="flex justify-between mt-1">
                        <p class="text-red-500 text-xs hidden" id="charError"><i class="fas fa-exclamation-circle mr-1"></i>Maximum 250 caractères</p>
                        <p class="text-gray-400 text-xs ml-auto" id="charCount">{{ strlen($event->description) }} / 250</p>
                    </div>
                    @error('description')
                        <p class="text-red-500 text-xs mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                    @enderror
                </div>

                <!-- Lien source -->
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2 text-sm">Lien source *</label>
                    <div class="relative">
                        <i class="fas fa-link absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="url" name="source_link" value="{{ old('source_link', $event->source_link) }}"
                            placeholder="https://example.com"
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 text-sm @error('source_link') border-red-500 @enderror"
                            required>
                    </div>
                    @error('source_link')
                        <p class="text-red-500 text-xs mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                    @enderror
                </div>

                <!-- Boutons -->
                <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3">
                    <button type="submit" class="flex-1 bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition font-semibold flex items-center justify-center">
                        <i class="fas fa-save mr-2"></i> Mettre à jour
                    </button>
                    <a href="{{ route('events.show', $event) }}" class="flex-1 bg-gray-100 text-gray-600 px-6 py-3 rounded-lg hover:bg-gray-200 transition font-semibold text-center flex items-center justify-center">
                        <i class="fas fa-times mr-2"></i> Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Prévisualisation image
    const dropZone = document.getElementById('dropZone');
    const imageInput = document.getElementById('imageInput');
    const imagePreview = document.getElementById('imagePreview');

    dropZone.addEventListener('click', () => imageInput.click());

    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });

    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('border-blue-500', 'bg-blue-50');
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('border-blue-500', 'bg-blue-50');
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('border-blue-500', 'bg-blue-50');
        const file = e.dataTransfer.files[0];
        if (file && file.type.startsWith('image/')) {
            imageInput.files = e.dataTransfer.files;
            const reader = new FileReader();
            reader.onload = function(ev) {
                imagePreview.src = ev.target.result;
            };
            reader.readAsDataURL(file);
        }
    });

    // Compteur de caractères
    const textarea = document.querySelector('textarea[name="description"]');
    const charCount = document.getElementById('charCount');
    const charError = document.getElementById('charError');

    textarea.addEventListener('input', function() {
        const length = this.value.length;
        charCount.textContent = `${length} / 250`;
        if (length > 250) {
            charError.classList.remove('hidden');
            charCount.classList.add('text-red-500');
        } else {
            charError.classList.add('hidden');
            charCount.classList.remove('text-red-500');
        }
    });
</script>
@endsection