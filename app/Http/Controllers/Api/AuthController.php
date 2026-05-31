<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\VerificationCodeMail;
use App\Models\User;
use App\Services\VerificationCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function __construct(private readonly VerificationCodeService $verificationCodeService)
    {
    }

    public function sendCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'purpose' => ['required', Rule::in(['register', 'reset_password'])],
        ]);

        $email = $validated['email'];
        $purpose = $validated['purpose'];

        if ($purpose === 'register' && User::query()->where('email', $email)->exists()) {
            return response()->json(['message' => '该邮箱已注册'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($purpose === 'reset_password' && ! User::query()->where('email', $email)->exists()) {
            return response()->json(['message' => '该邮箱尚未注册'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $code = $this->verificationCodeService->generate($email, $purpose);
        } catch (\RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], Response::HTTP_TOO_MANY_REQUESTS);
        }

        Mail::to($email)->queue(new VerificationCodeMail($code, $purpose));

        return response()->json(['data' => ['message' => '验证码已发送']]);
    }

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'code' => ['required', 'digits:6'],
            'password' => ['required', 'confirmed', 'min:8', 'max:100'],
            'name' => ['nullable', 'string', 'max:50'],
        ]);

        if (! $this->verificationCodeService->verify($validated['email'], 'register', $validated['code'])) {
            return response()->json(['message' => '验证码错误或已过期'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (User::query()->where('email', $validated['email'])->exists()) {
            return response()->json(['message' => '该邮箱已注册'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = User::query()->create([
            'name' => ($validated['name'] ?? null) ?: explode('@', $validated['email'])[0],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'email_verified_at' => now(),
        ]);

        $this->verificationCodeService->consume($validated['email'], 'register');

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'data' => [
                'token' => $token,
                'user' => $user,
            ],
        ], Response::HTTP_CREATED);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('email', $validated['email'])->first();
        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json(['message' => '邮箱或密码错误'], Response::HTTP_UNAUTHORIZED);
        }

        $token = $user->createToken('api')->plainTextToken;

        return response()->json(['data' => ['token' => $token, 'user' => $user]]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:6'],
            'password' => ['required', 'confirmed', 'min:8', 'max:100'],
        ]);

        if (! $this->verificationCodeService->verify($validated['email'], 'reset_password', $validated['code'])) {
            return response()->json(['message' => '验证码错误或已过期'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = User::query()->where('email', $validated['email'])->first();
        if (! $user) {
            return response()->json(['message' => '用户不存在'], Response::HTTP_NOT_FOUND);
        }

        $user->update(['password' => $validated['password']]);
        $this->verificationCodeService->consume($validated['email'], 'reset_password');

        return response()->json(['data' => ['message' => '密码重置成功']]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['data' => $request->user()]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['data' => ['message' => '已退出登录']]);
    }
}
