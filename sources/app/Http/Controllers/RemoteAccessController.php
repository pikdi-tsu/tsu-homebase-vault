<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\ModuleAccessLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class RemoteAccessController extends Controller
{
    public function __invoke(Request $request, Module $module)
    {
        // Ambil data dari URL Parameter
        $targetId   = $request->query('target_id');
        $targetType = $request->query('target_type');

        if (!class_exists($targetType)) {
            return response()->view('errors.index', [
                'message' => 'Users Tidak Valid!',
                'code' => 404
            ], 404);
        }

        // Ambil Data non User
        $record = $targetType::query()->findOrFail($targetId);
        $userData = $record->toArray();
        $userData['roles'] = $record->getRoleNames()->toArray();

        // Encode Data
        try {
            $jsonPayload = json_encode($userData, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            Log::error($e->getMessage());
            return response()->view('errors.index', [
                'message' => 'Terjadi kesalahan! silahkan menghubungi PIKDI!',
                'code' => 500
            ], 500);
        }
        $base64Payload = base64_encode($jsonPayload);

        // LOGIC GENERATE TOKEN
        $secret = config('pikdi.key.emergency');
        $timestamp = now()->timestamp;
        $rawUrl = rtrim($module->url, '/');
        if (!preg_match("~^(?:f|ht)tps?://~i", $rawUrl)) {
            $rawUrl = "https://" . $rawUrl;
        }
        $baseUrl = $rawUrl;

        // Generate token
        $token = hash_hmac('sha256', $base64Payload . $timestamp, $secret);

        // CATAT LOG
        ModuleAccessLog::query()->create([
            'target_user_id'   => $record->id,
            'target_user_type' => $targetType,
            'module_id'        => $module->id,
            'admin_id'         => auth()->id(),
            'ip_address'       => $request->ip(),
            'login_method'     => 'REMOTE_IMPERSONATE',
            'accessed_at'      => now(),
        ]);

        // Build Query Params
        $queryParams = http_build_query([
            'payload'   => $base64Payload,
            'timestamp' => $timestamp,
            'signature' => $token,
        ]);

        $finalUrl = "{$baseUrl}/emergency-login?{$queryParams}";

        return Redirect::away($finalUrl);
    }
}
