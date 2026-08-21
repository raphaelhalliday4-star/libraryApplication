<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReservationRequest;
use Illuminate\Http\Request;
use App\Models\Reservation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use PhpParser\Node\Stmt\TryCatch;
use OpenApi\Attributes as OA;

class ReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    #[OA\Get(
        path: "/api/reservations",
        summary: "Get all reservations",
        tags: ["reserve"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Reservations retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "Reservations", type: "array", items: new OA\Items()),
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthorized"),
        ]
    )]
    public function index()
    {
        Gate::authorize('viewAny', Reservation::class);
        $reserves = Reservation::latest()->get();

        return response()->json([
            'Reservations'=>$reserves
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    #[OA\Post(
        path: "/api/reservations",
        summary: "Create a new reservation",
        tags: ["reserve"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["member_id", "book_id"],
                properties: [
                    new OA\Property(property: "member_id", type: "integer", example: 1),
                    new OA\Property(property: "book_id", type: "integer", example: 1),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Reservation created successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Book reservation successful"),
                        new OA\Property(property: "reservation", type: "object"),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validation error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "The member_id field is required."),
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
    public function store(ReservationRequest $request)
    {
        Gate::authorize('create', Reservation::class);
         $reserve = Reservation::create([
            'member_id'=> $request->member_id,
            'book_id'=> $request->book_id,
            'reservation_date' => Carbon::today()->format('y-m-d') ?? null,
            'status' => 'pending',
         ]);

         return response()->json([
            'message'=> 'Book reservation successful',
            'reservation'=>$reserve,
         ]);
    }

    /**
     * Display the specified resource.
     */
    #[OA\Get(
        path: "/api/reservations/{id}",
        summary: "Get a specific reservation",
        tags: ["reserve"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Reservation ID",
                required: true,
                in: "path",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Reservation retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "reservation", type: "object"),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Reservation not found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Reservation not found"),
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
        $user = Auth::user();
        
        $reserve = Reservation::with(['member', 'book:id,title,publisher_id,category_id'])-> findOrFail($id);
        Gate::authorize('view', $reserve);

        if(!$reserve){
            return response()->json([
                'message'=>'Reservation not found',
            ]);
        }

        return response()->json([
            'reservation' => $reserve,
        ]);

    }


    public function update(ReservationRequest $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    #[OA\Delete(
        path: "/api/reservations/{id}",
        summary: "cancel a reservation",
        tags: ["reserve"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"), description: "Reservation ID"),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Reservation cancelled successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "reservation", type: "object"),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Reservation not found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Reservation not found"),
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
    public function destroy($id)
    {
        $reserve = Reservation::findOrFail($id);
        Gate::authorize('delete', $reserve);

        if($reserve->status == 'cancelled'){
            return response()->json([
                'message'=> 'Book reservation has already been cancelled',
                'reservation' => $reserve,
            ]);
        }
        $reserve->status = 'cancelled';
        $reserve->save();

        return response()->json([
            'Message'=> 'Reservation cancelled successfully',
            'reservation' => $reserve,
        ]);
    }
}
