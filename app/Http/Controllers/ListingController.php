<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Support\Markdown;
use Illuminate\Http\Request;

class ListingController extends Controller
{
    public function index()
    {
        return view('create-listing');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'position' => 'required|max:255',
            'church' => 'required|max:255',
            'city' => 'required|max:255',
            'state' => 'required|max:255',
            'content' => 'required',
            'email' => 'required|max:255',
            'phone' => 'required|max:255',
            'facebook' => 'nullable|max:255',
            'website' => 'nullable|max:255',
            'published_at' => ['nullable', 'date'],
        ]);

        $data = $this->getArr($data);

        $listing = new Listing($data);
        $listing->user_id = auth()->id();
        $listing->save();

        return redirect('/positions')->with('success', 'Position submitted. It will appear once an admin approves it.');
    }

    public function getArr(array $data): array
    {
        $data['city'] = strip_tags($data['city']);
        $data['state'] = strip_tags($data['state']);
        $data['position'] = strip_tags($data['position']);
        $data['content'] = strip_tags($data['content']);
        $data['church'] = strip_tags($data['church']);
        $data['email'] = strip_tags($data['email']);
        $data['phone'] = strip_tags($data['phone']);
        $data['facebook'] = strip_tags((string) ($data['facebook'] ?? ''));
        $data['website'] = strip_tags((string) ($data['website'] ?? ''));

        return $data;
    }

    public function showPositions()
    {
        $positions = Listing::query()
            ->where(function ($query) {
                $query->approved()->orWhere('user_id', auth()->id());
            })
            ->latest()
            ->cursorPaginate(10);

        return view('positions', ['positions' => $positions]);
    }

    public function showPosition(Listing $position)
    {
        if (! $position->isApproved() && $position->user_id !== auth()->id() && ! auth()->user()->is_admin) {
            abort(404);
        }

        $position['content'] = Markdown::render($position->content);

        return view('single-position', ['position' => $position]);
    }

    public function edit($id)
    {
        $listing = Listing::findOrFail($id);

        if ($listing->user_id !== auth()->id() && ! auth()->user()->is_admin) {
            abort(403);
        }

        return view('edit-listing', ['listing' => $listing]);
    }

    public function update(Request $request, $id)
    {
        $position = Listing::findOrFail($id);

        if ($position->user_id !== auth()->id() && ! auth()->user()->is_admin) {
            abort(403);
        }

        $data = $request->validate([
            'position' => 'required|max:255',
            'church' => 'required|max:255',
            'city' => 'required|max:255',
            'state' => 'required|max:255',
            'content' => 'required',
            'email' => 'required|max:255',
            'phone' => 'required|max:255',
            'facebook' => 'nullable|max:255',
            'website' => 'nullable|max:255',
            'published_at' => ['nullable', 'date'],
        ]);

        $data = $this->getArr($data);

        $position->fill($data);

        // A poster's changes go back through review; admin edits keep the status.
        $sentBackToReview = ! auth()->user()->is_admin && $position->isApproved();
        if ($sentBackToReview) {
            $position->approved_at = null;
        }

        $position->save();

        return redirect('/positions')->with('success', $sentBackToReview
            ? 'Changes saved. Your listing will reappear once an admin approves it.'
            : 'Listing Updated Successfully');
    }
}
