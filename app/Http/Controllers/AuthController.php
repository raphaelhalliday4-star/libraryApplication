<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use App\Http\Requests\FileRequest;
use App\Models\User;
use App\Models\Member;
use App\Models\userProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tymon\JWTAuth\Facades\JWTAuth;
use PhpParser\Node\Stmt\TryCatch;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
        #[OA\Post(
        path: "/api/register",
        summary: "Register a new user",
        tags: ["libry"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "email", "password"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Jane Doe"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "jane@example.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "secret123"),

                ]
            )
        ),
 responses: [
            new OA\Response(
                response: 201,
                description: "Member retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Member retrieved successfully"),
                        new OA\Property(property: "member", type: "object"),
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
   public function register(Request $request)
   {
        $request->validate([
           'name' => 'required|string|max:255',
           'email' => 'required|string|email|max:255|unique:users',
           'password' => 'required',
       ]);

try{
       $user = User::create([
           'name' => $request->name,
           'email' => $request->email,
           'password' => bcrypt($request->password),
       ]);

     $role = Role::firstOrCreate([
         'name' => 'member',
         'guard_name' => 'api',
     ]);

     $user->assignRole('member');

         $member = Member::create([
                'user_id' => $user->id,
                'phone' => $request->phone,
                'address' => $request->address,
                'gender' => $request->gender,
                'membership_number' => 'LIB-' . strtoupper(Str::random(8)),
                'photo' => $request->photo,
                'status' => $request->status ?? 'active',
            ]);
   }catch(\Exception $e){
        return response()->json(['error' => 'Registration failed', 'message' => $e->getMessage()], 500);
   }

       return response()->json([
        'message' => 'User registered successfully',
        'user' => $user,
        ], 201);

   }


   #[OA\Post(
        path: "/api/login",
        summary: "Login a user",
        tags: ["libry"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "jane@example.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "secret123"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Login successful"),
            new OA\Response(response: 401, description: "Unauthorized"),
        ]
    )]
   public function login(Request $request){
     $credentials = $request->only('email', 'password');

     if (!$token = JWTAuth::attempt($credentials)) {
         return response()->json(['error' => 'Unauthorized'], 401);
     }

        return $this->respondWithToken($token);
   }

          protected function respondWithToken($token)
        {
            return response()->json([
                'access_token' => $token,
                'token_type' => 'bearer',
            ]);
        }

    public function logout()
        {
            Auth::logout();
            return response()->json(['message' => 'Successfully logged out']);
        }

    #[OA\Get(
        path: "/api/user",
        summary: "Get the authenticated user",
        tags: ["libry"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 201,
                description: "user retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "user retrieved successfully"),
                        new OA\Property(property: "borrowing", type: "object"),
                    ]
                )
            ),
            new OA\Response(
                response: 200,
                description: "user request rejected",
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
    public function getUser()
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user->loadMissing('member');

        return response()->json([
            'user' => $user
        ]);
        }

   
        #[OA\Put(
            path: "/api/user",
            summary: "Update the authenticated user",
            tags: ["libry"],
            security: [["bearerAuth" => []]],
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "name", type: "string", example: "Jane Doe"),
                        new OA\Property(property: "email", type: "string", format: "email", example: "jane@example.com"),
                        new OA\Property(property: "password", type: "string", format: "password", example: "secret123"),
                    ]
                )
            ),
            responses: [
                new OA\Response(response: 200, description: "User updated successfully"),
                new OA\Response(response: 401, description: "Unauthorized"),
                new OA\Response(response: 422, description: "Validation error"),
            ]
        )]
        public function updateUser(Request $request){
            $user = Auth::user();

            if(!$user){
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $request->validate([
                'name' => 'string|max:255',
                'email' => 'string|email|max:255|unique:users,email,'.$user->id,
                'password' => 'string',
            ]);

            if($request->has('name')){
                $user->name = $request->name;
            }

            if($request->has('email')){
                $user->email = $request->email;
            }

            if($request->has('password')){
                $user->password = bcrypt($request->password);
            }

            $user->save();

            return response()->json([
                'message' => 'User updated successfully',
                'user' => $user
            ]);
        }


        #[OA\Delete(
            path: "/api/user",
            summary: "Delete the authenticated user",
            tags: ["libry"],
            security: [["bearerAuth" => []]],
            responses: [
                new OA\Response(response: 200, description: "User deleted successfully"),
                new OA\Response(response: 401, description: "Unauthorized"),
            ]
        )]
        public function deleteUser(){
            $user = Auth::user();

            if(!$user){
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $user->delete();

            return response()->json([
                'message' => 'User deleted successfully'
            ]);
        }
        // i am making my frist push
     
        #[OA\Post(
    path: "/api/user/profile-image",
    summary: "Upload user profile image",
    tags: ["Libry"],
    security: [["bearerAuth" => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: "multipart/form-data",
            schema: new OA\Schema(
                type: "object",
                required: ["file"],
                properties: [
                    new OA\Property(
                        property: "file",
                        type: "string",
                        format: "binary"
                    )
                ]
            )
        )
    ),
 responses: [
            new OA\Response(
                response: 201,
                description: "Author created successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "file uploaded successfully"),
                        new OA\Property(property: "author", type: "object"),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validation error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "The file  is required."),
                        new OA\Property(property: "errors", type: "object"),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Unauthorized",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "error", example: "Unauthorized"),
                    ]
                )
            ),
        ]
    )]      
      public function handleFileImage(FileRequest $request){
            
        $user=Auth::user();
        $file=$request->file('file');
        $filename=Str::random(20);
        $filename = $filename . '-' . time() . '.' . $file->getClientOriginalExtension();
        $path=$file->storeAs('images',$filename,'public');

        $profileImage = userProfile::create([
        'user_id' => $user->id,
        'path' => $path,
    ]);
          
        return response()->json([
            'message' => 'Profile image uploaded successfully',
            'profile' => $profileImage,
        ]);
        
        }
}


