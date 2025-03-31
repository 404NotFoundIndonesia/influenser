<?php

namespace App\Http\Controllers\Web\Essential;

use App\Http\Controllers\Controller;
use App\Http\Requests\Niche\StoreNicheRequest;
use App\Http\Requests\Niche\UpdateNicheRequest;
use App\Models\Niche;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class NicheController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('niche/Index', [
            'items' => Niche::query()
                ->filter($request->query('filter'))
                ->render($request->query('size'))
        ]);
    }

    public function store(StoreNicheRequest $request): RedirectResponse
    {
        try {
            $input = $request->validated();
            Niche::query()->create($input);

            return redirect()->route('niche.index')->with('success', 'Niche created successfully.');
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());
            dd($exception->getMessage());

            return back()->with('error', 'Something went wrong.');
        }
    }

    public function update(UpdateNicheRequest $request, Niche $niche): RedirectResponse
    {
        try {
            $input = $request->validated();
            $niche->update($input);

            return back()->with('success', 'Niche updated successfully.');
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());

            return back()->with('error', 'Something went wrong.');
        }
    }

    public function destroy(Niche $niche): RedirectResponse
    {
        try {
            $niche->delete();

            return back()->with('success', 'Niche deleted successfully.');
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());

            return back()->with('error', 'Something went wrong.');
        }
    }

    public function massDestroy(Request $request): RedirectResponse
    {
        try {
            Niche::query()
                ->whereIn('id', request('ids', []))
                ->delete();

            return back()->with('success', 'Niche deleted successfully.');
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());

            return back()->with('error', 'Something went wrong.');
        }
    }
}
