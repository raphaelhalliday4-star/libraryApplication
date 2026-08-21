<?php

namespace App\Http\Controllers;

use App\Http\Requests\BorrowRequest;
use App\Http\Requests\ReturnBookRequest;
use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Member;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;

class BorrowingController extends Controller
{
    #[OA\Get(
        path: "/api/borrowings",
        summary: "Get all borrowed records",
        tags: ["Borrowing"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Borrowing records retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Borrowing records retrieved successfully"),
                        new OA\Property(property: "Borrowing", type: "object"),
                    ]
                )
            ),
        ]
    )]
public function index(){
    $borrow = Borrowing::with(['member','book'])->get();

     Gate::authorize('view', $borrow);

    return response()->json([
        'Message'=>'borrowing records retrieved successfully',
        'Borrowing'=>$borrow
    ]);

}

    #[OA\Post(
        path: "/api/borrow",
        summary: "Borrow a book for a member",
        tags: ["Borrowing"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["member_id", "book_id"],
                properties: [
                    new OA\Property(property: "member_id", type: "integer", example: 1),
                    new OA\Property(property: "book_id", type: "integer", example: 5),
                    new OA\Property(property: "remarks", type: "string", example: "For semester reading"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Book borrowed successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Book borrowed successfully"),
                        new OA\Property(property: "borrowing", type: "object"),
                    ]
                )
            ),
            new OA\Response(
                response: 200,
                description: "Borrow request rejected",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Member not active"),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validation error or no available copies",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "No copies available for borrowing."),
                        new OA\Property(property: "errors", type: "object"),
                    ]
                )
            ),
        ]
    )]
    public function borrow(BorrowRequest $request)
    {
         Gate::authorize('create', Borrowing::class);
         
        if (!auth('api')->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return DB::transaction(function () use ($request) {

            $member = Member::findOrFail($request->member_id);
            $book = Book::findOrFail($request->book_id);

            if($member->status !== "active"){
                return response()->json([
                    'message'=> 'Member not active'
                ]);
            }

            if ($book->available_copies < 1) {
                return response()->json([
                    'message' => 'No copies available for borrowing.',
                ], 422);
            }

            $activeBorrowing = Borrowing::where('member_id', $member->id)->whereIn('status',['borrowed','overdue'])->count();
             
            if($activeBorrowing > 5){
                return response()->json([
                    'Message'=> 'Member can not borrow more than 5 books'
                ]);
            }

            $borrowDate = Carbon::today()->format('Y-m-d');
            $dueDate = Carbon::today()->plus(days:14)->format('Y-m-d');;

            $borrowing = Borrowing::create([
                'member_id' => $member->id,
                'book_id' => $book->id,
                'borrowed_date' => $borrowDate,
                'due_date' => $dueDate,
                'status' => 'borrowed',
                'fine_amount' => 0,
                'remarks' => $request->remarks,
            ]);

            $book->decrement('available_copies');

            return response()->json([
                'message' => 'Book borrowed successfully',
                'borrowing' => $borrowing,
            ], 201);
        });
    }

    #[OA\Post(
        path: "/api/return",
        summary: "Return a borrowed book",
        tags: ["Borrowing"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["borrowing_id"],
                properties: [
                    new OA\Property(property: "borrowing_id", type: "integer", example: 1),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Book returned successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Book returned successfully"),
                        new OA\Property(property: "borrowing", type: "object"),
                    ]
                )
            ),
            new OA\Response(
                response: 409,
                description: "Book already returned",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Book has been returned"),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Borrowing record not found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "No borrowing record found"),
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
    public function returnBook(ReturnBookRequest $request){
        if (!auth('api')->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        Gate::authorize('update', $request);
        return DB::transaction(function () use ($request){
                
         $borrowing = Borrowing::findOrFail($request->borrowing_id);

         $book = $borrowing->book;

            if($borrowing->status === 'returned'){
                return response()->json([
                      'message'=> 'Book has already been been returned'
                ]);
            }

             $borrowing->returned_date = Carbon::today();
             $borrowing->status = 'returned';
             $book->increment('available_copies');

             $borrowing->save();

             return response()->json([
                'message'=>'Book has been returned'
             ]);

         });
              
    }
}

