<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookRequest;
use Illuminate\Http\Request;
use App\Models\Book;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use PhpParser\Node\Stmt\TryCatch;
use OpenApi\Attributes as OA;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    #[OA\Get(
        path: "/api/books",
        summary: "Get all books",
        tags: ["Books"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Books retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "books", type: "array", items: new OA\Items(type: "object")),
                    ]
                )
            ),
        ]
    )]
    public function index()
    {
       
        $books = Book::latest()->get();
        return response()->json([
            'books' => $books,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    #[OA\Post(
        path: "/api/books",
        summary: "Create a new book",
        tags: ["Books"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["title", "isbn", "author_id", "publisher_id", "category_id", "copies", "available_copies"],
                properties: [
                    new OA\Property(property: "title", type: "string", example: "The Great Gatsby"),
                    new OA\Property(property: "isbn", type: "string", example: "9780743273565"),
                    new OA\Property(property: "author_id", type: "integer", example: 1),
                    new OA\Property(property: "publisher_id", type: "integer", example: 1),
                    new OA\Property(property: "category_id", type: "integer", example: 1),
                    new OA\Property(property: "description", type: "string", example: "A classic American novel"),
                    new OA\Property(property: "edition", type: "string", example: "First Edition"),
                    new OA\Property(property: "publication_year", type: "integer", example: 1925),
                    new OA\Property(property: "language", type: "string", example: "English"),
                    new OA\Property(property: "pages", type: "integer", example: 180),
                    new OA\Property(property: "cover_image", type: "string", example: "https://example.com/cover.jpg"),
                    new OA\Property(property: "copies", type: "integer", example: 5),
                    new OA\Property(property: "available_copies", type: "integer", example: 3),
                    new OA\Property(property: "location", type: "string", example: "Section A, Shelf 1"),
                    new OA\Property(property: "status", type: "string", example: "active"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Book created successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Book created successfully"),
                        new OA\Property(property: "book", type: "object"),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validation error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "The title field is required."),
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
    public function store(BookRequest $request)
    {
        Gate::authorize('create', Book::class);

        if (!auth('api')->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

       $book = Book::create($request->validated());

        return response()->json([
            'message' => 'Book created successfully',
            'book' => $book,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    #[OA\Get(
        path: "/api/books/{id}",
        summary: "Get book details",
        tags: ["Books"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Book id",
                required: true,
                in: "path",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Book retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
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
        ]
    )]
    public function show($id)
    {
        $book = Book::findOrFail($id);

        if(!$book){
            return response()->json([
                'message'=> 'Book not found',
                'status'=> 404
            ], 404);
        }
        return response()->json([
            'book' => $book,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    #[OA\Put(
        path: "/api/books/{id}",
        summary: "Update an existing book",
        tags: ["Books"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Book id",
                required: true,
                in: "path",
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "title", type: "string", example: "The Great Gatsby"),
                    new OA\Property(property: "isbn", type: "string", example: "9780743273565"),
                    new OA\Property(property: "author_id", type: "integer", example: 1),
                    new OA\Property(property: "publisher_id", type: "integer", example: 1),
                    new OA\Property(property: "category_id", type: "integer", example: 1),
                    new OA\Property(property: "description", type: "string", example: "Updated description"),
                    new OA\Property(property: "edition", type: "string", example: "Second Edition"),
                    new OA\Property(property: "publication_year", type: "integer", example: 1925),
                    new OA\Property(property: "language", type: "string", example: "English"),
                    new OA\Property(property: "pages", type: "integer", example: 220),
                    new OA\Property(property: "cover_image", type: "string", example: "https://example.com/cover-updated.jpg"),
                    new OA\Property(property: "copies", type: "integer", example: 10),
                    new OA\Property(property: "available_copies", type: "integer", example: 7),
                    new OA\Property(property: "location", type: "string", example: "Section B, Shelf 2"),
                    new OA\Property(property: "status", type: "string", example: "active"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Book updated successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Book updated successfully"),
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
    public function update(BookRequest $request, string $id)
    {

        $book = Book::findOrFail($id);
        Gate::authorize('update', $book);

        if(!$book){
            return response()->json([
                'message'=> 'Book not found',
                'status'=> 404
            ], 404);
        }

        $book->update($request->validated());

        return response()->json([
            'message' => 'Book updated successfully',
            'book' => $book,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    #[OA\Delete(
        path: "/api/books/{id}",
        summary: "Delete a book",
        tags: ["Books"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Book id",
                required: true,
                in: "path",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Book deleted successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Book deleted successfully"),
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
        ]
    )]
    public function destroy(string $id)
    {
        if (!auth('api')->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $book = Book::findOrFail($id);
        Gate::authorize('delete', $book);

        if(!$book){
            return response()->json([
                'message'=> 'Book not found',
                'status'=> 404
            ], 404);
        }
        

        $book->delete();

        return response()->json([
            'message' => 'Book deleted successfully',
        ]);
    }
}
