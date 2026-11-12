
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#080f1c">

    <title>IT Panel — Riwayat Log Aktivitas</title>

    @include('partials.favicon')

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        * {
            box-sizing: border-box;
        }

        html,
        body {
            width: 100%;
            height: 100%;
            margin: 0;
            overflow: hidden;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background:
                radial-gradient(circle at 12% 15%, rgba(14, 165, 233, .13), transparent 30%),
                radial-gradient(circle at 88% 82%, rgba(59, 130, 246, .12), transparent 28%),
                #080f1c;
        }

        h1, h2, h3 {
            font-family: 'Manrope', sans-serif;
        }

        .grid-pattern {
            background-image:
                linear-gradient(rgba(148,163,184,.045) 1px, transparent 1px),
                linear-gradient(90deg, rgba(148,163,184,.045) 1px, transparent 1px);
            background-size: 34px 34px;
            mask-image: linear-gradient(to bottom, black, transparent 90%);
        }

        .glass {
            background: rgba(15, 23, 42, .78);
            border: 1px solid rgba(148, 163, 184, .16);
            box-shadow: 0 24px 80px rgba(0, 0, 0, .35);
            backdrop-filter: blur(22px);
        }

        .field {
            background: rgba(2, 6, 23, .48);
            border: 1px solid rgba(148, 163, 184, .2);
            transition: .2s ease;
        }

        .field:focus-within {
            border-color: rgba(56, 189, 248, .75);
            background: rgba(2, 6, 23, .72);
            box-shadow: 0 0 0 3px rgba(14, 165, 233, .12);
        }

        .login-button {
            background: linear-gradient(105deg, #0284c7, #2563eb);
            box-shadow: 0 8px 24px rgba(37, 99, 235, .22);
            transition: .2s ease;
        }

        .login-button:hover {
            filter: brightness(1.12);
            transform: translateY(-1px);
            box-shadow: 0 12px 28px rgba(37, 99, 235, .3);
        }

        .soft-link {
            transition: .2s ease;
        }

        .soft-link:hover {
            color: #e0f2fe;
            background: rgba(14, 165, 233, .09);
            border-color: rgba(56, 189, 248, .3);
        }

        .company-logo {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        /* Layar dengan tinggi terbatas */
        @media (max-height: 750px) and (min-width: 1024px) {
            .main-panel {
                padding-top: 22px !important;
                padding-bottom: 22px !important;
            }

            .left-intro {
                margin-top: 30px !important;
            }

            .overview-card {
                margin-top: 22px !important;
            }

            .login-content {
                padding-top: 24px !important;
                padding-bottom: 24px !important;
            }

            .login-header {
                margin-bottom: 20px !important;
            }

            .dashboard-divider {
                margin-top: 22px !important;
                margin-bottom: 22px !important;
            }

            .login-footer {
                margin-top: 20px !important;
            }
        }

        /* Mobile */
        @media (max-width: 1023px) {
            .main-panel {
                max-height: calc(100dvh - 24px);
            }
        }

        @media (max-height: 650px) {
            .mobile-brand {
                margin-bottom: 16px !important;
            }

            .login-header {
                margin-bottom: 16px !important;
            }

            .login-content {
                padding-top: 20px !important;
                padding-bottom: 20px !important;
            }

            .dashboard-divider {
                margin-top: 16px !important;
                margin-bottom: 16px !important;
            }

            .login-footer {
                margin-top: 16px !important;
            }
        }
    </style>
</head>

<body class="text-slate-100 antialiased">

    <!-- BACKGROUND GRID -->
    <div class="grid-pattern pointer-events-none fixed inset-0"></div>

    <!-- MAIN WRAPPER -->
    <div class="relative flex h-full w-full items-center justify-center overflow-hidden px-4 py-3">

        <main class="main-panel grid w-full max-h-full max-w-5xl overflow-hidden rounded-[28px] glass lg:grid-cols-[1.05fr_.95fr]">

            <!-- LEFT PANEL -->
            <section class="relative hidden min-h-0 flex-col justify-between overflow-hidden border-r border-white/10 bg-slate-950/35 p-8 lg:flex xl:p-10">

                <!-- DECORATION -->
                <div class="absolute -left-24 top-28 h-72 w-72 rounded-full bg-sky-500/10 blur-3xl"></div>

                <div class="absolute -bottom-20 right-0 h-72 w-72 rounded-full bg-blue-600/10 blur-3xl"></div>

                <div class="relative z-10">

                    <!-- COMPANY BRAND -->
                    <div class="flex items-center gap-3">

                        <div class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-xl">
                            <img
                                src="{{ asset('images/logo.png') }}"
                                alt="Company Logo"
                                class="company-logo"
                            >
                        </div>

                        <div>
                            <p class="font-bold tracking-[.18em] text-white">
                                IT PANEL
                            </p>

                            <p class="text-[10px] font-semibold uppercase tracking-[.22em] text-slate-500">
                                System Access
                            </p>
                        </div>

                    </div>

                    <!-- INTRO -->
                    <div class="left-intro mt-12 max-w-md">

                        <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-emerald-400/20 bg-emerald-400/[.07] px-3 py-1.5 text-[11px] font-semibold tracking-wide text-emerald-300">

                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 shadow-[0_0_10px_#34d399]"></span>

                            INTERNAL SYSTEM

                        </div>

                        <h1 class="text-4xl font-extrabold leading-[1.18] tracking-tight text-white xl:text-[42px]">
                            Monitor aktivitas sistem dengan lebih terkontrol.
                        </h1>

                        <p class="mt-5 max-w-sm text-sm leading-7 text-slate-400">
                            Akses panel IT untuk meninjau riwayat log dan membantu menjaga operasional sistem tetap terpantau.
                        </p>

                    </div>

                    <!-- SYSTEM OVERVIEW -->
                    <div class="overview-card mt-8 rounded-2xl border border-white/10 bg-white/[.035] p-4">

                        <div class="mb-4 flex items-center justify-between">

                            <span class="text-xs font-semibold uppercase tracking-[.16em] text-slate-400">
                                System Overview
                            </span>

                            <i class="fa-solid fa-chart-line text-sky-300"></i>

                        </div>

                        <div class="space-y-4">

                            <!-- ACTIVITY -->
                            <div class="flex items-center gap-3">

                                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-sky-400/10 text-sky-300">
                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                </div>

                                <div>
                                    <p class="text-sm font-semibold text-slate-200">
                                        Activity History
                                    </p>

                                    <p class="mt-0.5 text-xs text-slate-500">
                                        Riwayat aktivitas sistem
                                    </p>
                                </div>

                                <i class="fa-solid fa-arrow-up-right-from-square ml-auto text-xs text-slate-600"></i>

                            </div>

                            <div class="h-px bg-white/[.07]"></div>

                            <!-- ACCESS CONTROL -->
                            <div class="flex items-center gap-3">

                                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-400/10 text-indigo-300">
                                    <i class="fa-solid fa-shield-halved"></i>
                                </div>

                                <div>
                                    <p class="text-sm font-semibold text-slate-200">
                                        Access Control
                                    </p>

                                    <p class="mt-0.5 text-xs text-slate-500">
                                        Akses khusus tim IT
                                    </p>
                                </div>

                                <i class="fa-solid fa-lock ml-auto text-xs text-slate-600"></i>

                            </div>

                        </div>

                    </div>

                </div>

                <!-- LEFT FOOTER -->
                <div class="relative z-10 flex items-center justify-between text-[11px] text-slate-600">

                    <span>© {{ date('Y') }} IT Panel</span>

                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-circle text-[6px] text-emerald-400"></i>
                        Secure Workspace
                    </span>

                </div>

            </section>


            <!-- RIGHT PANEL / LOGIN -->
            <section class="login-content flex flex-col justify-center p-7 sm:p-10 lg:p-10 xl:p-12">

                <!-- MOBILE BRAND -->
                <div class="mobile-brand mb-8 flex items-center gap-3 lg:hidden">

                    <div class="flex h-11 w-11 items-center justify-center overflow-hidden rounded-xl">

                        <img
                            src="{{ asset('images/logo.png') }}"
                            alt="Company Logo"
                            class="company-logo"
                        >

                    </div>

                    <div>
                        <p class="font-bold tracking-[.18em] text-white">
                            IT PANEL
                        </p>

                        <p class="text-[10px] font-semibold uppercase tracking-[.2em] text-slate-500">
                            System Access
                        </p>
                    </div>

                </div>


                <!-- LOGIN HEADER -->
                <div class="login-header mb-8">

                    <p class="mb-3 text-xs font-bold uppercase tracking-[.2em] text-sky-400">
                        Welcome Back
                    </p>

                    <h2 class="text-3xl font-extrabold tracking-tight text-white">
                        Sign in to continue
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-slate-400">
                        Masuk menggunakan akun internal IT Anda.
                    </p>

                </div>


                <!-- LOGIN FORM -->
                <form method="POST" action="{{ route('it.login.attempt') }}" class="space-y-5">

                    @csrf

                    <!-- USER ID -->
                    <div>

                        <label for="employee_code" class="mb-2 block text-xs font-semibold tracking-wide text-slate-300">
                            USER ID
                        </label>

                        <div class="field flex items-center rounded-xl px-4">

                            <i class="fa-regular fa-id-card mr-3 text-slate-500"></i>

                            <input
                                id="employee_code"
                                type="text"
                                name="employee_code"
                                value="{{ old('employee_code') }}"
                                placeholder="Masukkan ID karyawan"
                                autocomplete="username"
                                autofocus
                                class="w-full bg-transparent py-3.5 text-sm text-white outline-none placeholder:text-slate-600"
                            >

                        </div>

                    </div>


                    <!-- PASSWORD -->
                    <div>

                        <div class="mb-2 flex items-center justify-between">

                            <label for="password" class="block text-xs font-semibold tracking-wide text-slate-300">
                                PASSWORD
                            </label>

                            <span class="text-[10px] font-medium text-slate-600">
                                Private Access
                            </span>

                        </div>

                        <div class="field flex items-center rounded-xl px-4">

                            <i class="fa-solid fa-lock mr-3 text-slate-500"></i>

                            <input
                                id="password"
                                type="password"
                                name="password"
                                placeholder="Masukkan password"
                                autocomplete="current-password"
                                class="w-full bg-transparent py-3.5 text-sm text-white outline-none placeholder:text-slate-600"
                            >

                        </div>

                    </div>


                    <!-- ERROR MESSAGE -->
                    @error('employee_code')

                        <div class="flex items-start gap-2 rounded-xl border border-red-400/20 bg-red-500/[.08] px-4 py-3 text-xs leading-5 text-red-300">

                            <i class="fa-solid fa-circle-exclamation mt-0.5"></i>

                            <span>{{ $message }}</span>

                        </div>

                    @enderror


                    <!-- LOGIN BUTTON -->
                    <button
                        type="submit"
                        class="login-button flex w-full items-center justify-center gap-2 rounded-xl py-3.5 text-sm font-bold text-white"
                    >

                        Login to IT Panel

                        <i class="fa-solid fa-arrow-right text-xs"></i>

                    </button>

                </form>


                <!-- DIVIDER -->
                <div class="dashboard-divider my-8 flex items-center gap-4">

                    <div class="h-px flex-1 bg-white/10"></div>

                    <span class="text-[10px] font-semibold uppercase tracking-[.16em] text-slate-600">
                        Other Dashboards
                    </span>

                    <div class="h-px flex-1 bg-white/10"></div>

                </div>


                <!-- DASHBOARD LINKS -->
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">

                    <!-- PRODUCTION -->
                    <a
                        href="{{ route('dashboard') }}"
                        class="soft-link flex items-center gap-3 rounded-xl border border-white/10 bg-white/[.025] px-3.5 py-3 text-xs font-semibold text-slate-400"
                    >

                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-700/50 text-slate-300">
                            <i class="fa-solid fa-industry"></i>
                        </span>

                        <span>Dashboard Produksi</span>

                        <i class="fa-solid fa-arrow-up-right-from-square ml-auto text-[10px] opacity-50"></i>

                    </a>


                    <!-- WAREHOUSE -->
                    <a
                        href="{{ route('warehouse.dashboard') }}"
                        class="soft-link flex items-center gap-3 rounded-xl border border-white/10 bg-white/[.025] px-3.5 py-3 text-xs font-semibold text-slate-400"
                    >

                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-700/50 text-slate-300">
                            <i class="fa-solid fa-warehouse"></i>
                        </span>

                        <span>Dashboard Warehouse</span>

                        <i class="fa-solid fa-arrow-up-right-from-square ml-auto text-[10px] opacity-50"></i>

                    </a>

                </div>


                <!-- FOOTER -->
                <p class="login-footer mt-9 text-center text-[10px] leading-5 text-slate-600">
                    Authorized personnel only. All access may be monitored and logged.
                </p>

            </section>

        </main>

    </div>

</body>
</html>
