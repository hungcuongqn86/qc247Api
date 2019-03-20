<?php

namespace Modules\Common\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class PassportController extends Controller
{
    public $sucessStatus = 200;

    /**
     * @SWG\POST(
     *      path="/login",
     *      operationId="postLogin",
     *      tags={"Auth"},
     *      summary="login get token",
     *      description="Returns token",
     *      @SWG\Parameter(
     *         description="login",
     *         name="body",
     *         in="body",
     *         required=true,
     *         @SWG\Schema(
     *             type="object",
     *              @SWG\Property(property="email", type="string"),
     *              @SWG\Property(property="password", type="string"),
     *         )
     *      ),
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation"
     *       ),
     *       @SWG\Response(response=400, description="Bad request"),
     *       security={
     *           {"api_key_security_example": {}}
     *       }
     *     )
     *
     * Login
     */

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'remember_me' => 'boolean'
        ]);
        $credentials = request(['email', 'password']);
        // $credentials['active'] = 1;
        // $credentials['deleted_at'] = null;
        if(!Auth::attempt($credentials))
            return response()->json([
                'message' => __('auth.login_failed')
            ], 401);
        $user = $request->user();
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        if ($request->remember_me)
            $token->expires_at = Carbon::now()->addWeeks(1);
        $token->save();
        return response()->json([
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse($tokenResult->token->expires_at)->toDateTimeString()
        ]);
    }

    /**
     * @SWG\POST(
     *      path="/register",
     *      operationId="postRegister",
     *      tags={"Auth"},
     *      summary="Register user",
     *      description="Returns user",
     *      @SWG\Parameter(
     *         description="user",
     *         name="body",
     *         in="body",
     *         required=true,
     *         @SWG\Schema(
     *             type="object",
     *              @SWG\Property(property="name", type="string"),
     *              @SWG\Property(property="email", type="string"),
     *              @SWG\Property(property="password", type="string"),
     *              @SWG\Property(property="c_password", type="string"),
     *         )
     *      ),
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation"
     *       ),
     *       @SWG\Response(response=400, description="Bad request"),
     *       security={
     *           {"api_key_security_example": {}}
     *       }
     *     )
     *
     * Register
     */

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['token'] = $user->createToken('MyApp')->accessToken;
        $success['name'] = $user->name;

        return response()->json(['success' => $success], $this->sucessStatus);
    }

    /*
     * details api
     * 
     * @return \Illumiante\Http\Response
     */
    public function getDetails()
    {
        $user = Auth::user();
        return response()->json(['success' => $user], $this->sucessStatus);
    }

}
