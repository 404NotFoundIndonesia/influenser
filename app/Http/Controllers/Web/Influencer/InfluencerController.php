<?php

namespace App\Http\Controllers\Web\Influencer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Influencer\StoreInfluencerRequest;
use App\Http\Requests\Influencer\UpdateInfluencerRequest;
use App\Models\Influencer;
use App\Models\Niche;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class InfluencerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('influencer/Index', [
            'items' => Influencer::with(['key_opinion_leaders', 'niches'])
                ->filter($request->query('filter'))
                ->render($request->query('size'))
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('influencer/Create', [
            'niches' => Niche::all(['id', 'name']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInfluencerRequest $request): RedirectResponse
    {
        try {
            $input = $request->validated();
            unset($input['keyOpinionLeaders']);

            if ($request->hasFile('photo')) {
                $input['profile_picture_path'] = $request->file('photo')->storePublicly('influencers');
            }

            $influencer = Influencer::query()->create($input);
            foreach ($request->input('keyOpinionLeaders') as $key) {
                $influencer->key_opinion_leaders()->create($key);
            }

            if (count($request->input('niches', [])) > 0) {
                $influencer->niches()->sync($request->input('niches'));
            }

            return redirect()->route('influencer.show', ['influencer' => $influencer])->with('success', 'Influencer created successfully.');
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());

            return back()->with('error', 'Something went wrong.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Influencer $influencer): Response
    {
        $influencer->load(['key_opinion_leaders', 'niches']);
        return Inertia::render('influencer/Show', [
            'item' => $influencer,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Influencer $influencer): Response
    {
        $influencer->load(['key_opinion_leaders', 'niches']);
        return Inertia::render('influencer/Edit', [
            'item' => $influencer,
            'niches' => Niche::all(['id', 'name']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInfluencerRequest $request, Influencer $influencer): RedirectResponse
    {
        try {
            $input = $request->validated();
            unset($input['keyOpinionLeaders']);

            if ($request->hasFile('photo')) {
                $influencer->deletePicture();
                $input['profile_picture_path'] = $request->file('photo')->storePublicly('influencers');
            }

            $influencer->update($input);
            foreach ($request->input('keyOpinionLeaders') as $key) {
                $id = $key['id'];
                unset($key['id']);
                if ($id) {
                    $influencer->key_opinion_leaders()
                        ->firstWhere('id', '=', $id)
                        ->update($key);
                } else {
                    $influencer->key_opinion_leaders()->create($key);
                }
            }

            if (count($request->input('niches', [])) > 0) {
                $influencer->niches()->sync($request->input('niches'));
            }

            return back()->with('success', 'Influencer updated successfully.');
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());

            return back()->with('error', 'Something went wrong.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Influencer $influencer): RedirectResponse
    {
        try {
            $influencer->delete();

            return back()->with('success', 'Influencer deleted successfully.');
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());

            return back()->with('error', 'Something went wrong.');
        }
    }

    public function massDestroy(Request $request): RedirectResponse
    {
        try {
            Influencer::query()
                ->whereIn('id', $request->input('ids', []))
                ->delete();

            return back()->with('success', 'Influencer deleted successfully.');
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());

            return back()->with('error', 'Something went wrong.');
        }
    }
}
