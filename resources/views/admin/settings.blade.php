<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - MedQ</title>
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/fontawesome/css/all.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        /* ── Page override ── */
        .main-content { background: #0d1117 !important; min-height: 100vh; }

        /* ── Terminal typography ── */
        .term-label {
            font-family: 'Courier New', Courier, monospace;
            font-size: 0.7rem; letter-spacing: 0.12em;
            text-transform: uppercase; color: #3fb950;
        }
        .term-prompt { font-family: 'Courier New', Courier, monospace; font-size: 0.8rem; color: #8b949e; }
        .term-prompt span { color: #58a6ff; }

        /* ── Cards ── */
        .dark-card { background: #161b22; border: 1px solid #30363d; border-radius: 10px; overflow: hidden; }
        .dark-card-header {
            background: #1c2128; border-bottom: 1px solid #30363d;
            padding: 12px 18px; display: flex; align-items: center; gap: 10px;
        }
        .dark-card-header .header-dot { width: 11px; height: 11px; border-radius: 50%; display: inline-block; }
        .dark-card-header .title-text { font-family: 'Courier New', Courier, monospace; font-size: 0.82rem; color: #8b949e; }
        .dark-card-header .title-text strong { color: #c9d1d9; }

        /* ── Inputs ── */
        .dark-input {
            background: #0d1117 !important; border: 1px solid #30363d !important;
            color: #c9d1d9 !important; border-radius: 6px;
            font-family: 'Courier New', Courier, monospace; font-size: 0.9rem;
        }
        .dark-input:focus { border-color: #58a6ff !important; box-shadow: 0 0 0 3px rgba(88,166,255,0.12) !important; }
        .dark-input::placeholder { color: #484f58 !important; }

        /* ── Upload zone ── */
        .upload-zone {
            border: 2px dashed #30363d; border-radius: 8px; padding: 18px 14px;
            text-align: center; cursor: pointer; transition: border-color 0.2s, background 0.2s;
        }
        .upload-zone:hover { border-color: #58a6ff; background: rgba(88,166,255,0.05); }
        .upload-zone input[type=file] { display: none; }
        .upload-zone .uz-icon { color: #58a6ff; font-size: 1.6rem; margin-bottom: 6px; }
        .upload-zone .uz-title { color: #c9d1d9; font-size: 0.85rem; font-weight: 600; margin-bottom: 2px; }
        .upload-zone .uz-sub { color: #484f58; font-size: 0.75rem; }

        /* ── Logo preview ── */
        .logo-ring { width: 80px; height: 80px; border-radius: 50%; border: 2px solid #30363d; padding: 5px; background: #0d1117; object-fit: contain; flex-shrink: 0; }
        .logo-ring-placeholder { width: 80px; height: 80px; border-radius: 50%; border: 2px dashed #30363d; display: flex; align-items: center; justify-content: center; background: #0d1117; color: #30363d; font-size: 1.6rem; flex-shrink: 0; }

        /* ── Color picker row ── */
        .color-row { display: flex; align-items: center; gap: 12px; }
        .color-swatch-wrap { position: relative; width: 44px; height: 44px; flex-shrink: 0; }
        .color-swatch-wrap input[type=color] { opacity: 0; position: absolute; inset: 0; width: 100%; height: 100%; cursor: pointer; border: none; padding: 0; }
        .color-swatch-display { width: 44px; height: 44px; border-radius: 8px; border: 2px solid #30363d; pointer-events: none; }
        .color-hex-input { width: 120px; }
        .color-preview-bar { height: 6px; border-radius: 3px; transition: background 0.2s; }

        /* ── Buttons ── */
        .btn-save { background: #238636; border: 1px solid #2ea043; color: #fff; font-family: 'Courier New', Courier, monospace; font-size: 0.9rem; letter-spacing: 0.03em; padding: 10px 28px; border-radius: 6px; transition: background 0.15s; cursor: pointer; }
        .btn-save:hover { background: #2ea043; color: #fff; }
        .btn-reset { background: transparent; border: 1px solid #6e3535; color: #f85149; font-family: 'Courier New', Courier, monospace; font-size: 0.9rem; letter-spacing: 0.03em; padding: 10px 28px; border-radius: 6px; transition: all 0.15s; cursor: pointer; }
        .btn-reset:hover { background: rgba(248,81,73,0.1); border-color: #f85149; color: #f85149; }

        /* ── Alerts ── */
        .dark-alert-success { background: #0d2818; border: 1px solid #2ea043; color: #3fb950; border-radius: 8px; padding: 12px 16px; font-size: 0.9rem; font-family: 'Courier New', Courier, monospace; }
        .dark-alert-danger { background: #2d1113; border: 1px solid #6e3535; color: #f85149; border-radius: 8px; padding: 12px 16px; font-size: 0.9rem; font-family: 'Courier New', Courier, monospace; }

        /* ── Page header ── */
        .settings-page-header { border-bottom: 1px solid #21262d; padding-bottom: 18px; margin-bottom: 28px; }
        .settings-page-header h2 { font-family: 'Courier New', Courier, monospace; color: #c9d1d9; font-size: 1.3rem; margin: 0; }
        .settings-page-header h2 span { color: #3fb950; }
        .settings-page-header .sub { font-family: 'Courier New', Courier, monospace; color: #484f58; font-size: 0.8rem; margin-top: 4px; }

        /* ── Section label line ── */
        .section-line { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
        .section-line::after { content: ''; flex: 1; height: 1px; background: #21262d; }

        /* ── Dirty state ── */
        .btn-save.dirty { background: #2ea043; box-shadow: 0 0 0 3px rgba(46,160,67,0.3); }
        .btn-reset.cancel-dirty { border-color: #f85149 !important; color: #f85149 !important; background: rgba(248,81,73,0.06); }
    </style>
</head>
<body>
    @include('components.topnav')
    @include('components.sidebar')

    <div class="main-content">
        <div class="container-fluid" style="max-width:960px;">

            {{-- Page Header --}}
            <div class="settings-page-header">
                <h2><span>$</span> medq <span style="color:#58a6ff;">admin</span> --configure</h2>
                <div class="sub">// Application Settings &nbsp;|&nbsp; admin@medq &nbsp;|&nbsp; {{ now()->format('Y-m-d H:i') }}</div>
            </div>

            {{-- Alerts --}}
            @if(session('success'))
                <div class="dark-alert-success mb-4">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                </div>
            @endif
            @if($errors->any())
                <div class="dark-alert-danger mb-4">
                    @foreach($errors->all() as $err)
                        <div><i class="fas fa-times-circle me-2"></i>{{ $err }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
                @csrf

                {{-- COLOUR SCHEME --}}
                <div class="section-line">
                    <span class="term-label">// colour scheme</span>
                </div>

                <div class="dark-card mb-4">
                    <div class="dark-card-header">
                        <span class="header-dot" style="background:#f78166;"></span>
                        <span class="header-dot" style="background:#e3b341;"></span>
                        <span class="header-dot" style="background:#3fb950;"></span>
                        <span class="title-text ms-1"><strong>colors.config</strong> — theme customisation</span>
                    </div>
                    <div class="p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="term-label mb-2">primary_color</div>
                                <div class="color-row">
                                    <div class="color-swatch-wrap">
                                        <div class="color-swatch-display" id="primary-swatch" style="background:{{ $settings['primary_color'] }};"></div>
                                        <input type="color" id="primary-picker" value="{{ $settings['primary_color'] }}"
                                               oninput="syncColor('primary', this.value)">
                                    </div>
                                    <input type="text" name="primary_color" id="primary-hex"
                                           class="form-control dark-input color-hex-input"
                                           value="{{ $settings['primary_color'] }}"
                                           maxlength="7" placeholder="#93c5fd"
                                           oninput="syncColorFromHex('primary', this.value)">
                                    <span class="term-prompt d-none d-md-inline">// navbar, buttons</span>
                                </div>
                                <div class="color-preview-bar mt-2" id="primary-bar" style="background:{{ $settings['primary_color'] }};"></div>
                                <div class="term-prompt mt-1">default: <span>#93c5fd</span></div>
                            </div>

                            <div class="col-md-6">
                                <div class="term-label mb-2">secondary_color</div>
                                <div class="color-row">
                                    <div class="color-swatch-wrap">
                                        <div class="color-swatch-display" id="secondary-swatch" style="background:{{ $settings['secondary_color'] }};"></div>
                                        <input type="color" id="secondary-picker" value="{{ $settings['secondary_color'] }}"
                                               oninput="syncColor('secondary', this.value)">
                                    </div>
                                    <input type="text" name="secondary_color" id="secondary-hex"
                                           class="form-control dark-input color-hex-input"
                                           value="{{ $settings['secondary_color'] }}"
                                           maxlength="7" placeholder="#bfdbfe"
                                           oninput="syncColorFromHex('secondary', this.value)">
                                    <span class="term-prompt d-none d-md-inline">// accents, highlights</span>
                                </div>
                                <div class="color-preview-bar mt-2" id="secondary-bar" style="background:{{ $settings['secondary_color'] }};"></div>
                                <div class="term-prompt mt-1">default: <span>#bfdbfe</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- LOGOS --}}
                <div class="section-line">
                    <span class="term-label">// logos</span>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-lg-6">
                        <div class="dark-card">
                            <div class="dark-card-header">
                                <span class="header-dot" style="background:#f78166;"></span>
                                <span class="header-dot" style="background:#e3b341;"></span>
                                <span class="header-dot" style="background:#3fb950;"></span>
                                <span class="title-text ms-1"><strong>splash.logo</strong> — loading screen ( / )</span>
                            </div>
                            <div class="p-4">
                                <div class="d-flex align-items-center gap-3 mb-4">
                                    @if($settings['splash_logo'])
                                        <img src="{{ asset('storage/' . $settings['splash_logo']) }}" class="logo-ring" id="splash-preview" alt="Splash Logo">
                                    @else
                                        <div class="logo-ring-placeholder" id="splash-placeholder"><i class="fas fa-image"></i></div>
                                        <img src="" class="logo-ring d-none" id="splash-preview" alt="Splash Logo">
                                    @endif
                                    <div>
                                        <div class="term-label mb-1">route: /</div>
                                        <div class="term-prompt">PNG · JPG · SVG · max 2MB</div>
                                        <div class="term-prompt">square + transparent bg recommended</div>
                                    </div>
                                </div>
                                <div class="upload-zone" onclick="document.getElementById('splash_logo_input').click()">
                                    <input type="file" id="splash_logo_input" name="splash_logo" accept="image/*"
                                           onchange="previewLogo(this,'splash-preview','splash-placeholder','splash-fname')">
                                    <div class="uz-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                    <div class="uz-title">Click to upload</div>
                                    <div class="uz-sub" id="splash-fname">no file selected</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="dark-card">
                            <div class="dark-card-header">
                                <span class="header-dot" style="background:#f78166;"></span>
                                <span class="header-dot" style="background:#e3b341;"></span>
                                <span class="header-dot" style="background:#3fb950;"></span>
                                <span class="title-text ms-1"><strong>welcome.logo</strong> — home page ( /welcome )</span>
                            </div>
                            <div class="p-4">
                                <div class="d-flex align-items-center gap-3 mb-4">
                                    @if($settings['welcome_logo'])
                                        <img src="{{ asset('storage/' . $settings['welcome_logo']) }}" class="logo-ring" id="welcome-preview" alt="Welcome Logo">
                                    @else
                                        <div class="logo-ring-placeholder" id="welcome-placeholder"><i class="fas fa-image"></i></div>
                                        <img src="" class="logo-ring d-none" id="welcome-preview" alt="Welcome Logo">
                                    @endif
                                    <div>
                                        <div class="term-label mb-1">route: /welcome</div>
                                        <div class="term-prompt">PNG · JPG · SVG · max 2MB</div>
                                        <div class="term-prompt">square + transparent bg recommended</div>
                                    </div>
                                </div>
                                <div class="upload-zone" onclick="document.getElementById('welcome_logo_input').click()">
                                    <input type="file" id="welcome_logo_input" name="welcome_logo" accept="image/*"
                                           onchange="previewLogo(this,'welcome-preview','welcome-placeholder','welcome-fname')">
                                    <div class="uz-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                    <div class="uz-title">Click to upload</div>
                                    <div class="uz-sub" id="welcome-fname">no file selected</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ORGANISATION --}}
                <div class="section-line">
                    <span class="term-label">// organisation</span>
                </div>

                <div class="dark-card mb-4">
                    <div class="dark-card-header">
                        <span class="header-dot" style="background:#f78166;"></span>
                        <span class="header-dot" style="background:#e3b341;"></span>
                        <span class="header-dot" style="background:#3fb950;"></span>
                        <span class="title-text ms-1"><strong>branding.config</strong> — public page text</span>
                    </div>
                    <div class="p-4">
                        <div class="row g-4">
                            <div class="col-md-7">
                                <div class="term-label mb-2">org_name</div>
                                <input type="text" name="org_name" id="orgname-input" class="form-control dark-input"
                                       value="{{ $settings['org_name'] }}"
                                       placeholder="MAKERERE UNIVERSITY MEDICAL STUDENTS ASSOCIATION (MUMSA)">
                                <div class="term-prompt mt-1">// shown beside logo on splash &amp; welcome</div>
                            </div>
                            <div class="col-md-5">
                                <div class="term-label mb-2">org_tagline</div>
                                <input type="text" name="org_tagline" id="orgtagline-input" class="form-control dark-input"
                                       value="{{ $settings['org_tagline'] }}"
                                       placeholder="All Rights Reserved &copy; 2026">
                                <div class="term-prompt mt-1">// copyright / tagline line</div>
                            </div>
                        </div>

                        {{-- Live branding preview --}}
                        <div class="mt-4 pt-4" style="border-top:1px solid #21262d;">
                            <div class="term-label mb-3">// live_preview() &mdash; splash &amp; welcome</div>
                            <div style="background:#f1f5f9; border-radius:10px; padding:16px 20px; display:flex; align-items:center; gap:14px; max-width:520px;">
                                <div style="width:48px;height:48px;border-radius:50%;border:3px solid #93c5fd;background:#fff;flex-shrink:0;display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-circle-notch" style="color:#93c5fd;font-size:1.2rem;"></i>
                                </div>
                                <div>
                                    <div id="preview-orgname" style="color:#1e293b;font-size:0.88rem;font-weight:700;line-height:1.4;">{{ $settings['org_name'] }}</div>
                                    <div id="preview-orgtagline" style="color:#64748b;font-size:0.78rem;margin-top:3px;">{{ $settings['org_tagline'] }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SAVE --}}
                <div class="d-flex align-items-center gap-3 flex-wrap mb-4">
                    <button type="submit" class="btn-save" id="save-btn">
                        <i class="fas fa-save me-2"></i><span id="save-label">save_settings()</span>
                    </button>
                    <a href="{{ route('admin.dashboard') }}" class="btn-reset" id="cancel-btn" style="text-decoration:none;">
                        <span id="cancel-label">cancel()</span>
                    </a>
                </div>

            </form>

            {{-- DANGER ZONE --}}
            <div class="section-line">
                <span class="term-label" style="color:#f85149;">// danger zone</span>
            </div>
            <div class="dark-card mb-5" style="border-color:#6e3535;">
                <div class="dark-card-header" style="background:#1a1213; border-color:#6e3535;">
                    <span class="header-dot" style="background:#f78166;"></span>
                    <span class="header-dot" style="background:#e3b341;"></span>
                    <span class="header-dot" style="background:#3fb950;"></span>
                    <span class="title-text ms-1" style="color:#f85149;"><strong>reset.config</strong> — destructive action</span>
                </div>
                <div class="p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <div class="term-label mb-1" style="color:#f85149;">reset_to_defaults()</div>
                        <div class="term-prompt">Removes uploaded logos · resets colours to <span>#93c5fd</span> / <span>#bfdbfe</span> · resets branding text</div>
                    </div>
                    <form method="POST" action="{{ route('admin.settings.reset') }}"
                          onsubmit="return confirm('Reset all settings to factory defaults? This cannot be undone.')">
                        @csrf
                        <button type="submit" class="btn-reset">
                            <i class="fas fa-undo me-2"></i>reset_to_defaults()
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script>
        function syncColor(name, hex) {
            document.getElementById(name + '-swatch').style.background = hex;
            document.getElementById(name + '-bar').style.background = hex;
            document.getElementById(name + '-hex').value = hex;
            document.documentElement.style.setProperty('--' + name, hex);
        }
        function syncColorFromHex(name, hex) {
            if (/^#[0-9a-fA-F]{6}$/.test(hex)) {
                document.getElementById(name + '-swatch').style.background = hex;
                document.getElementById(name + '-bar').style.background = hex;
                document.getElementById(name + '-picker').value = hex;
                document.documentElement.style.setProperty('--' + name, hex);
            }
        }
        function previewLogo(input, previewId, placeholderId, fnameId) {
            if (!input.files || !input.files[0]) return;
            document.getElementById(fnameId).textContent = input.files[0].name;
            const reader = new FileReader();
            reader.onload = e => {
                const preview = document.getElementById(previewId);
                const ph = document.getElementById(placeholderId);
                preview.src = e.target.result;
                preview.classList.remove('d-none');
                if (ph) ph.style.display = 'none';
            };
            reader.readAsDataURL(input.files[0]);
        }
        function syncBranding() {
            const n = document.getElementById('orgname-input').value;
            const t = document.getElementById('orgtagline-input').value;
            document.getElementById('preview-orgname').textContent = n || '—';
            document.getElementById('preview-orgtagline').textContent = t || '—';
        }
        let _isDirty = false;
        function markDirty() {
            if (_isDirty) return;
            _isDirty = true;
            const saveBtn  = document.getElementById('save-btn');
            const saveLabel  = document.getElementById('save-label');
            const cancelBtn  = document.getElementById('cancel-btn');
            const cancelLabel = document.getElementById('cancel-label');
            saveBtn.classList.add('dirty');
            saveLabel.textContent = 'save_settings() ●';
            cancelBtn.classList.add('cancel-dirty');
            cancelLabel.textContent = 'discard_changes()';
        }
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('form[action*="settings"]');
            if (form) { form.addEventListener('input', markDirty); form.addEventListener('change', markDirty); }
        });
    </script>
</body>
</html>
