<?php

namespace Modules\Common\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

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
        if (!Auth::attempt($credentials))
            return response()->json([
                'status' => false,
                'code' => 401,
                'message' => __('auth.login_failed')
            ], 401);
        $user = $request->user();
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        if ($request->remember_me)
            $token->expires_at = Carbon::now()->addWeeks(1);
        $token->save();
        return response()->json([
            'status' => true,
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
            'name' => 'required',
            'phone_number' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $input['type'] = 1;
        $user = User::create($input);
        $user->assignRole('custumer');
        $success['token'] = $user->createToken('MyApp')->accessToken;
        $success['name'] = $user->name;

        return response()->json($success, $this->sucessStatus);
    }

    /*
     * details api
     *
     * @return \Illumiante\Http\Response
     */
    public function getDetails()
    {
        $user = Auth::user();
        $rResult = User::with(['Partner', 'roles'])->where('id', '=', $user['id'])->first();
        return response()->json(['success' => $rResult], $this->sucessStatus);
    }

    public function getPermissions()
    {
        return response()->json(['success' => self::getNav()], $this->sucessStatus);
    }

    private function getNav()
    {
        $nav = [];
        $user = Auth::user();
        if($user->hasPermissionTo('dashboard')){
            $newobj = new \stdClass();
            $newobj->name = 'Bảng tổng hợp';
            $newobj->url = '/dashboard';
            $newobj->icon = 'icon-speedometer';
            $nav[] = $newobj;
        }

        if($user->hasPermissionTo('cart')){
            $newobj = new \stdClass();
            $newobj->name = 'Giỏ hàng';
            $newobj->url = '/cart';
            $newobj->icon = 'fa fa-cart-plus';
            $nav[] = $newobj;
        }

        if($user->hasPermissionTo('mcustumer')){
            $newobj = new \stdClass();
            $newobj->name = 'Khách hàng';
            $newobj->url = '/mcustumer/custumer';
            $newobj->icon = 'fa fa-user-plus';
            $nav[] = $newobj;
        }

        if($user->hasPermissionTo('order')){
            $newobj = new \stdClass();
            $children = [];
            $newobj->name = 'Đơn hàng';
            $newobj->url = '/order';
            $newobj->icon = 'fa fa-gavel';

            $newchildren = new \stdClass();
            $newchildren->name = 'Tất cả';
            $newchildren->url = '/order/all';
            $newchildren->icon = 'fa fa-folder';
            $children[] = $newchildren;

            /*if($user->hasPermissionTo('order1')){
                $newchildren = new \stdClass();
                $newchildren->name = 'order1';
                $newchildren->url = '/order/order1';
                $newchildren->icon = 'fa fa-folder';
                $children[] = $newchildren;
            }
            if($user->hasPermissionTo('order2')){
                $newchildren = new \stdClass();
                $newchildren->name = 'order2';
                $newchildren->url = '/order/order2';
                $newchildren->icon = 'fa fa-folder';
                $children[] = $newchildren;
            }*/
            $newobj->children = $children;
            $nav[] = $newobj;
        }
        /*if($user->hasPermissionTo('package')){
            $newobj = new \stdClass();
            $newobj->name = 'Kiện hàng';
            $newobj->url = '/package';
            $newobj->icon = 'fa fa-cubes';
            $nav[] = $newobj;
        }*/
        if($user->hasPermissionTo('wallet')){
            $newobj = new \stdClass();
            $newobj->name = 'Ví điện tử';
            $newobj->url = '/wallet';
            $newobj->icon = 'fa fa-money';
            $nav[] = $newobj;
        }
        if($user->hasPermissionTo('mpartner')){
            $newobj = new \stdClass();
            $newobj->name = 'Đối tác';
            $newobj->url = '/mpartner/partner';
            $newobj->icon = 'icon-puzzle';
            $nav[] = $newobj;
        }
        if($user->hasPermissionTo('muser')){
            $newobj = new \stdClass();
            $newobj->name = 'Người dùng';
            $newobj->url = '/muser/user';
            $newobj->icon = 'fa fa-users';
            $nav[] = $newobj;
        }
        /*if($user->hasPermissionTo('profile')){
            $newobj = new \stdClass();
            $newobj->name = 'Hồ sơ cá nhân';
            $newobj->url = '/profile';
            $newobj->icon = 'fa fa-user';
            $nav[] = $newobj;
        }*/
        if($user->hasPermissionTo('setting')){
            $newobj = new \stdClass();
            $newobj->name = 'Setting';
            $newobj->url = '/setting';
            $newobj->icon = 'fa fa-gear';
            $nav[] = $newobj;
        }
        return $nav;
    }

    public function setPermissions()
    {
        // echo 1;exit;
        // Permissions
        /*Permission::create(['name' => 'dashboard']);
        Permission::create(['name' => 'mcustumer']);
        Permission::create(['name' => 'cart']);
        Permission::create(['name' => 'order']);
        Permission::create(['name' => 'package']);
        Permission::create(['name' => 'wallet']);
        Permission::create(['name' => 'mpartner']);
        Permission::create(['name' => 'muser']);
        Permission::create(['name' => 'profile']);
        Permission::create(['name' => 'setting']);*/

        // Role
        // administrator
        $role = Role::findByName('administrator');
        $role->givePermissionTo('dashboard');
        $role->givePermissionTo('mpartner');
        $role->givePermissionTo('muser');
        $role->givePermissionTo('setting');

        // adminpk
        $role = Role::findByName('admin');
        $role->givePermissionTo('dashboard');
        $role->givePermissionTo('mcustumer');
        $role->givePermissionTo('order');
        $role->givePermissionTo('package');
        $role->givePermissionTo('muser');
        $role->givePermissionTo('setting');

        // employees
        $role = Role::findByName('employees');
        $role->givePermissionTo('dashboard');
        $role->givePermissionTo('order');
        $role->givePermissionTo('package');

        // owner
        $role = Role::findByName('custumer');
        $role->givePermissionTo('cart');
        $role->givePermissionTo('order');
        $role->givePermissionTo('package');
        $role->givePermissionTo('wallet');
        $role->givePermissionTo('profile');
        echo 1;
        exit;
    }
}
