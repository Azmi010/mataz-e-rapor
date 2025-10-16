<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'MATAZ - Markaz Tahfidz El-Zahro')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            colors: {
              brand: {
                DEFAULT: '#115E39',
                dark: '#0B462A',
                light: '#E9F5EE'
              },
              accent: '#E5B90A',
              neutral: {
                25: '#FAFBFB',
                100: '#F5F6F7',
                200: '#E5E7EB',
                400: '#9CA3AF',
                600: '#4B5563',
                900: '#111827'
              }
            },
            boxShadow: {
              soft: '0 8px 24px rgba(0,0,0,.06)',
              card: '0 6px 20px rgba(17,94,57,.12)'
            },
            fontFamily: {
              sans: ['Inter', 'ui-sans-serif', 'system-ui']
            },
            borderRadius: {
              xl: '0.9rem'
            }
          }
        }
      }
    </script>
    <style>
      :root { --radius: 14px; }
      *:focus-visible { outline: 2px solid #E5B90A; outline-offset: 2px; }
      body { font-feature-settings: "rlig" 1, "calt" 1; }
    </style>
  </head>
  <body class="font-sans antialiased text-neutral-900 bg-neutral-25">
    <header class="sticky top-0 z-40 bg-white/80 backdrop-blur border-b border-neutral-200">
      <nav class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <a href="{{ url('/') }}" class="flex items-center gap-3">
          <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-brand/10 ring-1 ring-brand/20">
            {{-- <svg class="h-6 w-6 text-brand" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
              <path d="M12 2l7 4v8l-7 4-7-4V6l7-4z"/>
              <circle cx="12" cy="12" r="2" fill="currentColor" class="text-accent"></circle>
            </svg> --}}
            <img src="{{ asset('img/logo.png') }}" alt="MATAZ logo" class="h-8 w-8 object-contain">
          </span>
          <div class="leading-tight">
            <p class="font-semibold text-brand">MATAZ</p>
            <p class="text-xs text-neutral-600">Markaz Tahfidz El‑Zahro</p>
          </div>
        </a>
        <button class="md:hidden inline-flex items-center justify-center rounded-lg p-2 text-neutral-900 hover:bg-neutral-100" aria-label="Buka menu">
          <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <ul class="hidden md:flex items-center gap-6 text-sm">
          <li><a href="#tentang" class="hover:text-brand">Tentang</a></li>
          <li><a href="#program" class="hover:text-brand">Program</a></li>
          <li><a href="#pengajar" class="hover:text-brand">Pengajar</a></li>
          <li><a href="#testimoni" class="hover:text-brand">Testimoni</a></li>
          <li><a href="#kontak" class="hover:text-brand">Kontak</a></li>
        </ul>
        <a href="/login" class="hidden md:inline-flex items-center gap-2 rounded-lg bg-brand text-white px-4 py-2 text-sm font-medium hover:bg-brand-dark transition">
          Masuk
          <svg class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="M13.2 4l7.2 8-7.2 8H11l5.4-6H4v-4h12.4L11 6h2.2z"/></svg>
        </a>
      </nav>
    </header>

    <main>@yield('content')</main>

    <footer class="mt-20 bg-brand text-white">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-14 grid gap-10 md:grid-cols-4">
        <div>
          <div class="flex items-center gap-3">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-white/10 ring-1 ring-white/20">
              {{-- <svg class="h-6 w-6 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l7 4v8l-7 4-7-4V6l7-4z"/></svg> --}}
              <img src="{{ asset('img/logo.png') }}" alt="">
            </span>
            <p class="font-semibold">MATAZ</p>
          </div>
          <p class="mt-4 text-sm/6 text-white/80">Pusat pembelajaran dan penghafalan Al‑Qur’an dengan metode teruji dan pengajar berkualitas.</p>
        </div>
        <div>
          <p class="font-semibold mb-3">Link Penting</p>
          <ul class="space-y-2 text-sm/6 text-white/80">
            <li><a href="#tentang" class="hover:text-accent">Tentang Kami</a></li>
            <li><a href="#program" class="hover:text-accent">Program</a></li>
            <li><a href="#pengajar" class="hover:text-accent">Pengajar</a></li>
            <li><a href="#kontak" class="hover:text-accent">Kontak</a></li>
          </ul>
        </div>
        <div>
          <p class="font-semibold mb-3">Program</p>
          <ul class="space-y-2 text-sm/6 text-white/80">
            <li>Tahfidz Reguler</li>
            <li>Tahfidz Intensif</li>
            <li>Tahsin Al‑Qur’an</li>
            <li>Weekend Tahfidz</li>
          </ul>
        </div>
        <div id="daftar">
          <p class="font-semibold mb-3">Langganan</p>
          <p class="text-sm/6 text-white/80">Dapatkan informasi kegiatan & pendaftaran.</p>
          <form class="mt-4 flex gap-2">
            <label class="sr-only" for="footer-email">Email</label>
            <input id="footer-email" type="email" placeholder="Email Anda" class="w-full rounded-lg px-3 py-2 text-neutral-900 placeholder-neutral-600" />
            <button type="button" class="rounded-lg bg-accent text-neutral-900 px-4 py-2 font-medium">Kirim</button>
          </form>
        </div>
      </div>
      <div class="border-t border-white/15">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6 text-sm text-white/70">
          © 2025 MATAZ — All rights reserved.
        </div>
      </div>
    </footer>
  </body>
</html>
