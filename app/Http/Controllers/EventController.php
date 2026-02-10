<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
            if (config('filesystems.default') === 'cloudinary') {
                $uploaded = $request->file('image')->storeOnCloudinary('events');
                if ($uploaded) {
                    $validated['image'] = $uploaded->getSecurePath();
                }
            } else {
                $validated['image'] = $request->file('image')->store('events', 'public');
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
            if (config('filesystems.default') === 'cloudinary') {
                $uploaded = $request->file('image')->storeOnCloudinary('events');
                if ($uploaded) {
                    $validated['image'] = $uploaded->getSecurePath();
                }
            } else {
                if ($event->image) {
                    Storage::disk('public')->delete($event->image);
                }
                $validated['image'] = $request->file('image')->store('events', 'public');
            }
        }

        $event->update($validated);

        return redirect()->route('events.index')
            ->with('success', 'Événement modifié avec succès !');
    }

    public function destroy(Event $event)
    {
        abort_if(Auth::id() !== $event->user_id, 403);

        if (config('filesystems.default') !== 'cloudinary' && $event->image) {
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
