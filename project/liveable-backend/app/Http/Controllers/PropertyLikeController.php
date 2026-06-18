<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;

class PropertyLikeController extends Controller
{
    public function toggleLike(Request $request, Property $property)
    {
        $user = $request->user();
        $like = $user->likes()->where('property_id', $property->id)->first();

        if ($like) {
            $like->delete();
            return response()->json(['liked' => false]);
        }

        $user->likes()->create(['property_id' => $property->id]);
        return response()->json(['liked' => true]);
    }

    public function myLikes(Request $request)
    {
        $properties = $request->user()
            ->likes()
            ->with(['property.images'])
            ->get()
            ->map(function ($like) {
                $property = $like->property;
                if ($property) {
                    $property->images->transform(function ($image) {
                        $image->url = asset('storage/' . $image->path);
                        return $image;
                    });
                }
                return $property;
            })
            ->filter()
            ->values();

        return response()->json($properties);
    }
}
