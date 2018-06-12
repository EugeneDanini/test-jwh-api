<?php

namespace App\Http\Controllers;

use App\User;
use Firebase\JWT\ExpiredException;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Laravel\Lumen\Routing\Controller as BaseController;


class UserController extends BaseController
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function add(Request $request)
    {
        $validatorResult = $this->_validateCredentials($request, false);
        if ($validatorResult instanceof JsonResponse) {
            return $validatorResult;
        }

        $user = User::create($request->post('email'), $request->post('password'));

        return $this->_response(Response::HTTP_CREATED, $user->toArray());
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request)
    {

        $validatorResult = $this->_validateCredentials($request);
        if ($validatorResult instanceof JsonResponse) {
            return $validatorResult;
        }

        $user = User::getByEmail($request->post('email'));
        if (!$user || !$user->isValidPassword($request->post('password'))) {
            return $this->_response(Response::HTTP_BAD_REQUEST, ['errors' => ['Invalid credentials']]);
        }

        return $this->_response(Response::HTTP_OK, ['token' => $user->getJwtToken()]);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function get(Request $request, int $id)
    {
        $authUserResult = $this->_getUserByToken($request);
        if ($authUserResult instanceof JsonResponse) {
            return $authUserResult;
        }

        $user = User::getById((int) $id);
        if (!$user) {
            return $this->_response(Response::HTTP_NOT_FOUND, ['errors' => ['User not found']]);
        }

        return $this->_response(Response::HTTP_OK, $user->toArray());
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getList(Request $request)
    {
        $authUserResult = $this->_getUserByToken($request);
        if ($authUserResult instanceof JsonResponse) {
            return $authUserResult;
        }

        return $this->_response(Response::HTTP_OK, User::all());
    }

    /**
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id)
    {
        $authUserResult = $this->_getUserByToken($request);
        if ($authUserResult instanceof JsonResponse) {
            return $authUserResult;
        }

        $validatorResult = $this->_validateCredentials($request, false);
        if ($validatorResult instanceof JsonResponse) {
            return $validatorResult;
        }

        $user = User::getById((int) $id);
        if (!$user) {
            return $this->_response(Response::HTTP_NOT_FOUND, ['errors' => ['User not found']]);
        }

        $user->updateCredentials($request->post('email'), $request->post('password'));

        return $this->_response(Response::HTTP_ACCEPTED, $user->toArray());
    }

    public function delete(Request $request, int $id)
    {
        $authUserResult = $this->_getUserByToken($request);
        if ($authUserResult instanceof JsonResponse) {
            return $authUserResult;
        }

        $user = User::getById((int) $id);
        if (!$user) {
            return $this->_response(Response::HTTP_NOT_FOUND, ['errors' => ['User not found']]);
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        $user->delete();

        return $this->_response(Response::HTTP_ACCEPTED);
    }

    /**
     * @param int $code
     * @param mixed $data
     * @return JsonResponse
     */
    private function _response(int $code, $data = [])
    {
        $isSuccess = false;
        if (in_array($code, [Response::HTTP_OK, Response::HTTP_CREATED, Response::HTTP_ACCEPTED])) {
            $isSuccess = true;
        }
        $data = ['data' => $data, 'is_success' => $isSuccess];

        return response()->json($data, $code);
    }

    /**
     * @param Request $request
     * @param bool $isLogin
     * @return bool|JsonResponse
     */
    private function _validateCredentials(Request $request, $isLogin = true)
    {
        $validator = Validator::make($request->all(), [
            'email' => ($isLogin) ? 'required|email' : 'required|email|unique:users',
            'password' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->_response(Response::HTTP_BAD_REQUEST, ['errors' => $validator->errors()->all()]);
        }

        return true;
    }

    /**
     * @param Request $request
     * @return User|JsonResponse
     */
    private function _getUserByToken(Request $request)
    {
        $token = $request->header('Authorization', '');
        if (!$token) {
            return $this->_response(Response::HTTP_UNAUTHORIZED, ['errors' => ['This method requires authorization token']]);
        }
        try {
            $user = User::getByJwtToken($token);
        } catch (ExpiredException $e) {
            return $this->_response(Response::HTTP_UNAUTHORIZED, ['errors' => ['Token expired']]);
        } catch (Exception $e) {
            return $this->_response(Response::HTTP_UNAUTHORIZED, ['errors' => ['Invalid token']]);
        }
        if (!$user) {
            return $this->_response(Response::HTTP_NOT_FOUND, ['errors' => ['Token user not found']]);
        }

        return $user;
    }
}