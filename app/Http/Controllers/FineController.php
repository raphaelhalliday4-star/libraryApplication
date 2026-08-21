<?php

namespace App\Http\Controllers;

use App\Http\Requests\FineRequest;
use App\Models\Fine;
use Illuminate\Http\Request;
use App\Models\Borrowing;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;

class FineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    #[OA\Get(
        path: "/api/fines",
        summary: "Get all fines",
        tags: ["Fines"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Fines retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "fines", type: "array", items: new OA\Items(type: "object")),
                    ]
                )
            ),
        ]
    )]
    public function index()
    {
        Gate::authorize('viewAny', Fine::class);
        $fine = Fine::with('borrow')->get();

        return response()->json([
            'Fine'=> $fine
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    #[OA\Post(
        path: "/api/fines",
        summary: "Create a new fine",
        tags: ["Fines"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["borrowing_id", "amount"],
                properties: [
                    new OA\Property(property: "borrowing_id", type: "integer", example: 1),
                    new OA\Property(property: "amount", type: "number", format: "float", example: 50000),
                    new OA\Property(property: "remarks", type: "string", example: "Late return fine"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Fine created successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "payment successful"),
                        new OA\Property(property: "fine", type: "object"),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Borrowing record not found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "borrwing record not found"),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validation error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "The borrowing_id field is required."),
                        new OA\Property(property: "errors", type: "object"),
                    ]
                )
            ),
        ]
    )]
    public function store(FineRequest $request)
    {
        if (!auth('api')->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $borrow = Borrowing::findOrFail($request->borrowing_id);
        Gate::authorize('create', Fine::class);
        
        $data = $request->validated();
     
        if(!$borrow){
            return response()->json([
                'message'=>'borrwing record not found'
            ]);
        }

        $fine = Fine::create([
        'borrowing_id' => $data['borrowing_id'],
        'amount' => $data['amount'],
        'paid'=> false,
        'payment_date'=> null,
        'remarks' =>$data['remarks'] ?? null,
        ]);

        return response()->json([
            'message' => 'fine created successful',
            'fine' => $fine,
        ]);

    }

    /**
     * Display the specified resource.
     */
    #[OA\Get(
        path: "/api/fines/{id}",
        summary: "Get fine details",
        tags: ["Fines"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Fine id",
                required: true,
                in: "path",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Fine retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "fine", type: "object"),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Fine not found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "record not found"),
                    ]
                )
            ),
        ]
    )]
    public function show($id)
    { 
        $user = Auth::user();
        $fine = Fine::with('borrow')->findOrFail($id);
        Gate::authorize('view', $fine);

        if(!$fine){
            return response()->json([
                'message'=> 'record not found'
            ]);
        }

        return response()->json([
            'fine'=>$fine,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    #[OA\Put(
        path: "/api/fines/{id}",
        summary: "Update an existing fine",
        tags: ["Fines"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Fine id",
                required: true,
                in: "path",
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "amount", type: "number", format: "float", example: 50000),
                    new OA\Property(property: "paid", type: "boolean", example: false),
                    new OA\Property(property: "payment_date", type: "string", format: "date-time", nullable: true, example: "2026-08-18T12:00:00Z"),
                    new OA\Property(property: "remarks", type: "string", example: "Updated remarks"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Fine updated successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Fine updated successfully"),
                        new OA\Property(property: "fine", type: "object"),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Fine not found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Fine not found"),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validation error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "The amount field is required."),
                        new OA\Property(property: "errors", type: "object"),
                    ]
                )
            ),
        ]
    )]
    public function update(Request $request, string $id)
    {
        if (!auth('api')->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $fine = Fine::findOrFail($id);

         Gate::authorize('update', $fine);

        $request->validate([
            'amount' => 'numeric',
            'paid' => 'boolean',
            'payment_date' => 'nullable|date',
            'remarks' => 'nullable|string',
        ]);

        if ($request->has('amount')) {
            $fine->amount = $request->amount;
        }

        if ($request->has('paid')) {
            $fine->paid = $request->paid;
        }

        if ($request->has('payment_date')) {
            $fine->payment_date = $request->payment_date;
        }

        if ($request->has('remarks')) {
            $fine->remarks = $request->remarks;
        }

        $fine->update();

        return response()->json([
            'message' => 'Fine updated successfully',
            'fine' => $fine,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    #[OA\Delete(
        path: "/api/fines/{id}",
        summary: "Delete a fine",
        tags: ["Fines"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Fine id",
                required: true,
                in: "path",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Fine deleted successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Fine deleted successfully"),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Fine not found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Fine not found"),
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

        $fine = Fine::findOrFail($id);
         Gate::authorize('delete', $fine);
        $fine->delete();

        return response()->json([
            'message' => 'Fine deleted successfully',
        ]);
    }
}
