<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Author;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use PhpParser\Node\Stmt\TryCatch;
use OpenApi\Attributes as OA;

class AuthorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    #[OA\Get(
        path: "/api/authors",
        summary: "Get all authors",
        tags: ["Authors"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Authors retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Authors retrieved successfully"),
                        new OA\Property(property: "authors", type: "object"),
                    ]
                )
            ),
        ]
    )]
    public function index()
    {
        Gate::authorize('viewAny', Author::class);
        $authors = Author::all();
        return response()->json([
            'message' => 'Authors retrieved successfully',
            'authors' => $authors,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
           #[OA\Post(
        path: "/api/authors",
        summary: "Create a new author",
        tags: ["Authors"],
        security: [["bearerAuth" => []]],
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
                description: "Author created successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Author created successfully"),
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
    public function store(Request $request)
    {
        if (!auth('api')->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        Gate::authorize('create', Author::class);

        $request->validate([
            'name' => 'required|string',
            'biography' => 'nullable|string',
            'country' => 'required|string',
            'birth_date' => 'required|date',
            'photo' => 'nullable|string',
            'status' => 'string|in:active,inactive',
        ]);
        
  try {
        $author = Author::create([
            'name' => $request->name,
            'biography' => $request->biography,
            'country' => $request->country,
            'birth_date' => $request->birth_date,
            'photo' => $request->photo,
            'status' => $request->status,
        ]);
           
        } catch (\Exception $e) {
            return response()->json(['error' => 'Author creation failed', 'message' => $e->getMessage()], 500);
        }

        return response()->json([
            'message' => 'Author created successfully',
            'author' => $author,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
        
     #[OA\Get(
        path: "/api/authors/{id}",
        summary: "Get author details",
        tags: ["Authors"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Author id",
                required: true,
                in: "path",
                schema: new OA\Schema(type: "integer")
            )
        ],
       responses: [
            new OA\Response(
                response: 201,
                description: "Author retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Author retrieved successfully"),
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
    public function show($id)
    {
        $author = Author::findOrFail($id);

        Gate::authorize('viewAny', $author);
        if (!$author) {
            return response()->json(['error' => 'Author not found'], 404);
        }

        return response()->json([
            'message' => 'Author retrieved successfully',
            'author' => $author,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */

#[OA\Put(
        path: "/api/authors/{id}",
        summary: "Update an existing author",
        tags: ["Authors"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Author id",
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
        // if (!auth('api')->check()) {
        //     return response()->json(['error' => 'Unauthorized'], 401);
        // }

        $author = Author::findOrFail($id);

        Gate::authorize('update',$author);

        $request->validate([
            'name' => 'string|max:255',
            'biography' => 'nullable|string',
            'country' => 'string|max:255',
            'birth_date' => 'date',
            'photo' => 'nullable|string|max:255',
            'status' => 'string|in:active,inactive',
        ]);

         if($request->has('name')){
            $author->name = $request->name;
        }
        if($request->has('biography')){
            $author->biography = $request->biography;
        }
        if($request->has('country')){
            $author->country = $request->country;
        }
        if($request->has('birth_date')){
            $author->birth_date = $request->birth_date;
        }
        if($request->has('photo')){
            $author->photo = $request->photo;
        }
        if($request->has('status')){
            $author->status = $request->status;
        }

        $author->save();

        return response()->json([
            'message' => 'Author updated successfully',
            'author' => $author,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    #[OA\Delete(
        path: "/api/authors/{id}",
        summary: "Delete an existing author",
        tags: ["Authors"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Author id",
                required: true,
                in: "path",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Author deleted successfully"),
            new OA\Response(response: 404, description: "Author not found"),
        ]
    )]
    public function destroy($id)
    {
        // if (!auth('api')->check()) {
        //     return response()->json(['error' => 'Unauthorized'], 401);
        // }

        $author = Author::findOrFail($id);
        Gate::authorize('delete',$author);

        if (!$author) {
            return response()->json(['error' => 'Author not found'], 404);
        }

        $author->delete();

        return response()->json([
            'message' => 'Author deleted successfully',
        ]);
    }
}
