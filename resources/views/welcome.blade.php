<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@php
    $appName = config('app.name', 'APLI Mail');
    $canonicalUrl = url()->current();
    $metaTitle = 'APLI Mail Untuk Travel Umrah dan Haji | Email Personal Jamaah + Inbox Viewer';
    $metaDescription = 'APLI Mail membantu travel membuat email personal untuk setiap jamaah, menerima email masuk secara aman, dan mengelola inbox terpusat dengan viewer modern. Tersedia paket Free, Silver, dan Gold.';
    $metaKeywords = 'email travel umrah, email personal jamaah, sistem email travel, inbox viewer travel, email haji umrah, catch all email saas';
    $heroImagePrompt = rawurlencode('professional SaaS email dashboard for travel agency managing personal pilgrim email inboxes, modern blue interface, realistic laptop and mobile screen, premium business website hero, clean office lighting, highly detailed');
    $heroImageUrl = "https://coresg-normal.trae.ai/api/ide/v1/text_to_image?prompt={$heroImagePrompt}&image_size=landscape_16_9";
    $plans = [
        [
            'name' => 'Paket Free',
            'period' => '7 hari',
            'price' => 'Rp. 125rb',
            'headline' => 'Mulai cepat untuk trial operasional travel',
            'limits' => [
                '2 inbox aktif',
                '10 email received',
                'Viewer token per group',
                'Cocok untuk uji coba tim kecil',
            ],
            'badge' => 'Starter',
        ],
        [
            'name' => 'Paket Silver',
            'period' => '12 bulan',
            'price' => 'Rp. 250rb',
            'headline' => 'Skala stabil untuk travel yang sudah berjalan rutin',
            'limits' => [
                '1000 inbox aktif',
                'Unlimited email received',
                'Import inbox CSV/XLSX',
                'Dashboard admin group',
            ],
            'badge' => 'Populer',
        ],
        [
            'name' => 'Paket Gold',
            'period' => '12 bulan',
            'price' => 'Rp. 350rb',
            'headline' => 'Volume besar untuk travel dengan banyak jamaah dan cabang',
            'limits' => [
                '5000 inbox aktif',
                'Unlimited email received',
                'Viewer email skala besar',
                'Siap untuk operasional high-volume',
            ],
            'badge' => 'Scale',
        ],
    ];
    $faqs = [
        [
            'question' => 'APLI Mail cocok untuk bisnis apa?',
            'answer' => 'APLI Mail paling cocok untuk travel umrah dan haji yang ingin membuat email personal untuk setiap jamaah, memusatkan email masuk, dan membagikan akses viewer yang aman ke tim operasional.',
        ],
        [
            'question' => 'Apakah setiap jamaah bisa memiliki email sendiri?',
            'answer' => 'Ya. Travel dapat membuat inbox personal untuk masing-masing jamaah, seperti nama-jamaah@domain, lalu menerima seluruh email masuk dan mengelolanya dari dashboard.',
        ],
        [
            'question' => 'Bagaimana pembatasan paket subscription bekerja?',
            'answer' => 'Setiap paket menentukan durasi berlangganan, jumlah inbox aktif, dan kuota email received. Paket Silver dan Gold mendukung email received tanpa batas sesuai informasi paket.',
        ],
        [
            'question' => 'Apakah import inbox bisa dilakukan sekaligus?',
            'answer' => 'Bisa. Admin SaaS maupun admin group dapat melakukan import inbox massal melalui file CSV atau XLSX sesuai hak akses masing-masing.',
        ],
    ];
    $schema = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'Organization',
                'name' => $appName,
                'url' => config('app.url'),
                'description' => $metaDescription,
            ],
            [
                '@type' => 'SoftwareApplication',
                'name' => 'APLI Mail',
                'applicationCategory' => 'BusinessApplication',
                'operatingSystem' => 'Web',
                'description' => $metaDescription,
                'offers' => collect($plans)->map(fn (array $plan) => [
                    '@type' => 'Offer',
                    'name' => $plan['name'],
                    'priceCurrency' => 'IDR',
                    'price' => preg_replace('/[^\d]/', '', $plan['price']),
                    'description' => $plan['headline'].' - '.$plan['period'],
                ])->all(),
            ],
            [
                '@type' => 'FAQPage',
                'mainEntity' => collect($faqs)->map(fn (array $faq) => [
                    '@type' => 'Question',
                    'name' => $faq['question'],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $faq['answer'],
                    ],
                ])->all(),
            ],
        ],
    ];
@endphp
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $metaTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <meta name="keywords" content="{{ $metaKeywords }}">
    <meta name="robots" content="index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1">
    <meta name="author" content="{{ $appName }}">
    <meta name="application-name" content="{{ $appName }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <meta property="og:locale" content="id_ID">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:site_name" content="{{ $appName }}">
    <meta property="og:image" content="{{ $heroImageUrl }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    <meta name="twitter:image" content="{{ $heroImageUrl }}">
    <script type="application/ld+json">{!! \Illuminate\Support\Js::from($schema) !!}</script>
    <script>
        if (localStorage.getItem('apli-theme') === 'dark' || (! localStorage.getItem('apli-theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="relative overflow-hidden">
        <div class="absolute inset-x-0 top-0 h-[42rem] bg-[radial-gradient(circle_at_top,_rgba(59,130,246,0.26),_transparent_55%)]"></div>

        <header class="relative mx-auto flex max-w-7xl items-center justify-between px-4 py-6 sm:px-6 lg:px-8">
            <div class="flex items-center gap-3">
                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-blue-700 text-sm font-bold text-white shadow-lg shadow-blue-600/25">AM</span>
                <div>
                    <p class="text-sm font-semibold">APLI Mail</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Email Personal Jamaah & Viewer SaaS</p>
                </div>
            </div>

            <div class="hidden items-center gap-6 text-sm font-medium text-slate-600 dark:text-slate-300 md:flex">
                <a href="#fitur" class="hover:text-blue-600 dark:hover:text-blue-300">Fitur</a>
                <a href="#paket" class="hover:text-blue-600 dark:hover:text-blue-300">Paket</a>
                <a href="#use-case" class="hover:text-blue-600 dark:hover:text-blue-300">Use Case</a>
                <a href="#faq" class="hover:text-blue-600 dark:hover:text-blue-300">FAQ</a>
            </div>

            <div class="flex items-center gap-3">
                <button type="button" data-theme-toggle class="btn-secondary px-4 py-2.5">
                    Mode Gelap
                </button>
                @auth
                    <a href="{{ route('dashboard', absolute: false) }}" class="btn-primary px-4 py-2.5">Dashboard</a>
                @else
                    <a href="{{ route('login', absolute: false) }}" class="btn-primary px-4 py-2.5">Login Admin</a>
                @endauth
            </div>
        </header>

        <main class="relative mx-auto max-w-7xl px-4 pb-16 pt-8 sm:px-6 lg:px-8">
            <section class="grid gap-8 lg:grid-cols-[1.05fr_0.95fr] lg:items-center">
                <div>
                    <p class="section-kicker">Landing Page Travel Email SaaS</p>
                    <h1 class="mt-6 max-w-4xl text-5xl font-semibold tracking-tight text-slate-950 dark:text-white sm:text-6xl">
                        Buat email personal untuk setiap jamaah dan kelola semua inbox travel dari satu dashboard.
                    </h1>
                    <p class="mt-6 max-w-3xl text-lg leading-8 text-slate-600 dark:text-slate-300">
                        APLI Mail membantu travel umrah dan haji membuat inbox personal per jamaah, menerima email penting seperti visa, tiket, manifest, dan notifikasi pembayaran, lalu membukanya lewat viewer modern yang aman dan mudah dipantau tim operasional.
                    </p>

                    <div class="mt-8 flex flex-wrap gap-4">
                        <a href="#paket" class="btn-primary">Lihat Paket Subscription</a>
                        <a href="#use-case" class="btn-secondary">Lihat Use Case Travel</a>
                    </div>

                    <div class="mt-10 grid gap-4 sm:grid-cols-3">
                        <div class="glass-banner">
                            <p class="text-3xl font-semibold text-slate-950 dark:text-white">Personal</p>
                            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Satu jamaah bisa punya satu email sendiri untuk komunikasi dan dokumen perjalanan.</p>
                        </div>
                        <div class="glass-banner">
                            <p class="text-3xl font-semibold text-slate-950 dark:text-white">Terpusat</p>
                            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Tim travel memantau semua inbox dari dashboard group tanpa akses manual satu per satu.</p>
                        </div>
                        <div class="glass-banner">
                            <p class="text-3xl font-semibold text-slate-950 dark:text-white">Aman</p>
                            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Viewer token per group membuat akses email lebih terkontrol untuk masing-masing customer.</p>
                        </div>
                    </div>
                </div>

                <div class="page-hero p-6">
                    <div class="mb-5 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-slate-950 dark:text-white">Skenario Travel Umrah</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Inbox jamaah, operasional, dan vendor dalam satu alur</p>
                        </div>
                        <span class="status-badge-blue">SEO Ready</span>
                    </div>

                    <img
                        src="{{ $heroImageUrl }}"
                        alt="Dashboard APLI Mail untuk travel yang mengelola email personal jamaah"
                        class="h-auto w-full rounded-[1.8rem] border border-white/70 object-cover shadow-sm dark:border-slate-800/80"
                    />

                    <div class="mt-5 space-y-3">
                        <div class="glass-banner border-slate-200/80 bg-white/80 shadow-none dark:border-slate-800/80 dark:bg-slate-950/50">
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">siti-nurjanah@email.apli.my.id</p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Inbox personal jamaah untuk visa, tiket, invoice, dan informasi keberangkatan.</p>
                        </div>
                        <div class="space-y-3 rounded-[1.8rem] border border-white/70 bg-white/80 p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900/80">
                            <div class="mail-row">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white">E-ticket keberangkatan jamaah sudah terbit</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">ticketing@airline.example</p>
                                </div>
                                <span class="status-badge-slate">PDF</span>
                            </div>
                            <div class="mail-row">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white">Konfirmasi visa dan data paspor</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">visa@provider.example</p>
                                </div>
                                <span class="status-badge-slate">2 file</span>
                            </div>
                            <div class="mail-row">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white">Info briefing keberangkatan kloter Makkah</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">ops@travel.example</p>
                                </div>
                                <span class="status-badge-emerald">baru</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="fitur" class="mt-20">
                <div class="max-w-3xl">
                    <p class="section-kicker">Fitur Utama</p>
                    <h2 class="section-title">Satu sistem email untuk operasional travel yang cepat, rapi, dan mudah dijual ke pelanggan</h2>
                    <p class="section-copy">Landing page ini menempatkan APLI Mail sebagai solusi email personal jamaah, inbox monitoring, dan akses viewer terpusat untuk travel, umrah, haji, pendidikan, maupun customer service berbasis inbox massal.</p>
                </div>

                <div class="mt-8 grid gap-6 lg:grid-cols-3">
                    <div class="panel-card">
                        <p class="text-sm font-semibold text-blue-600">Email Personal Jamaah</p>
                        <h3 class="mt-3 text-xl font-semibold text-slate-950 dark:text-white">Satu inbox per jamaah atau per kebutuhan operasional</h3>
                        <p class="mt-3 text-sm leading-7 text-slate-600 dark:text-slate-300">Travel dapat membuat email untuk masing-masing jamaah, marketing, ticketing, visa, handling, atau cabang agar informasi penting tidak tercampur.</p>
                    </div>
                    <div class="panel-card">
                        <p class="text-sm font-semibold text-blue-600">Inbox Viewer Modern</p>
                        <h3 class="mt-3 text-xl font-semibold text-slate-950 dark:text-white">Buka email dengan cepat tanpa setup rumit</h3>
                        <p class="mt-3 text-sm leading-7 text-slate-600 dark:text-slate-300">Daftar email tampil rapi dengan pencarian, preview isi, lampiran, dark mode, dan URL viewer bertoken untuk akses yang lebih aman.</p>
                    </div>
                    <div class="panel-card">
                        <p class="text-sm font-semibold text-blue-600">Multi Tenant SaaS</p>
                        <h3 class="mt-3 text-xl font-semibold text-slate-950 dark:text-white">Group pelanggan, token viewer, dan admin group</h3>
                        <p class="mt-3 text-sm leading-7 text-slate-600 dark:text-slate-300">Admin SaaS membuat customer baru, menetapkan viewer token, lalu menyerahkan pengelolaan inbox ke admin group masing-masing.</p>
                    </div>
                </div>
            </section>

            <section id="paket" class="mt-20">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-3xl">
                        <p class="section-kicker">Paket Subscription</p>
                        <h2 class="section-title">Pilih paket sesuai skala travel dan jumlah inbox jamaah yang Anda butuhkan</h2>
                        <p class="section-copy">Semua paket dirancang untuk operasional travel yang membutuhkan email received terpusat, viewer cepat, dan pembagian akses per customer atau per tim.</p>
                    </div>
                    <a href="{{ route('login', absolute: false) }}" class="btn-secondary">Diskusikan Setup Admin</a>
                </div>

                <div class="mt-8 grid gap-6 xl:grid-cols-3">
                    @foreach ($plans as $plan)
                        <article class="panel-card flex h-full flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-xl font-semibold text-slate-950 dark:text-white">{{ $plan['name'] }}</p>
                                    <span class="status-badge-blue">{{ $plan['badge'] }}</span>
                                </div>
                                <p class="mt-4 text-4xl font-semibold tracking-tight text-slate-950 dark:text-white">{{ $plan['price'] }}</p>
                                <p class="mt-2 text-sm font-medium text-blue-600 dark:text-blue-300">Masa aktif {{ $plan['period'] }}</p>
                                <p class="mt-4 text-sm leading-7 text-slate-600 dark:text-slate-300">{{ $plan['headline'] }}</p>

                                <div class="mt-6 space-y-3">
                                    @foreach ($plan['limits'] as $limit)
                                        <div class="helper-item">
                                            <span class="helper-item-dot"></span>
                                            <p>{{ $limit }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mt-8 flex gap-3">
                                <a href="{{ route('login', absolute: false) }}" class="btn-primary w-full">Pilih Paket</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            <section id="use-case" class="mt-20 grid gap-6 lg:grid-cols-[1fr_1fr]">
                <div class="panel-card">
                    <p class="section-kicker">Use Case Travel</p>
                    <h2 class="section-title">Travel bisa memberikan email personal untuk masing-masing jamaah</h2>
                    <p class="section-copy">Dengan inbox personal, travel dapat menerima tiket, visa, notifikasi pembayaran, hasil medical checkup, dan informasi vendor langsung ke alamat jamaah atau alamat yang disiapkan tim.</p>
                    <div class="mt-6 space-y-3">
                        <div class="helper-item">
                            <span class="helper-item-dot"></span>
                            <p>Setiap jamaah memperoleh identitas email yang mudah dipahami dan mudah dicari.</p>
                        </div>
                        <div class="helper-item">
                            <span class="helper-item-dot"></span>
                            <p>Tim operasional dapat mengecek email masuk tanpa harus membuka banyak akun satu per satu.</p>
                        </div>
                        <div class="helper-item">
                            <span class="helper-item-dot"></span>
                            <p>Group viewer token memudahkan pembagian akses ke masing-masing customer atau divisi.</p>
                        </div>
                        <div class="helper-item">
                            <span class="helper-item-dot"></span>
                            <p>Import CSV/XLSX mempercepat pembuatan inbox massal saat ada kloter atau batch keberangkatan baru.</p>
                        </div>
                    </div>
                </div>

                <div class="panel-card">
                    <p class="section-kicker">Alur Operasional</p>
                    <h2 class="section-title">Dari onboarding customer sampai inbox jamaah aktif dalam satu dashboard</h2>
                    <div class="mt-6 space-y-4">
                        <div class="detail-pair">
                            <p class="detail-pair-label">1. Setup customer</p>
                            <p class="detail-pair-value">Admin SaaS membuat group baru, viewer token, dan admin group dari satu form onboarding.</p>
                        </div>
                        <div class="detail-pair">
                            <p class="detail-pair-label">2. Import inbox</p>
                            <p class="detail-pair-value">Admin group mengimpor inbox jamaah, tim ticketing, visa, atau handling via CSV/XLSX.</p>
                        </div>
                        <div class="detail-pair">
                            <p class="detail-pair-label">3. Email received</p>
                            <p class="detail-pair-value">Semua email masuk diproses, disimpan, dan siap dibaca melalui viewer inbox bertoken.</p>
                        </div>
                        <div class="detail-pair">
                            <p class="detail-pair-label">4. Monitoring</p>
                            <p class="detail-pair-value">Tim melihat statistik email, lampiran, dan daftar inbox terbaru dari panel admin yang rapi.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="mt-20">
                <div class="page-hero p-8">
                    <div class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr] lg:items-center">
                        <div>
                            <p class="section-kicker">Kenapa APLI Mail</p>
                            <h2 class="section-title">Bangun layanan email travel yang terlihat profesional di mata jamaah dan tim internal</h2>
                            <p class="section-copy">Ketika setiap jamaah memiliki email personal dan setiap pesan bisa dipantau dari viewer modern, travel lebih mudah menjaga informasi keberangkatan tetap rapi, cepat, dan mudah ditelusuri.</p>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
                            <div class="glass-banner">
                                <p class="text-3xl font-semibold text-slate-950 dark:text-white">1000+</p>
                                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Inbox aktif tersedia di Paket Silver untuk skala travel yang berkembang.</p>
                            </div>
                            <div class="glass-banner">
                                <p class="text-3xl font-semibold text-slate-950 dark:text-white">5000</p>
                                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Inbox aktif di Paket Gold untuk operasional besar dan multi-kloter.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="faq" class="mt-20">
                <div class="max-w-3xl">
                    <p class="section-kicker">FAQ</p>
                    <h2 class="section-title">Pertanyaan yang sering ditanyakan calon pelanggan</h2>
                    <p class="section-copy">Bagian FAQ ini juga membantu SEO karena menjawab intent pencarian seputar email travel, inbox jamaah, dan sistem viewer SaaS.</p>
                </div>

                <div class="mt-8 grid gap-4">
                    @foreach ($faqs as $faq)
                        <article class="panel-card">
                            <h3 class="text-lg font-semibold text-slate-950 dark:text-white">{{ $faq['question'] }}</h3>
                            <p class="mt-3 text-sm leading-7 text-slate-600 dark:text-slate-300">{{ $faq['answer'] }}</p>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="mt-20 pb-8">
                <div class="page-hero p-8 text-center">
                    <p class="section-kicker">Call To Action</p>
                    <h2 class="section-title mx-auto max-w-3xl">Siapkan email personal jamaah dan operasional travel Anda mulai hari ini</h2>
                    <p class="section-copy mx-auto">Mulai dari Paket Free untuk uji coba cepat, lalu tingkatkan ke Silver atau Gold saat jumlah inbox dan kebutuhan email received terus bertambah.</p>
                    <div class="mt-8 flex flex-wrap justify-center gap-4">
                        <a href="#paket" class="btn-primary">Pilih Paket Sekarang</a>
                        <a href="{{ route('login', absolute: false) }}" class="btn-secondary">Masuk Ke Admin</a>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
