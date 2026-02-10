<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

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
            'source_link' => 'required|url'
        ]);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('events', 'public');
            $validated['image'] = $imagePath;
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
        if (Auth::id() !== $event->user_id) {
            return redirect()->route('events.index')
                ->with('error', 'Vous n\'êtes pas autorisé à modifier cet événement.');
        }
        return view('events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        if (Auth::id() !== $event->user_id) {
            return redirect()->route('events.index')
                ->with('error', 'Vous n\'êtes pas autorisé à modifier cet événement.');
        }

        $validated = $request->validate([
            'title' => 'required|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'required|max:250',
            'source_link' => 'required|url'
        ]);

        if ($request->hasFile('image')) {
            if ($event->image) {
                Storage::disk('public')->delete($event->image);
            }
            $imagePath = $request->file('image')->store('events', 'public');
            $validated['image'] = $imagePath;
        }

        $event->update($validated);

        return redirect()->route('events.index')
            ->with('success', 'Événement modifié avec succès !');
    }

    public function destroy(Event $event)
    {
        if (Auth::id() !== $event->user_id) {
            return redirect()->route('events.index')
                ->with('error', 'Vous n\'êtes pas autorisé à supprimer cet événement.');
        }

        if ($event->image) {
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