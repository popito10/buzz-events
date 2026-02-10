<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::with('user')->latest()->paginate(12);
        return view('events.index', compact('events'));
    }

    public function create()
{
    dd('TEST: create() method is called');
    return view('events.create');
}
    public function store(Request $request)
    {
        // DEBUG - AFFICHER LES VARIABLES
        dd([
            'APP_ENV' => env('APP_ENV'),
            'CLOUDINARY_CLOUD_NAME' => env('CLOUDINARY_CLOUD_NAME'),
            'CLOUDINARY_API_KEY' => env('CLOUDINARY_API_KEY'),
            'CLOUDINARY_API_SECRET' => env('CLOUDINARY_API_SECRET'),
            'Testing' => 'dd() works'
        ]);

        $validated = $request->validate([
            'title' => 'required|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'required|max:250',
            'source_link' => 'required|url'
        ]);

        if ($request->hasFile('image')) {
            try {
                if (env('APP_ENV') === 'production') {
                    $result = Cloudinary::upload($request->file('image')->getRealPath(), [
                        'folder' => 'events'
                    ]);
                    $validated['image'] = $result->getSecurePath();
                } else {
                    $imagePath = $request->file('image')->store('events', 'public');
                    $validated['image'] = $imagePath;
                }
            } catch (\Exception $e) {
                Log::error('Upload error: ' . $e->getMessage());
                return back()->with('error', 'Erreur: ' . $e->getMessage());
            }
        }

        $validated['user_id'] = Auth::id();
        Event::create($validated);

        return redirect()->route('events.index')->with('success', 'Événement ajouté !');
    }

    public function show(Event $event)
    {
        $event->increment('views');
        return view('events.show', compact('event'));
    }

    public function edit(Event $event)
    {
        if (Auth::id() !== $event->user_id) {
            return redirect()->route('events.index')->with('error', 'Non autorisé');
        }
        return view('events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        if (Auth::id() !== $event->user_id) {
            return redirect()->route('events.index')->with('error', 'Non autorisé');
        }

        $validated = $request->validate([
            'title' => 'required|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'required|max:250',
            'source_link' => 'required|url'
        ]);

        if ($request->hasFile('image')) {
            try {
                if (env('APP_ENV') === 'production') {
                    $result = Cloudinary::upload($request->file('image')->getRealPath(), [
                        'folder' => 'events'
                    ]);
                    $validated['image'] = $result->getSecurePath();
                } else {
                    if ($event->image && !str_starts_with($event->image, 'http')) {
                        Storage::disk('public')->delete($event->image);
                    }
                    $imagePath = $request->file('image')->store('events', 'public');
                    $validated['image'] = $imagePath;
                }
            } catch (\Exception $e) {
                Log::error('Update error: ' . $e->getMessage());
                return back()->with('error', 'Erreur: ' . $e->getMessage());
            }
        }

        $event->update($validated);
        return redirect()->route('events.index')->with('success', 'Événement modifié !');
    }

    public function destroy(Event $event)
    {
        if (Auth::id() !== $event->user_id) {
            return redirect()->route('events.index')->with('error', 'Non autorisé');
        }

        if (env('APP_ENV') !== 'production' && $event->image && !str_starts_with($event->image, 'http')) {
            Storage::disk('public')->delete($event->image);
        }

        $event->delete();
        return redirect()->route('events.index')->with('success', 'Événement supprimé !');
    }

    public function about()
    {
        return view('about');
    }
}