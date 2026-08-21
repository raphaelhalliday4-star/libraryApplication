<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Publisher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use PhpParser\Node\Stmt\TryCatch;
use OpenApi\Attributes as OA;

class PublisherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
      #[OA\Get(
        path: "/api/publishers",
        summary: "Get all publishers",
        tags: ["Publishers"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Publishers retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Publishers retrieved successfully"),
                        new OA\Property(property: "publishers", type: "object"),
                    ]
                )
            ),
        ]
    )]
    public function index()
    {
        Gate::authorize('viewAny', Publisher::class);
        $publishers = Publisher::all();
        return response()->json($publishers);
    }

    /**
     * Store a newly created resource in storage.
     */
    #[OA\Post(
        path: "/api/publishers",
        summary: "Create a new publisher",
        tags: ["Publishers"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Sunrise Books"),
                    new OA\Property(property: "address", type: "string", example: "123 Main Street"),
                    new OA\Property(property: "phone", type: "string", example: "+1234567890"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "contact@sunrisebooks.com"),
                    new OA\Property(property: "website", type: "string", format: "uri", example: "https://sunrisebooks.com"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Publisher created successfully"),
            new OA\Response(response: 422, description: "Validation error"),
             new OA\Response(response: '401', description: "Validation error"),
        ]
    )]
    public function store(Request $request)
    {
        if (!auth('api')->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        Gate::authorize('create', Publisher::class);
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url',
        ]);

        $publisher = Publisher::create([
            'name' => $request->name,
            'address' => $request->address,
            'phone' => $request->phone,
            'email' => $request->email,
            'website' => $request->website,
        ]);

        return response()->json([
            'message' => 'Publisher created successfully',
            'publisher' => $publisher,
        ]);
    }

    /**
     * Display the specified resource.
     */
        #[OA\Get(
        path: "/api/publishers/{id}",
        summary: "Get publisher details",
        tags: ["Publishers"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Publisher id",
                required: true,
                in: "path",
                schema: new OA\Schema(type: "integer")
            )
        ],
       responses: [
            new OA\Response(
                response: 201,
                description: "Publisher retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Publisher retrieved successfully"),
                        new OA\Property(property: "publisher", type: "object"),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validation error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "The name field is required."),
                        new OA\Property(property: "errors", type: "object"),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Unauthorized",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "error", type: "string", example: "Unauthorized"),
                    ]
                )
            ),
        ]
    )]
    public function show($id)
    {
        $publisher = Publisher::findOrFail($id);
        Gate::authorize('view', $publisher);

        return response()->json([
            'publisher' => $publisher,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    #[OA\Put(
        path: "/api/publishers/{id}",
        summary: "Update an existing publisher",
        tags: ["Publishers"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Publisher id",
                required: true,
                in: "path",
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "country", "birth_date"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Jane Doe"),
                    new OA\Property(property: "biography", type: "string", example: "Jane Doe is a renowned author."),
                    new OA\Property(property: "country", type: "string", example: "USA"),
                    new OA\Property(property: "birth_date", type: "string", format: "date", example: "1980-01-01"),
                    new OA\Property(property: "photo", type: "string", example: "https://example.com/photo.jpg"),
                    new OA\Property(property: "status", type: "string", example: "active"),

                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Author updated successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Author updated successfully"),
                        new OA\Property(property: "author", type: "object"),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validation error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "The name field is required."),
                        new OA\Property(property: "errors", type: "object"),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Unauthorized",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "error", type: "string", example: "Unauthorized"),
                    ]
                )
            ),
        ]
    )]
    public function update(Request $request,$id)
    {
        if (!auth('api')->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $publisher = Publisher::findOrFail($id);
        Gate::authorize('update', $publisher);

        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url',
        ]);

        if(!$publisher){
            return response()->json([
                'message' => 'Publisher not found',
            ], 404);
        }

      if($request->has('name')){
            $publisher->name = $request->name;
        }
        if($request->has('address')){
            $publisher->address = $request->address;
        }
        if($request->has('phone')){
            $publisher->phone = $request->phone;
        }  
        if($request->has('email')){
                $publisher->email = $request->email;
            }
        if($request->has('website')){
                $publisher->website = $request->website;
            }

        $publisher->save();

        return response()->json([
            'message' => 'Publisher updated successfully',
            'publisher' => $publisher,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    #[OA\Delete(
        path: "/api/publishers/{id}",
        summary: "Delete an existing publisher",
        tags: ["Publishers"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Publisher id",
                required: true,
                in: "path",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Publisher deleted successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Publisher deleted successfully"),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Publisher not found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Publisher not found"),
                    ]
                )
            ),
        ]
    )]
    public function destroy($id)
    {
        if (!auth('api')->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $publisher = Publisher::findOrFail($id);
        Gate::authorize('delete', $publisher);

        if(!$publisher){
            return response()->json([
                'message' => 'Publisher not found',
            ], 404);
        }
        
        $publisher->delete();

        return response()->json([
            'message' => 'Publisher deleted successfully',
        ]);
    }
}
