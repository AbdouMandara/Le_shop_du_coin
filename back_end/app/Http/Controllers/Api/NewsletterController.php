<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Newsletter;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function index()
    {
        $newsletters = Newsletter::orderBy('created_at', 'desc')->get();
        return response()->json($newsletters);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => 'nullable|email|max:255',
            'whatsapp_number' => 'required|string|max:255',
        ]);

        $newsletter = Newsletter::create($validated);

        return response()->json([
            'message' => 'Inscription réussie',
            'newsletter' => $newsletter
        ], 201);
    }
}
