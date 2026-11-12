@extends('layouts.app')

@section('title', 'Audit Log Aktivitas')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Audit Trail & Log Aktivitas</h1>
            <p class="text-slate-500 text-sm mt-0.5">Rekam jejak seluruh transaksi, perubahan data stok, pengguna, dan otorisasi dalam sistem.</p>
        </div>
    </div>

    <!-- Search & Filter Form -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('audit-logs.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Pencarian Aktivitas</label>
                <div class="relative">
                    <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama pengguna, aksi, atau detail deskripsi..."
                           class="w-full pl-10 pr-4 py-2 text-sm border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Filter Modul</label>
                <select name="module" class="w-full text-sm border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">Semua Modul</option>
                    @foreach($modules as $m)
                    <option value="{{ $m }}" {{ $module === $m ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-semibold transition">
                    Cari & Filter
                </button>
                <a href="{{ route('audit-logs.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-semibold transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Audit Log Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-900">Riwayat Catatan Log Sistem</h2>
            <span class="text-xs text-slate-500">Total {{ $logs->total() }} aktivitas tercatat</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-200/75 text-slate-600 text-xs uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">Waktu Kejadian</th>
                        <th class="py-3 px-4">Pengguna</th>
                        <th class="py-3 px-4">Modul</th>
                        <th class="py-3 px-4">Aksi</th>
                        <th class="py-3 px-4">Deskripsi Rincian</th>
                        <th class="py-3 px-4">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="py-3.5 px-4 font-mono text-xs text-slate-500 whitespace-nowrap">
                            {{ $log->created_at->format('d/m/Y H:i:s') }}
                        </td>
                        <td class="py-3.5 px-4 font-semibold text-slate-900 whitespace-nowrap">
                            {{ $log->user_name ?? ($log->user?->name ?? 'System') }}
                        </td>
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-800 border border-slate-200">
                                {{ $log->module }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 font-mono text-xs text-indigo-700 font-semibold whitespace-nowrap">
                            {{ $log->action }}
                        </td>
                        <td class="py-3.5 px-4 text-slate-700 text-xs max-w-md">
                            {{ $log->description }}
                        </td>
                        <td class="py-3.5 px-4 font-mono text-xs text-slate-500 whitespace-nowrap">
                            {{ $log->ip_address ?? '127.0.0.1' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-12 text-slate-400">
                            <svg class="w-12 h-12 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Belum ada riwayat aktivitas yang sesuai dengan filter pencarian.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
