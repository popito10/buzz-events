<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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
        return view('events.create');
    }

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
                if (env('APP_ENV') === 'production') {
                    // Upload vers Cloudinary
                    $uploaded = Cloudinary::upload(
                        $request->file('image')->getRealPath(),
                        [
                            'folder' => 'events',
                            'upload_preset' => env('CLOUDINARY_UPLOAD_PRESET'), // preset "events"
                        ]
                    );

                    $validated['image'] = $uploaded->getSecurePath();
                } else {
                    // Stockage local en développement
                    $validated['image'] = $request->file('image')->store('events', 'public');
                }
            } catch (\Exception $e) {
                Log::error('Upload error: ' . $e->getMessage());
                return back()->with('error', 'Erreur upload: ' . $e->getMessage());
            }
        }

        $validated['user_id'] = Auth::id();
        Event::create($validated);

        return redirect()->route('events.index')
            ->with('success', 'Événement ajouté avec succès !');
    }

    public function show(Event $event)
    {
        $event->increment('views');
        return view('events.show', compact('event'));
    }

    public function edit(Event $event)
    {
        abort_if(Auth::id() !== $event->user_id, 403);
        return view('events.edit', compact('event'));
    }

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
                if (env('APP_ENV') === 'production') {
                    $uploaded = Cloudinary::upload(
                        $request->file('image')->getRealPath(),
                        [
                            'folder' => 'events',
                            'upload_preset' => env('CLOUDINARY_UPLOAD_PRESET'),
                        ]
                    );

                    $validated['image'] = $uploaded->getSecurePath();
                } else {
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

        return redirect()->route('events.index')
            ->with('success', 'Événement modifié avec succès !');
    }

    public function destroy(Event $event)
    {
        abort_if(Auth::id() !== $event->user_id, 403);

        if (env('APP_ENV') !== 'production' && $event->image && !str_starts_with($event->image, 'http')) {
            Storage::disk('public')->delete($event->image);
        }

        $event->delete();

        return redirect()->route('events.index')
            ->with('success', 'Événement supprimé avec succès !');
    }

    public function about()
    {
        return view('about');
    }
}
