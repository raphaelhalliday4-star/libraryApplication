<?php

namespace App\Http\Controllers;

use App\Http\Requests\MemberRequest;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\member;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;


class MemberController extends Controller
{
        #[OA\Get(
        path: "/api/get-member",
        summary: "Get all members",
        tags: ["member"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "members retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "members", type: "array", items: new OA\Items(type: "object")),
                    ]
                )
            ),
        ]
    )]
    public function index(){
        Gate::authorize('view', Member::class);
        $member = Member::with('user')->get();

        return response()->json([
              'member'=>$member,
        ]);
    }
    

    #[OA\Put(
        path: "/api/member/{id}",
        summary: "Update an existing member",
        tags: ["member"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Member id",
                required: true,
                in: "path",
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "phone", type: "string", example: "081625904234"),
                    new OA\Property(property: "address", type: "string", example: "123 Main St"),
                    new OA\Property(property: "gender", type: "string", example: "male", description: "one of: male, female, other"),
                    new OA\Property(property: "membership_number", type: "string", example: "LIB-ABCD1234"),
                    new OA\Property(property: "photo", type: "string", example: "https://example.com/photo.jpg"),
                    new OA\Property(property: "status", type: "string", example: "active", description: "active"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Member updated successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Member updated successfully"),
                        new OA\Property(property: "member", type: "object"),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Member not found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Member not found"),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validation error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "The membership_number field has already been taken."),
                        new OA\Property(property: "errors", type: "object"),
                    ]
                )
            ),
        ]
    )]
    public function update(MemberRequest $request, $id){
        if (!auth('api')->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $member = Member::findOrFail($id);

        Gate::authorize('update', $member);
 
        $member->update($request->validated());

        return response()->json([
            'message' => 'Member updated successfully',
            'member' => $member,
    ]);

    }
}
