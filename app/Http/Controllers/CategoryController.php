<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use PhpParser\Node\Stmt\TryCatch;
use OpenApi\Attributes as OA;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
          #[OA\Get(
        path: "/api/categories",
        summary: "Get all categories",
        tags: ["Categories"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Categories retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Categories retrieved successfully"),
                        new OA\Property(property: "categories", type: "object"),
                    ]
                )
            ),
        ]
    )]
    public function index()
    {
        $category = Category::all();
        return response()->json([
            'categories' => $category,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
        #[OA\Post(
        path: "/api/categories",
        summary: "Create a new category",
        tags: ["Categories"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Fiction"),
                    new OA\Property(property: "description", type: "string", example: "Books of fictional nature"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Category created successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "category created successfully"),
                        new OA\Property(property: "book", type: "object"),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Book not found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Book not found"),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validation error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "The isbn field has already been taken."),
                        new OA\Property(property: "errors", type: "object"),
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
        Gate::authorize('create', Category::class);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $category = Category::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json([
            'message' => 'Category created successfully',
            'category' => $category,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
            #[OA\Get(
        path: "/api/categories/{id}",
        summary: "Get category details",
        tags: ["Categories"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Category id",
                required: true,
                in: "path",
                schema: new OA\Schema(type: "integer")
            )
        ],
       responses: [
            new OA\Response(
                response: 201,
                description: "Category retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Category retrieved successfully"),
                        new OA\Property(property: "category", type: "object"),
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
        $category = Category::findOrFail($id);

        if(!$category) {
            return response()->json([
                'message' => 'Category not found',
            ], 404);
        }

        return response()->json([
            'category' => $category,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
     #[OA\Put(
        path: "/api/categories/{id}",
        summary: "Update an existing category",
        tags: ["Categories"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Category id",
                required: true,
                in: "path",
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Fiction"),
                    new OA\Property(property: "description", type: "string", example: "Books in the fiction genre"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Category updated successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Category updated successfully"),
                        new OA\Property(property: "category", type: "object"),
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
    public function update(Request $request, $id)
    {
        if (!auth('api')->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $category = Category::findOrFail($id);

        Gate::authorize('update', $category);

        if(!$category){
            return response()->json([
                'message' => 'Category not found',
            ], 404);
        }

        if($request->has('name')){
            $category->name = $request->name;
        }
        if($request->has('description')){
            $category->description = $request->description;
    }

        $category->save();

        return response()->json([
            'message' => 'Category updated successfully',
            'category' => $category,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    #[OA\Delete(
        path: "/api/categories/{id}",
        summary: "Delete an existing category",
        tags: ["Categories"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Category id",
                required: true,
                in: "path",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Category deleted successfully"),
            new OA\Response(response: 404, description: "Category not found"),
        ]
    )]
    public function destroy($id)
    {
        if (!auth('api')->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $category = Category::findOrFail($id);

        Gate::authorize('delete', $category);

        if(!$category){
            return response()->json([
                'message' => 'Category not found',
            ], 404);
        }

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully',
        ]);
    }
}
