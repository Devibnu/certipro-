<?php

namespace App\Http\Controllers\AdminUI;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Display audit logs with filtering.
     */
    public function index(Request $request)
    {
        $query = AuditLog::with('user')->orderBy('created_at', 'desc');
        
        // Filter by module
        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }
        
        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        
        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        
        // Filter by event type
        if ($request->filled('event')) {
            $query->whereJsonContains('metadata->event', $request->event);
        }
        
        // Search by reference number or description
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('user_name', 'like', "%{$search}%");
            });
        }
        
        $logs = $query->paginate(25);
        
        // Get filter options
        $modules = [
            AuditLog::MODULE_AUTH => 'Autentikasi',
            AuditLog::MODULE_PRA_PENDAFTARAN => 'Pra-Pendaftaran',
            AuditLog::MODULE_PENDAFTARAN => 'Pendaftaran',
            AuditLog::MODULE_ASESMEN => 'Asesmen',
            AuditLog::MODULE_KEPUTUSAN => 'Keputusan',
            AuditLog::MODULE_SERTIFIKAT => 'Sertifikat',
            AuditLog::MODULE_USER => 'Pengguna',
            AuditLog::MODULE_SKEMA => 'Skema',
        ];
        
        $actions = [
            AuditLog::ACTION_CREATE => 'Membuat',
            AuditLog::ACTION_UPDATE => 'Mengubah',
            AuditLog::ACTION_DELETE => 'Menghapus',
            AuditLog::ACTION_VIEW => 'Melihat',
            AuditLog::ACTION_APPROVE => 'Menyetujui',
            AuditLog::ACTION_REJECT => 'Menolak',
            AuditLog::ACTION_VERIFY => 'Memverifikasi',
            AuditLog::ACTION_ASSESS => 'Mengasesmen',
            AuditLog::ACTION_DECIDE => 'Keputusan',
            AuditLog::ACTION_ISSUE => 'Menerbitkan',
            AuditLog::ACTION_REVOKE => 'Mencabut',
            AuditLog::ACTION_LOGIN => 'Login',
            AuditLog::ACTION_LOGOUT => 'Logout',
        ];
        
        $users = User::with('userRole')
            ->whereHas('userRole', function($q) {
                $q->whereIn('name', ['super_admin', 'admin', 'asesor', 'komite_teknis']);
            })
            ->orderBy('name')
            ->get();
        
        // Get event types for filtering
        $events = AuditLog::getEventTypes();
        
        // Summary statistics for audit dashboard
        $stats = [
            'total' => AuditLog::count(),
            'today' => AuditLog::whereDate('created_at', today())->count(),
            'active_users' => AuditLog::whereDate('created_at', today())
                ->distinct('user_id')
                ->count('user_id'),
            'critical_events' => AuditLog::whereIn('action', [
                AuditLog::ACTION_DELETE,
                AuditLog::ACTION_REVOKE,
                AuditLog::ACTION_REJECT,
            ])->whereDate('created_at', '>=', now()->subDays(7))->count(),
        ];
        
        return view('adminui.audit-log.index', compact('logs', 'modules', 'actions', 'users', 'events', 'stats'));
    }

    /**
     * Show detail of a single audit log.
     */
    public function show($id)
    {
        $log = AuditLog::with('user')->findOrFail($id);
        
        return view('adminui.audit-log.show', compact('log'));
    }

    /**
     * Export audit logs to CSV.
     */
    public function export(Request $request)
    {
        $query = AuditLog::with('user')->orderBy('created_at', 'desc');
        
        // Apply same filters as index
        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        
        $logs = $query->get();
        
        $filename = 'audit_log_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, [
                'ID',
                'Waktu',
                'User',
                'Role',
                'Aksi',
                'Modul',
                'Deskripsi',
                'Reference',
                'IP Address',
                'URL',
            ]);
            
            // Data rows
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->user_name,
                    $log->user_role,
                    $log->action,
                    $log->module,
                    $log->description,
                    $log->reference_number,
                    $log->ip_address,
                    $log->url,
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get audit summary statistics.
     */
    public function statistics(Request $request)
    {
        $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        
        $stats = [
            'total' => AuditLog::dateRange($startDate, $endDate)->count(),
            'by_module' => AuditLog::dateRange($startDate, $endDate)
                ->selectRaw('module, count(*) as count')
                ->groupBy('module')
                ->pluck('count', 'module'),
            'by_action' => AuditLog::dateRange($startDate, $endDate)
                ->selectRaw('action, count(*) as count')
                ->groupBy('action')
                ->pluck('count', 'action'),
            'by_user' => AuditLog::dateRange($startDate, $endDate)
                ->selectRaw('user_name, count(*) as count')
                ->groupBy('user_name')
                ->orderByDesc('count')
                ->limit(10)
                ->pluck('count', 'user_name'),
        ];
        
        return response()->json($stats);
    }
}
