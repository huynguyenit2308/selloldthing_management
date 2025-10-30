<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ProfileService;

class ProfileController extends Controller
{
    protected $profileService;

    public function __construct(ProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    /**
     * Hiển thị thông tin cá nhân
     */
    public function show()
    {
        return $this->profileService->show();
    }

    /**
     * Hiển thị form chỉnh sửa
     */
    public function edit()
    {
        return $this->profileService->edit();
    }

    /**
     * Cập nhật thông tin cá nhân
     */
    public function update(Request $request)
    {
        return $this->profileService->update($request);
    }
}
