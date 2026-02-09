@extends('layouts.app')

@section('title', 'Modifier - ' . $event->title)

@section('content')
<div class="max-w-2xl mx-auto px-4">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">

        <div class="bg-gradient-to-r from-blue-600 to-purple-600 p-6">
            <h2 class="text-2xl font-bold text-white">Modifier l'événement</h2>
        </div>

        <div class="p-6">
            <form action="{{ route('events.update', $event) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Titre -->
                <div class="mb-4">
                    <label class="font-semibold">Titre</label>
                    <input type="text" name="title"
                           value="{{ old('title', $event->title) }}"
                           class="w-full border rounded p-2" required>
                </div>

                <!-- Image -->
                <div class="mb-4">
                    <label class="font-semibold">Image actuelle</label>

                    <img id="imagePreview"
                         class="max-h-40 mx-auto rounded my-2"
                         src="{{ $event->image
                            ? (str_starts_with($event->image, 'http')
                                ? $event->image
                                : asset('storage/' . $event->image))
                            : asset('images/placeholder.png') }}"
                         alt="Image">

                    <input type="file" name="image" id="imageInput" class="mt-2">
                </div>

                <!-- Description -->
                <div class="mb-4">
                    <label class="font-semibold">Description</label>
                    <textarea name="description" rows="3"
                              class="w-full border rounded p-2"
                              maxlength="250"
                              required>{{ old('description', $event->description) }}</textarea>
                </div>

                <!-- Lien -->
                <div class="mb-6">
                    <label class="font-semibold">Lien source</label>
                    <input type="url" name="source_link"
                           value="{{ old('source_link', $event->source_link) }}"
                           class="w-full border rounded p-2" required>
                </div>

                <button class="bg-blue-600 text-white px-4 py-2 rounded">
                    Mettre à jour
                </button>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('imageInput').addEventListener('change', e => {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = ev => {
        document.getElementById('imagePreview').src = ev.target.result;
    };
    reader.readAsDataURL(file);
});
</script>
@endsection
