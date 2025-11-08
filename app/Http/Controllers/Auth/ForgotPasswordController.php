<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ForgotPasswordService;

class ForgotPasswordController extends Controller
{
    protected $forgotService;

    public function __construct(ForgotPasswordService $forgotService)
    {
        $this->forgotService = $forgotService;
    }

    public function showForgotForm()
    {
        return $this->forgotService->showForgotForm();
    }

    public function sendResetCode(Request $request)
    {
        return $this->forgotService->sendResetCode($request);
    }

    public function showVerifyForm()
    {
        return $this->forgotService->showVerifyForm();
    }

    public function verifyCode(Request $request)
    {
        return $this->forgotService->verifyCode($request);
    }

    public function showResetForm()
    {
        return $this->forgotService->showResetForm();
    }

    public function resetPassword(Request $request)
    {
        return $this->forgotService->resetPassword($request);
    }
}
