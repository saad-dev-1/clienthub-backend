<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $clients = $request->user()
            ->clients()
            ->latest()
            ->get();

        return response()->json($clients);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $client = $request->user()->clients()->create($validated);

        return response()->json($client, 201);
    }

    public function show(Request $request, Client $client)
    {
        $this->authorizeOwnership($request, $client);

        return response()->json($client);
    }

    public function update(Request $request, Client $client)
    {
        $this->authorizeOwnership($request, $client);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $client->update($validated);

        return response()->json($client);
    }

    public function destroy(Request $request, Client $client)
    {
        $this->authorizeOwnership($request, $client);
        $client->delete();

        return response()->json(['message' => 'Client deleted.']);
    }

    protected function authorizeOwnership(Request $request, Client $client): void
    {
        if ($client->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized.');
        }
    }
}