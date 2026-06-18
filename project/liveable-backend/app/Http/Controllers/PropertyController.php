<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PropertyController extends Controller
{
    public function index()
    {
        $properties = Property::with('images', 'reviews')->get();

        $properties->transform(function ($property) {
            $property->images->transform(function ($image) {
                $image->url = asset('storage/' . $image->path);
                return $image;
            });

            $property->avaliation = $property->reviews->count()
                ? round($property->reviews->avg('rating'), 1)
                : 0;

            return $property;
        });

        return response()->json($properties, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'local'           => 'required|string',
            'type'            => 'required|string',
            'beds_qtd'        => 'required|integer',
            'toilette'        => 'required|integer',
            'rooms'        => 'required|integer',
            'area'            => 'required|integer',
            'property_title'  => 'required|string',
            'wifi'            => 'boolean',
            'tv'              => 'boolean',
            'cooler'          => 'boolean',
            'air_conditioning' => 'boolean',
            'washer'          => 'boolean',
            'microwave'       => 'boolean',
            'smoker'       => 'boolean',
            'pricePerDay'     => 'required|integer',
            'pricePerWeek'    => 'integer',
            'pricePerMonth'   => 'integer',
            'status'          => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $property = Auth::user()->property()->create($request->only([
            'local',
            'type',
            'beds_qtd',
            'toilette',
            'rooms',
            'area',
            'property_title',
            'wifi',
            'tv',
            'cooler',
            'air_conditioning',
            'washer',
            'microwave',
            'smoker',
            'pricePerDay',
            'status',
        ]));

        if ($request->hasFile('images')) {
            $directory = 'assets/images/properties/' . $request->property_title;
            Storage::disk('public')->makeDirectory($directory);

            foreach ($request->images as $image) {
                $path     = $image->storeAs($directory, $image->getClientOriginalName(), 'public');
                $newImage = PropertyImage::create([
                    'property_id' => $property->id,
                    'path'        => $path,
                ]);
                if (!isset($property->property_image_id)) {
                    $property->update(['property_image_id' => $newImage->id]);
                }
            }
        }

        return response()->json(['message' => 'Property Created'], 201);
    }

    public function show(Property $property)
    {
        $property->load('images', 'user');
        $property->images->transform(function ($image) {
            $image->url = asset('storage/' . $image->path);
            return $image;
        });
        return response()->json(['Propriedade' => $property]);
    }

    public function update(Request $request, $id)
    {
        $property = Property::findOrFail($id);
        $user     = $request->user();

        if ($user->role !== 'admin' && $property->user_id !== $user->id) {
            return response()->json(['message' => 'Sem permissão.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'property_title'   => 'sometimes|string|max:255',
            'local'            => 'sometimes|string|max:255',
            'area'             => 'sometimes|numeric|min:0',
            'type'             => 'sometimes|string|in:casa,apartamento,chacara',
            'beds_qtd'         => 'sometimes|integer|min:1',
            'toilette'         => 'sometimes|integer|min:1',
            'wifi'             => 'sometimes|boolean',
            'tv'               => 'sometimes|boolean',
            'cooler'           => 'sometimes|boolean',
            'air_conditioning' => 'sometimes|boolean',
            'washer'           => 'sometimes|boolean',
            'microwave'        => 'sometimes|boolean',
            'smoker'        => 'sometimes|boolean',
            'pricePerDay'      => 'sometimes|nullable|numeric|min:0',
            'pricePerWeek'     => 'sometimes|nullable|numeric|min:0',
            'pricePerMonth'    => 'sometimes|nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $property->update($request->only([
            'property_title',
            'local',
            'area',
            'type',
            'beds_qtd',
            'toilette',
            'wifi',
            'tv',
            'cooler',
            'air_conditioning',
            'washer',
            'microwave',
            'smoker',
            'pricePerDay',
            'pricePerWeek',
            'pricePerMonth',
        ]));

        return response()->json([
            'message'     => 'Propriedade atualizada com sucesso!',
            'Propriedade' => $property->fresh(),
        ]);
    }

    public function destroy(Request $request, Property $property)
    {
        $user = $request->user();

        if ($user->role !== 'admin' && $property->user_id !== $user->id) {
            return response()->json(['message' => 'Sem permissão.'], 403);
        }

        $property->delete();

        return response()->json(['message' => 'Propriedade deletada com sucesso!']);
    }

    public function toggleEnableProperty(Property $property)
    {
        if ($property->isEnabled($property)) {
            return response()->json(['message' => 'Propriedade Disponível'], 200);
        }
        return response()->json(['message' => 'Propriedade desabilitada pelo administrador'], 201);
    }

    public function myProperties()
    {
        $properties = Property::with('images', 'reviews')
            ->where('user_id', Auth::id())
            ->get();

        $properties->transform(function ($property) {
            $property->images->transform(function ($image) {
                $image->url = asset('storage/' . $image->path);
                return $image;
            });

            $property->avaliation = $property->reviews->count()
                ? round($property->reviews->avg('rating'), 1)
                : 0;

            return $property;
        });

        return response()->json($properties, 200);
    }

    public function myRent(Property $property)
    {
        $hasRent = $property->rents()
            ->where('user_id', auth()->id())
            ->exists();

        return response()->json(['has_rent' => $hasRent]);
    }

    // GET /api/properties/featured — retorna só as em alta
    public function featured()
    {
        $properties = Property::with('images', 'reviews')
            ->where('is_featured', true)
            ->get();

        $properties->transform(function ($property) {
            $property->images->transform(function ($image) {
                $image->url = asset('storage/' . $image->path);
                return $image;
            });

            $property->avaliation = $property->reviews->count()
                ? round($property->reviews->avg('rating'), 1)
                : 0;

            return $property;
        });

        return response()->json($properties, 200);
    }

    // PATCH /api/properties/{id}/featured — admin toggle
    public function toggleFeatured(Request $request, Property $property)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Sem permissão.'], 403);
        }

        $property->update(['is_featured' => $request->boolean('is_featured')]);

        return response()->json([
            'message'    => 'Atualizado com sucesso!',
            'is_featured' => $property->is_featured,
        ]);
    }
}
