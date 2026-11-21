<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log in - EduGPT</title>
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>
    <button class="theme-toggle-fixed" id="themeToggle" aria-label="Toggle theme">
        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M20.5 14.2A8.3 8.3 0 0 1 9.8 3.5 8.5 8.5 0 1 0 20.5 14.2Z"/>
        </svg>
    </button>

    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none">
                    <path d="M12 3.5a4.2 4.2 0 0 1 4 2.9 4.2 4.2 0 0 1 2.7 7.2 4.2 4.2 0 0 1-6.4 4.8A4.2 4.2 0 0 1 5.9 15 4.2 4.2 0 0 1 8 7.2 4.2 4.2 0 0 1 12 3.5Z" stroke="currentColor" stroke-width="1.8"/>
                    <path d="m8.6 8.4 6.8 4M8.6 15.6V8.4l6.2-3.5M15.4 15.6l-6.8-4m6.8-3.2v7.2l-6.2 3.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            </div>
            <h1 class="auth-title">Welcome back</h1>
            <p class="auth-subtitle">Log in to your EduGPT account to continue your conversations</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form action="{{ url('/login') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="email" class="form-label">Email address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="form-input @error('email') is-invalid @enderror" 
                    value="{{ old('email') }}" 
                    required 
                    autofocus
                    placeholder="name@example.com"
                >
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="form-input @error('password') is-invalid @enderror" 
                    required
                    placeholder="••••••••"
                >
            </div>

            <div class="form-actions">
                <label class="remember-label">
                    <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <span>Remember me</span>
                </label>
            </div>

            <button type="submit" class="btn-primary">
                Log In
            </button>
        </form>

        <div class="auth-footer">
            Don't have an account? 
            <a href="{{ url('/register') }}" class="auth-link">Sign up</a>
        </div>
    </div>

    <script>
        const root = document.documentElement;
        const savedTheme = localStorage.getItem('chatbot-theme');
        if (savedTheme) {
            root.dataset.theme = savedTheme;
        } else if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
            root.dataset.theme = 'dark';
        }

        document.getElementById('themeToggle').addEventListener('click', () => {
            const isDark = root.dataset.theme === 'dark';
            root.dataset.theme = isDark ? 'light' : 'dark';
            localStorage.setItem('chatbot-theme', root.dataset.theme);
        });
    </script>
</body>
</html>
