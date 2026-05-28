<?php

namespace App\Http\Controllers;

use App\Http\Requests\ManagerVerifyRequest;
use App\Models\AuditLog;
use App\Services\ManagerOverrideService;

class ManagerOverrideController extends Controller
{
    public function __construct(private readonly ManagerOverrideService $override) {}

    /**
     * Verify manager credentials for a protected POS action.
     *
     * POST /api/manager/verify
     *
     * The cashier triggers this via an Axios modal. On success the backend
     * logs the override and returns the manager's name so the frontend can
     * display confirmation; it does NOT change the current session.
     */
    public function verify(ManagerVerifyRequest $request)
    {
        $manager = $this->override->verify(
            $request->username,
            $request->password
        );

        if (!$manager) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials or insufficient permissions.',
            ], 401);
        }

        // Record the authorised override in the audit log
        AuditLog::create([
            'order_id'                => $request->order_id,
            'action_type'             => $request->action,
            'cashier_id'              => auth()->id(),
            'authorizing_manager_id'  => $manager->id,
            'remarks'                 => "Manager override granted for action: {$request->action}",
            'meta'                    => [
                'manager_name' => $manager->name,
                'ip'           => $request->ip(),
            ],
        ]);

        return response()->json([
            'success'      => true,
            'manager_name' => $manager->name,
            'message'      => "Override granted by {$manager->name}.",
        ]);
    }
}
