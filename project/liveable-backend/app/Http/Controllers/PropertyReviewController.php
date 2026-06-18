<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PropertyReviewController extends Controller
{
    public function index(Property $property)
    {
        $reviews = $property->reviews()
            ->with('user:id,name')
            ->latest()
            ->get()
            ->map(fn($r) => [
                'id'           => $r->id,
                'author'       => $r->user->name,
                'rating'       => $r->rating,
                'text'         => $r->comment,
                'date'         => $r->created_at->toDateString(),
                'propertyName' => $property->property_title,
            ]);

        return response()->json(['data' => $reviews]);
    }

    public function store(Request $request, Property $property)
    {
        $validated = $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
            'title'   => 'nullable|string|max:255',
        ]);

        $jaAvaliou = Review::where('property_id', $property->id)
            ->where('user_id', Auth::id())
            ->exists();

        if ($jaAvaliou) {
            return response()->json(['message' => 'Você já avaliou este imóvel.'], 422);
        }

        $review = Review::create([
            'property_id' => $property->id,
            'user_id'     => Auth::id(),
            'rating'      => $validated['rating'],
            'title'       => $validated['title'] ?? null,
            'comment'     => $validated['comment'],
        ]);

        return response()->json([
            'message' => 'Avaliação enviada com sucesso!',
            'data'    => [
                'id'           => $review->id,
                'author'       => Auth::user()->name,
                'rating'       => $review->rating,
                'text'         => $review->comment,
                'date'         => $review->created_at->toDateString(),
                'propertyName' => $property->property_title,
            ],
        ], 201);
    }
}
