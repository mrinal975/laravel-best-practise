<?php

namespace App\Http\Controllers\Base;

use App\Models\Employee\Employee;
use App\Models\Employee\EmployeeInfo;
use App\Models\Leave\AllocatedLeaveTypes;
use App\Models\Setup\Group;
use Illuminate\Http\Request;
use App\Http\Resources\User as UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use App\Engine\HttpStatus;
use App\Http\Resources\Auth\LoginResource as AuthLoginResource;
use Exception;
use Laravel\Passport\Http\Controllers\AccessTokenController;
use App\Models\ClientInfo\ClientAuth;
use App\Http\Resources\Auth\CustomerResource;
use Auth;

class AuthController extends AccessTokenController
{
    /**
     * @OA\Post(
     ** path="/api/login",
     *   tags={"Login"},
     *   summary="Login",
     *   operationId="login",
     *
     *   @OA\Parameter(name="email", in="query", required=true,
     *      @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter( name="password", in="query", required=true,
     *      @OA\Schema(type="string")
     *   ),
     *   @OA\Response(response=200, description="Success",
     *      @OA\MediaType(mediaType="application/json")
     *      ),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=400, description="Bad Request"),
     *   @OA\Response(response=404, description="not found"),
     *   @OA\Response(response=403, description="Forbidden")
     *)
     **/
    /**
     * login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()]);
        }

        $credentials = $request->only('email', 'password');

        // Other all user login
        if (auth()->attempt($credentials)) {
            $token = auth()->user()->createToken('water_billing', ['adminer'])->accessToken;
            $user = Auth::user();
            $user->access_token = $token;
            $user = new AuthLoginResource($user);
            $user['status'] = HttpStatus::OK;
            return response()->json(['data' => $user, HttpStatus::STATUS => HttpStatus::OK], HttpStatus::OK);
        } else {
            return response()->json(['error' => 'UnAuthorised', HttpStatus::STATUS => HttpStatus::UNAUTHORIZED], HttpStatus::OK);
        }
    }


    public function customerLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()]);
        }
        $client = ClientAuth::where('client_id', $request->client_id)
                ->select('id', 'name', 'father_name', 'nid', 'mother_name', 'street_name')
                ->first();
        if (!$client) {
            return response()->json(['errors' => 'Client credential not match'], HttpStatus::UNAUTHORIZED);
        }
        $token = $client->createToken('client', ['client'])->accessToken;
        $client->is_client = 1;
        $client->access_token = $token;

        return response()->json(['data' => $client, HttpStatus::STATUS => HttpStatus::OK], HttpStatus::OK);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{6,}$/',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()]);
        }

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->save();

        return response()->json(['success' => 'User created successfully']);
    }

    public function userUpdate(Request $request, $id)
    {
        if ($request->field) {
            $field = $request->field;
            $user = User::where($field, $id)->first();
        } else {
            if ($id > 0) {
                $user = User::find($id);
            }
        }

        $name_validaation = '';
        $email_validation = '';
        if (isset($user)) {
            if (!$user) {
                return response()->json(['errors' => 'User not found']);
            } else {
                if ($request->name != $user->name) {
                    $name_validaation .= '|unique:users';
                }
                if ($request->email != $user->email) {
                    $email_validation .= '|unique:users';
                }
            }
            $validator = Validator::make($request->all(), [
                'name' => 'required' . $name_validaation,
                'email' => 'required|email' . $email_validation,
                'password' => $request->password ? 'required|string|min:8|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{6,}$/' : '',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->messages()]);
            }

            $user->name = $request->name;
            $user->email = $request->email;
            if (isset($request->group_id)) {
                $user->group_id = $request->group_id;
            }
            if (isset($request->role_id)) {
                $user->role_id = $request->role_id;
            }
            if ($request->password) {
                $user->password = bcrypt($request->password);
            }
            $user->save();
        } else {
            $this->userRegistation($request);
        }

        # allocate leave balance if not exist...
        try {
            $leave_allocate = AllocatedLeaveTypes::EmployeeLeaveBalanceInitialize($user->staff_id, '');
        } catch (Exception $x) {
            //
        }

        return response()->json(['success' => 'User updated successfully']);
    }

    public function userEdit(Request $request, $id)
    {
        if ($request->field) {
            $field = $request->field;
            $result = User::where($field, $id)->first();
        } else {
            $result = User::find($id);
        }

        if ($result) {
            return response()->json(['data' => $result, HttpStatus::STATUS => HttpStatus::OK], 200);
        } else {
            return response()->json(['error' => 'User not found!', HttpStatus::STATUS => HttpStatus::NOT_FOUND], 200);
        }
    }

    /**
     * @OA\Post(
     ** path="/api/user-registration",
     *   tags={"Register"},
     * security={
     *  {"passport": {}},
     *   },
     *   summary="Register",
     *   operationId="register",
     *
     *  @OA\Parameter(
     *      name="name",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *  @OA\Parameter(
     *      name="email",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *   @OA\Parameter(
     *       name="staff_id",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="integer"
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="password",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *      @OA\Parameter(
     *      name="group_id",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="integer"
     *      )
     *   ),
     *   @OA\Response(response=200, description="Success",
     *      @OA\MediaType(mediaType="application/json")
     *      ),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=400, description="Bad Request"),
     *   @OA\Response(response=404, description="not found"),
     *   @OA\Response(response=403, description="Forbidden")
     *)
     **/
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function userRegistation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{6,}$/',
            'staff_id' => 'required',
            'group_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages(), HttpStatus::STATUS => HttpStatus::UNPROCESSABLE_ENTITY]);
        }

        $check_info = EmployeeInfo::where('staff_id', $request->staff_id)->first();
        if (!isset($check_info->joining_date) || !$check_info->joining_date) {
            return response()->json(['message' => "Joining date not found. User can't be created.", HttpStatus::STATUS => HttpStatus::NOT_FOUND], 200);
        }

        $user = User::where('staff_id', '=', $request->staff_id)->first();
        if (empty($user)) {
            $user = new User();
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->staff_id = $request->staff_id;
        $user->group_id = $request->group_id;
        $user->password = bcrypt($request->password);
        $user->save();

        $employee = Employee::find($request->staff_id);
        $group = Group::find($request->group_id);

        //Leave balance initialize when new employee register
        AllocatedLeaveTypes::EmployeeLeaveBalanceInitialize($request->staff_id, "");

        $data = array(
            "employee_name" => $employee->employee_name,
            'user_name' => $user->name,
            'group_name' => $group->name,
            'message' => 'User Created Successfully'
        );

        $userData = array('data' => $data);

        return $userData;
    }

    /**
     * @return UserResource
     */
    public function me()
    {
        return new UserResource(Auth::user());
    }

    /**
     * Get the bearer token from the request headers.
     *
     * @return string|null
     */
    public function bearerToken()
    {
        $header = $this->header('Authorization', '');
        // if (Str::startsWith($header, 'Bearer ')) {
        //     return Str::substr($header, 7);
        // }
    }

    public static function userActivitySet($time = 0)
    {
        User::where('id', Auth::user()->id)->update(['last_activity_time'=> $time]);
    }

    /**
     * Log the user out (Invalidate the token)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        if (!Auth::user()) {
            return response(['data' => 'Unauthenticated', HttpStatus::STATUS => HttpStatus::OK], HttpStatus::UNPROCESSABLE_ENTITY);
        }
        $token = $request->user()->token();
        $token->revoke();
        return response(['data' => 'Log out successfully done!', HttpStatus::STATUS => HttpStatus::ACCEPTED]);
    }


    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->factory()->getTTL() * 60
        ]);
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */
    public function guard()
    {
        return Auth::guard();
    }
}
