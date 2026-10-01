@php
    $record = $getRecord();
    $logs = \App\Models\AuditLog::where('model_name', get_class($record))
        ->where('object_id', $record->id)
        ->with('user')
        ->latest() // Standard timeline sorted latest first so users see recent actions immediately
        ->get();
@endphp

<div class="relative py-4">
    @if ($logs->isEmpty())
        <div class="flex flex-col items-center justify-center p-8 bg-gray-50 dark:bg-gray-950/30 border border-dashed border-gray-200 dark:border-gray-800 rounded-xl">
            <svg class="w-10 h-10 text-gray-300 dark:text-gray-700 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">No activity logs recorded for this record yet.</p>
        </div>
    @else
        <!-- Vertical connecting line -->
        <div class="absolute top-6 bottom-6 w-0.5 bg-gray-200 dark:bg-gray-800" style="left: 1.625rem;"></div>

        <div class="space-y-6">
            @foreach ($logs as $log)
                @php
                    // Enforce custom icon, color palette, and descriptive text per action type
                    $action = $log->action;
                    $status = $log->details['status'] ?? '';
                    
                    $iconBgClass = 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400';
                    $actionTitle = 'Activity logged';
                    $iconSvg = '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><circle cx="12" cy="12" r="10" /></svg>';

                    if ($action === 'create') {
                        $iconBgClass = 'bg-sky-100 text-sky-600 dark:bg-sky-950/40 dark:text-sky-400';
                        $actionTitle = 'Drafted record';
                        $iconSvg = '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>';
                    } elseif ($action === 'update') {
                        $iconBgClass = 'bg-amber-100 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400';
                        $actionTitle = 'Modified record';
                        $iconSvg = '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.83 20.04a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>';
                    } elseif ($action === 'submit') {
                        $iconBgClass = 'bg-indigo-100 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400';
                        $actionTitle = 'Submitted for Review';
                        $iconSvg = '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>';
                    } elseif ($action === 'validate') {
                        if ($status === 'Reviewed') {
                            $iconBgClass = 'bg-teal-100 text-teal-600 dark:bg-teal-950/40 dark:text-teal-400';
                            $actionTitle = 'Reviewed and Recommended';
                            $iconSvg = '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" /></svg>';
                        } elseif ($status === 'Noted') {
                            $iconBgClass = 'bg-emerald-100 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400';
                            $actionTitle = 'Officially Noted';
                            $iconSvg = '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>';
                        } else {
                            $iconBgClass = 'bg-emerald-100 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400';
                            $actionTitle = 'Validated Record';
                            $iconSvg = '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>';
                        }
                    } elseif ($action === 'revision') {
                        $iconBgClass = 'bg-rose-100 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400';
                        $actionTitle = 'Returned to Draft for Revision';
                        $iconSvg = '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" /></svg>';
                    }

                    // Enforce sleek corporate colors for Spatie roles
                    $roleName = $log->user?->role ?? '';
                    $roleBadgeClass = 'bg-gray-100 text-gray-700 border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700';

                    if ($roleName === 'superadmin') {
                        $roleBadgeClass = 'bg-red-50 text-red-700 border border-red-200/50 dark:bg-red-950/20 dark:text-red-400 dark:border-red-800/30';
                    } elseif ($roleName === 'admin') {
                        $roleBadgeClass = 'bg-purple-50 text-purple-700 border border-purple-200/50 dark:bg-purple-950/20 dark:text-purple-400 dark:border-purple-800/30';
                    } elseif ($roleName === 'manager') {
                        $roleBadgeClass = 'bg-blue-50 text-blue-700 border border-blue-200/50 dark:bg-blue-950/20 dark:text-blue-400 dark:border-blue-800/30';
                    } elseif ($roleName === 'supervisor') {
                        $roleBadgeClass = 'bg-emerald-50 text-emerald-700 border border-emerald-200/50 dark:bg-emerald-950/20 dark:text-emerald-400 dark:border-emerald-800/30';
                    }
                @endphp

                <div class="relative flex items-start group" style="padding-left: 3.5rem;">
                    <!-- Circular Icon Center Node -->
                    <div class="absolute w-7 h-7 rounded-full flex items-center justify-center border-2 border-white dark:border-gray-900 shadow-sm ring-1 ring-black/5 transition-transform duration-200 group-hover:scale-110 {{ $iconBgClass }}" style="left: 0.75rem;">
                        {!! $iconSvg !!}
                    </div>

                    <!-- Visual Timeline Card Container -->
                    <div class="flex-1 bg-white dark:bg-gray-900 rounded-xl p-4 border border-gray-100 dark:border-gray-800 shadow-sm transition-shadow duration-200 hover:shadow-md">
                        <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-sm text-gray-800 dark:text-gray-200">
                                    {{ $log->user?->name ?? 'System Process' }}
                                </span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xxs font-medium {{ $roleBadgeClass }}">
                                    {{ $log->user?->role_display ?? 'Automated' }}
                                </span>
                            </div>
                            <span class="text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap" title="{{ $log->created_at->format('Y-m-d H:i:s') }}">
                                {{ $log->created_at->diffForHumans() }}
                            </span>
                        </div>

                        <!-- Action Title -->
                        <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1">
                            {{ $actionTitle }}
                        </h4>

                        <!-- Activity Messages or Rejection Remarks -->
                        @if (isset($log->details['msg']) && !empty($log->details['msg']))
                            @php
                                $msgBgClass = 'bg-gray-50 dark:bg-gray-950/40 text-gray-600 dark:text-gray-400 border-gray-100 dark:border-gray-800/50';
                                if ($action === 'revision') {
                                    $msgBgClass = 'bg-rose-50/50 text-rose-700 dark:bg-rose-950/20 dark:text-rose-400 border-rose-100/30 dark:border-rose-900/20';
                                } elseif ($action === 'validate') {
                                    $msgBgClass = 'bg-emerald-50/50 text-emerald-700 dark:bg-emerald-950/20 dark:text-emerald-400 border-emerald-100/30 dark:border-emerald-900/20';
                                }
                            @endphp
                            <div class="mt-1 text-xs italic p-2.5 rounded-lg border {{ $msgBgClass }}">
                                "{{ $log->details['msg'] }}"
                            </div>
                        @endif

                        <!-- Detailed Update Comparer -->
                        @if ($action === 'update' && isset($log->details['changes']) && is_array($log->details['changes']) && count($log->details['changes']) > 0)
                            @php
                                // Filter out standard system changes that don't need visualization
                                $filteredChanges = array_filter(
                                    $log->details['changes'],
                                    fn($key) => !in_array($key, ['updated_at', 'created_at', 'signature_image', 'signature_updated_at', 'password', 'remember_token']),
                                    ARRAY_FILTER_USE_KEY
                                );
                            @endphp

                            @if (count($filteredChanges) > 0)
                                <div class="mt-3 bg-gray-50 dark:bg-gray-950/40 border border-gray-100 dark:border-gray-800 rounded-lg p-3">
                                    <div class="text-xxs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                        Changed Attributes
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-2">
                                        @foreach ($filteredChanges as $key => $val)
                                            <div class="flex items-center justify-between gap-2 text-xs py-1 border-b border-gray-100/50 dark:border-gray-800/40 last:border-b-0">
                                                <span class="font-medium text-gray-500 dark:text-gray-400">
                                                    {{ str_replace('_', ' ', ucwords($key, '_')) }}
                                                </span>
                                                <div class="flex items-center gap-1.5 max-w-[60%]">
                                                    <svg class="w-3 h-3 text-gray-300 dark:text-gray-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                                    </svg>
                                                    <span class="font-semibold text-gray-800 dark:text-gray-200 truncate bg-amber-50/50 dark:bg-amber-950/20 px-1.5 py-0.5 rounded border border-amber-100/20 dark:border-amber-900/20 text-xxs" title="{{ is_array($val) ? json_encode($val) : $val }}">
                                                        {{ is_array($val) ? json_encode($val) : $val }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endif

                        <!-- Metadata Footer IP Stamp -->
                        <div class="flex items-center gap-3 text-[10px] text-gray-400 dark:text-gray-500 pt-2 mt-3 border-t border-gray-50 dark:border-gray-800/50">
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V18a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18V5.25M7.5 12h9m-9-3h9m-9 6h9" />
                                </svg>
                                IP Stamp: {{ $log->ip_address ?? '—' }}
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
