<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class EventController extends Controller
{
    /**
     * Affiche la liste des événements
     */
    public function index()
    {
        $events = Event::with('user')->latest()->paginate(12);
        return view('events.index', compact('events'));
    }

    /**
     * Formulaire de création d'événement
     */
    public function create()
    {
        return view('events.create');
    }

    /**
     * Stocke un nouvel événement
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'required|max:250',
            'source_link' => 'required|url',
        ]);

        if ($request->hasFile('image')) {
            try {
                if (config('filesystems.default') === 'cloudinary') {
                    // Upload vers Cloudinary
                    $uploaded = $request->file('image')->storeOnCloudinary('events');
                    $validated['image'] = $uploaded ? $uploaded->getSecurePath() : null;
                } else {
                    // Stockage local
                    $validated['image'] = $request->file('image')->store('events', 'public');
                }
            } catch (\Exception $e) {
                Log::error('Upload error: ' . $e->getMessage());
                return back()->with('error', 'Erreur upload: ' . $e->getMessage());
            }
        }

        $validated['user_id'] = Auth::id();
        Event::create($validated);

        return redirect()->route('events.index')->with('success', 'Événement ajouté avec succès !');
    }

    /**
     * Affiche un événement
     */
    public function show(Event $event)
    {
        $event->increment('views');
        return view('events.show', compact('event'));
    }

    /**
     * Formulaire d'édition
     */
    public function edit(Event $event)
    {
        abort_if(Auth::id() !== $event->user_id, 403);
        return view('events.edit', compact('event'));
    }

    /**
     * Met à jour un événement
     */
    public function update(Request $request, Event $event)
    {
        abort_if(Auth::id() !== $event->user_id, 403);

        $validated = $request->validate([
            'title' => 'required|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'required|max:250',
            'source_link' => 'required|url',
        ]);

        if ($request->hasFile('image')) {
            try {
                if (config('filesystems.default') === 'cloudinary') {
                    $uploaded = $request->file('image')->storeOnCloudinary('events');
                    if ($uploaded) {
                        $validated['image'] = $uploaded->getSecurePath();
                    }
                } else {
                    // Supprimer l'ancienne image locale si elle existe
                    if ($event->image && !str_starts_with($event->image, 'http')) {
                        Storage::disk('public')->delete($event->image);
                    }
                    $validated['image'] = $request->file('image')->store('events', 'public');
                }
            } catch (\Exception $e) {
                Log::error('Update image error: ' . $e->getMessage());
                return back()->with('error', 'Erreur upload: ' . $e->getMessage());
            }
        }

        $event->update($validated);

        return redirect()->route('events.index')->with('success', 'Événement modifié avec succès !');
    }

    /**
     * Supprime un événement
     */
    public function destroy(Event $event)
    {
        abort_if(Auth::id() !== $event->user_id, 403);

        // Supprime l'image locale uniquement si ce n'est pas Cloudinary
        if (config('filesystems.default') !== 'cloudinary' && $event->image && !str_starts_with($event->image, 'http')) {
            Storage::disk('public')->delete($event->image);
        }

        $event->delete();

        return redirect()->route('events.index')->with('success', 'Événement supprimé avec succès !');
    }

    /**
     * Page à propos
     */
    public function about()
    {
        return view('about');
    }
}
