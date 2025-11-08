<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AccountService;

class AccountController extends Controller
{
    protected $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    public function info()
    {
        return $this->accountService->info();
    }

    public function showChangePassword()
    {
        return $this->accountService->showChangePassword();
    }

    public function updatePassword(Request $request)
    {
        return $this->accountService->updatePassword($request);
    }

    public function confirmLogoutAfterChange()
    {
        return $this->accountService->confirmLogoutAfterChange();
    }

    public function deleteAccount(Request $request)
    {
        return $this->accountService->deleteAccount($request);
    }
}
